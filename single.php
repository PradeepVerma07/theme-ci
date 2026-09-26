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
	?>
	<main id="main" <?php post_class( 'site-main' ); ?>>
		<div class="page-content entry-content">
			<?php the_content(); ?>
		</div>
	</main>
	<?php
}

get_footer();
