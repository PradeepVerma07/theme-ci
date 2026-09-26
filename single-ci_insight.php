<?php
/**
 * Single insight.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) {
	the_post();
	$tpl_slug = get_page_template_slug();
	?>
	<main id="main" <?php post_class( 'site-main' ); ?>>
		<?php
		if ( 'template-case-study.php' === $tpl_slug && function_exists( 'ci_render_project' ) ) {
			echo ci_render_project( get_the_ID() );
		} elseif ( function_exists( 'ci_render_modern_blog_post' ) ) {
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
