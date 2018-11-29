<?php

/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */
class PostGalleryThemeCustomizer {
    private $sectionId;
    private $fields;
    private $postgalleryAdmin;
    private $postgallery;

    public function __construct() {
        $id = 'postgallery';
        $this->sectionId = $id;

        $this->postgalleryAdmin = \Admin\PostGalleryAdmin::getInstance();

        // slide animations from animate.css
        $sliderAnimations = explode( ',', 'bounce,	flash,	pulse,	rubberBand,
shake,	headShake,	swing,	tada,
wobble,	jello,	bounceIn,	bounceInDown,
bounceInLeft,	bounceInRight,	bounceInUp,	bounceOut,
bounceOutDown,	bounceOutLeft,	bounceOutRight,	bounceOutUp,
fadeIn,	fadeInDown,	fadeInDownBig,	fadeInLeft,
fadeInLeftBig,	fadeInRight,	fadeInRightBig,	fadeInUp,
fadeInUpBig,	fadeOut,	fadeOutDown,	fadeOutDownBig,
fadeOutLeft,	fadeOutLeftBig,	fadeOutRight,	fadeOutRightBig,
fadeOutUp,	fadeOutUpBig,	flipInX,	flipInY,
flipOutX,	flipOutY,	lightSpeedIn,	lightSpeedOut,
rotateIn,	rotateInDownLeft,	rotateInDownRight,	rotateInUpLeft,
rotateInUpRight,	rotateOut,	rotateOutDownLeft,	rotateOutDownRight,
rotateOutUpLeft,	rotateOutUpRight,	hinge,	jackInTheBox,
rollIn,	rollOut,	zoomIn,	zoomInDown,
zoomInLeft,	zoomInRight,	zoomInUp,	zoomOut,
zoomOutDown,	zoomOutLeft,	zoomOutRight,	zoomOutUp,
slideInDown,	slideInLeft,	slideInRight,	slideInUp,
slideOutDown,	slideOutLeft,	slideOutRight,	slideOutUp' );
        array_unshift( $sliderAnimations, '' );

        // need as key-value pair
        $sliderAnimationsKeyValue = [];
        foreach ( $sliderAnimations as $value ) {
            $sliderAnimationsKeyValue[trim( $value )] = trim( $value );
        }
        $sliderAnimations = $sliderAnimationsKeyValue;


        $this->fields = [];

        $this->fields['postgallery-base'] =
            [
                'title' => 'Main-Settings',
                'fields' => [
                    'postgalleryDebugmode' => [
                        'type' => 'checkbox',
                        'label' => __( 'Debug-Mode', 'postgallery' ),
                        'default' => false,
                    ],
                    'sliderType' => [
                        'type' => 'select',
                        'label' => __( 'Slider-Type', 'postgallery' ),
                        'choices' => [
                            'owl' => 'OWL Carousel 2.x',
                            'owl1' => 'OWL Carousel 1.3',
                            'swiper' => 'Swiper (experimental)',
                        ],
                        'default' => 'owl',
                    ],

                    'globalPosition' => [
                        'label' => __( 'Global position', 'postgallery' ),
                        'type' => 'select',
                        'choices' => [
                            'bottom' => __( 'bottom', 'postgallery' ),
                            'top' => __( 'top', 'postgallery' ),
                            'custom' => __( 'custom', 'postgallery' ),
                        ],
                        'default' => defined( 'ELEMENTOR_VERSION' ) ? 'custom' : 'bottom',
                    ],
                    'disableScripts' => [
                        'type' => 'checkbox',
                        'label' => __( 'Disable scripts loading', 'postgallery' ),
                        'default' => false,
                        'description' => 'Will disable litebox and slider',
                    ],
                    'disableGroupedMedia' => [
                        'type' => 'checkbox',
                        'label' => __( 'Disable grouped media', 'postgallery' ),
                        'default' => false,
                    ],
                ],
            ];

        $this->fields['postgallery-templateSettings'] =
            [
                'title' => 'Template-Settings',
                'fields' => [
                    'globalTemplate' => [
                        'label' => __( 'Global template', 'postgallery' ),
                        'type' => 'select',
                        'choices' => array_merge(
                            $this->postgalleryAdmin->getCustomTemplates(),
                            $this->postgalleryAdmin->defaultTemplates
                        ),
                    ],

                    'columns' => [
                        'label' => __( 'Columns', 'postgallery' ),
                        'type' => 'select',
                        'choices' => [
                            'auto' => 'Auto',
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
                            '11' => '11',
                            '12' => '12',
                        ],
                        'default' => 'auto',
                    ],

                    'thumbWidth' => [
                        'label' => __( 'Thumb width', 'postgallery' ),
                        'type' => 'text',
                        'default' => 150,
                    ],

                    'thumbHeight' => [
                        'label' => __( 'Thumb height', 'postgallery' ),
                        'type' => 'text',
                        'default' => 150,
                    ],
                    'thumbScale' => [
                        'label' => __( 'Thumb scale', 'postgallery' ),
                        'type' => 'select',
                        'default' => '1',
                        'choices' => [
                            '0' => __( 'crop', 'postgallery' ),
                            '1' => __( 'long edge', 'postgallery' ),
                            '2' => __( 'short edge', 'postgallery' ),
                            '3' => __( 'ignore proportions', 'postgallery' ),
                        ],
                    ],

                    'sliderOwlConfig' => [
                        'type' => 'textarea',
                        'label' => __( 'Owl-Slider-Config (for Slider-Template)', 'postgallery' ),
                        'default' => "items: 1,\nnav: 1,\ndots: 1,\nloop: 1,",
                    ],


                    'stretchImages' => [
                        'label' => __( 'Stretch small images (for watermark)', 'postgallery' ),
                        'type' => 'checkbox',
                    ],
                ],
            ];

        $this->fields['postgallery-liteboxAnimation'] = [
            'title' => 'Animation',

            'fields' => [
                'slideSpeed' => [
                    'id' => 'slideSpeed',
                    'label' => 'Speed (ms)',
                    'type' => 'number',
                    'datasrc' => 'moduldata',
                    //'tooltip' => 'Gibt an wie lange die Animation eines Slides dauert.'
                ],

                'autoplay' => [
                    'id' => 'autoplay',
                    'label' => 'Autoplay',
                    'type' => 'checkbox',
                    'datasrc' => 'moduldata',
                    //'description' => 'Slider wechselt automatisch die Bilder.',
                ],
                'autoplayTimeout' => [
                    'id' => 'autoplayTimeout',
                    'label' => 'Autoplay timeout (ms)',
                    'type' => 'number',
                    'placeholder' => 5000,
                    'datasrc' => 'moduldata',
                    //'description' => 'Gibt an wie lange ein Item angezeigt wird und bis die nächste Animation beginnt.'
                ],
                'animateOut' => [
                    'id' => 'animateOut',
                    'label' => 'Animate out',
                    'type' => 'select',
                    'choices' => $sliderAnimations,
                    'datasrc' => 'moduldata',
                    //'description' => 'Gibt die Animation an mit welcher ein Item ausgeblendet wird',
                ],

                'animateIn' => [
                    'id' => 'animateIn',
                    'label' => 'Einblend-Animation (animateIn)',
                    'type' => 'select',
                    'choices' => $sliderAnimations,
                    'datasrc' => 'moduldata',
                    //'description' => 'Gibt die Animation an mit welcher ein Item eingeblendet wird<br />'
                    //.'Look <a target="_blank" href="https://daneden.github.io/animate.css/">Animate.css</a>',
                ],
            ],
        ];

        $this->fields['postgallery-liteboxSettings'] =
            [
                'title' => 'Litebox-Settings',
                'fields' => [
                    'enableLitebox' => [
                        'type' => 'checkbox',
                        'label' => __( 'Enable', 'postgallery' ) . ' Litebox',
                        'default' => true,
                    ],
                    'liteboxTemplate' => [
                        'type' => 'select',
                        'default' => 'default',
                        'label' => __( 'Litebox-Template', 'postgallery' ),
                        'choices' => $this->postgalleryAdmin->getLiteboxTemplates(),
                    ],

                    'owlTheme' => [
                        'type' => 'text',
                        'default' => 'default',
                        'label' => __( 'Owl-Theme', 'postgallery' ),
                        'input_attrs' => [ 'list' => 'postgallery-owl-theme' ],
                        'description' => '<datalist id="postgallery-owl-theme"><option>default</option><option>green</option></datalist>',
                    ],
                    'clickEvents' => [
                        'type' => 'checkbox',
                        'label' => __( 'Enable Click-Events', 'postgallery' ),
                        'default' => true,
                    ],
                    'keyEvents' => [
                        'type' => 'checkbox',
                        'label' => __( 'Enable Keypress-Events', 'postgallery' ),
                        'default' => true,
                    ],
                    'arrows' => [
                        'type' => 'checkbox',
                        'label' => __( 'Show arrows', 'postgallery' ),
                        'default' => false,
                    ],
                    'asBg' => [
                        'type' => 'checkbox',
                        'label' => __( 'Images as Background', 'postgallery' ),
                        'default' => false,
                    ],

                    'items' => [
                        'id' => 'items',
                        'label' => 'Items',
                        'type' => 'number',
                        'default' => 1,
                    ],

                    'mainColor' => [
                        'type' => 'text',
                        'label' => __( 'Main-Color', 'postgallery' ),
                        'default' => '#fff',
                    ],
                    'secondColor' => [
                        'type' => 'text',
                        'label' => __( 'Second-Color', 'postgallery' ),
                        'default' => '#333',
                    ],

                    'owlConfig' => [
                        'type' => 'textarea',
                        'label' => __( 'Owl-Litebox-Config', 'postgallery' ),
                        /*'description' => '<b>' . __( 'Presets', 'postgallery' ) . '</b>:'
                            . '<select class="owl-slider-presets">
                                <option value="">Slide (' . __( 'Default', 'postgallery' ) . ')</option>
                                <option value="fade">Fade</option>
                                <option value="slidevertical">SlideVertical</option>
                                <option value="zoominout">Zoom In/out</option>
                                </select>',*/
                        'default' => '',
                    ],

                    'owlThumbConfig' => [
                        'type' => 'textarea',
                        'label' => __( 'Owl-Config for Thumbnail-Slider', 'postgallery' ),
                        'description' => '<b>' . __( 'Presets', 'postgallery' ) . '</b>:'
                            . '<select class="owl-slider-presets">
                                <option value="">Slide (' . __( 'Default', 'postgallery' ) . ')</option>
                                <option value="fade">Fade</option>
                                <option value="slidevertical">SlideVertical</option>
                                <option value="zoominout">Zoom In/out</option>
                                </select>',
                    ],

                    'owlDesc' => [
                        'type' => 'hidden',
                        'label' => __( 'Description', 'postgallery' ),
                        'description' => __( 'You can use these options', 'postgallery' ) . ':<br />' .
                            '<a href="https://owlcarousel2.github.io/OwlCarousel2/docs/api-options.html" target="_blank">
							OwlCarousel Options
						</a>
						<br />' .
                            __( 'You can use these animations', 'postgallery' ) . ':<br />
						<a href="http://daneden.github.io/animate.css/" target="_blank">
							Animate.css
						</a>
					</div>',
                    ],
                ],
            ];
    }

    public function actionCustomizeRegister( $wp_customize ) {
        $prefix = 'postgallery_';
        $wp_customize->add_panel( 'postgallery-panel', [
            'title' => __( 'PostGallery' ),
            'section' => 'postgallery',
        ] );


        foreach ( $this->fields as $sectionId => $section ) {
            $wp_customize->add_section( $sectionId, [
                'title' => __( $section['title'], 'postgallery' ),
                'panel' => 'postgallery-panel',
            ] );

            foreach ( $section['fields'] as $fieldId => $field ) {
                $settingId = $prefix . ( !is_numeric( $fieldId ) ? $fieldId : $field['id'] );
                $controlId = $settingId . '-control';

                $wp_customize->add_setting( $settingId, [
                    'default' => !empty( $field['default'] ) ? $field['default'] : '',
                    'transport' => !empty( $field['transport'] ) ? $field['transport'] : 'refresh',
                ] );

                $wp_customize->add_control( $controlId, [
                    'label' => __( $field['label'], 'postgallery' ),
                    'section' => $sectionId,
                    'type' => !empty( $field['type'] ) ? $field['type'] : 'text',
                    'settings' => $settingId,
                    'description' => !empty( $field['description'] ) ? __( $field['description'], 'postgallery' ) : '',
                    'choices' => !empty( $field['choices'] ) ? $field['choices'] : null,
                    'input_attrs' => !empty( $field['input_attrs'] ) ? $field['input_attrs'] : null,
                ] );
            }
        }
    }
}

/*if( class_exists( 'WP_Customize_Control' ) ) {
    class WP_Customize_Headline_Control extends WP_Customize_Control {
        public $type = 'headline';

        public function render_content() {
            echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
        }
    }
}*/