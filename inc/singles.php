<?php
/**
 * Page-body renderers for single services, projects and insights, plus the site header/footer.
 * Used by the single-*.php templates and by the “current post” Elementor widgets (for Elementor Pro Theme Builder).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ci_render_service( $post_id ) {
	ob_start();

	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}

	$title       = get_the_title( $post_id );
	$permalink   = get_permalink( $post_id );
	$contact_url = get_permalink( ci_page_id( 'contact' ) );
	if ( ! $contact_url ) {
		$contact_url = home_url( '/contact/' );
	}

	// Service Category
	$cats     = get_the_terms( $post_id, 'ci_service_category' );
	$cat_name = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0]->name : 'OUR SERVICE';

	// Summary / Excerpt
	$summary = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : get_post_meta( $post_id, 'service_summary', true );
	if ( empty( $summary ) ) {
		$summary = 'Creating platform-led strategies and content that spark conversations, build communities, and strengthen brand engagement.';
	}

	// Featured Image
	$thumb_url = get_the_post_thumbnail_url( $post_id, 'full' );
	if ( ! $thumb_url ) {
		$thumb_url = CI360_URI . '/assets/images/studio-detail.webp';
	}

	// Check if post is built or being edited with Elementor
	$is_elementor = class_exists( '\Elementor\Plugin' ) && (
		\Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id ) ||
		\Elementor\Plugin::$instance->editor->is_edit_mode() ||
		\Elementor\Plugin::$instance->preview->is_preview_mode()
	);

	// Related 4 Services
	$rel_query = new WP_Query( array(
		'post_type'      => array( 'ci_service', 'post' ),
		'posts_per_page' => 4,
		'post__not_in'   => array( $post_id ),
		'post_status'    => 'publish',
	) );

	$related_services_html = '';
	if ( $rel_query->have_posts() ) {
		$idx = 0;
		$default_thumbs = array(
			CI360_URI . '/assets/images/studio-detail.webp',
			CI360_URI . '/assets/images/studio-hero.webp',
			CI360_URI . '/assets/images/station.webp',
			CI360_URI . '/assets/images/brand-icon.png',
		);
		while ( $rel_query->have_posts() ) {
			$rel_query->the_post();
			$rid       = get_the_ID();
			$r_title   = get_the_title( $rid );
			$r_url     = get_permalink( $rid );
			$r_thumb   = get_the_post_thumbnail_url( $rid, 'medium_large' );
			$r_summary = has_excerpt( $rid ) ? get_the_excerpt( $rid ) : wp_trim_words( get_the_content(), 15 );
			if ( ! $r_thumb ) {
				$r_thumb = $default_thumbs[ $idx % count( $default_thumbs ) ];
			}
			$idx++;

			$related_services_html .= '<div class="ci360-service-card-item">'
				. '<div class="ci360-service-card-thumb"><img src="' . esc_url( $r_thumb ) . '" alt="' . esc_attr( $r_title ) . '" loading="lazy"></div>'
				. '<div class="ci360-service-card-content">'
				. '<h3>' . esc_html( $r_title ) . '</h3>'
				. '<p>' . esc_html( $r_summary ) . '</p>'
				. '<a href="' . esc_url( $r_url ) . '" class="ci360-service-card-link">Learn More ' . ci_arrow() . '</a>'
				. '</div>'
				. '</div>';
		}
		wp_reset_postdata();
	}

	// Title accent formatting (highlight last word or gradient word)
	$words = explode( ' ', $title );
	if ( count( $words ) > 1 ) {
		$last_word       = array_pop( $words );
		$formatted_title = esc_html( implode( ' ', $words ) ) . ' <span class="ci360-title-gradient">' . esc_html( $last_word ) . '</span>';
	} else {
		$formatted_title = esc_html( $title );
	}

	// Dynamic Overview Data
	$ov_heading  = ci_get( 'service_overview_heading', $post_id ) ?: 'Turn Conversations Into <span class="ci360-title-gradient">Communities</span>';
	$ov_copy     = ci_get( 'service_overview_copy', $post_id ) ?: "Social media is more than just posting — it's about people, conversations, and real connections. We help brands show up with purpose, create engaging content, and build communities that drive meaningful business results.";
	$ov_features = ci_rows( 'service_overview_features', $post_id );

	// Dynamic Highlights Card Data
	$hl_title = ci_get( 'service_highlights_title', $post_id ) ?: 'Service Highlights';
	$hl_rows  = ci_rows( 'service_highlights_list', $post_id );

	// Dynamic What's Included Data
	$inc_heading = ci_get( 'service_included_heading', $post_id ) ?: 'Everything You Need to <span class="ci360-title-gradient">Grow on Social</span>';
	$inc_rows    = ci_rows( 'service_included_cards', $post_id );

	// Dynamic Our Approach Data
	$app_heading = ci_get( 'service_approach_heading', $post_id ) ?: 'A Strategic, <span class="ci360-title-gradient">Results-Driven Process</span>';
	$app_steps   = ci_rows( 'service_steps', $post_id );

	// Dynamic CTA Banner Data
	$cta_heading = ci_get( 'service_cta_heading', $post_id ) ?: 'Ready to grow your brand on social media?';
	$cta_sub     = ci_get( 'service_cta_sub', $post_id ) ?: 'Our team is here to understand your goals and create a tailored strategy that drives real results.';

	// Override image if specified in ACF
	$custom_img_id = ci_get( 'service_image', $post_id );
	if ( $custom_img_id ) {
		$img_url_meta = wp_get_attachment_image_url( $custom_img_id, 'full' );
		if ( $img_url_meta ) {
			$thumb_url = $img_url_meta;
		}
	}

	?>
	<div id="ci360-single-service-root" class="ci360-single-service-page">
		<!-- 1. HERO SECTION -->
		<?php
		echo ci_section( 'service-hero', array(
			'kicker'             => $cat_name,
			'title'              => $title,
			'lead'               => $summary,
			'cta_primary'        => 'Start a Conversation',
			'cta_primary_link'   => $contact_url,
			'cta_secondary'      => 'Contact Us',
			'cta_secondary_link' => $contact_url,
			'image'              => $thumb_url,
		) );
		?>

		<!-- 2. OVERVIEW SECTION -->
		<?php
		echo ci_section( 'service-overview', array(
			'kicker'           => 'OVERVIEW',
			'heading'          => $ov_heading,
			'copy'             => $ov_copy,
			'features'         => $ov_features,
			'highlights_title' => $hl_title,
			'highlights_list'  => $hl_rows,
			'card_button'      => 'Discuss Your Goals',
			'card_button_link' => $contact_url,
			'card_subtext'     => 'Get a tailored strategy for your brand.',
		) );
		?>

		<!-- 3. WHAT'S INCLUDED SECTION -->
		<?php
		echo ci_section( 'service-included', array(
			'kicker'  => "WHAT'S INCLUDED",
			'heading' => $inc_heading,
			'subtext' => 'From strategy to execution, we handle every part of your journey.',
			'cards'   => $inc_rows,
		) );
		?>

		<!-- 4. OUR APPROACH SECTION -->
		<?php
		echo ci_section( 'service-approach', array(
			'kicker'  => 'OUR APPROACH',
			'heading' => $app_heading,
			'intro'   => 'We combine strategy, creativity, and data to create experiences that deliver real business impact.',
			'steps'   => $app_steps,
		) );
		?>

		<!-- 5. DARK CTA BANNER -->
		<?php
		echo ci_section( 'service-cta', array(
			'kicker'      => "LET'S WORK TOGETHER",
			'heading'     => $cta_heading,
			'subtext'     => $cta_sub,
			'button'      => 'Contact Us',
			'button_link' => $contact_url,
			'subcaption'  => 'Talk to our experts today.',
		) );
		?>

		<!-- 6. ELEMENTOR & WORDPRESS EDITABLE CONTENT -->
		<section class="ci360-service-main-content">
			<div class="ci360-service-body entry-content <?php echo $is_elementor ? '' : 'wrap'; ?>">
				<?php the_content(); ?>
			</div>
		</section>

		<!-- 7. RELATED SERVICES SECTION -->
		<?php if ( ! empty( $related_services_html ) ) : ?>
			<section class="ci360-service-related wrap">
				<div class="ci360-section-header">
					<span class="ci360-sub-kicker">RELATED SERVICES</span>
					<h2 class="ci360-section-title">Explore More Ways We <span class="ci360-title-gradient">Help Brands Grow</span></h2>
				</div>
				<div class="ci360-services-grid-4">
					<?php echo $related_services_html; ?>
				</div>
			</section>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

function ci_render_project( $post_id ) {
	ob_start();

	$wp_post = get_post( $post_id );
	if ( ! $wp_post ) {
		return '';
	}

	$title         = get_the_title( $post_id );
	$permalink     = get_permalink( $post_id );
	$encoded_url   = urlencode( $permalink );
	$encoded_title = urlencode( $title );

	// Fetch category
	$cats = get_the_category( $post_id );
	if ( ! empty( $cats ) ) {
		$cat_name = $cats[0]->name;
	} else {
		$terms = get_the_terms( $post_id, 'ci_project_category' );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$cat_name = $terms[0]->name;
		} else {
			$cat_name = 'Case Study';
		}
	}

	// Excerpt / Headline
	$headline = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '';
	$p        = ci_project( $post_id );
	if ( empty( $headline ) && ! empty( $p['headline'] ) ) {
		$headline = $p['headline'];
	}

	// Featured Image URL
	$feat_img_url = get_the_post_thumbnail_url( $post_id, 'full' );
	if ( ! $feat_img_url && ! empty( $p['image'] ) ) {
		$feat_img_url = wp_get_attachment_image_url( $p['image'], 'full' );
	}

	// Prev & Next Posts (works for any post type!)
	$prev_post = get_previous_post();
	$next_post = get_next_post();

	$prev_data = null;
	if ( $prev_post ) {
		$prev_data = array(
			'name' => get_the_title( $prev_post->ID ),
			'url'  => get_permalink( $prev_post->ID ),
		);
	}

	$next_data = null;
	if ( $next_post ) {
		$next_data = array(
			'name' => get_the_title( $next_post->ID ),
			'url'  => get_permalink( $next_post->ID ),
		);
	}

	// Fallback to CPT ci_project IDs list if standard WP prev/next returns empty
	if ( ! $prev_data || ! $next_data ) {
		$ids   = ci_ids( 'ci_project' );
		$total = count( $ids );
		$pos   = array_search( $post_id, $ids, true );
		if ( false !== $pos && $total > 1 ) {
			if ( ! $prev_data ) {
				$prev_id   = $ids[ 0 === $pos ? $total - 1 : $pos - 1 ];
				$prev_proj = ci_project( $prev_id );
				$prev_data = array( 'name' => $prev_proj['name'], 'url' => $prev_proj['url'] );
			}
			if ( ! $next_data ) {
				$next_id   = $ids[ $pos === $total - 1 ? 0 : $pos + 1 ];
				$next_proj = ci_project( $next_id );
				$next_data = array( 'name' => $next_proj['name'], 'url' => $next_proj['url'] );
			}
		}
	}

	// Check if post is built or being edited with Elementor
	$is_elementor = class_exists( '\Elementor\Plugin' ) && (
		\Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id ) ||
		\Elementor\Plugin::$instance->editor->is_edit_mode() ||
		\Elementor\Plugin::$instance->preview->is_preview_mode()
	);

	// Custom field story blocks (fallback or legacy CPT content)
	$custom_story = '';
	if ( 'case' === $p['type'] && ! empty( $p['sections'] ) ) {
		foreach ( $p['sections'] as $r ) {
			$custom_story .= '<div class="ci360-case-story-block"><h3>' . ci_e( $r['title'] ) . '</h3><p>' . ci_e( $r['text'] ) . '</p></div>';
		}
	}

	// Project Gallery
	$gallery = '';
	if ( ! empty( $p['gallery'] ) ) {
		$items = '';
		foreach ( $p['gallery'] as $i => $im ) {
			$items .= '<button type="button" class="gallery-image tone-' . esc_attr( $p['tone'] ) . '" data-lightbox="' . esc_url( wp_get_attachment_image_url( $im, 'full' ) ) . '" aria-label="View creative image ' . ( $i + 1 ) . ' full size">' . ci_img( $im, $title . ' creative ' . ( $i + 1 ) ) . '<span>View Image ' . ci_arrow() . '</span></button>';
		}
		$gallery = '<div class="project-gallery-box"><div class="gallery-heading"><h4>PROJECT GALLERY</h4><span>' . ci_pad( count( $p['gallery'] ) ) . ' IMAGES</span></div><div class="gallery-grid ' . ( 1 === count( $p['gallery'] ) ? 'gallery-single' : '' ) . '">' . $items . '</div></div>';
	}

	// Related 4 Case Studies (filtered by category if available)
	$related_args = array(
		'post_type'      => array( 'ci_project', 'post' ),
		'posts_per_page' => 4,
		'post__not_in'   => array( $post_id ),
		'post_status'    => 'publish',
	);

	$terms = get_the_terms( $post_id, 'ci_project_category' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$related_args['tax_query'] = array(
			array(
				'taxonomy' => 'ci_project_category',
				'field'    => 'term_id',
				'terms'    => $terms[0]->term_id,
			),
		);
	} else {
		$cats = get_the_category( $post_id );
		if ( ! empty( $cats ) ) {
			$related_args['cat'] = $cats[0]->term_id;
		}
	}

	$rel_query = new WP_Query( $related_args );
	if ( ! $rel_query->have_posts() ) {
		unset( $related_args['tax_query'] );
		unset( $related_args['cat'] );
		$rel_query = new WP_Query( $related_args );
	}

	$related_cards = '';
	if ( $rel_query->have_posts() ) {
		while ( $rel_query->have_posts() ) {
			$rel_query->the_post();
			$rid       = get_the_ID();
			$r_title   = get_the_title( $rid );
			$r_url     = get_permalink( $rid );
			$r_thumb   = get_the_post_thumbnail_url( $rid, 'medium_large' );
			$r_cats    = get_the_category( $rid );
			if ( empty( $r_cats ) ) {
				$r_terms = get_the_terms( $rid, 'ci_project_category' );
				$r_cat   = ( ! empty( $r_terms ) && ! is_wp_error( $r_terms ) ) ? $r_terms[0]->name : 'Case Study';
			} else {
				$r_cat = $r_cats[0]->name;
			}
			$r_excerpt = has_excerpt( $rid ) ? get_the_excerpt( $rid ) : wp_trim_words( get_the_content(), 15 );

			$img_tag = $r_thumb ? '<img src="' . esc_url( $r_thumb ) . '" alt="' . esc_attr( $r_title ) . '" loading="lazy">' : ci_star();

			$related_cards .= '<a class="ci360-related-card" href="' . esc_url( $r_url ) . '">'
				. '<div class="ci360-related-media">' . $img_tag . '<span class="ci360-related-cat">' . esc_html( $r_cat ) . '</span></div>'
				. '<div class="ci360-related-body"><h3>' . esc_html( $r_title ) . '</h3><p>' . esc_html( $r_excerpt ) . '</p><span class="ci360-related-link">View Case Study ' . ci_arrow() . '</span></div>'
				. '</a>';
		}
		wp_reset_postdata();
	}

	?>
	<div id="ci360-case-study-root" class="ci360-case-study-page">
		<!-- 1. Hero Banner Header (ONLY output if NOT built with Elementor) -->
		<?php if ( ! $is_elementor ) : ?>
			<section class="ci360-hero-full-screen ci360-case-hero" <?php if ( $feat_img_url ) : ?>style="background-image: url('<?php echo esc_url( $feat_img_url ); ?>');"<?php endif; ?>>
				<div class="ci360-hero-overlay"></div>
				<div class="ci360-hero-full-content wrap">
					<div class="ci360-hero-left-align">
						<nav class="ci360-blog-breadcrumb-light" aria-label="Breadcrumb">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
							<span>/</span>
							<a href="<?php echo esc_url( home_url( '/work/' ) ); ?>">Work</a>
							<span>/</span>
							<span><?php echo esc_html( $title ); ?></span>
						</nav>
						<div class="ci360-blog-kicker-light">
							<span><?php echo esc_html( mb_strtoupper( $cat_name ) ); ?></span>
						</div>
						<h1 class="ci360-hero-full-title"><?php echo esc_html( $title ); ?></h1>
						<?php if ( ! empty( $headline ) ) : ?>
							<p class="ci360-hero-lead"><?php echo esc_html( $headline ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- 2. Main Case Study Story Content Area (FULL WIDTH - NO SIDEBAR) -->
		<section class="ci360-case-main-container">
			<article class="ci360-case-article ci360-case-full-width">
				<!-- WordPress / Elementor Main Content Area -->
				<div class="ci360-case-body entry-content <?php echo $is_elementor ? '' : 'wrap'; ?>">
					<?php the_content(); ?>
				</div>

				<!-- Legacy Custom Story Blocks (if present) -->
				<?php if ( ! empty( $custom_story ) ) : ?>
					<div class="ci360-case-story-blocks wrap">
						<?php echo $custom_story; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $gallery ) ) : ?>
					<div class="wrap">
						<?php echo $gallery; ?>
					</div>
				<?php endif; ?>

				<!-- 3. Related 4 Case Studies Grid AT THE END OF CONTENT -->
				<?php if ( ! empty( $related_cards ) ) : ?>
					<div class="ci360-related-box-container ci360-case-end-related wrap">
						<div class="ci360-related-box">
							<div class="ci360-related-header">
								<h2>Featured Case Studies</h2>
								<a href="<?php echo esc_url( home_url( '/work/' ) ); ?>" class="text-link">Explore all work <?php echo ci_arrow(); ?></a>
							</div>
							<div class="ci360-related-grid articles-grid four-col">
								<?php echo $related_cards; ?>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<!-- Professional Next & Previous Buttons Bar Below Content -->
				<nav class="ci360-case-nav-bar ci360-case-nav-pro wrap" aria-label="Case Study Navigation">
					<?php if ( ! empty( $prev_data ) ) : ?>
						<a href="<?php echo esc_url( $prev_data['url'] ); ?>" class="ci360-case-nav-btn prev">
							<span class="nav-arrow">&larr;</span>
							<div class="nav-content">
								<small>Previous Case Study</small>
								<strong><?php echo esc_html( $prev_data['name'] ); ?></strong>
							</div>
						</a>
					<?php else : ?>
						<div class="ci360-case-nav-btn prev disabled"></div>
					<?php endif; ?>

					<?php if ( ! empty( $next_data ) ) : ?>
						<a href="<?php echo esc_url( $next_data['url'] ); ?>" class="ci360-case-nav-btn next">
							<div class="nav-content text-right">
								<small>Next Case Study</small>
								<strong><?php echo esc_html( $next_data['name'] ); ?></strong>
							</div>
							<span class="nav-arrow">&rarr;</span>
						</a>
					<?php else : ?>
						<div class="ci360-case-nav-btn next disabled"></div>
					<?php endif; ?>
				</nav>

				<!-- Professional Share Icons Bar Below Content -->
				<div class="ci360-share-bar ci360-share-bar-pro wrap">
					<span class="ci360-share-label">Share this Case Study:</span>
					<div class="ci360-share-icons">
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $encoded_url; ?>" target="_blank" rel="noopener noreferrer" class="ci360-share-icon fb" title="Share on Facebook">
							<?php echo ci_social_icon_svg( 'facebook' ); ?>
						</a>
						<a href="https://twitter.com/intent/tweet?url=<?php echo $encoded_url; ?>&text=<?php echo $encoded_title; ?>" target="_blank" rel="noopener noreferrer" class="ci360-share-icon tw" title="Share on X">
							<?php echo ci_social_icon_svg( 'twitter' ); ?>
						</a>
						<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $encoded_url; ?>" target="_blank" rel="noopener noreferrer" class="ci360-share-icon li" title="Share on LinkedIn">
							<?php echo ci_social_icon_svg( 'linkedin' ); ?>
						</a>
						<a href="https://api.whatsapp.com/send?text=<?php echo $encoded_title; ?>%20<?php echo $encoded_url; ?>" target="_blank" rel="noopener noreferrer" class="ci360-share-icon wa" title="Share on WhatsApp">
							<?php echo ci_social_icon_svg( 'whatsapp' ); ?>
						</a>
						<button type="button" class="ci360-share-icon copy" title="Copy Link" onclick="navigator.clipboard.writeText('<?php echo esc_url( $permalink ); ?>'); alert('Link copied to clipboard!');">
							<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
						</button>
					</div>
				</div>
			</article>
		</section>
	</div>
	<?php
	return ob_get_clean();
}

function ci_render_insight( $post_id ) {
	ob_start();
	$post = get_post( $post_id );
	$a    = ci_insight( $post_id );
	$toc  = '';
	$body = '';

	$raw_content = $post ? trim( (string) $post->post_content ) : '';

	if ( ! empty( $raw_content ) ) {
		$body = '<div class="article-wp-content entry-content">' . apply_filters( 'the_content', $raw_content ) . '</div>';
	} else {
		foreach ( $a['sections'] as $i => $r ) {
			if ( ! empty( $r['title'] ) ) {
				$toc  .= '<a href="#section-' . $i . '">' . ci_pad( $i + 1 ) . ' ' . ci_e( $r['title'] ) . '</a>';
				$body .= '<section id="section-' . $i . '"><h2>' . ci_e( $r['title'] ) . '</h2>' . ci_paragraphs( $r['text'] ) . '</section>';
			} else {
				$body .= '<section id="section-' . $i . '">' . ( false !== strpos( (string) $r['text'], '<' ) ? $r['text'] : ci_paragraphs( $r['text'] ) ) . '</section>';
			}
		}
	}
	$others = '';
	$n      = 0;
	foreach ( ci_ids( 'ci_insight' ) as $aid ) {
		if ( $aid !== $a['id'] ) {
			$others .= ci_article_card( ci_insight( $aid ), $n++ );
		}
	}
	?><article><header class="article-hero wrap"><?php echo ci_breadcrumb( 'Insights / ' . $a['title'] ); ?><?php echo ci_label( mb_strtoupper( $a['kicker'] ), $a['status'] ); ?><h1 class="article-display" data-title><?php echo ci_e( $a['title'] ); ?></h1><p class="article-standfirst"><?php echo ci_e( $a['intro'] ); ?></p><div class="article-byline"><span><?php echo ci_e( ci_opt( 'tpl_insight_byline' ) ); ?></span><span><?php echo ci_e( $a['read'] ); ?> read</span><button class="share-article"><?php echo ci_e( ci_opt( 'tpl_insight_share' ) ); ?> <?php echo ci_arrow(); ?></button></div><div class="article-banner tone-<?php echo esc_attr( $a['tone'] ); ?>"><?php echo ci_img( $a['image'], $a['title'] . ' editorial visual', '', true ); ?><div></div><span><?php echo ci_e( $a['title'] ); ?></span><?php echo ci_star(); ?></div></header><div class="article-reading wrap<?php echo empty( $toc ) ? ' no-toc' : ''; ?>"><?php if ( ! empty( $toc ) ) : ?><aside class="article-toc"><span class="small-label"><?php echo ci_e( ci_opt( 'tpl_insight_toc' ) ); ?></span><?php echo $toc; ?><div class="reading-progress"><i></i></div><span class="small-label"><?php echo ci_e( ci_opt( 'tpl_insight_toc_end' ) ); ?></span></aside><?php endif; ?><div class="article-body"><?php echo $body; ?><?php if ( $a['note'] ) : ?><div class="article-editorial-note"><strong><?php echo ci_e( ci_opt( 'tpl_insight_note_title' ) ); ?></strong><p><?php echo ci_e( $a['note'] ); ?></p></div><?php endif; ?><?php echo ci_btn( ci_opt( 'tpl_insight_button' ), get_permalink( ci_page_id( 'contact' ) ), 'outline' ); ?></div></div></article><?php if ( $others ) : ?><section class="section wrap"><div class="section-heading"><?php echo ci_sec_label( 'tpl_insight_related', 'option' ); ?><h2><?php echo ci_html( ci_opt( 'tpl_insight_related_heading' ) ); ?></h2></div><div class="articles-grid"><?php echo $others; ?></div></section><?php endif; ?>
	<?php
	return ob_get_clean();
}

/** Modern Blog Post Template renderer with full-screen hero and sidebar. */
function ci_render_modern_blog_post( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}

	$title     = get_the_title( $post_id );
	$permalink = get_permalink( $post_id );
	$cats      = get_the_category( $post_id );
	$cat_name  = ! empty( $cats ) ? $cats[0]->name : 'Blog';
	$cat_id    = ! empty( $cats ) ? $cats[0]->term_id : 0;
	$date      = get_the_date( 'M j, Y', $post_id );

	$content_raw = $post->post_content;
	$word_count  = str_word_count( wp_strip_all_tags( $content_raw ) );
	$read_time   = max( 1, (int) ceil( $word_count / 200 ) ) . ' min read';

	$thumb_url = get_the_post_thumbnail_url( $post_id, 'full' );
	if ( ! $thumb_url ) {
		$thumb_url = CI360_URI . '/assets/images/studio-detail.webp';
	}

	$prev_post = get_previous_post();
	$next_post = get_next_post();

	$all_cats         = get_categories( array( 'hide_empty' => true, 'number' => 8 ) );
	$sidebar_projects = ci_posts( 'ci_project', array( 'posts_per_page' => 3 ) );
	$sidebar_services = ci_posts( 'ci_service', array( 'posts_per_page' => 4 ) );

	$related_args = array(
		'post_type'      => 'post',
		'posts_per_page' => 4,
		'post__not_in'   => array( $post_id ),
		'post_status'    => 'publish',
	);
	if ( $cat_id ) {
		$related_args['cat'] = $cat_id;
	}
	$related_query = new WP_Query( $related_args );
	if ( ! $related_query->have_posts() && $cat_id ) {
		unset( $related_args['cat'] );
		$related_query = new WP_Query( $related_args );
	}

	$related_html = '';
	if ( $related_query->have_posts() ) {
		$idx = 0;
		while ( $related_query->have_posts() ) {
			$related_query->the_post();
			$rel_id        = get_the_ID();
			$rel_a         = ci_insight( $rel_id );
			$related_html .= ci_article_card( $rel_a, $idx++ );
		}
		wp_reset_postdata();
	}

	ob_start();
	?>
	<article class="ci360-full-blog-single">
		<!-- 1. FULL SCREEN HERO BANNER -->
		<header class="ci360-blog-hero-full" style="background-image: url('<?php echo esc_url( $thumb_url ); ?>');">
			<div class="ci360-hero-full-overlay"></div>
			<div class="ci360-hero-full-content wrap">
				<div class="ci360-hero-left-align">
					<nav class="ci360-blog-breadcrumb-light" aria-label="Breadcrumb">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
						<span class="sep">/</span>
						<a href="<?php echo esc_url( get_permalink( ci_page_id( 'insights' ) ) ); ?>">Blog</a>
						<span class="sep">/</span>
						<span class="current"><?php echo esc_html( $title ); ?></span>
					</nav>

					<div class="ci360-blog-kicker-light">
						<span class="ci360-cat-badge-light"><?php echo esc_html( $cat_name ); ?></span>
						<span class="ci360-meta-badge-light"><?php echo esc_html( $read_time ); ?></span>
						<span class="ci360-meta-badge-light"><?php echo esc_html( $date ); ?></span>
					</div>

					<h1 class="ci360-hero-full-title"><?php echo esc_html( $title ); ?></h1>
				</div>
			</div>
		</header>

		<!-- 2. MAIN LAYOUT: LEFT CONTENT + RIGHT SIDEBAR -->
		<div class="ci360-blog-layout wrap">
			<!-- LEFT: ARTICLE CONTENT & NAV -->
			<main class="ci360-blog-main-content">
				<div class="ci360-blog-body entry-content">
					<?php the_content(); ?>
				</div>

				<!-- Social Share Bar -->
				<div class="ci360-blog-share-section">
					<span class="ci360-share-title">Share Article:</span>
					<div class="ci360-share-buttons">
						<a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( $permalink ); ?>&text=<?php echo urlencode( $title ); ?>" target="_blank" rel="noopener" aria-label="Share on X" class="ci360-share-btn share-x">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
						</a>
						<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode( $permalink ); ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn" class="ci360-share-btn share-linkedin">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
						</a>
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( $permalink ); ?>" target="_blank" rel="noopener" aria-label="Share on Facebook" class="ci360-share-btn share-facebook">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
						</a>
						<a href="https://api.whatsapp.com/send?text=<?php echo urlencode( $title . ' ' . $permalink ); ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp" class="ci360-share-btn share-whatsapp">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
						</a>
						<button type="button" class="ci360-share-btn share-copy" onclick="navigator.clipboard.writeText('<?php echo esc_js( $permalink ); ?>'); alert('Link copied to clipboard!');" aria-label="Copy link">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
						</button>
					</div>
				</div>

				<!-- Next / Previous Article Cards -->
				<nav class="ci360-blog-nav" aria-label="Post Navigation">
					<?php if ( $prev_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="ci360-nav-card nav-prev">
							<span class="nav-label">&larr; Previous Article</span>
							<span class="nav-title"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></span>
						</a>
					<?php else : ?>
						<div class="ci360-nav-card nav-prev disabled"></div>
					<?php endif; ?>

					<?php if ( $next_post ) : ?>
						<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="ci360-nav-card nav-next">
							<span class="nav-label">Next Article &rarr;</span>
							<span class="nav-title"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></span>
						</a>
					<?php endif; ?>
				</nav>
			</main>

			<!-- RIGHT: SIDEBAR WIDGETS -->
			<aside class="ci360-blog-sidebar">
				<!-- Category Filters -->
				<?php if ( ! empty( $all_cats ) ) : ?>
					<div class="ci360-sidebar-widget">
						<h3 class="widget-title">Categories</h3>
						<ul class="ci360-cat-list">
							<?php foreach ( $all_cats as $c ) : ?>
								<li class="<?php echo $c->term_id === $cat_id ? 'active' : ''; ?>">
									<a href="<?php echo esc_url( get_category_link( $c->term_id ) ); ?>">
										<span><?php echo esc_html( $c->name ); ?></span>
										<small><?php echo (int) $c->count; ?></small>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<!-- Featured Case Studies -->
				<?php if ( ! empty( $sidebar_projects ) ) : ?>
					<div class="ci360-sidebar-widget">
						<h3 class="widget-title">Featured Case Studies</h3>
						<div class="ci360-sidebar-projects">
							<?php foreach ( $sidebar_projects as $p_item ) : ?>
								<?php
								$proj_data = ci_project( $p_item );
								$p_img_id  = $proj_data['image'] ? $proj_data['image'] : $proj_data['front'];
								?>
								<a href="<?php echo esc_url( $proj_data['url'] ); ?>" class="ci360-sidebar-project-item">
									<span class="sidebar-proj-img">
										<?php echo ci_img( $p_img_id, $proj_data['name'] ); ?>
									</span>
									<div>
										<span class="sidebar-proj-cat"><?php echo esc_html( $proj_data['category'] ); ?></span>
										<h4><?php echo esc_html( $proj_data['name'] ); ?></h4>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<!-- Key Services -->
				<?php if ( ! empty( $sidebar_services ) ) : ?>
					<div class="ci360-sidebar-widget">
						<h3 class="widget-title">Core Services</h3>
						<ul class="ci360-sidebar-services">
							<?php foreach ( $sidebar_services as $s_item ) : ?>
								<?php $srv_data = ci_service( $s_item ); ?>
								<li>
									<a href="<?php echo esc_url( $srv_data['url'] ); ?>">
										<span><?php echo esc_html( $srv_data['title'] ); ?></span>
										<?php echo ci_arrow(); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<!-- Consultation CTA Widget -->
				<div class="ci360-sidebar-cta-box">
					<span class="cta-kicker">STRATEGIC GROWTH</span>
					<h3>Ready to elevate your brand?</h3>
					<p>Let's turn your vision into measurable digital impact.</p>
					<a href="<?php echo esc_url( get_permalink( ci_page_id( 'contact' ) ) ); ?>" class="ci360-cta-button">Start a Conversation <?php echo ci_arrow(); ?></a>
				</div>
			</aside>
		</div>

		<!-- 3. BOTTOM 4-IN-A-ROW RELATED READS SECTION IN BOX -->
		<?php if ( $related_html ) : ?>
			<section class="ci360-blog-related-section wrap">
				<div class="ci360-related-box">
					<div class="section-heading">
						<div class="section-label"><span>04</span><span>RELATED READS</span></div>
						<h2>Related Articles & Case Studies</h2>
					</div>
					<div class="articles-grid four-col">
						<?php echo $related_html; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
	return ob_get_clean();
}

