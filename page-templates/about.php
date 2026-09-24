<?php
/**
 * Template Name: CI360: About
 *
 * Pick this from Page Attributes on any Page you create yourself -- nothing is created
 * automatically. Renders the CI360 "about" section recipe, or your own Elementor design
 * or custom section list if you've built one on this specific page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
ci_render_recipe_page( 'about' );
get_footer();
