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
				ci_t( 'opt_email', 'Main email', 'pramit.ghosh@ci360degrees.com' ),
				ci_t( 'opt_phone', 'Main phone (display)', '+91 97118 09099' ),
				ci_ta( 'opt_meta_description', 'Default meta description', 'CI360 Degrees is an integrated digital marketing and strategic communication agency helping businesses transform ideas into impactful brand experiences and measurable growth.' ),
				ci_t( 'opt_locations_line', 'Locations line', 'Ahmedabad · Delhi · Greenville' ),
				ci_rep( 'opt_socials', 'Social links', array( ci_t( 'label', 'Label' ), ci_f( 'url', 'url', 'URL' ) ) ),

				ci_tab( 'Header & menu' ),
				ci_t( 'opt_logo_text', 'Logo text', 'ci360' ),
				ci_t( 'opt_logo_small', 'Logo small text', 'DEGREES' ),
				ci_t( 'opt_header_cta', 'Header button text', "Let's talk" ),
				ci_t( 'opt_header_cta_link', 'Header button link', '/contact/' ),
				ci_t( 'opt_menu_label', 'Menu overlay label', 'A NEW PERSPECTIVE.' ),
				ci_f( 'image', 'opt_menu_image', 'Menu overlay image' ),
				ci_h( 'opt_menu_text', 'Menu overlay text', 'Ideas into impact.<br>That&apos;s our kind of story.' ),

				ci_tab( 'Footer' ),
				ci_t( 'opt_footer_label_1', 'CTA label (left)', 'NEXT CHAPTER' ),
				ci_t( 'opt_footer_label_2', 'CTA label (right)', 'START A CONVERSATION' ),
				ci_h( 'opt_footer_heading', 'CTA heading', 'Let&apos;s make<br>your <em>next move.</em>' ),
				ci_t( 'opt_footer_link', 'CTA link', '/contact/' ),
				ci_h( 'opt_footer_sticker', 'Sticker text', 'A FULL CIRCLE<br>OF POSSIBILITIES' ),
				ci_ta( 'opt_footer_about', 'About text', 'An integrated digital marketing and strategic communication agency built around the power of strategic storytelling.' ),
				ci_t( 'opt_footer_explore', 'Links label', 'EXPLORE' ),
				ci_t( 'opt_footer_offices', 'Offices label', 'THREE LOCATIONS. ONE CONNECTED TEAM.' ),

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

	/* ---------------------------------------------------------------- Home */
	ci_group(
		'home',
		'Home page',
		array_merge(
			array(
				ci_tab( 'Hero' ),
				ci_t( 'home_seo_title', 'Browser title', 'Stories that move brands forward' ),
				ci_t( 'home_eyebrow', 'Eyebrow', 'BLUE-SKY THINKING. BUSINESS-GROUND RESULTS.' ),
				ci_t( 'home_title_1', 'Title line 1', 'Stories that' ),
				ci_t( 'home_title_2', 'Title line 2', 'move brands' ),
				ci_t( 'home_title_3', 'Title line 3 (accent, underlined)', 'forward.' ),
				ci_ta( 'home_description', 'Description', 'Strategy, creativity, digital and technology — moving together to make brands impossible to ignore.' ),
				ci_t( 'home_cta', 'Button text', 'Start a conversation' ),
				ci_t( 'home_cta_link', 'Button link', '/contact/' ),
				ci_t( 'home_secondary', 'Secondary link text', 'Explore our work' ),
				ci_t( 'home_collage_label', 'Collage label', 'A FULL CIRCLE OF POSSIBILITIES.' ),
				ci_rep(
					'home_collage',
					'Collage frames (left, main, right)',
					array(
						ci_f( 'post_object', 'project', 'Project', null, array( 'post_type' => array( 'ci_project' ) ) ),
						ci_f( 'image', 'image', 'Image' ),
						ci_t( 'caption', 'Caption' ),
					),
					array( 'max' => 3, 'layout' => 'table' )
				),
				ci_h( 'home_sticker', 'Sticker', 'MAKE<br>IT MOVE.' ),
				ci_t( 'home_collage_caption', 'Collage caption', 'STRATEGY × STORY × DESIGN × PERFORMANCE' ),
				ci_t( 'home_collage_badge', 'Collage badge', '360°' ),
				ci_t( 'home_bottom_left', 'Bottom left', 'AHMEDABAD · DELHI · GREENVILLE' ),
				ci_t( 'home_bottom_right', 'Bottom right', 'SCROLL TO FIND YOUR NEXT PERSPECTIVE' ),
				ci_tab( 'Selected work' ),
			),
			ci_sec( 'home_work', 'Selected work', '01 / SELECTED WORK', 'DIFFERENT BRANDS. CONNECTED THINKING.', 'Not just seen.<br><em>Felt. Remembered.</em>', 'From everyday moments to extraordinary possibilities. A selection of the brands in our world.' ),
			array(
				ci_f( 'message', 'home_work_note', 'Which projects?', null, array( 'message' => 'Projects with a “Featured position” (1–4) appear here in that order.' ) ),
				ci_t( 'home_work_link', 'Card link text', 'Explore the project' ),
				ci_t( 'home_work_end', 'Closing line', 'A collection of different challenges. One integrated approach.' ),
				ci_t( 'home_work_button', 'Button (%d = number of projects)', 'View all %d projects' ),
				ci_tab( 'What we do' ),
			),
			ci_sec( 'home_cap', 'Capabilities', '02 / WHAT WE DO', 'ONE PARTNER. EVERY MARKETING POSSIBILITY.', 'A full circle<br>of <em>possibilities.</em>', 'Strategy, creativity, digital, technology and performance. Working together, not in silos.' ),
			array(
				ci_f( 'number', 'home_cap_count', 'Number of services listed', 8 ),
				ci_t( 'home_cap_all', 'Link (%d = total services)', 'Discover all %d capabilities' ),
				ci_t( 'home_cap_preview', 'Preview caption (first service)', 'One clear story. A world of ways to tell it.' ),
				ci_tab( 'About teaser' ),
				ci_f( 'image', 'home_about_image', 'Photo' ),
				ci_h( 'home_about_stamp', 'Photo stamp', 'CURIOUS MINDS<br>AT WORK.' ),
			),
			ci_sec( 'home_about', 'About teaser', '03 / THE AGENCY', 'ALWAYS LOOKING CLOSER', 'Big on ideas.<br><em>Closer to you.</em>' ),
			array(
				ci_paras( 'home_about_text', 'Text', "CI360 Degrees is an integrated digital marketing and strategic communication agency helping businesses transform ideas into impactful brand experiences and measurable growth.\n\nWe begin by listening. Then bring the right minds, disciplines and stories together around your business." ),
				ci_t( 'home_about_button', 'Button', 'Meet CI360' ),
				ci_t( 'home_about_link', 'Button link', '/about/' ),
				ci_t( 'home_marquee', 'Marquee words (separate with |)', 'LISTEN. | CREATE. | CONNECT. | GROW.' ),
				ci_tab( 'Insights & FAQ' ),
			),
			ci_sec( 'home_insights', 'Insights', '04 / INSIGHTS', 'THOUGHTS THAT MOVE THE CONVERSATION', 'A fresh<br><em>perspective.</em>', 'Marketing never stops evolving, and neither does our thinking. Explore CI360 perspectives on strategic storytelling, digital marketing trends, AI in marketing, SEO, AEO, GEO, performance marketing, brand strategy, content marketing, podcasting, and changing consumer behaviour.' ),
			array(
				ci_t( 'home_insights_button', 'Button', 'Explore our thinking' ),
				ci_t( 'home_insights_link', 'Button link', '/insights/' ),
			),
			ci_sec( 'home_faq', 'FAQ', 'A LITTLE MORE CLARITY', 'GOOD QUESTIONS. USEFUL ANSWERS.', 'Before we<br><em>say hello.</em>' ),
			array( ci_f( 'taxonomy', 'home_faq_group', 'FAQ group', null, array( 'taxonomy' => 'ci_faq_group', 'field_type' => 'select', 'return_format' => 'id', 'add_term' => 0 ) ) )
		),
		array( array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ) )
	);

	/* ---------------------------------------------------------------- About */
	ci_group(
		'about',
		'About page',
		array_merge(
			array( ci_tab( 'Hero' ), ci_t( 'about_crumb', 'Breadcrumb', 'About us' ) ),
			ci_sec( 'about_hero', 'Hero', 'ABOUT CI360', 'NAVIGATING CHANGE WITH PURPOSE', 'The brands people<br><em>remember.</em>' ),
			array(
				ci_t( 'about_intro_short', 'Intro (short)', 'Because great stories endure.' ),
				ci_ta( 'about_intro', 'Intro', 'CI360 stands at the forefront of an ever-shifting digital landscape, dedicated to the art of focused attention. Each strand of content and every pixel of design reflects our belief in the transformative power of digital. Our team remains a community of learners and innovators - always evolving, always looking closer, always building the specific idea that makes a story stick.' ),
				ci_f( 'image', 'about_image', 'Wide image' ),
				ci_t( 'about_image_note', 'Image note', 'A COMMUNITY OF LEARNERS & INNOVATORS' ),
				ci_h( 'about_image_type', 'Image overlay type', 'Always<br>looking closer.' ),
				ci_tab( 'Compass' ),
			),
			ci_sec( 'about_compass', 'Vision, mission, values', 'OUR COMPASS', 'VISION. MISSION. VALUES.', 'Purpose in<br><em>every direction.</em>' ),
			array(
				ci_t( 'about_vision_title', 'Vision title', 'A broader perspective.' ),
				ci_ta( 'about_vision', 'Vision', 'To be a globally trusted marketing partner, where creativity, technology, and innovation redefine brand communication.' ),
				ci_t( 'about_mission_title', 'Mission title', 'Ideas into impact.' ),
				ci_ta( 'about_mission', 'Mission', 'To empower brands through strategic storytelling, creativity, and technology that drive meaningful engagement and measurable growth.' ),
				ci_t( 'about_values_title', 'Values title', 'What keeps us true.' ),
				ci_rep( 'about_values', 'Values', array( ci_t( 'value', 'Value' ) ), array( 'layout' => 'table' ) ),
				ci_tab( 'Belief' ),
			),
			ci_sec( 'about_belief', 'The way we think', 'THE WAY WE THINK', 'A SINGLE INTEGRATED DIRECTION', 'We turn ideas<br>into <em>impactful<br>solutions.</em>' ),
			array(
				ci_t( 'about_belief_button', 'Button', 'Meet our founders' ),
				ci_t( 'about_belief_link', 'Button link', '/founders/' ),
				ci_paras( 'about_belief_text', 'Text (first paragraph is large)' ),
				ci_tab( 'Journey' ),
			),
			ci_sec( 'about_journey', 'Timeline', 'THE JOURNEY', '2017 TO TODAY', 'Always in<br><em>forward motion.</em>' ),
			array(
				ci_rep( 'about_timeline', 'Timeline', array( ci_t( 'year', 'Year' ), ci_t( 'title', 'Title' ), ci_ta( 'text', 'Text' ), ci_f( 'image', 'image', 'Image' ), ci_t( 'tags', 'Tags (comma separated)' ) ) ),
				ci_tab( 'Team & industries' ),
			),
			ci_sec( 'about_team', 'Team (members come from Team)', 'THE PEOPLE', 'DIFFERENT MINDS. SHARED PURPOSE.', 'The thinking<br><em>behind the work.</em>', 'A multidisciplinary team connecting strategy, creative, technology and delivery.' ),
			ci_sec( 'about_industries', 'Industries', 'OUR WORLD', 'ACROSS INDUSTRIES. ACROSS MARKETS.', 'Many worlds.<br><em>One connected team.</em>' ),
			array(
				ci_rep( 'about_industries_list', 'Industries', array( ci_t( 'name', 'Industry' ) ), array( 'layout' => 'table' ) ),
				ci_t( 'about_cta', 'Bottom CTA', 'Let&apos;s find what makes your story stick.' ),
			)
		),
		ci_loc_template( 'about.php' )
	);

	/* ---------------------------------------------------------------- Founders */
	ci_group(
		'founders',
		'Founders page',
		array_merge(
			array( ci_t( 'founders_crumb', 'Breadcrumb', 'Founders' ) ),
			ci_sec( 'founders_hero', 'Hero', 'THE LEADERSHIP BEHIND CI360', 'LEADERSHIP, IN STEREO', 'Different lenses.<br><em>Shared purpose.</em>' ),
			array(
				ci_t( 'founders_intro_short', 'Intro (short)', 'Ideas. Insight. Impact.' ),
				ci_ta( 'founders_intro', 'Intro', 'Different lenses. Shared accountability. A multidisciplinary leadership group connecting marketing experience, entrepreneurial thinking, operations and technology.' ),
				ci_f( 'image', 'founders_fallback_image', 'Portrait fallback image' ),
				ci_f( 'message', 'founders_note', 'Founder profiles', null, array( 'message' => 'Profiles come from Team members marked “Is a founder”.' ) ),
			),
			ci_sec( 'founders_belief', 'Shared belief', 'BUILT ON A SHARED BELIEF', 'GREAT MARKETING BEGINS WITH LISTENING', 'A story rooted<br>in <em>purpose.</em>' ),
			array(
				ci_paras( 'founders_belief_text', 'Text' ),
				ci_f( 'image', 'founders_studio_image', 'Studio image' ),
				ci_h( 'founders_studio_heading', 'Studio heading', 'Good ideas<br>need <em>good company.</em>' ),
				ci_ta( 'founders_studio_text', 'Studio text', 'Our team remains a community of learners and innovators - always evolving, always looking closer, always building the specific idea that makes a story stick.' ),
				ci_t( 'founders_studio_button', 'Studio button', 'Meet the whole team' ),
				ci_t( 'founders_studio_link', 'Studio button link', '/about/#team' ),
				ci_t( 'founders_cta', 'Bottom CTA', 'A new chapter starts with a conversation.' ),
			)
		),
		ci_loc_template( 'founders.php' )
	);

	/* ---------------------------------------------------------------- Services page */
	ci_group(
		'services_page',
		'Services page',
		array_merge(
			array( ci_t( 'services_crumb', 'Breadcrumb', 'Services' ) ),
			ci_sec( 'services_hero', 'Hero', 'OUR CAPABILITIES', 'STRATEGY THROUGH TO EXECUTION', 'One partner.<br><em>Every possibility.</em>', 'From strategic storytelling and brand building to digital marketing, AI search visibility, content, technology and performance. Every capability, together around meaningful, measurable growth.' ),
			array(
				ci_t( 'services_button', 'Button', 'Find your next move' ),
				ci_t( 'services_button_link', 'Button link', '/contact/' ),
				ci_f( 'post_object', 'services_hero_service', 'Hero visual (service)', null, array( 'post_type' => array( 'ci_service' ), 'allow_null' => 1 ) ),
				ci_h( 'services_badge', 'Badge small text', 'CONNECTED<br>CAPABILITIES' ),
			),
			ci_sec( 'services_faq', 'FAQ section', 'WORKING TOGETHER', 'FLEXIBLE BY DESIGN', 'One project.<br><em>Or the full picture.</em>', 'Engage a specialist capability or bring an integrated team around your whole marketing challenge.' ),
			array(
				ci_f( 'taxonomy', 'services_faq_group', 'FAQ group', null, array( 'taxonomy' => 'ci_faq_group', 'field_type' => 'select', 'return_format' => 'id', 'add_term' => 0 ) ),
				ci_f( 'number', 'services_faq_limit', 'Number of FAQs', 3 ),
				ci_t( 'services_cta', 'Bottom CTA', 'What does your next chapter need?' ),
			)
		),
		ci_loc_template( 'services.php' )
	);

	/* ---------------------------------------------------------------- Work page */
	ci_group(
		'work_page',
		'Work page',
		array_merge(
			array( ci_t( 'work_crumb', 'Breadcrumb', 'Work' ) ),
			ci_sec( 'work_hero', 'Hero', 'THE PORTFOLIO', 'OUR WORK. YOUR NEXT POSSIBILITY.', 'Different worlds.<br><em>Distinctive work.</em>' ),
			array(
				ci_h( 'work_circle', 'Circle text', 'BRANDS IN<br>THE COLLECTION' ),
				ci_t( 'work_intro_short', 'Intro (short)', 'Creativity meets strategy.' ),
				ci_ta( 'work_intro', 'Intro', "Dive into CI360's digital marketing solutions, where creativity meets strategy to drive brand growth. A collection of client brands, creative galleries and sector perspectives." ),
				ci_t( 'work_all', 'Filter “All” label', 'All' ),
				ci_t( 'work_search', 'Search placeholder', 'Find a brand' ),
				ci_t( 'work_count', 'Count label', 'projects in view' ),
				ci_h( 'work_empty_heading', 'No results heading', 'A different<br>search, perhaps?' ),
				ci_t( 'work_empty_text', 'No results text', 'Try another brand name or explore the full collection.' ),
				ci_t( 'work_empty_button', 'No results button', 'Show all projects' ),
				ci_ta( 'work_note', 'Portfolio note', 'Creative galleries use supplied imagery. Portfolio perspectives use clearly labelled editorial sector imagery and do not assert project results.' ),
				ci_t( 'work_cta', 'Bottom CTA', 'Your brand could be our next great conversation.' ),
			)
		),
		ci_loc_template( 'work.php' )
	);

	/* ---------------------------------------------------------------- Insights page */
	ci_group(
		'insights_page',
		'Insights page',
		array_merge(
			array( ci_t( 'insights_crumb', 'Breadcrumb', 'Insights' ) ),
			ci_sec( 'insights_hero', 'Hero', 'STRATEGIC INTELLIGENCE', 'INSIGHTS & PERSPECTIVES', 'Good questions.<br><em>Fresh thinking.</em>' ),
			array(
				ci_t( 'insights_intro_short', 'Intro (short)', 'The conversation keeps moving.' ),
				ci_ta( 'insights_intro', 'Intro', 'Marketing never stops evolving, and neither does our thinking. Explore CI360 perspectives on strategic storytelling, digital marketing trends, AI in marketing, SEO, AEO, GEO, performance marketing, brand strategy, content marketing, podcasting, and changing consumer behaviour.' ),
				ci_ta( 'insights_note', 'Note under grid', 'Studio notes: newly written editorial drafts developed from the themes in the supplied website content. Review before publication.' ),
			),
			ci_sec( 'insights_topics', 'Topics', 'WHAT WE THINK ABOUT', 'A CURIOUS MIND DOES NOT STAND STILL', 'More ways<br>to <em>look closer.</em>' ),
			array(
				ci_rep( 'insights_topic_list', 'Topics (link to contact form)', array( ci_t( 'topic', 'Topic' ) ), array( 'layout' => 'table' ) ),
				ci_t( 'insights_cta', 'Bottom CTA', 'A thought worth talking about?' ),
			)
		),
		ci_loc_template( 'insights.php' )
	);

	/* ---------------------------------------------------------------- Contact page */
	ci_group(
		'contact_page',
		'Contact page',
		array_merge(
			array( ci_tab( 'Intro' ), ci_t( 'contact_crumb', 'Breadcrumb', 'Contact' ) ),
			ci_sec( 'contact_hero', 'Hero', 'YOUR NEXT CHAPTER', 'STARTS HERE', 'Let&apos;s make<br><em>something<br>matter.</em>' ),
			array(
				ci_ta( 'contact_lead', 'Lead', 'Every great story starts with a conversation. We&apos;d love to hear from you.' ),
				ci_rep( 'contact_blocks', 'Contact details', array( ci_t( 'label', 'Label' ), ci_t( 'email', 'Email' ), ci_t( 'phone', 'Phone' ) ) ),
				ci_f( 'image', 'contact_image', 'Photo' ),
				ci_t( 'contact_image_label', 'Photo label', 'ONE CONNECTED TEAM.' ),
				ci_t( 'contact_confidential', 'Confidential line (use | between items)', 'Let&apos;s connect | 100% confidential | We sign NDA' ),
				ci_tab( 'Form' ),
				ci_t( 'form_label', 'Form label', 'LET\'S CREATE SOMETHING EXCEPTIONAL.' ),
				ci_h( 'form_heading', 'Form heading', 'Tell us what<br>you&apos;re <em>thinking.</em>' ),
				ci_ta( 'form_intro', 'Form intro', 'Share your goal, your challenge and where you are in the process. A rough brief is fine.' ),
				ci_t( 'form_extra_1', 'Extra service option 1', 'An integrated marketing partnership' ),
				ci_t( 'form_extra_2', 'Extra service option 2', 'Let\'s work it out together' ),
				ci_h( 'form_consent', 'Consent text', 'I agree that CI360 may use my details to respond to this enquiry. I have read the <a href="/privacy-policy/">Privacy Policy</a> and <a href="/terms-and-conditions/">Terms &amp; Conditions</a>.' ),
				ci_tab( 'Locations & FAQ' ),
			),
			ci_sec( 'contact_locations', 'Locations (from Settings › Offices)', 'HERE. THERE. TOGETHER.', 'THREE LOCATIONS. ONE CONNECTED TEAM.', 'Local understanding.<br><em>Connected perspectives.</em>' ),
			ci_sec( 'contact_faq', 'FAQ', 'A USEFUL FIRST CONVERSATION', 'PRACTICAL ANSWERS', 'Start with<br><em>a question.</em>' ),
			array( ci_f( 'taxonomy', 'contact_faq_group', 'FAQ group', null, array( 'taxonomy' => 'ci_faq_group', 'field_type' => 'select', 'return_format' => 'id', 'add_term' => 0 ) ) )
		),
		ci_loc_template( 'contact.php' )
	);

	/* ---------------------------------------------------------------- Legal page */
	ci_group(
		'legal_page',
		'Legal page',
		array(
			ci_t( 'legal_label_1', 'Label (left)', 'THE DETAILS' ),
			ci_t( 'legal_label_2', 'Label (right)', 'DRAFT / REVIEW BEFORE PUBLICATION' ),
			ci_ta( 'legal_intro', 'Intro' ),
			ci_h( 'legal_art', 'Side art text', 'Clarity<br>comes first.' ),
			ci_ta( 'legal_aside', 'Side note', 'Draft operational copy. Owner and legal review required before launch.' ),
			ci_rep( 'legal_sections', 'Sections', array( ci_t( 'title', 'Title' ), ci_ta( 'text', 'Text' ) ) ),
			ci_t( 'legal_cta', 'Bottom CTA', 'A question about the details?' ),
		),
		ci_loc_template( 'legal.php' )
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