/** Site header: skip link, bar, desktop navigation and full-screen menu. */
function ci_render_header() {
	$ci_email = ci_opt( 'opt_email' );
	ob_start();
	?><a class="skip-link" href="#main">Skip to content</a><header class="site-header"><?php echo ci_site_logo( 'header' ); ?><nav class="desktop-nav" aria-label="Primary navigation"><?php
foreach ( ci_menu( 'desktop' ) as $ci_item ) {
	echo '<a href="' . esc_url( $ci_item[0] ) . '" ' . ( ci_is_current( $ci_item[0] ) ? 'aria-current="page"' : '' ) . '>' . ci_e( $ci_item[1] ) . '</a>';
}
?></nav><div class="nav-right"><button class="menu-toggle" aria-label="Open navigation" aria-expanded="false" aria-controls="menu-dialog"><span></span><span></span></button></div></header><div class="scroll-progress" aria-hidden="true"></div><dialog class="menu-dialog" id="menu-dialog"><div class="menu-top"><span class="small-label"><?php echo ci_e( ci_opt( 'opt_menu_label' ) ); ?></span><button class="menu-close" aria-label="Close navigation">Close <span>&times;</span></button></div><div class="menu-layout"><nav aria-label="Expanded navigation"><?php
foreach ( ci_menu( 'overlay' ) as $ci_i => $ci_item ) {
	echo '<a href="' . esc_url( $ci_item[0] ) . '"><small>' . ci_pad( $ci_i + 1 ) . '</small>' . ci_e( $ci_item[1] ) . '<span>' . ci_arrow() . '</span></a>';
}
?></nav><div class="menu-art"><?php echo ci_img( ci_opt( 'opt_menu_image' ) ); ?><p><?php echo ci_html( ci_opt( 'opt_menu_text' ) ); ?></p></div></div><div class="menu-bottom"><a href="mailto:<?php echo esc_attr( $ci_email ); ?>"><?php echo ci_e( $ci_email ); ?></a><span><?php echo ci_e( ci_opt( 'opt_locations_line' ) ); ?></span></div></dialog><?php
	return ob_get_clean();
}

