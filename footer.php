<?php
/**
 * Footer.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ci_email = ci_opt( 'opt_email' );
$ci_phone = ci_opt( 'opt_phone' );
?><?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
	echo ci_render_footer(); // phpcs:ignore WordPress.Security.EscapeOutput
}
?></div><?php wp_footer(); ?><noscript><div style="padding:20px;text-align:center;background:#ece8df">All pages and images can be read without JavaScript. Enable JavaScript for filtering, animated navigation and the enquiry form, or contact <a href="mailto:<?php echo esc_attr( $ci_email ); ?>"><?php echo ci_e( $ci_email ); ?></a>.</div></noscript></body></html>
