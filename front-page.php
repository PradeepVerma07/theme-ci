<?php
/**
 * Home page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$id       = get_queried_object_id();
$g        = function ( $n ) use ( $id ) {
	return ci_get( $n, $id );
};
$services = array_map( 'ci_service', ci_ids( 'ci_service' ) );
$featured = array_map( 'ci_project', ci_featured_projects() );
$insights = array_map( 'ci_insight', array_slice( ci_ids( 'ci_insight' ), 0, 3 ) );

// Hero collage.
$positions = array( array( 'collage-left', '-12' ), array( 'collage-main', '8' ), array( 'collage-right', '17' ) );
$collage   = '';
foreach ( array_slice( ci_rows( 'home_collage', $id ), 0, 3 ) as $i => $row ) {
	$pid      = is_object( $row['project'] ) ? $row['project']->ID : (int) $row['project'];
	$collage .= '<a href="' . esc_url( $pid ? get_permalink( $pid ) : home_url( '/work/' ) ) . '" class="collage-frame ' . $positions[ $i ][0] . '" data-depth="' . $positions[ $i ][1] . '">' . ci_img( $row['image'], 'Explore ' . ( $pid ? ci_title( $pid ) : '' ) . ' creative', '', true ) . '<span>' . ci_e( $row['caption'] ) . '</span></a>';
}

// Featured stack.
$stack = '';
foreach ( $featured as $i => $p ) {
	$stack .= '<article class="showcase tone-' . esc_attr( $p['tone'] ) . '" data-stack><a class="showcase-media" href="' . esc_url( $p['url'] ) . '" data-cursor="Explore">' . ci_case_visual( $p ) . '</a><div class="showcase-copy"><span class="small-label">0' . ( $i + 1 ) . ' / ' . ci_e( mb_strtoupper( $p['category'] ) ) . '</span><h3>' . ci_e( $p['name'] ) . '</h3><p class="showcase-headline">' . ci_e( $p['headline'] ) . '</p><p>' . ci_e( $p['summary'] ) . '</p><a class="text-link" href="' . esc_url( $p['url'] ) . '">' . ci_e( $g( 'home_work_link' ) ) . ' ' . ci_arrow() . '</a></div></article>';
}

// Capability list.
$cap_services = array_slice( $services, 0, max( 1, (int) $g( 'home_cap_count' ) ) );
$rows         = '';
foreach ( $cap_services as $s ) {
	$rows .= '<a class="capability-row" href="' . esc_url( $s['url'] ) . '" data-service-preview="' . esc_attr( $s['slug'] ) . '"><span>' . ci_e( $s['number'] ) . '</span><div><h3>' . ci_e( $s['title'] ) . '</h3><p>' . ci_e( implode( ' / ', array_slice( $s['tags'], 0, 3 ) ) ) . '</p></div>' . ci_arrow() . '</a>';
}

// Marquee.
$words = array_filter( array_map( 'trim', explode( '|', $g( 'home_marquee' ) ) ) );
$line  = '';
foreach ( $words as $w ) {
	$line .= ci_e( $w ) . ' ' . ci_star() . ' ';
}
$line = rtrim( $line );

// Insight cards.
$cards = '';
foreach ( $insights as $i => $a ) {
	$cards .= ci_article_card( $a, $i );
}
?><main id="main"><section class="hero wrap"><div class="hero-copy"><div class="hero-eyebrow"><i></i> <?php echo ci_e( $g( 'home_eyebrow' ) ); ?></div><h1 class="hero-title"><span class="line"><?php echo ci_e( $g( 'home_title_1' ) ); ?></span><span class="line"><?php echo ci_e( $g( 'home_title_2' ) ); ?></span><span class="line accent"><?php echo ci_e( $g( 'home_title_3' ) ); ?><svg viewBox="0 0 540 35" aria-hidden="true"><path d="M5 25C125 2 319 0 533 15M35 33C159 12 340 8 490 17"/></svg></span></h1><p class="hero-description"><?php echo ci_e( $g( 'home_description' ) ); ?></p><div class="hero-actions"><?php echo ci_btn( $g( 'home_cta' ), $g( 'home_cta_link' ) ); ?><a href="#selected-work" class="text-link"><?php echo ci_e( $g( 'home_secondary' ) ); ?> <?php echo ci_arrow( 'se' ); ?></a></div></div><div class="hero-collage" aria-label="Selected CI360 campaign imagery"><div class="collage-coordinate"><?php echo ci_e( $g( 'home_collage_label' ) ); ?></div><div class="collage-ring" aria-hidden="true"></div><?php echo $collage; ?><div class="hero-sticker" aria-hidden="true"><?php echo ci_star(); ?><b><?php echo ci_html( $g( 'home_sticker' ) ); ?></b></div><div class="collage-caption"><span><?php echo ci_e( $g( 'home_collage_caption' ) ); ?></span><span><?php echo ci_e( $g( 'home_collage_badge' ) ); ?></span></div></div><div class="hero-bottom"><span><?php echo ci_e( $g( 'home_bottom_left' ) ); ?></span><a href="#selected-work"><?php echo ci_e( $g( 'home_bottom_right' ) ); ?> <span>&darr;</span></a></div></section><?php echo ci_brand_strip(); ?><section class="work-section section" id="selected-work"><div class="wrap"><div class="section-heading heading-row"><?php echo ci_sec_label( 'home_work', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'home_work_heading' ) ); ?></h2><p><?php echo ci_e( $g( 'home_work_intro' ) ); ?></p></div><div class="work-stack"><?php echo $stack; ?></div><div class="work-end"><span><?php echo ci_e( $g( 'home_work_end' ) ); ?></span><?php echo ci_btn( sprintf( $g( 'home_work_button' ), ci_count( 'ci_project' ) ), get_permalink( ci_page_id( 'work' ) ), 'outline' ); ?></div></div></section><section class="capabilities section dark-section"><div class="wrap"><div class="section-heading heading-row"><?php echo ci_sec_label( 'home_cap', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'home_cap_heading' ) ); ?></h2><p><?php echo ci_e( $g( 'home_cap_intro' ) ); ?></p></div><div class="capability-layout"><div class="capability-list"><?php echo $rows; ?><a class="all-services" href="<?php echo esc_url( get_permalink( ci_page_id( 'services' ) ) ); ?>"><?php echo ci_e( sprintf( $g( 'home_cap_all' ), count( $services ) ) ); ?> <?php echo ci_arrow(); ?></a></div><div class="capability-preview" aria-hidden="true"><?php echo $services ? ci_service_visual( $services[0] ) : ''; ?><p><?php echo ci_e( $g( 'home_cap_preview' ) ); ?></p></div></div></div><?php echo ci_service_preview_templates( $cap_services ); ?></section><section class="about-teaser section wrap"><div class="studio-photo" data-reveal><?php echo ci_img( $g( 'home_about_image' ) ); ?><span class="photo-stamp"><?php echo ci_html( $g( 'home_about_stamp' ) ); ?></span></div><div class="about-teaser-copy"><?php echo ci_sec_label( 'home_about', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'home_about_heading' ) ); ?></h2><?php echo ci_paragraphs( $g( 'home_about_text' ) ); ?><?php echo ci_btn( $g( 'home_about_button' ), $g( 'home_about_link' ), 'outline' ); ?></div></section><div class="word-marquee" aria-hidden="true"><div class="marquee-track"><span><?php echo $line; ?></span><span><?php echo $line; ?></span></div></div><?php echo ci_process(); ?><?php echo ci_testimonial(); ?><section class="insights-section section wrap"><div class="section-heading heading-row"><?php echo ci_sec_label( 'home_insights', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'home_insights_heading' ) ); ?></h2><p><?php echo ci_e( $g( 'home_insights_intro' ) ); ?></p></div><div class="articles-grid"><?php echo $cards; ?></div><div class="align-right"><?php echo ci_btn( $g( 'home_insights_button' ), $g( 'home_insights_link' ), 'outline' ); ?></div></section><section class="faq-section section wrap"><div><?php echo ci_sec_label( 'home_faq', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'home_faq_heading' ) ); ?></h2></div><?php echo ci_faq( ci_faq_group( 'home_faq_group', $id, 'home' ) ); ?></section></main><?php
get_footer();
