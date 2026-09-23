<?php
/**
 * Template Name: Legal
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$id       = get_queried_object_id();
$sections = '';
foreach ( ci_rows( 'legal_sections', $id ) as $i => $r ) {
	$sections .= '<section><span class="small-label">' . ci_pad( $i + 1 ) . '</span><h2>' . ci_e( $r['title'] ) . '</h2><p>' . ci_e( $r['text'] ) . '</p></section>';
}
?><main id="main"><section class="page-hero wrap legal-hero"><?php echo ci_breadcrumb( ci_title( $id ) ); ?><?php echo ci_label( ci_get( 'legal_label_1', $id ), ci_get( 'legal_label_2', $id ) ); ?><h1 class="display" data-title><?php echo ci_e( ci_title( $id ) ); ?></h1><p class="large-copy"><?php echo ci_e( ci_get( 'legal_intro', $id ) ); ?></p></section><section class="legal-layout wrap"><aside><div class="legal-art tone-lilac"><?php echo ci_star(); ?><span><?php echo ci_html( ci_get( 'legal_art', $id ) ); ?></span></div><p><?php echo ci_e( ci_get( 'legal_aside', $id ) ); ?></p></aside><div class="legal-body"><?php echo $sections; ?></div></section><?php echo ci_compact_cta( ci_get( 'legal_cta', $id ) ); ?></main><?php
get_footer();
