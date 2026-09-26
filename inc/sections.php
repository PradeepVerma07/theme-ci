<?php
/**
 * Section renderers. Each ci_s_{id}( $s ) returns the HTML of one CI360 section.
 * $s holds the section settings (from an Elementor widget, or the defaults in
 * inc/sections-config.php). The markup is identical to the CI360 demo theme.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Default settings of a section (keys => default values). */
function ci_section_defaults( $id ) {
	$defs = ci_section_defs();
	$out  = array();
	foreach ( $defs[ $id ]['controls'] ?? array() as $c ) {
		if ( ! empty( $c['key'] ) ) {
			$out[ $c['key'] ] = $c['default'] ?? '';
		}
	}
	return $out;
}

/** Render a section by id with optional overrides (used by PHP templates and widgets). */
function ci_section( $id, $settings = array() ) {
	$fn = 'ci_s_' . str_replace( '-', '_', $id );
	if ( ! function_exists( $fn ) ) {
		return '';
	}
	$s = array_merge( ci_section_defaults( $id ), (array) $settings );
	return (string) call_user_func( $fn, $s );
}

/** id="…" attribute from an anchor setting. */
function ci_s_id( $s ) {
	return ! empty( $s['anchor'] ) ? ' id="' . esc_attr( $s['anchor'] ) . '"' : '';
}
/** Replace {services} {projects} {insights} {team} {posts} with live counts. */
function ci_s_tokens( $text ) {
	$map = array(
		'{services}' => ci_count( 'ci_service' ),
		'{projects}' => ci_count( 'ci_project' ),
		'{insights}' => ci_count( 'ci_insight' ),
		'{team}'     => ci_count( 'ci_team' ),
		'{posts}'    => (int) wp_count_posts( 'post' )->publish,
	);
	return strtr( (string) $text, array_map( 'strval', $map ) );
}
/** Section label from settings. */
function ci_s_label( $s ) {
	return ci_label( $s['label_1'] ?? '', $s['label_2'] ?? '' );
}
/** Post id from a slug or id setting. */
function ci_s_post( $v, $type ) {
	if ( is_numeric( $v ) ) {
		return (int) $v;
	}
	if ( ! $v ) {
		return 0;
	}
	$p = get_page_by_path( (string) $v, OBJECT, $type );
	return $p ? (int) $p->ID : 0;
}
function ci_s_rows( $v ) {
	return is_array( $v ) ? array_values( $v ) : array();
}
function ci_s_page_url( $key, $fallback ) {
	$id = ci_page_id( $key );
	return $id ? get_permalink( $id ) : ci_url( $fallback );
}

/* ================================================================ HOME */

function ci_s_home_hero( $s ) {
	$positions = array( array( 'collage-left', '-12' ), array( 'collage-main', '8' ), array( 'collage-right', '17' ) );
	$collage   = '';
	foreach ( array_slice( ci_s_rows( $s['collage'] ), 0, 3 ) as $i => $row ) {
		$pid      = ci_s_post( $row['project'] ?? '', 'ci_project' );
		$collage .= '<a href="' . esc_url( $pid ? get_permalink( $pid ) : ci_s_page_url( 'work', '/work/' ) ) . '" class="collage-frame ' . $positions[ $i ][0] . '" data-depth="' . $positions[ $i ][1] . '">' . ci_img( $row['image'] ?? '', 'Explore ' . ( $pid ? ci_title( $pid ) : '' ) . ' creative', '', true ) . '<span>' . ci_e( $row['caption'] ?? '' ) . '</span></a>';
	}
	return '<section class="hero wrap"><div class="hero-copy"><div class="hero-eyebrow"><i></i> ' . ci_e( $s['eyebrow'] ) . '</div><h1 class="hero-title"><span class="line">' . ci_e( $s['title_1'] ) . '</span><span class="line">' . ci_e( $s['title_2'] ) . '</span><span class="line accent">' . ci_e( $s['title_3'] ) . '<svg viewBox="0 0 540 35" aria-hidden="true"><path d="M5 25C125 2 319 0 533 15M35 33C159 12 340 8 490 17"/></svg></span></h1><p class="hero-description">' . ci_e( $s['description'] ) . '</p><div class="hero-actions">' . ci_btn( $s['cta'], $s['cta_link'] ) . '<a href="' . esc_attr( $s['secondary_link'] ) . '" class="text-link">' . ci_e( $s['secondary'] ) . ' ' . ci_arrow( 'se' ) . '</a></div></div><div class="hero-collage" aria-label="Selected CI360 campaign imagery"><div class="collage-coordinate">' . ci_e( $s['collage_label'] ) . '</div><div class="collage-ring" aria-hidden="true"></div>' . $collage . '<div class="hero-sticker" aria-hidden="true">' . ci_star() . '<b>' . ci_html( $s['sticker'] ) . '</b></div><div class="collage-caption"><span>' . ci_e( $s['collage_caption'] ) . '</span><span>' . ci_e( $s['collage_badge'] ) . '</span></div></div><div class="hero-bottom"><span>' . ci_e( $s['bottom_left'] ) . '</span><a href="' . esc_attr( $s['secondary_link'] ) . '">' . ci_e( $s['bottom_right'] ) . ' <span>&darr;</span></a></div></section>';
}

function ci_s_brand_strip( $s ) {
	$logos = ci_s_rows( $s['logos'] );
	return ci_brand_strip( $s['label'], $logos ? $logos : ci_default_logos() );
}

function ci_s_home_work( $s ) {
	$ids = array_filter( array_map( function ( $v ) {
		return ci_s_post( $v, 'ci_project' );
	}, (array) $s['projects'] ) );
	if ( ! $ids ) {
		$ids = ci_featured_projects();
	}
	$stack = '';
	foreach ( array_values( $ids ) as $i => $pid ) {
		$p      = ci_project( $pid );
		$stack .= '<article class="showcase tone-' . esc_attr( $p['tone'] ) . '" data-stack><a class="showcase-media" href="' . esc_url( $p['url'] ) . '" data-cursor="Explore">' . ci_case_visual( $p ) . '</a><div class="showcase-copy"><span class="small-label">0' . ( $i + 1 ) . ' / ' . ci_e( mb_strtoupper( $p['category'] ) ) . '</span><h3>' . ci_e( $p['name'] ) . '</h3><p class="showcase-headline">' . ci_e( $p['headline'] ) . '</p><p>' . ci_e( $p['summary'] ) . '</p><a class="text-link" href="' . esc_url( $p['url'] ) . '">' . ci_e( $s['card_link'] ) . ' ' . ci_arrow() . '</a></div></article>';
	}
	$anchor = $s['anchor'] ? ' id="' . esc_attr( $s['anchor'] ) . '"' : '';
	return '<section class="work-section section"' . $anchor . '><div class="wrap"><div class="section-heading heading-row">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><p>' . ci_e( $s['intro'] ) . '</p></div><div class="work-stack">' . $stack . '</div><div class="work-end"><span>' . ci_e( $s['end_text'] ) . '</span>' . ci_btn( sprintf( $s['button'], ci_count( 'ci_project' ) ), ci_s_link( $s['button_link'], 'work' ), 'outline' ) . '</div></div></section>';
}

/** Links to core pages follow the page if its slug changes. */
function ci_s_link( $url, $page_key ) {
	$default = '/' . $page_key . '/';
	if ( $url === $default || '' === $url ) {
		return ci_s_page_url( $page_key, $default );
	}
	return $url;
}

function ci_s_home_capabilities( $s ) {
	$services     = array_map( 'ci_service', ci_ids( 'ci_service' ) );
	$cap_services = array_slice( $services, 0, max( 1, (int) $s['count'] ) );
	$rows         = '';
	foreach ( $cap_services as $sv ) {
		$rows .= '<a class="capability-row" href="' . esc_url( $sv['url'] ) . '" data-service-preview="' . esc_attr( $sv['slug'] ) . '"><span>' . ci_e( $sv['number'] ) . '</span><div><h3>' . ci_e( $sv['title'] ) . '</h3><p>' . ci_e( implode( ' / ', array_slice( $sv['tags'], 0, 3 ) ) ) . '</p></div>' . ci_arrow() . '</a>';
	}
	return '<section class="capabilities section dark-section"><div class="wrap"><div class="section-heading heading-row">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><p>' . ci_e( $s['intro'] ) . '</p></div><div class="capability-layout"><div class="capability-list">' . $rows . '<a class="all-services" href="' . esc_url( ci_s_link( $s['all_link'], 'services' ) ) . '">' . ci_e( sprintf( $s['all_text'], count( $services ) ) ) . ' ' . ci_arrow() . '</a></div><div class="capability-preview" aria-hidden="true">' . ( $services ? ci_service_visual( $services[0] ) : '' ) . '<p>' . ci_e( $s['preview'] ) . '</p></div></div></div>' . ci_service_preview_templates( $cap_services ) . '</section>';
}

function ci_s_home_about( $s ) {
	return '<section class="about-teaser section wrap"><div class="studio-photo" data-reveal>' . ci_img( $s['image'] ) . '<span class="photo-stamp">' . ci_html( $s['stamp'] ) . '</span></div><div class="about-teaser-copy">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2>' . ci_paragraphs( $s['text'] ) . ci_btn( $s['button'], ci_s_link( $s['button_link'], 'about' ), 'outline' ) . '</div></section>';
}

