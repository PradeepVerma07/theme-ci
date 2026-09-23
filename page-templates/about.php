<?php
/**
 * Template Name: About
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$id = get_queried_object_id();
$g  = function ( $n ) use ( $id ) {
	return ci_get( $n, $id );
};
$values = '';
foreach ( ci_rows( 'about_values', $id ) as $r ) {
	$values .= '<li>' . ci_e( $r['value'] ) . '</li>';
}
$timeline = ci_rows( 'about_timeline', $id );
$tabs     = '';
foreach ( $timeline as $i => $r ) {
	$tabs .= '<button role="tab" id="year-' . $i . '" aria-controls="timeline-panel" aria-selected="' . ( $i ? 'false' : 'true' ) . '" data-year="' . $i . '">' . ci_e( $r['year'] ) . '<span></span></button>';
}
$first = $timeline ? $timeline[0] : array( 'year' => '', 'title' => '', 'text' => '' );
$team_data = array(
	array( 'name' => 'Pramit Ghosh', 'role' => 'CEO | Founder', 'photo' => '122A0148.webp' ),
	array( 'name' => 'Aashit Shah', 'role' => 'Director | Co-Founder', 'photo' => 'Aashit-.jpg' ),
	array( 'name' => 'Urna Banerji', 'role' => 'COO', 'photo' => 'Urna.jpeg' ),
	array( 'name' => 'Bhumi Chabbra', 'role' => 'Director, US', 'photo' => 'Bn.png' ),
	array( 'name' => 'John Seaman', 'role' => 'Head of Technology', 'photo' => 'John-Seaman.jpg' ),
	array( 'name' => 'Mansi Bagdai', 'role' => 'Strategy and Growth Lead', 'photo' => 'Mansi.jpg' ),
	array( 'name' => 'Manan Dhingra', 'role' => 'Creative Lead', 'photo' => 'Manan-Dhingra.jpg' ),
	array( 'name' => 'Aneri Shah', 'role' => 'Creative Lead', 'photo' => 'AS.jpeg' ),
	array( 'name' => 'Maryanne deSousa', 'role' => 'Graphic Designer and Video Editor', 'photo' => 'MD.jpeg' ),
	array( 'name' => 'Aarya Parsodkar', 'role' => 'Graphic Designer', 'photo' => 'Aarya.jpg' ),
	array( 'name' => 'Pratik Hemani', 'role' => 'Strategy Lead – Digital & OOH', 'photo' => 'pratik.jpeg' ),
	array( 'name' => 'Meshwa Kadia', 'role' => 'Web Developer', 'photo' => 'mmk.jpeg' ),
	array( 'name' => 'Ajay Shankar', 'role' => 'Video Editor and Motion Designer', 'photo' => 'as-e1785923665718.jpeg' ),
	array( 'name' => 'Pradeep Verma', 'role' => 'Executive Web Developer', 'photo' => '65794823924813429381189938331.jpg' ),
	array( 'name' => 'Harshada', 'role' => 'SEO Lead', 'photo' => 'H.jpg' ),
	array( 'name' => 'Aadhya Bhidodiya', 'role' => 'Graphic Designer', 'photo' => 'AB-rotated.jpeg' ),
	array( 'name' => 'Arushi Singh', 'role' => 'Social Media Manager', 'photo' => 'Arushi_SM.jpg' ),
);

$team_dir = get_template_directory_uri() . '/assets/images/team/';
$team = '';
$db_posts = ci_posts( 'ci_team' );

if ( ! empty( $db_posts ) ) {
	foreach ( $db_posts as $i => $m ) {
		$name = ci_title( $m );
		$role = ci_get( 'team_role', $m->ID );
		$img_id = (int) ci_get( 'founder_portrait', $m->ID );
		if ( ! $img_id ) {
			$img_id = get_post_thumbnail_id( $m->ID );
		}
		$photo_html = '';
		if ( $img_id ) {
			$photo_html = '<img class="team-member-photo" src="' . esc_url( wp_get_attachment_image_url( $img_id, 'medium_large' ) ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" />';
		} else {
			foreach ( $team_data as $td ) {
				if ( strcasecmp( $td['name'], $name ) === 0 || strcasecmp( strtok( $td['name'], ' ' ), strtok( $name, ' ' ) ) === 0 ) {
					$photo_html = '<img class="team-member-photo" src="' . esc_url( $team_dir . $td['photo'] ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" />';
					break;
				}
			}
		}
		if ( empty( $photo_html ) ) {
			$photo_html = '<div class="team-photo-fallback"><span>' . esc_html( mb_substr( $name, 0, 2 ) ) . '</span></div>';
		}
		$team .= '<div class="team-member-card" data-reveal>' .
			'<div class="team-member-photo-wrap">' . $photo_html . '</div>' .
			'<div class="team-member-info">' .
				'<span class="team-index">' . ci_pad( $i + 1 ) . '</span>' .
				'<h3>' . ci_e( $name ) . '</h3>' .
				'<p>' . ci_e( $role ) . '</p>' .
			'</div>' .
		'</div>';
	}
} else {
	foreach ( $team_data as $i => $td ) {
		$team .= '<div class="team-member-card" data-reveal>' .
			'<div class="team-member-photo-wrap">' .
				'<img class="team-member-photo" src="' . esc_url( $team_dir . $td['photo'] ) . '" alt="' . esc_attr( $td['name'] ) . '" loading="lazy" />' .
			'</div>' .
			'<div class="team-member-info">' .
				'<span class="team-index">' . ci_pad( $i + 1 ) . '</span>' .
				'<h3>' . ci_e( $td['name'] ) . '</h3>' .
				'<p>' . ci_e( $td['role'] ) . '</p>' .
			'</div>' .
		'</div>';
	}
}
$industries = '';
foreach ( ci_rows( 'about_industries_list', $id ) as $i => $r ) {
	$industries .= '<span data-reveal>' . ci_pad( $i + 1 ) . ' ' . ci_e( $r['name'] ) . '</span>';
}
?><main id="main"><section class="page-hero wrap"><?php echo ci_breadcrumb( $g( 'about_crumb' ) ); ?><?php echo ci_sec_label( 'about_hero', $id ); ?><h1 class="display" data-title><?php echo ci_html( $g( 'about_hero_heading' ) ); ?></h1><div class="hero-intro-row"><p><?php echo ci_e( $g( 'about_intro_short' ) ); ?></p><p><?php echo ci_e( $g( 'about_intro' ) ); ?></p></div><div class="about-wide-image" data-reveal><?php echo ci_img( $g( 'about_image' ), null, '', true ); ?><span class="image-note"><?php echo ci_e( $g( 'about_image_note' ) ); ?></span><div class="wide-image-type" aria-hidden="true"><?php echo ci_html( $g( 'about_image_type' ) ); ?></div></div></section><section class="section wrap"><div class="section-heading"><?php echo ci_sec_label( 'about_compass', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_compass_heading' ) ); ?></h2></div><div class="purpose-grid"><article class="tone-orange" data-reveal><?php echo ci_star(); ?><span class="small-label">01 / VISION</span><h3><?php echo ci_e( $g( 'about_vision_title' ) ); ?></h3><p><?php echo ci_e( $g( 'about_vision' ) ); ?></p></article><article class="tone-lilac" data-reveal><?php echo ci_arrow(); ?><span class="small-label">02 / MISSION</span><h3><?php echo ci_e( $g( 'about_mission_title' ) ); ?></h3><p><?php echo ci_e( $g( 'about_mission' ) ); ?></p></article><article class="tone-mint" data-reveal><div class="values-symbol" aria-hidden="true">+</div><span class="small-label">03 / VALUES</span><h3><?php echo ci_e( $g( 'about_values_title' ) ); ?></h3><ul><?php echo $values; ?></ul></article></div></section><section class="belief-section dark-section section"><div class="wrap belief-grid"><div><?php echo ci_sec_label( 'about_belief', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_belief_heading' ) ); ?></h2><?php echo ci_btn( $g( 'about_belief_button' ), $g( 'about_belief_link' ), 'light' ); ?></div><div><?php echo ci_paragraphs( $g( 'about_belief_text' ), 'large-copy' ); ?></div></div></section><section class="section wrap journey-section"><div class="section-heading"><?php echo ci_sec_label( 'about_journey', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_journey_heading' ) ); ?></h2></div><div class="timeline-tabs" role="tablist" aria-label="Company timeline"><?php echo $tabs; ?></div><div id="timeline-panel" class="timeline-panel tone-yellow" role="tabpanel" aria-labelledby="year-0"><span class="timeline-year"><?php echo ci_e( $first['year'] ); ?></span><div><h3><?php echo ci_e( $first['title'] ); ?></h3><p><?php echo ci_e( $first['text'] ); ?></p></div><?php echo ci_star(); ?></div></section><section class="team-section section wrap" id="team"><div class="section-heading heading-row"><?php echo ci_sec_label( 'about_team', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_team_heading' ) ); ?></h2><p><?php echo ci_e( $g( 'about_team_intro' ) ); ?></p></div><div class="team-roster"><?php echo $team; ?></div></section><section class="section industry-section wrap"><div class="section-heading"><?php echo ci_sec_label( 'about_industries', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_industries_heading' ) ); ?></h2></div><div class="industry-pills"><?php echo $industries; ?></div></section><?php echo ci_compact_cta( $g( 'about_cta' ) ); ?></main><?php
get_footer();
