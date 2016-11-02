<?php
namespace Pub;

use Inc\PostGallery;
use MagicAdminPage\MagicAdminPage;
use Thumb\Thumb;


class SliderShortcodePublic {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $pluginName The ID of this plugin.
     */
    private $pluginName;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;


    /**
     * Textdomain of the plugin
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $textdomain;

    private $thumbOnly = false;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of this plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version ) {
        $this->textdomain = $pluginName;
        $this->pluginName = $pluginName;
        $this->version = $version;

        add_shortcode( 'postslider', array( $this, '_shortcode' ) );
        add_shortcode( 'slider', array( $this, '_shortcode' ) );
    }

    public function _shortcode( $args, $content ) {
        if ( empty( $args[0] ) ) {
            return;
        }

        $sliderid = $args[0];
        $slider = get_post( $sliderid );

        if ( empty( $slider ) ) {
            return;
        }

        $output = '';

        // get settings from post-meta
        $loadFrom = get_post_meta( $sliderid, 'sliderLoadFrom', true );
        $width = get_post_meta( $sliderid, 'sliderWidth', true );
        $height = get_post_meta( $sliderid, 'sliderHeight', true );
        $scale = get_post_meta( $sliderid, 'sliderScale', true );
        $owlConfig = get_post_meta( $sliderid, 'sliderOwlConfig', true );

        $imgWidth = get_post_meta( $sliderid, 'sliderImageWidth', true );
        $imgHeight = get_post_meta( $sliderid, 'sliderImageHeight', true );

        $noLazy = get_post_meta( $sliderid, 'sliderNoLazy', true );
        $autoplay = get_post_meta( $sliderid, 'sliderAutoplay', true );
        $loop = get_post_meta( $sliderid, 'sliderLoop', true );
        $items = get_post_meta( $sliderid, 'sliderItems', true );
        $asBg = get_post_meta( $sliderid, 'sliderAsBg', true );
        $shuffle = get_post_meta( $sliderid, 'sliderShuffle', true );
        $linkPost = get_post_meta( $sliderid, 'sliderLinkPost', true );

        $this->thumbOnly = get_post_meta( $sliderid, 'sliderThumbOnly', true );

        $images = PostGallery::getImages( $sliderid );
        $class = '';

        // get from sgortcode-arguments
        if ( !empty( $args['width'] ) ) {
            $width = $args['width'];
        }
        if ( !empty( $args['height'] ) ) {
            $height = $args['height'];
        }
        if ( !empty( $args['imgwidth'] ) ) {
            $imgWidth = $args['imgwidth'];
        }
        if ( !empty( $args['imgheight'] ) ) {
            $imgHeight = $args['imgheight'];
        }
        if ( isset( $args['scale'] ) ) {
            $scale = $args['scale'];
        }
        if ( !empty( $args['class'] ) ) {
            $class = $args['class'];
        }

        if ( in_array( 'autoplay', $args ) ) {
            $autoplay = true;
        }
        if ( in_array( 'loop', $args ) ) {
            $loop = true;
        }
        if ( !empty( $args['items'] ) ) {
            $items = $args['items'];
        }

        if ( !empty( $args['owlExtra'] ) ) {
            $owlConfig .= ',' . $args['owlExtra'];
        }
        if ( in_array( 'noLazy', $args ) ) {
            $noLazy = true;
        }

        if ( in_array( 'asbg', $args ) ) {
            $asBg = true;
        }

        if ( in_array( 'shuffle', $args ) || in_array( 'random', $args ) ) {
            $shuffle = true;
        }

        $class .= ' pg-slider-' . $slider->post_name;

        if ( $autoplay ) {
            $owlConfig .= 'autoplay:1,' . $owlConfig;
        }
        if ( $loop ) {
            $owlConfig .= 'loop:1,' . $owlConfig;
        }
        if ( $items ) {
            $owlConfig .= 'items:' . $items . ',' . $owlConfig;
        }

        if ( in_array( 'link', $args ) ) {
            $linkPost = true;
        }

        // use slider-width/height as maximun for image-scaling
        if ( empty( $imgWidth ) ) {
            $imgWidth = $width;
        }
        if ( empty( $imgHeight ) ) {
            $imgHeight = $height;
        }

        // load images from other posts
        if ( !empty( $loadFrom ) && is_array( $loadFrom ) ) {
            $images = $this->getImagesFromPostList( $loadFrom, $images );
        }

        // resize images
        if ( !empty( $imgWidth ) || !empty( $imgHeight ) ) {
            $images = PostGallery::getPicsResized( $images, array(
                'width' => !empty( $imgWidth ) ? $imgWidth : '9999',
                'height' => !empty( $imgHeight ) ? $imgHeight : '9999',
                'scale' => is_null( $scale ) ? 0 : $scale,
            ) );
        }

        // set style
        $style = '';
        $style .= !empty( $width ) ? 'max-width:' . $width . 'px;' : '';
        $style .= !empty( $height ) ? 'max-height:' . $height . 'px;' : '';

        if ( $shuffle ) {
            shuffle( $images );
        }

        $tag = 'div';

        // output html
        $output .= '<figure class="pg-slider-' . $sliderid . ' ' . $class . ' postgallery-slider owl-carousel owl-theme" style="' . $style . '">';
        foreach ( $images as $image ) {
            $permalink = get_the_permalink( $image['post_id'] );
            $background = '';
            if ( $asBg ) {
                $background = ' style="background-image:url(' . $image['url'] . ');height: ' . $height . 'px;"';
            }

            $href = '';
            if ( $linkPost ) {
                $tag = 'a';
                $href = ' href="' . $permalink . '"';
            }


            $output .= '<' . $tag . ' ' . $href . ' class="slider-image" data-post_id="' . $image['post_id'] .
                '" data-post_permalink="' . $permalink .
                '" data-post_title="' . $image['post_title'] . '" ' . $background . '>';

            if ( empty( $image['width'] ) ) {
                $image['width'] = 'auto';
            }
            if ( empty( $image['height'] ) ) {
                $image['height'] = 'auto';
            }
            if ( empty( $image['alt'] ) ) {
                $image['alt'] = '';
            }


            if ( !$asBg && empty( $noLazy ) ) {
                $output .= '<img width="' . $image['width'] . '" height="' . $image['height']
                    . '" src="#" class="lazyload" data-src="' . $image['url'] . '" alt="' . $image['alt'] . '" />';
            } else if ( !$asBg ) {
                $output .= '<img width="' . $image['width'] . '" height="' . $image['height']
                    . '" src="' . $image['url'] . '" alt="' . $image['alt'] . '" />';
            }

            if ( !empty( $image['title'] ) || !empty( $image['desc'])) {
                $output .= '<div class="slider-image-info">';
                if ( !empty( $image['title'] ) ) {
                    $output .= '<div class="slider-image-title">' . $image['title'] . '</div>';
                }
                if ( !empty( $image['desc'] ) ) {
                    $output .= '<div class="slider-image-desc">' . $image['desc'] . '</div>';
                }
                $output .= '</div>';
            }
            $output .= '</' . $tag . '>';
        }
        $output .= '</figure>';

        // output script
        $output .= '<script>
            jQuery(function($) {$(".pg-slider-' . $sliderid . '").owlCarousel({' . $owlConfig . '});});
            </script>';

        return $output;
    }


    /**
     * Load images from an image list
     *
     * @param $loadFrom
     * @param array $images
     * @return array
     */
    public function getImagesFromPostList( $loadFrom, $images = array() ) {
        $postTypes = get_post_types();
        unset( $postTypes['revision'] );
        unset( $postTypes['nav_menu_item'] );

        // load from other posts
        foreach ( $loadFrom as $loadId ) {
            if ( !empty( $loadId ) ) {
                if ( is_numeric( $loadId ) ) {
                    $images = array_merge( $images, $this->getImagesFromPost( $loadId ) );
                } else {
                    // is category
                    // get posts from category
                    ob_start();
                    $catPosts = get_posts( array(
                        'post_type' => $postTypes,
                        'category' => str_replace( 'cat-', '', $loadId ),
                        'posts_per_page' => -1,
                        'suppress_filters' => true,
                    ) );
                    ob_end_clean();

                    foreach ( $catPosts as $catPost ) {
                        $images = array_merge( $images, $this->getImagesFromPost( $catPost->ID ) );
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Get images of a post
     *
     * @param $loadId
     * @return array|\Inc\type
     */
    public function getImagesFromPost( $loadId ) {
        if ( $this->thumbOnly ) {
            // only thumb
            $url = wp_get_attachment_url( get_post_thumbnail_id( $loadId ) );
            $url = apply_filters( 'postgallerySlider_getPostThumbUrl', $url, $loadId );

            if ( empty( $url ) ) {
                // no post-thumb, get first image
                $images = PostGallery::getImages( $loadId );
                $images = array_splice( $images, 0, 1 );
                return $images;
            }


            return array(
                array(
                    'url' => $url,
                    'post_id' => $loadId,
                    'post_title' => get_the_title( $loadId ),
                ),
            );
        } else {
            return PostGallery::getImages( $loadId );
        }
    }
}