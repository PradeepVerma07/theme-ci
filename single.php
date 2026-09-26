<?php
/**
 * Single Blog post template.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();

while ( have_posts() ) {
	the_post();
	$post_id  = get_the_ID();
	$tpl_slug = get_page_template_slug();
	$cats     = get_the_category( $post_id );
	$cat_slugs = array();
	if ( ! empty( $cats ) ) {
		foreach ( $cats as $c ) {
			$cat_slugs[] = strtolower( $c->slug );
			$cat_slugs[] = strtolower( $c->name );
		}
	}

	$is_case_study = ( 'template-case-study.php' === $tpl_slug ) || ( 'ci_project' === get_post_type() );
	if ( ! $is_case_study && ! empty( $cat_slugs ) ) {
		foreach ( $cat_slugs as $cs ) {
			if ( false !== strpos( $cs, 'case' ) || false !== strpos( $cs, 'work' ) || false !== strpos( $cs, 'project' ) ) {
				$is_case_study = true;
				break;
			}
		}
	}

	?>
	<main id="main" <?php post_class( 'site-main' ); ?>>
		<?php
		if ( $is_case_study && function_exists( 'ci_render_project' ) ) {
			echo ci_render_project( $post_id );
		} elseif ( function_exists( 'ci_render_modern_blog_post' ) ) {
			echo ci_render_modern_blog_post( $post_id );
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

