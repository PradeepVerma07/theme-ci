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
				'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions', 'elementor' ),
				'rewrite'            => $public ? array( 'slug' => $slug, 'with_front' => false ) : false,
			)
		);
	}

	register_taxonomy(
		'ci_service_category',
		'ci_service',
		array(
			'labels'            => array( 'name' => 'Service Categories', 'singular_name' => 'Service Category' ),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
		)
	);

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

/**
 * Seed Demo Service Categories and Demo Services (Elementor Editable)
 */
add_action( 'init', 'ci_seed_demo_services', 20 );

function ci_seed_demo_services() {
	if ( get_option( 'ci360_demo_services_seeded_v1' ) ) {
		return;
	}

	$categories = array(
		'Social Media'           => 'Social media strategy, content creation & community growth.',
		'Performance Marketing'  => 'Paid media, Google Ads, Meta Ads & conversion optimization.',
		'Creative & Design'      => 'Brand design, copywriting, video production & motion graphics.',
		'Search & AI'            => 'SEO, AI-driven visibility & local search optimization.',
		'Strategic Storytelling' => 'Brand narratives & positioning strategy.',
	);

	$cat_term_ids = array();
	foreach ( $categories as $cat_name => $cat_desc ) {
		$term = get_term_by( 'name', $cat_name, 'ci_service_category' );
		if ( ! $term ) {
			$inserted = wp_insert_term( $cat_name, 'ci_service_category', array( 'description' => $cat_desc ) );
			if ( ! is_wp_error( $inserted ) ) {
				$cat_term_ids[ $cat_name ] = $inserted['term_id'];
			}
		} else {
			$cat_term_ids[ $cat_name ] = $term->term_id;
		}
	}

	$demo_services = array(
		array(
			'title'    => 'Social Media Marketing',
			'category' => 'Social Media',
			'excerpt'  => 'Creating platform-led strategies and content that spark conversations, build communities, and strengthen brand engagement.',
			'content'  => '<!-- wp:paragraph --><p>Social media is more than just posting — it’s about people, conversations, and real connections. We help brands show up with purpose, create engaging content, and build communities that drive meaningful business results.</p><!-- /wp:paragraph -->',
		),
		array(
			'title'    => 'Performance Marketing',
			'category' => 'Performance Marketing',
			'excerpt'  => 'Driving measurable growth through Google Ads, Meta Ads, paid media, lead generation, retargeting, and conversion-focused campaigns.',
			'content'  => '<!-- wp:paragraph --><p>Data-driven performance marketing engineered for scale, high ROI, and sustainable customer acquisition.</p><!-- /wp:paragraph -->',
		),
		array(
			'title'    => 'Content & Creative',
			'category' => 'Creative & Design',
			'excerpt'  => 'Creating compelling content, campaigns, videos, reels, animation, and communication designed to inform, engage, and inspire action.',
			'content'  => '<!-- wp:paragraph --><p>Elevate your visual identity with storytelling assets that stop the scroll and elevate brand perception.</p><!-- /wp:paragraph -->',
		),
		array(
			'title'    => 'Search, AI & Local Visibility',
			'category' => 'Search & AI',
			'excerpt'  => 'Helping brands get discovered through search, AI, and local presence, driving high-intent traffic and real opportunities.',
			'content'  => '<!-- wp:paragraph --><p>Dominate search engine results pages and modern AI-driven search models with structured optimization.</p><!-- /wp:paragraph -->',
		),
		array(
			'title'    => 'Strategic Storytelling',
			'category' => 'Strategic Storytelling',
			'excerpt'  => 'Building powerful brand narratives that create relevance, meaningful connections, and lasting recall.',
			'content'  => '<!-- wp:paragraph --><p>Craft an unforgettable brand story that connects emotionally and positions your company for industry leadership.</p><!-- /wp:paragraph -->',
		),
	);

	foreach ( $demo_services as $s ) {
		$existing = get_page_by_title( $s['title'], OBJECT, 'ci_service' );
		if ( ! $existing ) {
			$post_id = wp_insert_post( array(
				'post_title'   => $s['title'],
				'post_excerpt' => $s['excerpt'],
				'post_content' => $s['content'],
				'post_type'    => 'ci_service',
				'post_status'  => 'publish',
			) );

			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_wp_page_template', 'template-service.php' );
				if ( isset( $cat_term_ids[ $s['category'] ] ) ) {
					wp_set_object_terms( $post_id, array( $cat_term_ids[ $s['category'] ] ), 'ci_service_category' );
				}
			}
		}
	}

	update_option( 'ci360_demo_services_seeded_v1', 1 );
}

