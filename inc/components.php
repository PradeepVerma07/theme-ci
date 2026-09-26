<?php
/**
 * Data mappers and shared components (ports of the prototype render helpers).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------------ Data */

/** Ordered IDs of a post type (cached per request). */
function ci_ids( $type ) {
	static $cache = array();
	if ( ! isset( $cache[ $type ] ) ) {
		$cache[ $type ] = wp_list_pluck( ci_posts( $type ), 'ID' );
	}
	return $cache[ $type ];
}

function ci_service( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$id    = $post->ID;
	$index = array_search( $id, ci_ids( 'ci_service' ), true );
	$num   = ci_get( 'service_number', $id );
	return array(
		'id'       => $id,
		'title'    => ci_title( $id ),
		'url'      => get_permalink( $id ),
		'slug'     => $post->post_name,
		'number'   => $num ? $num : ci_pad( false === $index ? 0 : $index + 1 ),
		'index'    => false === $index ? 0 : $index,
		'group'    => ci_term_name( $id, 'ci_service_group' ),
		'summary'  => ci_get( 'service_summary', $id ),
		'body'     => ci_get( 'service_body', $id ),
		'headline' => ci_get( 'service_headline', $id ),
		'tags'     => wp_list_pluck( ci_rows( 'service_tags', $id ), 'tag' ),
		'steps'    => ci_rows( 'service_steps', $id ),
		'tone'     => ci_get( 'service_tone', $id ),
		'visual'   => ci_get( 'service_visual', $id ),
		'image'    => (int) ci_get( 'service_image', $id ),
		'card_image' => (int) ci_get( 'service_card_image', $id ),
		'images'   => array_values( array_filter( array_map( 'intval', (array) ci_get( 'service_visual_images', $id ) ) ) ),
	);
}

function ci_project( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$id    = $post->ID;
	$index = array_search( $id, ci_ids( 'ci_project' ), true );
	$terms = get_the_terms( $id, 'ci_project_category' );
	$term  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$image = (int) ci_get( 'project_image', $id );
	$front = (int) ci_get( 'project_scene_front', $id );
	$head  = ci_get( 'project_headline', $id );
	if ( ! $head ) {
		$head = ( $term && ci_term_get( 'cat_headline', $term ) ) ? ci_term_get( 'cat_headline', $term ) : ci_opt( 'opt_sector_headline' );
	}
	return array(
		'id'         => $id,
		'name'       => ci_title( $id ),
		'url'        => get_permalink( $id ),
		'slug'       => $post->post_name,
		'number'     => ci_pad( false === $index ? 1 : $index + 1 ),
		'term'       => $term,
		'category'   => $term ? $term->name : '',
		'type'       => ci_get( 'project_type', $id ),
		'tone'       => ci_get( 'project_tone', $id ),
		'headline'   => $head,
		'summary'    => ci_get( 'project_summary', $id ),
		'image'      => $image,
		'scene'      => ci_get( 'project_scene', $id ),
		'front'      => $front ? $front : $image,
		'side'       => (int) ci_get( 'project_scene_side', $id ),
		'caption'    => ci_get( 'project_scene_caption', $id ),
		'gallery'    => array_values( array_filter( array_map( 'intval', (array) ci_get( 'project_gallery', $id ) ) ) ),
		'note'       => ci_get( 'project_note', $id ),
		'disclosure' => ci_get( 'project_disclosure', $id ),
		'sections'   => ci_rows( 'project_case_sections', $id ),
		'featured'   => (int) ci_get( 'project_featured', $id ),
	);
}

/** Featured projects (home page stack) in their featured order. */
function ci_featured_projects() {
	$list = array();
	foreach ( ci_ids( 'ci_project' ) as $id ) {
		$f = (int) ci_get( 'project_featured', $id );
		if ( $f > 0 ) {
			$list[ $f * 1000 + count( $list ) ] = $id;
		}
	}
	ksort( $list );
	return array_values( $list );
}

