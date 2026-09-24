<?php
/**
 * Elementor widget classes. Loaded only when Elementor registers widgets.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Base widget: builds its controls from the section registry and renders ci_s_{id}().
 */
abstract class CI360_Section_Widget extends Widget_Base {

	/** Section id in ci_section_defs(). */
	protected $ci_id = '';

	protected function ci_def() {
		$defs = ci_section_defs();
		return $defs[ $this->ci_id ];
	}
	public function get_name() {
		return 'ci360-' . $this->ci_id;
	}
	public function get_title() {
		return $this->ci_def()['title'];
	}
	public function get_icon() {
		return $this->ci_def()['icon'];
	}
	public function get_categories() {
		return array( 'ci360' );
	}
	public function get_keywords() {
		return array_merge( array( 'ci360', 'section' ), preg_split( '/[\s–&,()]+/u', strtolower( $this->get_title() ), -1, PREG_SPLIT_NO_EMPTY ) );
	}
	public function get_style_depends() {
		return array( 'ci360-main', 'ci360-sections', 'ci360-elementor', 'ci360-editable-v25' );
	}
	public function get_script_depends() {
		return array( 'ci360-main' );
	}
	/** No extra inner wrapper (Elementor 3.24+). */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}
	/** Output depends on posts and settings: never cache. */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/** Elementor control args for one registry control. */
	protected function ci_control_args( $c ) {
		$args = array( 'label' => $c['label'], 'label_block' => true );
		$def  = $c['default'] ?? '';
		switch ( $c['type'] ) {
			case 'text':
				$args += array( 'type' => Controls_Manager::TEXT, 'default' => $def, 'dynamic' => array( 'active' => true ) );
				break;
			case 'html':
				$args += array(
					'type'        => Controls_Manager::TEXT,
					'default'     => $def,
					'description' => 'Use &lt;br&gt; for a line break and &lt;em&gt;…&lt;/em&gt; for the accent words.',
				);
				break;
			case 'textarea':
				$args += array( 'type' => Controls_Manager::TEXTAREA, 'default' => $def, 'rows' => 5, 'dynamic' => array( 'active' => true ) );
				break;
			case 'number':
				$args += array( 'type' => Controls_Manager::NUMBER, 'default' => $def, 'min' => 0 );
				$args['label_block'] = false;
				break;
			case 'switch':
				$args += array( 'type' => Controls_Manager::SWITCHER, 'default' => $def, 'return_value' => 'yes' );
				$args['label_block'] = false;
				break;
			case 'image':
				$args += array( 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => $def ? ci_img_url( $def ) : '', 'id' => '' ) );
				break;
			case 'select':
				$args += array( 'type' => Controls_Manager::SELECT, 'default' => $def, 'options' => $c['options'] );
				break;
			case 'post':
				$args += array( 'type' => Controls_Manager::SELECT, 'default' => $def, 'options' => ci_el_post_options( $c['source'] ) );
				break;
			case 'posts':
				$args += array( 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'default' => $def, 'options' => array_slice( ci_el_post_options( $c['source'] ), 1, null, true ) );
				break;
			case 'term':
				$args += array( 'type' => Controls_Manager::SELECT, 'default' => $def, 'options' => ci_el_term_options( $c['source'] ) );
				break;
		}
		return $args;
	}

	/** Repeater default rows: image paths become media values. */
	protected function ci_repeater_default( $c ) {
		$rows = $c['default'];
		if ( ! is_array( $rows ) ) {
			return array();
		}
		foreach ( $rows as &$row ) {
			foreach ( $c['fields'] as $f ) {
				if ( 'image' === $f['type'] ) {
					$v               = $row[ $f['key'] ] ?? '';
					$row[ $f['key'] ] = array( 'url' => $v ? ci_img_url( $v ) : '', 'id' => '' );
				}
			}
		}
		return $rows;
	}

	protected function register_controls() {
		$def  = $this->ci_def();
		$open = false;
		$n    = 0;
		$note = function () use ( $def ) {
			$html = '<div style="line-height:1.5">';
			if ( ! empty( $def['desc'] ) ) {
				$html .= esc_html( $def['desc'] ) . '<br>';
			}
			$html .= '<a href="' . esc_url( admin_url( 'admin.php?page=ci360-settings' ) ) . '" target="_blank">CI360 Settings</a> · <a href="' . esc_url( admin_url( 'edit.php?post_type=ci_service' ) ) . '" target="_blank">Services</a> · <a href="' . esc_url( admin_url( 'edit.php?post_type=ci_project' ) ) . '" target="_blank">Projects</a></div>';
			$this->add_control(
				'ci_note',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => $html,
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				)
			);
		};
		foreach ( $def['controls'] as $c ) {
			if ( 'section' === $c['type'] ) {
				if ( $open ) {
					$this->end_controls_section();
				}
				$this->start_controls_section( 'ci_sec_' . ( $n++ ), array( 'label' => $c['label'], 'tab' => Controls_Manager::TAB_CONTENT ) );
				if ( ! $open ) {
					$note();
				}
				$open = true;
				continue;
			}
			if ( ! $open ) {
				$this->start_controls_section( 'ci_sec_' . ( $n++ ), array( 'label' => 'Content', 'tab' => Controls_Manager::TAB_CONTENT ) );
				$note();
				$open = true;
			}
			if ( 'repeater' === $c['type'] ) {
				$rep = new Repeater();
				foreach ( $c['fields'] as $f ) {
					$rep->add_control( $f['key'], $this->ci_control_args( $f ) );
				}
				$this->add_control(
					$c['key'],
					array(
						'label'       => $c['label'],
						'type'        => Controls_Manager::REPEATER,
						'fields'      => $rep->get_controls(),
						'default'     => $this->ci_repeater_default( $c ),
						'title_field' => '{{{ ' . $c['title_field'] . ' }}}',
					)
				);
				continue;
			}
			$this->add_control( $c['key'], $this->ci_control_args( $c ) );
		}
		if ( ! $open ) {
			$this->start_controls_section( 'ci_sec_0', array( 'label' => 'Content', 'tab' => Controls_Manager::TAB_CONTENT ) );
			$note();
		}
		$this->end_controls_section();
	}

	protected function render() {
		$s = (array) $this->get_settings_for_display();
		foreach ( $this->ci_def()['controls'] as $c ) {
			if ( empty( $c['key'] ) || ! array_key_exists( $c['key'], $s ) ) {
				continue;
			}
			if ( 'image' === $c['type'] ) {
				$s[ $c['key'] ] = ci_media( $s[ $c['key'] ] );
			} elseif ( 'repeater' === $c['type'] && is_array( $s[ $c['key'] ] ) ) {
				foreach ( $s[ $c['key'] ] as &$row ) {
					foreach ( $c['fields'] as $f ) {
						if ( 'image' === $f['type'] && isset( $row[ $f['key'] ] ) ) {
							$row[ $f['key'] ] = ci_media( $row[ $f['key'] ] );
						}
					}
				}
				unset( $row );
			}
		}
		$html = ci_section( $this->ci_id, $s );
		if ( '' === $html && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$html = '<div style="padding:40px;text-align:center;border:1px dashed #9db8ff;font:14px sans-serif">' . esc_html( $this->get_title() ) . ': nothing to show yet. Add content in WordPress admin.</div>';
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the renderer.
	}
}

