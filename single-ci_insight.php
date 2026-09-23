<?php
/**
 * Single insight (article).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$a   = ci_insight( get_queried_object_id() );
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
?><main id="main"><article><header class="article-hero wrap"><?php echo ci_breadcrumb( 'Insights / ' . $a['title'] ); ?><?php echo ci_label( mb_strtoupper( $a['kicker'] ), $a['status'] ); ?><h1 class="article-display" data-title><?php echo ci_e( $a['title'] ); ?></h1><p class="article-standfirst"><?php echo ci_e( $a['intro'] ); ?></p><div class="article-byline"><span><?php echo ci_e( ci_opt( 'tpl_insight_byline' ) ); ?></span><span><?php echo ci_e( $a['read'] ); ?> read</span><button class="share-article"><?php echo ci_e( ci_opt( 'tpl_insight_share' ) ); ?> <?php echo ci_arrow(); ?></button></div><div class="article-banner tone-<?php echo esc_attr( $a['tone'] ); ?>"><?php echo ci_img( $a['image'], $a['title'] . ' editorial visual', '', true ); ?><div></div><span><?php echo ci_e( $a['title'] ); ?></span><?php echo ci_star(); ?></div></header><div class="article-reading wrap"><aside class="article-toc"><span class="small-label"><?php echo ci_e( ci_opt( 'tpl_insight_toc' ) ); ?></span><?php echo $toc; ?><div class="reading-progress"><i></i></div><span class="small-label"><?php echo ci_e( ci_opt( 'tpl_insight_toc_end' ) ); ?></span></aside><div class="article-body"><?php echo $body; ?><?php if ( $a['note'] ) : ?><div class="article-editorial-note"><strong><?php echo ci_e( ci_opt( 'tpl_insight_note_title' ) ); ?></strong><p><?php echo ci_e( $a['note'] ); ?></p></div><?php endif; ?><?php echo ci_btn( ci_opt( 'tpl_insight_button' ), get_permalink( ci_page_id( 'contact' ) ), 'outline' ); ?></div></div></article><?php if ( $others ) : ?><section class="section wrap"><div class="section-heading"><?php echo ci_sec_label( 'tpl_insight_related', 'option' ); ?><h2><?php echo ci_html( ci_opt( 'tpl_insight_related_heading' ) ); ?></h2></div><div class="articles-grid"><?php echo $others; ?></div></section><?php endif; ?></main><?php
get_footer();
