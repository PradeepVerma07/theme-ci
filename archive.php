<?php
/**
 * Category & Taxonomy Archive Template.
 * Renders case studies and posts as a 4-in-a-row grid of cards.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Get archive title & description
$archive_title = get_the_archive_title();
if ( is_category() ) {
	$archive_title = single_cat_title( '', false );
} elseif ( is_tax() ) {
	$archive_title = single_term_title( '', false );
}

$archive_desc = get_the_archive_description();
?>

<main id="main" class="site-main ci360-archive-page">
	<!-- Archive Header Banner -->
	<header class="ci360-archive-hero">
		<div class="wrap">
			<nav class="ci360-service-breadcrumb" aria-label="Breadcrumb" style="margin-bottom: 16px;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
				<span class="sep">&gt;</span>
				<a href="<?php echo esc_url( home_url( '/work/' ) ); ?>">Work</a>
				<span class="sep">&gt;</span>
				<span class="current"><?php echo esc_html( $archive_title ); ?></span>
			</nav>
			<div class="ci360-blog-kicker-light" style="margin-bottom: 12px;">
				<span>CATEGORY ARCHIVE</span>
			</div>
			<h1 class="ci360-archive-title" style="font-size: 42px; font-weight: 800; color: #0f172a; margin: 0 0 12px 0; line-height: 1.2;">
				<?php echo esc_html( $archive_title ); ?>
			</h1>
			<?php if ( ! empty( $archive_desc ) ) : ?>
				<div class="ci360-archive-desc" style="font-size: 18px; color: #475569; max-width: 720px; margin-top: 8px;">
					<?php echo wp_kses_post( $archive_desc ); ?>
				</div>
			<?php endif; ?>
		</div>
	</header>

	<!-- Archive Grid Content Section -->
	<section class="ci360-archive-container wrap" style="padding: 50px 0 90px 0;">
		<?php if ( have_posts() ) : ?>
			<div class="ci360-related-box">
				<div class="ci360-related-grid articles-grid four-col">
					<?php
					while ( have_posts() ) :
						the_post();
						$pid       = get_the_ID();
						$p_title   = get_the_title( $pid );
						$p_url     = get_permalink( $pid );
						$p_thumb   = get_the_post_thumbnail_url( $pid, 'medium_large' );
						$p_cats    = get_the_category( $pid );
						if ( empty( $p_cats ) ) {
							$p_terms = get_the_terms( $pid, 'ci_project_category' );
							$p_cat   = ( ! empty( $p_terms ) && ! is_wp_error( $p_terms ) ) ? $p_terms[0]->name : 'Case Study';
						} else {
							$p_cat = $p_cats[0]->name;
						}
						$p_excerpt = has_excerpt( $pid ) ? get_the_excerpt( $pid ) : wp_trim_words( get_the_content(), 16 );

						$img_tag = $p_thumb ? '<img src="' . esc_url( $p_thumb ) . '" alt="' . esc_attr( $p_title ) . '" loading="lazy">' : ci_star();
						?>
						<a class="ci360-related-card" href="<?php echo esc_url( $p_url ); ?>">
							<div class="ci360-related-media">
								<?php echo $img_tag; ?>
								<span class="ci360-related-cat"><?php echo esc_html( $p_cat ); ?></span>
							</div>
							<div class="ci360-related-body">
								<h3><?php echo esc_html( $p_title ); ?></h3>
								<p><?php echo esc_html( $p_excerpt ); ?></p>
								<span class="ci360-related-link">View Case Study <?php echo ci_arrow(); ?></span>
							</div>
						</a>
					<?php endwhile; ?>
				</div>

				<!-- Pagination Bar -->
				<div class="ci360-pagination-wrap" style="margin-top: 50px; text-align: center;">
					<?php
					echo paginate_links(
						array(
							'prev_text' => '&larr; Previous',
							'next_text' => 'Next &rarr;',
							'type'      => 'plain',
						)
					);
					?>
				</div>
			</div>
		<?php else : ?>
			<div class="ci360-no-posts" style="text-align: center; padding: 60px 20px;">
				<h2>No Case Studies Found</h2>
				<p style="color: #64748b; margin-bottom: 24px;">There are currently no case studies available under this category.</p>
				<a href="<?php echo esc_url( home_url( '/work/' ) ); ?>" class="text-link" style="display: inline-flex; align-items: center; gap: 8px; font-weight: 700;">
					Explore All Work <?php echo ci_arrow(); ?>
				</a>
			</div>
		<?php endif; ?>
	</section>
</main>

<?php
get_footer();
