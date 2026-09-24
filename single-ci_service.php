<?php
/**
 * Single service.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
echo '<main id="main">';
echo ci_render_service( get_queried_object_id() ); // phpcs:ignore WordPress.Security.EscapeOutput
echo '</main>';
get_footer();
