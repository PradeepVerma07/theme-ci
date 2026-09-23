<?php
/**
 * Header: logo, desktop nav, full-screen menu dialog.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ci_email = ci_opt( 'opt_email' );
?><!doctype html>
<html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head><body <?php body_class(); ?>><?php wp_body_open(); ?><div class="page-wipe" aria-hidden="true"><?php echo ci_star(); ?></div><div id="site-shell"><a class="skip-link" href="#main">Skip to content</a><header class="site-header"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo" aria-label="<?php echo esc_attr( ci_opt( 'opt_brand' ) ); ?> home"><?php echo ci_e( ci_opt( 'opt_logo_text' ) ); ?><span class="degree">&deg;</span><small><?php echo ci_e( ci_opt( 'opt_logo_small' ) ); ?></small></a><nav class="desktop-nav" aria-label="Primary navigation"><?php
foreach ( ci_menu( 'desktop' ) as $ci_item ) {
	echo '<a href="' . esc_url( $ci_item[0] ) . '" ' . ( ci_is_current( $ci_item[0] ) ? 'aria-current="page"' : '' ) . '>' . ci_e( $ci_item[1] ) . '</a>';
}
?></nav><div class="nav-right"><a class="nav-contact" href="<?php echo esc_url( ci_url( ci_opt( 'opt_header_cta_link' ) ) ); ?>"><?php echo ci_html( ci_opt( 'opt_header_cta' ) ); ?> <span><?php echo ci_arrow(); ?></span></a><button class="menu-toggle" aria-label="Open navigation" aria-expanded="false" aria-controls="menu-dialog"><span></span><span></span></button></div></header><div class="scroll-progress" aria-hidden="true"></div><dialog class="menu-dialog" id="menu-dialog"><div class="menu-top"><span class="small-label"><?php echo ci_e( ci_opt( 'opt_menu_label' ) ); ?></span><button class="menu-close" aria-label="Close navigation">Close <span>&times;</span></button></div><div class="menu-layout"><nav aria-label="Expanded navigation"><?php
foreach ( ci_menu( 'overlay' ) as $ci_i => $ci_item ) {
	echo '<a href="' . esc_url( $ci_item[0] ) . '"><small>' . ci_pad( $ci_i + 1 ) . '</small>' . ci_e( $ci_item[1] ) . '<span>' . ci_arrow() . '</span></a>';
}
?></nav><div class="menu-art"><?php echo ci_img( ci_opt( 'opt_menu_image' ) ); ?><p><?php echo ci_html( ci_opt( 'opt_menu_text' ) ); ?></p></div></div><div class="menu-bottom"><a href="mailto:<?php echo esc_attr( $ci_email ); ?>"><?php echo ci_e( $ci_email ); ?></a><span><?php echo ci_e( ci_opt( 'opt_locations_line' ) ); ?></span></div></dialog>
