<?php
/**
 * Demo builder: turns the page recipes (inc/demo-pages.php) into Elementor pages and
 * Elementor library templates (one per page and one per section).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once CI360_DIR . '/inc/demo-pages.php';

function ci_demo_uid() {
	return substr( md5( wp_generate_uuid4() ), 0, 7 );
}

function ci_demo_media_value( $path, $media ) {
	if ( is_array( $path ) ) {
		return $path;
	}
	if ( ! $path ) {
		return array( 'url' => '', 'id' => '' );
	}
	$id = $media[ $path ] ?? 0;
	return $id ? array( 'url' => wp_get_attachment_url( $id ), 'id' => $id ) : array( 'url' => ci_img_url( $path ), 'id' => '' );
}

/**
 * Full widget settings for a section: overrides + all images/repeaters written out
 * (images point at the media library copies).
 */
function ci_demo_settings( $id, $over, $media ) {
	$defs = ci_section_defs();
	$s    = (array) $over;
	foreach ( $defs[ $id ]['controls'] as $c ) {
		if ( empty( $c['key'] ) ) {
			continue;
		}
		$k = $c['key'];
		if ( 'image' === $c['type'] ) {
			$s[ $k ] = ci_demo_media_value( $s[ $k ] ?? ( $c['default'] ?? '' ), $media );
		} elseif ( 'repeater' === $c['type'] ) {
			$rows = $s[ $k ] ?? $c['default'];
			$out  = array();
			foreach ( (array) $rows as $row ) {
				foreach ( $c['fields'] as $f ) {
					if ( 'image' === $f['type'] ) {
						$row[ $f['key'] ] = ci_demo_media_value( $row[ $f['key'] ] ?? '', $media );
					}
				}
				$row['_id'] = ci_demo_uid();
				$out[]      = $row;
			}
			$s[ $k ] = $out;
		}
	}
	return $s;
}

function ci_demo_containers_active() {
	return class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->experiments->is_feature_active( 'container' );
}

/** Elementor element tree for one section: a full-width, zero-padding row holding the widget. */
function ci_demo_element( $id, $over, $media ) {
	$widget = array(
		'id'         => ci_demo_uid(),
		'elType'     => 'widget',
		'widgetType' => 'ci360-' . $id,
		'settings'   => ci_demo_settings( $id, $over, $media ),
		'elements'   => array(),
	);
	$zero = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true );
	if ( ci_demo_containers_active() ) {
		return array(
			'id'       => ci_demo_uid(),
			'elType'   => 'container',
			'isInner'  => false,
			'settings' => array(
				'content_width' => 'full',
				'flex_gap'      => array( 'unit' => 'px', 'size' => 0, 'column' => '0', 'row' => '0', 'isLinked' => true ),
				'padding'       => $zero,
				'_title'        => ci_section_defs()[ $id ]['title'],
				'css_classes'   => 'ci360-row e-no-lazyload',
			),
			'elements' => array( $widget ),
		);
	}
	return array(
		'id'       => ci_demo_uid(),
		'elType'   => 'section',
		'isInner'  => false,
		'settings' => array( 'layout' => 'full_width', 'gap' => 'no', 'padding' => $zero, '_title' => ci_section_defs()[ $id ]['title'], 'css_classes' => 'ci360-row e-no-lazyload' ),
		'elements' => array(
			array(
				'id'       => ci_demo_uid(),
				'elType'   => 'column',
				'isInner'  => false,
				'settings' => array( '_column_size' => 100, '_inline_size' => null, 'padding' => $zero ),
				'elements' => array( $widget ),
			),
		),
	);
}

function ci_demo_elements( $sections, $media ) {
	$out = array();
	foreach ( $sections as $row ) {
		$out[] = ci_demo_element( $row[0], $row[1], $media );
	}
	return $out;
}

function ci_demo_save_elementor( $post_id, $elements, $type ) {
	update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $post_id, '_elementor_template_type', $type );
	if ( defined( 'ELEMENTOR_VERSION' ) ) {
		update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
	}
	update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
	delete_post_meta( $post_id, '_elementor_css' );
	delete_post_meta( $post_id, '_elementor_element_cache' );
}

