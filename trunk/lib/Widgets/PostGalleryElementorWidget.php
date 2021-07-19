<?php

namespace Lib\Widgets;

use Admin\PostGalleryAdmin;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Modules\DynamicTags\Module;
use Elementor\Plugin;
use Elementor\Repeater;
use Elementor\Core\Schemes\Typography;
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
     * @return string Widget name.
     * @since 1.0.0
     *
     * @access public
     *
     */
    public function get_name() {
        return 'postgallery';
    }

    /**
     * Retrieve the widget title.
     *
     * @return string Widget title.
     * @since 1.0.0
     *
     * @access public
     *
     */
    public function get_title() {
        return __( 'PostGallery', 'postgallery' );
    }

    /**
     * Retrieve the widget icon.
     *
     * @return string Widget icon.
     * @since 1.0.0
     *
     * @access public
     *
     */
    public function get_icon() {
        return 'eicons eicon-gallery-masonry';
    }

    /**
     * Retrieve the list of categories the widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * Note that currently Elementor supports only one category.
     * When multiple categories passed, Elementor uses the first one.
     *
     * @return array Widget categories.
     * @since 1.0.0
     *
     * @access public
     *
     */
    public function get_categories() {
        return [ 'basic' ];
    }

    /**
     * Retrieve the list of scripts the widget depended on.
     *
     * Used to set scripts dependencies required to run the widget.
     *
     * @return array Widget scripts dependencies.
     * @since 1.0.0
     *
     * @access public
     *
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
            if ( !empty( $element['widgetType'] ) && $element['widgetType'] === $type ) {
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
            'pgimgsource',
            [
                'label' => __( 'Image-Source', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => $selectPosts,
                'selectors' => [],
            ]
        );

        $this->add_control(
            'pgimagesource_dynamic',
            [
                'label' => __( 'Extra-Image-Source', 'postgallery' ),
                'type' => Controls_Manager::GALLERY,
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        Module::GALLERY_CATEGORY,
                    ],
                ],
            ]
        );

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


        $this->_register_style_controls();
        $this->_register_border_controls();
        $this->_register_caption_controls();
        $this->_register_animation_controls();
        $this->_register_append_controls();
    }

    private function _register_style_controls() {
        $elementIds = [ 0 => 'none' ];
        if ( filter_has_var( INPUT_GET, 'post' ) ) {
            $document = Plugin::$instance->documents->get_doc_for_frontend( filter_input( INPUT_GET, 'post' ) );
            $data = $document->get_elements_data();
            $filteredElements = $this->searchElements( $data, 'media-carousel' );

            foreach ( $filteredElements as $element ) {
                $elementIds[$element['id']] = $element['id'];
            }
        }

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
                'default' => 3,
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
            ]
        );
        $this->add_control(
            'pgelementorlitebox',
            [
                'label' => __( 'Use Elementor-Litebox', 'postgallery' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'on',
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
                'default' => 'on',
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
    }

    private function _register_border_controls() {
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
    }

    /**
     * Register controls for captions
     */
    private function _register_caption_controls() {
        $metaSources = [
            'title' => __( 'Titel' ),
            'attachment_alt' => __( 'Alternative Text' ),
            'attachment_caption' => __( 'Caption' ),
            'content' => __( 'Content' ),
        ];


        $this->start_controls_section(
            'section_postgallery_caption',
            [
                'label' => __( 'Captions', 'elementor' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'showCaptions',
            [
                'label' => __( 'Show captions', 'postgallery' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'return_value' => 'on',
            ]
        );

        $this->add_control(
            'captionPosition',
            [
                'label' => __( 'Position', 'postgallery' ),
                'type' => Controls_Manager::CHOOSE,
                'label_block' => false,
                'toggle' => false,
                'default' => 'bottom',
                'options' => [
                    'top' => [
                        'title' => __( 'Top', 'elementor' ),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'bottom' => [
                        'title' => __( 'Bottom', 'elementor' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => '{{VALUE}}: 0;',
                ],
                'render_type' => 'ui',
            ]
        );

        $this->add_control(
            'captionSource',
            [
                'label' => __( 'Source', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'title',
                'options' => $metaSources,
            ]
        );


        $this->add_control(
            'pg_caption_sperator_style',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'pg_caption_headline_style',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Style', 'elementor' ),
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'pgcaption_typography',
                'label' => __( 'Typography', 'elementor' ),
                'scheme' => Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .item .caption-wrapper',
            ]
        );
        $this->add_control(
            'pgcaption_color',
            [
                'type' => Controls_Manager::COLOR,
                'label' => __( 'Color', 'elementor' ),
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => 'color: {{VALUE}};',
                ],
                'default' => '#ffffff',
            ]
        );
        $this->add_control(
            'pgcaption_align',
            [
                'type' => Controls_Manager::CHOOSE,
                'label' => __( 'Align', 'elementor-pro' ),
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => 'text-align: {{VALUE}};',
                ],
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'elementor' ),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'elementor' ),
                        'icon' => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'elementor' ),
                        'icon' => 'fa fa-align-right',
                    ],
                    'justify' => [
                        'title' => __( 'Justify', 'elementor' ),
                        'icon' => 'fa fa-align-justify',
                    ],
                ],
                'default' => 'center',
            ]
        );

        $this->add_control(
            'pgcaption_backgroundcolor',
            [
                'type' => Controls_Manager::COLOR,
                'label' => __( 'Background-Color', 'elementor-pro' ),
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => 'background-color: {{VALUE}};',
                ],
                'default' => 'rgba(0, 0, 0, 0.7)',
            ]
        );

        $this->add_control(
            'pg_caption_sperator_margin',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'pg_caption_headline_margin',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Margin & Borders', 'postgallery' ),
            ]
        );
        $this->add_control(
            'pgcaption_padding',
            [
                'label' => __( 'Padding', 'elementor-pro' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'rem' ],
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'pgcaption_margin',
            [
                'label' => __( 'Margin', 'elementor-pro' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'rem' ],
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'pgcaption_border',
                'selector' => '{{WRAPPER}} .item .caption-wrapper',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'pgcaption_border_radius',
            [
                'label' => __( 'Border Radius', 'elementor' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
            'pg_caption_sperator_animation',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'pg_caption_headline_animation',
            [
                'type' => Controls_Manager::HEADING,
                'label' => __( 'Animation', 'elementor' ),
            ]
        );

        $this->add_control(
            'pgcaption_animation',
            [
                'label' => __( 'Animation', 'elementor-pro' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'return_value' => 'on',
            ]
        );

        $this->add_control(
            'pgcaption_animation_nonhover',
            [
                'label' => __( 'Custom-CSS for non hover', 'postgallery' ),
                'type' => Controls_Manager::CODE,
                'default' => '',
                'condition' => [
                    'pgcaption_animation' => 'on',
                ],
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => '{{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'pgcaption_animation_hover',
            [
                'label' => __( 'Custom-CSS for hover', 'postgallery' ),
                'type' => Controls_Manager::CODE,
                'default' => '',
                'condition' => [
                    'pgcaption_animation' => 'on',
                ],
                'selectors' => [
                    '{{WRAPPER}} .item:hover .caption-wrapper' => '{{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'pgcaption_animation_duration',
            [
                'label' => __( 'Animation Duration', 'postgallery' ) . ' (ms)',
                'type' => Controls_Manager::NUMBER,
                'default' => '300',
                'selectors' => [
                    '{{WRAPPER}} .item .caption-wrapper' => 'transition-duration: {{VALUE}}ms;',
                ],
                'condition' => [
                    'pgcaption_animation' => 'on',
                ],
            ]
        );

        $this->add_control(
            'pgcaption_overflow_hidden',
            [
                'label' => __( 'Overflow hidden', 'postgallery' ),
                'type' => Controls_Manager::SWITCHER,
                'default' => '',
                'return_value' => 'hidden',
                'selectors' => [
                    '{{WRAPPER}} .item .inner' => 'overflow: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function _register_animation_controls() {
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
     * Register controls to append templates
     */
    private function _register_append_controls() {
        $this->start_controls_section(
            'section_postgallery_append',
            [
                'label' => __( 'PostGallery Append', 'postgallery' ),
                'tab' => Controls_Manager::TAB_ADVANCED,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'template_to_append',
            [
                'label' => __( 'Template to append', 'elegesamt' ),
                'type' => Controls_Manager::SELECT,
                'default' => 0,
                'options' => $this->getTemplateList(),
            ]
        );

        $repeater->add_control(
            'position_to_append',
            [
                'label' => __( 'At position', 'elegesamt' ),
                'type' => Controls_Manager::NUMBER,
                'default' => 1,
                'min' => 1,
            ]
        );


        $this->add_control(
            'append_templates',
            [
                'label' => __( 'Append Templates', 'postgallery' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
            ]
        );

        $this->end_controls_section();
    }


    /**
     * Returns template lists for elementor-templates
     *
     * @return array
     */
    private function getTemplateList(): array {
        if ( !filter_has_var( INPUT_GET, 'post' ) ) {
            // prevents calling query in frontend
            return [];
        }
        $templateList = [];

        $templatePosts = get_posts( [
            'post_type' => 'elementor_library',
            'numberposts' => -1,
        ] );

        foreach ( $templatePosts as $templatePost ) {
            $templateList[$templatePost->ID] = $templatePost->post_title;
        }

        return $templateList;
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
        $srcDynamic = $this->get_settings_for_display( 'pgimagesource_dynamic' );
        $settings = $GLOBALS['elementorWidgetSettings'] = $this->get_settings();
        $settings['pgimagesource_dynamic'] = $srcDynamic;
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
        $html = $pgInstance->returnGalleryHtml( $args['template'], $loadFrom, $args );
        $html = $this->setLitebox( $html, $args );

        $GLOBALS['PgIsElementorWidget'] = false;

        // echo gallery html
        echo $html;
    }

    /**
     * Choose which litebox will be used
     *
     * @param string $html
     * @param array $args
     *
     * @return string
     */
    private function setLitebox( $html, $args ) {
        if ( !empty( $args['connectedWith'] ) ) {
            // no litebox, connect with media-carousel
            $html = str_replace( '<a ', '<a class="no-litebox" data-elementor-open-lightbox="no" ', $html );

        } else if ( !empty( $args['pgelementorlitebox'] ) && $args['pgelementorlitebox'] == 'on' ) {
            // use elementor litebox
            $html = str_replace( '<a ', '<a class="no-litebox" data-elementor-lightbox-slideshow="' . $this->get_id() . '" ', $html );
        } else {
            // use postgallery litebox
            $html = str_replace( '<a ', '<a data-elementor-open-lightbox="no" ', $html );
        }

        return $html;
    }

    /**
     *  Create widget-args for rendering
     *
     * @param $settings
     * @return array
     */
    private function createArgs( $settings ) {
        $args = $settings;
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

        if ( isset( $settings['template'] ) ) {
            $args['globalTemplate'] = $settings['template'];
        } else {
            $args['template'] = '';
        }

        $args['equalHeight'] = false;
        if ( !empty( $settings['equal_height'] ) ) {
            $args['equalHeight'] = true;
        }

        if ( isset( $settings['item_ratio'] ) ) {
            $args['itemRatio'] = $settings['item_ratio'];
        }

        $args['wrapperClass'] = ' elementor-image-gallery';
        $args['template'] = 'thumbs';

        return $args;
    }
}