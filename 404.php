<?php
/**
 * 404.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
?><main id="main"><section class="not-found wrap"><div class="error-art"><?php echo ci_img( ci_opt( 'opt_404_image' ) ); ?><span>404</span></div><?php echo ci_label( ci_opt( 'opt_404_label_1' ), ci_opt( 'opt_404_label_2' ) ); ?><h1 class="display"><?php echo ci_html( ci_opt( 'opt_404_heading' ) ); ?></h1><p><?php echo ci_html( ci_opt( 'opt_404_text' ) ); ?></p><?php echo ci_btn( ci_opt( 'opt_404_button' ), '/' ); ?></section></main><?php
get_footer();
