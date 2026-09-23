<?php
/**
 * CI360 Degrees theme bootstrap.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CI360_VERSION', '1.0.0' );
define( 'CI360_DIR', get_template_directory() );
define( 'CI360_URI', get_template_directory_uri() );

require CI360_DIR . '/inc/setup.php';
require CI360_DIR . '/inc/cpt.php';
require CI360_DIR . '/inc/fields.php';
require CI360_DIR . '/inc/helpers.php';
require CI360_DIR . '/inc/components.php';
require CI360_DIR . '/inc/enquiry.php';
require CI360_DIR . '/inc/importer.php';
