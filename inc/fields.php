<?php
/**
 * All field groups, registered in code (no import needed).
 * Every text field carries the prototype copy as its default value, so a fresh
 * install renders the design exactly, and editors see the text pre-filled.
 *
 * Works with ACF Pro or the free Secure Custom Fields plugin (both include
 * repeaters, galleries and options pages).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$GLOBALS['ci_defaults'] = array();

const CI_HTML_HINT = 'Allowed: <br> for a line break, <em>…</em> for the italic accent words.';

/** Field factory. */
function ci_f( $type, $name, $label, $default = null, $extra = array() ) {
	$f = array(
		'key'   => 'field_ci_' . $name,
		'name'  => $name,
		'label' => $label,
		'type'  => $type,
	);
	if ( null !== $default ) {
		$f['default_value']               = $default;
		$GLOBALS['ci_defaults'][ $name ] = $default;
	}
	switch ( $type ) {
		case 'textarea':
			$f['new_lines'] = '';
			$f['rows']      = 3;
			break;
		case 'image':
			$f['return_format'] = 'id';
			$f['preview_size']  = 'medium';
			break;
		case 'gallery':
			$f['return_format'] = 'id';
			$f['preview_size']  = 'medium';
			break;
		case 'post_object':
		case 'relationship':
			$f['return_format'] = 'id';
			break;
		case 'true_false':
			$f['ui'] = 1;
			break;
	}
	return array_merge( $f, $extra );
}
function ci_t( $name, $label, $default = '', $extra = array() ) {
	return ci_f( 'text', $name, $label, $default, $extra );
}
function ci_h( $name, $label, $default = '', $extra = array() ) {
	return ci_f( 'text', $name, $label, $default, array_merge( array( 'instructions' => CI_HTML_HINT ), $extra ) );
}
function ci_ta( $name, $label, $default = '', $extra = array() ) {
	return ci_f( 'textarea', $name, $label, $default, $extra );
}
function ci_paras( $name, $label, $default = '' ) {
	return ci_f( 'textarea', $name, $label, $default, array( 'rows' => 8, 'instructions' => 'Leave an empty line between paragraphs.' ) );
}
function ci_tab( $label ) {
	static $i = 0;
	$i++;
	return array(
		'key'   => 'field_ci_tab_' . $i,
		'label' => $label,
		'type'  => 'tab',
	);
}
function ci_rep( $name, $label, $subs, $extra = array() ) {
	foreach ( $subs as &$s ) {
		$s['key'] = 'field_ci_' . $name . '__' . $s['name'];
	}
	return ci_f( 'repeater', $name, $label, null, array_merge( array( 'sub_fields' => $subs, 'layout' => 'block', 'button_label' => 'Add row' ), $extra ) );
}
function ci_sel( $name, $label, $choices, $default ) {
	return ci_f( 'select', $name, $label, $default, array( 'choices' => $choices ) );
}
/** Section heading block: two labels, heading, optional intro. */
function ci_sec( $p, $title, $l1, $l2, $heading, $intro = null ) {
	$out = array(
		ci_f( 'message', $p . '_msg', $title, null, array( 'message' => '' ) ),
		ci_t( $p . '_label_1', 'Label (left)', $l1, array( 'wrapper' => array( 'width' => 50 ) ) ),
		ci_t( $p . '_label_2', 'Label (right)', $l2, array( 'wrapper' => array( 'width' => 50 ) ) ),
		ci_h( $p . '_heading', 'Heading', $heading ),
	);
	if ( null !== $intro ) {
		$out[] = ci_ta( $p . '_intro', 'Intro', $intro );
	}
	return $out;
}
function ci_tones() {
	return array( 'orange' => 'Orange', 'pink' => 'Pink', 'lilac' => 'Lilac', 'yellow' => 'Yellow', 'mint' => 'Mint', 'blue' => 'Blue' );
}
function ci_loc_template( $file ) {
	return array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-templates/' . $file ) ) );
}
function ci_loc_type( $type ) {
	return array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => $type ) ) );
}
function ci_group( $key, $title, $fields, $location, $extra = array() ) {
	acf_add_local_field_group(
		array_merge(
			array(
				'key'                   => 'group_ci_' . $key,
				'title'                 => $title,
				'fields'                => $fields,
				'location'              => $location,
				'position'              => 'acf_after_title',
				'style'                 => 'default',
				'label_placement'       => 'top',
				'hide_on_screen'        => array( 'the_content', 'excerpt', 'discussion', 'comments' ),
			),
			$extra
		)
	);
}

