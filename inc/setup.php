<?php
/**
 * Theme setup, assets, menus and head output.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'menus' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 420,
			'flex-height' => true,
			'flex-width'  => true,
			'unlink-homepage-logo' => false,
		)
	);
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus(
		array(
			'desktop' => __( 'Header (desktop bar)', 'ci360' ),
			'overlay' => __( 'Full-screen menu', 'ci360' ),
			'footer'  => __( 'Footer explore links', 'ci360' ),
			'legal'   => __( 'Footer legal links', 'ci360' ),
		)
	);
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'ci360-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', array(), null );
	wp_enqueue_style( 'ci360-main', CI360_URI . '/assets/css/main.css', array( 'ci360-fonts' ), CI360_VERSION );
	wp_enqueue_style( 'ci360-sections', CI360_URI . '/assets/css/sections-extra.css', array( 'ci360-main' ), CI360_VERSION );
	wp_enqueue_style( 'ci360-elementor', CI360_URI . '/assets/css/elementor.css', array( 'ci360-sections' ), CI360_VERSION );
	wp_enqueue_style( 'ci360-editable-v25', CI360_URI . '/assets/css/editable-v25.css', array( 'ci360-elementor' ), CI360_VERSION );
	wp_add_inline_style( 'ci360-main', '.admin-bar .site-header{top:32px}@media (max-width:782px){.admin-bar .site-header{top:46px}}.ci-wipe-in .page-wipe{transform:translateY(0)}' );

	wp_enqueue_script( 'ci360-main', CI360_URI . '/assets/js/main.js', array(), CI360_VERSION, true );
	wp_localize_script( 'ci360-main', 'CI360', ci_js_data() );
	wp_enqueue_script( 'ci360-final-enhancements', CI360_URI . '/assets/js/final-enhancements.js', array( 'ci360-main' ), CI360_VERSION, true );

	// Remove block library CSS: the theme does not render block content on the front end.
	if ( ! is_singular() || ! has_blocks() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}, 20 );

/**
 * Data the front-end script needs (testimonial slider, timeline, form settings).
 */
function ci_js_data() {
	$testimonials = array();
	foreach ( ci_posts( 'ci_testimonial' ) as $t ) {
		$testimonials[] = array(
			'name'    => ci_title( $t ),
			'company' => (string) ci_get( 'testimonial_company', $t->ID ),
			'quote'   => (string) ci_get( 'testimonial_quote', $t->ID ),
		);
	}
	return array(
		'settings'     => array(
			'email'           => (string) ci_opt( 'opt_email' ),
			'contactMode'     => (string) ci_opt( 'opt_contact_mode' ),
			'enquiryEndpoint' => esc_url_raw( rest_url( 'ci360/v1/enquiry' ) ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'siteUrl'         => home_url(),
		),
		'testimonials' => $testimonials,
		'timeline'     => array(),
	);
}

/**
 * Head: reduced-motion bootstrap (same as prototype) + page-transition flag + favicon.
 */
add_action( 'wp_head', function () {
	echo "<meta name=\"theme-color\" content=\"#f5f3ed\">\n";
	echo "<script>try{if(localStorage.getItem('ci360-motion')==='off'||(!localStorage.getItem('ci360-motion')&&matchMedia('(prefers-reduced-motion: reduce)').matches))document.documentElement.classList.add('reduced-motion')}catch(e){}try{if(sessionStorage.getItem('ci360-wipe')){sessionStorage.removeItem('ci360-wipe');if(!document.documentElement.classList.contains('reduced-motion'))document.documentElement.classList.add('ci-wipe-in')}}catch(e){}</script>\n";
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2080%2080%22%3E%3Crect%20width%3D%2280%22%20height%3D%2280%22%20rx%3D%2220%22%20fill%3D%22%23f45a31%22%2F%3E%3Ctext%20x%3D%2212%22%20y%3D%2255%22%20font-family%3D%22Arial%22%20font-weight%3D%22bold%22%20font-size%3D%2246%22%20fill%3D%22%2322221f%22%3Eci%3C%2Ftext%3E%3C%2Fsvg%3E">' . "\n";
	}
	// Basic meta description when no SEO plugin is active.
	if ( ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'RankMath' ) ) {
		$desc = ci_opt( 'opt_meta_description' );
		if ( is_singular( array( 'ci_service', 'ci_project' ) ) ) {
			$desc = ci_get( get_post_type() === 'ci_service' ? 'service_summary' : 'project_summary' );
		} elseif ( is_singular( 'ci_insight' ) ) {
			$desc = ci_get( 'insight_intro' );
		}
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $desc ) ) . "\">\n";
		}
	}
}, 1 );

// "Title | CI360 Degrees", and the front page uses its own SEO title.
add_filter( 'document_title_separator', function () {
	return '|';
} );
add_filter( 'document_title_parts', function ( $parts ) {
	if ( is_front_page() && ci_opt( 'opt_home_title' ) ) {
		$parts['title'] = ci_opt( 'opt_home_title' );
		unset( $parts['tagline'] );
	}
	return $parts;
} );
