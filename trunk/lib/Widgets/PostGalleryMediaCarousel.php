<?php

namespace Lib\Widgets;

use Admin\PostGalleryAdmin;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Image_Size;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use ElementorPro\Modules\Carousel\Widgets\Media_Carousel;
use Lib\PostGallery;
use Lib\PostGalleryImageList;
use Lib\Widgets\MediaCarouselInjections;
use Pub\PostGalleryPublic;

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class PostGalleryMediaCarousel extends Media_Carousel {
    use MediaCarouselInjections;

    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => 'PostGallery',
            ]
        );
        PostGalleryMediaCarousel::addControls( $this );

        $this->end_controls_section();

        parent::_register_controls();

        MediaCarouselInjections::addInjections( $this );
    }

    /**
     * Add pics to slides
     *
     * @param $settings
     *
     * @return int $countImages
     */
    public static function addPostGalleryToSlides( &$settings ): int {
        if ( $settings['pgimgsource'] === -1 ) {
            return 0;
        }
        $countImages = 0;
        $images = PostGalleryImageList::get( $settings['pgimgsource'] );

        if ( empty( $images ) ) {
            return 0;
        }
        foreach ( $images as $pic ) {
            array_push( $settings['slides'], [
                'image' => [
                    'url' => $pic['url'],
                    'id' => null,
                ],
                'type' => 'image',
                'image_link_to' => $pic['url'],
                'video' => null,
                'image_link_to_type' => 'file',
                'isGesamt' => true,
                'auftragName' => $pic['title'],
                'auftragAnzeigentext' => $pic['desc'],
            ] );

            $countImages += 1;
            if ( !empty( $settings['max_images'] ) && $countImages >= $settings['max_images'] ) {
                break;
            }
        }

        return $countImages;
    }

    public static function addControls( $element ) {
        $element->add_control(
            'postgallery_seperator',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );
        $selectPosts = PostGallery::getPostList();
        $element->add_control(
            'pgimgsource',
            [
                'label' => __( 'PostGallery', 'postgallery' ),
                'type' => Controls_Manager::SELECT,
                'default' => -1,
                'options' => $selectPosts + [ -1 => 'None' ],
                'selectors' => [],
            ]
        );
    }

    /**
     * Override print slider to add images from gesamt
     * @inheritdoc
     *
     * @param array|null $settings
     */
    protected function print_slider( array $settings = null ) {
        if ( null === $settings ) {
            $settings = $this->get_active_settings();
        }

        // remove empty slides
        $imageCount = PostGalleryMediaCarousel::addPostGalleryToSlides( $settings );
        MediaCarouselInjections::removeEmptySlides( $settings, !empty( $imageCount ) );

        parent::print_slider( $settings );
    }

}