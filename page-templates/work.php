<?php
/**
 * Template Name: CI360: Work / Stories
 *
 * Pick this from Page Attributes on any Page you create yourself -- nothing is created
 * automatically. Renders the CI360 "work" section recipe, or your own Elementor design
 * or custom section list if you've built one on this specific page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
ci_render_recipe_page( 'work' );
get_footer();
