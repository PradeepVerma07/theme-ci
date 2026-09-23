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
	$im    = function ( $i ) use ( $imgs ) {
		return isset( $imgs[ $i ] ) ? ci_img( $imgs[ $i ] ) : '';
	};
	$photo = ci_img( $s['image'], $s['title'] . ' visual composition', 'service-photo' );
	$frame = function ( $inside ) use ( $s, $total ) {
		return '<div class="service-visual visual-' . esc_attr( $s['visual'] ) . ' tone-' . esc_attr( $s['tone'] ) . '"><span class="visual-label">CI360 / ' . ci_e( mb_strtoupper( $s['group'] ) ) . '</span>' . $inside . '<span class="visual-corner">' . ci_e( $s['number'] ) . ' / ' . $total . '</span></div>';
	};
	switch ( $s['visual'] ) {
		case 'strategy':
			return $frame( $photo . '<div class="strategy-paper"><span>THE STARTING POINT</span><b>A better<br>question.</b>' . ci_star() . '</div><div class="pencil-line" aria-hidden="true"></div>' );
		case 'branding':
			return $frame( '<div class="brand-board"><span>THE BRAND IS<br>THE FEELING.</span><b>Aa.</b><div class="colour-swatches"><i></i><i></i><i></i><i></i></div></div><div class="brand-paper">ci360&deg;' . ci_star() . '<small>MADE TO BE REMEMBERED.</small></div>' );
		case 'website':
			return $frame( '<div class="device-browser"><div class="browser-bar"><i></i><i></i><i></i></div>' . $im( 0 ) . '</div><div class="device-phone">' . $im( 1 ) . '</div><span class="design-cross" aria-hidden="true">+</span>' );
		case 'social':
		case 'creative':
			return $frame( '<div class="social-post post-one">' . $im( 0 ) . '</div><div class="social-post post-two">' . $im( 1 ) . '</div><div class="social-heart" aria-hidden="true">&hearts;</div>' );
		case 'performance':
			$bars = '';
			foreach ( array( 35, 48, 42, 66, 54, 85, 96 ) as $h ) {
				$bars .= '<i style="--h:' . $h . '%"></i>';
			}
			return $frame( '<div class="ad-phone">' . $im( 0 ) . '</div><div class="metric-card"><span>CREATIVE + MEDIA</span><b>Make it<br>mean more.</b><div class="mini-bars" aria-hidden="true">' . $bars . '</div></div>' );
		case 'search':
			return $frame( '<div class="search-orbit" aria-hidden="true"></div><div class="search-window"><span class="search-address">A CLEARER WAY TO BE FOUND</span><div class="search-input">Your brand, discovered.<span>' . ci_arrow() . '</span></div><b>SEO. AEO. GEO.</b><span class="search-result-line"></span><span class="search-result-line short"></span><div class="search-tags">' . ci_tags( array( 'Search', 'AI answers', 'Local' ) ) . '</div></div>' );
		case 'podcast':
			$wave = '';
			for ( $i = 0; $i < 25; $i++ ) {
				$wave .= '<i style="--i:' . $i . ';--h:' . ( 20 + ( $i * 37 % 80 ) ) . '%"></i>';
			}
			return $frame( '<div class="podcast-grid" aria-hidden="true"></div><div class="mic"><div class="mic-head"></div><div class="mic-arm"></div><div class="mic-base"></div></div><div class="audio-wave" aria-hidden="true">' . $wave . '</div><span class="on-air"><i></i> STORIES, ON AIR.</span>' );
		case 'film':
			return $frame( '<div class="film-photo">' . $photo . '<div class="viewfinder" aria-hidden="true"><i></i><i></i><i></i><i></i></div><span class="rec">REC <i></i></span></div><span class="film-title">A DIFFERENT<br>POINT OF VIEW.</span>' );
		case 'crm':
			return $frame( '<div class="crm-photo">' . $photo . '</div><div class="message one">A conversation, not a broadcast.<span>01</span></div><div class="message two">The right story. The right moment.<span>02</span></div><div class="message three">Keep the connection going.<span>03</span></div>' );
		case 'campaign':
			return $frame( '<div class="billboard"><div class="billboard-poster">' . $im( 0 ) . '</div><div class="billboard-leg"></div></div><div class="campaign-type" aria-hidden="true">OUT<br>THERE.</div>' );
	}
	return $frame( '<div class="analytics-card"><span>OBSERVE. LEARN. IMPROVE.</span><div class="analytics-graph"><svg viewBox="0 0 360 160" aria-hidden="true"><path d="M0 140 50 110 90 120 140 72 180 90 230 30 280 49 350 5" fill="none" stroke="currentColor" stroke-width="6" stroke-linecap="round"/></svg></div><b>Clarity is<br>a competitive edge.</b></div><div class="analytics-disc">' . ci_star() . '</div>' );
}

function ci_service_card( $s ) {
	return '<article class="service-card" data-reveal><a href="' . esc_url( $s['url'] ) . '" class="service-card-media" data-cursor="Discover">' . ci_service_visual( $s ) . '<span class="media-arrow">' . ci_arrow() . '</span></a><div class="service-card-body"><span class="small-label">' . ci_e( $s['number'] ) . ' / ' . ci_e( mb_strtoupper( $s['group'] ) ) . '</span><h3><a href="' . esc_url( $s['url'] ) . '">' . ci_e( $s['title'] ) . '</a></h3><p>' . ci_e( $s['summary'] ) . '</p></div></article>';
}

/* ------------------------------------------------------------------ Insights */

function ci_article_card( $a, $i ) {
	return '<article class="article-card" data-reveal><a href="' . esc_url( $a['url'] ) . '" class="article-cover tone-' . esc_attr( $a['tone'] ) . '" data-cursor="Read">' . ci_img( $a['image'], $a['title'] . ' editorial illustration' ) . '<div class="article-cover-overlay"></div><span class="cover-index">NOTES / 0' . ( $i + 1 ) . '</span><span class="cover-title">' . ci_e( $a['title'] ) . '</span>' . ci_star() . '</a><div class="article-meta"><span>' . ci_e( $a['kicker'] ) . '</span><span>' . ci_e( $a['read'] ) . ' read</span></div><h3><a href="' . esc_url( $a['url'] ) . '">' . ci_e( $a['title'] ) . ' ' . ci_arrow() . '</a></h3></article>';
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

function ci_brand_strip() {
	$names = wp_list_pluck( ci_rows( 'opt_clients', 'option' ), 'name' );
	$sets  = '';
	foreach ( array( 0, 1 ) as $n ) {
		$sets .= '<div class="brand-set" ' . ( $n ? 'aria-hidden="true"' : '' ) . '>';
		foreach ( $names as $i => $t ) {
			$sets .= '<span class="client-name client-' . ( $i % 8 ) . '">' . ci_e( $t ) . '</span>';
		}
		$sets .= '</div>';
	}
	return '<section class="brand-strip" aria-label="Selected client brands"><span class="small-label">' . ci_e( ci_opt( 'opt_clients_label' ) ) . '</span><div class="brand-marquee"><div class="marquee-track">' . $sets . '</div></div></section>';
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
