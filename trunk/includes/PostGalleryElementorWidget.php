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
    public $textdomain;

    public function __construct( $data = [], $args = null ) {
        $instances[] = $this;
        $this->textdomain = 'post-gallery';
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
                'label' => __( 'Images', $this->textdomain ),
            ]
        );
        $this->add_control(
            'pgimgsource',
            [
                'label' => __( 'Image-Source', $this->textdomain ),
                'type' => Controls_Manager::SELECT,
                'default' => filter_input( INPUT_GET, 'post' ),
                'options' => $selectPosts,
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgthumbwidth',
            [
                'label' => __( 'Thumb width', $this->textdomain ),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
                'placeholder' => PostGalleryPublic::getInstance()->option( 'thumbWidth' ),
            ]
        );
        $this->add_control(
            'pgthumbheight',
            [
                'label' => __( 'Thumb height', $this->textdomain ),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
                'placeholder' => PostGalleryPublic::getInstance()->option( 'thumbHeight' ),
            ]
        );
        $this->add_control(
            'pgthumbscale',
            [
                'label' => __( 'Thumb scale', $this->textdomain ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'selectors' => [],
                'options' => [
                    '0' => __( 'crop', $this->textdomain ),
                    '1' => __( 'long edge', $this->textdomain ),
                    '2' => __( 'short edge', $this->textdomain ),
                    '3' => __( 'ignore proportions', $this->textdomain ),
                ],
            ]
        );
        $this->add_control(
            'pgmaxthumbs',
            [
                'label' => __( 'Thumb amount', $this->textdomain ),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                /*'selectors' => [
                    '{{WRAPPER}} .gallery a:nth-child(n+{{VALUE}})' => 'display: none;'
                ],*/
            ]
        );

        $this->add_control(
            'pgelementorlitebox',
            [
                'label' => __( 'Use Elementor-Litebox', $this->textdomain ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'return_value' => 'on',
            ]
        );

        $this->add_control(
            'pgsort',
            [
                'label' => __( 'PostGallery Sort', $this->textdomain ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimgdescs',
            [
                'label' => __( 'PostGallery Descs', $this->textdomain ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimgtitles',
            [
                'label' => __( 'PostGallery Titles', $this->textdomain ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimgalts',
            [
                'label' => __( 'PostGallery Alts', $this->textdomain ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimgoptions',
            [
                'label' => __( 'PostGallery Options', $this->textdomain ),
                'type' => 'hidden',//Controls_Manager::TEXT,
                'default' => '',
                'selectors' => [],
            ]
        );
        $this->add_control(
            'pgimages',
            [
                'label' => __( 'PostGallery Images', $this->textdomain ),
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
        $settings = $GLOBALS['elementorWidgetSettings'] = $this->get_settings();
        $pgInstance = PostGalleryPublic::getInstance();

        // override global settings with widget-settings
        if ( !empty( $settings['pgthumbwidth'] ) ) {
            $globalWidth = $pgInstance->option( 'thumbWidth' );
            $pgInstance->setOption( 'thumbWidth', $settings['pgthumbwidth'] );
        }

        if ( !empty( $settings['pgthumbheight'] ) ) {
            $globalHeight = $pgInstance->option( 'thumbHeight' );
            $pgInstance->setOption( 'thumbHeight', $settings['pgthumbheight'] );
        }

        if ( isset( $settings['pgthumbscale'] ) ) {
            $globalScale = $pgInstance->option( 'thumbScale' );
            $pgInstance->setOption( 'thumbScale', $settings['pgthumbscale'] );
        }

        // get gallery
        $gallery = $pgInstance->returnGalleryHtml( '', $settings['pgimgsource'] );

        if ( !empty( $settings['pgelementorlitebox'] ) && $settings['pgelementorlitebox'] == 'on' ) {
            // use elementor litebox
            $gallery = str_replace( '<a ', '<a class="no-litebox" data-elementor-lightbox-slideshow="' . $this->get_id() . '" ', $gallery );
        } else {
            // use postgallery litebox
            $gallery = str_replace( '<a ', '<a data-elementor-open-lightbox="no" ', $gallery );
        }

        // echo gallery
        echo $gallery;

        // hide thumbs
        if ( !empty( $settings['pgmaxthumbs'] ) ) {
            echo '<style>';
            echo '.elementor-element-' . $this->get_id()
                . ' .gallery a:nth-child(n+' . ( $settings['pgmaxthumbs'] + 1 ) . ') { ';
            echo 'display: none;';
            echo '}';
            echo '</style>';
        }

        // reset global settings
        if ( isset( $globalWidth ) ) {
            $pgInstance->setOption( 'thumbWidth', $globalWidth );
        }
        if ( isset( $globalHeight ) ) {
            $pgInstance->setOption( 'thumbHeight', $globalHeight );
        }
        if ( isset( $globalScale ) ) {
            $pgInstance->setOption( 'thumbScale', $globalScale );
        }
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