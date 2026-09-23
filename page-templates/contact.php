<?php
/**
 * Template Name: Contact
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$id = get_queried_object_id();
$g  = function ( $n ) use ( $id ) {
	return ci_get( $n, $id );
};
$details = '';
foreach ( ci_rows( 'contact_blocks', $id ) as $r ) {
	$details .= '<span class="small-label">' . ci_e( $r['label'] ) . '</span><a href="mailto:' . esc_attr( $r['email'] ) . '">' . ci_e( $r['email'] ) . ' ' . ci_arrow() . '</a><a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $r['phone'] ) ) . '">' . ci_e( $r['phone'] ) . '</a>';
}
$conf = implode( ' <i></i> ', array_map( 'ci_html', array_map( 'trim', explode( '|', $g( 'contact_confidential' ) ) ) ) );

$options = '';
foreach ( ci_ids( 'ci_service' ) as $sid ) {
	$options .= '<option value="' . esc_attr( get_post_field( 'post_name', $sid ) ) . '">' . ci_e( ci_title( $sid ) ) . '</option>';
}
if ( $g( 'form_extra_1' ) ) {
	$options .= '<option value="integrated">' . ci_e( $g( 'form_extra_1' ) ) . '</option>';
}
if ( $g( 'form_extra_2' ) ) {
	$options .= '<option value="not-sure">' . ci_e( $g( 'form_extra_2' ) ) . '</option>';
}
$server = 'server' === ci_opt( 'opt_contact_mode' );

$locations = '';
foreach ( ci_rows( 'opt_offices', 'option' ) as $i => $o ) {
	$locations .= '<article class="location-card" data-reveal><div class="location-illustration location-' . $i . '" aria-hidden="true"><div class="sun"></div><div class="building b1"></div><div class="building b2"></div><div class="building b3"></div><div class="ground"></div><span>0' . ( $i + 1 ) . '</span></div><div><h3>' . ci_e( $o['city'] ) . ' <small>' . ci_e( $o['country_code'] ) . '</small></h3><time data-zone="' . esc_attr( $o['zone'] ) . '">--:--</time></div><p>' . ci_e( $o['address'] ? $o['address'] : 'A connected team in ' . $o['city'] . '. Contact us to arrange a conversation.' ) . '</p><a href="mailto:' . esc_attr( $o['email'] ) . '">' . ci_e( $o['email'] ) . ' ' . ci_arrow() . '</a></article>';
}
?><main id="main"><section class="contact-page wrap"><?php echo ci_breadcrumb( $g( 'contact_crumb' ) ); ?><div class="contact-grid"><div class="contact-intro"><?php echo ci_sec_label( 'contact_hero', $id ); ?><h1 class="display" data-title><?php echo ci_html( $g( 'contact_hero_heading' ) ); ?></h1><p class="large-copy"><?php echo ci_e( $g( 'contact_lead' ) ); ?></p><div class="contact-details"><?php echo $details; ?></div><div class="contact-photo"><?php echo ci_img( $g( 'contact_image' ) ); ?><span><?php echo ci_e( $g( 'contact_image_label' ) ); ?></span></div><div class="contact-confidential"><?php echo $conf; ?></div></div><form id="enquiry-form" class="enquiry-form" novalidate><span class="small-label"><?php echo ci_e( $g( 'form_label' ) ); ?></span><h2><?php echo ci_html( $g( 'form_heading' ) ); ?></h2><p class="form-intro"><?php echo ci_e( $g( 'form_intro' ) ); ?></p><div class="form-grid"><label>Your name <span>*</span><input name="name" autocomplete="name" required minlength="2" maxlength="120" placeholder="What should we call you?"></label><label>Email address <span>*</span><input name="email" type="email" autocomplete="email" required maxlength="254" placeholder="you@company.com"></label><label>Phone number<input name="phone" type="tel" autocomplete="tel" maxlength="40" placeholder="Include your country code"></label><label>Company name<input name="company" autocomplete="organization" maxlength="160" placeholder="Your company or organisation"></label><label>Country<input name="country" autocomplete="country-name" maxlength="100" placeholder="Where are you based?"></label><label>What can we help with? <span>*</span><select name="service" required><option value="">Select a service</option><?php echo $options; ?></select></label><label class="full-width">Your message <span>*</span><textarea name="message" required minlength="20" maxlength="5000" rows="4" placeholder="The goal, the challenge, the big idea..."></textarea><small class="field-help">At least 20 characters. Please do not include confidential credentials.</small></label></div><div class="honeypot" aria-hidden="true"><label>Leave this empty<input name="website" tabindex="-1" autocomplete="off"></label></div><label class="consent"><input type="checkbox" name="consent" required><span><?php echo ci_links( ci_html( $g( 'form_consent' ) ) ); ?></span></label><div class="form-status" role="status" aria-live="polite"></div><button type="submit" class="button button-dark"><span><?php echo $server ? 'Send enquiry' : 'Review &amp; email enquiry'; ?></span><i><?php echo ci_arrow(); ?></i></button><p class="form-note"><?php echo $server ? 'Your message is sent only after you submit this form.' : 'Review your message, then send it using your email app. Nothing is silently submitted.'; ?></p></form></div></section><section class="section dark-section"><div class="wrap"><div class="section-heading"><?php echo ci_sec_label( 'contact_locations', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'contact_locations_heading' ) ); ?></h2></div><div class="locations-grid"><?php echo $locations; ?></div></div></section><?php echo ci_process(); ?><section class="faq-section section wrap"><div><?php echo ci_sec_label( 'contact_faq', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'contact_faq_heading' ) ); ?></h2></div><?php echo ci_faq( ci_faq_group( 'contact_faq_group', $id, 'contact' ) ); ?></section></main><?php
get_footer();