function ci_insight( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$id       = $post->ID;
	$sections = ci_rows( 'insight_sections', $id );
	$read     = ci_get( 'insight_read', $id );
	if ( ! $read ) {
		$words = str_word_count( wp_strip_all_tags( implode( ' ', wp_list_pluck( $sections, 'text' ) ) ) );
		$read  = max( 1, (int) ceil( $words / 200 ) ) . ' min';
	}
	return array(
		'id'       => $id,
		'title'    => ci_title( $id ),
		'url'      => get_permalink( $id ),
		'kicker'   => ci_get( 'insight_kicker', $id ),
		'tone'     => ci_get( 'insight_tone', $id ),
		'image'    => (int) ci_get( 'insight_image', $id ),
		'read'     => $read,
		'intro'    => ci_get( 'insight_intro', $id ),
		'sections' => $sections,
		'status'   => ci_get( 'insight_status', $id ),
		'note'     => ci_get( 'insight_note', $id ),
	);
}

/* ------------------------------------------------------------------ Projects */

function ci_type_label( $type ) {
	return ci_opt( 'tpl_type_' . ( in_array( $type, array( 'case', 'gallery' ), true ) ? $type : 'perspective' ) );
}

function ci_case_visual( $p ) {
	if ( 'perspective' === $p['type'] ) {
		return '<div class="editorial-art tone-' . esc_attr( $p['tone'] ) . '">' . ci_img( $p['image'], 'Editorial sector image for ' . $p['category'] ) . '<div class="art-overlay"></div><span class="art-eyebrow">' . ci_e( ci_opt( 'tpl_art_eyebrow' ) ) . '</span><div class="art-type">' . ci_e( $p['name'] ) . '</div><span class="art-bottom">' . ci_e( $p['category'] ) . ' ' . ci_star() . '</span></div>';
	}
	$cap = ci_html( $p['caption'] );
	switch ( $p['scene'] ) {
		case 'crave':
			return '<div class="poster-scene scene-crave">' . ci_img( $p['side'], null, 'poster side-poster' ) . ci_img( $p['front'], null, 'poster front-poster' ) . ci_star() . '</div>';
		case 'station':
			return '<div class="poster-scene scene-station"><div class="signal-rings" aria-hidden="true"></div>' . ci_img( $p['front'], null, 'poster front-poster' ) . '<span class="scene-caption">' . $cap . '</span></div>';
		case 'vardan':
			return '<div class="poster-scene scene-vardan">' . ci_img( $p['side'], null, 'poster side-poster' ) . ci_img( $p['front'], null, 'poster front-poster' ) . '<span class="scene-number" aria-hidden="true">' . $cap . '</span></div>';
		case 'ayaan':
			return '<div class="poster-scene scene-ayaan">' . ci_img( $p['front'], null, 'architecture-img' ) . '<div class="architecture-caption">' . $cap . '</div></div>';
		case 'museum':
			return '<div class="poster-scene scene-museum"><div class="browser-frame"><div class="browser-bar"><i></i><i></i><i></i></div>' . ci_img( $p['front'] ) . '</div>' . ci_img( $p['side'], null, 'museum-cut' ) . '</div>';
	}
	return '<div class="poster-scene tone-' . esc_attr( $p['tone'] ) . '">' . ci_img( $p['front'], $p['name'] . ' supplied creative', 'poster front-poster' ) . '</div>';
}

function ci_project_card( $p ) {
	return '<article class="project-card" data-category="' . esc_attr( $p['category'] ) . '" data-name="' . esc_attr( mb_strtolower( $p['name'] ) ) . '" data-reveal><a class="project-media" href="' . esc_url( $p['url'] ) . '" aria-label="Explore ' . esc_attr( $p['name'] ) . '" data-cursor="Explore">' . ci_case_visual( $p ) . '<span class="media-arrow">' . ci_arrow() . '</span></a><div class="project-caption"><div><span class="small-label">' . ci_e( $p['category'] ) . ' / ' . ci_e( ci_type_label( $p['type'] ) ) . '</span><h3><a href="' . esc_url( $p['url'] ) . '">' . ci_e( $p['name'] ) . '</a></h3></div><span class="project-index">' . ci_e( $p['number'] ) . '</span></div></article>';
}

/* ------------------------------------------------------------------ Services */

