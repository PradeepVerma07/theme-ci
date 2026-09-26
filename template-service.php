<?php
/**
 * Template Name: Service Page Template
 * Template Post Type: ci_service, post, page
 *
 * Custom Single Service Template matching high-fidelity design.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) {
	the_post();
	?>
	<main id="main" <?php post_class( 'site-main' ); ?>>
		<?php
		if ( function_exists( 'ci_render_service' ) ) {
			echo ci_render_service( get_the_ID() );
		} else {
			echo '<div class="page-content entry-content">';
			the_content();
			echo '</div>';
		}
		?>
	</main>
	<?php
}

get_footer();
