<?php
/**
 * Single service.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$s     = ci_service( get_queried_object_id() );
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
?><main id="main"><section class="page-hero wrap service-detail-hero"><?php echo ci_breadcrumb( 'Services / ' . $s['title'] ); ?><div class="service-detail-grid"><div><?php echo ci_label( ci_opt( 'tpl_service_capability' ) . ' ' . $s['number'], ci_opt( 'tpl_service_label_2' ) ); ?><h1 class="detail-display" data-title><?php echo ci_e( $s['title'] ); ?></h1><p class="service-lead"><?php echo ci_e( $s['summary'] ); ?></p><?php echo ci_btn( sprintf( ci_opt( 'tpl_service_cta' ), mb_strtolower( $s['group'] ) ), add_query_arg( 'service', $s['slug'], $contact ) ); ?></div><div class="service-detail-art" data-reveal><?php echo ci_service_visual( $s ); ?></div></div></section><section class="section wrap service-intro"><div><?php echo ci_label( ci_opt( 'tpl_service_opportunity' ), mb_strtoupper( $s['group'] ) ); ?><h2 data-reveal><?php echo ci_e( $s['headline'] ); ?></h2></div><div><p class="large-copy"><?php echo ci_e( $s['body'] ); ?></p><p><?php echo ci_e( ci_opt( 'tpl_service_connected' ) ); ?></p></div></section><section class="deliverables-section section tone-<?php echo esc_attr( $s['tone'] ); ?>"><div class="wrap"><?php echo $sec( 'tpl_service_deliver' ); ?><div class="deliverables-grid"><?php echo $deliverables; ?></div><p class="scope-note"><?php echo ci_e( ci_opt( 'tpl_service_scope' ) ); ?></p></div></section><section class="section wrap"><?php echo $sec( 'tpl_service_steps' ); ?><div class="service-steps"><?php echo $steps; ?></div></section><section class="section dark-section"><div class="wrap"><?php echo $sec( 'tpl_service_related', '', true ); ?><div class="services-grid related-services"><?php echo $related; ?></div></div></section><section class="section wrap"><?php echo $sec( 'tpl_service_work' ); ?><div class="project-grid two-col"><?php echo $work; ?></div></section><?php echo ci_compact_cta( sprintf( ci_opt( 'tpl_service_cta_text' ), mb_strtolower( $s['title'] ) ) ); ?></main><?php
get_footer();
