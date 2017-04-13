<?php

/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */
class PostGalleryThemeCustomizer {
    private $sectionId;
    private $textdomain;
    private $fields;
    private $postgalleryAdmin;

    public function __construct() {
        $id = 'postgallery';
        $this->textdomain = 'post-gallery';
        $this->sectionId = $id;

        $this->postgalleryAdmin = \Admin\PostGalleryAdmin::getInstance();

        $this->fields = array();

        $this->fields['postgallery-base'] =
            array(
                'title' => 'Main-Settings',
                'fields' => array(
                    'debugmode' => array(
                        'type' => 'checkbox',
                        'label' => __( 'Debug-Mode', $this->textdomain ),
                        'default' => false,
                    ),
                    'sliderType' => array(
                        'type' => 'select',
                        'label' => __( 'Slider-Type', $this->textdomain ),
                        'choices' => array(
                            'owl' => 'OWL Carousel 2.x',
                            'owl1' => 'OWL Carousel 1.3',
                        ), // Todo: Add swyper,
                        'default' => 'owl',
                    ),

                    'globalPosition' => array(
                        'label' => __( 'Global position', $this->textdomain ),
                        'type' => 'select',
                        'choices' => array(
                            'bottom' => __( 'bottom', $this->textdomain ),
                            'top' => __( 'top', $this->textdomain ),
                            'custom' => __( 'custom', $this->textdomain ),
                        ),
                        'default' => 'bottom',
                    ),
                ),
            );

        $this->fields['postgallery-templateSettings'] =
            array(
                'title' => 'Template-Settings',
                'fields' => array(
                    'globalTemplate' => array(
                        'label' => __( 'Global template', $this->textdomain ),
                        'type' => 'select',
                        'choices' => array_merge(
                            $this->postgalleryAdmin->getCustomTemplates(),
                            $this->postgalleryAdmin->defaultTemplates
                        ),
                    ),

                    'thumbWidth' => array(
                        'label' => __( 'Thumb width', $this->textdomain ),
                        'type' => 'text',
                        'default' => 150,
                    ),

                    'thumbHeight' => array(
                        'label' => __( 'Thumb height', $this->textdomain ),
                        'type' => 'text',
                        'default' => 150,
                    ),
                    'thumbScale' => array(
                        'label' => __( 'Thumb scale', $this->textdomain ),
                        'type' => 'select',
                        'default' => '1',
                        'choices' => array(
                            '0' => __( 'crop', $this->textdomain ),
                            '1' => __( 'long edge', $this->textdomain ),
                            '2' => __( 'short edge', $this->textdomain ),
                            '3' => __( 'ignore proportions', $this->textdomain ),
                        ),
                        'use_key' => true,
                    ),

                    'sliderOwlConfig' => array(
                        'type' => 'textarea',
                        'label' => __( 'Owl-Slider-Config (for Slider-Template)', $this->textdomain ),
                        'default' => "items: 1,\nnav: 1,\ndots: 1,\nloop: 1,",
                    ),


                    'stretchImages' => array(
                        'label' => __( 'Stretch small images (for watermark)', $this->textdomain ),
                        'type' => 'checkbox',
                    ),
                ),
            );


        $this->fields['postgallery-liteboxSettings'] =
            array(
                'title' => 'Litebox-Settings',
                'fields' => array(
                    'enable' => array(
                        'type' => 'checkbox',
                        'label' => __( 'Enable', $this->textdomain ) . ' Litebox',
                        'default' => true,
                    ),
                    'liteboxTemplate' => array(
                        'type' => 'select',
                        'default' => 'default',
                        'label' => __( 'Litebox-Template', $this->textdomain ),
                        'choices' => $this->postgalleryAdmin->getLiteboxTemplates(),
                    ),

                    'owlTheme' => array(
                        'type' => 'text',
                        'default' => 'default',
                        'label' => __( 'Owl-Theme', $this->textdomain ),
                        'input_attrs' => array( 'list' => 'postgallery-owl-theme' ),
                        'description' => '<datalist id="postgallery-owl-theme"><option>default</option><option>green</option></datalist>',
                    ),
                    'clickEvents' => array(
                        'type' => 'checkbox',
                        'label' => __( 'Enable Click-Events', $this->textdomain ),
                        'default' => true,
                    ),
                    'keyEvents' => array(
                        'type' => 'checkbox',
                        'label' => __( 'Enable Keypress-Events', $this->textdomain ),
                        'default' => true,
                    ),
                    'asBg' => array(
                        'type' => 'checkbox',
                        'label' => __( 'Images as Background', $this->textdomain ),
                        'default' => false,
                    ),

                    'owlConfig' => array(
                        'type' => 'textarea',
                        'label' => __( 'Owl-Litebox-Config', $this->textdomain ),
                        'description' => '<b>' . __( 'Presets', $this->textdomain ) . '</b>:'
                            . '<select class="owl-slider-presets">
                                <option value="">Slide (' . __( 'Default', $this->textdomain ) . ')</option>
                                <option value="fade">Fade</option>
                                <option value="slidevertical">SlideVertical</option>
                                <option value="zoominout">Zoom In/out</option>
                                </select>',
                        'default' => 'items: 1,',
                    ),

                    'owlThumbConfig' => array(
                        'type' => 'textarea',
                        'label' => __( 'Owl-Config for Thumbnail-Slider', $this->textdomain ),
                        'description' => '<b>' . __( 'Presets', $this->textdomain ) . '</b>:'
                            . '<select class="owl-slider-presets">
                                <option value="">Slide (' . __( 'Default', $this->textdomain ) . ')</option>
                                <option value="fade">Fade</option>
                                <option value="slidevertical">SlideVertical</option>
                                <option value="zoominout">Zoom In/out</option>
                                </select>',
                    ),

                    'owlDesc' => array(
                        'type' => 'hidden',
                        'label' => __( 'Description', $this->textdomain ),
                        'description' => __( 'You can use these options', $this->textdomain ) . ':<br />' .
                            '<a href="https://owlcarousel2.github.io/OwlCarousel2/docs/api-options.html" target="_blank">
							OwlCarousel Options
						</a>
						<br />' .
                            __( 'You can use these animations', $this->textdomain ) . ':<br />
						<a href="http://daneden.github.io/animate.css/" target="_blank">
							Animate.css
						</a>
					</div>',
                    ),
                ),
            );
    }