function ci_service_visual( $s ) {
	$total = ci_pad( ci_count( 'ci_service' ) );
	$imgs  = $s['images'];
	$im    = function ( $i ) use ( $imgs, $s ) {
		if ( isset( $imgs[ $i ] ) && $imgs[ $i ] ) {
			return ci_img( $imgs[ $i ] );
		}
		if ( 0 === $i && $s['card_image'] ) {
			return ci_img( $s['card_image'] );
		}
		if ( $s['image'] ) {
			return ci_img( $s['image'] );
		}
		return '';
	};

	$photo         = $s['image'] ? ci_img( $s['image'], $s['title'] . ' visual composition', 'service-photo' ) : '';
	$card_photo_id = $s['card_image'] ? $s['card_image'] : ( isset( $imgs[0] ) ? $imgs[0] : 0 );
	$card_photo    = $card_photo_id ? ci_img( $card_photo_id, $s['title'] . ' card image', 'service-card-photo-img' ) : '';

	$frame = function ( $inside ) use ( $s, $total ) {
		return '<div class="service-visual visual-' . esc_attr( $s['visual'] ) . ' tone-' . esc_attr( $s['tone'] ) . '"><span class="visual-label">CI360 / ' . ci_e( mb_strtoupper( $s['group'] ) ) . '</span>' . $inside . '<span class="visual-corner">' . ci_e( $s['number'] ) . ' / ' . $total . '</span></div>';
	};

	switch ( $s['visual'] ) {
		case 'strategy':
			$paper_content = $card_photo ? $card_photo : '<span>THE STARTING POINT</span><b>A better<br>question.</b>' . ci_star();
			return $frame( ( $photo ? $photo : '' ) . '<div class="strategy-paper' . ( $card_photo ? ' strategy-paper-has-img' : '' ) . '">' . $paper_content . '</div><div class="pencil-line" aria-hidden="true"></div>' );

		case 'branding':
			$board_content = $card_photo ? $card_photo : '<span>THE BRAND IS<br>THE FEELING.</span><b>Aa.</b><div class="colour-swatches"><i></i><i></i><i></i><i></i></div>';
			return $frame( ( $photo ? $photo : '' ) . '<div class="brand-board' . ( $card_photo ? ' brand-board-has-img' : '' ) . '">' . $board_content . '</div><div class="brand-paper">ci360&deg;' . ci_star() . '<small>MADE TO BE REMEMBERED.</small></div>' );

		case 'website':
			$b_img = $photo ? $photo : $im(0);
			$p_img = $card_photo ? $card_photo : $im(1);
			return $frame( '<div class="device-browser"><div class="browser-bar"><i></i><i></i><i></i></div>' . $b_img . '</div><div class="device-phone">' . $p_img . '</div><span class="design-cross" aria-hidden="true">+</span>' );

		case 'social':
		case 'creative':
			$post1 = $photo ? $photo : $im(0);
			$post2 = $card_photo ? $card_photo : $im(1);
			return $frame( '<div class="social-post post-one">' . $post1 . '</div><div class="social-post post-two">' . $post2 . '</div><div class="social-heart" aria-hidden="true">&hearts;</div>' );

		case 'performance':
			$bars = '';
			foreach ( array( 35, 48, 42, 66, 54, 85, 96 ) as $h ) {
				$bars .= '<i style="--h:' . $h . '%"></i>';
			}
			$phone_img = $photo ? $photo : $im(0);
			$metric    = $card_photo ? '<div class="metric-card metric-card-has-img">' . $card_photo . '</div>' : '<div class="metric-card"><span>CREATIVE + MEDIA</span><b>Make it<br>mean more.</b><div class="mini-bars" aria-hidden="true">' . $bars . '</div></div>';
			return $frame( '<div class="ad-phone">' . $phone_img . '</div>' . $metric );

		case 'search':
			return $frame( ( $photo ? $photo : '' ) . '<div class="search-orbit" aria-hidden="true"></div><div class="search-window"><span class="search-address">A CLEARER WAY TO BE FOUND</span><div class="search-input">Your brand, discovered.<span>' . ci_arrow() . '</span></div><b>SEO. AEO. GEO.</b><span class="search-result-line"></span><span class="search-result-line short"></span><div class="search-tags">' . ci_tags( array( 'Search', 'AI answers', 'Local' ) ) . '</div></div>' );

		case 'podcast':
			$wave = '';
			for ( $i = 0; $i < 25; $i++ ) {
				$wave .= '<i style="--i:' . $i . ';--h:' . ( 20 + ( $i * 37 % 80 ) ) . '%"></i>';
			}
			return $frame( ( $photo ? $photo : '' ) . '<div class="podcast-grid" aria-hidden="true"></div><div class="mic"><div class="mic-head"></div><div class="mic-arm"></div><div class="mic-base"></div></div><div class="audio-wave" aria-hidden="true">' . $wave . '</div><span class="on-air"><i></i> STORIES, ON AIR.</span>' );

		case 'film':
			return $frame( '<div class="film-photo">' . ( $photo ? $photo : $im(0) ) . '<div class="viewfinder" aria-hidden="true"><i></i><i></i><i></i><i></i></div><span class="rec">REC <i></i></span></div>' . ( $card_photo ? '<div class="film-card-img">' . $card_photo . '</div>' : '<span class="film-title">A DIFFERENT<br>POINT OF VIEW.</span>' ) );

		case 'crm':
			return $frame( '<div class="crm-photo">' . ( $photo ? $photo : $im(0) ) . '</div>' . ( $card_photo ? '<div class="crm-card-overlay">' . $card_photo . '</div>' : '<div class="message one">A conversation, not a broadcast.<span>01</span></div><div class="message two">The right story. The right moment.<span>02</span></div><div class="message three">Keep the connection going.<span>03</span></div>' ) );

		case 'campaign':
			$b_img = $photo ? $photo : $im(0);
			return $frame( '<div class="billboard"><div class="billboard-poster">' . $b_img . '</div><div class="billboard-leg"></div></div>' . ( $card_photo ? '<div class="campaign-card-img">' . $card_photo . '</div>' : '<div class="campaign-type" aria-hidden="true">OUT<br>THERE.</div>' ) );
	}
	return $frame( '<div class="analytics-card"><span>OBSERVE. LEARN. IMPROVE.</span><div class="analytics-graph"><svg viewBox="0 0 360 160" aria-hidden="true"><path d="M0 140 50 110 90 120 140 72 180 90 230 30 280 49 350 5" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round"/></svg></div><b>Clarity is<br>a competitive edge.</b></div><div class="analytics-disc">' . ci_star() . '</div>' );
}