/** Site footer. */
function ci_render_footer() {
	$id = get_queried_object_id();

	$hide_cta = '1' === (string) ci_get( 'hide_footer_cta', $id );

	$label_1 = ci_get( 'footer_label_1', $id );
	if ( '' === (string) $label_1 ) {
		$label_1 = ci_opt( 'opt_footer_label_1' );
	}

	$label_2 = ci_get( 'footer_label_2', $id );
	if ( '' === (string) $label_2 ) {
		$label_2 = ci_opt( 'opt_footer_label_2' );
	}

	$heading = ci_get( 'footer_heading', $id );
	if ( '' === (string) $heading ) {
		$heading = ci_opt( 'opt_footer_heading' );
	}

	$link = ci_get( 'footer_link', $id );
	if ( '' === (string) $link ) {
		$link = ci_opt( 'opt_footer_link' );
	}

	$sticker = ci_get( 'footer_sticker', $id );
	if ( '' === (string) $sticker ) {
		$sticker = ci_opt( 'opt_footer_sticker' );
	}

	$about = ci_opt( 'opt_footer_about' );
	if ( empty( $about ) ) {
		$about = 'CI360 Degrees is an integrated digital marketing and strategic communication agency built around the power of strategic storytelling.';
	}

	$nav_title = ci_opt( 'opt_footer_explore' );
	if ( empty( $nav_title ) ) {
		$nav_title = 'Navigation';
	}

	$caps_title = ci_opt( 'opt_footer_caps_title' );
	if ( empty( $caps_title ) ) {
		$caps_title = 'Key Capabilities';
	}

	$caps_raw = ci_opt( 'opt_footer_capabilities' );
	if ( ! empty( $caps_raw ) ) {
		$caps = array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $caps_raw ) ) );
	} else {
		$caps = array(
			'Strategic Storytelling',
			'Social Media Marketing',
			'Performance Marketing',
			'SEO, AI Search',
			'Local Visibility',
			'Content, Creative',
			'Campaigns',
			'Branding',
			'Design',
			'Websites',
			'Digital Experiences',
			'Podcast Production',
			'Marketing',
		);
	}

	$locs_title = ci_opt( 'opt_footer_locs_title' );
	if ( empty( $locs_title ) ) {
		$locs_title = 'Locations';
	}

	$contact_title = ci_opt( 'opt_footer_contact_title' );
	if ( empty( $contact_title ) ) {
		$contact_title = 'Direct Contact';
	}

	$ci_email  = ci_opt( 'opt_email' );
	$ci_email2 = ci_opt( 'opt_email_2' );
	if ( empty( $ci_email2 ) ) {
		$ci_email2 = 'aashit.shah@ci360degrees.com';
	}
	if ( empty( $ci_email ) ) {
		$ci_email = 'pramit.ghosh@ci360degrees.com';
	}

	$back_top_label = ci_opt( 'opt_footer_back_top_label' );
	if ( empty( $back_top_label ) ) {
		$back_top_label = 'Back to top';
	}

	$logo_id = ci_opt( 'opt_footer_logo' );
	$logo_w  = (int) ci_opt( 'opt_footer_logo_width' );

	ob_start();
	?>
	<footer class="footer ci360-new-footer">
		<?php if ( ! $hide_cta && ( $heading || $label_1 || $label_2 ) ) : ?>
			<div class="footer-cta wrap">
				<div>
					<?php echo ci_label( $label_1, $label_2 ); ?>
					<a class="footer-head" href="<?php echo esc_url( ci_url( $link ) ); ?>">
						<?php echo ci_html( $heading ); ?>
						<span><?php echo ci_arrow(); ?></span>
					</a>
				</div>
				<?php if ( $sticker ) : ?>
					<div class="footer-sticker" aria-hidden="true">
						<?php echo ci_star(); ?>
						<span><?php echo ci_html( $sticker ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="footer-lower wrap ci360-footer-grid">
			<!-- Col 1: Brand & Bio -->
			<div class="ci360-fcol ci360-fcol-brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ci360-footer-logo" aria-label="Home">
					<?php if ( $logo_id ) : ?>
						<?php echo ci_img( $logo_id, ci_opt( 'opt_brand' ), '', array( 'style' => 'width:' . ( $logo_w ? $logo_w : 160 ) . 'px; height:auto;' ) ); ?>
					<?php else : ?>
						<svg width="60" height="60" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="50" cy="50" r="42" stroke="url(#ci360_grad)" stroke-width="8" stroke-linecap="round"/>
							<line x1="50" y1="50" x2="80" y2="20" stroke="url(#ci360_grad)" stroke-width="8" stroke-linecap="round"/>
							<defs>
								<linearGradient id="ci360_grad" x1="0" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
									<stop stop-color="#3b82f6"/>
									<stop offset="1" stop-color="#22d3ee"/>
								</linearGradient>
							</defs>
						</svg>
					<?php endif; ?>
				</a>
				<p class="ci360-footer-about"><?php echo ci_e( $about ); ?></p>
			</div>

			<!-- Col 2: Navigation -->
			<div class="ci360-fcol ci360-fcol-nav">
				<h4 class="ci360-fhead"><?php echo ci_e( $nav_title ); ?></h4>
				<ul class="ci360-flist">
					<?php
					$nav_items = ci_menu( 'explore' );
					if ( empty( $nav_items ) ) {
						$nav_items = ci_menu( 'footer' );
					}
					if ( ! empty( $nav_items ) ) {
						foreach ( $nav_items as $ci_item ) {
							echo '<li><a href="' . esc_url( $ci_item[0] ) . '">' . ci_e( $ci_item[1] ) . '</a></li>';
						}
					} else {
						?>
						<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">ABOUT</a></li>
						<li><a href="<?php echo esc_url( home_url( '/founders/' ) ); ?>">FOUNDERS</a></li>
						<li><a href="<?php echo esc_url( home_url( '/services/' ) ); ?>">SERVICES</a></li>
						<li><a href="<?php echo esc_url( home_url( '/work/' ) ); ?>">STORIES</a></li>
						<li><a href="<?php echo esc_url( home_url( '/insights/' ) ); ?>">BLOG</a></li>
						<?php
					}
					?>
				</ul>
			</div>

			<!-- Col 3: Key Capabilities -->
			<div class="ci360-fcol ci360-fcol-caps">
				<h4 class="ci360-fhead"><?php echo ci_e( $caps_title ); ?></h4>
				<ul class="ci360-flist">
					<?php foreach ( $caps as $cap_item ) : ?>
						<li><?php echo ci_e( $cap_item ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Col 4: Locations -->
			<div class="ci360-fcol ci360-fcol-locs">
				<h4 class="ci360-fhead"><?php echo ci_e( $locs_title ); ?></h4>
				<?php
				$offices = ci_rows( 'opt_offices', 'option' );
				if ( ! empty( $offices ) ) {
					foreach ( $offices as $office ) {
						$flag = ( 'US' === strtoupper( $office['country_code'] ?? '' ) ) ? '🇺🇸' : '🇮🇳';
						?>
						<div class="ci360-loc-group" style="margin-bottom:12px;">
							<span class="ci360-flag-head"><?php echo $flag; ?> <strong><?php echo ci_e( $office['city'] ); ?></strong></span>
							<?php if ( ! empty( $office['address'] ) ) : ?>
								<div class="ci360-loc-block">
									<p><?php echo ci_e( $office['address'] ); ?></p>
								</div>
							<?php endif; ?>
						</div>
						<?php
					}
				} else {
					?>
					<div class="ci360-loc-group">
						<span class="ci360-flag-head">🇮🇳 <strong>India</strong></span>
						<div class="ci360-loc-block">
							<strong>Ahmedabad</strong>
							<p>203 – 204, Devashish Complex, Nr. Hotel Kalssic Gold, Off. C.G Road, Ahmedabad, India 380009</p>
						</div>
						<div class="ci360-loc-block" style="margin-top:6px;">
							<strong>Delhi</strong>
						</div>
					</div>
					<div class="ci360-loc-group" style="margin-top:16px;">
						<span class="ci360-flag-head">🇺🇸 <strong>International</strong></span>
						<div class="ci360-loc-block">
							<strong>Greenville, USA</strong>
						</div>
					</div>
					<?php
				}
				?>
			</div>

			<!-- Col 5: Direct Contact -->
			<div class="ci360-fcol ci360-fcol-contact">
				<h4 class="ci360-fhead"><?php echo ci_e( $contact_title ); ?></h4>
				<ul class="ci360-contact-list">
					<?php if ( $ci_email ) : ?>
						<li>
							<a href="mailto:<?php echo esc_attr( $ci_email ); ?>">
								<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/></svg>
								<?php echo ci_e( $ci_email ); ?>
							</a>
						</li>
					<?php endif; ?>
					<?php if ( $ci_email2 ) : ?>
						<li>
							<a href="mailto:<?php echo esc_attr( $ci_email2 ); ?>">
								<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/></svg>
								<?php echo ci_e( $ci_email2 ); ?>
							</a>
						</li>
					<?php endif; ?>
				</ul>
				<div class="ci360-social-icons">
					<?php
					$socials = ci_rows( 'opt_socials', 'option' );
					if ( ! empty( $socials ) ) {
						foreach ( $socials as $soc ) {
							$soc_label = trim( (string) ( $soc['label'] ?? '' ) );
							$soc_url   = trim( (string) ( $soc['url'] ?? '#' ) );
							$icon_svg  = ci_social_icon_svg( $soc_label, $soc_url );
							echo '<a href="' . esc_url( $soc_url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $soc_label ? $soc_label : 'Social link' ) . '">' . $icon_svg . '</a>';
						}
					} else {
						?>
						<a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook">
							<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
						</a>
						<a href="https://linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn">
							<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
						</a>
						<a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram">
							<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
						</a>
						<a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube">
							<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" fill="#ffffff"/></svg>
						</a>
						<?php
					}
					?>
				</div>
			</div>
		</div>

		<div class="footer-bottom wrap">
			<span>
				<?php
				$ci_copy = strtr( (string) ci_opt( 'opt_footer_copyright' ), array( '{year}' => gmdate( 'Y' ), '{brand}' => ci_opt( 'opt_brand' ) ) );
				echo ci_e( $ci_copy );
				?>
			</span>
			<div>
				<?php
				foreach ( ci_menu( 'legal' ) as $ci_item ) {
					echo '<a href="' . esc_url( $ci_item[0] ) . '">' . ci_e( $ci_item[1] ) . '</a>';
				}
				?>
				<button class="motion-toggle" aria-pressed="false">Motion <span>on</span></button>
				<button class="back-top" aria-label="<?php echo esc_attr( $back_top_label ); ?>"><?php echo ci_e( $back_top_label ); ?> <?php echo ci_arrow(); ?></button>
			</div>
		</div>
	</footer>
	<?php
	return ob_get_clean();
}
