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
$s     = ci_service( $post_id );
$ids   = ci_ids( 'ci_service' );
$n     = count( $ids );
$index = $s['index'];

// Related services: chosen, or the prototype's pattern (+1, +3, +5).
$related_ids = array_map( 'intval', (array) ci_get( 'service_related', $s['id'] ) );
if ( ! array_filter( $related_ids ) && $n > 1 ) {
	$related_ids = array( $ids[ ( $index + 1 ) % $n ], $ids[ ( $index + 3 ) % $n ], $ids[ ( $index + 5 ) % $n ] );
}
$related = '';
foreach ( array_filter( $related_ids ) as $rid ) {
	$related .= ci_service_card( ci_service( $rid ) );
}

// Related work: chosen, or two featured projects.
$work_ids = array_map( 'intval', (array) ci_get( 'service_projects', $s['id'] ) );
if ( ! array_filter( $work_ids ) ) {
	$feat     = ci_featured_projects();
	$work_ids = $feat ? array( $feat[ $index % count( $feat ) ], $feat[ ( $index + 1 ) % count( $feat ) ] ) : array();
}
$work = '';
foreach ( array_filter( $work_ids ) as $pid ) {
	$work .= ci_project_card( ci_project( $pid ) );
}

$deliverables = '';
foreach ( $s['tags'] as $i => $t ) {
	$deliverables .= '<div class="deliverable" data-reveal><span>' . ci_pad( $i + 1 ) . '</span><h3>' . ci_e( $t ) . '</h3>' . ci_arrow() . '</div>';
}
$steps = '';
foreach ( $s['steps'] as $i => $r ) {
	$steps .= '<article data-reveal><span class="step-num">0' . ( $i + 1 ) . '</span><h3>' . ci_e( $r['title'] ) . '</h3><p>' . ci_e( $r['text'] ) . '</p></article>';
}
$contact = get_permalink( ci_page_id( 'contact' ) );
$sec     = function ( $p, $h2attr = ' data-reveal', $intro = false ) {
	return '<div class="section-heading' . ( $intro ? ' heading-row' : '' ) . '">' . ci_sec_label( $p, 'option' ) . '<h2' . $h2attr . '>' . ci_html( ci_opt( $p . '_heading' ) ) . '</h2>' . ( $intro ? '<p>' . ci_e( ci_opt( $p . '_intro' ) ) . '</p>' : '' ) . '</div>';
};
?><section class="page-hero wrap service-detail-hero"><?php echo ci_breadcrumb( 'Services / ' . $s['title'] ); ?><div class="service-detail-grid"><div><?php echo ci_label( ci_opt( 'tpl_service_capability' ) . ' ' . $s['number'], ci_opt( 'tpl_service_label_2' ) ); ?><h1 class="detail-display" data-title><?php echo ci_e( $s['title'] ); ?></h1><p class="service-lead"><?php echo ci_e( $s['summary'] ); ?></p><?php echo ci_btn( sprintf( ci_opt( 'tpl_service_cta' ), mb_strtolower( $s['group'] ) ), add_query_arg( 'service', $s['slug'], $contact ) ); ?></div><div class="service-detail-art" data-reveal><?php echo ci_service_visual( $s ); ?></div></div></section><section class="section wrap service-intro"><div><?php echo ci_label( ci_opt( 'tpl_service_opportunity' ), mb_strtoupper( $s['group'] ) ); ?><h2 data-reveal><?php echo ci_e( $s['headline'] ); ?></h2></div><div><p class="large-copy"><?php echo ci_e( $s['body'] ); ?></p><p><?php echo ci_e( ci_opt( 'tpl_service_connected' ) ); ?></p></div></section><section class="deliverables-section section tone-<?php echo esc_attr( $s['tone'] ); ?>"><div class="wrap"><?php echo $sec( 'tpl_service_deliver' ); ?><div class="deliverables-grid"><?php echo $deliverables; ?></div><p class="scope-note"><?php echo ci_e( ci_opt( 'tpl_service_scope' ) ); ?></p></div></section><section class="section wrap"><?php echo $sec( 'tpl_service_steps' ); ?><div class="service-steps"><?php echo $steps; ?></div></section><section class="section dark-section"><div class="wrap"><?php echo $sec( 'tpl_service_related', '', true ); ?><div class="services-grid related-services"><?php echo $related; ?></div></div></section><section class="section wrap"><?php echo $sec( 'tpl_service_work' ); ?><div class="project-grid two-col"><?php echo $work; ?></div></section><?php echo ci_compact_cta( sprintf( ci_opt( 'tpl_service_cta_text' ), mb_strtolower( $s['title'] ) ) ); ?>
	<?php
	return ob_get_clean();
}