function ci_s_word_marquee( $s ) {
	$words = array_filter( array_map( 'trim', explode( '|', (string) $s['words'] ) ) );
	$line  = '';
	foreach ( $words as $w ) {
		$line .= ci_e( $w ) . ' ' . ci_star() . ' ';
	}
	$line = rtrim( $line );
	return '<div class="word-marquee" aria-hidden="true"><div class="marquee-track"><span>' . $line . '</span><span>' . $line . '</span></div></div>';
}

function ci_s_process( $s ) {
	$steps = '';
	foreach ( ci_s_rows( $s['steps'] ) as $i => $r ) {
		$steps .= '<article class="process-step" data-reveal><div><span>0' . ( $i + 1 ) . '</span>' . ci_arrow() . '</div><h3>' . ci_e( $r['title'] ?? '' ) . '</h3><p>' . ci_e( $r['text'] ?? '' ) . '</p></article>';
	}
	return '<section class="process-section section"><div class="wrap"><div class="section-heading">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><p>' . ci_e( $s['intro'] ) . '</p></div><div class="process-grid">' . $steps . '</div></div></section>';
}

function ci_s_testimonials( $s ) {
	$all = ci_posts( 'ci_testimonial' );
	if ( (int) $s['count'] > 0 ) {
		$all = array_slice( $all, 0, (int) $s['count'] );
	}
	if ( ! $all ) {
		return '';
	}
	$data = array();
	foreach ( $all as $p ) {
		$data[] = array( 'name' => ci_title( $p ), 'company' => (string) ci_get( 'testimonial_company', $p->ID ), 'quote' => (string) ci_get( 'testimonial_quote', $p->ID ) );
	}
	$t = $data[0];
	return '<section class="testimonials section" data-quotes="' . esc_attr( wp_json_encode( $data ) ) . '"><div class="wrap testimonial-grid"><div>' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><div class="quote-decoration" aria-hidden="true">&ldquo;</div></div><div class="testimonial-main"><div id="quote-content" aria-live="polite"><blockquote>' . ci_e( $t['quote'] ) . '</blockquote><div class="quote-person"><span class="avatar-initials">' . ci_e( ci_initials( $t['name'] ) ) . '</span><div><strong>' . ci_e( $t['name'] ) . '</strong><span>' . ci_e( $t['company'] ) . '</span></div></div></div><div class="quote-controls"><span><b id="quote-number">01</b> / ' . ci_pad( count( $data ) ) . '</span><div><button data-quote="-1" aria-label="Previous testimonial">' . ci_arrow( 'sw' ) . '</button><button data-quote="1" aria-label="Next testimonial">' . ci_arrow() . '</button></div></div></div></div></section>';
}

function ci_s_insights_teaser( $s ) {
	$cards = '';
	foreach ( array_slice( ci_ids( 'ci_insight' ), 0, max( 1, (int) $s['count'] ) ) as $i => $aid ) {
		$cards .= ci_article_card( ci_insight( $aid ), $i );
	}
	return '<section class="insights-section section wrap"><div class="section-heading heading-row">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><p>' . ci_e( $s['intro'] ) . '</p></div><div class="articles-grid">' . $cards . '</div><div class="align-right">' . ci_btn( $s['button'], ci_s_link( $s['button_link'], 'insights' ), 'outline' ) . '</div></section>';
}

function ci_s_faq( $s ) {
	$term  = get_term_by( 'slug', (string) $s['group'], 'ci_faq_group' );
	$intro = '' !== trim( (string) $s['intro'] ) ? '<p>' . ci_e( $s['intro'] ) . '</p>' : '';
	$reveal = ( 'yes' === $s['reveal'] || true === $s['reveal'] ) ? ' data-reveal' : '';
	return '<section class="faq-section section wrap"><div>' . ci_s_label( $s ) . '<h2' . $reveal . '>' . ci_html( $s['heading'] ) . '</h2>' . $intro . '</div>' . ci_faq( $term ? $term->term_id : 0, (int) $s['limit'] > 0 ? (int) $s['limit'] : -1 ) . '</section>';
}

function ci_s_compact_cta( $s ) {
	return '<section class="compact-cta wrap" data-reveal><h2>' . ci_html( $s['text'] ) . '</h2>' . ci_btn( $s['button'], ci_s_link( $s['button_link'], 'contact' ) ) . '</section>';
}

/* ================================================================ ABOUT & GENERIC */

function ci_s_page_hero( $s ) {
	$image = '';
	if ( 'yes' === $s['show_image'] || true === $s['show_image'] ) {
		$image = '<div class="about-wide-image" data-reveal>' . ci_img( $s['image'], null, '', true ) . '<span class="image-note">' . ci_e( $s['image_note'] ) . '</span><div class="wide-image-type" aria-hidden="true">' . ci_html( $s['image_type'] ) . '</div></div>';
	}
	return '<section class="page-hero wrap">' . ci_breadcrumb( $s['crumb'] ) . ci_s_label( $s ) . '<h1 class="display" data-title>' . ci_html( $s['heading'] ) . '</h1><div class="hero-intro-row"><p>' . ci_e( $s['intro_short'] ) . '</p><p>' . ci_e( $s['intro'] ) . '</p></div>' . $image . '</section>';
}

function ci_s_about_compass( $s ) {
	$values = '';
	foreach ( ci_s_rows( $s['values'] ) as $r ) {
		$values .= '<li>' . ci_e( $r['value'] ?? '' ) . '</li>';
	}
	return '<section class="section wrap"><div class="section-heading">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2></div><div class="purpose-grid"><article class="tone-orange" data-reveal>' . ci_star() . '<span class="small-label">01 / VISION</span><h3>' . ci_e( $s['vision_title'] ) . '</h3><p>' . ci_e( $s['vision'] ) . '</p></article><article class="tone-lilac" data-reveal>' . ci_arrow() . '<span class="small-label">02 / MISSION</span><h3>' . ci_e( $s['mission_title'] ) . '</h3><p>' . ci_e( $s['mission'] ) . '</p></article><article class="tone-mint" data-reveal><div class="values-symbol" aria-hidden="true">+</div><span class="small-label">03 / VALUES</span><h3>' . ci_e( $s['values_title'] ) . '</h3><ul>' . $values . '</ul></article></div></section>';
}

function ci_s_about_belief( $s ) {
	return '<section class="belief-section dark-section section"><div class="wrap belief-grid"><div>' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2>' . ci_btn( $s['button'], ci_s_link( $s['button_link'], 'founders' ), 'light' ) . '</div><div>' . ci_paragraphs( $s['text'], 'large-copy' ) . '</div></div></section>';
}

