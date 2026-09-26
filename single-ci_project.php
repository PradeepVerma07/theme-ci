<?php
/**
 * Single project.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) {
	the_post();

	$content      = get_the_content();
	$is_elementor = false;

	if ( class_exists( '\Elementor\Plugin' ) ) {
		$document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
		if ( $document && $document->is_built_with_elementor() ) {
			$is_elementor = true;
		}
	}

	if ( $is_elementor || ! empty( trim( (string) $content ) ) ) {
		?>
		<main id="main" <?php post_class( 'site-main' ); ?>>
			<div class="page-content entry-content">
				<?php the_content(); ?>
			</div>
		</main>
		<?php
	} else {
		echo '<main id="main">';
		echo ci_render_project( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '</main>';
	}
}

get_footer();
