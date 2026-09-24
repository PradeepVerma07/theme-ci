<?php
/**
 * Small admin guide so editors can find the editable theme areas quickly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_theme_page(
		'CI360 Editing Guide',
		'CI360 Editing Guide',
		'edit_theme_options',
		'ci360-editing-guide',
		'ci360_render_editing_guide'
	);
} );

function ci360_render_editing_guide() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$settings = admin_url( 'admin.php?page=ci360-settings' );
	$menus    = admin_url( 'nav-menus.php' );
	$services = admin_url( 'edit.php?post_type=ci_service' );
	$pages    = admin_url( 'edit.php?post_type=page' );
	$custom   = admin_url( 'customize.php' );
	?>
	<div class="wrap" style="max-width:980px">
		<h1>CI360 Editing Guide</h1>
		<p>This theme keeps the branded front-end design while exposing the main content areas to WordPress and Elementor.</p>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:24px">
			<div class="card" style="max-width:none"><h2>Header &amp; logo</h2><p>Upload the header logo and edit the CTA in CI360 Settings. You can also use the native WordPress Custom Logo.</p><p><a class="button button-primary" href="<?php echo esc_url( $settings ); ?>">CI360 Settings</a> <a class="button" href="<?php echo esc_url( $custom ); ?>">Site Identity</a></p></div>
			<div class="card" style="max-width:none"><h2>Menus</h2><p>Add, remove, reorder or rename header, overlay, footer and legal menu items from Appearance → Menus.</p><p><a class="button button-primary" href="<?php echo esc_url( $menus ); ?>">Edit menus</a></p></div>
			<div class="card" style="max-width:none"><h2>Footer</h2><p>Footer logo, CTA, about text, office labels and copyright line are in CI360 Settings. Footer link lists come from WordPress menus.</p><p><a class="button button-primary" href="<?php echo esc_url( $settings ); ?>">Edit footer</a></p></div>
			<div class="card" style="max-width:none"><h2>Hero sections</h2><p>Open the page with Elementor and select its CI360 hero/archive widget. Headings, badges, copy, buttons, images and layout style are editable there.</p><p><a class="button button-primary" href="<?php echo esc_url( $pages ); ?>">Pages</a></p></div>
			<div class="card" style="max-width:none"><h2>Services page</h2><p>Edit each service title, summary, card image, visual composition, tags and order in Services. The Services Grid Elementor widget controls the card layout and display options.</p><p><a class="button button-primary" href="<?php echo esc_url( $services ); ?>">Edit services</a></p></div>
		</div>
	</div>
	<?php
}