function ci_s_about_journey( $s ) {
	$timeline = ci_s_rows( $s['timeline'] );
	$years    = array_map( function ( $r ) { return strtoupper( trim( (string) ( $r['year'] ?? '' ) ) ); }, $timeline );

	/* Older CI360 installs stored the previous six-step timeline in Elementor.
	 * Upgrade that untouched legacy data to the new visual journey automatically. */
	if ( count( $timeline ) < 8 && ( ! in_array( '2018', $years, true ) || ! in_array( '2024', $years, true ) ) ) {
		$defaults = ci_section_defaults( 'about-journey' );
		$timeline = ci_s_rows( $defaults['timeline'] ?? array() );
	}

	$heading = (string) $s['heading'];
	if ( 'Always in<br><em>forward motion.</em>' === $heading ) {
		$heading = 'Built one <em>meaningful</em><br>business problem at a time.';
	}

	$data  = array();
	$steps = '';
	foreach ( $timeline as $i => $r ) {
		$year = trim( (string) ( $r['year'] ?? '' ) );
		$tags = array_filter( array_map( 'trim', explode( '|', (string) ( $r['tags'] ?? '' ) ) ) );
		$image = ci_img_url( $r['image'] ?? '' );
		$glows = array(
			'rgba(59,130,246,0.22), rgba(6,182,212,0.08)',
			'rgba(59,130,246,0.22), rgba(99,102,241,0.08)',
			'rgba(99,102,241,0.22), rgba(59,130,246,0.08)',
			'rgba(251,191,36,0.16), rgba(239,68,68,0.07)',
			'rgba(16,185,129,0.16), rgba(6,182,212,0.08)',
			'rgba(6,182,212,0.22), rgba(59,130,246,0.08)',
			'rgba(16,185,129,0.20), rgba(99,102,241,0.08)',
			'rgba(6,182,212,0.20), rgba(37,99,235,0.10)',
			'rgba(99,102,241,0.22), rgba(6,182,212,0.10)',
		);
		$data[] = array(
			'year'  => $year,
			'title' => wp_kses( (string) ( $r['title'] ?? '' ), array( 'br' => array() ) ),
			'desc'  => wp_strip_all_tags( (string) ( $r['text'] ?? '' ) ),
			'tags'  => array_values( $tags ),
			'img'   => $image,
			'glow'  => $glows[ $i % count( $glows ) ],
		);
		$dot = 'NOW' === strtoupper( $year ) ? '<span class="ci360-tl-flame">' . ci_star() . '</span>' : ci_e( strlen( $year ) >= 2 ? substr( $year, -2 ) : $year );
		$steps .= '<button type="button" class="ci360-tl-step' . ( 0 === $i ? ' active' : '' ) . '" data-ci360-tl-step="' . (int) $i . '" aria-pressed="' . ( 0 === $i ? 'true' : 'false' ) . '"><span class="ci360-tl-dot">' . $dot . '</span><span class="ci360-tl-year-label">' . ci_e( $year ) . '</span></button>';
	}

	if ( ! $data ) {
		return '';
	}

	$first = $data[0];
	$tags  = '';
	foreach ( $first['tags'] as $tag ) {
		$tags .= '<span class="ci360-tl-tag">' . ci_e( $tag ) . '</span>';
	}
	$image = $first['img'] ? '<img class="ci360-tl-image" src="' . esc_url( $first['img'] ) . '" alt="CI360 ' . esc_attr( $first['year'] ) . '" loading="eager" decoding="async" fetchpriority="high">' : '<span class="ci360-tl-image-fallback"></span>';

	$card = '<div class="ci360-tl-card-grid"><div class="ci360-tl-card-left"><div><span class="ci360-tl-year-big">' . ci_e( $first['year'] ) . '</span><h3 class="ci360-tl-card-title">' . wp_kses( $first['title'], array( 'br' => array() ) ) . '</h3></div><p class="ci360-tl-card-desc">' . ci_e( $first['desc'] ) . '</p><div class="ci360-tl-tags">' . $tags . '</div><div class="ci360-tl-nav"><button type="button" class="ci360-tl-nav-btn prev" data-ci360-tl-prev disabled>← <span>Previous</span></button><button type="button" class="ci360-tl-nav-btn next" data-ci360-tl-next><span>Next</span> →</button></div></div><div class="ci360-tl-card-right">' . $image . '<span class="ci360-tl-img-fade" aria-hidden="true"></span><span class="ci360-tl-img-vignette" aria-hidden="true"></span><span class="ci360-tl-progress" aria-hidden="true"></span></div></div>';

	$style_vars = '';
	if ( ! empty( $s['heading_color'] ) ) {
		$style_vars .= '--ci-tl-heading-color:' . esc_attr( $s['heading_color'] ) . ';';
	}
	if ( ! empty( $s['accent_color'] ) ) {
		$style_vars .= '--ci-tl-accent-color:' . esc_attr( $s['accent_color'] ) . ';';
	}
	if ( ! empty( $s['card_title_color'] ) ) {
		$style_vars .= '--ci-tl-card-title-color:' . esc_attr( $s['card_title_color'] ) . ';';
	}
	if ( ! empty( $s['font_weight'] ) ) {
		$style_vars .= '--ci-tl-font-weight:' . esc_attr( $s['font_weight'] ) . ';';
	}
	$style_attr = $style_vars ? ' style="' . $style_vars . '"' : '';

	$h_styles = array();
	if ( ! empty( $s['heading_color'] ) ) {
		$h_styles[] = 'color:' . esc_attr( $s['heading_color'] );
	}
	if ( ! empty( $s['font_weight'] ) ) {
		$h_styles[] = 'font-weight:' . esc_attr( $s['font_weight'] );
	}
	$h_style_attr = $h_styles ? ' style="' . implode( ';', $h_styles ) . '"' : '';

	$em_styles = array();
	if ( ! empty( $s['accent_color'] ) ) {
		$em_styles[] = 'color:' . esc_attr( $s['accent_color'] );
		$em_styles[] = '-webkit-text-fill-color:' . esc_attr( $s['accent_color'] );
		$em_styles[] = 'background:none';
	}
	if ( ! empty( $s['font_weight'] ) ) {
		$em_styles[] = 'font-weight:' . esc_attr( $s['font_weight'] );
	}
	$em_style_attr = $em_styles ? ' style="' . implode( ';', $em_styles ) . '"' : '';

	$heading_html = ci_html( $heading );
	if ( $em_style_attr ) {
		$heading_html = str_replace( array( '<em>', '<span>' ), array( '<em' . $em_style_attr . '>', '<span' . $em_style_attr . '>' ), $heading_html );
	}

	return '<section id="ci360-timeline-section"' . $style_attr . ' data-ci360-timeline="' . esc_attr( wp_json_encode( $data ) ) . '"><div class="ci360-tl-inner"><div class="ci360-tl-heading"><h2' . $h_style_attr . '>' . $heading_html . '</h2></div><div class="ci360-tl-track-wrap"><span class="ci360-tl-track-rail" aria-hidden="true"></span><span class="ci360-tl-track-fill" data-ci360-tl-fill aria-hidden="true"></span><div class="ci360-tl-steps" data-ci360-tl-steps>' . $steps . '</div></div><div class="ci360-tl-card"><span class="ci360-tl-card-glow" data-ci360-tl-glow style="background:radial-gradient(ellipse,' . esc_attr( $first['glow'] ) . ')" aria-hidden="true"></span><div class="ci360-tl-card-inner" data-ci360-tl-card>' . $card . '</div></div></div></section>';
}

/** Photos bundled with the theme, matched by name when a Team member has no photo. */
function ci_team_fallback_photos() {
	return array(
		'Pramit Ghosh' => '122A0148.webp', 'Aashit Shah' => 'Aashit-.jpg', 'Urna Banerji' => 'Urna.jpeg', 'Bhumi Chabbra' => 'Bn.png',
		'John Seaman' => 'John-Seaman.jpg', 'Mansi Bagdai' => 'Mansi.jpg', 'Manan Dhingra' => 'Manan-Dhingra.jpg', 'Aneri Shah' => 'AS.jpeg',
		'Maryanne deSousa' => 'MD.jpeg', 'Aarya Parsodkar' => 'Aarya.jpg', 'Pratik Hemani' => 'pratik.jpeg', 'Meshwa Kadia' => 'mmk.jpeg',
		'Ajay Shankar' => 'as-e1785923665718.jpeg', 'Pradeep Verma' => '65794823924813429381189938331.jpg', 'Harshada' => 'H.jpg',
		'Aadhya Bhidodiya' => 'AB-rotated.jpeg', 'Arushi Singh' => 'Arushi_SM.jpg',
	);
}

