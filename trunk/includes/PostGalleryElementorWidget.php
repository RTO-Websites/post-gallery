<?php

namespace PostGalleryWidget\Widgets;

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
class PostGalleryElementorWidget extends Widget_Base {
    public static $instances = [];
    public $textdomain;
    public $postgalleryAdmin;

    public function __construct( $data = [], $args = null ) {
        $instances[] = $this;
        $this->textdomain = 'post-gallery';

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
        $filerPostTypes = explode( ',', 'nav_menu_item,revision,custom_css,customize_changeset,'
            . 'oembed_cache,ocean_modal_window,nxs_qp,elementor_library,attachment,dtbaker_style' );
        $allPosts = get_posts( [
            'post_type' => get_post_types(),
            'posts_per_page' => -1,
            'post_status' => 'any',
            'suppress_filters' => false,
        ] );

        $selectPosts = [ 0 => __( 'Dynamic', $this->textdomain ) ];

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
            'template',
            [
                'label' => __( 'Template', $this->textdomain ),
                'type' => Controls_Manager::SELECT,
                'default' => 'thumbs',
                'selectors' => [],
                'options' => array_merge(
                    [ 'global' => 'From Global' ],
                    $this->postgalleryAdmin->getCustomTemplates(),
                    $this->postgalleryAdmin->defaultTemplates
                ),
            ]
        );

        $this->add_control(
            'columns',
            [
                'label' => __( 'Columns', $this->textdomain ),
                'type' => Controls_Manager::SELECT,
                'default' => 'auto',
                'selectors' => [],
                'options' => [
                    'none' => __( 'None' ),
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                    '7' => '7',
                    '8' => '8',
                    '9' => '9',
                    '10' => '10',
                ],
            ]
        );

        $this->add_control(
            'pgimgsource',
            [
                'label' => __( 'Image-Source', $this->textdomain ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => $selectPosts,
                'selectors' => [],
            ]
        );

        $imageSizes = [
            0 => __( 'Custom' ),
        ];

        foreach ( Group_Control_Image_Size::get_all_image_sizes() as $name => $size ) {
            $key = $size['width'] . 'x' . $size['height'];
            $label = ucfirst( $name ) . ' (' . $key . ')';
            $imageSizes[$key] = $label;
        }

        $this->add_control(
            'imageSize',
            [
                'label' => __( 'Image-Size', $this->textdomain ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => $imageSizes,
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
            'masonry',
            [
                'label' => __( 'Masonry', $this->textdomain ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => [
                    0 => __( 'off' ),
                    'vertical' => 'vertical',
                    'horizontal' => 'horizontal',
                ],
                'selectors' => [],
            ]
        );


        $this->add_control(
            'equal_height',
            [
                'label' => __( 'Equal height', $this->textdomain ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'return_value' => 'on',
            ]
        );

        $this->add_control(
            'item_ratio',
            [
                'label' => __( 'Item Ratio', 'elementor-pro' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.66,
                ],
                'range' => [
                    'px' => [
                        'min' => 0.1,
                        'max' => 2,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item .bg-image' => 'padding-bottom: calc( {{SIZE}} * 100% )',
                    '{{WRAPPER}}:after' => 'content: "{{SIZE}}"; position: absolute; color: transparent;',
                ],
                'condition' => [
                    'equal_height' => 'on',
                ],
                'frontend_available' => true,
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


        $this->start_controls_section(
            'section_gallery_images',
            [
                'label' => __( 'Images', 'elementor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'column_gap',
            [
                'label' => __( 'Columns Gap', 'elementor-pro' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-image-gallery' => 'margin-left: calc(-{{SIZE}}{{UNIT}} / 2);margin-right: calc(-{{SIZE}}{{UNIT}} / 2);',
                    '{{WRAPPER}} .elementor-image-gallery .gallery-item' => 'padding-left: calc({{SIZE}}{{UNIT}} / 2);padding-right: calc({{SIZE}}{{UNIT}} / 2);',
                ],
            ]
        );

        $this->add_control(
            'row_gap',
            [
                'label' => __( 'Rows Gap', 'elementor-pro' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'frontend_available' => true,
                'selectors' => [
                    '{{WRAPPER}} .elementor-image-gallery .gallery-item' => 'padding-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} .gallery-item img, {{WRAPPER}} .gallery-item .bg-image',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'image_border_radius',
            [
                'label' => __( 'Border Radius', 'elementor' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .gallery-item img, {{WRAPPER}} .gallery-item .bg-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
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

        $wrapperClass = '';

        // override global settings with widget-settings
        if ( !empty( $settings['imageSize'] ) ) {
            $globalWidth = $pgInstance->option( 'thumbWidth' );
            $globalHeight = $pgInstance->option( 'thumbHeight' );

            $sizes = explode( 'x', $settings['imageSize'] );
            $pgInstance->setOption( 'thumbWidth', $sizes[0] );
            $pgInstance->setOption( 'thumbHeight', $sizes[1] );
        } else {
            if ( !empty( $settings['pgthumbwidth'] ) ) {
                $globalWidth = $pgInstance->option( 'thumbWidth' );
                $pgInstance->setOption( 'thumbWidth', $settings['pgthumbwidth'] );
            }

            if ( !empty( $settings['pgthumbheight'] ) ) {
                $globalHeight = $pgInstance->option( 'thumbHeight' );
                $pgInstance->setOption( 'thumbHeight', $settings['pgthumbheight'] );
            }
        }

        if ( isset( $settings['pgthumbscale'] ) ) {
            $globalScale = $pgInstance->option( 'thumbScale' );
            $pgInstance->setOption( 'thumbScale', $settings['pgthumbscale'] );
        }

        if ( isset( $settings['columns'] ) ) {
            $globalColumns = $pgInstance->option( 'columns' );
            $pgInstance->setOption( 'columns', $settings['columns'] );
        }

        $template = '';
        if ( isset( $settings['template'] ) ) {
            $globalTemplate = $pgInstance->option( 'globalTemplate' );
            $pgInstance->setOption( 'globalTemplate', $settings['template'] );
            $template = $settings['template'];
        }

        if ( !empty( $settings['masonry'] ) ) {
            $pgInstance->setOption( 'masonry', $settings['masonry'] );
        }

        if ( !empty( $settings['equal_height'] ) ) {
            $wrapperClass .= ' items-equal';
        }

        // get gallery
        $loadFrom = $settings['pgimgsource'];
        if ( empty( $loadFrom ) ) {
            $loadFrom = get_the_ID();
        }

        $gallery = '<div class="elementor-image-gallery ' . $wrapperClass . '">';
        $gallery .= $pgInstance->returnGalleryHtml( $template, $loadFrom );
        $gallery .= '</div>';

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
        if ( isset( $globalColumns ) ) {
            $pgInstance->setOption( 'globalColumns', $globalColumns );
        }
        if ( isset( $globalTemplate ) ) {
            $pgInstance->setOption( 'globalTemplate', $globalTemplate );
        }
        $pgInstance->setOption( 'masonry', '' );
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