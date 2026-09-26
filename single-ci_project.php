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
	?>
	<main id="main" <?php post_class( 'site-main' ); ?>>
		<div class="page-content entry-content">
			<?php the_content(); ?>
		</div>
	</main>
	<?php
}

get_footer();