/* One class per section (Elementor needs a class per widget type). */
class CI360_Widget_home_hero extends CI360_Section_Widget { protected $ci_id = 'home-hero'; }
class CI360_Widget_brand_strip extends CI360_Section_Widget { protected $ci_id = 'brand-strip'; }
class CI360_Widget_home_work extends CI360_Section_Widget { protected $ci_id = 'home-work'; }
class CI360_Widget_home_capabilities extends CI360_Section_Widget { protected $ci_id = 'home-capabilities'; }
class CI360_Widget_home_about extends CI360_Section_Widget { protected $ci_id = 'home-about'; }
class CI360_Widget_word_marquee extends CI360_Section_Widget { protected $ci_id = 'word-marquee'; }
class CI360_Widget_process extends CI360_Section_Widget { protected $ci_id = 'process'; }
class CI360_Widget_testimonials extends CI360_Section_Widget { protected $ci_id = 'testimonials'; }
class CI360_Widget_insights_teaser extends CI360_Section_Widget { protected $ci_id = 'insights-teaser'; }
class CI360_Widget_insights_featured extends CI360_Section_Widget { protected $ci_id = 'insights-featured'; }
class CI360_Widget_faq extends CI360_Section_Widget { protected $ci_id = 'faq'; }
class CI360_Widget_compact_cta extends CI360_Section_Widget { protected $ci_id = 'compact-cta'; }
class CI360_Widget_page_banner extends CI360_Section_Widget { protected $ci_id = 'page-banner'; }
class CI360_Widget_blog_archive extends CI360_Section_Widget { protected $ci_id = 'blog-archive'; }
class CI360_Widget_blog_grid extends CI360_Section_Widget { protected $ci_id = 'blog-grid'; }
class CI360_Widget_page_hero extends CI360_Section_Widget { protected $ci_id = 'page-hero'; }
class CI360_Widget_about_compass extends CI360_Section_Widget { protected $ci_id = 'about-compass'; }
class CI360_Widget_about_belief extends CI360_Section_Widget { protected $ci_id = 'about-belief'; }
class CI360_Widget_about_journey extends CI360_Section_Widget { protected $ci_id = 'about-journey'; }
class CI360_Widget_about_team extends CI360_Section_Widget { protected $ci_id = 'about-team'; }
class CI360_Widget_about_industries extends CI360_Section_Widget { protected $ci_id = 'about-industries'; }
class CI360_Widget_founders_profiles extends CI360_Section_Widget { protected $ci_id = 'founders-profiles'; }
class CI360_Widget_founders_belief extends CI360_Section_Widget { protected $ci_id = 'founders-belief'; }
class CI360_Widget_founders_studio extends CI360_Section_Widget { protected $ci_id = 'founders-studio'; }
class CI360_Widget_services_hero extends CI360_Section_Widget { protected $ci_id = 'services-hero'; }
class CI360_Widget_services_grid extends CI360_Section_Widget { protected $ci_id = 'services-grid'; }
class CI360_Widget_work_hero extends CI360_Section_Widget { protected $ci_id = 'work-hero'; }
class CI360_Widget_work_portfolio extends CI360_Section_Widget { protected $ci_id = 'work-portfolio'; }
class CI360_Widget_insights_list extends CI360_Section_Widget { protected $ci_id = 'insights-list'; }
class CI360_Widget_topics extends CI360_Section_Widget { protected $ci_id = 'topics'; }
class CI360_Widget_contact_main extends CI360_Section_Widget { protected $ci_id = 'contact-main'; }
class CI360_Widget_locations extends CI360_Section_Widget { protected $ci_id = 'locations'; }
class CI360_Widget_legal extends CI360_Section_Widget { protected $ci_id = 'legal'; }
class CI360_Widget_service_detail extends CI360_Section_Widget { protected $ci_id = 'service-detail'; }
class CI360_Widget_project_detail extends CI360_Section_Widget { protected $ci_id = 'project-detail'; }
class CI360_Widget_insight_detail extends CI360_Section_Widget { protected $ci_id = 'insight-detail'; }
class CI360_Widget_site_header extends CI360_Section_Widget { protected $ci_id = 'site-header'; }
class CI360_Widget_site_footer extends CI360_Section_Widget { protected $ci_id = 'site-footer'; }
