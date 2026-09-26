<?php
/**
 * Single project.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
$current_post = get_queried_object();
if ( $current_post && isset( $current_post->post_name ) ) {
	$matching_posts = get_posts(
		array(
			'name'        => $current_post->post_name,
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => 1,
		)
	);
	if ( ! empty( $matching_posts ) && $matching_posts[0]->ID !== $current_post->ID ) {
		wp_safe_redirect( get_permalink( $matching_posts[0]->ID ), 301 );
		exit;
	}
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
