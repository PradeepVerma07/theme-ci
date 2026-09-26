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
		if ( ! class_exists( $class ) ) {
			eval( 'class ' . $class . ' extends CI360_Section_Widget { protected $ci_id = "' . $id . '"; }' );
		}
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

/** Generate default Elementor widget data array for a service post. */
function ci_build_default_service_elementor_data( $post_id ) {
	$title       = get_the_title( $post_id );
	$cats        = get_the_terms( $post_id, 'ci_service_category' );
	$cat_name    = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0]->name : 'OUR SERVICE';
	$summary     = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : get_post_meta( $post_id, 'service_summary', true );
	if ( empty( $summary ) ) {
		$summary = 'Building distinctive brand identities through strategy, logo design, visual language, typography, and integrated brand communication.';
	}
	$thumb_id    = get_post_thumbnail_id( $post_id );
	$thumb_url   = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'full' ) : CI360_URI . '/assets/images/studio-detail.webp';
	$contact_url = get_permalink( ci_page_id( 'contact' ) ) ?: home_url( '/contact/' );

	$sections_config = array(
		array(
			'widget'   => 'ci360-service-hero',
			'settings' => array(
				'kicker'             => $cat_name,
				'title'              => $title,
				'lead'               => $summary,
				'cta_primary'        => 'Start a Conversation',
				'cta_primary_link'   => $contact_url,
				'cta_secondary'      => 'Contact Us',
				'cta_secondary_link' => $contact_url,
				'image'              => array( 'id' => (string) $thumb_id, 'url' => $thumb_url ),
				'show_badges'        => 'yes',
				'badge1_label'       => ci_get( 'service_badge1_label', $post_id ) ?: 'Followers',
				'badge1_value'       => ci_get( 'service_badge1_value', $post_id ) ?: '125K',
				'badge1_trend'       => ci_get( 'service_badge1_trend', $post_id ) ?: '+12%',
				'badge2_label'       => ci_get( 'service_badge2_label', $post_id ) ?: 'Engagement',
				'badge2_value'       => ci_get( 'service_badge2_value', $post_id ) ?: '+278%',
				'badge3_label'       => ci_get( 'service_badge3_label', $post_id ) ?: 'Reach',
				'badge3_value'       => ci_get( 'service_badge3_value', $post_id ) ?: '2.4M',
			),
		),
		array(
			'widget'   => 'ci360-service-overview',
			'settings' => array(
				'kicker'           => 'OVERVIEW',
				'heading'          => ci_get( 'service_overview_heading', $post_id ) ?: 'Turn Conversations Into <em>Communities</em>',
				'copy'             => ci_get( 'service_overview_copy', $post_id ) ?: "Social media is more than just posting — it's about people, conversations, and real connections. We help brands show up with purpose, create engaging content, and build communities that drive meaningful business results.",
				'features'         => ci_rows( 'service_overview_features', $post_id ),
				'highlights_title' => ci_get( 'service_highlights_title', $post_id ) ?: 'Service Highlights',
				'highlights_list'  => ci_rows( 'service_highlights_list', $post_id ),
				'card_button'      => 'Discuss Your Goals',
				'card_button_link' => $contact_url,
				'card_subtext'     => 'Get a tailored strategy for your brand.',
			),
		),
		array(
			'widget'   => 'ci360-service-included',
			'settings' => array(
				'kicker'  => "WHAT'S INCLUDED",
				'heading' => ci_get( 'service_included_heading', $post_id ) ?: 'Everything You Need to <em>Grow on Social</em>',
				'subtext' => 'From strategy to execution, we handle every part of your journey.',
				'cards'   => ci_rows( 'service_included_cards', $post_id ),
			),
		),
		array(
			'widget'   => 'ci360-service-approach',
			'settings' => array(
				'kicker'  => 'OUR APPROACH',
				'heading' => ci_get( 'service_approach_heading', $post_id ) ?: 'A Strategic, <em>Results-Driven Process</em>',
				'intro'   => 'We combine strategy, creativity, and data to create experiences that deliver real business impact.',
				'steps'   => ci_rows( 'service_steps', $post_id ),
			),
		),
		array(
			'widget'   => 'ci360-service-cta',
			'settings' => array(
				'kicker'      => "LET'S WORK TOGETHER",
				'heading'     => ci_get( 'service_cta_heading', $post_id ) ?: 'Ready to grow your brand on social media?',
				'subtext'     => ci_get( 'service_cta_sub', $post_id ) ?: 'Our team is here to understand your goals and create a tailored strategy that drives real results.',
				'button'      => 'Contact Us',
				'button_link' => $contact_url,
				'subcaption'  => 'Talk to our experts today.',
			),
		),
	);

	$data = array();
	foreach ( $sections_config as $idx => $cfg ) {
		$sec_id = 'sec_' . substr( md5( $cfg['widget'] . $idx . $post_id ), 0, 7 );
		$col_id = 'col_' . substr( md5( $cfg['widget'] . 'col' . $idx . $post_id ), 0, 7 );
		$wgt_id = 'wgt_' . substr( md5( $cfg['widget'] . 'wgt' . $idx . $post_id ), 0, 7 );

		$data[] = array(
			'id'       => $sec_id,
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => array(),
			'elements' => array(
				array(
					'id'       => $col_id,
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => array( '_column_size' => 100 ),
					'elements' => array(
						array(
							'id'         => $wgt_id,
							'elType'     => 'widget',
							'isInner'    => false,
							'widgetType' => $cfg['widget'],
							'settings'   => $cfg['settings'],
							'elements'   => array(),
						),
					),
				),
			),
		);
	}

	return $data;
}

/** Ensure _elementor_data is populated with default widgets for a service post. */
function ci_ensure_service_elementor_data( $post_id ) {
	if ( ! $post_id || 'ci_service' !== get_post_type( $post_id ) ) {
		return;
	}
	$raw = get_post_meta( $post_id, '_elementor_data', true );
	$force_reset = isset( $_GET['reset_ci360'] );
	if ( $force_reset || empty( $raw ) || '[]' === trim( (string) $raw ) || false === strpos( (string) $raw, 'ci360-service-hero' ) ) {
		$data = ci_build_default_service_elementor_data( $post_id );
		update_post_meta( $post_id, '_elementor_data', wp_slash( json_encode( $data ) ) );
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-post' );
	}
}

add_action( 'wp', function () {
	if ( is_singular( 'ci_service' ) ) {
		ci_ensure_service_elementor_data( get_the_ID() );
	}
} );

add_action( 'elementor/editor/before_enqueue_scripts', function () {
	if ( isset( $_GET['post'] ) ) {
		ci_ensure_service_elementor_data( (int) $_GET['post'] );
	}
} );

add_action( 'admin_init', function () {
	if ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] && isset( $_GET['post'] ) ) {
		ci_ensure_service_elementor_data( (int) $_GET['post'] );
	}
} );
