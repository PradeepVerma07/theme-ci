<?php
/**
 * Single Blog post.
 *
 * Uses the same reading layout as a CI360 Insight (ci_insight() in inc/components.php
 * normalises a plain WordPress post automatically), and its "related" grid pulls in
 * other Insights, Projects and Blog posts that share a Category with this one — see
 * the category-inheritance notes in inc/cpt.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
echo '<main id="main">';
echo ci_render_insight( get_queried_object_id() ); // phpcs:ignore WordPress.Security.EscapeOutput
echo '</main>';
get_footer();