add_action( 'acf/init', 'ci_register_options_pages' );
function ci_register_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title' => 'CI360 Site Settings',
			'menu_title' => 'CI360 Settings',
			'menu_slug'  => 'ci360-settings',
			'icon_url'   => 'dashicons-admin-site-alt3',
			'position'   => 3,
			'redirect'   => false,
		)
	);
}

add_action( 'acf/init', 'ci_register_fields' );
function ci_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$tones = ci_tones();

	/* ---------------------------------------------------------------- Options */
	ci_group(
		'options',
		'Site settings',
		array_merge(
			array(
				ci_tab( 'General' ),
				ci_t( 'opt_brand', 'Brand name', 'CI360 Degrees' ),
				ci_t( 'opt_home_title', 'Home page browser title', 'Stories that move brands forward' ),
				ci_t( 'opt_email', 'Main email', 'pramit.ghosh@ci360degrees.com' ),
				ci_t( 'opt_phone', 'Main phone (display)', '+91 97118 09099' ),
				ci_ta( 'opt_meta_description', 'Default meta description', 'CI360 Degrees is an integrated digital marketing and strategic communication agency helping businesses transform ideas into impactful brand experiences and measurable growth.' ),
				ci_t( 'opt_locations_line', 'Locations line', 'Ahmedabad · Delhi · Greenville' ),
				ci_rep( 'opt_socials', 'Social links', array( ci_t( 'label', 'Label' ), ci_f( 'url', 'url', 'URL' ) ) ),

				ci_tab( 'Header & menu' ),
				ci_f( 'message', 'opt_header_help', 'Header editing', null, array( 'message' => '<strong>Logo:</strong> upload below or use Appearance → Customize → Site Identity. <strong>Menu items:</strong> use Appearance → Menus and assign a menu to “Header (desktop bar)” and “Full-screen menu”.' ) ),
				ci_f( 'image', 'opt_header_logo', 'Header logo image (optional)' ),
				ci_f( 'number', 'opt_header_logo_width', 'Header logo width (px)', 142, array( 'min' => 60, 'max' => 320, 'step' => 1 ) ),
				ci_t( 'opt_logo_text', 'Fallback logo text', 'ci360' ),
				ci_t( 'opt_logo_small', 'Logo small text', 'DEGREES' ),
				ci_t( 'opt_header_cta', 'Header button text', "Let's talk" ),
				ci_t( 'opt_header_cta_link', 'Header button link', '/contact/' ),
				ci_t( 'opt_menu_label', 'Menu overlay label', 'A NEW PERSPECTIVE.' ),
				ci_f( 'image', 'opt_menu_image', 'Menu overlay image' ),
				ci_h( 'opt_menu_text', 'Menu overlay text', 'Ideas into impact.<br>That&apos;s our kind of story.' ),

				ci_tab( 'Footer' ),
				ci_f( 'message', 'opt_footer_help', 'Footer editing', null, array( 'message' => '<strong>Footer logo:</strong> upload below. <strong>Explore and legal links:</strong> use Appearance → Menus and assign menus to the footer locations.' ) ),
				ci_f( 'image', 'opt_footer_logo', 'Footer logo image (optional)' ),
				ci_f( 'number', 'opt_footer_logo_width', 'Footer logo width (px)', 160, array( 'min' => 60, 'max' => 360, 'step' => 1 ) ),
				ci_t( 'opt_footer_label_1', 'CTA label (left)', 'NEXT CHAPTER' ),
				ci_t( 'opt_footer_label_2', 'CTA label (right)', 'START A CONVERSATION' ),
				ci_h( 'opt_footer_heading', 'CTA heading', 'Let&apos;s make<br>your <em>next move.</em>' ),
				ci_t( 'opt_footer_link', 'CTA link', '/contact/' ),
				ci_h( 'opt_footer_sticker', 'Sticker text', 'A FULL CIRCLE<br>OF POSSIBILITIES' ),
				ci_ta( 'opt_footer_about', 'About text', 'An integrated digital marketing and strategic communication agency built around the power of strategic storytelling.' ),
				ci_t( 'opt_footer_explore', 'Links label', 'EXPLORE' ),
				ci_t( 'opt_footer_offices', 'Offices label', 'THREE LOCATIONS. ONE CONNECTED TEAM.' ),
				ci_t( 'opt_footer_copyright', 'Copyright line', '© {year} {brand}.' ),
				ci_t( 'opt_footer_back_top_label', 'Back to top button text', 'Back to top' ),

				ci_tab( 'Offices' ),
				ci_rep(
					'opt_offices',
					'Offices',
					array(
						ci_t( 'city', 'City' ),
						ci_t( 'country_code', 'Country code (IN / US)' ),
						ci_t( 'zone', 'Time zone (e.g. Asia/Kolkata)' ),
						ci_ta( 'address', 'Address' ),
						ci_t( 'email', 'Email' ),
						ci_t( 'phone', 'Phone' ),
					)
				),

				ci_tab( 'Client strip' ),
				ci_t( 'opt_clients_label', 'Label', 'IN GOOD COMPANY' ),
				ci_rep( 'opt_clients', 'Client names (style follows position 1–8)', array( ci_t( 'name', 'Name' ) ) ),
			),
			array( ci_tab( 'Process' ) ),
			ci_sec( 'opt_process', 'Process section', 'THE PROCESS', 'FROM FIRST QUESTION TO NEXT CHAPTER', 'One story.<br><em>Connected thinking.</em>', 'We begin with the business challenge, not the marketing channel.' ),
			array( ci_rep( 'opt_process_steps', 'Steps', array( ci_t( 'title', 'Title' ), ci_ta( 'text', 'Text' ) ) ) ),
			array( ci_tab( 'Testimonials' ) ),
			ci_sec( 'opt_testimonials', 'Testimonials section', 'KIND WORDS', 'PARTNERSHIPS THAT SPEAK', 'Good work.<br><em>Better together.</em>' ),
			array(
				ci_tab( 'Call to action' ),
				ci_t( 'opt_cta_text', 'Default CTA heading', 'A new chapter starts with a conversation.' ),
				ci_t( 'opt_cta_button', 'CTA button text', "Let's talk" ),
				ci_t( 'opt_cta_link', 'CTA button link', '/contact/' ),

				ci_tab( 'Sector notes' ),
				ci_t( 'opt_sector_headline', 'Default sector headline', 'A clear story. A connected experience.' ),
				ci_ta( 'opt_sector_p1', 'Default paragraph 1', 'Every brand arrives with its own context, audience and ambition. Our approach starts by understanding that context before deciding what the communication should become.' ),
				ci_ta( 'opt_sector_p2', 'Default paragraph 2', 'Strategy, identity, content, digital experience and performance are most useful when they are working towards the same objective, rather than existing as disconnected activities.' ),

				ci_tab( 'Service pages' ),
				ci_t( 'tpl_service_capability', 'Hero label prefix', 'CAPABILITY' ),
				ci_t( 'tpl_service_label_2', 'Hero label (right)', 'PART OF ONE INTEGRATED APPROACH' ),
				ci_t( 'tpl_service_cta', 'Hero button (%s = group)', 'Let&apos;s talk %s' ),
				ci_t( 'tpl_service_opportunity', 'Intro label', 'THE OPPORTUNITY' ),
				ci_ta( 'tpl_service_connected', 'Intro second paragraph', 'One capability is useful. Connected capabilities can build a more consistent brand experience. We begin with your business context and shape the scope around the work that matters.' ),
			),
			ci_sec( 'tpl_service_deliver', 'Deliverables section', 'WHAT WE BRING TOGETHER', 'THE BUILDING BLOCKS', 'Designed around<br><em>your next move.</em>' ),
			array( ci_ta( 'tpl_service_scope', 'Deliverables note', 'The right combination is defined together around your brief, audience and business objectives.' ) ),
			ci_sec( 'tpl_service_steps', 'Steps section', 'HOW WE CONNECT IT', 'A CLEAR PATH FROM THINKING TO DOING', 'Thoughtfully planned.<br><em>Purposefully delivered.</em>' ),
			ci_sec( 'tpl_service_related', 'Related services section', 'BETTER TOGETHER', 'CONNECTED CAPABILITIES', 'Never working<br><em>in isolation.</em>', 'Explore the disciplines that can bring more of your story into the same picture.' ),
			ci_sec( 'tpl_service_work', 'Related work section', 'FROM OUR WORLD', 'SELECTED CREATIVE', 'Ideas in<br><em>the real world.</em>' ),
			array( ci_t( 'tpl_service_cta_text', 'Bottom CTA (%s = service name)', 'Let&apos;s talk about %s.' ) ),
			array(
				ci_tab( 'Project pages' ),
				ci_t( 'tpl_type_case', 'Type label: case', 'CASE STUDY' ),
				ci_t( 'tpl_type_gallery', 'Type label: gallery', 'CREATIVE GALLERY' ),
				ci_t( 'tpl_type_perspective', 'Type label: perspective', 'PORTFOLIO PERSPECTIVE' ),
				ci_t( 'tpl_tag_case', 'Tag: case', 'Brand transformation' ),
				ci_t( 'tpl_tag_gallery', 'Tag: gallery', 'Selected creative' ),
				ci_t( 'tpl_tag_perspective', 'Tag: perspective', 'Sector perspective' ),
				ci_t( 'tpl_art_eyebrow', 'Editorial art eyebrow', 'CI360 / BRAND IN FOCUS' ),
			),
			ci_sec( 'tpl_project_story', 'Story section', 'THE PERSPECTIVE', 'A STORY WITH ITS OWN POINT OF VIEW', '' ),
			array(
				ci_t( 'tpl_project_lens', 'Sub-heading (perspective)', 'Our lens for this sector' ),
				ci_t( 'tpl_project_direction', 'Sub-heading (gallery)', 'The visual direction' ),
			),
			ci_sec( 'tpl_project_gallery', 'Gallery section', 'A CLOSER LOOK', 'SELECTED VISUALS', '' ),
			array( ci_t( 'tpl_project_view', 'Gallery button', 'VIEW FULL CREATIVE' ) ),
			ci_sec( 'tpl_project_links', 'Capabilities section', 'THE CONNECTED PICTURE', 'CAPABILITIES TO EXPLORE', 'Different disciplines.<br><em>One story.</em>' ),
			array( ci_t( 'tpl_project_next', 'Next project label', 'NEXT IN THE COLLECTION' ) ),
			array(
				ci_tab( 'Insight pages' ),
				ci_t( 'tpl_insight_byline', 'Byline', 'CI360 editorial' ),
				ci_t( 'tpl_insight_share', 'Share button', 'Copy page link' ),
				ci_t( 'tpl_insight_toc', 'Table of contents label', 'IN THIS NOTE' ),
				ci_t( 'tpl_insight_toc_end', 'TOC footer label', 'A NEW PERSPECTIVE.' ),
				ci_t( 'tpl_insight_note_title', 'Editorial note title', 'Editorial status' ),
				ci_t( 'tpl_insight_button', 'Bottom button', 'Continue the conversation' ),
			),
			ci_sec( 'tpl_insight_related', 'Related section', 'KEEP THE CONVERSATION GOING', 'RELATED PERSPECTIVES', 'Another<br><em>point of view.</em>' ),
			array(
				ci_tab( '404 page' ),
				ci_f( 'image', 'opt_404_image', 'Image' ),
				ci_t( 'opt_404_label_1', 'Label (left)', 'A SMALL DETOUR' ),
				ci_t( 'opt_404_label_2', 'Label (right)', 'THE STORY CONTINUES' ),
				ci_h( 'opt_404_heading', 'Heading', 'Wrong turn.<br><em>Right studio.</em>' ),
				ci_t( 'opt_404_text', 'Text', 'This page isn&apos;t in the collection. Let&apos;s get you back to something good.' ),
				ci_t( 'opt_404_button', 'Button', 'Back to the beginning' ),

				ci_tab( 'Enquiries' ),
				ci_sel( 'opt_contact_mode', 'Form mode', array( 'server' => 'Send through WordPress (saved under Enquiries + emailed)', 'email' => 'Open visitor’s email app (prototype behaviour)' ), 'server' ),
				ci_t( 'opt_enquiry_to', 'Send enquiries to', '', array( 'instructions' => 'Leave empty to use the main email.' ) ),
			)
		),
		array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'ci360-settings' ) ) ),
		array( 'position' => 'normal' )
	);

	/* ---------------------------------------------------------------- Service */
	ci_group(
		'service',
		'Service details',
		array(
			ci_tab( 'Content' ),
			ci_t( 'service_number', 'Number', '', array( 'instructions' => 'Leave empty to number automatically from the order.' ) ),
			ci_t( 'service_short', 'Short name' ),
			ci_ta( 'service_summary', 'Summary' ),
			ci_t( 'service_headline', 'Opportunity headline' ),
			ci_ta( 'service_body', 'Body', '', array( 'rows' => 5 ) ),
			ci_rep( 'service_tags', 'Deliverables / tags (first 3 show on the home page)', array( ci_t( 'tag', 'Tag' ) ), array( 'layout' => 'table' ) ),
			ci_rep( 'service_steps', 'Steps', array( ci_t( 'title', 'Title' ), ci_ta( 'text', 'Text' ) ) ),
			ci_tab( 'Visual' ),
			ci_sel( 'service_tone', 'Colour tone', $tones, 'orange' ),
			ci_sel(
				'service_visual',
				'Visual composition',
				array(
					'strategy'    => 'Strategy (photo + paper)',
					'branding'    => 'Branding (board + swatches)',
					'website'     => 'Website (browser + phone)',
					'social'      => 'Social (two posts)',
					'creative'    => 'Creative (two posts)',
					'performance' => 'Performance (phone + metrics)',
					'search'      => 'Search (search window)',
					'podcast'     => 'Podcast (microphone)',
					'film'        => 'Film (viewfinder)',
					'crm'         => 'CRM (messages)',
					'campaign'    => 'Campaign (billboard)',
					'analytics'   => 'Analytics (graph)',
				),
				'strategy'
			),
			ci_f( 'image', 'service_image', 'Main photo (strategy, film, CRM)' ),
			ci_f( 'image', 'service_card_image', 'Services page card image (optional)', null, array( 'instructions' => 'Optional. If empty, the designed service composition is used on the Services page.' ) ),
			ci_f( 'gallery', 'service_visual_images', 'Extra images (website: browser, phone · social/creative: post 1, post 2 · performance: phone · campaign: billboard)' ),
			ci_tab( 'Related' ),
			ci_f( 'relationship', 'service_related', 'Related services (leave empty for automatic)', null, array( 'post_type' => array( 'ci_service' ), 'max' => 3 ) ),
			ci_f( 'relationship', 'service_projects', 'Related work (leave empty for automatic)', null, array( 'post_type' => array( 'ci_project' ), 'max' => 2 ) ),
		),
		ci_loc_type( 'ci_service' )
	);

	/* ---------------------------------------------------------------- Project */
	ci_group(
		'project',
		'Project details',
		array(
			ci_tab( 'Content' ),
			ci_sel( 'project_type', 'Page type', array( 'case' => 'Case study', 'gallery' => 'Creative gallery', 'perspective' => 'Portfolio perspective' ), 'perspective' ),
			ci_f( 'number', 'project_featured', 'Featured position on home page (0 = not featured)', 0 ),
			ci_t( 'project_headline', 'Headline', '', array( 'instructions' => 'Leave empty to use the category’s sector headline.' ) ),
			ci_ta( 'project_summary', 'Summary', 'A brand in the CI360 portfolio. Explore the sector perspective and the capabilities that connect our work.' ),
			ci_rep( 'project_case_sections', 'Case study sections (case study type only)', array( ci_t( 'title', 'Title' ), ci_ta( 'text', 'Text' ) ) ),
			ci_h( 'project_disclosure', 'Disclosure note' ),
			ci_tab( 'Visual' ),
			ci_sel( 'project_tone', 'Colour tone', $tones, 'orange' ),
			ci_f( 'image', 'project_image', 'Main image' ),
			ci_sel(
				'project_scene',
				'Hero composition',
				array(
					'default' => 'Single poster',
					'crave'   => 'Two posters + star',
					'station' => 'Poster + signal rings + caption',
					'vardan'  => 'Two posters + big word',
					'ayaan'   => 'Architecture + caption',
					'museum'  => 'Browser frame + cut-out',
				),
				'default'
			),
			ci_f( 'image', 'project_scene_front', 'Composition: front image (defaults to main image)' ),
			ci_f( 'image', 'project_scene_side', 'Composition: side / second image' ),
			ci_h( 'project_scene_caption', 'Composition caption', '', array( 'instructions' => CI_HTML_HINT . ' <small> is also allowed.' ) ),
			ci_f( 'gallery', 'project_gallery', 'Gallery' ),
			ci_t( 'project_note', 'Image note', "Editorial sector imagery; not a photograph of this client's project." ),
		),
		ci_loc_type( 'ci_project' )
	);

	/* ---------------------------------------------------------------- Insight */
	ci_group(
		'insight',
		'Insight details',
		array(
			ci_t( 'insight_kicker', 'Kicker / topic' ),
			ci_t( 'insight_read', 'Read time', '', array( 'instructions' => 'e.g. “4 min”. Leave empty to calculate.' ) ),
			ci_ta( 'insight_intro', 'Standfirst' ),
			ci_sel( 'insight_tone', 'Colour tone', $tones, 'lilac' ),
			ci_f( 'image', 'insight_image', 'Cover image' ),
			ci_t( 'insight_status', 'Status label', 'STUDIO NOTE / EDITORIAL DRAFT' ),
			ci_rep( 'insight_sections', 'Sections', array( ci_t( 'title', 'Title' ), ci_paras( 'text', 'Text' ) ) ),
			ci_ta( 'insight_note', 'Editorial note (empty = hidden)', 'This is a newly written studio-note draft inspired by the supplied website\'s topic list. It is not presented as the text of an existing published CI360 article.' ),
		),
		ci_loc_type( 'ci_insight' )
	);

	/* ---------------------------------------------------------------- Small types */
	ci_group(
		'testimonial',
		'Testimonial',
		array( ci_ta( 'testimonial_quote', 'Quote', '', array( 'rows' => 5 ) ), ci_t( 'testimonial_company', 'Company / role' ) ),
		ci_loc_type( 'ci_testimonial' )
	);
	ci_group(
		'team',
		'Team member',
		array(
			ci_t( 'team_role', 'Role' ),
			ci_f( 'image', 'team_photo', 'Member photo / portrait' ),
			ci_f( 'true_false', 'team_is_founder', 'Is a founder (shown on Founders page)', 0 ),
			ci_t( 'founder_role', 'Founder title', '', array( 'conditional_logic' => array( array( array( 'field' => 'field_ci_team_is_founder', 'operator' => '==', 'value' => '1' ) ) ) ) ),
			ci_t( 'founder_initials', 'Initials', '', array( 'conditional_logic' => array( array( array( 'field' => 'field_ci_team_is_founder', 'operator' => '==', 'value' => '1' ) ) ) ) ),
			ci_t( 'founder_lens', 'Lens line', '', array( 'conditional_logic' => array( array( array( 'field' => 'field_ci_team_is_founder', 'operator' => '==', 'value' => '1' ) ) ) ) ),
			ci_ta( 'founder_quote', 'Quote', '', array( 'rows' => 5, 'conditional_logic' => array( array( array( 'field' => 'field_ci_team_is_founder', 'operator' => '==', 'value' => '1' ) ) ) ) ),
			ci_f( 'image', 'founder_portrait', 'Founder portrait (optional fallback)', null, array( 'conditional_logic' => array( array( array( 'field' => 'field_ci_team_is_founder', 'operator' => '==', 'value' => '1' ) ) ) ) ),
			ci_sel( 'founder_tone', 'Photo tone', ci_tones(), 'orange' ),
		),
		ci_loc_type( 'ci_team' )
	);
	ci_group(
		'faq',
		'Answer',
		array( ci_ta( 'faq_answer', 'Answer', '', array( 'rows' => 5 ) ) ),
		ci_loc_type( 'ci_faq' )
	);
	ci_group(
		'project_category',
		'Sector notes',
		array(
			ci_t( 'cat_headline', 'Sector headline' ),
			ci_ta( 'cat_p1', 'Paragraph 1' ),
			ci_ta( 'cat_p2', 'Paragraph 2' ),
			ci_f( 'true_false', 'cat_in_filter', 'Show as filter on the Work page', 0 ),
			ci_f( 'number', 'cat_filter_order', 'Filter order', 0 ),
		),
		array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'ci_project_category' ) ) )
	);
}

// Admin notice when no fields plugin is active.
add_action( 'admin_notices', function () {
	if ( function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p><strong>CI360 theme:</strong> please install and activate <em>Secure Custom Fields</em> (free, on WordPress.org) or <em>ACF Pro</em>. The theme registers all its fields automatically once one is active.</p></div>';
} );
