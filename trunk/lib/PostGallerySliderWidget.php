<?php

namespace Lib;

use Admin\PostGalleryAdmin;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Pub\PostGalleryPublic;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Elementor PostGallery
 *
 * Elementor widget for PostGallery.
 *
 * @since 1.0.0
 */
class PostGallerySliderWidget extends Widget_Base {
    public static $instances = [];
    private $postgalleryAdmin;

    public function __construct( $data = [], $args = null ) {
        $instances[] = $this;

        $this->postgalleryAdmin = PostGalleryAdmin::getInstance();

        parent::__construct( $data, $args );
    }

    public static function getInstances() {
        return self::instances;
    }

    /**
     * Retrieve the widget name.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'postgalleryslider';
    }

    /**
     * Retrieve the widget title.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'PostGallery-Slider', 'postgallery' );
    }

    /**
     * Retrieve the widget icon.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-slideshow';
    }

    /**
     * Retrieve the list of categories the widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * Note that currently Elementor supports only one category.
     * When multiple categories passed, Elementor uses the first one.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return [ 'basic' ];
    }

    /**
     * Retrieve the list of scripts the widget depended on.
     *
     * Used to set scripts dependencies required to run the widget.
     *
     * @since 1.0.0
     *
     * @access public
     *
     * @return array Widget scripts dependencies.
     */
    public function get_script_depends() {
        return [ 'postgallery' ];
    }

    /**
     * Register the widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     *
     * @access protected
     */
    protected function _register_controls() {
        $filterPostTypes = explode( ',', 'nav_menu_item,revision,custom_css,customize_changeset,'
            . 'oembed_cache,ocean_modal_window,nxs_qp,elementor_library,attachment,dtbaker_style' );
        $allPosts = get_posts( [
            'post_type' => 'postgalleryslider',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'suppress_filters' => false,
        ] );

        // $selectPosts = [ 0 => __( 'Dynamic', 'postgallery' ) ];

        $selectPosts = [];
        foreach ( $allPosts as $post ) {
            if ( in_array( $post->post_type, $filterPostTypes ) ) {
                continue;
            }
            $selectPosts[$post->ID] = $post->post_title . ' (' . $post->post_type . ')';
        }

        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'elementor' ),
            ]
        );

        $this->add_control(
            'slider',
            [
                'label' => __( 'Slider', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => $selectPosts,
                'selectors' => [],
            ]
        );

        $this->add_control(
            'sliderOpenSliderNotice',
            [
                'type' => Controls_Manager::RAW_HTML,
                'label' => 'Open Slider',
                'show_label' => false,
                'raw' => '<a href="#" class="button-link" 
                    onclick="window.open(\''.admin_url( 'post.php?action=edit&post='  ).'\' + $(\'.elementor-control-slider select\').val(), \'pgslidernew\', \'height=500,width=600\');return false;">'
                    . __( 'Open Slider', 'postgallery' ) . '</a>',
                'content_classes' => '',
                'condition' => [
                    'slider!' => 0,
                ]
            ]
        );

        $this->add_control(
            'sliderAddNewLinkNotice',
            [
                'type' => Controls_Manager::RAW_HTML,
                'label' => 'New Slider',
                'show_label' => false,
                'raw' => '<a href="#" class="button-link" 
                    onclick="window.open(\''.admin_url( 'post-new.php?post_type=postgalleryslider' ).'\', \'pgslidernew\', \'height=500,width=600\');return false;">'
                    . __( 'New Slider', 'postgallery' ) . '</a>',
                'content_classes' => '',
            ]
        );


        $this->end_controls_section();
    }

    /**
     * Render the widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     *
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings();
        $pgInstance = PostGalleryPublic::getInstance();

        if ( is_numeric( $settings['slider'] ) ) {
            echo do_shortcode( '[slider ' . $settings['slider'] . ']' );
        }
    }
}