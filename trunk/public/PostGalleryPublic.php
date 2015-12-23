<?php namespace Pub;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/crazypsycho
 * @since      1.0.0
 *
 * @package    PostGallery
 * @subpackage PostGallery/public
 */
use Inc\PostGallery;
use MagicAdminPage\MagicAdminPage;
use Thumb\Thumb;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PostGallery
 * @subpackage PostGallery/public
 * @author     crazypsycho <info@hennewelt.de>
 */
class PostGalleryPublic {

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

    private $textdomain;


    /**
     * The options from admin-page
     *
     * @since       1.0.3
     * @access      private
     * @var         array[]
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $pluginName The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct( $pluginName, $version ) {
        $this->pluginName = $pluginName;
        $this->textdomain = $pluginName;
        $this->version = $version;
        $this->options = MagicAdminPage::getOption( 'post-gallery' );

        new SliderShortcodePublic( $pluginName, $version );

        add_filter( 'the_content', array( $this, 'addGalleryToContent' ) );
        add_shortcode( 'postgallery', array( $this, 'postgalleryShortcode' ) );
        add_action( 'plugins_loaded', array( $this, 'postgalleryThumb' ) );
        add_action( 'plugins_loaded', array( $this, 'getThumbList' ) );

        // Embed headerscript
        add_action( 'wp_head', array( $this, 'insertHeaderscript' ) );

        // Embed footer-html
        add_action( 'wp_footer', array( $this, 'insertFooterHtml' ) );

        add_filter( 'post_thumbnail_html', array( $this, 'postgalleryThumbnail' ), 10, 5 );
        add_filter( 'get_post_metadata', array( $this, 'postgalleryHasPostThumbnail' ), 10, 5 );

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueStyles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PostGalleryLoader as all of the hooks are defined
         * in that particular class.
         *
         * The PostGalleryLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/post-gallery-public.css', array(), $this->version, 'all' );

        $buildPath = plugin_dir_url( __FILE__ ) . '../build';

        if ( !empty( $this->options['useOldOwl'] ) ) {
            // owl 1
            wp_enqueue_style( 'owl.carousel', $buildPath . '/css/owl.carousel-v1.css' );
            wp_enqueue_style( 'owl.carousel.theme', $buildPath . '/css/owl.theme-v1.css' );
            wp_enqueue_style( 'owl.carousel.transitions', $buildPath . '/css/owl.transitions-v1.css' );
        } else {
            // owl 2
            wp_enqueue_style( 'owl.carousel', $buildPath . '/css/owl.carousel.min.css' );
            wp_enqueue_style( 'owl.carousel.theme', $buildPath . '/css/owl.theme.default.min.css' );
        }
        wp_enqueue_style( 'animate.css', $buildPath .  '/css/animate.min.css' );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueueScripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in PostGalleryLoader as all of the hooks are defined
         * in that particular class.
         *
         * The PostGalleryLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        $buildPath = plugin_dir_url( __FILE__ ) . '../build';

        wp_enqueue_script( 'lazysizes', $buildPath
            . '/js/lazysizes.min.js' );

        if ( !empty( $this->options['useOldOwl'] ) ) {
            wp_enqueue_script( 'owl.carousel', $buildPath . '/owl.carouse-v1l.min.js', array( 'jquery' ) );
        } else {
            wp_enqueue_script( 'owl.carousel', $buildPath . '/owl.carousel.min.js', array( 'jquery' ) );
        }

        wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js/post-gallery-public.js', array( 'jquery' ), $this->version, false );
        wp_enqueue_script( $this->pluginName . '-litebox', plugin_dir_url( __FILE__ ) . 'js/litebox-gallery.class.js', array( 'jquery' ), $this->version, false );

    }


    /**
     * Register request for thumbnails
     */
    public function postgalleryThumb() {
        if ( isset( $_REQUEST['loadThumb'] ) ) {
            Thumb::theThumb();
            exit();
        }
    }


