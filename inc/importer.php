<?php
/**
 * One-click setup: imports all prototype content into CPTs, fields, pages,
 * menus and the media library. Safe to run again (updates, never duplicates).
 *
 * Runs automatically the first time the theme is activated (if a fields plugin
 * is active and no services exist yet), from Appearance › CI360 Setup, or with
 * `wp ci360 import`.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_switch_theme', function () {
	if ( function_exists( 'update_field' ) && ! get_posts( array( 'post_type' => 'ci_service', 'post_status' => 'any', 'numberposts' => 1 ) ) ) {
		ci_register_content_types();
		$result = ci_import();
		set_transient( 'ci360_import_notice', $result, 60 );
	}
} );

add_action( 'admin_notices', function () {
	$msg = get_transient( 'ci360_import_notice' );
	if ( $msg ) {
		delete_transient( 'ci360_import_notice' );
		echo '<div class="notice notice-success is-dismissible"><p><strong>CI360:</strong> ' . esc_html( $msg ) . '</p></div>';
	}
} );

add_action( 'admin_menu', function () {
	add_theme_page( 'CI360 Setup', 'CI360 Setup', 'manage_options', 'ci360-setup', 'ci_setup_page' );
} );

function ci_setup_page() {
	$done = '';
	if ( isset( $_POST['ci360_import'] ) && check_admin_referer( 'ci360_import' ) && current_user_can( 'manage_options' ) ) {
		$done = ci_import();
	}
	echo '<div class="wrap"><h1>CI360 Setup</h1>';
	if ( ! function_exists( 'update_field' ) ) {
		echo '<div class="notice notice-error"><p>Install and activate <strong>Secure Custom Fields</strong> or <strong>ACF Pro</strong> first.</p></div></div>';
		return;
	}
	if ( $done ) {
		echo '<div class="notice notice-success"><p>' . esc_html( $done ) . '</p></div>';
	}
	echo '<p>This imports every service, project, insight, testimonial, team member, FAQ, page, menu and image from the CI360 design, and sets the Home page as the front page.</p>';
	echo '<p>Running it again re-imports the original services, projects, insights, team, FAQs, lists, images and menus (posts you added yourself are left alone). Single text fields you have edited on pages and in CI360 Settings are kept.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'ci360_import' );
	submit_button( 'Import / restore CI360 content', 'primary', 'ci360_import' );
	echo '</form></div>';
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'ci360 import',
		function () {
			WP_CLI::success( ci_import() );
		}
	);
}

/* ------------------------------------------------------------------ Import */

