<?php namespace Pub;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/RTO-Websites/post-gallery
 * @since      1.0.0
 *
 * @package    PostGallery
 * @subpackage PostGallery/public
 */

use Elementor\Core\Files\CSS\Post;
use Lib\PostGallery;
use Lib\PostGalleryImageList;
use Lib\Thumb;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    PostGallery
 * @subpackage PostGallery/public
 * @author     RTO GmbH
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

    public static $instance;

    /**
     * Counts how often gallery is called (used for gallery-id)
     *
     * @var int
     */
    public static $count = 0;

    /**
     * The options from admin-page
     *
     * @since       1.0.3
     * @access      private
     * @var         array[]
     */
    private $options;

    public $sliderClass = '';
    public $liteboxClass = '';
    public $jsFunction = 'owlCarousel';

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginName The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct( $pluginName, $version ) {
        if ( is_admin() && !class_exists( '\Elementor\Plugin' ) ) {
            return;
        }
        $this->pluginName = $pluginName;
        $this->version = $version;
        self::$instance = $this;

        $this->options = PostGallery::getOptions();

        $sliderType = !empty( $this->option( 'sliderType' ) ) ? $this->option( 'sliderType' ) : 'owl';


        switch ( $sliderType ) {
            case 'swiper':
                $this->sliderClass = ' swiper-container';
                break;
            default:
                $this->sliderClass = ' owl-carousel owl-theme';
                break;
        }

        if ( $this->option( 'arrows' ) ) {
            $this->liteboxClass .= ' show-arrows';
        }

        new SliderShortcodePublic( $pluginName, $version );
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

        $buildPath = plugin_dir_url( __FILE__ ) . '../build';
        if ( !empty( $this->option( 'disableScripts' ) ) ) {
            wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/post-gallery-public.css', [], $this->version, 'all' );
            return;
        }

        switch ( $this->option( 'sliderType' ) ) {
            case
            'owl1':
                // owl 1
                wp_enqueue_style( 'owl.carousel', $buildPath . '/css/owl.carousel-v1.css' );
                wp_enqueue_style( 'owl.carousel.theme', $buildPath . '/css/owl.theme-v1.css' );
                wp_enqueue_style( 'owl.carousel.transitions', $buildPath . '/css/owl.transition-v1.css' );
                break;

            case 'swiper':
                wp_enqueue_style( 'swiper', $buildPath . '/css/swiper.min.css' );
                break;

            case 'owl':
                // nobreak
            default:
                // owl 2
                wp_enqueue_style( 'owl.carousel', $buildPath . '/css/owl.carousel.min.css' );
                wp_enqueue_style( 'owl.carousel.theme', $buildPath . '/css/owl.theme.default.min.css' );
                wp_enqueue_style( 'animate.css', $buildPath . '/css/animate.min.css' );
                break;
        }

        wp_enqueue_style( $this->pluginName, plugin_dir_url( __FILE__ ) . 'css/post-gallery-public.css', [], $this->version, 'all' );
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
        if ( !empty( $this->option( 'disableScripts' ) ) ) {
            return;
        }

        switch ( $this->option( 'sliderType' ) ) {
            case
            'owl1':
                wp_enqueue_script( 'owl.carousel', $buildPath . '/js/owl.carousel-v1.min.js', [ 'jquery' ] );
                break;

            case 'swiper':
                wp_enqueue_script( 'swiper', $buildPath . '/js/swiper.jquery.min.js', [ 'jquery' ] );
                break;

            case 'owl':
                // nobreak
            default:
                wp_enqueue_script( 'owl.carousel', $buildPath . '/js/owl.carousel.min.js', [ 'jquery' ] );
                break;
        }

        if ( !empty( $this->option( 'debugmode' ) ) ) {
            wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js/post-gallery-public.js', null, $this->version, true );
            wp_enqueue_script( $this->pluginName . '-litebox', plugin_dir_url( __FILE__ ) . 'js/litebox-gallery.class.js', null, $this->version, true );

            wp_enqueue_script( 'owl-postgallery', $buildPath . '/js/owl.postgallery.js', [ 'jquery', $this->pluginName . '-litebox' ] );

            wp_enqueue_script( 'swiper-postgallery', $buildPath . '/js/swiper.postgallery.js', [ 'jquery', $this->pluginName . '-litebox' ] );
        } else {
            wp_enqueue_script( $this->pluginName, $buildPath . '/js/postgallery.min.js', null, $this->version, true );
        }

        // masonry
        wp_enqueue_script( 'masonry' );
        wp_enqueue_script( 'imagesLoaded' );
    }

    /**
     * Load scripts async
     *
     * @param $tag
     * @param $handle
     * @return mixed
     */
    public function addAsyncAttribute( $tag, $handle ) {
        if ( strpos( $handle, 'postgallery' ) === false ) {
            return $tag;
        }

        return str_replace( ' src', ' async="async" src', $tag );
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


    /**
     * Hooks has_post_thumbnail and return true if a gallery-image exists
     *
     * @param $null
     * @param $object_id
     * @param $meta_key
     * @param $single
     * @return bool|null
     */
    public function postgalleryHasPostThumbnail( $null, $object_id, $meta_key, $single ) {
        if ( $meta_key == '_thumbnail_id' ) {
            $meta_type = 'post';

            $meta_cache = wp_cache_get( $object_id, $meta_type . '_meta' );

            if ( !$meta_cache ) {
                $meta_cache = update_meta_cache( $meta_type, [ $object_id ] );
                $meta_cache = $meta_cache[$object_id];
            }

            if ( !$meta_key ) {
                return $meta_cache;
            }

            if ( isset( $meta_cache[$meta_key] ) ) {
                if ( $single )
                    return maybe_unserialize( $meta_cache[$meta_key][0] );
                else
                    return array_map( 'maybe_unserialize', $meta_cache[$meta_key] );
            }

            if ( count( PostGalleryImageList::get( $object_id ) ) )
                return true;
            if ( $single )
                return '';
            else
                return [];
        }
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
     * @throws \ImagickException
     */
    public function postgalleryThumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
        if ( '' == $html ) {

            $image = PostGalleryImageList::getFirstImage( $size, $post_id );

            if ( empty( $image ) || empty( $image['url'] ) ) {
                return '';
            }

            $html = '<img width="' . $image['width'] . '" height="' . $image['height'] . '" src="'
                . $image['url']
                . '" alt="" class="attachment-' . $image['size'] . $image['orientation'] . ' wp-post-image  post-image-from-postgallery" />';
        }

        return $html;
    }


    /**
     * Adds the gallery to the_content
     *
     * @param type $content
     * @return string
     */
    public function addGalleryToContent( $content ) {
        $position = get_post_meta( $GLOBALS['post']->ID, 'postgalleryPosition', true );
        $template = get_post_meta( $GLOBALS['post']->ID, 'postgalleryTemplate', true );
        if ( empty( $position ) || $position == 'global' ) {
            $position = ( !empty( $this->option( 'globalPosition' ) ) ? $this->option( 'globalPosition' ) : 'bottom' );
        }

        // from global
        if ( empty( $template ) || $template == 'global' ) {
            $template = ( !empty( $this->option( 'globalTemplate' ) ) ? $this->option( 'globalTemplate' ) : 'thumbs' );
        }

        if ( $position === 'top' ) {
            $content = $this->returnGalleryHtml( $template ) . $content;
        } else if ( $position === 'bottom' ) {
            $content = $content . $this->returnGalleryHtml( $template );
        }

        return $content;
    }

    /**
     * Remove the_content filter for excerpt
     *
     * @param $content
     * @return mixed
     */
    public function removeContentFilterForExcerpt( $content ) {
        remove_filter( 'the_content', [ $this, 'addGalleryToContent' ] );
        return $content;
    }

    /**
     * Re-Add the_content filter after excerpt is processed
     *
     * @param $content
     * @return mixed
     */
    public function reAddContentFilterForExcerpt( $content ) {
        add_filter( 'the_content', [ $this, 'addGalleryToContent' ] );
        return $content;
    }

    /**
     * Return the gallery-html
     *
     * @param string $template
     * @param int $postid
     * @param array $args
     * @return string
     */
    public function returnGalleryHtml( $template = '', $postid = 0, $args = [] ) {
        self::$count += 1;
        $id = 'postgallery-' . ( !empty( $args['id'] ) ? $args['id'] : self::$count );

        $templateDirs = [
            POSTGALLERY_DIR . '/templates',
            get_stylesheet_directory() . '/post-gallery',
            get_stylesheet_directory() . '/plugins/post-gallery',
            get_stylesheet_directory() . '/postgallery',
        ];

        $tmpOptions = $this->options;
        $images = PostGalleryImageList::get( $postid );

        if ( !empty( $args['pgimagesource_dynamic'] ) ) {
            $images = $images + PostGalleryImageList::getByDynamic( $args['pgimagesource_dynamic'] );
        }

        if ( empty( $images )
            && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode()
        ) {
            $images = PostGalleryImageList::getPseudoImages();
        }

        if ( empty( $images ) ) {
            return '<!--postgallery: no images found for ' . $postid . '-->';
        }

        if ( empty( $template ) || $template == 'global' ) {
            $template = $this->option( 'globalTemplate' );
        }

        if ( empty( $template ) ) {
            $template = 'thumbs';
        }

        // merge args from elementor with global options
        $this->options = array_change_key_case( (array)$this->options, CASE_LOWER );
        $args = array_change_key_case( (array)$args, CASE_LOWER );
        $this->options = array_merge( $this->options, $args );

        // set wrapper class
        $wrapperClass = $this->getWrapperClass();

        $srcsetSizes = '';
        if ( !empty( $this->option( 'imageViewportWidth' ) ) ) {
            $srcsetSizes .= sprintf( '(max-width: %1$sspx) 100vw, %1$spx', $this->option( 'imageViewportWidth' ) );
        }

        $dataAttributes = '';
        if ( !empty( $this->option( 'imageAnimationDelay' ) ) ) {
            $dataAttributes .= ' data-animationdelay="' . $this->option( 'imageAnimationDelay' ) . '" ';
        }


        $count = 0;

        $appendList = $this->getAppendedTemplateList();

        ob_start();
        echo '<!--postgallery: template: ' . $template . ';postid:' . $postid . '-->';
        echo '<div class="postgallery-wrapper ' . $wrapperClass . '"  id="' . $id . '" ' . $dataAttributes . '>';
        foreach ( $templateDirs as $tplDir ) {
            if ( file_exists( $tplDir . '/' . $template . '.php' ) ) {
                require( $tplDir . '/' . $template . '.php' );
                break;
            }
        }
        echo '</div>';

        // echo extra style
        $extraStyle = $this->createExtraCss( $id );
        if ( !empty( $extraStyle ) ) {
            echo '<style><!--';
            echo $extraStyle;
            echo '--></style>';
        }

        echo '<!--end postgallery-->';

        $content = ob_get_contents();
        ob_end_clean();

        $this->options = $tmpOptions;

        return $content;
    }

    /**
     * Returns list of appended templates, ordered by position to append
     *
     * @return array
     */
    private function getAppendedTemplateList() {
        if ( empty( $this->option( 'append_templates' ) ) ) {
            return [];
        }
        $appendList = [];
        foreach ( $this->option( 'append_templates' ) as $item ) {
            if ( empty( $appendList[$item['position_to_append']] ) ) {
                $appendList[$item['position_to_append']] = [];
            }

            $appendList[$item['position_to_append']][] = $item['template_to_append'];
        }

        return $appendList;
    }

    public function getCaption( $image ) {
        switch ( $this->option( 'captionSource' ) ) {
            case 'title':
                return $image['title'];
                break;

            case 'attachment_alt':
                return $image['alt'];
                break;

            case 'attachment_caption':
                return $image['imageCaption'];
                break;

            case 'content':
                return $image['desc'];
                break;
        }
    }

    /**
     * Create style for widget
     *
     * @param $id
     *
     * @return string
     */
    private function createExtraCss( $id ) {
        $extraStyle = '';

        // hide thumbs
        if ( !empty( $this->option( 'pgmaxthumbs' ) ) ) {
            $extraStyle .= '#' . $id
                . ' .gallery .item:nth-child(n+' . ( $this->option( 'pgmaxthumbs' ) + 1 ) . ') { ';
            $extraStyle .= 'display: none;';
            $extraStyle .= '}';
        }

        // image animation
        $extraStyle .= $this->createImageAnimationCss( $id );

        // css for non elementor websites
        $extraStyle .= $this->createNonElementorExtraCss( $id );

        return $extraStyle;
    }

    /**
     * Creates css for image animations
     *
     * @param $id
     * @return string
     */
    private function createImageAnimationCss( $id ) {
        if ( empty( $this->option( 'imageAnimation' ) ) ) {
            return '';
        }

        $extraStyle = '';

        if ( !empty( $this->option( 'imageAnimationDuration' ) ) ) {
            $extraStyle .= '#' . $id
                . ' .gallery .item { ';
            $extraStyle .= 'transition-duration: ' . $this->option( 'imageAnimationDuration' ) . 'ms;';
            $extraStyle .= '}';
        }

        if ( !empty( $this->option( 'imageAnimationCss' ) ) ) {
            $extraStyle .= '#' . $id
                . ' .gallery .item { ';
            $extraStyle .= $this->option( 'imageAnimationCss' );
            $extraStyle .= '}';
        }

        if ( !empty( $this->option( 'imageAnimationCssAnimated' ) ) ) {
            $extraStyle .= '#' . $id
                . ' .gallery .item.show { ';
            $extraStyle .= $this->option( 'imageAnimationCssAnimated' );
            $extraStyle .= '}';
        }

        return $extraStyle;
    }

    /**
     * Create extra styles for non elementor embed
     *
     * @param $id
     * @return string
     */
    private function createNonElementorExtraCss( $id ) {
        if ( !empty( $GLOBALS['PgIsElementorWidget'] ) ) {
            return '';
        }
        $extraStyle = '';

        // item ratio for non-elementor
        if ( !empty( $this->option( 'equalheight' ) ) && !empty( $this->option( 'itemratio' ) ) && is_string( $this->option( 'itemratio' ) )
        ) {
            $extraStyle .= '#' . $id
                . ' .gallery .item .bg-image { ';
            $extraStyle .= 'padding-bottom: calc( ' . $this->option( 'itemratio' ) . ' * 100% );';
            $extraStyle .= '}';
        }

        if ( !empty( $this->option( 'nogrid' ) ) ) {
            $extraStyle .= '#' . $id
                . ' .gallery { display: block; }';
            $extraStyle .= '#' . $id
                . ' .gallery .item { display: inline-block; width: auto; }';
            $extraStyle .= '#' . $id
                . ' .gallery .item img { width: auto; }';
        } else {
            $extraStyle .= '#' . $id
                . ' .gallery { grid-template-columns: repeat(' . $this->option( 'columns' ) . ', minmax(0, 1fr));}';
        }
        // column gap
        if ( !empty( $this->option( 'columngap' ) ) ) {
            $extraStyle .= '#' . $id
                . ' .gallery .item { ';
            $extraStyle .= 'padding-left: ' . ( $this->option( 'columngap' ) / 2 ) . 'px;';
            $extraStyle .= 'padding-right: ' . ( $this->option( 'columngap' ) / 2 ) . 'px;';
            $extraStyle .= '}';

            $extraStyle .= '#' . $id
                . ' .gallery { ';
            $extraStyle .= 'margin-left: -' . ( $this->option( 'columngap' ) / 2 ) . 'px;';
            $extraStyle .= 'margin-right: -' . ( $this->option( 'columngap' ) / 2 ) . 'px;';
            $extraStyle .= '}';
        }

        // row gap
        if ( !empty( $this->option( 'rowgap' ) ) ) {
            $extraStyle .= '#' . $id
                . ' .gallery .item { ';
            $extraStyle .= 'padding-bottom: ' . ( $this->option( 'rowgap' ) ) . 'px;';
            $extraStyle .= '}';

        }

        return $extraStyle;
    }

    /**
     * Set css-classes for wrapper
     *
     * @return string
     */
    private function getWrapperClass() {
        $wrapperClass = $this->option( 'wrapperClass' );

        // equal height
        if ( !empty( $this->option( 'equalHeight' ) ) ) {
            $wrapperClass .= ' items-equal';
        }

        // image animation
        if ( !empty( $this->option( 'imageAnimation' ) ) ) {
            $wrapperClass .= ' with-animation';
        }

        // masonry
        $masonry = $this->option( 'masonry' );
        switch ( $masonry ) {
            case 'css':
                $wrapperClass .= ' with-css-masonry';
                break;
            case 'on':
                $wrapperClass .= ' with-js-masonry';
                break;
            case 'horizontal':
                $wrapperClass .= ' with-js-masonry js-masonry-horizontal';
                break;
        }

        $captionAnimation = $this->option( 'pgcaption_animation' );
        switch ( $captionAnimation ) {
            case 'show_on_hover':
                $wrapperClass .= ' caption-animation-show-on-hover has-caption-animation';
                break;

            case 'hide_on_hover':
                $wrapperClass .= ' caption-animation-hide-on-hover has-caption-animation';
                break;
        }

        return $wrapperClass;
    }

    /**
     * Add html to footer
     *
     * @param string $footer
     */
    public function insertFooterHtml( $footer ) {
        if ( empty( $this->option( 'enableLitebox' ) ) || !empty( $this->option( 'disableScripts' ) ) ) {
            return;
        }
        $template = $this->option( 'liteboxTemplate' );

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
     * @param array $args
     * @param string $content
     * @return string
     */
    public function postgalleryShortcode( $args, $content = '' ) {
        if ( empty( $this->option( 'template' ) ) ) {
            $template = get_post_meta( $GLOBALS['post']->ID, 'postgalleryTemplate', true );
        } else {
            $template = $this->option( 'template' );
        }
        $postid = 0;
        if ( !empty( $this->option( 'post' ) ) ) {
            if ( is_numeric( $this->option( 'post' ) ) ) {
                $postid = $this->option( 'post' );
            } else {
                $postid = url_to_postid( $this->option( 'post' ) );
            }
        }

        if ( empty( $postid ) && empty( $this->option( 'post' ) ) ) {
            $postid = $GLOBALS['post']->ID;
        }

        return $this->returnGalleryHtml( $template, $postid, $args );
    }

    /**
     * Gives a url from cache
     */
    public function getThumbList() {
        if ( isset( $_REQUEST['getFullsizeThumbs'] ) || isset( $_REQUEST['getThumbList'] ) ) {

            $_SESSION['postGalleryWindowSize'] = [
                'width' => $_REQUEST['width'],
                'height' => $_REQUEST['height'],
            ];

            if ( empty( $_REQUEST['pics'] ) ) {
                die( '{}' );
            }
            $pics = ( $_REQUEST['pics'] );

            if ( !empty( $pics ) ) {
                $pics = PostGalleryImageList::resize( $pics, [
                    'width' => $_REQUEST['width'],
                    'height' => $_REQUEST['height'],
                    'scale' => ( !isset( $_REQUEST['scale'] ) ? 1 : $_REQUEST['scale'] ),
                ] );
            }
            echo json_encode( $pics );

            exit();
        }
    }

    /**
     * Inserts script with settings in header
     *
     * @param $header
     */
    public function insertHeaderscript( $header ) {

        if ( empty( $this->option( 'enableLitebox' ) ) || !empty( $this->option( 'disableScripts' ) ) ) {
            echo $header;
            return;
        }

        $sliderType = $this->option( 'sliderType' );
        $oldOwl = $this->option( 'sliderType' ) == 'owl1' ? 'owlVersion: 1,' : '';
        $asBg = !empty( $this->option( 'asBg' ) ) ? 'asBg: 1,' : '';
        $clickEvents = !empty( $this->option( 'clickEvents' ) ) ? 'clickEvents: 1,' : '';
        $keyEvents = !empty( $this->option( 'keyEvents' ) ) ? 'keyEvents: 1,' : '';
        $customSliderConfig = $this->option( 'owlConfig' );
        $owlThumbConfig = $this->option( 'owlThumbConfig' );
        $debug = !empty( $this->option( 'debugmode' ) );
        $sliderConfig = '';

        // minify
        $customSliderConfig = preg_replace( "/^\s{2,}?([^,]+?),?$/m", ',', $customSliderConfig );
        $customSliderConfig = preg_replace( "/(\r?\n?)*/", '', $customSliderConfig );

        $sliderConfig .= ( !empty( $this->option( 'autoplay' ) ) || in_array( 'autoplay', $this->option(), true ) ? 'autoplay: true,' : '' );
        $sliderConfig .= ( !empty( $this->option( 'loop' ) ) || in_array( 'loop', $this->option(), true ) ? 'loop: true,' : '' );
        $sliderConfig .= ( !empty( $this->option( 'animateOut' ) ) ? 'animateOut: "' . $this->option( 'animateOut' ) . '",' : '' );
        $sliderConfig .= ( !empty( $this->option( 'animateIn' ) ) ? 'animateIn: "' . $this->option( 'animateIn' ) . '",' : '' );
        $sliderConfig .= ( !empty( $this->option( 'autoplayTimeout' ) ) ? 'autoplayTimeout: ' . $this->option( 'autoplayTimeout' ) . ',' : '' );
        $sliderConfig .= ( !empty( $this->option( 'items' ) ) ? 'items: ' . $this->option( 'items' ) . ',' : 'items: 1,' );

        $sliderConfig .= $customSliderConfig;


        $owlThumbConfig = preg_replace( "/^\s{2,}?([^,]+?),?$/m", ',', $owlThumbConfig );
        $owlThumbConfig = preg_replace( "/(\r?\n?)*/", '', $owlThumbConfig );

        // script for websiteurl
        $script = PHP_EOL . '<script>';
        $script .= 'window.pgConfig = { websiteUrl: "' . get_bloginfo( 'wpurl' ) . '",';
        $script .= 'liteboxArgs: {
            sliderType: "' . $sliderType . '",'
            . $asBg . $clickEvents . $keyEvents . $oldOwl
            . 'sliderArgs: {' . $sliderConfig . '},'
            . 'owlThumbArgs: {' . $owlThumbConfig . '}'
            . ( $debug ? ',debug: true,' : '' )
            . '}};';
        $script .= '</script>' . PHP_EOL;

        $header = $header . $script;

        echo $header;
    }

    /**
     * Insert styles
     *
     * @param $header
     */
    public function insertHeaderstyle( $header ) {
        $style = '<style class="postgallery-style">';

        // arrows und close-button color
        $style .= '.litebox-gallery.show-arrows::after,
            .litebox-gallery.show-arrows::before,
            .litebox-gallery .close-button {
                color: ' . $this->option( 'mainColor' ) . ';
            }';

        // highlight color on active thumb
        $style .= '.litebox-gallery .thumb-container .current-img img {
                box-shadow: 0 0 0px 2px ' . $this->option( 'mainColor' ) . ';
            }';

        // gallery background-color
        $style .= '.litebox-gallery {
                background-color: ' . $this->option( 'secondColor' ) . ';
            }';

        // owl-dots color
        $style .= '.owl-theme .owl-dots .owl-dot.active span, 
            .owl-theme .owl-dots .owl-dot:hover span {
                background-color: ' . $this->option( 'mainColor' ) . ';
             }';

        $style .= '</style>';
        echo $style . $header;
    }

    /**
     * Returns a single option, or all options if property is null
     *
     * @param null $property
     * @return array|\array[]|\mixed[]|null
     */
    public function option( $property = null ) {
        $options = array_change_key_case( (array)$this->options, CASE_LOWER );
        if ( !empty( $property ) ) {
            $property = strtolower( $property );

            return isset( $options[$property] ) ? $options[$property] : null;
        }

        return $options;
    }

    /**
     * Sets an option
     *
     * @param $property
     * @param $value
     */
    public function setOption( $property, $value ) {
        $this->options[$property] = $value;
    }

    static function getInstance() {
        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
