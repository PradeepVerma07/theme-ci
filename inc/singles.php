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
$is_case = 'case' === $p['type'];
$pos     = array_search( $p['id'], $ids, true );
$next    = ci_project( $ids[ ( false === $pos ? 0 : $pos + 1 ) % max( 1, $total ) ] );

$t  = $p['term'];
$p1 = ( $t && ci_term_get( 'cat_p1', $t ) ) ? ci_term_get( 'cat_p1', $t ) : ci_opt( 'opt_sector_p1' );
$p2 = ( $t && ci_term_get( 'cat_p2', $t ) ) ? ci_term_get( 'cat_p2', $t ) : ci_opt( 'opt_sector_p2' );

if ( $is_case && $p['sections'] ) {
	$story = '';
	foreach ( $p['sections'] as $r ) {
		$story .= '<h3>' . ci_e( $r['title'] ) . '</h3><p>' . ci_e( $r['text'] ) . '</p>';
	}
} else {
	$story = '<p class="large-copy">' . ci_e( $p['summary'] ) . '</p><h3>' . ci_e( ci_opt( 'perspective' === $p['type'] ? 'tpl_project_lens' : 'tpl_project_direction' ) ) . '</h3>' . ci_paragraphs( array( $p1, $p2 ) );
}
$tag_type = ci_opt( 'tpl_tag_' . ( in_array( $p['type'], array( 'case', 'gallery' ), true ) ? $p['type'] : 'perspective' ) );

$gallery = '';
if ( $p['gallery'] ) {
	$items = '';
	foreach ( $p['gallery'] as $i => $im ) {
		$items .= '<button class="gallery-image tone-' . esc_attr( $p['tone'] ) . '" data-lightbox="' . esc_url( wp_get_attachment_image_url( $im, 'full' ) ) . '" aria-label="View ' . esc_attr( $p['name'] ) . ' image ' . ( $i + 1 ) . ' full size">' . ci_img( $im, $p['name'] . ' creative ' . ( $i + 1 ) ) . '<span>' . ci_e( ci_opt( 'tpl_project_view' ) ) . ' ' . ci_arrow() . '</span></button>';
	}
	$gallery = '<section class="project-gallery wrap"><div class="gallery-heading">' . ci_sec_label( 'tpl_project_gallery', 'option' ) . '<span>' . ci_pad( count( $p['gallery'] ) ) . ' IMAGES</span></div><div class="gallery-grid ' . ( 1 === count( $p['gallery'] ) ? 'gallery-single' : '' ) . '">' . $items . '</div></section>';
}
$links = '';
foreach ( array_slice( ci_ids( 'ci_service' ), 0, 4 ) as $sid ) {
	$s      = ci_service( $sid );
	$links .= '<a href="' . esc_url( $s['url'] ) . '"><span>' . ci_e( $s['number'] ) . '</span><h3>' . ci_e( $s['title'] ) . '</h3>' . ci_arrow() . '</a>';
}
?><section class="page-hero wrap project-detail-hero"><?php echo ci_breadcrumb( 'Work / ' . $p['name'] ); ?><div class="project-detail-eyebrow"><?php echo ci_label( mb_strtoupper( $p['category'] ), ci_type_label( $p['type'] ) ); ?><span><?php echo ci_e( $p['number'] ); ?> / <?php echo ci_pad( $total ); ?></span></div><h1 class="project-display" data-title><?php echo ci_e( $p['name'] ); ?></h1><div class="project-hero-visual" data-reveal><?php echo ci_case_visual( $p ); ?></div><p class="image-attribution"><?php echo ci_e( $p['note'] ); ?></p></section><section class="section wrap project-story"><div><?php echo ci_sec_label( 'tpl_project_story', 'option' ); ?><h2 data-reveal><?php echo ci_e( $p['headline'] ); ?></h2><?php echo ci_tags( array( $p['category'], $tag_type ) ); ?></div><div><?php echo $story; ?><?php if ( $p['disclosure'] ) : ?><p class="project-disclosure"><?php echo ci_html( $p['disclosure'] ); ?></p><?php endif; ?></div></section><?php echo $gallery; ?><section class="section wrap"><div class="section-heading"><?php echo ci_sec_label( 'tpl_project_links', 'option' ); ?><h2 data-reveal><?php echo ci_html( ci_opt( 'tpl_project_links_heading' ) ); ?></h2></div><div class="service-link-grid"><?php echo $links; ?></div></section><?php if ( $next ) : ?><a class="next-project tone-<?php echo esc_attr( $next['tone'] ); ?>" href="<?php echo esc_url( $next['url'] ); ?>"><div class="wrap"><span class="small-label"><?php echo ci_e( ci_opt( 'tpl_project_next' ) ); ?></span><h2><?php echo ci_e( $next['name'] ); ?> <?php echo ci_arrow(); ?></h2></div></a><?php endif; ?>
	<?php
	return ob_get_clean();
}