function ci_import() {
	if ( ! function_exists( 'update_field' ) ) {
		return 'No fields plugin active.';
	}
	@set_time_limit( 300 ); // phpcs:ignore
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$seed  = json_decode( file_get_contents( CI360_DIR . '/data/seed.json' ), true ); // phpcs:ignore
	$media = ci_import_media( $seed['alts'] );
	$m     = function ( $key ) use ( $media ) {
		return $media[ $key ] ?? 0;
	};
	$u = function ( $name, $value, $id ) {
		update_field( 'field_ci_' . $name, $value, $id );
	};

	/* Terms */
	$group_ids = array();
	foreach ( $seed['services'] as $s ) {
		$group_ids[ $s['group'] ] = ci_import_term( $s['group'], 'ci_service_group' );
	}
	$cat_ids = array();
	foreach ( $seed['categories'] as $c ) {
		$tid                  = ci_import_term( $c['name'], 'ci_project_category' );
		$cat_ids[ $c['name'] ] = $tid;
		$term                 = 'term_' . $tid;
		$u( 'cat_headline', $c['headline'], $term );
		$u( 'cat_p1', $c['p1'], $term );
		$u( 'cat_p2', $c['p2'], $term );
		$u( 'cat_in_filter', $c['in_filter'] ? 1 : 0, $term );
		$u( 'cat_filter_order', $c['order'], $term );
	}
	$faq_groups = array(
		'home'    => ci_import_term( 'Home', 'ci_faq_group', 'home' ),
		'contact' => ci_import_term( 'Contact', 'ci_faq_group', 'contact' ),
	);

	/* Services */
	$service_ids = array();
	foreach ( $seed['services'] as $i => $s ) {
		$id                         = ci_import_post( 'ci_service', $s['slug'], $s['title'], $i );
		$service_ids[ $s['slug'] ] = $id;
		wp_set_object_terms( $id, array( $group_ids[ $s['group'] ] ), 'ci_service_group' );
		$u( 'service_number', $s['number'], $id );
		$u( 'service_short', $s['short'], $id );
		$u( 'service_summary', $s['summary'], $id );
		$u( 'service_headline', $s['headline'], $id );
		$u( 'service_body', $s['body'], $id );
		$u( 'service_tags', array_map( function ( $t ) {
			return array( 'tag' => $t );
		}, $s['tags'] ), $id );
		$u( 'service_steps', $s['steps'], $id );
		$u( 'service_tone', $s['tone'], $id );
		$u( 'service_visual', $s['visual'], $id );
		$u( 'service_image', $m( $s['image'] ), $id );
		$u( 'service_visual_images', array_values( array_filter( array_map( $m, $s['visual_images'] ) ) ), $id );
	}

	/* Projects */
	$project_ids = array();
	foreach ( $seed['projects'] as $i => $p ) {
		$id                         = ci_import_post( 'ci_project', $p['slug'], $p['title'], $i );
		$project_ids[ $p['slug'] ] = $id;
		wp_set_object_terms( $id, array( $cat_ids[ $p['category'] ] ), 'ci_project_category' );
		$u( 'project_type', $p['type'], $id );
		$u( 'project_featured', $p['featured'], $id );
		$u( 'project_headline', $p['headline'], $id );
		$u( 'project_summary', $p['summary'], $id );
		$u( 'project_case_sections', $p['case_sections'], $id );
		$u( 'project_disclosure', $p['disclosure'], $id );
		$u( 'project_tone', $p['tone'], $id );
		$u( 'project_image', $m( $p['image'] ), $id );
		$u( 'project_scene', $p['scene'], $id );
		$u( 'project_scene_front', $m( $p['scene_front'] ), $id );
		$u( 'project_scene_side', $p['scene_side'] ? $m( $p['scene_side'] ) : '', $id );
		$u( 'project_scene_caption', $p['scene_caption'], $id );
		$u( 'project_gallery', array_values( array_filter( array_map( $m, $p['gallery'] ) ) ), $id );
		$u( 'project_note', $p['note'], $id );
	}

	/* Insights */
	foreach ( $seed['insights'] as $i => $a ) {
		$id = ci_import_post( 'ci_insight', $a['slug'], $a['title'], $i );
		$u( 'insight_kicker', $a['kicker'], $id );
		$u( 'insight_read', $a['read'], $id );
		$u( 'insight_intro', $a['intro'], $id );
		$u( 'insight_tone', $a['tone'], $id );
		$u( 'insight_image', $m( $a['image'] ), $id );
		$u( 'insight_sections', $a['sections'], $id );
	}

	/* Testimonials, team, FAQs */
	foreach ( $seed['testimonials'] as $i => $t ) {
		$id = ci_import_post( 'ci_testimonial', sanitize_title( $t['name'] ), $t['name'], $i );
		$u( 'testimonial_quote', $t['quote'], $id );
		$u( 'testimonial_company', $t['company'], $id );
	}
	foreach ( $seed['team'] as $i => $t ) {
		$id = ci_import_post( 'ci_team', sanitize_title( $t['title'] ), $t['title'], $i );
		$u( 'team_role', $t['role'], $id );
		$u( 'team_is_founder', empty( $t['is_founder'] ) ? 0 : 1, $id );
		if ( ! empty( $t['is_founder'] ) ) {
			$u( 'founder_role', $t['founder_role'], $id );
			$u( 'founder_initials', $t['initials'], $id );
			$u( 'founder_lens', $t['lens'], $id );
			$u( 'founder_quote', $t['quote'], $id );
			$u( 'founder_tone', $t['tone'], $id );
			if ( ! get_field( 'founder_portrait', $id ) ) {
				$u( 'founder_portrait', ci_import_remote_image( $t['portrait_url'], $t['title'] ), $id );
			}
		}
	}
	foreach ( $seed['faqs'] as $group => $items ) {
		foreach ( $items as $i => $f ) {
			$id = ci_import_post( 'ci_faq', $group . '-' . sanitize_title( $f[0] ), $f[0], $i );
			wp_set_object_terms( $id, array( $faq_groups[ $group ] ), 'ci_faq_group' );
			$u( 'faq_answer', $f[1], $id );
		}
	}

	/* Options */
	$o = 'option';
	$u( 'opt_offices', $seed['options']['offices'], $o );
	$u( 'opt_socials', $seed['options']['socials'], $o );
	$u( 'opt_clients', $seed['options']['clients'], $o );
	$u( 'opt_process_steps', $seed['options']['process_steps'], $o );
	$u( 'opt_menu_image', $m( 'crave-family' ), $o );
	$u( 'opt_404_image', $m( 'crave-play' ), $o );

	/* Pages */
	$pages = array(
		'home'                 => array( 'Home', 'default', '' ),
		'about'                => array( 'About CI360', 'about.php', 'about' ),
		'founders'             => array( 'Our Founders', 'founders.php', 'founders' ),
		'services'             => array( 'Our Services', 'services.php', 'services' ),
		'work'                 => array( 'Selected Work', 'work.php', 'work' ),
		'insights'             => array( 'Insights & Perspectives', 'insights.php', 'insights' ),
		'contact'              => array( 'Start a Conversation', 'contact.php', 'contact' ),
		'privacy-policy'       => array( 'Privacy policy', 'legal.php', 'privacy-policy' ),
		'terms-and-conditions' => array( 'Terms & conditions', 'legal.php', 'terms-and-conditions' ),
	);
	$page_ids = array();
	foreach ( $pages as $key => $pg ) {
		$slug             = $pg[2] ? $pg[2] : 'home';
		$id               = ci_import_page( $key, $slug, $pg[0] );
		$page_ids[ $key ] = $id;
		update_post_meta( $id, '_wp_page_template', 'default' === $pg[1] ? 'default' : 'page-templates/' . $pg[1] );
	}
	update_option( 'ci360_pages', $page_ids );
	update_option( 'wp_page_for_privacy_policy', $page_ids['privacy-policy'] );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_ids['home'] );

	$h = $page_ids['home'];
	$u( 'home_collage', array_map( function ( $r ) use ( $project_ids, $m ) {
		return array( 'project' => $project_ids[ $r['project'] ] ?? '', 'image' => $m( $r['image'] ), 'caption' => $r['caption'] );
	}, $seed['home']['collage'] ), $h );
	$u( 'home_about_image', $m( 'studio' ), $h );
	$u( 'home_faq_group', $faq_groups['home'], $h );

	$a = $page_ids['about'];
	$u( 'about_image', $m( 'studio' ), $a );
	$u( 'about_values', array_map( function ( $v ) {
		return array( 'value' => $v );
	}, $seed['about']['values'] ), $a );
	$u( 'about_belief_text', $seed['about']['belief'], $a );
	$u( 'about_timeline', $seed['about']['timeline'], $a );
	$u( 'about_industries_list', array_map( function ( $v ) {
		return array( 'name' => $v );
	}, $seed['about']['industries'] ), $a );

	$f = $page_ids['founders'];
	$u( 'founders_fallback_image', $m( 'studio' ), $f );
	$u( 'founders_studio_image', $m( 'studio' ), $f );
	$u( 'founders_belief_text', $seed['founders']['belief'], $f );

	$sv = $page_ids['services'];
	$u( 'services_hero_service', $service_ids['websites-digital-experiences'] ?? '', $sv );
	$u( 'services_faq_group', $faq_groups['contact'], $sv );

	$u( 'insights_topic_list', array_map( function ( $v ) {
		return array( 'topic' => $v );
	}, $seed['insights_topics'] ), $page_ids['insights'] );

	$c = $page_ids['contact'];
	$u( 'contact_blocks', $seed['contact_blocks'], $c );
	$u( 'contact_image', $m( 'studio' ), $c );
	$u( 'contact_faq_group', $faq_groups['contact'], $c );

	foreach ( $seed['legal'] as $key => $l ) {
		$u( 'legal_intro', $l['intro'], $page_ids[ $key ] );
		$u( 'legal_sections', $l['sections'], $page_ids[ $key ] );
	}

	/* Menus */
	$nav = array(
		'desktop' => array( 'about' => 'About', 'founders' => 'Founders', 'services' => 'Services', 'work' => 'Work', 'insights' => 'Insights' ),
		'overlay' => array( 'home' => 'Home', 'about' => 'About', 'founders' => 'Founders', 'services' => 'Services', 'work' => 'Work', 'insights' => 'Insights', 'contact' => 'Contact' ),
		'footer'  => array( 'about' => 'About', 'founders' => 'Founders', 'services' => 'Services', 'work' => 'Work', 'insights' => 'Insights', 'contact' => 'Contact' ),
		'legal'   => array( 'privacy-policy' => 'Privacy policy', 'terms-and-conditions' => 'Terms & conditions' ),
	);
	$names     = array( 'desktop' => 'CI360 Header', 'overlay' => 'CI360 Full-screen menu', 'footer' => 'CI360 Footer', 'legal' => 'CI360 Legal' );
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	foreach ( $nav as $loc => $items ) {
		$menu = wp_get_nav_menu_object( $names[ $loc ] );
		if ( $menu ) {
			foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $old ) {
				wp_delete_post( $old->ID, true );
			}
			$menu_id = $menu->term_id;
		} else {
			$menu_id = wp_create_nav_menu( $names[ $loc ] );
		}
		$pos = 1;
		foreach ( $items as $key => $label ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => $label,
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_ids[ $key ],
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $pos++,
				)
			);
		}
		$locations[ $loc ] = $menu_id;
	}
	set_theme_mod( 'nav_menu_locations', $locations );

	/* Site settings */
	update_option( 'blogname', 'CI360 Degrees' );
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules();
	update_option( 'ci360_imported', time() );

	return sprintf( 'Imported %d services, %d projects, %d insights, %d testimonials, %d team members, %d images, pages and menus.', count( $seed['services'] ), count( $seed['projects'] ), count( $seed['insights'] ), count( $seed['testimonials'] ), count( $seed['team'] ), count( $media ) );
}

