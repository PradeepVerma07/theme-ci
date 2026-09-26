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

// Enable Elementor editing support for custom post types
add_action( 'init', function () {
	add_post_type_support( 'ci_service', 'elementor' );
	add_post_type_support( 'ci_project', 'elementor' );
	add_post_type_support( 'ci_insight', 'elementor' );
	add_post_type_support( 'post', 'elementor' );
	add_post_type_support( 'page', 'elementor' );
}, 99 );

// Guarantee Elementor CPT support filter returns custom post types
add_filter( 'elementor/cpt/support', function ( $cpts ) {
	$our_cpts = array( 'page', 'post', 'ci_service', 'ci_project', 'ci_insight' );
	return array_unique( array_merge( (array) $cpts, $our_cpts ) );
} );

// Synchronize option elementor_cpt_support in database
add_action( 'admin_init', function () {
	$cpts   = get_option( 'elementor_cpt_support', array( 'page', 'post' ) );
	$needed = array( 'page', 'post', 'ci_service', 'ci_project', 'ci_insight' );
	$diff   = array_diff( $needed, (array) $cpts );
	if ( ! empty( $diff ) ) {
		update_option( 'elementor_cpt_support', array_unique( array_merge( (array) $cpts, $needed ) ) );
	}
} );

// Render "Edit with Elementor" button in top admin bar for single CPTs
add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
	if ( ! is_admin() && is_singular() && current_user_can( 'edit_posts' ) ) {
		$post_id = get_the_ID();
		if ( $post_id && class_exists( '\Elementor\Plugin' ) ) {
			$post_type = get_post_type( $post_id );
			if ( in_array( $post_type, array( 'ci_service', 'ci_project', 'ci_insight' ), true ) ) {
				$doc = \Elementor\Plugin::$instance->documents->get( $post_id );
				$edit_url = $doc ? $doc->get_edit_url() : add_query_arg( array( 'post' => $post_id, 'action' => 'elementor' ), admin_url( 'post.php' ) );
				$wp_admin_bar->add_node(
					array(
						'id'    => 'elementor_inspector',
						'title' => '<span class="ab-icon"></span><span class="ab-label">Edit with Elementor</span>',
						'href'  => $edit_url,
					)
				);
			}
		}
	}
}, 99 );