function ci_render_insight( $post_id ) {
	ob_start();
$a   = ci_insight( $post_id );
$toc = '';
$body = '';
foreach ( $a['sections'] as $i => $r ) {
	$toc  .= '<a href="#section-' . $i . '">' . ci_pad( $i + 1 ) . ' ' . ci_e( $r['title'] ) . '</a>';
	$body .= '<section id="section-' . $i . '"><h2>' . ci_e( $r['title'] ) . '</h2>' . ci_paragraphs( $r['text'] ) . '</section>';
}
$others = '';
$n      = 0;
foreach ( ci_ids( 'ci_insight' ) as $aid ) {
	if ( $aid !== $a['id'] ) {
		$others .= ci_article_card( ci_insight( $aid ), $n++ );
	}
}
?><article><header class="article-hero wrap"><?php echo ci_breadcrumb( 'Insights / ' . $a['title'] ); ?><?php echo ci_label( mb_strtoupper( $a['kicker'] ), $a['status'] ); ?><h1 class="article-display" data-title><?php echo ci_e( $a['title'] ); ?></h1><p class="article-standfirst"><?php echo ci_e( $a['intro'] ); ?></p><div class="article-byline"><span><?php echo ci_e( ci_opt( 'tpl_insight_byline' ) ); ?></span><span><?php echo ci_e( $a['read'] ); ?> read</span><button class="share-article"><?php echo ci_e( ci_opt( 'tpl_insight_share' ) ); ?> <?php echo ci_arrow(); ?></button></div><div class="article-banner tone-<?php echo esc_attr( $a['tone'] ); ?>"><?php echo ci_img( $a['image'], $a['title'] . ' editorial visual', '', true ); ?><div></div><span><?php echo ci_e( $a['title'] ); ?></span><?php echo ci_star(); ?></div></header><div class="article-reading wrap"><aside class="article-toc"><span class="small-label"><?php echo ci_e( ci_opt( 'tpl_insight_toc' ) ); ?></span><?php echo $toc; ?><div class="reading-progress"><i></i></div><span class="small-label"><?php echo ci_e( ci_opt( 'tpl_insight_toc_end' ) ); ?></span></aside><div class="article-body"><?php echo $body; ?><?php if ( $a['note'] ) : ?><div class="article-editorial-note"><strong><?php echo ci_e( ci_opt( 'tpl_insight_note_title' ) ); ?></strong><p><?php echo ci_e( $a['note'] ); ?></p></div><?php endif; ?><?php echo ci_btn( ci_opt( 'tpl_insight_button' ), get_permalink( ci_page_id( 'contact' ) ), 'outline' ); ?></div></div></article><?php if ( $others ) : ?><section class="section wrap"><div class="section-heading"><?php echo ci_sec_label( 'tpl_insight_related', 'option' ); ?><h2><?php echo ci_html( ci_opt( 'tpl_insight_related_heading' ) ); ?></h2></div><div class="articles-grid"><?php echo $others; ?></div></section><?php endif; ?>
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
							$soc_label = $soc['label'] ?? '';
							$soc_url   = $soc['url'] ?? '#';
							echo '<a href="' . esc_url( $soc_url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $soc_label ) . '">' . ci_e( $soc_label ) . '</a>';
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