/** Create or update a post identified by type + slug. */
function ci_import_post( $type, $slug, $title, $order ) {
	$existing = get_posts( array( 'post_type' => $type, 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1 ) );
	$data     = array(
		'post_type'   => $type,
		'post_name'   => $slug,
		'post_title'  => $title,
		'post_status' => 'publish',
		'menu_order'  => $order,
	);
	if ( $existing ) {
		$data['ID'] = $existing[0]->ID;
		wp_update_post( $data );
		return $existing[0]->ID;
	}
	return wp_insert_post( $data );
}

/** Pages: reuse the page created before, or an existing page at the same path (e.g. WordPress's draft privacy page). */
function ci_import_page( $key, $slug, $title ) {
	$map = (array) get_option( 'ci360_pages', array() );
	$id  = ( ! empty( $map[ $key ] ) && get_post_status( $map[ $key ] ) && 'trash' !== get_post_status( $map[ $key ] ) ) ? (int) $map[ $key ] : 0;
	if ( ! $id ) {
		$page = get_page_by_path( $slug );
		$id   = $page ? (int) $page->ID : 0;
	}
	$data = array(
		'post_type'   => 'page',
		'post_name'   => $slug,
		'post_title'  => $title,
		'post_status' => 'publish',
	);
	if ( $id ) {
		$data['ID'] = $id;
		wp_update_post( $data );
		return $id;
	}
	return wp_insert_post( $data );
}

