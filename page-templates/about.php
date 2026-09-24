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
		$img_id = (int) ci_get( 'team_photo', $m->ID );
		if ( ! $img_id ) {
			$img_id = (int) ci_get( 'founder_portrait', $m->ID );
		}
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
?><main id="main"><section class="page-hero wrap"><?php echo ci_breadcrumb( $g( 'about_crumb' ) ); ?><?php echo ci_sec_label( 'about_hero', $id ); ?><h1 class="display" data-title><?php echo ci_html( $g( 'about_hero_heading' ) ); ?></h1><div class="hero-intro-row"><p><?php echo ci_e( $g( 'about_intro_short' ) ); ?></p><p><?php echo ci_e( $g( 'about_intro' ) ); ?></p></div><div class="about-wide-image" data-reveal><?php echo ci_img( $g( 'about_image' ), null, '', true ); ?><span class="image-note"><?php echo ci_e( $g( 'about_image_note' ) ); ?></span><div class="wide-image-type" aria-hidden="true"><?php echo ci_html( $g( 'about_image_type' ) ); ?></div></div></section><section class="section wrap"><div class="section-heading"><?php echo ci_sec_label( 'about_compass', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_compass_heading' ) ); ?></h2></div><div class="purpose-grid"><article class="tone-orange" data-reveal><?php echo ci_star(); ?><span class="small-label">01 / VISION</span><h3><?php echo ci_e( $g( 'about_vision_title' ) ); ?></h3><p><?php echo ci_e( $g( 'about_vision' ) ); ?></p></article><article class="tone-lilac" data-reveal><?php echo ci_arrow(); ?><span class="small-label">02 / MISSION</span><h3><?php echo ci_e( $g( 'about_mission_title' ) ); ?></h3><p><?php echo ci_e( $g( 'about_mission' ) ); ?></p></article><article class="tone-mint" data-reveal><div class="values-symbol" aria-hidden="true">+</div><span class="small-label">03 / VALUES</span><h3><?php echo ci_e( $g( 'about_values_title' ) ); ?></h3><ul><?php echo $values; ?></ul></article></div></section><section class="belief-section dark-section section"><div class="wrap belief-grid"><div><?php echo ci_sec_label( 'about_belief', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_belief_heading' ) ); ?></h2><?php echo ci_btn( $g( 'about_belief_button' ), $g( 'about_belief_link' ), 'light' ); ?></div><div><?php echo ci_paragraphs( $g( 'about_belief_text' ), 'large-copy' ); ?></div></div></section><style>
#ci360-timeline-section {
  position: relative;
  background-color: #020617;
  color: #ffffff;
  font-family: 'Poppins', Arial, sans-serif;
  padding: 60px 0 90px;
  box-sizing: border-box;
  overflow: hidden;
  width: 100%;
}
#ci360-timeline-section::before {
  content: '';
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%,-50%);
  width: 1000px; height: 500px;
  background: radial-gradient(ellipse, rgba(37,99,235,0.09) 0%, transparent 70%);
  border-radius: 9999px;
  pointer-events: none;
  z-index: 0;
}
#ci360-timeline-section .tl-inner {
  max-width: 1400px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
  padding: 0 32px;
}
@media (min-width: 1024px) {
  #ci360-timeline-section .tl-inner { padding: 0 64px; }
}
#ci360-timeline-section .tl-heading-wrap { margin-bottom: 44px; }
#ci360-timeline-section .tl-heading-wrap .section-label { margin-bottom: 20px; color: #94a3b8; }
#ci360-timeline-section .tl-heading-wrap .section-label span+span { color: #64748b; border-color: #334155; }
#ci360-timeline-section .tl-main-heading {
  font-family: 'Poppins', Arial, sans-serif;
  font-size: clamp(34px, 4.5vw, 64px);
  font-weight: 700;
  line-height: 1.15;
  letter-spacing: -0.025em;
  color: #f1f5f9;
  margin: 0;
  max-width: 980px;
}
@media (max-width: 1023px) {
  #ci360-timeline-section .tl-main-heading { font-size: 36px; }
}
#ci360-timeline-section .tl-main-heading em {
  font-style: normal;
  background: linear-gradient(to right, #22d3ee, #3b82f6);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  color: transparent;
}
#ci360-timeline-section .tl-track-wrap { position: relative; margin-bottom: 40px; }
#ci360-timeline-section .tl-track-rail {
  display: none;
  position: absolute;
  top: 50%; left: 16px; right: 16px;
  height: 3px;
  transform: translateY(-50%);
  background: #1e293b;
  border-radius: 9999px;
  z-index: 0;
}
#ci360-timeline-section .tl-track-fill {
  display: none;
  position: absolute;
  top: 50%; left: 16px;
  height: 3px;
  transform: translateY(-50%);
  background: linear-gradient(to right, #3b82f6, #22d3ee, #6366f1);
  border-radius: 9999px;
  z-index: 0;
  box-shadow: 0 0 10px rgba(34,211,238,0.4);
  transition: width 0.45s ease;
}
@media (min-width: 768px) {
  #ci360-timeline-section .tl-track-rail,
  #ci360-timeline-section .tl-track-fill { display: block; }
}
#ci360-timeline-section .tl-steps {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  overflow-x: auto;
  -ms-overflow-style: none;
  scrollbar-width: none;
  padding: 16px 8px;
  position: relative;
  z-index: 1;
}
#ci360-timeline-section .tl-steps::-webkit-scrollbar { display: none; }
#ci360-timeline-section .tl-step-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  min-width: 64px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  transition: transform 0.25s ease, opacity 0.25s ease;
  opacity: 0.55;
}
#ci360-timeline-section .tl-step-btn:hover { opacity: 1; transform: scale(1.06); }
#ci360-timeline-section .tl-step-btn.active { opacity: 1; transform: scale(1.13); }
#ci360-timeline-section .tl-dot {
  width: 44px; height: 44px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700;
  font-size: 12px;
  font-family: 'Poppins', Arial, sans-serif;
  border: 2px solid #334155;
  background: #0f172a;
  color: #64748b;
  transition: all 0.4s ease;
}
#ci360-timeline-section .tl-step-btn.passed .tl-dot { border-color: #3b82f6; color: #60a5fa; }
#ci360-timeline-section .tl-step-btn.active .tl-dot {
  background: #2563eb;
  border-color: #22d3ee;
  color: #fff;
  box-shadow: 0 0 0 5px rgba(37,99,235,0.18), 0 6px 20px rgba(34,211,238,0.35);
}
#ci360-timeline-section .tl-year-lbl {
  font-size: 11px;
  font-family: 'Poppins', Arial, sans-serif;
  font-weight: 600;
  letter-spacing: 0.06em;
  color: #64748b;
  transition: color 0.3s;
}
#ci360-timeline-section .tl-step-btn.passed .tl-year-lbl { color: #cbd5e1; }
#ci360-timeline-section .tl-step-btn.active .tl-year-lbl { color: #22d3ee; }
#ci360-timeline-section .tl-card {
  position: relative;
  border-radius: 24px;
  background: rgba(15,23,42,0.90);
  border: 1px solid rgba(51,65,85,0.80);
  overflow: hidden;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  transition: border-color 0.35s ease;
  box-shadow: 0 30px 70px rgba(0,0,0,0.50), 0 0 0 1px rgba(255,255,255,0.03) inset;
}
#ci360-timeline-section .tl-card:hover { border-color: rgba(34,211,238,0.30); }
#ci360-timeline-section .tl-card-glow {
  position: absolute;
  top: -60px; right: -60px;
  width: 480px; height: 480px;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  opacity: 0.50;
  transition: background 0.5s ease;
  z-index: 0;
}
#ci360-timeline-section .tl-card-grid {
  display: grid;
  grid-template-columns: 1fr;
  position: relative;
  z-index: 1;
  min-height: 420px;
  background: #0f172a;
}
@media (min-width: 900px) {
  #ci360-timeline-section .tl-card-grid { display: block; min-height: 500px; }
  #ci360-timeline-section .tl-card-left { width: 52%; min-height: 500px; }
}
@media (min-width: 1200px) {
  #ci360-timeline-section .tl-card-grid { min-height: 520px; }
  #ci360-timeline-section .tl-card-left { width: 50%; min-height: 520px; }
}
#ci360-timeline-section .tl-card-left {
  display: flex;
  flex-direction: column;
  gap: 22px;
  padding: 50px 46px;
  justify-content: center;
  position: relative;
  z-index: 2;
}
@media (max-width: 899px) {
  #ci360-timeline-section .tl-card-left { padding: 32px 26px; }
}
#ci360-timeline-section .tl-year-big {
  font-size: clamp(3rem, 6vw, 4.8rem);
  font-weight: 700;
  font-family: 'Poppins', Arial, sans-serif;
  background: linear-gradient(135deg, #22d3ee 0%, #3b82f6 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  color: transparent;
  line-height: 1;
  display: block;
  margin-bottom: 6px;
}
#ci360-timeline-section .tl-card-title {
  font-family: 'Poppins', Arial, sans-serif;
  font-size: clamp(1.45rem, 2.6vw, 2.1rem);
  font-weight: 700;
  color: #f1f5f9;
  line-height: 1.25;
  margin: 0;
}
#ci360-timeline-section .tl-card-desc {
  font-family: 'Poppins', Arial, sans-serif;
  font-size: 1rem;
  color: #94a3b8;
  font-weight: 300;
  line-height: 1.8;
  margin: 0;
  max-width: 500px;
}
#ci360-timeline-section .tl-tags { display: flex; flex-wrap: wrap; gap: 8px; }
#ci360-timeline-section .tl-tag {
  display: inline-flex;
  align-items: center;
  padding: 5px 14px;
  border-radius: 9999px;
  font-size: 12px;
  font-weight: 600;
  font-family: 'Poppins', Arial, sans-serif;
  letter-spacing: 0.06em;
  border: 1px solid rgba(34,211,238,0.25);
  background: rgba(34,211,238,0.06);
  color: #67e8f9;
  transition: all 0.2s ease;
}
#ci360-timeline-section .tl-tag:hover {
  background: rgba(34,211,238,0.14);
  border-color: rgba(34,211,238,0.45);
}
#ci360-timeline-section .tl-nav-row { display: flex; align-items: center; gap: 12px; padding-top: 6px; }
#ci360-timeline-section .tl-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  font-family: 'Poppins', Arial, sans-serif;
  cursor: pointer;
  transition: all 0.25s ease;
  border: none;
}
#ci360-timeline-section .tl-nav-btn:disabled { opacity: 0.25; pointer-events: none; }
#ci360-timeline-section .tl-nav-btn.prev {
  background: rgba(15,23,42,0.8);
  border: 1px solid #334155;
  color: #cbd5e1;
}
#ci360-timeline-section .tl-nav-btn.prev:hover { border-color: #475569; color: #fff; }
#ci360-timeline-section .tl-nav-btn.next {
  background: linear-gradient(135deg, #2563eb, #0891b2);
  color: #fff;
  box-shadow: 0 6px 20px rgba(37,99,235,0.30);
}
#ci360-timeline-section .tl-nav-btn.next:hover { filter: brightness(1.12); }
#ci360-timeline-section .tl-card-right {
  position: relative;
  overflow: hidden;
  min-height: 320px;
  border: 0 !important;
  outline: 0 !important;
  box-shadow: none !important;
  background: transparent !important;
  z-index: 1;
}
@media (min-width: 900px) {
  #ci360-timeline-section .tl-card-right {
    position: absolute;
    top: 0; right: 0; bottom: 0;
    width: 68%; height: 100%; min-height: 100%;
    margin: 0; overflow: hidden;
  }
}
@media (min-width: 1200px) {
  #ci360-timeline-section .tl-card-right { width: 70%; }
}
#ci360-timeline-section .tl-card-img {
  position: absolute;
  inset: 0;
  width: 100%; height: 100%;
  min-width: 100%; min-height: 100%;
  object-fit: cover;
  object-position: center center;
  display: block;
  border: 0 !important;
  outline: 0 !important;
  box-shadow: none !important;
  transform: scale(1.015);
  transform-origin: center center;
  transition: transform 0.8s cubic-bezier(0.25,0.46,0.45,0.94);
  -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,0.06) 8%, rgba(0,0,0,0.18) 16%, rgba(0,0,0,0.38) 26%, rgba(0,0,0,0.62) 38%, rgba(0,0,0,0.82) 50%, rgba(0,0,0,0.96) 62%, #000 72%, #000 100%);
  mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,0.06) 8%, rgba(0,0,0,0.18) 16%, rgba(0,0,0,0.38) 26%, rgba(0,0,0,0.62) 38%, rgba(0,0,0,0.82) 50%, rgba(0,0,0,0.96) 62%, #000 72%, #000 100%);
  -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat;
  -webkit-mask-size: 100% 100%; mask-size: 100% 100%;
}
#ci360-timeline-section .tl-card:hover .tl-card-img { transform: scale(1.055); }
#ci360-timeline-section .tl-img-fade {
  position: absolute; inset: 0; z-index: 2; pointer-events: none;
  border: 0 !important; outline: 0 !important;
  background: linear-gradient(to right, #0f172a 0%, rgba(15,23,42,0.99) 10%, rgba(15,23,42,0.96) 20%, rgba(15,23,42,0.88) 30%, rgba(15,23,42,0.72) 40%, rgba(15,23,42,0.48) 52%, rgba(15,23,42,0.25) 64%, rgba(15,23,42,0.10) 76%, rgba(15,23,42,0.03) 88%, transparent 100%);
}
#ci360-timeline-section .tl-img-vignette {
  position: absolute; inset: 0; z-index: 2; pointer-events: none;
  background: linear-gradient(180deg, rgba(2,6,23,0.20) 0%, transparent 24%, transparent 72%, rgba(2,6,23,0.32) 100%);
}
@media (max-width: 899px) {
  #ci360-timeline-section .tl-card-right {
    position: relative; width: 100%; height: auto; min-height: 260px; margin: 0; order: -1;
  }
  #ci360-timeline-section .tl-card-img {
    inset: 0; width: 100%; height: 100%; transform: scale(1.015);
    -webkit-mask-image: linear-gradient(to bottom, #000 0%, #000 52%, rgba(0,0,0,0.92) 64%, rgba(0,0,0,0.70) 76%, rgba(0,0,0,0.42) 86%, rgba(0,0,0,0.15) 95%, transparent 100%);
    mask-image: linear-gradient(to bottom, #000 0%, #000 52%, rgba(0,0,0,0.92) 64%, rgba(0,0,0,0.70) 76%, rgba(0,0,0,0.42) 86%, rgba(0,0,0,0.15) 95%, transparent 100%);
  }
  #ci360-timeline-section .tl-img-fade {
    background: linear-gradient(to bottom, transparent 0%, transparent 52%, rgba(15,23,42,0.10) 66%, rgba(15,23,42,0.32) 78%, rgba(15,23,42,0.68) 90%, #0f172a 100%);
  }
}
#ci360-timeline-section .tl-progress-dots {
  position: absolute; bottom: 18px; right: 20px; display: flex; flex-direction: row; gap: 6px; align-items: center; z-index: 2;
}
#ci360-timeline-section .tl-pdot {
  width: 7px; height: 7px; border-radius: 50%;
  background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.28); transition: all 0.4s ease;
}
#ci360-timeline-section .tl-pdot.done { background: #3b82f6; border-color: #3b82f6; }
#ci360-timeline-section .tl-pdot.current {
  background: #22d3ee; border-color: #22d3ee; box-shadow: 0 0 8px rgba(34,211,238,0.7); transform: scale(1.5);
}
#ci360-timeline-section .tl-card-inner { animation: tlContentFade 0.34s ease forwards; }
@keyframes tlContentFade { from { opacity: 0; } to { opacity: 1; } }
#ci360-timeline-section .tl-card-img { animation: tlImageGrowFade 0.72s cubic-bezier(0.22, 1, 0.36, 1) both; }
@keyframes tlImageGrowFade { 0% { opacity: 0; transform: scale(0.96); } 100% { opacity: 1; transform: scale(1.015); } }
@keyframes ci360FlamePulse { 0%,100% { opacity:1; transform:scale(1); } 50% { opacity:0.7; transform:scale(1.2); } }
.ci360-flame { animation: ci360FlamePulse 2s ease-in-out infinite; color: #fbbf24; display: inline-flex; }
#ci360-timeline-section svg { display: inline-block; vertical-align: middle; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
@media (max-width: 767px) {
  #ci360-timeline-section { padding: 32px 0 48px; overflow-x: hidden; }
  #ci360-timeline-section .tl-inner { padding: 0 16px; }
  #ci360-timeline-section .tl-heading-wrap { margin-bottom: 28px; }
  #ci360-timeline-section .tl-main-heading { font-size: 28px; }
  #ci360-timeline-section .tl-track-wrap { margin-bottom: 22px; }
  #ci360-timeline-section .tl-steps { justify-content: flex-start; gap: 10px; padding: 12px 4px 16px; scroll-snap-type: x proximity; -webkit-overflow-scrolling: touch; }
  #ci360-timeline-section .tl-step-btn { min-width: 64px; flex: 0 0 64px; gap: 7px; scroll-snap-align: center; }
  #ci360-timeline-section .tl-step-btn:hover { transform: none; }
  #ci360-timeline-section .tl-step-btn.active { transform: scale(1.06); }
  #ci360-timeline-section .tl-dot { width: 40px; height: 40px; font-size: 11px; }
  #ci360-timeline-section .tl-year-lbl { font-size: 10px; }
  #ci360-timeline-section .tl-card { border-radius: 18px; }
  #ci360-timeline-section .tl-card-grid { grid-template-columns: 1fr; }
  #ci360-timeline-section .tl-card-right { min-height: 200px; }
  #ci360-timeline-section .tl-card-left { padding: 24px 18px; gap: 16px; }
  #ci360-timeline-section .tl-year-big { font-size: 42px; }
  #ci360-timeline-section .tl-card-title { font-size: 22px; }
  #ci360-timeline-section .tl-card-desc { font-size: 14px; line-height: 1.65; max-width: 100%; }
  #ci360-timeline-section .tl-tags { gap: 6px; }
  #ci360-timeline-section .tl-tag { padding: 5px 10px; font-size: 10px; }
  #ci360-timeline-section .tl-nav-row { width: 100%; gap: 10px; }
  #ci360-timeline-section .tl-nav-btn { flex: 1 1 0; justify-content: center; padding: 11px 12px; border-radius: 10px; font-size: 11px; }
  #ci360-timeline-section .tl-progress-dots { bottom: 10px; right: 14px; }
}
@media (prefers-reduced-motion: reduce) {
  #ci360-timeline-section .tl-card-inner, #ci360-timeline-section .tl-card-img { animation: none !important; transition: none !important; }
}
</style>

<section id="ci360-timeline-section">
  <div class="tl-inner">
    <div class="tl-heading-wrap">
      <?php echo ci_sec_label( 'about_journey', $id ); ?>
      <h2 class="tl-main-heading">
        Built one <em>meaningful</em><br>
        business problem at a time.
      </h2>
    </div>
    <div class="tl-track-wrap">
      <div class="tl-track-rail"></div>
      <div class="tl-track-fill" id="tlFill"></div>
      <div class="tl-steps" id="tlSteps"></div>
    </div>
    <div class="tl-card" id="tlCard">
      <div class="tl-card-glow" id="tlGlow"></div>
      <div class="tl-card-inner" id="tlCardInner"></div>
    </div>
  </div>
</section>

<script>
(function () {
  var fallbackImg = "<?php echo esc_url( get_template_directory_uri() . '/assets/images/Asset-2365.jpg' ); ?>";
  var milestones = [
    {
      year: "2017",
      title: "Founded by a veteran of <br/>18 years in core <br/>marketing functions.",
      desc: "We started by getting closer to businesses, their audiences, and the challenges that truly mattered.",
      tags: ["Strategy", "Research", "Clarity"],
      img:  "/wp-content/uploads/2026/08/bg7.jpeg",
      glow: "rgba(59,130,246,0.22), rgba(6,182,212,0.08)"
    },
    {
      year: "2018",
      title: "Launched digital <br/>marketing services <br/>in the US.",
      desc: "Launched digital marketing services in the US, expanding our reach into new markets.",
      tags: ["Digital", "Expansion", "US Market"],
      img:  "/wp-content/uploads/2026/09/Creative.jpg",
      glow: "rgba(59,130,246,0.22), rgba(99,102,241,0.08)"
    },
    {
      year: "2019",
      title: "Ideas Became <br/>Connected Brand <br/>Experiences.",
      desc: "Our canvas widened as we began shaping brands across multiple communication touchpoints.",
      tags: ["Brand", "Content", "Digital"],
      img:  "/wp-content/uploads/2026/08/bg2.jpeg",
      glow: "rgba(99,102,241,0.22), rgba(59,130,246,0.08)"
    },
    {
      year: "2020",
      title: "Growing fast in Ahmedabad, Delhi, Mumbai & the US — focused on SMEs.",
      desc: "New realities pushed us to rethink faster, respond smarter, and help brands navigate uncertainty.",
      tags: ["Agility", "Focus", "Momentum"],
      img:  "/wp-content/uploads/2026/08/bg3.jpeg",
      glow: "rgba(251,191,36,0.16), rgba(239,68,68,0.07)"
    },
    {
      year: "2021",
      title: "Expanded US operations in digital marketing and custom software development.",
      desc: "Ideas gained greater scale and precision as new tools reshaped how we brought them to life.",
      tags: ["Technology", "Automation", "Performance"],
      img:  "/wp-content/uploads/2026/08/bg4.jpeg",
      glow: "rgba(16,185,129,0.16), rgba(6,182,212,0.08)"
    },
    {
      year: "2023",
      title: "Expanded horizons — introduced business consultancy to the portfolio.",
      desc: "A single narrative approach began guiding every interaction, from the first idea to the final customer experience.",
      tags: ["Consulting", "Storytelling", "Delivery"],
      img:  "/wp-content/uploads/2026/08/bg5.jpeg",
      glow: "rgba(6,182,212,0.22), rgba(59,130,246,0.08)"
    },
    {
      year: "2024",
      title: "Pioneering AI, <br/>forging a path toward <br/>transformative growth.",
      desc: "Pioneering AI, forging a path toward transformative growth across every service line.",
      tags: ["AI", "Innovation", "Growth"],
      img:  "/wp-content/uploads/2026/09/WI.jpg",
      glow: "rgba(16,185,129,0.20), rgba(99,102,241,0.08)"
    },
    {
      year: "2025",
      title: "Expansion and hiring <br/>of talent across <br/>segments.",
      desc: "Expansion and hiring of talent across segments, strengthening our capabilities nationwide.",
      tags: ["Expansion", "Talent", "Scale"],
      img:  "/wp-content/uploads/2026/09/PI.jpg",
      glow: "rgba(6,182,212,0.20), rgba(37,99,235,0.10)"
    },
    {
      year: "NOW",
      title: "One Integrated Partner<br/> for Meaningful<br/> Growth.",
      desc: "We now solve business challenges through connected thinking that turns opportunities into measurable outcomes.",
      tags: ["Intelligence", "Judgement", "Impact"],
      img:  "/wp-content/uploads/2026/08/bg_1.jpeg",
      glow: "rgba(99,102,241,0.22), rgba(6,182,212,0.10)"
    }
  ];

  var SVG = {
    flame:  '<svg viewBox="0 0 24 24" width="18" height="18"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',
    arrowL: '<svg viewBox="0 0 24 24" width="14" height="14"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
    arrowR: '<svg viewBox="0 0 24 24" width="14" height="14"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>'
  };

  var activeIdx = 0;
  var fillEl    = document.getElementById('tlFill');
  var stepsEl   = document.getElementById('tlSteps');
  var cardInner = document.getElementById('tlCardInner');
  var glowEl    = document.getElementById('tlGlow');

  var autoTimer = null;
  var autoDelay = 5000;

  function startAutoSlide() {
    stopAutoSlide();
    autoTimer = setInterval(function () {
      var nextIndex = activeIdx + 1;
      if (nextIndex >= milestones.length) { nextIndex = 0; }
      setActive(nextIndex, true);
    }, autoDelay);
  }

  function stopAutoSlide() {
    if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
  }

  function restartAutoSlide() { startAutoSlide(); }

  function isMobile() { return window.matchMedia('(max-width: 767px)').matches; }

  function scrollActiveYear() {
    if (!isMobile() || !stepsEl) return;
    var btn = stepsEl.querySelector('.tl-step-btn.active');
    if (!btn) return;
    var target = btn.offsetLeft - (stepsEl.clientWidth - btn.offsetWidth) / 2;
    stepsEl.scrollTo({ left: Math.max(0, target), behavior: 'smooth' });
  }

  function buildSteps() {
    stepsEl.innerHTML = '';
    milestones.forEach(function (m, idx) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'tl-step-btn' + (idx === activeIdx ? ' active' : idx < activeIdx ? ' passed' : '');
      var dot = m.year === 'NOW' ? '<span class="ci360-flame">' + SVG.flame + '</span>' : m.year.slice(2);
      btn.innerHTML = '<div class="tl-dot">' + dot + '</div><span class="tl-year-lbl">' + m.year + '</span>';
      btn.addEventListener('click', (function (i) {
        return function (e) {
          e.preventDefault();
          setActive(i, true);
          restartAutoSlide();
        };
      })(idx));
      stepsEl.appendChild(btn);
    });
  }

  function updateFill() {
    if (!fillEl) return;
    if (activeIdx === 0) { fillEl.style.width = '0'; return; }
    var pct = (activeIdx / (milestones.length - 1)) * 100;
    fillEl.style.width = 'calc(' + pct + '% - 16px)';
  }

  function buildProgressDots() {
    return milestones.map(function (m, idx) {
      var cls = idx < activeIdx ? 'tl-pdot done' : idx === activeIdx ? 'tl-pdot current' : 'tl-pdot';
      return '<div class="' + cls + '"></div>';
    }).join('');
  }

  function renderCard() {
    if (!cardInner || !glowEl) return;
    var m = milestones[activeIdx];
    glowEl.style.background = 'radial-gradient(ellipse, ' + m.glow + ')';

    var tagsHTML = m.tags.map(function (t) {
      return '<span class="tl-tag">' + t + '</span>';
    }).join('');

    cardInner.innerHTML =
      '<div class="tl-card-grid">' +
        '<div class="tl-card-left">' +
          '<div>' +
            '<span class="tl-year-big">' + m.year + '</span>' +
            '<h3 class="tl-card-title">' + m.title + '</h3>' +
          '</div>' +
          '<p class="tl-card-desc">' + m.desc + '</p>' +
          '<div class="tl-tags">' + tagsHTML + '</div>' +
          '<div class="tl-nav-row">' +
            '<button type="button" class="tl-nav-btn prev" id="tlPrev"' + (activeIdx === 0 ? ' disabled' : '') + '>' +
              SVG.arrowL + ' Previous' +
            '</button>' +
            '<button type="button" class="tl-nav-btn next" id="tlNext"' + (activeIdx === milestones.length - 1 ? ' disabled' : '') + '>' +
              'Next ' + SVG.arrowR +
            '</button>' +
          '</div>' +
        '</div>' +
        '<div class="tl-card-right">' +
          '<img class="tl-card-img"' +
            ' src="' + m.img + '"' +
            ' alt="CI360 ' + m.year + '"' +
            ' loading="eager"' +
            ' onerror="this.onerror=null;this.src=\'' + fallbackImg + '\';">' +
          '<div class="tl-img-fade"></div>' +
          '<div class="tl-img-vignette"></div>' +
          '<div class="tl-progress-dots">' + buildProgressDots() + '</div>' +
        '</div>' +
      '</div>';

    var p = document.getElementById('tlPrev');
    var n = document.getElementById('tlNext');
    if (p) p.addEventListener('click', function () { setActive(activeIdx - 1, true); restartAutoSlide(); });
    if (n) n.addEventListener('click', function () { setActive(activeIdx + 1, true); restartAutoSlide(); });

    cardInner.style.animation = 'none';
    void cardInner.offsetHeight;
    cardInner.style.animation = '';
  }

  function setActive(idx, scrollYear) {
    if (idx < 0 || idx >= milestones.length) return;
    activeIdx = idx;
    buildSteps();
    updateFill();
    renderCard();
    if (scrollYear && isMobile()) requestAnimationFrame(scrollActiveYear);
  }

  buildSteps();
  updateFill();
  renderCard();
  startAutoSlide();

  document.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight') { setActive(activeIdx + 1, true); restartAutoSlide(); }
    if (e.key === 'ArrowLeft') { setActive(activeIdx - 1, true); restartAutoSlide(); }
  });
})();
</script><section class="team-section section wrap" id="team"><div class="section-heading heading-row"><?php echo ci_sec_label( 'about_team', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_team_heading' ) ); ?></h2><p><?php echo ci_e( $g( 'about_team_intro' ) ); ?></p></div><div class="team-roster"><?php echo $team; ?></div></section><section class="section industry-section wrap"><div class="section-heading"><?php echo ci_sec_label( 'about_industries', $id ); ?><h2 data-reveal><?php echo ci_html( $g( 'about_industries_heading' ) ); ?></h2></div><div class="industry-pills"><?php echo $industries; ?></div></section><?php echo ci_compact_cta( $g( 'about_cta' ) ); ?></main><?php
get_footer();
