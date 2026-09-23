<?php
/**
 * Template Name: Founders
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$id = get_queried_object_id();
$g  = function ( $n ) use ( $id ) {
	return ci_get( $n, $id );
};
$profiles = '';
$founders = ci_posts( 'ci_team', array( 'meta_key' => 'team_is_founder', 'meta_value' => '1' ) );
foreach ( $founders as $i => $f ) {
	$fid      = $f->ID;
	$name     = ci_title( $f );
	$role     = ci_get( 'founder_role', $fid );
	$portrait = (int) ci_get( 'founder_portrait', $fid );
	$profiles .= '<article class="founder-profile" data-reveal><div class="founder-photo tone-' . esc_attr( ci_get( 'founder_tone', $fid ) ) . '"><div class="founder-fallback">' . ci_img( $g( 'founders_fallback_image' ), 'Creative studio environment' ) . '<div><span>' . ci_e( ci_get( 'founder_initials', $fid ) ) . '</span><p>' . ci_e( ci_get( 'founder_lens', $fid ) ) . '</p></div></div>'
		. ( $portrait ? '<img class="founder-portrait" src="' . esc_url( wp_get_attachment_image_url( $portrait, 'full' ) ) . '" alt="' . esc_attr( $name ) . ' - portrait" loading="lazy" data-external-portrait>' : '' )
		. '<span class="founder-photo-label">0' . ( $i + 1 ) . ' / ' . ci_e( mb_strtoupper( $role ) ) . '</span></div><div class="founder-body"><span class="small-label">' . ci_e( $role ) . '</span><h2>' . ci_e( $name ) . '</h2><blockquote>' . ci_e( ci_get( 'founder_quote', $fid ) ) . '</blockquote></div></article>';
}
?><main id="main"><section class="page-hero wrap"><?php echo ci_breadcrumb( $g( 'founders_crumb' ) ); ?><?php echo ci_sec_label( 'founders_hero', $id ); ?><h1 class="display" data-title><?php echo ci_html( $g( 'founders_hero_heading' ) ); ?></h1><div class="hero-intro-row"><p><?php echo ci_e( $g( 'founders_intro_short' ) ); ?></p><p><?php echo ci_e( $g( 'founders_intro' ) ); ?></p></div></section><section class="founder-duo wrap"><?php echo $profiles; ?></section><section class="section dark-section"><div class="wrap shared-belief"><div><?php echo ci_sec_label( 'founders_belief', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'founders_belief_heading' ) ); ?></h2><div class="shared-symbol" aria-hidden="true"><?php echo ci_star(); ?> + <?php echo ci_star(); ?></div></div><div><?php echo ci_paragraphs( $g( 'founders_belief_text' ) ); ?></div></div></section><section class="section wrap founder-studio"><div class="studio-photo" data-reveal><?php echo ci_img( $g( 'founders_studio_image' ) ); ?></div><div><h2 data-reveal><?php echo ci_html( $g( 'founders_studio_heading' ) ); ?></h2><p><?php echo ci_e( $g( 'founders_studio_text' ) ); ?></p><?php echo ci_btn( $g( 'founders_studio_button' ), $g( 'founders_studio_link' ), 'outline' ); ?></div></section><?php echo ci_compact_cta( $g( 'founders_cta' ) ); ?></main><?php
get_footer();
