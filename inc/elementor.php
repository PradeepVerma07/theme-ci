<?php
/**
 * Elementor integration: one widget per CI360 section (panel category “CI360 Sections”).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'elementor/elements/categories_registered', function ( $manager ) {
	$manager->add_category( 'ci360', array( 'title' => 'CI360 Sections', 'icon' => 'eicon-star' ) );
} );

add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once CI360_DIR . '/inc/elementor-widgets.php';
	foreach ( array_keys( ci_section_defs() ) as $id ) {
		$class = 'CI360_Widget_' . str_replace( '-', '_', $id );
		if ( class_exists( $class ) ) {
			$widgets_manager->register( new $class() );
		}
	}
} );

// Theme CSS + JS inside the editor preview too.
add_action( 'elementor/preview/enqueue_styles', function () {
	wp_enqueue_style( 'ci360-main' );
	wp_enqueue_style( 'ci360-sections' );
	wp_enqueue_style( 'ci360-elementor' );
	wp_enqueue_style( 'ci360-editable-v25' );
} );

// Put the CI360 category near the top of the panel.
add_action( 'elementor/editor/after_enqueue_styles', function () {
	wp_add_inline_style( 'elementor-editor', '.elementor-panel-category-ci360 .elementor-panel-category-title{color:#0b36e2}' );
} );

/** Options for a post select (slug => title). */
function ci_el_post_options( $type, $empty = '— Automatic —' ) {
	$out = array( '' => $empty );
	foreach ( ci_posts( $type ) as $p ) {
		$out[ $p->post_name ] = ci_title( $p );
	}
	return $out;
}
/** Options for a term select (slug => name). */
function ci_el_term_options( $tax, $empty = '— All —' ) {
	$out   = array( '' => $empty );
	$terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
	foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
		$out[ $t->slug ] = $t->name;
	}
	return $out;
}