/** Pages: recipe stored for the no-Elementor fallback, Elementor data when Elementor is active. */
function ci_demo_build_pages( $page_ids, $media ) {
	$elementor = did_action( 'elementor/loaded' );
	foreach ( ci_demo_pages() as $key => $pg ) {
		$pid = $page_ids[ $key ] ?? 0;
		if ( ! $pid ) {
			continue;
		}
		update_post_meta( $pid, '_ci360_sections', $pg['sections'] );
		update_post_meta( $pid, '_wp_page_template', 'default' );
		if ( $elementor ) {
			ci_demo_save_elementor( $pid, ci_demo_elements( $pg['sections'], $media ), 'wp-page' );
		}
	}
	if ( $elementor ) {
		update_option( 'elementor_disable_color_schemes', 'yes' );
		update_option( 'elementor_disable_typography_schemes', 'yes' );
		update_option( 'elementor_global_image_lightbox', '' );
		// CI360 sections have their own backgrounds; Elementor's background lazy-load would hide them until scrolled.
		update_option( 'elementor_experiment-e_lazyload', 'inactive' );
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}

/** Library templates: one per page (type “page”) and one per section (type “container”/“section”). */
function ci_demo_build_library( $media ) {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	$block = ci_demo_containers_active() ? 'container' : 'section';
	$defs  = ci_section_defs();
	$done  = array();
	foreach ( ci_demo_pages() as $key => $pg ) {
		ci_demo_library_post( 'page-' . $key, 'CI360 · ' . $pg['title'] . ' (full page)', 'page', ci_demo_elements( $pg['sections'], $media ) );
		foreach ( $pg['sections'] as $row ) {
			$sig = $row[0] . md5( wp_json_encode( $row[1] ) );
			if ( isset( $done[ $sig ] ) ) {
				continue;
			}
			$done[ $sig ] = true;
			$title        = 'CI360 · ' . $defs[ $row[0] ]['title'] . ( $row[1] ? ' (' . $pg['title'] . ')' : '' );
			ci_demo_library_post( 'section-' . $key . '-' . $row[0], $title, $block, array( ci_demo_element( $row[0], $row[1], $media ) ) );
		}
	}
}

function ci_demo_library_post( $key, $title, $type, $elements ) {
	$existing = get_posts( array( 'post_type' => 'elementor_library', 'meta_key' => '_ci360_template', 'meta_value' => $key, 'numberposts' => 1, 'post_status' => 'any' ) );
	$data     = array( 'post_type' => 'elementor_library', 'post_title' => $title, 'post_status' => 'publish' );
	if ( $existing ) {
		$data['ID'] = $existing[0]->ID;
		$id         = wp_update_post( $data );
	} else {
		$id = wp_insert_post( $data );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}
	update_post_meta( $id, '_ci360_template', $key );
	wp_set_object_terms( $id, $type, 'elementor_library_type' );
	ci_demo_save_elementor( $id, $elements, $type );
}

/** Rebuild just the Elementor pages/templates (e.g. after activating Elementor later). */
function ci_demo_rebuild_elementor() {
	$media = array();
	foreach ( get_posts( array( 'post_type' => 'attachment', 'meta_key' => '_ci360_key', 'numberposts' => -1, 'post_status' => 'any' ) ) as $a ) {
		$media[ get_post_meta( $a->ID, '_ci360_key', true ) ] = $a->ID;
		$rel = get_post_meta( $a->ID, '_ci360_rel', true );
		if ( $rel ) {
			$media[ $rel ] = $a->ID;
		}
	}
	$page_ids = (array) get_option( 'ci360_pages', array() );
	ci_demo_build_pages( $page_ids, $media );
	ci_demo_build_library( $media );
	return 'Elementor pages and templates rebuilt.';
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'ci360 elementor',
		function () {
			WP_CLI::success( ci_demo_rebuild_elementor() );
		}
	);
}
