<?php
/**
 * Template Name: Blog Post Template
 * Template Post Type: post, ci_project, page, ci_insight
 *
 * Custom Template for Blog Posts.
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
		if ( function_exists( 'ci_render_modern_blog_post' ) ) {
			echo ci_render_modern_blog_post( get_the_ID() );
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