function ci_import_term( $name, $tax, $slug = '' ) {
	$t = term_exists( $slug ? $slug : $name, $tax );
	if ( ! $t ) {
		$t = wp_insert_term( $name, $tax, $slug ? array( 'slug' => $slug ) : array() );
	}
	return is_wp_error( $t ) ? 0 : (int) $t['term_id'];
}

/** Copies the theme's bundled images into the media library (once). */
function ci_import_media( $alts ) {
	$map = array();
	foreach ( glob( CI360_DIR . '/assets/images/*.{webp,jpg,jpeg,png}', GLOB_BRACE ) as $file ) {
		$key      = pathinfo( $file, PATHINFO_FILENAME );
		$existing = get_posts( array( 'post_type' => 'attachment', 'meta_key' => '_ci360_key', 'meta_value' => $key, 'numberposts' => 1, 'post_status' => 'any' ) );
		if ( $existing ) {
			$map[ $key ] = $existing[0]->ID;
			continue;
		}
		$tmp = wp_tempnam( basename( $file ) );
		copy( $file, $tmp );
		$id = media_handle_sideload( array( 'name' => basename( $file ), 'tmp_name' => $tmp ), 0, $alts[ $key ] ?? $key );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp ); // phpcs:ignore
			continue;
		}
		update_post_meta( $id, '_ci360_key', $key );
		update_post_meta( $id, '_wp_attachment_image_alt', $alts[ $key ] ?? '' );
		$map[ $key ] = $id;
	}
	return $map;
}

function ci_import_remote_image( $url, $name ) {
	$tmp = download_url( $url, 20 );
	if ( is_wp_error( $tmp ) ) {
		return '';
	}
	$id = media_handle_sideload( array( 'name' => basename( wp_parse_url( $url, PHP_URL_PATH ) ), 'tmp_name' => $tmp ), 0, $name );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp ); // phpcs:ignore
		return '';
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $name );
	return $id;
}
