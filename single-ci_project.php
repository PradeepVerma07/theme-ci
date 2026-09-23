<?php
/**
 * Single project.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$p       = ci_project( get_queried_object_id() );
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
?><main id="main"><section class="page-hero wrap project-detail-hero"><?php echo ci_breadcrumb( 'Work / ' . $p['name'] ); ?><div class="project-detail-eyebrow"><?php echo ci_label( mb_strtoupper( $p['category'] ), ci_type_label( $p['type'] ) ); ?><span><?php echo ci_e( $p['number'] ); ?> / <?php echo ci_pad( $total ); ?></span></div><h1 class="project-display" data-title><?php echo ci_e( $p['name'] ); ?></h1><div class="project-hero-visual" data-reveal><?php echo ci_case_visual( $p ); ?></div><p class="image-attribution"><?php echo ci_e( $p['note'] ); ?></p></section><section class="section wrap project-story"><div><?php echo ci_sec_label( 'tpl_project_story', 'option' ); ?><h2 data-reveal><?php echo ci_e( $p['headline'] ); ?></h2><?php echo ci_tags( array( $p['category'], $tag_type ) ); ?></div><div><?php echo $story; ?><?php if ( $p['disclosure'] ) : ?><p class="project-disclosure"><?php echo ci_html( $p['disclosure'] ); ?></p><?php endif; ?></div></section><?php echo $gallery; ?><section class="section wrap"><div class="section-heading"><?php echo ci_sec_label( 'tpl_project_links', 'option' ); ?><h2 data-reveal><?php echo ci_html( ci_opt( 'tpl_project_links_heading' ) ); ?></h2></div><div class="service-link-grid"><?php echo $links; ?></div></section><?php if ( $next ) : ?><a class="next-project tone-<?php echo esc_attr( $next['tone'] ); ?>" href="<?php echo esc_url( $next['url'] ); ?>"><div class="wrap"><span class="small-label"><?php echo ci_e( ci_opt( 'tpl_project_next' ) ); ?></span><h2><?php echo ci_e( $next['name'] ); ?> <?php echo ci_arrow(); ?></h2></div></a><?php endif; ?></main><?php
get_footer();