    public function postgalleryHasPostThumbnail( $null, $object_id, $meta_key, $single ) {

        if ( $meta_key == '_thumbnail_id' ) {
            return true;
        }

        return null;
    }

    /**
     * Hooks the_post_thumbnail() and loads first gallery-image if post-thumb is empty
     *
     * @param $html
     * @param $post_id
     * @param $post_thumbnail_id
     * @param $size
     * @param $attr
     * @return string
     */
    public function postgalleryThumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
        if ( '' == $html ) {
            // get id from main-language post
            if ( class_exists( 'SitePress' ) ) {
                global $sitepress;

                $post_id = icl_object_id( $post_id, 'any', true, $sitepress->get_default_language() );
            }

            $postGalleryImages = PostGallery::getImages( $post_id );
            if ( !count( $postGalleryImages ) ) {
                return $html;
            }

            $firstThumb = array_shift( $postGalleryImages );

            if ( empty( $size ) ) {
                $size = 'post-thumbnail';
            }

            // get width of thumbnail
            $width = intval( get_option( "{$size}_size_w" ) );
            $height = intval( get_option( "{$size}_size_h" ) );
            $crop = intval( get_option( "{$size}_crop" ) );

            if ( empty( $width ) && empty( $height ) ) {
                global $_wp_additional_image_sizes;
                if ( !empty( $_wp_additional_image_sizes ) &&
                    !empty( $_wp_additional_image_sizes[$size] )
                ) {
                    $width = $_wp_additional_image_sizes[$size]['width'];
                    $height = $_wp_additional_image_sizes[$size]['height'];
                }
            }

            if ( empty( $width ) ) {
                $width = '1920';
            }
            if ( empty( $height ) ) {
                $height = '1080';
            }

            $path = $firstThumb['path'];
            $path = explode( '/wp-content/', $path );
            $path = '/wp-content/' . array_pop( $path );

            $thumb = Thumb::getThumb( $path, array(
                'width' => $width,
                'height' => $height,
                'scale' => '0',
            ) );

            $width = $height = 'auto';

            $orientation = ' wide';

            if ( $thumb['width'] > $thumb['height'] ) {
                $width = $thumb['width'];
            } else {
                $height = $thumb['height'];
                $orientation = ' upright';
            }

            $html = '<img width="' . $width . '" height="' . $height . '" src="'
                . $thumb['url']
                . '" alt="" class="attachment-' . $size . $orientation . ' wp-post-image  post-image-from-postgallery" />';
        }