function ci_s_about_team( $s ) {
	$members = ci_posts( 'ci_team' );
	if ( (int) $s['count'] > 0 ) {
		$members = array_slice( $members, 0, (int) $s['count'] );
	}
	$photos = ci_team_fallback_photos();
	$team   = '';
	foreach ( $members as $i => $m ) {
		$name   = ci_title( $m );
		$img_id = (int) ci_get( 'team_photo', $m->ID );
		if ( ! $img_id ) {
			$img_id = (int) ci_get( 'founder_portrait', $m->ID );
		}
		if ( ! $img_id ) {
			$img_id = (int) get_post_thumbnail_id( $m->ID );
		}
		$src = $img_id ? wp_get_attachment_image_url( $img_id, 'medium_large' ) : '';
		if ( ! $src ) {
			foreach ( $photos as $pn => $file ) {
				if ( 0 === strcasecmp( $pn, $name ) || 0 === strcasecmp( strtok( $pn, ' ' ), strtok( $name, ' ' ) ) ) {
					$src = CI360_URI . '/assets/images/team/' . $file;
					break;
				}
			}
		}
		$photo = $src ? '<img class="team-member-photo" src="' . esc_url( $src ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" />' : '<div class="team-photo-fallback"><span>' . esc_html( mb_substr( $name, 0, 2 ) ) . '</span></div>';
		$team .= '<div class="team-member-card" data-reveal><div class="team-member-photo-wrap">' . $photo . '</div><div class="team-member-info"><span class="team-index">' . ci_pad( $i + 1 ) . '</span><h3>' . ci_e( $name ) . '</h3><p>' . ci_e( ci_get( 'team_role', $m->ID ) ) . '</p></div></div>';
	}
	$anchor = $s['anchor'] ? ' id="' . esc_attr( $s['anchor'] ) . '"' : '';
	return '<section class="team-section section wrap"' . $anchor . '><div class="section-heading heading-row">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><p>' . ci_e( $s['intro'] ) . '</p></div><div class="team-roster">' . $team . '</div></section>';
}

function ci_s_about_industries( $s ) {
	$out = '';
	foreach ( ci_s_rows( $s['industries'] ) as $i => $r ) {
		$out .= '<span data-reveal>' . ci_pad( $i + 1 ) . ' ' . ci_e( $r['name'] ?? '' ) . '</span>';
	}
	return '<section class="section industry-section wrap"><div class="section-heading">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2></div><div class="industry-pills">' . $out . '</div></section>';
}

/* ================================================================ FOUNDERS */

function ci_s_founders_profiles( $s ) {
	$profiles = '';
	foreach ( ci_posts( 'ci_team', array( 'meta_key' => 'team_is_founder', 'meta_value' => '1' ) ) as $i => $f ) {
		$fid      = $f->ID;
		$name     = ci_title( $f );
		$role     = ci_get( 'founder_role', $fid );
		$portrait = (int) ci_get( 'team_photo', $fid );
		if ( ! $portrait ) {
			$portrait = (int) ci_get( 'founder_portrait', $fid );
		}
		if ( ! $portrait ) {
			$portrait = (int) get_post_thumbnail_id( $fid );
		}
		$profiles .= '<article class="founder-profile" data-reveal><div class="founder-photo tone-' . esc_attr( ci_get( 'founder_tone', $fid ) ) . '"><div class="founder-fallback">' . ci_img( $s['fallback_image'], 'Creative studio environment' ) . '<div><span>' . ci_e( ci_get( 'founder_initials', $fid ) ) . '</span><p>' . ci_e( ci_get( 'founder_lens', $fid ) ) . '</p></div></div>'
			. ( $portrait ? '<img class="founder-portrait" src="' . esc_url( wp_get_attachment_image_url( $portrait, 'full' ) ) . '" alt="' . esc_attr( $name ) . ' - portrait" loading="lazy" data-external-portrait>' : '' )
			. '<span class="founder-photo-label">0' . ( $i + 1 ) . ' / ' . ci_e( mb_strtoupper( $role ) ) . '</span></div><div class="founder-body"><span class="small-label">' . ci_e( $role ) . '</span><h2>' . ci_e( $name ) . '</h2><blockquote>' . ci_e( ci_get( 'founder_quote', $fid ) ) . '</blockquote></div></article>';
	}
	return '<section class="founder-duo wrap">' . $profiles . '</section>';
}

function ci_s_founders_belief( $s ) {
	return '<section class="section dark-section"><div class="wrap shared-belief"><div>' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><div class="shared-symbol" aria-hidden="true">' . ci_star() . ' + ' . ci_star() . '</div></div><div>' . ci_paragraphs( $s['text'] ) . '</div></div></section>';
}

function ci_s_founders_studio( $s ) {
	return '<section class="section wrap founder-studio"><div class="studio-photo" data-reveal>' . ci_img( $s['image'] ) . '</div><div><h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><p>' . ci_e( $s['text'] ) . '</p>' . ci_btn( $s['button'], $s['button_link'], 'outline' ) . '</div></section>';
}

/* ================================================================ SERVICES */

function ci_s_services_hero( $s ) {
	$services = ci_ids( 'ci_service' );
	$hid      = ci_s_post( $s['service'], 'ci_service' );
	$hero     = $hid ? ci_service( $hid ) : ( isset( $services[2] ) ? ci_service( $services[2] ) : ( $services ? ci_service( $services[0] ) : null ) );

	$style = in_array( $s['hero_style'] ?? 'editorial', array( 'editorial', 'panel', 'minimal' ), true ) ? $s['hero_style'] : 'editorial';
	$show_visual = ( 'yes' === ( $s['show_visual'] ?? 'yes' ) || true === ( $s['show_visual'] ?? true ) ) && $hero;
	$show_badge  = ( 'yes' === ( $s['show_badge'] ?? 'yes' ) || true === ( $s['show_badge'] ?? true ) );
	$bg          = ci_img_url( $s['hero_background'] ?? '' );
	$attr        = $bg ? ' style="' . esc_attr( '--ci-services-hero-bg:url("' . $bg . '")' ) . '"' : '';
	$classes     = 'page-hero services-hero services-hero--' . $style . ( $bg ? ' has-custom-background' : '' ) . ( $show_visual ? '' : ' no-visual' );

	$visual = '';
	if ( $show_visual ) {
		$badge = $show_badge ? '<div class="hero-art-badge">' . count( $services ) . '<br><small>' . ci_html( $s['badge'] ) . '</small></div>' : '';
		$visual = '<div class="services-hero-art">' . ci_service_visual( $hero ) . $badge . '</div>';
	}

	return '<section class="' . esc_attr( $classes ) . '"' . $attr . '><div class="wrap services-hero-shell">' . ci_breadcrumb( $s['crumb'] ) . '<div class="services-hero-grid"><div class="services-hero-copy">' . ci_s_label( $s ) . '<h1 class="display" data-title>' . ci_html( $s['heading'] ) . '</h1><p>' . ci_e( $s['intro'] ) . '</p>' . ci_btn( $s['button'], ci_s_link( $s['button_link'], 'contact' ) ) . '</div>' . $visual . '</div></div></section>';
}

function ci_s_services_grid( $s ) {
	$args = array();
	if ( $s['group'] ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'ci_service_group', 'field' => 'slug', 'terms' => $s['group'] ) );
	}
	$ids = $args ? wp_list_pluck( ci_posts( 'ci_service', $args ), 'ID' ) : ci_ids( 'ci_service' );
	if ( (int) $s['count'] > 0 ) {
		$ids = array_slice( $ids, 0, (int) $s['count'] );
	}

	$layout = in_array( $s['layout'] ?? 'editorial', array( 'editorial', 'classic', 'compact' ), true ) ? $s['layout'] : 'editorial';
	$cols   = '3' === (string) ( $s['columns'] ?? '2' ) ? '3' : '2';
	$cards  = '';
	foreach ( $ids as $id ) {
		$service = ci_service( $id );
		if ( $service ) {
			$cards .= ci_service_grid_card( $service, $s );
		}
	}

	$heading = '';
	if ( 'yes' === ( $s['show_heading'] ?? '' ) || true === ( $s['show_heading'] ?? false ) ) {
		$heading = '<div class="ci360-services-heading">' . ci_s_label( $s ) . '<div class="ci360-services-heading-grid"><h2>' . ci_html( $s['heading'] ) . '</h2><p>' . ci_e( $s['intro'] ) . '</p></div></div>';
	}

	$grid_class = 'classic' === $layout ? 'services-grid' : 'ci360-services-grid';
	return '<section class="ci360-services-section ci360-services-section--' . esc_attr( $layout ) . ' section wrap"' . ci_s_id( $s ) . '>' . $heading . '<div class="' . esc_attr( $grid_class ) . ' layout-' . esc_attr( $layout ) . ' cols-' . esc_attr( $cols ) . '">' . $cards . '</div></section>';
}

/* ================================================================ WORK */

function ci_s_work_hero( $s ) {
	$total = ci_count( 'ci_project' );
	return '<section class="page-hero wrap work-hero">' . ci_breadcrumb( $s['crumb'] ) . ci_s_label( $s ) . '<div class="work-hero-head"><h1 class="display" data-title>' . ci_html( $s['heading'] ) . '</h1><div class="work-hero-circle">' . (int) $total . '<span>' . ci_html( $s['circle'] ) . '</span>' . ci_star() . '</div></div><div class="hero-intro-row"><p>' . ci_e( $s['intro_short'] ) . '</p><p>' . ci_e( $s['intro'] ) . '</p></div></section>';
}

function ci_s_work_portfolio( $s ) {
	$featured = ci_featured_projects();
	$ordered  = array_merge( $featured, array_diff( ci_ids( 'ci_project' ), $featured ) );
	$total    = count( $ordered );
	$cards    = '';
	foreach ( $ordered as $pid ) {
		$cards .= ci_project_card( ci_project( $pid ) );
	}
	$terms = get_terms( array( 'taxonomy' => 'ci_project_category', 'hide_empty' => false ) );
	$pills = array();
	foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
		if ( ci_term_get( 'cat_in_filter', $t ) ) {
			$pills[ (int) ci_term_get( 'cat_filter_order', $t ) * 1000 + count( $pills ) ] = $t->name;
		}
	}
	ksort( $pills );
	$buttons = '<button data-filter="All" aria-pressed="true">' . ci_e( $s['all'] ) . ' <small>' . $total . '</small></button>';
	foreach ( $pills as $name ) {
		$buttons .= '<button data-filter="' . esc_attr( $name ) . '" aria-pressed="false">' . ci_e( $name ) . '</button>';
	}
	$space = ( 'yes' === ( $s['space_top'] ?? 'yes' ) || true === ( $s['space_top'] ?? true ) ) ? ' has-top-space' : '';
	return '<section class="wrap portfolio-section' . $space . '"' . ci_s_id( $s ) . '><div class="portfolio-tools"><div class="filter-pills" role="group" aria-label="Filter projects">' . $buttons . '</div><label class="search-field"><span class="sr-only">Search projects</span><input type="search" id="project-search" placeholder="' . esc_attr( $s['search'] ) . '" autocomplete="off"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="10" r="6" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m15 15 5 5" stroke="currentColor" stroke-width="1.5"/></svg></label></div><div class="portfolio-count" role="status" aria-live="polite"><span id="result-count">' . (int) $total . '</span> ' . ci_e( $s['count_label'] ) . '</div><div class="project-grid" id="project-grid">' . $cards . '</div><div class="no-results" hidden><h2>' . ci_html( $s['empty_heading'] ) . '</h2><p>' . ci_e( $s['empty_text'] ) . '</p><button class="button button-dark" id="reset-filters">' . ci_e( $s['empty_button'] ) . ' ' . ci_arrow() . '</button></div><p class="portfolio-note">' . ci_e( $s['note'] ) . '</p></section>';
}

