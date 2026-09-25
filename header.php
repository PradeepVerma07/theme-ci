<?php
/**
 * Header: logo, desktop nav, full-screen menu dialog.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ci_email = ci_opt( 'opt_email' );
?><!doctype html>
<html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>><?php wp_body_open(); ?><div class="page-wipe" aria-hidden="true"><img class="page-loader-logo" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/brand-icon.png' ); ?>" alt="Loading..." width="90" height="90" /></div><div id="site-shell"><?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
	echo ci_render_header(); // phpcs:ignore WordPress.Security.EscapeOutput
}
?>