function ci_service_card( $s ) {
	return '<article class="service-card" data-reveal><a href="' . esc_url( $s['url'] ) . '" class="service-card-media" data-cursor="Discover">' . ci_service_visual( $s ) . '<span class="media-arrow">' . ci_arrow() . '</span></a><div class="service-card-body"><span class="small-label">' . ci_e( $s['number'] ) . ' / ' . ci_e( mb_strtoupper( $s['group'] ) ) . '</span><h3><a href="' . esc_url( $s['url'] ) . '">' . ci_e( $s['title'] ) . '</a></h3><p>' . ci_e( $s['summary'] ) . '</p></div></article>';
}

/** Premium, fully editable card used by the Services page grid. */
function ci_service_grid_card( $s, $settings = array() ) {
	$layout       = $settings['layout'] ?? 'editorial';
	$show_summary = ( 'yes' === ( $settings['show_summary'] ?? 'yes' ) || true === ( $settings['show_summary'] ?? true ) );
	$show_tags    = ( 'yes' === ( $settings['show_tags'] ?? 'yes' ) || true === ( $settings['show_tags'] ?? true ) );
	$button       = trim( (string) ( $settings['button_label'] ?? 'Explore service' ) );

	if ( 'classic' === $layout ) {
		return ci_service_card( $s );
	}

	$group = $s['group'] ? mb_strtoupper( $s['group'] ) : 'CAPABILITY';
	$media = $s['card_image']
		? ci_img( $s['card_image'], $s['title'], 'ci360-service-card-photo' )
		: ci_service_visual( $s );
	$tags = '';
	if ( $show_tags && ! empty( $s['tags'] ) ) {
		$tags = '<span class="ci360-service-card-tags">';
		foreach ( array_slice( array_filter( (array) $s['tags'] ), 0, 3 ) as $tag ) {
			$tags .= '<span>' . ci_e( $tag ) . '</span>';
		}
		$tags .= '</span>';
	}
	$summary = $show_summary && $s['summary'] ? '<p>' . ci_e( $s['summary'] ) . '</p>' : '';
	$cta     = $button ? '<a class="ci360-service-card-link" href="' . esc_url( $s['url'] ) . '" aria-label="' . esc_attr( $button . ': ' . $s['title'] ) . '"><span class="ci360-service-card-cta">' . ci_e( $button ) . ' ' . ci_arrow() . '</span></a>' : '';

	return '<article class="ci360-service-card ci360-service-card--' . esc_attr( $layout ) . '" data-reveal>'
		. '<a class="ci360-service-card-media" href="' . esc_url( $s['url'] ) . '" data-cursor="Discover"><span class="ci360-service-card-visual">' . $media . '</span><span class="ci360-service-card-index">' . ci_e( $s['number'] ) . '</span></a>'
		. '<div class="ci360-service-card-content"><span class="ci360-service-card-kicker">' . ci_e( $group ) . '</span><h3><a href="' . esc_url( $s['url'] ) . '">' . ci_e( $s['title'] ) . '</a></h3>' . $summary . $tags . $cta . '</div>'
		. '</article>';
}