/* ================================================================ INSIGHTS */

function ci_s_insights_list( $s ) {
	$items = array();
	$cats  = array();

	foreach ( ci_ids( 'ci_insight' ) as $aid ) {
		$a = ci_insight( $aid );
		if ( ! $a ) {
			continue;
		}

		$badge = trim( (string) $a['kicker'] );
		if ( '' === $badge ) {
			$badge = 'Insight';
		}
		$slug = sanitize_title( $badge );
		$cats[ $slug ] = $badge;

		$post = get_post( $aid );
		$author = $post ? get_the_author_meta( 'display_name', $post->post_author ) : '';
		if ( ! $author ) {
			$author = $s['default_author'];
		}

		$items[] = array(
			'title'   => $a['title'],
			'url'     => $a['url'],
			'image'   => $a['image'],
			'badge'   => $badge,
			'slug'    => $slug,
			'date'    => get_the_date( 'M d, Y', $aid ),
			'read'    => trim( (string) $a['read'] ) . ( false === stripos( (string) $a['read'], 'read' ) ? ' read' : '' ),
			'excerpt' => $a['intro'],
			'author'  => $author,
		);
	}

	asort( $cats );

	$pills = '<button type="button" class="ci360-insights-filter active" data-blog-filter="all" aria-pressed="true">' . ci_e( $s['all_label'] ) . '</button>';
	foreach ( $cats as $slug => $name ) {
		$pills .= '<button type="button" class="ci360-insights-filter" data-blog-filter="' . esc_attr( $slug ) . '" aria-pressed="false">' . ci_e( $name ) . '</button>';
	}

	$cards = '';
	foreach ( $items as $it ) {
		$img = ci_img( $it['image'], $it['title'], 'ci360-insights-card-img' );
		$cards .= '<a href="' . esc_url( $it['url'] ) . '" class="ci360-insights-card blog-card" data-cats="' . esc_attr( $it['slug'] ) . '" data-text="' . esc_attr( mb_strtolower( wp_strip_all_tags( $it['title'] . ' ' . $it['excerpt'] . ' ' . $it['author'] . ' ' . $it['badge'] ) ) ) . '" data-reveal>'
			. '<span class="ci360-insights-card-top"><span class="ci360-insights-card-media">' . ( $img ? $img : '<span class="ci360-insights-image-fallback"></span>' ) . '<span class="ci360-insights-card-badge">' . ci_e( $it['badge'] ) . '</span></span>'
			. '<span class="ci360-insights-card-body"><span class="ci360-insights-meta">' . ci_e( $it['date'] ) . '<i></i>' . ci_e( $it['read'] ) . '</span><strong class="ci360-insights-card-title">' . ci_e( $it['title'] ) . '</strong><span class="ci360-insights-card-excerpt">' . ci_e( $it['excerpt'] ) . '</span></span></span>'
			. '<span class="ci360-insights-card-footer"><span class="ci360-insights-author">' . ci_e( $it['author'] ) . '</span><span class="ci360-insights-read">' . ci_e( $s['read_more'] ) . ' <span aria-hidden="true">→</span></span></span>'
			. '</a>';
	}

	$spotlight = '';
	if ( $items ) {
		$sp    = $items[0];
		$spimg = ci_img( $sp['image'], $sp['title'], 'ci360-insights-spotlight-img', true );
		$spotlight = '<a href="' . esc_url( $sp['url'] ) . '" class="ci360-insights-spotlight">'
			. '<span class="ci360-insights-spotlight-media">' . ( $spimg ? $spimg : '<span class="ci360-insights-image-fallback"></span>' ) . '<span class="ci360-insights-card-badge">' . ci_e( $sp['badge'] ) . '</span></span>'
			. '<span class="ci360-insights-spotlight-copy"><span class="ci360-insights-meta">' . ci_e( $sp['date'] ) . '<i></i>' . ci_e( $sp['read'] ) . '</span><strong>' . ci_e( $sp['title'] ) . '</strong><span>' . ci_e( $sp['excerpt'] ) . '</span></span>'
			. '<span class="ci360-insights-primary ci360-insights-spotlight-button"><span>' . ci_e( $s['read_more'] ) . '</span><span aria-hidden="true">→</span></span>'
			. '</a>';
	}

	$note = $s['note'] ? '<p class="ci360-insights-note">' . ci_e( $s['note'] ) . '</p>' : '';

	$anchor = ! empty( $s['anchor'] ) ? '<span id="' . esc_attr( $s['anchor'] ) . '" class="ci360-insights-anchor" aria-hidden="true"></span>' : '';
	$hero_style = in_array( $s['hero_style'] ?? 'cinematic', array( 'cinematic', 'split', 'minimal' ), true ) ? $s['hero_style'] : 'cinematic';
	$show_spotlight = ( 'yes' === ( $s['show_spotlight'] ?? 'yes' ) || true === ( $s['show_spotlight'] ?? true ) ) && $spotlight;
	$hero_bg = ci_img_url( $s['hero_background'] ?? '' );
	$hero_attr = $hero_bg ? ' style="' . esc_attr( '--ci-insights-hero-bg:url("' . $hero_bg . '")' ) . '"' : '';
	$hero_class = ' hero-style-' . $hero_style . ( $show_spotlight ? '' : ' no-spotlight' ) . ( $hero_bg ? ' has-custom-background' : '' );

	return '<section id="ci360-insights-archive-root" data-blog-archive>' . $anchor
		. '<div class="ci360-insights-hero' . esc_attr( $hero_class ) . '"' . $hero_attr . '><span class="ci360-insights-glow ci360-insights-glow-a" aria-hidden="true"></span><span class="ci360-insights-glow ci360-insights-glow-b" aria-hidden="true"></span><div class="ci360-insights-hero-grid">'
		. '<div class="ci360-insights-hero-left"><span class="ci360-insights-pill">' . ci_star() . '<span>' . ci_e( $s['badge'] ) . '</span></span><h1>' . ci_e( $s['heading_1'] ) . '<br><em>' . ci_e( $s['heading_2'] ) . '</em></h1><p>' . ci_e( $s['intro'] ) . '</p><a href="' . esc_url( ci_s_link( $s['cta_link'], 'contact' ) ) . '" class="ci360-insights-primary"><span>' . ci_e( $s['cta'] ) . '</span><span aria-hidden="true">→</span></a></div>'
		. ( $show_spotlight ? '<div class="ci360-insights-hero-right">' . $spotlight . '</div>' : '' )
		. '</div></div>'
		. '<div class="ci360-insights-toolbar"><div class="ci360-insights-toolbar-inner"><div class="ci360-insights-filters" role="group" aria-label="Filter insights by topic">' . $pills . '</div><label class="ci360-insights-search"><span class="sr-only">Search insights</span><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><path d="m20 20-4.35-4.35"></path></svg><input type="search" data-blog-search placeholder="' . esc_attr( $s['search'] ) . '" autocomplete="off"></label></div></div>'
		. '<div class="ci360-insights-grid">' . $cards . '</div>'
		. '<div class="ci360-insights-no-results blog-no-results" hidden><h2>' . ci_html( $s['empty_heading'] ) . '</h2><p>' . ci_e( $s['empty_text'] ) . '</p><button type="button" class="ci360-insights-primary" data-blog-reset><span>' . ci_e( $s['empty_button'] ) . '</span><span aria-hidden="true">→</span></button></div>'
		. $note
		. '</section>';
}

function ci_s_topics( $s ) {
	$contact = ci_s_link( $s['link'], 'contact' );
	$topics  = '';
	foreach ( ci_s_rows( $s['topics'] ) as $r ) {
		$topics .= '<a href="' . esc_url( add_query_arg( 'topic', rawurlencode( $r['topic'] ?? '' ), $contact ) ) . '">' . ci_e( $r['topic'] ?? '' ) . ' ' . ci_arrow() . '</a>';
	}
	return '<section class="section wrap topic-section"><div class="section-heading">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2></div><div class="industry-pills">' . $topics . '</div></section>';
}

/* ================================================================ CONTACT */

