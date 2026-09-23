<?php
/**
 * Contact form: REST endpoint that saves each enquiry and emails it.
 * POST /wp-json/ci360/v1/enquiry
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_post_type(
		'ci_enquiry',
		array(
			'labels'          => array( 'name' => 'Enquiries', 'singular_name' => 'Enquiry' ),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 22,
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
} );

add_action( 'rest_api_init', function () {
	register_rest_route(
		'ci360/v1',
		'/enquiry',
		array(
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => 'ci_handle_enquiry',
		)
	);
} );

function ci_handle_enquiry( WP_REST_Request $req ) {
	$d = (array) $req->get_json_params();
	if ( ! empty( $d['website'] ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 ); // Honeypot: pretend success.
	}
	// Basic rate limit: 5 per 10 minutes per IP.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$key = 'ci_enq_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 5 ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'Too many enquiries. Please email us directly.' ), 429 );
	}
	set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );

	$f = array(
		'name'    => sanitize_text_field( $d['name'] ?? '' ),
		'email'   => sanitize_email( $d['email'] ?? '' ),
		'phone'   => sanitize_text_field( $d['phone'] ?? '' ),
		'company' => sanitize_text_field( $d['company'] ?? '' ),
		'country' => sanitize_text_field( $d['country'] ?? '' ),
		'service' => sanitize_text_field( $d['serviceLabel'] ?? ( $d['service'] ?? '' ) ),
		'message' => sanitize_textarea_field( $d['message'] ?? '' ),
	);
	if ( mb_strlen( $f['name'] ) < 2 || ! is_email( $f['email'] ) || '' === $f['service'] || mb_strlen( $f['message'] ) < 20 || empty( $d['consent'] ) ) {
		return new WP_REST_Response( array( 'ok' => false, 'error' => 'Please complete the required fields.' ), 400 );
	}
	$body = "New website enquiry\n\nName: {$f['name']}\nEmail: {$f['email']}\nPhone: " . ( $f['phone'] ? $f['phone'] : 'Not provided' ) . "\nCompany: " . ( $f['company'] ? $f['company'] : 'Not provided' ) . "\nCountry: " . ( $f['country'] ? $f['country'] : 'Not provided' ) . "\nService: {$f['service']}\n\nMessage:\n{$f['message']}\n\nConsent: I agree that CI360 may use my details to respond to this enquiry.";

	wp_insert_post(
		array(
			'post_type'    => 'ci_enquiry',
			'post_status'  => 'private',
			'post_title'   => $f['name'] . ( $f['company'] ? ' – ' . $f['company'] : '' ),
			'post_content' => $body,
		)
	);
	$to = ci_opt( 'opt_enquiry_to' );
	$to = $to ? $to : ci_opt( 'opt_email' );
	wp_mail( $to, 'Website enquiry - ' . ( $f['company'] ? $f['company'] : $f['name'] ), $body, array( 'Reply-To: ' . $f['name'] . ' <' . $f['email'] . '>' ) );

	return new WP_REST_Response( array( 'ok' => true ), 200 );
}
