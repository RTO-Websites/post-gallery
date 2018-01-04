<?php

namespace PostGalleryWidget\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Pub\PostGalleryPublic;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Elementor Hello World
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class PostGalleryElementorWidget extends Widget_Base {
    public static $instances = [];

    public function __construct( $data = [], $args = null ) {
        $instances[] = $this;
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
        return 'postgallery';
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
        return __( 'PostGallery', 'postgallery' );
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
        return 'eicon-posts-ticker';
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
        return [ 'general-elements' ];
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
        $filerPostTypes = explode( ',', 'nav_menu_item,revision,custom_css,customize_changeset,'
            . 'oembed_cache,ocean_modal_window,nxs_qp,elementor_library,attachment,dtbaker_style' );
        $allPosts = get_posts( array(
            'post_type' => get_post_types(),
            'posts_per_page' => -1,
            'post_status' => 'any',
            'suppress_filters' => false,
        ) );
        //$selectPosts = [0 => __('Self')];
        foreach ( $allPosts as $post ) {
            if ( in_array( $post->post_type, $filerPostTypes ) ) {
                continue;
            }
            $selectPosts[$post->ID] = $post->post_title . ' (' . $post->post_type . ')';
        }

        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Images', 'postgallery' ),
            ]
        );
        $this->add_control(
            'pgimgsource',
            [
                'label' => __( 'PostGallery Source', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => filter_input( INPUT_GET, 'post' ),
                'options' => $selectPosts,
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgsort',
            [
                'label' => __( 'PostGallery Sort', 'postgallery' ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimgdescs',
            [
                'label' => __( 'PostGallery Descs', 'postgallery' ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimgtitles',
            [
                'label' => __( 'PostGallery Titles', 'postgallery' ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimgalts',
            [
                'label' => __( 'PostGallery Alts', 'postgallery' ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimgoptions',
            [
                'label' => __( 'PostGallery Options', 'postgallery' ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimages',
            [
                'label' => __( 'PostGallery Images', 'postgallery' ),
                'type' => 'postgallerycontrol',
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
        //var_dump($settings);
        echo PostGalleryPublic::getInstance()->returnGalleryHtml( '', $settings['pgimgsource'] );
        //echo do_shortcode( "[postgallery]" );
    }
    /**
     * Render the widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 1.0.0
     *
     * @access protected
     */
    /*protected function _content_template() {
        ?>
        <div class="title">
            {{{ settings.title }}}
        </div>
        <?php
    }*/
}