function ci_s_contact_main( $s ) {
	$details = '';
	foreach ( ci_s_rows( $s['blocks'] ) as $r ) {
		$details .= '<span class="small-label">' . ci_e( $r['label'] ?? '' ) . '</span><a href="mailto:' . esc_attr( $r['email'] ?? '' ) . '">' . ci_e( $r['email'] ?? '' ) . ' ' . ci_arrow() . '</a><a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $r['phone'] ?? '' ) ) . '">' . ci_e( $r['phone'] ?? '' ) . '</a>';
	}
	$conf    = implode( ' <i></i> ', array_map( 'ci_html', array_map( 'trim', explode( '|', (string) $s['confidential'] ) ) ) );
	$options = '';
	foreach ( ci_ids( 'ci_service' ) as $sid ) {
		$options .= '<option value="' . esc_attr( get_post_field( 'post_name', $sid ) ) . '">' . ci_e( ci_title( $sid ) ) . '</option>';
	}
	if ( $s['extra_1'] ) {
		$options .= '<option value="integrated">' . ci_e( $s['extra_1'] ) . '</option>';
	}
	if ( $s['extra_2'] ) {
		$options .= '<option value="not-sure">' . ci_e( $s['extra_2'] ) . '</option>';
	}
	$server = 'server' === ci_opt( 'opt_contact_mode' );
	$tag   = 'h2' === ( $s['heading_tag'] ?? 'h1' ) ? 'h2' : 'h1';
	$crumb = ( 'yes' === ( $s['show_crumb'] ?? 'yes' ) || true === ( $s['show_crumb'] ?? true ) ) ? ci_breadcrumb( $s['crumb'] ) : '';
	return '<section class="contact-page wrap">' . $crumb . '<div class="contact-grid"><div class="contact-intro">' . ci_s_label( $s ) . '<' . $tag . ' class="display" data-title>' . ci_html( $s['heading'] ) . '</' . $tag . '><p class="large-copy">' . ci_e( $s['lead'] ) . '</p><div class="contact-details">' . $details . '</div><div class="contact-photo">' . ci_img( $s['image'] ) . '<span>' . ci_e( $s['image_label'] ) . '</span></div><div class="contact-confidential">' . $conf . '</div></div><form id="enquiry-form" class="enquiry-form" novalidate><span class="small-label">' . ci_e( $s['form_label'] ) . '</span><h2>' . ci_html( $s['form_heading'] ) . '</h2><p class="form-intro">' . ci_e( $s['form_intro'] ) . '</p><div class="form-grid"><label>Your name <span>*</span><input name="name" autocomplete="name" required minlength="2" maxlength="120" placeholder="What should we call you?"></label><label>Email address <span>*</span><input name="email" type="email" autocomplete="email" required maxlength="254" placeholder="you@company.com"></label><label>Phone number<input name="phone" type="tel" autocomplete="tel" maxlength="40" placeholder="Include your country code"></label><label>Company name<input name="company" autocomplete="organization" maxlength="160" placeholder="Your company or organisation"></label><label>Country<input name="country" autocomplete="country-name" maxlength="100" placeholder="Where are you based?"></label><label>What can we help with? <span>*</span><select name="service" required><option value="">Select a service</option>' . $options . '</select></label><label class="full-width">Your message <span>*</span><textarea name="message" required minlength="20" maxlength="5000" rows="4" placeholder="The goal, the challenge, the big idea..."></textarea><small class="field-help">At least 20 characters. Please do not include confidential credentials.</small></label></div><div class="honeypot" aria-hidden="true"><label>Leave this empty<input name="website" tabindex="-1" autocomplete="off"></label></div><label class="consent"><input type="checkbox" name="consent" required><span>' . ci_links( ci_html( $s['consent'] ) ) . '</span></label><div class="form-status" role="status" aria-live="polite"></div><button type="submit" class="button button-dark"><span>' . ( $server ? 'Send enquiry' : 'Review &amp; email enquiry' ) . '</span><i>' . ci_arrow() . '</i></button><p class="form-note">' . ( $server ? 'Your message is sent only after you submit this form.' : 'Review your message, then send it using your email app. Nothing is silently submitted.' ) . '</p></form></div></section>';
}

function ci_s_locations( $s ) {
	$locations = '';
	foreach ( ci_rows( 'opt_offices', 'option' ) as $i => $o ) {
		$locations .= '<article class="location-card" data-reveal><div class="location-illustration location-' . $i . '" aria-hidden="true"><div class="sun"></div><div class="building b1"></div><div class="building b2"></div><div class="building b3"></div><div class="ground"></div><span>0' . ( $i + 1 ) . '</span></div><div><h3>' . ci_e( $o['city'] ) . ' <small>' . ci_e( $o['country_code'] ) . '</small></h3><time data-zone="' . esc_attr( $o['zone'] ) . '">--:--</time></div><p>' . ci_e( $o['address'] ? $o['address'] : 'A connected team in ' . $o['city'] . '. Contact us to arrange a conversation.' ) . '</p><a href="mailto:' . esc_attr( $o['email'] ) . '">' . ci_e( $o['email'] ) . ' ' . ci_arrow() . '</a></article>';
	}
	return '<section class="section dark-section"><div class="wrap"><div class="section-heading">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2></div><div class="locations-grid">' . $locations . '</div></div></section>';
}

/* ================================================================ LEGAL */

function ci_s_legal( $s ) {
	$title    = '' !== (string) $s['title'] ? $s['title'] : ( is_singular() ? ci_title( get_queried_object_id() ) : '' );
	$sections = '';
	foreach ( ci_s_rows( $s['sections'] ) as $i => $r ) {
		$sections .= '<section><span class="small-label">' . ci_pad( $i + 1 ) . '</span><h2>' . ci_e( $r['title'] ?? '' ) . '</h2><p>' . ci_e( $r['text'] ?? '' ) . '</p></section>';
	}
	return '<section class="page-hero wrap legal-hero">' . ci_breadcrumb( $title ) . ci_label( $s['label_1'], $s['label_2'] ) . '<h1 class="display" data-title>' . ci_e( $title ) . '</h1><p class="large-copy">' . ci_e( $s['intro'] ) . '</p></section><section class="legal-layout wrap"><aside><div class="legal-art tone-lilac">' . ci_star() . '<span>' . ci_html( $s['art'] ) . '</span></div><p>' . ci_e( $s['aside'] ) . '</p></aside><div class="legal-body">' . $sections . '</div></section>';
}

/* ================================================================ CURRENT POST / SITE PARTS */

function ci_s_current( $v, $type ) {
	$id = ci_s_post( $v, $type );
	if ( ! $id && get_post_type() === $type ) {
		$id = get_the_ID();
	}
	if ( ! $id ) {
		$ids = ci_ids( $type );
		$id  = $ids ? $ids[0] : 0; // Editor preview: first item.
	}
	return $id;
}
function ci_s_service_detail( $s ) {
	$id = ci_s_current( $s['service'], 'ci_service' );
	return $id ? ci_render_service( $id ) : '';
}
function ci_s_project_detail( $s ) {
	$id = ci_s_current( $s['project'], 'ci_project' );
	return $id ? ci_render_project( $id ) : '';
}
function ci_s_insight_detail( $s ) {
	$id = ci_s_current( $s['insight'], 'ci_insight' );
	return $id ? ci_render_insight( $id ) : '';
}
function ci_s_site_header( $s ) {
	return ci_render_header();
}
function ci_s_site_footer( $s ) {
	return ci_render_footer();
}

/* ================================================================ INSIGHTS – FEATURED + STACK */

/** Articles for the featured insights layout: [ title, category, excerpt, image, url ]. */
function ci_insights_featured_items( $s ) {
	$count = max( 1, (int) $s['count'] );
	$items = array();
	if ( 'insights' === $s['source'] ) {
		foreach ( array_slice( ci_ids( 'ci_insight' ), 0, $count ) as $id ) {
			$a       = ci_insight( $id );
			$items[] = array( $a['title'], $a['kicker'], $a['intro'], $a['image'], $a['url'] );
		}
	} elseif ( 'posts' === $s['source'] ) {
		foreach ( get_posts( array( 'post_type' => 'post', 'numberposts' => $count, 'post_status' => 'publish', 'suppress_filters' => false ) ) as $p ) {
			$cats    = wp_list_pluck( (array) get_the_category( $p->ID ), 'name' );
			$excerpt = has_excerpt( $p ) ? get_the_excerpt( $p ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $p->post_content ) ), 24 );
			$items[] = array( ci_title( $p ), implode( ' • ', array_slice( $cats, 0, 2 ) ), $excerpt, (int) get_post_thumbnail_id( $p ), get_permalink( $p ) );
		}
	} else {
		foreach ( ci_s_rows( $s['items'] ) as $r ) {
			$items[] = array( $r['title'] ?? '', $r['category'] ?? '', $r['excerpt'] ?? '', $r['image'] ?? '', ci_url( $r['link'] ?? '' ) );
		}
	}
	return $items;
}

