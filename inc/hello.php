<?php
/**
 * Hello Elementor integration.
 *
 * The CI360 design ships its own complete stylesheet, header and footer, so the parent
 * theme's reset/theme CSS and its customizer header/footer are switched off here.
 * Elementor Pro Theme Builder headers/footers still take over when you publish one.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'hello_elementor_enqueue_style', '__return_false' );
add_filter( 'hello_elementor_enqueue_theme_style', '__return_false' );
add_filter( 'hello_elementor_header_footer', '__return_false' );
add_filter( 'hello_elementor_register_menus', '__return_false' );
add_filter( 'hello_elementor_description_meta_tag', '__return_false' );
add_filter( 'hello_elementor_content_width', function () {
	return 1512;
} );

/** Warn when the parent theme is missing. */
add_action( 'admin_notices', function () {
	$parent = wp_get_theme()->parent();
	if ( ! $parent || 'hello-elementor' !== $parent->get_template() ) {
		echo '<div class="notice notice-error"><p><strong>CI360:</strong> this is a child theme of <em>Hello Elementor</em>. Install Hello Elementor (Appearance › Themes › Add New) and keep it installed.</p></div>';
	}
	if ( ! did_action( 'elementor/loaded' ) ) {
		echo '<div class="notice notice-warning"><p><strong>CI360:</strong> install and activate the free <em>Elementor</em> plugin to edit pages section by section. The theme still works without it (built-in templates are used).</p></div>';
	}
} );

/** Is this post edited with Elementor? */
function ci_is_elementor( $post_id ) {
	if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	$doc = \Elementor\Plugin::$instance->documents->get( $post_id );
	return $doc && $doc->is_built_with_elementor();
}

/** Register Elementor Theme Builder header and footer locations. */
add_action( 'elementor/theme/register_locations', function ( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
} );