/* ------------------------------------------------------------------ Insights */

function ci_article_card( $a, $i ) {
	$fallback_imgs = array( 'studio-detail.webp', 'crave-family.webp', 'station.webp', 'crave-play.webp', 'garden.webp', 'school.webp', 'story.webp', 'ayaan.webp' );

	$img_val  = ! empty( $a['image'] ) ? $a['image'] : $fallback_imgs[ $i % count( $fallback_imgs ) ];
	$img_html = ci_img( $img_val, $a['title'] . ' editorial illustration' );

	$num_str  = ( $i + 1 ) < 10 ? '0' . ( $i + 1 ) : (string) ( $i + 1 );
	$kicker   = ! empty( $a['kicker'] ) ? $a['kicker'] : 'Article';
	$read_str = ! empty( $a['read'] ) ? ( false !== strpos( (string) $a['read'], 'read' ) ? $a['read'] : $a['read'] . ' read' ) : '';

	$cover_inner = $img_html . '<div class="article-cover-overlay"></div><span class="cover-index">' . ci_e( $kicker ) . '</span>';

	return '<article class="article-card has-featured-image" data-reveal>'
		. '<a href="' . esc_url( $a['url'] ) . '" class="article-cover tone-' . esc_attr( ! empty( $a['tone'] ) ? $a['tone'] : 'blue' ) . '" data-cursor="Read">'
		. $cover_inner
		. '</a>'
		. '<div class="article-meta"><span>' . ci_e( $kicker ) . '</span>' . ( $read_str ? '<span>' . ci_e( $read_str ) . '</span>' : '' ) . '</div>'
		. '<h3><a href="' . esc_url( $a['url'] ) . '">' . ci_e( $a['title'] ) . ' ' . ci_arrow() . '</a></h3>'
		. '</article>';
}

/* ------------------------------------------------------------------ Shared sections */

/** FAQ list from a FAQ group term (id). */
function ci_faq( $term_id, $limit = -1 ) {
	$args = array();
	if ( $term_id ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'ci_faq_group', 'terms' => (int) $term_id ) );
	}
	if ( $limit > 0 ) {
		$args['posts_per_page'] = (int) $limit;
	}
	$out = '<div class="faq-list">';
	foreach ( ci_posts( 'ci_faq', $args ) as $i => $f ) {
		$out .= '<details class="faq" data-reveal><summary><span><small>0' . ( $i + 1 ) . '</small>' . ci_e( ci_title( $f ) ) . '</span><i>+</i></summary><div class="faq-answer"><p>' . ci_e( ci_get( 'faq_answer', $f->ID ) ) . '</p></div></details>';
	}
	return $out . '</div>';
}
/** FAQ group id from a page field, falling back to a slug. */
function ci_faq_group( $field, $post_id, $fallback_slug ) {
	$id = ci_get( $field, $post_id );
	if ( is_array( $id ) ) {
		$id = reset( $id );
	}
	if ( $id instanceof WP_Term ) {
		$id = $id->term_id;
	}
	if ( ! $id ) {
		$t  = get_term_by( 'slug', $fallback_slug, 'ci_faq_group' );
		$id = $t ? $t->term_id : 0;
	}
	return (int) $id;
}