function ci_s_insights_featured( $s ) {
	$items = ci_insights_featured_items( $s );
	if ( ! $items ) {
		return '';
	}
	$dark  = 'light' !== ( $s['style'] ?? 'dark' );
	$f     = $items[0];
	$stack = '';
	foreach ( array_slice( $items, 1, 3 ) as $it ) {
		$stack .= '<a class="if-card" href="' . esc_url( $it[4] ) . '" data-reveal><span class="if-card-img">' . ci_img( $it[3], $it[0], '', false, 'medium_large' ) . '</span><span class="if-card-body"><span class="if-cat">' . ci_e( $it[1] ) . '</span><h4>' . ci_e( $it[0] ) . '</h4><span class="if-excerpt">' . ci_e( $it[2] ) . '</span></span><span class="if-card-arrow">' . ci_arrow() . '</span></a>';
	}
	$slides = '';
	foreach ( $items as $i => $it ) {
		$slides .= '<a class="if-slide" href="' . esc_url( $it[4] ) . '">' . ci_img( $it[3], $it[0], '', false, 'medium_large' ) . '<span class="if-shade" aria-hidden="true"></span><span class="if-slide-body">' . ( 0 === $i && $s['badge'] ? '<span class="if-badge">' . ci_e( $s['badge'] ) . '</span>' : '' ) . '<span class="if-cat">' . ci_e( $it[1] ) . '</span><h3>' . ci_e( $it[0] ) . '</h3></span></a>';
	}
	$bottom = '';
	if ( 'yes' === $s['show_bottom'] || true === $s['show_bottom'] ) {
		$bottom = '<div class="if-bottom"><div class="if-bottom-left"><span class="if-bottom-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></span><span class="if-bottom-label">' . ci_html( $s['bottom_label'] ) . '</span><i class="if-divider" aria-hidden="true"></i><p>' . ci_e( $s['bottom_text'] ) . '</p></div>' . ci_btn( $s['cta'], $s['cta_link'], $dark ? 'light' : 'dark' ) . '</div>';
	}
	return '<section class="insights-feature section' . ( $dark ? ' is-dark' : '' ) . '">' . ( $dark ? '<span class="if-glow if-glow-a" aria-hidden="true"></span><span class="if-glow if-glow-b" aria-hidden="true"></span>' : '' ) . '<div class="wrap"><div class="section-heading heading-row">' . ci_s_label( $s ) . '<h2 data-reveal>' . ci_html( $s['heading'] ) . '</h2><div class="if-head-side"><p>' . ci_e( $s['intro'] ) . '</p>' . ( $s['view_text'] ? ci_btn( $s['view_text'], $s['view_link'], 'outline' ) : '' ) . '</div></div>'
		. '<div class="if-grid"><a class="if-featured" href="' . esc_url( $f[4] ) . '" data-cursor="Read" data-reveal>' . ci_img( $f[3], $f[0], '', false, 'large' ) . '<span class="if-shade" aria-hidden="true"></span><span class="if-featured-body">' . ( $s['badge'] ? '<span class="if-badge">' . ci_e( $s['badge'] ) . '</span>' : '' ) . '<span class="if-cat">' . ci_e( $f[1] ) . '</span><h3>' . ci_e( $f[0] ) . '</h3><span class="if-excerpt">' . ci_e( $f[2] ) . '</span><span class="if-read">' . ci_e( $s['read_text'] ) . ' ' . ci_arrow() . '</span></span></a>'
		. ( $stack ? '<div class="if-stack">' . $stack . '</div>' : '' ) . '</div>'
		. '<div class="if-carousel" data-if-carousel><div class="if-track">' . $slides . '</div><div class="if-dots"></div></div>'
		. $bottom . '</div></section>';
}

/* ================================================================ PAGE HERO – BANNER */

function ci_s_page_banner( $s ) {
	$crumb  = '' !== trim( (string) $s['crumb'] ) ? ci_breadcrumb( $s['crumb'] ) : '';
	$second = $s['secondary'] ? '<a class="text-link" href="' . esc_url( 0 === strpos( (string) $s['secondary_link'], '#' ) ? $s['secondary_link'] : ci_url( $s['secondary_link'] ) ) . '">' . ci_e( $s['secondary'] ) . ' ' . ci_arrow( 0 === strpos( (string) $s['secondary_link'], '#' ) ? 'se' : 'ne' ) . '</a>' : '';
	$button = $s['button'] ? ( 0 === strpos( (string) $s['button_link'], '#' )
		? '<a class="button button-light" href="' . esc_attr( $s['button_link'] ) . '" data-magnetic><span>' . ci_html( $s['button'] ) . '</span><i>' . ci_arrow( 'se' ) . '</i></a>'
		: ci_btn( $s['button'], $s['button_link'], 'light' ) ) : '';
	$img2   = ci_img( $s['image_2'], '' );
	$card   = '' !== trim( (string) $s['card_value'] ) ? '<div class="pb-card"><b>' . ci_e( ci_s_tokens( $s['card_value'] ) ) . '</b><span>' . ci_e( $s['card_label'] ) . '</span></div>' : '';
	$stats  = '';
	foreach ( ci_s_rows( $s['stats'] ) as $r ) {
		if ( '' === trim( (string) ( $r['value'] ?? '' ) ) ) {
			continue;
		}
		$stats .= '<div class="pb-stat" data-reveal><b>' . ci_e( ci_s_tokens( $r['value'] ) ) . '</b><span>' . ci_e( $r['label'] ?? '' ) . '</span></div>';
	}
	return '<section class="page-banner dark-section"><div class="wrap"><div class="pb-grid"><div class="pb-copy">' . $crumb . ci_s_label( $s ) . '<h1 class="pb-title" data-title>' . ci_html( $s['heading'] ) . '</h1>' . ( $s['intro'] ? '<p class="pb-intro">' . ci_e( $s['intro'] ) . '</p>' : '' ) . ( $button || $second ? '<div class="pb-actions">' . $button . $second . '</div>' : '' ) . '</div>'
		. '<div class="pb-visual" aria-hidden="true"><div class="pb-ring"></div><div class="pb-frame">' . ci_img( $s['image'], '', '', true ) . '</div>' . ( $img2 ? '<div class="pb-frame-2">' . $img2 . '</div>' : '' ) . $card . '<div class="pb-sticker">' . ci_star() . '</div></div></div>'
		. ( $stats ? '<div class="pb-stats">' . $stats . '</div>' : '' ) . '</div></section>';
}

/* ================================================================ BLOG – POSTS GRID */

function ci_s_blog_grid( $s ) {
	$per   = max( 1, (int) $s['per_page'] );
	$paged = max( 1, isset( $_GET['pg'] ) ? (int) $_GET['pg'] : 1 ); // phpcs:ignore WordPress.Security.NonceVerification
	$args  = array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => $per, 'paged' => $paged, 'ignore_sticky_posts' => false );
	if ( $s['category'] ) {
		$args['category_name'] = $s['category'];
	}
	$q     = new WP_Query( $args );
	$cards = '';
	$tones = array( 'lilac', 'blue', 'mint', 'yellow', 'pink', 'orange' );
	foreach ( $q->posts as $i => $p ) {
		$cats  = wp_list_pluck( (array) get_the_category( $p->ID ), 'name' );
		$words = str_word_count( wp_strip_all_tags( $p->post_content ) );
		$cards .= ci_article_card(
			array(
				'title'  => ci_title( $p ),
				'url'    => get_permalink( $p ),
				'tone'   => $tones[ $i % count( $tones ) ],
				'image'  => (int) get_post_thumbnail_id( $p ),
				'kicker' => $cats ? $cats[0] : get_the_date( '', $p ),
				'read'   => max( 1, (int) ceil( $words / 200 ) ) . ' min',
			),
			( $paged - 1 ) * $per + $i
		);
	}
	$pager = '';
	if ( $q->max_num_pages > 1 ) {
		$base  = remove_query_arg( 'pg' );
		$pager = '<nav class="blog-pager" aria-label="Blog pages">';
		if ( $paged > 1 ) {
			$pager .= '<a class="button button-outline" href="' . esc_url( add_query_arg( 'pg', $paged - 1, $base ) ) . '" data-magnetic><span>Newer</span><i>' . ci_arrow( 'sw' ) . '</i></a>';
		}
		$pager .= '<span class="blog-pager-count">' . ci_pad( $paged ) . ' / ' . ci_pad( $q->max_num_pages ) . '</span>';
		if ( $paged < $q->max_num_pages ) {
			$pager .= '<a class="button button-outline" href="' . esc_url( add_query_arg( 'pg', $paged + 1, $base ) ) . '" data-magnetic><span>Older</span><i>' . ci_arrow() . '</i></a>';
		}
		$pager .= '</nav>';
	}
	wp_reset_postdata();
	if ( ! $cards ) {
		if ( 'yes' === $s['fallback'] || true === $s['fallback'] ) {
			foreach ( ci_ids( 'ci_insight' ) as $i => $aid ) {
				$cards .= ci_article_card( ci_insight( $aid ), $i );
			}
		}
		if ( ! $cards ) {
			return '<section class="wrap section blog-section"' . ci_s_id( $s ) . '><p class="blog-empty">' . ci_e( $s['empty'] ) . '</p></section>';
		}
	}
	return '<section class="wrap section blog-section"' . ci_s_id( $s ) . '><div class="articles-grid insights-grid">' . $cards . '</div>' . $pager . '</section>';
}

/* ================================================================ BLOG – ARCHIVE (filters + search) */

/** Is this category excluded (e.g. case studies)? */
function ci_blog_excluded( $term, $patterns ) {
	$hay = strtolower( $term->slug . ' ' . $term->name . ' ' . str_replace( '-', ' ', $term->slug ) );
	foreach ( $patterns as $p ) {
		if ( '' !== $p && false !== strpos( $hay, $p ) ) {
			return true;
		}
	}
	return false;
}

