<?php
/**
 * Demo page recipes: which CI360 sections each page uses, in order, and the settings that differ
 * from each section's defaults. Used for the demo import, the Elementor library templates
 * and as a no-Elementor fallback.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ci_demo_pages() {
	return array(
		'home' => array(
			'title'    => 'Home',
			'sections' => array(
				array( 'home-hero', array(  ) ),
				array( 'brand-strip', array(  ) ),
				array( 'home-work', array(  ) ),
				array( 'home-capabilities', array(  ) ),
				array( 'home-about', array(  ) ),
				array( 'word-marquee', array(  ) ),
				array( 'process', array(  ) ),
				array( 'testimonials', array(  ) ),
				array( 'insights-featured', array(  ) ),
				array( 'faq', array( 'group' => 'home' ) ),
			),
		),
		'about' => array(
			'title'    => 'About CI360',
			'sections' => array(
				array( 'page-banner', array(  ) ),
				array( 'about-compass', array(  ) ),
				array( 'about-belief', array(  ) ),
				array( 'about-journey', array(  ) ),
				array( 'about-team', array(  ) ),
				array( 'about-industries', array(  ) ),
				array( 'compact-cta', array( 'text' => 'Let&apos;s find what makes your story stick.' ) ),
			),
		),
		'founders' => array(
			'title'    => 'Our Founders',
			'sections' => array(
				array( 'page-banner', array( 'crumb' => 'Founders', 'label_1' => 'THE LEADERSHIP BEHIND CI360', 'label_2' => 'LEADERSHIP, IN STEREO', 'heading' => 'Different lenses.<br><em>Shared purpose.</em>', 'intro' => 'Different lenses. Shared accountability. A multidisciplinary leadership group connecting marketing experience, entrepreneurial thinking, operations and technology.', 'button' => 'Meet the whole team', 'button_link' => '/about/#team', 'secondary' => 'Start a conversation', 'secondary_link' => '/contact/', 'image' => 'team/122A0148.webp', 'image_2' => 'team/Aashit-.jpg', 'card_value' => '2', 'card_label' => 'Founders. One shared belief.', 'stats' => array( array( 'value' => '2017', 'label' => 'The year it began' ), array( 'value' => '{team}', 'label' => 'Minds on the team' ), array( 'value' => '3', 'label' => 'Locations, one team' ) ) ) ),
				array( 'founders-profiles', array(  ) ),
				array( 'founders-belief', array(  ) ),
				array( 'founders-studio', array(  ) ),
				array( 'compact-cta', array( 'text' => 'A new chapter starts with a conversation.' ) ),
			),
		),
		'services' => array(
			'title'    => 'Our Services',
			'sections' => array(
				array( 'page-banner', array( 'crumb' => 'Services', 'label_1' => 'OUR CAPABILITIES', 'label_2' => 'STRATEGY THROUGH TO EXECUTION', 'heading' => 'One partner.<br><em>Every possibility.</em>', 'intro' => 'From strategic storytelling and brand building to digital marketing, AI search visibility, content, technology and performance. Every capability, together around meaningful, measurable growth.', 'button' => 'Find your next move', 'button_link' => '/contact/', 'secondary' => 'See every capability', 'secondary_link' => '#capabilities', 'image' => 'studio-detail.webp', 'image_2' => 'crave-family.webp', 'card_value' => '{services}', 'card_label' => 'Connected capabilities', 'stats' => array( array( 'value' => '{services}', 'label' => 'Capabilities under one roof' ), array( 'value' => '5', 'label' => 'Disciplines working together' ), array( 'value' => '{projects}', 'label' => 'Brands in our world' ) ) ) ),
				array( 'services-grid', array(  ) ),
				array( 'process', array(  ) ),
				array( 'faq', array( 'label_1' => 'WORKING TOGETHER', 'label_2' => 'FLEXIBLE BY DESIGN', 'heading' => 'One project.<br><em>Or the full picture.</em>', 'intro' => 'Engage a specialist capability or bring an integrated team around your whole marketing challenge.', 'group' => 'contact', 'limit' => 3, 'reveal' => '' ) ),
				array( 'compact-cta', array( 'text' => 'What does your next chapter need?' ) ),
			),
		),
		'work' => array(
			'title'    => 'Selected Work',
			'sections' => array(
				array( 'page-banner', array( 'crumb' => 'Work', 'label_1' => 'THE PORTFOLIO', 'label_2' => 'OUR WORK. YOUR NEXT POSSIBILITY.', 'heading' => 'Different worlds.<br><em>Distinctive work.</em>', 'intro' => 'Dive into CI360\'s digital marketing solutions, where creativity meets strategy to drive brand growth. A collection of client brands, creative galleries and sector perspectives.', 'button' => 'Start a project', 'button_link' => '/contact/', 'secondary' => 'Browse the collection', 'secondary_link' => '#portfolio', 'image' => 'crave-play.webp', 'image_2' => 'vardan-sport.webp', 'card_value' => '{projects}', 'card_label' => 'Brands in the collection', 'stats' => array( array( 'value' => '{projects}', 'label' => 'Brands in the collection' ), array( 'value' => '10+', 'label' => 'Industries served' ), array( 'value' => '{services}', 'label' => 'Capabilities behind the work' ) ) ) ),
				array( 'work-portfolio', array(  ) ),
				array( 'compact-cta', array( 'text' => 'Your brand could be our next great conversation.' ) ),
			),
		),
		'insights' => array(
			'title'    => 'Insights & Perspectives',
			'sections' => array(
				array( 'page-banner', array( 'crumb' => 'Insights', 'label_1' => 'STRATEGIC INTELLIGENCE', 'label_2' => 'INSIGHTS & PERSPECTIVES', 'heading' => 'Good questions.<br><em>Fresh thinking.</em>', 'intro' => 'Marketing never stops evolving, and neither does our thinking. Explore CI360 perspectives on strategic storytelling, digital marketing trends, AI in marketing, SEO, AEO, GEO, performance marketing, brand strategy, content marketing, podcasting, and changing consumer behaviour.', 'button' => 'Read the latest', 'button_link' => '#articles', 'secondary' => 'Visit the blog', 'secondary_link' => '/blogs/', 'image' => 'story.webp', 'image_2' => 'connectivity.webp', 'card_value' => '{insights}', 'card_label' => 'Studio notes to explore', 'stats' => array(  ) ) ),
				array( 'insights-list', array(  ) ),
				array( 'topics', array(  ) ),
				array( 'compact-cta', array( 'text' => 'A thought worth talking about?' ) ),
			),
		),
		'contact' => array(
			'title'    => 'Start a Conversation',
			'sections' => array(
				array( 'page-banner', array( 'crumb' => 'Contact', 'label_1' => 'YOUR NEXT CHAPTER', 'label_2' => 'STARTS HERE', 'heading' => 'Let\'s make<br><em>something matter.</em>', 'intro' => 'Every great story starts with a conversation. We\'d love to hear from you - share a rough brief and we\'ll come back with a clear next step.', 'button' => 'Send an enquiry', 'button_link' => '#enquiry-form', 'secondary' => 'Call +91 97118 09099', 'secondary_link' => 'tel:+919711809099', 'image' => 'studio.webp', 'image_2' => 'event.webp', 'card_value' => '3', 'card_label' => 'Locations. One connected team.', 'stats' => array( array( 'value' => '100%', 'label' => 'Confidential conversations' ), array( 'value' => 'NDA', 'label' => 'Signed on request' ), array( 'value' => '3', 'label' => 'Offices: Ahmedabad, Delhi, Greenville' ) ) ) ),
				array( 'contact-main', array( 'show_crumb' => '', 'heading_tag' => 'h2' ) ),
				array( 'locations', array(  ) ),
				array( 'process', array(  ) ),
				array( 'faq', array( 'label_1' => 'A USEFUL FIRST CONVERSATION', 'label_2' => 'PRACTICAL ANSWERS', 'heading' => 'Start with<br><em>a question.</em>', 'group' => 'contact' ) ),
			),
		),
		'blogs' => array(
			'title'    => 'Blog',
			'sections' => array(
				array( 'page-banner', array( 'crumb' => 'Blog', 'label_1' => 'THE CI360 BLOG', 'label_2' => 'IDEAS, NOTES & STORIES', 'heading' => 'Stories worth<br><em>reading.</em>', 'intro' => 'Articles from the CI360 team on branding, AI, digital marketing, content and life inside the studio.', 'button' => 'Read the latest', 'button_link' => '#posts', 'secondary' => 'Explore insights', 'secondary_link' => '/insights/', 'image' => '659284705824048024084208428-1.jpg', 'image_2' => 'Untitled-design-6.jpg', 'card_value' => '{posts}', 'card_label' => 'Articles published', 'stats' => array(  ) ) ),
				array( 'blog-archive', array(  ) ),
				array( 'compact-cta', array( 'text' => 'Have a story worth telling?' ) ),
			),
		),
		'privacy-policy' => array(
			'title'    => 'Privacy policy',
			'sections' => array(
				array( 'legal', array(  ) ),
				array( 'compact-cta', array( 'text' => 'A question about the details?' ) ),
			),
		),
		'terms-and-conditions' => array(
			'title'    => 'Terms & conditions',
			'sections' => array(
				array( 'legal', array( 'intro' => 'A clear starting point for using this website and beginning a conversation.', 'sections' => array( array( 'title' => 'Status and scope', 'text' => 'These are draft website terms for CI360 review before launch. They are not an executed client agreement and do not set the commercial terms of a project. The final site owner should review this text with appropriate legal support and publish an approved effective date.' ), array( 'title' => 'Website information', 'text' => 'The website introduces CI360, its capabilities and a portfolio of named brands. Service descriptions are general introductions. A particular engagement, deliverable, schedule, fee or performance objective must be set out in an agreed proposal or contract.' ), array( 'title' => 'Making an enquiry', 'text' => 'Submitting or preparing an enquiry does not create a service contract or reserve a delivery date. In the default mode, the form prepares an email draft that you must send using your own email application. A project begins only through a separately agreed engagement process.' ), array( 'title' => 'Portfolio, imagery and editorial notes', 'text' => 'Brand names and supplied creative are shown to present the portfolio. Some archive pages contain labelled editorial sector imagery rather than photographs of a specific client project. Newly written studio notes and policy drafts are labelled for review. No numerical campaign result or case-study outcome should be inferred from decorative charts or visual compositions.' ), array( 'title' => 'Intellectual property and permitted use', 'text' => 'Website content, creative assets, brand names and marks may be protected by rights held by CI360, its clients or other owners. Viewing the website does not itself grant a licence to reuse campaign work, logos or other protected material. Obtain permission from the appropriate rights holder before reuse.' ), array( 'title' => 'External links and availability', 'text' => 'Links to other websites or email applications may be provided for convenience. Their availability, content and processing are controlled by their respective operators. The final approved terms should address availability and any applicable limitations in a way appropriate to the actual service and relevant law.' ), array( 'title' => 'Contact and project agreements', 'text' => 'For website questions, contact pramit.ghosh@ci360degrees.com. Matters such as project scope, payment, confidentiality, ownership, approvals, timelines and dispute resolution belong in the applicable signed project agreement, rather than being assumed from this website draft.' ) ) ) ),
				array( 'compact-cta', array( 'text' => 'A question about the details?' ) ),
			),
		),
	);
}
