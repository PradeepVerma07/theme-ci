<?php
/**
 * Small helpers. Each markup helper mirrors a helper in the prototype JS
 * (e, img, arrow, star, label, btn, tags, paragraphs, breadcrumb) so the HTML is identical.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Field value for a post (defaults come from the field definitions). */
function ci_get( $name, $post_id = null ) {
	if ( null === $post_id ) {
		$post_id = get_the_ID();
	}
	if ( function_exists( 'get_field' ) ) {
		$v = get_field( $name, $post_id );
		if ( null !== $v && false !== $v ) {
			return $v;
		}
	} else {
		$v = is_numeric( $post_id ) ? get_post_meta( $post_id, $name, true ) : get_option( 'options_' . $name );
		if ( '' !== $v && false !== $v ) {
			return $v;
		}
	}
	return $GLOBALS['ci_defaults'][ $name ] ?? '';
}
/** Field value from the Site Settings page. */
function ci_opt( $name ) {
	return ci_get( $name, 'option' );
}
/** Term field value. */
function ci_term_get( $name, $term ) {
	return ci_get( $name, $term instanceof WP_Term ? $term : get_term( $term ) );
}
/** Repeater rows as an array (never null). */
function ci_rows( $name, $post_id = null ) {
	$v = ci_get( $name, $post_id );
	return is_array( $v ) ? $v : array();
}

function ci_e( $s ) {
	return esc_html( (string) $s );
}
/** Short rich text: line breaks, italic accents, small, strong and links. */
function ci_html( $s ) {
	return wp_kses(
		(string) $s,
		array(
			'br'     => array(),
			'em'     => array(),
			'small'  => array(),
			'strong' => array(),
			'b'      => array(),
			'a'      => array( 'href' => array(), 'target' => array(), 'rel' => array() ),
		)
	);
}
/** Site-relative links ("/contact/") become full URLs. */
function ci_url( $url ) {
	$url = (string) $url;
	if ( '' === $url ) {
		return home_url( '/' );
	}
	if ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
		return home_url( $url );
	}
	return $url;
}
/** Rewrites root-relative hrefs inside rich text. */
function ci_links( $html ) {
	return preg_replace_callback(
		'/href="(\/[^"\/][^"]*|\/)"/',
		function ( $m ) {
			return 'href="' . esc_url( home_url( $m[1] ) ) . '"';
		},
		$html
	);
}

function ci_img( $id, $alt = null, $cls = '', $eager = false ) {
	if ( is_string( $id ) && '' !== $id ) {
		if ( false !== strpos( $id, '/' ) ) {
			$src = ci_url( $id );
		} else {
			$src = get_template_directory_uri() . '/assets/images/' . $id;
		}
		if ( null === $alt ) {
			$alt = pathinfo( $id, PATHINFO_FILENAME );
		}
	} else {
		$id = (int) $id;
		if ( ! $id ) {
			return '';
		}
		$src = wp_get_attachment_image_url( $id, 'full' );
		if ( ! $src ) {
			return '';
		}
		if ( null === $alt ) {
			$alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
		}
	}
	return '<img class="' . esc_attr( $cls ) . '" src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ?? '' ) . '" loading="' . ( $eager ? 'eager' : 'lazy' ) . '" decoding="async" ' . ( $eager ? 'fetchpriority="high"' : '' ) . '>';
}
function ci_arrow( $dir = 'ne' ) {
	return '<svg class="icon arrow-' . $dir . '" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 19 19 5M5 5h14v14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}
function ci_star() {
	return '<svg class="star" viewBox="0 0 100 100" aria-hidden="true"><path d="M45 0h10v35L80 10l8 8-25 27h37v10H64l25 25-8 8-26-25v37H45V64L20 89l-8-8 25-26H0V45h35L10 20l8-8 27 25Z" fill="currentColor"/></svg>';
}
function ci_label( $n, $t ) {
	return '<div class="section-label"><span>' . ci_e( $n ) . '</span><span>' . ci_e( $t ) . '</span></div>';
}
/** Label from a section field prefix. */
function ci_sec_label( $p, $post_id = null ) {
	return ci_label( ci_get( $p . '_label_1', $post_id ), ci_get( $p . '_label_2', $post_id ) );
}
function ci_btn( $t, $url, $kind = 'dark' ) {
	return '<a class="button button-' . $kind . '" href="' . esc_url( ci_url( $url ) ) . '" data-magnetic><span>' . ci_html( $t ) . '</span><i>' . ci_arrow() . '</i></a>';
}
function ci_tags( $a ) {
	$out = '<div class="tags">';
	foreach ( $a as $t ) {
		$out .= '<span>' . ci_e( $t ) . '</span>';
	}
	return $out . '</div>';
}
/** Paragraph text (blank-line separated) or array to <p> tags. */
function ci_paragraphs( $text, $first_class = '' ) {
	$parts = is_array( $text ) ? $text : preg_split( '/\R\s*\R/', trim( (string) $text ) );
	$out   = '';
	foreach ( array_values( array_filter( array_map( 'trim', $parts ), 'strlen' ) ) as $i => $p ) {
		$cls  = ( 0 === $i && $first_class ) ? ' class="' . esc_attr( $first_class ) . '"' : '';
		$out .= '<p' . $cls . '>' . ci_e( $p ) . '</p>';
	}
	return $out;
}
function ci_breadcrumb( $t ) {
	return '<div class="breadcrumb"><a href="' . esc_url( home_url( '/' ) ) . '">Home</a><span>/</span>' . ci_e( $t ) . '</div>';
}
function ci_pad( $n ) {
	return str_pad( (string) $n, 2, '0', STR_PAD_LEFT );
}

/** Published posts of a type, in menu order. */
function ci_posts( $type, $args = array() ) {
	return get_posts(
		array_merge(
			array(
				'post_type'        => $type,
				'posts_per_page'   => -1,
				'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
				'post_status'      => 'publish',
				'suppress_filters' => false,
			),
			$args
		)
	);
}
function ci_count( $type ) {
	return (int) wp_count_posts( $type )->publish;
}
function ci_term_name( $post_id, $tax ) {
	$terms = get_the_terms( $post_id, $tax );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
}

/** ID of a core page (about, founders, services, work, insights, contact…). */
function ci_page_id( $key ) {
	$map = (array) get_option( 'ci360_pages', array() );
	if ( ! empty( $map[ $key ] ) && get_post_status( $map[ $key ] ) ) {
		return (int) $map[ $key ];
	}
	$pages = get_pages( array( 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/' . $key . '.php', 'number' => 1 ) );
	if ( $pages ) {
		return (int) $pages[0]->ID;
	}
	$page = get_page_by_path( $key );
	return $page ? (int) $page->ID : 0;
}

/** Raw post title (no smart-quote formatting, matching the design copy). */
function ci_title( $post ) {
	return (string) get_post_field( 'post_title', $post );
}
