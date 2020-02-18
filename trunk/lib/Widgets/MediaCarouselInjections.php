<?php

namespace Lib\Widgets;

use Elementor\Controls_Manager;
use ElementorPro\Modules\Carousel\Widgets\Media_Carousel;


trait MediaCarouselInjections {

    /**
     * Remove empty slides in frontend
     *
     * @param $settings
     */
    protected function removeEmptySlides( &$settings, $force = false ) {
        if ( !$force && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            return;
        }

        foreach ( $settings['slides'] as $key => $slide ) {
            if ( empty( $slide['isGesamt'] )
                && ( empty( $slide['image'] ) || empty( $slide['image']['id'] ) )
                && empty( $slide['video'] )
            ) {
                unset( $settings['slides'][$key] );
            }
        }
    }

    /**
     * Add controls for auto-height and ratio
     */
    protected function addInjections() {

        $this->start_injection( [
            'type' => 'section',
            'at' => 'end',
            'of' => 'section_slides',
        ] );

        $this->add_control(
            'autoHeight',
            [
                'type' => Controls_Manager::SWITCHER,
                'label' => __( 'Auto-Height', 'elegesamt' ),
                'default' => 'yes',
                'position' => 'section_additional_options',
                'prefix_class' => 'elementor-autoheight-',
                'render_type' => 'template',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'useAspectRatio',
            [
                'type' => Controls_Manager::SWITCHER,
                'label' => __( 'Use aspect-ratio', 'elegesamt' ),
                'default' => 'no',
                'position' => 'section_additional_options',
                'condition' => [
                    'autoHeight!' => 'yes',
                ],
                'prefix_class' => 'elementor-aspect-ratio-',
                'render_type' => 'template',
                'frontend_available' => true,
                'selectors' => [
                    '{{WRAPPER}}.elementor-aspect-ratio-yes .swiper-wrapper' => 'position: absolute; top: 0; left: 0;',
                    '{{WRAPPER}}.elementor-aspect-ratio-yes .swiper-container' => 'position: relative; height: 0;',
                ],
            ]
        );

        $this->add_control(
            'aspectRatio',
            [
                'label' => __( 'Aspect Ratio', 'elementor-pro' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 0.66,
                ],
                'range' => [
                    'px' => [
                        'min' => 0.1,
                        'max' => 5,
                        'step' => 0.01,
                    ],
                ],
                'condition' => [
                    'useAspectRatio' => 'yes',
                ],
                'selectors' => [
                    '{{WRAPPER}}.elementor-aspect-ratio-yes .swiper-container' => 'padding-bottom: calc( {{SIZE}} * 100% )',
                ],
            ]
        );


        $this->add_control(
            'arrowOffset',
            [
                'label' => __( 'Arrow Offset', 'elementor-pro' ),
                'type' => Controls_Manager::SLIDER,
                'default' => [
                    'size' => 10,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-swiper-button-prev' => 'left: {{SIZE}}px',
                    '{{WRAPPER}} .elementor-swiper-button-next' => 'right: {{SIZE}}px',
                ],
            ]
        );

        $this->add_control(
            'verticalAlign',
            [
                'label' => __( 'Vertical-Align', 'elegesamt' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .swiper-wrapper' => 'align-items: {{VALUE}};',
                ],
                'options' => [
                    'center' => __( 'Center', 'elegesamt' ),
                    'normal' => __( 'Top', 'elegesamt' ),
                    'end' => __( 'Bottom', 'elegesamt' ),
                ],
                'condition' => [
                    'autoHeight' => 'yes',
                ],
            ]
        );

        $this->end_injection();


        $this->start_injection( [
            'type' => 'section',
            'at' => 'end',
            'of' => 'section_additional_options',
        ] );


        $this->end_injection();

        $this->add_control(
            'overlayAlign',
            [
                'label' => __( 'Overlay-Align', 'elegesamt' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'top',
                'selectors' => [
                    '{{WRAPPER}} .elementor-carousel-image-overlay' => 'top:auto;bottom:auto; {{VALUE}}: 0;',
                ],
                'options' => [
                    'top' => __( 'Top', 'elegesamt' ),
                    'bottom' => __( 'Bottom', 'elegesamt' ),
                ],
            ],
            [
                'position' => [ 'of' => 'caption' ],
            ]
        );


        $this->update_responsive_control( 'height', [
            'condition' => [
                'autoHeight!' => 'yes',
                'aspectRatio!' => 'yes',
            ],
        ] );

        $this->update_responsive_control( 'width', [
            'range' => [
                'px' => [
                    'min' => 100,
                    'max' => 1140,
                ],
                '%' => [
                    'min' => 1,
                ],
            ],
        ] );
    }

    /**
     * Override output for single image
     *  Add img-tag for auto-height
     *
     * @param array $slide
     * @param $element_key
     * @param array $settings
     */
    protected function print_slide_image( array $slide, $element_key, array $settings ) {
        $this->add_render_attribute( $element_key . '-image', 'class', 'media-carousel-autoheight', false );
        ?>
        <div <?php echo $this->get_render_attribute_string( $element_key . '-image' ); ?>>
            <?php if ( 'video' === $slide['type'] && $settings['video_play_icon'] ) : ?>
                <div class="elementor-custom-embed-play">
                    <i class="eicon-play" aria-hidden="true"></i>
                    <span class="elementor-screen-only"><?php _e( 'Play', 'elementor-pro' ); ?></span>
                </div>
            <?php endif; ?>
            <img src="<?php echo $slide['image']['url']; ?>" alt="" class="media-carousel-img"/>
        </div>
        <?php if ( $settings['overlay'] ) : ?>
            <div <?php echo $this->get_render_attribute_string( 'image-overlay' ); ?>>
                <?php if ( 'text' === $settings['overlay'] ) : ?>
                    <?php echo $this->get_image_caption( $slide ); ?>
                <?php else : ?>
                    <i class="fa fa-<?php echo $settings['icon']; ?>"></i>
                <?php endif; ?>
            </div>
        <?php
        endif;
    }
}