        return $html;
    }

    /**
     * Adds the gallery to the_content
     *
     * @param type $content
     * @return type
     */
    public function addGalleryToContent( $content ) {
        $position = get_post_meta( $GLOBALS['post']->ID, 'postgalleryPosition', true );
        $template = get_post_meta( $GLOBALS['post']->ID, 'postgalleryTemplate', true );
        if ( empty( $position ) || $position == 'global' ) {
            $position = ( !empty( $this->options['globalPosition'] ) ? $this->options['globalPosition'] : 'bottom' );
        }

        // from global
        if ( empty( $template ) || $template == 'global' ) {
            $template = ( !empty( $this->options['globalTemplate'] ) ? $this->options['globalTemplate'] : 'thumbs' );
        }

        if ( $position === 'top' ) {
            $content = $this->returnGalleryHtml( $template ) . $content;
        } else if ( $position === 'bottom' ) {
            $content = $content . $this->returnGalleryHtml( $template );
        }

        return $content;
    }

    /**
     * Return the gallery-html
     *
     * @param type $template
     * @return type
     */
    public function returnGalleryHtml( $template, $postid = 0, $args = array() ) {
        $customTemplateDir = get_stylesheet_directory() . '/post-gallery';
        $customTemplateDir2 = get_stylesheet_directory() . '/plugins/post-gallery';
        $defaultTemplateDir = POSTGALLERY_DIR . '/templates';

        $images = PostGallery::getImages( $postid );

        if ( empty( $template ) || $template == 'global' ) {
            $template = $this->options['globalTemplate'];
        }

        ob_start();
        if ( file_exists( $customTemplateDir . '/' . $template . '.php' ) ) {
            require( $customTemplateDir . '/' . $template . '.php' );
        } else if ( file_exists( $customTemplateDir2 . '/' . $template . '.php' ) ) {
            require( $customTemplateDir2 . '/' . $template . '.php' );
        } else if ( file_exists( $defaultTemplateDir . '/' . $template . '.php' ) ) {
            require( $defaultTemplateDir . '/' . $template . '.php' );
        }

        $content = ob_get_contents();
        ob_clean();
        return $content;
    }

    /**
     * Add html to footer
     *
     * @param string $footer
     */
    public function insertFooterHtml( $footer ) {
        $options = $this->options;
        $template = ( !empty( $options['template'] ) ? $options['template'] : 'default' );

        $customTemplateDir = get_stylesheet_directory() . '/litebox';
        $defaultTemplateDir = POSTGALLERY_DIR . '/litebox-templates';

        if ( file_exists( $customTemplateDir . '/' . $template . '.php' ) ) {
            require( $customTemplateDir . '/' . $template . '.php' );
        } else if ( file_exists( $defaultTemplateDir . '/' . $template . '.php' ) ) {
            require( $defaultTemplateDir . '/' . $template . '.php' );
        }
    }

    /**
     * Adds shortcode for custom gallery-position
     *
     * @param type $args
     * @param type $content
     * @return {string}
     */
    public function postgalleryShortcode( $args, $content = '' ) {
        if ( empty( $args['template'] ) ) {
            $template = get_post_meta( $GLOBALS['post']->ID, 'postgalleryTemplate', true );
        } else {
            $template = $args['template'];
        }
        $postid = 0;
        if ( !empty( $args['post'] ) ) {
            $postid = $args['post'];
        }

        return $this->returnGalleryHtml( $template, $postid, $args );
    }


    /**
     * Gives a url from cache
     */
    public function getThumbList() {
        if ( isset( $_REQUEST['getFullsizeThumbs'] ) || isset( $_REQUEST['getThumbList'] ) ) {

            $_SESSION['postGalleryWindowSize'] = array(
                'width' => $_REQUEST['width'],
                'height' => $_REQUEST['height'],
            );

            if ( empty( $_REQUEST['pics'] ) ) {
                die( '{}' );
            }
            $pics = ( $_REQUEST['pics'] );

            if ( !empty( $pics ) ) {
                $pics = PostGallery::getPicsResized( $pics, array(
                    'width' => $_REQUEST['width'],
                    'height' => $_REQUEST['height'],
                    'scale' => ( !isset( $_REQUEST['scale'] ) ? 1 : $_REQUEST['scale'] ),
                ) );
            }
            echo json_encode( $pics );

            exit();
        }
    }

    public function insertHeaderscript( $header ) {
        $oldOwl = !empty( $this->options['useOldOwl'] ) ? 'owlVersion: 1,' : '';
        $clickEvents = !empty( $this->options['clickEvents'] ) ? 'clickEvents: 1,' : '';
        $keyEvents = !empty( $this->options['keyEvents'] ) ? 'keyEvents: 1,' : '';

        // script for websiteurl
        $script = '<script type="text/javascript">';
        $script .= 'var websiteUrl = "' . get_bloginfo( 'wpurl' ) . '";';
        $script .= 'var pluginUrl = "' . WP_PLUGIN_URL . '";';
        $script .= 'var liteboxArgs = {'
            . $clickEvents . $keyEvents . $oldOwl
            . 'owlArgs: {' . $this->options['owlConfig'] . '}};';
        $script .= '</script>';

        $header = $header . $script;

        echo $header;
    }
}
