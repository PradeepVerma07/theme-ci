<?php
/**
 * Editable, conditional footers.
 *
 * Create as many "Footers" as you like (new admin menu: Footers), each with its own
 * logo, CTA, about text and copyright line, plus a rule for where it should appear —
 * the whole site, the front page only, specific pages, specific content types, or
 * specific categories. When more than one footer matches a page, the most specific
 * match wins, and "Priority" breaks ties. If nothing matches (or you haven't created
 * any Footers yet) the original CI360 Settings footer is used, so existing sites are
 * unaffected until a Footer is created on purpose.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------ Post type */

add_action( 'init', function () {
	register_post_type(
		'ci_footer',
		array(
			'labels'             => array(
				'name'          => 'Footers',
				'singular_name' => 'Footer',
				'add_new_item'  => 'Add new footer',
				'edit_item'     => 'Edit footer',
				'menu_name'     => 'Footers',
				'not_found'     => 'No footers yet. The default CI360 Settings footer is shown everywhere until you create one.',
			),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-align-center',
			'menu_position'      => 22,
			'supports'           => array( 'title' ),
		)
	);
} );

/* ------------------------------------------------------------------ Fields */

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) || ! function_exists( 'ci_group' ) ) {
		return;
	}
	ci_group(
		'footer_template',
		'Footer content & display rule',
		array(
			ci_tab( 'Content' ),
			ci_f( 'true_false', 'footer_status', 'Active', 1, array( 'ui' => 1, 'instructions' => 'Turn off to keep this footer saved without showing it anywhere.' ) ),
			ci_f( 'image', 'footer_logo', 'Footer logo (optional — falls back to the site logo)' ),
			ci_f( 'number', 'footer_logo_width', 'Footer logo width (px)', 160, array( 'min' => 60, 'max' => 360 ) ),
			ci_t( 'footer_label_1', 'CTA label (left)', 'NEXT CHAPTER' ),
			ci_t( 'footer_label_2', 'CTA label (right)', 'START A CONVERSATION' ),
			ci_h( 'footer_heading', 'CTA heading', 'Let&apos;s make<br>your <em>next move.</em>' ),
			ci_t( 'footer_link', 'CTA link', '/contact/' ),
			ci_h( 'footer_sticker', 'Sticker text', 'A FULL CIRCLE<br>OF POSSIBILITIES' ),
			ci_ta( 'footer_about', 'About text', 'An integrated digital marketing and strategic communication agency built around the power of strategic storytelling.' ),
			ci_t( 'footer_explore', 'Links label', 'EXPLORE' ),
			ci_t( 'footer_offices', 'Offices label', 'THREE LOCATIONS. ONE CONNECTED TEAM.' ),
			ci_t( 'footer_copyright', 'Copyright line', '© {year} {brand}.' ),

			ci_tab( 'Show this footer on' ),
			ci_f(
				'message',
				'footer_rule_help',
				'How this works',
				null,
				array( 'message' => 'If two or more footers match the same page, the most specific match wins (a specific page beats a category, which beats a content type, which beats the whole site). Use Priority to break a tie between two footers with the same kind of rule.' )
			),
			ci_sel(
				'footer_match',
				'Condition',
				array(
					'entire_site' => 'Entire site (this becomes the default footer)',
					'front_page'  => 'Front page only',
					'post_types'  => 'Specific content types',
					'pages'       => 'Specific pages',
					'categories'  => 'Specific categories',
				),
				'entire_site'
			),
			ci_f(
				'checkbox',
				'footer_post_types',
				'Content types',
				array(),
				array(
					'choices'           => array(
						'page'       => 'Pages',
						'post'       => 'Blog posts',
						'ci_project' => 'Projects',
						'ci_insight' => 'Insights',
						'ci_service' => 'Services',
					),
					'conditional_logic' => array( array( array( 'field' => 'field_ci_footer_match', 'operator' => '==', 'value' => 'post_types' ) ) ),
				)
			),
			ci_f(
				'post_object',
				'footer_pages',
				'Pages',
				array(),
				array(
					'post_type'         => array( 'page' ),
					'multiple'          => 1,
					'ui'                => 1,
					'conditional_logic' => array( array( array( 'field' => 'field_ci_footer_match', 'operator' => '==', 'value' => 'pages' ) ) ),
				)
			),
			ci_f(
				'taxonomy',
				'footer_categories',
				'Categories',
				array(),
				array(
					'taxonomy'          => 'category',
					'field_type'        => 'checkbox',
					'return_format'     => 'id',
					'conditional_logic' => array( array( array( 'field' => 'field_ci_footer_match', 'operator' => '==', 'value' => 'categories' ) ) ),
				)
			),
			ci_f( 'number', 'footer_priority', 'Priority', 10 ),
		),
		ci_loc_type( 'ci_footer' )
	);
} );

/* ------------------------------------------------------------------ Matching */