function ci_process() {
	$steps = '';
	foreach ( ci_rows( 'opt_process_steps', 'option' ) as $i => $r ) {
		$steps .= '<article class="process-step" data-reveal><div><span>0' . ( $i + 1 ) . '</span>' . ci_arrow() . '</div><h3>' . ci_e( $r['title'] ) . '</h3><p>' . ci_e( $r['text'] ) . '</p></article>';
	}
	return '<section class="process-section section"><div class="wrap"><div class="section-heading">' . ci_sec_label( 'opt_process', 'option' ) . '<h2 data-reveal>' . ci_html( ci_opt( 'opt_process_heading' ) ) . '</h2><p>' . ci_e( ci_opt( 'opt_process_intro' ) ) . '</p></div><div class="process-grid">' . $steps . '</div></div></section>';
}

function ci_initials( $name ) {
	$out = '';
	foreach ( array_slice( preg_split( '/\s+/', trim( $name ) ), 0, 2 ) as $w ) {
		$out .= mb_substr( $w, 0, 1 );
	}
	return $out;
}

function ci_testimonial() {
	$all = ci_posts( 'ci_testimonial' );
	if ( ! $all ) {
		return '';
	}
	$t = $all[0];
	return '<section class="testimonials section"><div class="wrap testimonial-grid"><div>' . ci_sec_label( 'opt_testimonials', 'option' ) . '<h2 data-reveal>' . ci_html( ci_opt( 'opt_testimonials_heading' ) ) . '</h2><div class="quote-decoration" aria-hidden="true">&ldquo;</div></div><div class="testimonial-main"><div id="quote-content" aria-live="polite"><blockquote>' . ci_e( ci_get( 'testimonial_quote', $t->ID ) ) . '</blockquote><div class="quote-person"><span class="avatar-initials">' . ci_e( ci_initials( ci_title( $t ) ) ) . '</span><div><strong>' . ci_e( ci_title( $t ) ) . '</strong><span>' . ci_e( ci_get( 'testimonial_company', $t->ID ) ) . '</span></div></div></div><div class="quote-controls"><span><b id="quote-number">01</b> / ' . ci_pad( count( $all ) ) . '</span><div><button data-quote="-1" aria-label="Previous testimonial">' . ci_arrow( 'sw' ) . '</button><button data-quote="1" aria-label="Next testimonial">' . ci_arrow() . '</button></div></div></div></div></section>';
}

function ci_compact_cta( $text = '' ) {
	if ( '' === $text ) {
		$text = ci_opt( 'opt_cta_text' );
	}
	return '<section class="compact-cta wrap" data-reveal><h2>' . ci_html( $text ) . '</h2>' . ci_btn( ci_opt( 'opt_cta_button' ), ci_opt( 'opt_cta_link' ) ) . '</section>';
}

/** Logos bundled with the theme (default content of the Client Logo Strip). */
function ci_default_logos() {
	$logos = array(
		array( 'Vardan-logo1.png', 'Vardān' ),
		array( 'eutelsat-oneweb.webp', 'Eutelsat OneWeb' ),
		array( 'Crave-Logo.jpg-1.jpeg', 'Crave' ),
		array( 'ifb-logo.png', 'IFB' ),
		array( 'times-logo.png', 'The Times of India' ),
		array( 'Shatayu-Logo-1.png', 'Shatayu' ),
		array( 'Air-canada.webp', 'Air Canada' ),
		array( 'chaitanya-school-scaled.png', 'Chaitanya School' ),
		array( 'MB-LOGO.png', 'Media Buzz' ),
		array( 'SHREE-SAVA-PANCHANMRUT-LOGO.png', 'Shree Sava Panchanmrut' ),
		array( 'isat-africa.webp', 'ISAT Africa' ),
		array( 'piv.png', 'PIV Group' ),
		array( 'samunnati-colored-logo.png', 'Samunnati' ),
		array( 'MMCF-Logo-2-scaled.png', 'MMCF' ),
		array( 'Terrainless-connectivity-Logo-scaled.png', 'Station Satcom' ),
		array( 'Gaudiya-Mission-logo.png', 'Gaudiya Mission' ),
		array( 'kish_logo.png', 'Kish' ),
		array( 'TOLVV_Monochrome-Logo_0226-03-05-scaled.png', 'TOLVV' ),
		array( 'VNA-logo-usage-2-01-scaled.png', 'VNA' ),
		array( 'TIL-LOGO.png', 'TIL' ),
		array( 'times-language-logo1.png', 'Times Language' ),
		array( 'final-logo-png.png', 'SH' ),
	);
	$out = array();
	foreach ( $logos as $l ) {
		$out[] = array( 'image' => 'brand-logos/' . $l[0], 'alt' => $l[1] );
	}
	return $out;
}

