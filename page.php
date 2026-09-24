<?php
/**
 * Pages. Elementor pages render their CI360 section widgets full width;
 * other pages use the CI360 text layout.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
while ( have_posts() ) :
	the_post();
	if ( ci_is_elementor( get_the_ID() ) ) :
		?><main id="main" class="ci-elementor-main"><?php the_content(); ?></main><?php
	elseif ( get_post_meta( get_the_ID(), '_ci360_sections', true ) ) :
		// Built with CI360 sections but Elementor is not active: render the same sections.
		echo '<main id="main">';
		foreach ( (array) get_post_meta( get_the_ID(), '_ci360_sections', true ) as $ci_row ) {
			echo ci_section( $ci_row[0], $ci_row[1] ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</main>';
	else :
		?><main id="main"><section class="page-hero wrap legal-hero"><?php echo ci_breadcrumb( ci_title( get_the_ID() ) ); ?><h1 class="display" data-title><?php echo ci_e( ci_title( get_the_ID() ) ); ?></h1></section><section class="legal-layout wrap"><aside></aside><div class="legal-body"><?php the_content(); ?></div></section><?php echo ci_compact_cta(); ?></main><?php
	endif;
endwhile;
get_footer();
