<?php
/**
 * Template Name: Insights
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$id = get_queried_object_id();
$g  = function ( $n ) use ( $id ) {
	return ci_get( $n, $id );
};
$cards = '';
foreach ( ci_ids( 'ci_insight' ) as $i => $aid ) {
	$cards .= ci_article_card( ci_insight( $aid ), $i );
}
$contact = get_permalink( ci_page_id( 'contact' ) );
$topics  = '';
foreach ( ci_rows( 'insights_topic_list', $id ) as $r ) {
	$topics .= '<a href="' . esc_url( add_query_arg( 'topic', rawurlencode( $r['topic'] ), $contact ) ) . '">' . ci_e( $r['topic'] ) . ' ' . ci_arrow() . '</a>';
}
?><main id="main"><section class="page-hero wrap"><?php echo ci_breadcrumb( $g( 'insights_crumb' ) ); ?><?php echo ci_sec_label( 'insights_hero', $id ); ?><h1 class="display" data-title><?php echo ci_html( $g( 'insights_hero_heading' ) ); ?></h1><div class="hero-intro-row"><p><?php echo ci_e( $g( 'insights_intro_short' ) ); ?></p><p><?php echo ci_e( $g( 'insights_intro' ) ); ?></p></div></section><section class="wrap section-topless"><div class="articles-grid insights-grid"><?php echo $cards; ?></div><?php if ( $g( 'insights_note' ) ) : ?><p class="editorial-note"><?php echo ci_e( $g( 'insights_note' ) ); ?></p><?php endif; ?></section><section class="section wrap topic-section"><div class="section-heading"><?php echo ci_sec_label( 'insights_topics', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'insights_topics_heading' ) ); ?></h2></div><div class="industry-pills"><?php echo $topics; ?></div></section><?php echo ci_compact_cta( $g( 'insights_cta' ) ); ?></main><?php
get_footer();