    public function actionCustomizeRegister( $wp_customize ) {

        $wp_customize->add_panel( 'postgallery-panel', array(
            'title' => __( 'PostGallery' ),
            'section' => 'postgallery',
        ) );


        foreach ( $this->fields as $sectionId => $section ) {
            $wp_customize->add_section( $sectionId, array(
                'title' => __( $section['title'], $this->textdomain ),
                'panel' => 'postgallery-panel',
            ) );

            foreach ( $section['fields'] as $fieldId => $field ) {
                $settingId = !is_numeric( $fieldId ) ? $fieldId : $field['id'];
                $controlId = $settingId . '-control';

                $wp_customize->add_setting( $settingId, array(
                    'default' => !empty( $field['default'] ) ? $field['default'] : '',
                    'transport' => !empty( $field['transport'] ) ? $field['transport'] : 'refresh',
                ) );

                $wp_customize->add_control( $controlId, array(
                    'label' => __( $field['label'], $this->textdomain ),
                    'section' => $sectionId,
                    'type' => !empty( $field['type'] ) ? $field['type'] : 'text',
                    'settings' => $settingId,
                    'description' => !empty( $field['description'] ) ? __( $field['description'], $this->textdomain ) : '',
                    'choices' => !empty( $field['choices'] ) ? $field['choices'] : null,
                    'input_attrs' => !empty( $field['input_attrs'] ) ? $field['input_attrs'] : null,
                ) );
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