/** Client logo marquee. $logos: rows of image + alt. */
function ci_brand_strip( $label = null, $logos = null ) {
	if ( null === $label ) {
		$label = ci_opt( 'opt_clients_label' );
	}
	if ( null === $logos ) {
		$logos = ci_default_logos();
	}
	$brand_items = '';
	foreach ( $logos as $l ) {
		$src = ci_img_url( $l['image'] ?? '' );
		if ( $src ) {
			$brand_items .= '<span class="brand-logo-wrap"><img class="brand-logo-img" src="' . esc_url( $src ) . '" alt="' . esc_attr( $l['alt'] ?? '' ) . '" loading="lazy" /></span>';
		}
	}
	$sets = '';
	foreach ( array( 0, 1 ) as $n ) {
		$sets .= '<div class="brand-set" ' . ( $n ? 'aria-hidden="true"' : '' ) . '>' . $brand_items . '</div>';
	}

	return '<section class="brand-strip" aria-label="Selected client brands">' .
		'<span class="small-label">' . ci_e( $label ) . '</span>' .
		'<div class="brand-marquee"><div class="marquee-track">' . $sets . '</div></div>' .
	'</section>';
}

/** Hidden service visuals for the home capability hover preview. */
function ci_service_preview_templates( $services ) {
	$out = '';
	foreach ( $services as $s ) {
		$out .= '<template data-service-template="' . esc_attr( $s['slug'] ) . '">' . ci_service_visual( $s ) . '<p>' . ci_e( $s['headline'] ) . '</p></template>';
	}
	return $out;
}

/** Navigation items of a menu location as [url, title]. */
function ci_menu( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	$out   = array();
	foreach ( (array) $items as $item ) {
		if ( ! $item->menu_item_parent ) {
			$out[] = array( $item->url, $item->title );
		}
	}
	return $out;
}
function ci_is_current( $url ) {
	$path = trailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) );
	$home = trailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	if ( $path === $home ) {
		return false;
	}
	$req = trailingslashit( (string) wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ) );
	return 0 === strpos( $req, $path );
}

/** Returns SVG icon string for social network based on label or URL. */
function ci_social_icon_svg( $label, $url = '' ) {
	$str = strtolower( (string) $label . ' ' . (string) $url );

	if ( false !== strpos( $str, 'facebook' ) || false !== strpos( $str, 'fb.com' ) ) {
		return '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>';
	}
	if ( false !== strpos( $str, 'linkedin' ) ) {
		return '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>';
	}
	if ( false !== strpos( $str, 'instagram' ) || false !== strpos( $str, 'insta' ) ) {
		return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>';
	}
	if ( false !== strpos( $str, 'youtube' ) || false !== strpos( $str, 'youtu.be' ) ) {
		return '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="#ffffff"/></svg>';
	}
	if ( false !== strpos( $str, 'twitter' ) || false !== strpos( $str, 'x.com' ) ) {
		return '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
	}
	if ( false !== strpos( $str, 'whatsapp' ) || false !== strpos( $str, 'wa.me' ) ) {
		return '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.572-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.99c-.002 5.45-4.437 9.887-9.885 9.887"/></svg>';
	}

	return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>';
}
