<?php
/**
 * CI360 Degrees – Hello Elementor child theme bootstrap.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CI360_VERSION', '2.7.0' );
define( 'CI360_DIR', get_stylesheet_directory() );
define( 'CI360_URI', get_stylesheet_directory_uri() );

require CI360_DIR . '/inc/setup.php';
require CI360_DIR . '/inc/hello.php';
require CI360_DIR . '/inc/cpt.php';
require CI360_DIR . '/inc/fields.php';
require CI360_DIR . '/inc/helpers.php';
require CI360_DIR . '/inc/components.php';
require CI360_DIR . '/inc/sections-config.php';
require CI360_DIR . '/inc/sections.php';
require CI360_DIR . '/inc/singles.php';
require CI360_DIR . '/inc/enquiry.php';
require CI360_DIR . '/inc/elementor.php';
require CI360_DIR . '/inc/importer.php';
require CI360_DIR . '/inc/demo.php';
require CI360_DIR . '/inc/admin-guide.php';
