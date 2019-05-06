<?php

namespace Lib\Widgets;

use Admin\PostGalleryAdmin;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Lib\PostGallery;
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
        return 'fa fa-image';
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
     * Returns all widgets of given type
     *
     * @param array $data
     * @param string $type
     * @return array
     */
    public function searchElements( array $data, string $type ) {
        $output = [];
        foreach ( $data as $element ) {
            if ( !empty( $element['widgetType'] ) && $element['widgetType'] === 'media-carousel' ) {
                $output[] = $element;
            }

            if ( !empty( $element['elements'] ) ) {
                $children = $this->searchElements( $element['elements'], $type );
                if ( !empty( $children ) ) {
                    $output = array_merge( $output, $children );
                }
            }
        }

        return $output;
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
        $selectPosts = PostGallery::getPostList();

        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'elementor' ),
            ]
        );

        $this->add_control(
            'template',
            [
                'label' => __( 'Template', 'postgallery' ),
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
            'pgimgsource',
            [
                'label' => __( 'Image-Source', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => $selectPosts,
                'selectors' => [],
            ]
        );

        $elementIds = [ 0 => 'none' ];
        if (filter_has_var( INPUT_GET, 'post' ) ) {
            $document = Plugin::$instance->documents->get_doc_for_frontend( filter_input( INPUT_GET, 'post' ) );
            $data = $document->get_elements_data();
            $filteredElements = $this->searchElements( $data, 'media-carousel' );

            foreach ( $filteredElements as $element ) {
                $elementIds[$element['id']] = $element['id'];
            }
        }

        $imageSizes = [
            0 => __( 'Custom' ),
            'srcset' => __( 'Responsive (srcset)', 'postgallery' ),
        ];

        foreach ( Group_Control_Image_Size::get_all_image_sizes() as $name => $size ) {
            $key = $size['width'] . 'x' . $size['height'];
            $label = ucfirst( $name ) . ' (' . $key . ')';
            $imageSizes[$key] = $label;
        }

        $this->add_control(
            'imageSize',
            [
                'label' => __( 'Image-Size', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => $imageSizes,
                'selectors' => [],
            ]
        );

        $this->add_control(
            'pgthumbwidth',
            [
                'label' => __( 'Thumb width', 'postgallery' ),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                'selectors' => [],
                'placeholder' => PostGalleryPublic::getInstance()->option( 'thumbWidth' ),
            ]
        );
        $this->add_control(
            'pgthumbheight',
            [
                'label' => __( 'Thumb height', 'postgallery' ),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                'selectors' => [],
                'placeholder' => PostGalleryPublic::getInstance()->option( 'thumbHeight' ),
            ]
        );

        $this->add_control(
            'imageViewportWidth',
            [
                'label' => __( 'Image width in viewport', 'postgallery' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 800,
                'selectors' => [],
                'condition' => [
                    'imageSize' => 'srcset',
                ],
            ]
        );

        $this->add_control(
            'pgthumbscale',
            [
                'label' => __( 'Thumb scale', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'selectors' => [],
                'options' => [
                    '0' => __( 'crop', 'postgallery' ),
                    '1' => __( 'long edge', 'postgallery' ),
                    '2' => __( 'short edge', 'postgallery' ),
                    '3' => __( 'ignore proportions', 'postgallery' ),
                ],
            ]
        );

        $this->add_control(
            'pgmaxthumbs',
            [
                'label' => __( 'Max. images amount', 'postgallery' ),
                'type' => Controls_Manager::NUMBER,
                'default' => '',
                /*'selectors' => [
                    '{{WRAPPER}} .gallery a:nth-child(n+{{VALUE}})' => 'display: none;'
                ],*/
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
            'pgimages',
            [
                'label' => __( 'PostGallery Images', 'postgallery' ),
                'type' => 'postgallerycontrol',
            ]
        );
        $this->end_controls_section();


        $this->start_controls_section(
            'section_postgallery_style',
            [
                'label' => __( 'Appearance', 'postgallery' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __( 'Columns', 'postgallery' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
                'max' => 24,
                'selectors' => [
                    // for future: '{{WRAPPER}} .gallery' => 'grid-template-columns: repeat({{VALUE}}, 1fr);-ms-grid-columns: (1fr)[{{VALUE}}];',
                    '{{WRAPPER}} .gallery .item' => 'width: calc(100% / {{VALUE}});',
                    '{{WRAPPER}} .with-css-masonry .gallery' => 'column-count: {{VALUE}};',
                    '{{WRAPPER}} .with-css-masonry .gallery .item' => 'width: 100%;',
                ],
                'conditions' => [
                    'terms' =>
                        [ [
                            'name' => 'no_grid',
                            'operator' => '!in',
                            'value' => [ 'on' ],
                        ] ],
                ],
            ]
        );

        $this->add_control(
            'no_grid',
            [
                'label' => __( 'No Grid', 'postgallery' ),
                'type' => Controls_Manager::SWITCHER,
                'selectors' => [
                    '{{WRAPPER}} .gallery' => 'display: block;',
                    '{{WRAPPER}} .gallery .item' => 'display: inline-block; width: auto;',
                    '{{WRAPPER}} .gallery .item img' => 'width: auto;',

                ],
                'return_value' => 'on',
                'conditions' => [
                    'terms' =>
                        [ [
                            'name' => 'equal_height',
                            'operator' => '!in',
                            'value' => [ 'on' ],
                        ] ],
                ],
            ]
        );
        $this->add_control(
            'pgelementorlitebox',
            [
                'label' => __( 'Use Elementor-Litebox', 'postgallery' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'return_value' => 'on',
            ]
        );

        $this->add_control(
            'connectedWith',
            [
                'label' => __( 'Connected with', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => $elementIds,
                'selectors' => [],
            ]
        );

        $this->add_control(
            'masonry',
            [
                'label' => __( 'Masonry', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => [
                    0 => __( 'off' ),
                    'on' => __( 'on' ),
                    'horizontal' => 'horizontal order',
                    'css' => 'CSS only',
                ],
                'selectors' => [],
            ]
        );


        $this->add_control(
            'equal_height',
            [
                'label' => __( 'Equal height', 'postgallery' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'return_value' => 'on',
                'conditions' => [
                    'terms' =>
                        [ [
                            'name' => 'imageSize',
                            'operator' => '!in',
                            'value' => [ 'srcset' ],
                        ] ],
                ],
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
                    '{{WRAPPER}} .item .bg-image' => 'padding-bottom: calc( {{SIZE}} * 100% )',
                    '{{WRAPPER}}:after' => 'content: "{{SIZE}}"; position: absolute; color: transparent;',
                ],
                'condition' => [
                    'equal_height' => 'on',
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name' => 'css_filters',
                'selector' => '{{WRAPPER}} .item .bg-image, {{WRAPPER}} .item img',
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'section_postgallery_style_borders',
            [
                'label' => __( 'Margin & Borders', 'postgallery' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __( 'Columns Gap', 'postgallery' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'rem' ],
                'selectors' => [ // cant use column-gap here, cause of masonry
                    '{{WRAPPER}} .elementor-image-gallery' => 'margin-left: calc(-{{SIZE}}{{UNIT}} / 2);margin-right: calc(-{{SIZE}}{{UNIT}} / 2);',
                    '{{WRAPPER}} .elementor-image-gallery .item' => 'padding-left: calc({{SIZE}}{{UNIT}} / 2);padding-right: calc({{SIZE}}{{UNIT}} / 2);',
                ],
            ]
        );

        $this->add_responsive_control(
            'row_gap',
            [
                'label' => __( 'Rows Gap', 'postgallery' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'rem' ],
                'selectors' => [ // cant use row-gap here, cause of masonry
                    '{{WRAPPER}} .elementor-image-gallery .item' => 'padding-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} .item img, {{WRAPPER}} .item .bg-image',
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
                    '{{WRAPPER}} .item img, {{WRAPPER}} .item .bg-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'section_postgallery_animation',
            [
                'label' => __( 'Image Animation', 'postgallery' ),
                'tab' => Controls_Manager::TAB_ADVANCED,
            ]
        );

        $this->add_control(
            'imageAnimation',
            [
                'label' => __( 'Image Animation', 'postgallery' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'return_value' => 'on',
            ]
        );

        $this->add_control(
            'imageAnimationDuration',
            [
                'label' => __( 'Animation Duration', 'postgallery' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 300,
                'condition' => [
                    'imageAnimation' => 'on',
                ],
            ]
        );

        $this->add_control(
            'imageAnimationTimeBetween',
            [
                'label' => __( 'Time between images', 'postgallery' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 200,
                'condition' => [
                    'imageAnimation' => 'on',
                ],
            ]
        );

        $this->add_control(
            'imageAnimationDelay',
            [
                'label' => __( 'Initial delay', 'postgallery' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'condition' => [
                    'imageAnimation' => 'on',
                ],
            ]
        );

        $this->add_control(
            'imageAnimationCss',
            [
                'label' => __( 'Custom-CSS for Image', 'postgallery' ),
                'type' => Controls_Manager::CODE,
                'default' => '',
                'condition' => [
                    'imageAnimation' => 'on',
                ],
            ]
        );

        $this->add_control(
            'imageAnimationCssAnimated',
            [
                'label' => __( 'Custom-CSS for animated Image', 'postgallery' ),
                'type' => Controls_Manager::CODE,
                'default' => '',
                'condition' => [
                    'imageAnimation' => 'on',
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

        if ( !empty( $pgInstance->option( 'disableScripts' ) ) || empty( $pgInstance->option( 'enableLitebox' ) ) ) {
            $settings['pgelementorlitebox'] = 'on';
        }

        $args = $this->createArgs( $settings );
        $args['id'] = $this->get_id();

        // get gallery
        $loadFrom = $settings['pgimgsource'];
        if ( empty( $loadFrom ) ) {
            $loadFrom = get_the_ID();
        }

        $GLOBALS['PgIsElementorWidget'] = true;
        $gallery = $pgInstance->returnGalleryHtml( $settings['template'], $loadFrom, $args );

        if ( !empty( $args['connectedWith'] ) ) {
            // no litebox, connect with media-carousel
            $gallery = str_replace( '<a ', '<a class="no-litebox" data-elementor-open-lightbox="no" ', $gallery );

        } else if ( !empty( $settings['pgelementorlitebox'] ) && $settings['pgelementorlitebox'] == 'on' ) {
            // use elementor litebox
            $gallery = str_replace( '<a ', '<a class="no-litebox" data-elementor-lightbox-slideshow="' . $this->get_id() . '" ', $gallery );
        } else {
            // use postgallery litebox
            $gallery = str_replace( '<a ', '<a data-elementor-open-lightbox="no" ', $gallery );
        }
        $GLOBALS['PgIsElementorWidget'] = false;

        // echo gallery
        echo $gallery;
    }

    /**
     *  Create widget-args for rendering
     *
     * @param $settings
     * @return array
     */
    private function createArgs( $settings ) {
        $args = [];
        // create args
        if ( !empty( $settings['imageSize'] ) ) {
            if ( $settings['imageSize'] == 'srcset' ) {
                $args['useSrcset'] = true;
            } else {
                $sizes = explode( 'x', $settings['imageSize'] );
                $args['thumbWidth'] = $sizes[0];
                $args['thumbHeight'] = $sizes[1];
            }
        } else {

            if ( !empty( $settings['pgthumbwidth'] ) ) {
                $args['thumbWidth'] = $settings['pgthumbwidth'];
            }

            if ( !empty( $settings['pgthumbheight'] ) ) {
                $args['thumbHeight'] = $settings['pgthumbheight'];
            }
        }

        if ( isset( $settings['pgthumbscale'] ) ) {
            $args['thumbScale'] = $settings['pgthumbscale'];
        }

        if ( isset( $settings['imageViewportWidth'] ) ) {
            $args['imageViewportWidth'] = $settings['imageViewportWidth'];
        }

        if ( isset( $settings['columns'] ) ) {
            $args['columns'] = $settings['columns'];
        }

        if ( isset( $settings['template'] ) ) {
            $args['globalTemplate'] = $settings['template'];
        } else {
            $args['template'] = '';
        }

        if ( isset( $settings['masonry'] ) ) {
            $args['masonry'] = $settings['masonry'];
        }

        if ( isset( $settings['pgmaxthumbs'] ) ) {
            $args['pgmaxthumbs'] = $settings['pgmaxthumbs'];
        }

        if ( isset( $settings['equal_height'] ) ) {
            $args['equalHeight'] = $settings['equal_height'];
        }

        if ( isset( $settings['item_ratio'] ) ) {
            $args['itemRatio'] = $settings['item_ratio'];
        }

        if ( isset( $settings['imageAnimation'] ) ) {
            $args['imageAnimation'] = $settings['imageAnimation'];
            $args['imageAnimationTimeBetween'] = $settings['imageAnimationTimeBetween'];
        }

        if ( !empty( $settings['imageAnimationCss'] ) ) {
            $args['imageAnimationCss'] = $settings['imageAnimationCss'];
        }

        if ( !empty( $settings['imageAnimationCssAnimated'] ) ) {
            $args['imageAnimationCssAnimated'] = $settings['imageAnimationCssAnimated'];
        }

        if ( !empty( $settings['imageAnimationDelay'] ) ) {
            $args['imageAnimationDelay'] = $settings['imageAnimationDelay'];
        }

        $args['connectedWith'] = !empty( $settings['connectedWith'] ) ? $settings['connectedWith'] : false;

        $args['wrapperClass'] = ' elementor-image-gallery';

        return $args;
    }
}