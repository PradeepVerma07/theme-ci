<?php
/**
 * Footer.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ci_email = ci_opt( 'opt_email' );
$ci_phone = ci_opt( 'opt_phone' );
?><footer class="footer"><div class="footer-cta wrap"><div><?php echo ci_label( ci_opt( 'opt_footer_label_1' ), ci_opt( 'opt_footer_label_2' ) ); ?><a class="footer-head" href="<?php echo esc_url( ci_url( ci_opt( 'opt_footer_link' ) ) ); ?>"><?php echo ci_html( ci_opt( 'opt_footer_heading' ) ); ?><span><?php echo ci_arrow(); ?></span></a></div><div class="footer-sticker" aria-hidden="true"><?php echo ci_star(); ?><span><?php echo ci_html( ci_opt( 'opt_footer_sticker' ) ); ?></span></div></div><div class="footer-lower wrap"><div class="footer-brand"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo"><?php echo ci_e( ci_opt( 'opt_logo_text' ) ); ?><span class="degree">&deg;</span><small><?php echo ci_e( ci_opt( 'opt_logo_small' ) ); ?></small></a><p><?php echo ci_e( ci_opt( 'opt_footer_about' ) ); ?></p><a class="text-link" href="mailto:<?php echo esc_attr( $ci_email ); ?>"><?php echo ci_e( $ci_email ); ?> <?php echo ci_arrow(); ?></a></div><div class="footer-links"><span class="small-label"><?php echo ci_e( ci_opt( 'opt_footer_explore' ) ); ?></span><?php
foreach ( ci_menu( 'footer' ) as $ci_item ) {
	echo '<a href="' . esc_url( $ci_item[0] ) . '">' . ci_e( $ci_item[1] ) . '</a>';
}
?></div><div class="footer-offices"><span class="small-label"><?php echo ci_e( ci_opt( 'opt_footer_offices' ) ); ?></span><?php
foreach ( ci_rows( 'opt_offices', 'option' ) as $ci_o ) {
	echo '<div><span>' . ci_e( $ci_o['city'] ) . ' <small>' . ci_e( $ci_o['country_code'] ) . '</small></span><time data-zone="' . esc_attr( $ci_o['zone'] ) . '" aria-label="Current time in ' . esc_attr( $ci_o['city'] ) . '">--:--</time></div>';
}
?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $ci_phone ) ); ?>"><?php echo ci_e( $ci_phone ); ?></a></div></div><div class="footer-bottom wrap"><span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo ci_e( ci_opt( 'opt_brand' ) ); ?>.</span><div><?php
foreach ( ci_menu( 'legal' ) as $ci_item ) {
	echo '<a href="' . esc_url( $ci_item[0] ) . '">' . ci_e( $ci_item[1] ) . '</a>';
}
?><button class="motion-toggle" aria-pressed="false">Motion <span>on</span></button><button class="back-top" aria-label="Back to top">Back to top <?php echo ci_arrow(); ?></button></div></div></footer></div><?php wp_footer(); ?><noscript><div style="padding:20px;text-align:center;background:#ece8df">All pages and images can be read without JavaScript. Enable JavaScript for filtering, animated navigation and the enquiry form, or contact <a href="mailto:<?php echo esc_attr( $ci_email ); ?>"><?php echo ci_e( $ci_email ); ?></a>.</div></noscript></body></html>
