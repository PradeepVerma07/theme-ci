<?php
/**
 * Custom post types and taxonomies.
 *
 * URLs match the prototype: /services/{slug}/, /work/{slug}/, /insights/{slug}/.
 * The listing pages (/services/, /work/, /insights/) are normal Pages with templates,
 * so their headings stay editable.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'ci_register_content_types' );

function ci_register_content_types() {
	$types = array(
		'ci_service'     => array( 'Services', 'Service', 'services', 'dashicons-screenoptions', true ),
		'ci_project'     => array( 'Projects', 'Project', 'work', 'dashicons-portfolio', true ),
		'ci_insight'     => array( 'Insights', 'Insight', 'insights', 'dashicons-lightbulb', true ),
		'ci_testimonial' => array( 'Testimonials', 'Testimonial', '', 'dashicons-format-quote', false ),
		'ci_team'        => array( 'Team', 'Team member', '', 'dashicons-groups', false ),
		'ci_faq'         => array( 'FAQs', 'FAQ', '', 'dashicons-editor-help', false ),
	);
	foreach ( $types as $type => $t ) {
		list( $plural, $single, $slug, $icon, $public ) = $t;
		register_post_type(
			$type,
			array(
				'labels'             => array(
					'name'          => $plural,
					'singular_name' => $single,
					'add_new_item'  => 'Add new ' . strtolower( $single ),
					'edit_item'     => 'Edit ' . strtolower( $single ),
					'menu_name'     => $plural,
				),
				'public'             => $public,
				'publicly_queryable' => $public,
				'show_ui'            => true,
				'show_in_rest'       => true,
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_icon'          => $icon,
				'menu_position'      => 21,
				'supports'           => array( 'title', 'page-attributes', 'revisions' ),
				'rewrite'            => $public ? array( 'slug' => $slug, 'with_front' => false ) : false,
			)
		);
	}

	register_taxonomy(
		'ci_service_group',
		'ci_service',
		array(
			'labels'            => array( 'name' => 'Service groups', 'singular_name' => 'Service group' ),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);
	register_taxonomy(
		'ci_project_category',
		'ci_project',
		array(
			'labels'            => array( 'name' => 'Project categories', 'singular_name' => 'Project category' ),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);
	register_taxonomy(
		'ci_faq_group',
		'ci_faq',
		array(
			'labels'            => array( 'name' => 'FAQ groups', 'singular_name' => 'FAQ group' ),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);
}

// Show everything in the admin in menu_order, since order drives numbering on the site.
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() && $q->is_main_query() && in_array( $q->get( 'post_type' ), array( 'ci_service', 'ci_project', 'ci_insight', 'ci_testimonial', 'ci_team', 'ci_faq' ), true ) && ! $q->get( 'orderby' ) ) {
		$q->set( 'orderby', 'menu_order' );
		$q->set( 'order', 'ASC' );
	}
} );

// Order column in admin lists.
foreach ( array( 'ci_service', 'ci_project', 'ci_insight', 'ci_testimonial', 'ci_team', 'ci_faq' ) as $ci_type ) {
	add_filter( "manage_{$ci_type}_posts_columns", function ( $cols ) {
		$cols['ci_order'] = 'Order';
		return $cols;
	} );
	add_action( "manage_{$ci_type}_posts_custom_column", function ( $col, $id ) {
		if ( 'ci_order' === $col ) {
			echo (int) get_post_field( 'menu_order', $id );
		}
	}, 10, 2 );
}
