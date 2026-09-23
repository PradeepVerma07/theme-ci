<?php
/**
 * Template Name: Work
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$id = get_queried_object_id();
$g  = function ( $n ) use ( $id ) {
	return ci_get( $n, $id );
};
$featured = ci_featured_projects();
$ordered  = array_merge( $featured, array_diff( ci_ids( 'ci_project' ), $featured ) );
$total    = count( $ordered );
$cards    = '';
foreach ( $ordered as $pid ) {
	$cards .= ci_project_card( ci_project( $pid ) );
}
// Filter pills: categories marked "Show as filter".
$terms = get_terms( array( 'taxonomy' => 'ci_project_category', 'hide_empty' => false ) );
$pills = array();
foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
	if ( ci_term_get( 'cat_in_filter', $t ) ) {
		$pills[ (int) ci_term_get( 'cat_filter_order', $t ) * 1000 + count( $pills ) ] = $t->name;
	}
}
ksort( $pills );
$buttons = '<button data-filter="All" aria-pressed="true">' . ci_e( $g( 'work_all' ) ) . ' <small>' . $total . '</small></button>';
foreach ( $pills as $name ) {
	$buttons .= '<button data-filter="' . esc_attr( $name ) . '" aria-pressed="false">' . ci_e( $name ) . '</button>';
}
?><main id="main"><section class="page-hero wrap work-hero"><?php echo ci_breadcrumb( $g( 'work_crumb' ) ); ?><?php echo ci_sec_label( 'work_hero', $id ); ?><div class="work-hero-head"><h1 class="display" data-title><?php echo ci_html( $g( 'work_hero_heading' ) ); ?></h1><div class="work-hero-circle"><?php echo (int) $total; ?><span><?php echo ci_html( $g( 'work_circle' ) ); ?></span><?php echo ci_star(); ?></div></div><div class="hero-intro-row"><p><?php echo ci_e( $g( 'work_intro_short' ) ); ?></p><p><?php echo ci_e( $g( 'work_intro' ) ); ?></p></div></section><section class="wrap portfolio-section"><div class="portfolio-tools"><div class="filter-pills" role="group" aria-label="Filter projects"><?php echo $buttons; ?></div><label class="search-field"><span class="sr-only">Search projects</span><input type="search" id="project-search" placeholder="<?php echo esc_attr( $g( 'work_search' ) ); ?>" autocomplete="off"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10" cy="10" r="6" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="m15 15 5 5" stroke="currentColor" stroke-width="1.5"/></svg></label></div><div class="portfolio-count" role="status" aria-live="polite"><span id="result-count"><?php echo (int) $total; ?></span> <?php echo ci_e( $g( 'work_count' ) ); ?></div><div class="project-grid" id="project-grid"><?php echo $cards; ?></div><div class="no-results" hidden><h2><?php echo ci_html( $g( 'work_empty_heading' ) ); ?></h2><p><?php echo ci_e( $g( 'work_empty_text' ) ); ?></p><button class="button button-dark" id="reset-filters"><?php echo ci_e( $g( 'work_empty_button' ) ); ?> <?php echo ci_arrow(); ?></button></div><p class="portfolio-note"><?php echo ci_e( $g( 'work_note' ) ); ?></p></section><?php echo ci_compact_cta( $g( 'work_cta' ) ); ?></main><?php
get_footer();