function ci_render_project( $post_id ) {
	ob_start();
	$p       = ci_project( $post_id );
	$ids     = ci_ids( 'ci_project' );
	$total   = count( $ids );
	$pos     = array_search( $p['id'], $ids, true );

	// Next & Prev projects
	$prev_id   = $ids[ ( false === $pos || 0 === $pos ? $total - 1 : $pos - 1 ) ];
	$next_id   = $ids[ ( false === $pos || $pos === $total - 1 ? 0 : $pos + 1 ) ];
	$prev_proj = ci_project( $prev_id );
	$next_proj = ci_project( $next_id );

	// Featured image URL
	$feat_img_id  = get_post_thumbnail_id( $post_id );
	if ( ! $feat_img_id && ! empty( $p['image'] ) ) {
		$feat_img_id = $p['image'];
	}
	$feat_img_url = $feat_img_id ? wp_get_attachment_image_url( $feat_img_id, 'full' ) : ci_url( 'station.webp' );

	// Project Category
	$cat_name = ! empty( $p['category'] ) ? $p['category'] : 'Case Study';
	$permalink = get_permalink( $post_id );
	$encoded_url   = urlencode( $permalink );
	$encoded_title = urlencode( $p['name'] );

	// Story content
	$is_case = 'case' === $p['type'];
	if ( $is_case && ! empty( $p['sections'] ) ) {
		$story = '';
		foreach ( $p['sections'] as $r ) {
			$story .= '<div class="ci360-case-story-block"><h3>' . ci_e( $r['title'] ) . '</h3><p>' . ci_e( $r['text'] ) . '</p></div>';
		}
	} else {
		$t  = $p['term'];
		$p1 = ( $t && ci_term_get( 'cat_p1', $t ) ) ? ci_term_get( 'cat_p1', $t ) : ci_opt( 'opt_sector_p1' );
		$p2 = ( $t && ci_term_get( 'cat_p2', $t ) ) ? ci_term_get( 'cat_p2', $t ) : ci_opt( 'opt_sector_p2' );
		$story = '<p class="large-copy">' . ci_e( $p['summary'] ) . '</p><h3>' . ci_e( ci_opt( 'perspective' === $p['type'] ? 'tpl_project_lens' : 'tpl_project_direction' ) ) . '</h3>' . ci_paragraphs( array( $p1, $p2 ) );
	}

	// Gallery
	$gallery = '';
	if ( ! empty( $p['gallery'] ) ) {
		$items = '';
		foreach ( $p['gallery'] as $i => $im ) {
			$items .= '<button type="button" class="gallery-image tone-' . esc_attr( $p['tone'] ) . '" data-lightbox="' . esc_url( wp_get_attachment_image_url( $im, 'full' ) ) . '" aria-label="View ' . esc_attr( $p['name'] ) . ' image ' . ( $i + 1 ) . ' full size">' . ci_img( $im, $p['name'] . ' creative ' . ( $i + 1 ) ) . '<span>' . ci_e( ci_opt( 'tpl_project_view' ) ) . ' ' . ci_arrow() . '</span></button>';
		}
		$gallery = '<div class="project-gallery-box"><div class="gallery-heading"><h4>PROJECT GALLERY</h4><span>' . ci_pad( count( $p['gallery'] ) ) . ' IMAGES</span></div><div class="gallery-grid ' . ( 1 === count( $p['gallery'] ) ? 'gallery-single' : '' ) . '">' . $items . '</div></div>';
	}

	// Related 4 Case Studies (exclude current)
	$related_ids = array_diff( $ids, array( $post_id ) );
	$related_ids = array_slice( array_values( $related_ids ), 0, 4 );
	$related_cards = '';
	foreach ( $related_ids as $rid ) {
		$rp = ci_project( $rid );
		$rp_img = ci_img( $rp['image'], $rp['name'] );
		$related_cards .= '<a class="ci360-related-card" href="' . esc_url( $rp['url'] ) . '">'
			. '<div class="ci360-related-media">' . ( $rp_img ? $rp_img : ci_star() ) . '<span class="ci360-related-cat">' . ci_e( $rp['category'] ) . '</span></div>'
			. '<div class="ci360-related-body"><h3>' . ci_e( $rp['name'] ) . '</h3><p>' . ci_e( $rp['headline'] ) . '</p><span class="ci360-related-link">View Case Study ' . ci_arrow() . '</span></div>'
			. '</a>';
	}

	// Right Sidebar Content
	$terms = get_terms( array( 'taxonomy' => 'ci_project_category', 'hide_empty' => false ) );
	$cat_widget = '';
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		$cat_links = '';
		foreach ( $terms as $term ) {
			$cat_links .= '<li><a href="' . esc_url( get_term_link( $term ) ) . '"><span>' . esc_html( $term->name ) . '</span><small>(' . esc_html( $term->count ) . ')</small></a></li>';
		}
		$cat_widget = '<div class="ci360-sidebar-widget"><h4 class="ci360-sidebar-title">Categories</h4><ul class="ci360-sidebar-cat-list">' . $cat_links . '</ul></div>';
	}

	$feat_widget = '';
	$feat_ids = array_slice( array_diff( $ids, array( $post_id ) ), 0, 3 );
	if ( ! empty( $feat_ids ) ) {
		$feat_items = '';
		foreach ( $feat_ids as $fid ) {
			$fp = ci_project( $fid );
			$fp_img = ci_img( $fp['image'], $fp['name'] );
			$feat_items .= '<a class="ci360-sidebar-post-item" href="' . esc_url( $fp['url'] ) . '">'
				. '<span class="ci360-sidebar-post-thumb">' . ( $fp_img ? $fp_img : ci_star() ) . '</span>'
				. '<span class="ci360-sidebar-post-info"><span class="ci360-sidebar-post-cat">' . ci_e( $fp['category'] ) . '</span><h5 class="ci360-sidebar-post-heading">' . ci_e( $fp['name'] ) . '</h5></span>'
				. '</a>';
		}
		$feat_widget = '<div class="ci360-sidebar-widget"><h4 class="ci360-sidebar-title">Featured Work</h4><div class="ci360-sidebar-posts-list">' . $feat_items . '</div></div>';
	}

	$services_widget = '';
	$s_ids = array_slice( ci_ids( 'ci_service' ), 0, 4 );
	if ( ! empty( $s_ids ) ) {
		$s_links = '';
		foreach ( $s_ids as $sid ) {
			$sv = ci_service( $sid );
			$s_links .= '<li><a href="' . esc_url( $sv['url'] ) . '"><span>' . ci_e( $sv['title'] ) . '</span>' . ci_arrow() . '</a></li>';
		}
		$services_widget = '<div class="ci360-sidebar-widget"><h4 class="ci360-sidebar-title">Capabilities</h4><ul class="ci360-sidebar-services-list">' . $s_links . '</ul></div>';
	}

	$contact_url = get_permalink( ci_page_id( 'contact' ) );
	$cta_widget = '<div class="ci360-sidebar-cta-card">'
		. '<h4>Need a custom strategy?</h4>'
		. '<p>Let’s build a powerful brand narrative & performance engine for your business.</p>'
		. '<a href="' . esc_url( $contact_url ? $contact_url : '/contact/' ) . '" class="button button-light"><span>Start a Conversation</span><i>' . ci_arrow() . '</i></a>'
		. '</div>';

	?>
	<div id="ci360-case-study-root" class="ci360-case-study-page">
		<!-- 1. Full-Screen 100vh Hero Banner -->
		<section class="ci360-hero-full-screen ci360-case-hero" style="background-image: url('<?php echo esc_url( $feat_img_url ); ?>');">
			<div class="ci360-hero-overlay"></div>
			<div class="ci360-hero-full-content wrap">
				<div class="ci360-hero-left-align">
					<nav class="ci360-blog-breadcrumb-light" aria-label="Breadcrumb">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
						<span>/</span>
						<a href="<?php echo esc_url( home_url( '/work/' ) ); ?>">Work</a>
						<span>/</span>
						<span><?php echo ci_e( $p['name'] ); ?></span>
					</nav>
					<div class="ci360-blog-kicker-light">
						<span><?php echo ci_e( mb_strtoupper( $cat_name ) ); ?></span>
						<i></i>
						<span>CASE STUDY</span>
					</div>
					<h1 class="ci360-hero-full-title"><?php echo ci_e( $p['name'] ); ?></h1>
					<?php if ( ! empty( $p['headline'] ) ) : ?>
						<p class="ci360-hero-lead"><?php echo ci_e( $p['headline'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<!-- 2. Top 1 Row of 4 Related Case Studies -->
		<?php if ( $related_cards ) : ?>
			<section class="wrap ci360-related-box-container ci360-case-top-related">
				<div class="ci360-related-box">
					<div class="ci360-related-header">
						<h2>Featured Case Studies</h2>
						<a href="<?php echo esc_url( home_url( '/work/' ) ); ?>" class="text-link">Explore all work <?php echo ci_arrow(); ?></a>
					</div>
					<div class="ci360-related-grid">
						<?php echo $related_cards; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<!-- 3. Main Case Study Story Content Area (FULL WIDTH - NO SIDEBAR) -->
		<section class="ci360-case-main-container wrap">
			<article class="ci360-case-article ci360-case-full-width">
				<?php echo $story; ?>
				<?php echo $gallery; ?>

				<!-- Professional Next & Previous Buttons Bar Below Content -->
				<nav class="ci360-case-nav-bar ci360-case-nav-pro" aria-label="Case Study Navigation">
					<?php if ( $prev_proj ) : ?>
						<a href="<?php echo esc_url( $prev_proj['url'] ); ?>" class="ci360-case-nav-btn prev">
							<span class="nav-arrow">&larr;</span>
							<div class="nav-content">
								<small>Previous Case Study</small>
								<strong><?php echo ci_e( $prev_proj['name'] ); ?></strong>
							</div>
						</a>
					<?php else : ?>
						<div class="ci360-case-nav-btn prev disabled"></div>
					<?php endif; ?>

					<?php if ( $next_proj ) : ?>
						<a href="<?php echo esc_url( $next_proj['url'] ); ?>" class="ci360-case-nav-btn next">
							<div class="nav-content text-right">
								<small>Next Case Study</small>
								<strong><?php echo ci_e( $next_proj['name'] ); ?></strong>
							</div>
							<span class="nav-arrow">&rarr;</span>
						</a>
					<?php endif; ?>
				</nav>

				<!-- Professional Share Icons Bar Below Content -->
				<div class="ci360-share-bar ci360-share-bar-pro">
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
					<?php echo apply_filters( 'the_content', $content_raw ); ?>
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
