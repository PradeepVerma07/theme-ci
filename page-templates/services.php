<?php
/**
 * Template Name: Services
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$id = get_queried_object_id();
$g  = function ( $n ) use ( $id ) {
	return ci_get( $n, $id );
};
$services = array_map( 'ci_service', ci_ids( 'ci_service' ) );
$hero_id  = (int) $g( 'services_hero_service' );
$hero     = $hero_id ? ci_service( $hero_id ) : ( $services[2] ?? ( $services[0] ?? null ) );
$cards    = '';
foreach ( $services as $s ) {
	$cards .= ci_service_card( $s );
}
?><main id="main"><section class="page-hero wrap services-hero"><?php echo ci_breadcrumb( $g( 'services_crumb' ) ); ?><div class="services-hero-grid"><div><?php echo ci_sec_label( 'services_hero', $id ); ?><h1 class="display" data-title><?php echo ci_html( $g( 'services_hero_heading' ) ); ?></h1><p><?php echo ci_e( $g( 'services_hero_intro' ) ); ?></p><?php echo ci_btn( $g( 'services_button' ), $g( 'services_button_link' ) ); ?></div><div class="services-hero-art"><?php echo $hero ? ci_service_visual( $hero ) : ''; ?><div class="hero-art-badge"><?php echo count( $services ); ?><br><small><?php echo ci_html( $g( 'services_badge' ) ); ?></small></div></div></div></section><section class="section wrap"><div class="services-grid"><?php echo $cards; ?></div></section><?php echo ci_process(); ?><section class="faq-section section wrap"><div><?php echo ci_sec_label( 'services_faq', $id ); ?><h2><?php echo ci_html( $g( 'services_faq_heading' ) ); ?></h2><p><?php echo ci_e( $g( 'services_faq_intro' ) ); ?></p></div><?php echo ci_faq( ci_faq_group( 'services_faq_group', $id, 'contact' ), (int) $g( 'services_faq_limit' ) ); ?></section><?php echo ci_compact_cta( $g( 'services_cta' ) ); ?></main><?php
get_footer();
