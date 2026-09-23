<?php
/**
 * Fallback template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) :
	the_post();
	?><main id="main"><section class="page-hero wrap legal-hero"><?php echo ci_breadcrumb( ci_title() ); ?><h1 class="display" data-title><?php echo ci_e( ci_title() ); ?></h1></section><section class="legal-layout wrap"><aside></aside><div class="legal-body"><?php the_content(); ?></div></section><?php echo ci_compact_cta(); ?></main><?php
endwhile;
get_footer();