function ci_s_blog_archive( $s ) {
	$patterns = array_filter( array_map( function ( $x ) {
		return strtolower( trim( $x ) );
	}, explode( ',', (string) $s['exclude'] ) ) );
	$excluded = array();
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $t ) {
		if ( ci_blog_excluded( $t, $patterns ) ) {
			$excluded[] = $t->term_id;
		}
	}
	$posts = get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'numberposts'      => max( 1, (int) $s['count'] ),
			'category__not_in' => $excluded,
			'suppress_filters' => false,
		)
	);
	$items = array();
	$cats  = array();
	foreach ( $posts as $p ) {
		$terms = array();
		foreach ( (array) get_the_category( $p->ID ) as $t ) {
			if ( ! in_array( $t->term_id, $excluded, true ) ) {
				$terms[]           = $t;
				$cats[ $t->slug ] = $t->name;
			}
		}
		$words   = str_word_count( wp_strip_all_tags( $p->post_content ) );
		$excerpt = has_excerpt( $p ) ? get_the_excerpt( $p ) : wp_trim_words( wp_strip_all_tags( strip_shortcodes( $p->post_content ) ), 26 );
		$author  = get_the_author_meta( 'display_name', $p->post_author );
		$items[] = array(
			'title'   => ci_title( $p ),
			'url'     => get_permalink( $p ),
			'image'   => (int) get_post_thumbnail_id( $p ),
			'badge'   => $terms ? $terms[0]->name : 'Insight',
			'slugs'   => wp_list_pluck( $terms, 'slug' ),
			'date'    => get_the_date( 'M d, Y', $p ),
			'read'    => max( 1, (int) ceil( $words / 200 ) ) . ' min read',
			'excerpt' => $excerpt,
			'author'  => $author ? $author : $s['default_author'],
		);
	}
	if ( ! $items && ( 'yes' === $s['fallback'] || true === $s['fallback'] ) ) {
		foreach ( ci_ids( 'ci_insight' ) as $aid ) {
			$a    = ci_insight( $aid );
			$slug = sanitize_title( $a['kicker'] );
			$cats[ $slug ] = $a['kicker'];
			$items[]       = array(
				'title'   => $a['title'],
				'url'     => $a['url'],
				'image'   => $a['image'],
				'badge'   => $a['kicker'],
				'slugs'   => array( $slug ),
				'date'    => get_the_date( 'M d, Y', $aid ),
				'read'    => $a['read'] . ' read',
				'excerpt' => $a['intro'],
				'author'  => ci_opt( 'tpl_insight_byline' ),
			);
		}
	}
	asort( $cats );
	$pills = '<button type="button" data-blog-filter="all" aria-pressed="true">' . ci_e( $s['all_label'] ) . ' <small>' . count( $items ) . '</small></button>';
	foreach ( $cats as $slug => $name ) {
		$pills .= '<button type="button" data-blog-filter="' . esc_attr( $slug ) . '" aria-pressed="false">' . ci_e( $name ) . '</button>';
	}
	$cards = '';
	foreach ( $items as $it ) {
		$img    = ci_img( $it['image'], $it['title'] );
		$cards .= '<a class="blog-card" href="' . esc_url( $it['url'] ) . '" data-cats="' . esc_attr( implode( ' ', $it['slugs'] ) ) . '" data-text="' . esc_attr( mb_strtolower( wp_strip_all_tags( $it['title'] . ' ' . $it['excerpt'] . ' ' . $it['author'] . ' ' . $it['badge'] ) ) ) . '" data-reveal>'
			. '<span class="blog-card-media' . ( $img ? '' : ' no-image' ) . '">' . ( $img ? $img : ci_star() ) . '<span class="blog-card-badge">' . ci_e( $it['badge'] ) . '</span></span>'
			. '<span class="blog-card-body"><span class="blog-card-meta">' . ci_e( $it['date'] ) . ' <i></i> ' . ci_e( $it['read'] ) . '</span><h3>' . ci_e( $it['title'] ) . '</h3><span class="blog-card-excerpt">' . ci_e( $it['excerpt'] ) . '</span></span>'
			. '<span class="blog-card-foot"><span class="blog-card-author">' . ci_e( $it['author'] ) . '</span><span class="blog-card-read">' . ci_e( $s['read_more'] ) . ' ' . ci_arrow() . '</span></span></a>';
	}
	return '<section class="wrap blog-archive"' . ci_s_id( $s ) . ' data-blog-archive><div class="portfolio-tools blog-tools"><div class="filter-pills" role="group" aria-label="Filter articles by topic">' . $pills . '</div><label class="search-field"><span class="sr-only">Search articles</span><input type="search" data-blog-search placeholder="' . esc_attr( $s['search'] ) . '" autocomplete="off"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="10" r="6" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m15 15 5 5" stroke="currentColor" stroke-width="1.5"/></svg></label></div>'
		. '<div class="portfolio-count" role="status" aria-live="polite"><span data-blog-count>' . count( $items ) . '</span> ' . ci_e( $s['count_label'] ) . '</div>'
		. '<div class="blog-archive-grid">' . $cards . '</div>'
		. '<div class="blog-no-results" hidden><h2>' . ci_html( $s['empty_heading'] ) . '</h2><p>' . ci_e( $s['empty_text'] ) . '</p><button type="button" class="button button-dark" data-blog-reset><span>' . ci_e( $s['empty_button'] ) . '</span><i>' . ci_arrow() . '</i></button></div></section>';
}

/** Render Impact & Brand Channels Section */
function ci_s_impact_brand( $s ) {
	$channels_title = $s['channels_title'] ?? 'Brand Channels:';
	$channels       = ci_s_rows( $s['channels'] ?? array() );
	$impact_title   = $s['impact_title'] ?? 'The Impact';
	$cards          = ci_s_rows( $s['impact_cards'] ?? array() );

	$ch_html = '';
	if ( ! empty( $channels ) ) {
		foreach ( $channels as $ch ) {
			$platform = $ch['platform'] ?? '';
			$url      = ! empty( $ch['url'] ) ? $ch['url'] : '#';
			$icon     = ci_social_icon_svg( $platform, $url );
			$ch_html .= '<a href="' . esc_url( $url ) . '" class="ci360-brand-ch-icon" title="' . esc_attr( ucfirst( $platform ) ) . '" target="_blank" rel="noopener noreferrer">' . $icon . '</a>';
		}
	}

	$cards_html = '';
	if ( ! empty( $cards ) ) {
		foreach ( $cards as $card ) {
			$text = $card['text'] ?? '';
			$cards_html .= '<div class="ci360-impact-card"><div class="ci360-impact-card-content"><p>' . ci_e( $text ) . '</p></div></div>';
		}
	}

	$out = '<section class="ci360-impact-brand-section wrap"' . ci_s_id( $s ) . '>';
	if ( $channels_title || $ch_html ) {
		$out .= '<div class="ci360-brand-channels-wrapper">';
		if ( $channels_title ) {
			$out .= '<h3 class="ci360-brand-channels-title">' . ci_e( $channels_title ) . '</h3>';
		}
		if ( $ch_html ) {
			$out .= '<div class="ci360-brand-channels-icons">' . $ch_html . '</div>';
		}
		$out .= '</div>';
	}

	$out .= '<div class="ci360-impact-box">';
	if ( $impact_title ) {
		$out .= '<div class="ci360-impact-header"><h2>' . ci_e( $impact_title ) . '</h2></div>';
	}
	if ( $cards_html ) {
		$out .= '<div class="ci360-impact-grid">' . $cards_html . '</div>';
	}
	$out .= '</div>';

	$out .= '</section>';

	return $out;
}

/** Render Challenge & Approach Section */
function ci_s_challenge_approach( $s ) {
	$challenge_title = $s['challenge_title'] ?? 'The Challenge';
	$challenge_text  = $s['challenge_text'] ?? '';
	$approach_title   = $s['approach_title'] ?? 'The Approach';
	$approach_text    = $s['approach_text'] ?? '';
	$stats            = ci_s_rows( $s['stats'] ?? array() );

	$stats_html = '';
	if ( ! empty( $stats ) ) {
		foreach ( $stats as $st ) {
			$stats_html .= '<div class="ci360-ca-stat-card"><span class="ci360-ca-stat-number">' . ci_e( $st['number'] ?? '' ) . '</span><span class="ci360-ca-stat-label">' . ci_e( $st['label'] ?? '' ) . '</span></div>';
		}
	}

	$out = '<section class="ci360-challenge-approach-section wrap"' . ci_s_id( $s ) . '>';
	$out .= '<div class="ci360-ca-grid">';

	$out .= '<div class="ci360-ca-card ci360-ca-challenge">';
	$out .= '<div class="ci360-ca-card-header"><span class="ci360-ca-kicker">01 / PROBLEM</span><h3>' . ci_e( $challenge_title ) . '</h3></div>';
	$out .= '<div class="ci360-ca-card-body"><p>' . ci_e( $challenge_text ) . '</p></div>';
	$out .= '</div>';

	$out .= '<div class="ci360-ca-card ci360-ca-approach">';
	$out .= '<div class="ci360-ca-card-header"><span class="ci360-ca-kicker">02 / SOLUTION</span><h3>' . ci_e( $approach_title ) . '</h3></div>';
	$out .= '<div class="ci360-ca-card-body"><p>' . ci_e( $approach_text ) . '</p></div>';
	$out .= '</div>';

	$out .= '</div>';

	if ( $stats_html ) {
		$out .= '<div class="ci360-ca-stats-row">' . $stats_html . '</div>';
	}

	$out .= '</section>';

	return $out;
}


