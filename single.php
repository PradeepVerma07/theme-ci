<?php
/**
 * Single Blog post.
 *
 * Uses the same reading layout as a CI360 Insight (ci_insight() in inc/components.php
 * normalises a plain WordPress post automatically), and its "related" grid pulls in
 * other Insights, Projects and Blog posts that share a Category with this one — see
 * the category-inheritance notes in inc/cpt.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) {
	the_post();

	$post_id      = get_the_ID();
	$is_elementor = false;

	if ( class_exists( '\Elementor\Plugin' ) ) {
		$document = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( $document && $document->is_built_with_elementor() ) {
			$is_elementor = true;
		}
	}

	if ( $is_elementor ) {
		?>
		<main id="main" <?php post_class( 'site-main' ); ?>>
			<div class="page-content entry-content">
				<?php the_content(); ?>
			</div>
		</main>
		<?php
	} else {
		echo '<main id="main">';
		echo ci_render_modern_blog_post( $post_id );
		echo '</main>';
	}
}

get_footer();