/** ID of the most specific, active Footer matching the page currently being viewed, or 0. */
function ci_match_footer() {
	$footers = get_posts(
		array(
			'post_type'        => 'ci_footer',
			'posts_per_page'   => -1,
			'post_status'      => 'publish',
			'suppress_filters' => false,
		)
	);
	if ( ! $footers ) {
		return 0;
	}

	$queried_id = get_queried_object_id();
	$post_type  = $queried_id ? get_post_type( $queried_id ) : '';
	$cat_ids    = ( $queried_id && is_singular() ) ? wp_get_post_categories( $queried_id, array( 'fields' => 'ids' ) ) : array();

	$best = array( -1, 0, 0 ); // [specificity, priority, footer id]
	foreach ( $footers as $f ) {
		if ( ! ci_get( 'footer_status', $f->ID ) ) {
			continue;
		}
		$match = ci_get( 'footer_match', $f->ID );
		$spec  = -1;

		if ( 'entire_site' === $match ) {
			$spec = 0;
		} elseif ( 'front_page' === $match && is_front_page() ) {
			$spec = 3;
		} elseif ( 'post_types' === $match && $post_type && in_array( $post_type, (array) ci_get( 'footer_post_types', $f->ID ), true ) ) {
			$spec = 1;
		} elseif ( 'pages' === $match && is_page() && in_array( (int) $queried_id, array_map( 'intval', (array) ci_get( 'footer_pages', $f->ID ) ), true ) ) {
			$spec = 4;
		} elseif ( 'categories' === $match && $cat_ids && array_intersect( $cat_ids, array_map( 'intval', (array) ci_get( 'footer_categories', $f->ID ) ) ) ) {
			$spec = 2;
		}

		if ( $spec < 0 ) {
			continue;
		}
		$priority = (int) ci_get( 'footer_priority', $f->ID );
		if ( $spec > $best[0] || ( $spec === $best[0] && $priority > $best[1] ) ) {
			$best = array( $spec, $priority, $f->ID );
		}
	}
	return $best[2];
}

/** Site logo for a specific footer post, falling back to the global footer logo/site logo. */
function ci_footer_post_logo( $footer_id ) {
	$logo_id = (int) ci_get( 'footer_logo', $footer_id );
	if ( ! $logo_id ) {
		return ci_site_logo( 'footer' );
	}
	$brand = ci_opt( 'opt_brand' );
	$width = (int) ci_get( 'footer_logo_width', $footer_id );
	$width = $width ? max( 60, min( 360, $width ) ) : 160;
	$img   = wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'ci360-logo-img', 'loading' => 'eager', 'decoding' => 'async', 'alt' => $brand ) );
	if ( ! $img ) {
		return ci_site_logo( 'footer' );
	}
	return '<a href="' . esc_url( home_url( '/' ) ) . '" class="logo logo--image logo--footer" aria-label="' . esc_attr( $brand ) . ' home" style="--ci-logo-width:' . (int) $width . 'px">' . $img . '</a>';
}

/** Same markup as the default footer, filled from one Footer post's fields. */
function ci_render_footer_post( $footer_id ) {
	$ci_email = ci_opt( 'opt_email' );
	$ci_phone = ci_opt( 'opt_phone' );
	$f        = function ( $name ) use ( $footer_id ) {
		return ci_get( 'footer_' . $name, $footer_id );
	};
	ob_start();
	?><footer class="footer"><div class="footer-cta wrap"><div><?php echo ci_label( $f( 'label_1' ), $f( 'label_2' ) ); ?><a class="footer-head" href="<?php echo esc_url( ci_url( $f( 'link' ) ) ); ?>"><?php echo ci_html( $f( 'heading' ) ); ?><span><?php echo ci_arrow(); ?></span></a></div><div class="footer-sticker" aria-hidden="true"><?php echo ci_star(); ?><span><?php echo ci_html( $f( 'sticker' ) ); ?></span></div></div><div class="footer-lower wrap"><div class="footer-brand"><?php echo ci_footer_post_logo( $footer_id ); ?><p><?php echo ci_e( $f( 'about' ) ); ?></p><a class="text-link" href="mailto:<?php echo esc_attr( $ci_email ); ?>"><?php echo ci_e( $ci_email ); ?> <?php echo ci_arrow(); ?></a></div><div class="footer-links"><span class="small-label"><?php echo ci_e( $f( 'explore' ) ); ?></span><?php
	foreach ( ci_menu( 'footer' ) as $ci_item ) {
		echo '<a href="' . esc_url( $ci_item[0] ) . '">' . ci_e( $ci_item[1] ) . '</a>';
	}
	?></div><div class="footer-offices"><span class="small-label"><?php echo ci_e( $f( 'offices' ) ); ?></span><?php
	foreach ( ci_rows( 'opt_offices', 'option' ) as $ci_o ) {
		echo '<div><span>' . ci_e( $ci_o['city'] ) . ' <small>' . ci_e( $ci_o['country_code'] ) . '</small></span><time data-zone="' . esc_attr( $ci_o['zone'] ) . '" aria-label="Current time in ' . esc_attr( $ci_o['city'] ) . '">--:--</time></div>';
	}
	?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $ci_phone ) ); ?>"><?php echo ci_e( $ci_phone ); ?></a></div></div><div class="footer-bottom wrap"><span><?php $ci_copy = strtr( (string) $f( 'copyright' ), array( '{year}' => gmdate( 'Y' ), '{brand}' => ci_opt( 'opt_brand' ) ) ); echo ci_e( $ci_copy ); ?></span><div><?php
	foreach ( ci_menu( 'legal' ) as $ci_item ) {
		echo '<a href="' . esc_url( $ci_item[0] ) . '">' . ci_e( $ci_item[1] ) . '</a>';
	}
	?><button class="motion-toggle" aria-pressed="false">Motion <span>on</span></button><button class="back-top" aria-label="Back to top">Back to top <?php echo ci_arrow(); ?></button></div></div></footer><?php
	return ob_get_clean();
}

/** Small admin hint on the Footers list screen. */
add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'edit-ci_footer' === $screen->id ) {
		echo '<div class="notice notice-info"><p>Nothing here yet? The site is using the default footer from <strong>CI360 Settings</strong>. Add a Footer and set a condition to override it for the whole site, the front page, specific pages, content types or categories.</p></div>';
	}
} );
