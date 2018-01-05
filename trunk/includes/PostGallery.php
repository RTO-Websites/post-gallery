<?php namespace Inc;

use Admin\PostGalleryAdmin;
use PostGalleryWidget\Widgets\PostGalleryElementorWidget;
use Pub\PostGalleryPublic;
use Thumb\Thumb;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/crazypsycho
 * @since      1.0.0
 *
 * @package    PostGallery
 * @subpackage PostGallery/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    PostGallery
 * @subpackage PostGallery/includes
 * @author     crazypsycho <info@hennewelt.de>
 */
class PostGallery {
    static $cachedImages = array();
    static $cachedFolders = array();

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      PostGalleryLoader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $pluginName The string used to uniquely identify this plugin.
     */
    protected $pluginName;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    protected $textdomain;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->pluginName = 'post-gallery';
        $this->textdomain = 'post-gallery';
        $this->version = '1.0.0';

        $this->loadDependencies();
        $this->setLocale();
        $this->defineAdminHooks();
        $this->definePublicHooks();


        $this->initElementor();


        add_action( 'init', array( $this, 'addPostTypeGallery' ) );

        add_action( 'cronPostGalleryDeleteCachedImages', array( $this, 'postGalleryDeleteCachedImages' ) );
    }

    /**
     * Init elementor widget
     */
    public function initElementor() {
        add_action( 'elementor/editor/before_enqueue_styles', array( PostGalleryAdmin::getInstance(), 'enqueueStyles' ) );
        add_action( 'elementor/editor/before_enqueue_scripts', array( PostGalleryAdmin::getInstance(), 'enqueueScripts' ), 99999 );

        add_action( 'elementor/widgets/widgets_registered', function () {
            require_once( 'PostGalleryElementorControl.php' );
            require_once( 'PostGalleryElementorWidget.php' );

            \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new PostGalleryElementorWidget() );
        } );

        add_action( 'elementor/editor/after_save', function ( $post_id, $editor_data ) {
            $meta = json_decode( get_post_meta( $post_id, '_elementor_data' )[0], true );

            // fetch elements
            $widgets = [];
            self::getAllWidgets( $widgets, $meta, 'postgallery' );

            foreach ( $widgets as $widget ) {
                $pgSort = self::arraySearch( $widget, 'pgsort' );
                $pgTitles = self::arraySearch( $widget, 'pgimgtitles' );
                $pgDescs = self::arraySearch( $widget, 'pgimgdescs' );
                $pgAlts = self::arraySearch( $widget, 'pgimgalts' );
                $pgOptions = self::arraySearch( $widget, 'pgimgoptions' );
                $pgPostId = self::arraySearch( $widget, 'pgimgsource' );

                if ( empty( $pgPostId ) ) {
                    $pgPostId = $post_id;
                } else {
                    $pgPostId = $pgPostId[0];
                }


                if ( !empty( $pgSort ) ) {
                    update_post_meta( $pgPostId, 'postgalleryImagesort', $pgSort[0] );
                }
                if ( !empty( $pgTitles ) ) {
                    update_post_meta( $pgPostId, 'postgalleryTitles', json_decode( $pgTitles[0], true ) );
                }
                if ( !empty( $pgDescs ) ) {
                    update_post_meta( $pgPostId, 'postgalleryDescs', json_decode( $pgDescs[0], true ) );
                }
                if ( !empty( $pgAlts ) ) {
                    update_post_meta( $pgPostId, 'postgalleryAltAttributes', json_decode( $pgAlts[0], true ) );
                }
                if ( !empty( $pgOptions ) ) {
                    update_post_meta( $pgPostId, 'postgalleryImageOptions', json_decode( $pgOptions[0], true ) );
                }
            }
        } );
    }

    public static function getAllWidgets( &$widgets = [], $meta, $widgetType = '' ) {
        // fetch elements
        foreach ( $meta as $data ) {
            if ( $data['elType'] == 'widget' && ( !empty( $widgetType ) && $widgetType == $data['widgetType'] ) ) {
                $widgets[] = $data;
            }
            if ( !empty( $data['elements'] ) ) {
                self::getAllWidgets( $widgets, $data['elements'], $widgetType );
            }
        }
    }

    /**
     * Helper function, find value in mutlidimensonal array
     *
     * @param $array
     * @param $key
     * @return array
     */
    public static function arraySearch( $array, $key ) {
        $results = array();

        if ( is_array( $array ) ) {
            if ( isset( $array[$key] ) ) {
                $results[] = $array[$key];
            }

            foreach ( $array as $subarray ) {
                $results = array_merge( $results, self::arraySearch( $subarray, $key ) );
            }
        }

        return $results;
    }


    /**
     * Cron-Task: Delete cache images with no access for a month
     */
    public function postGalleryDeleteCachedImages() {
        $uploadDir = wp_upload_dir();
        file_put_contents( $uploadDir['path'] . '/_deleteCache.txt', date( 'd.M.Y H:i:s' ) . "\r\n", FILE_APPEND );

        $cacheFolder = $uploadDir['path'] . '/cache';
        foreach ( scandir( $cacheFolder ) as $file ) {
            if ( !is_dir( $cacheFolder . '/' . $file ) ) {
                $lastAccess = fileatime( $cacheFolder . '/' . $file );

                if ( $lastAccess < strtotime( '-1 month' ) ) { // older than 1 month
                    unlink( $cacheFolder . '/' . $file );
                }
            }
        }
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - PostGalleryLoader. Orchestrates the hooks of the plugin.
     * - PostGalleryI18n. Defines internationalization functionality.
     * - PostGalleryAdmin. Defines all hooks for the admin area.
     * - PostGalleryPublic. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadDependencies() {

        $this->loader = new PostGalleryLoader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the PostGalleryI18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function setLocale() {

        $pluginI18n = new PostGalleryI18n();
        $pluginI18n->setDomain( $this->getPostGallery() );

        $this->loader->addAction( 'plugins_loaded', $pluginI18n, 'loadPluginTextdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function defineAdminHooks() {

        $pluginAdmin = new PostGalleryAdmin( $this->getPostGallery(), $this->getVersion() );

        $this->loader->addAction( 'admin_enqueue_scripts', $pluginAdmin, 'enqueueStyles' );
        $this->loader->addAction( 'admin_enqueue_scripts', $pluginAdmin, 'enqueueScripts' );

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function definePublicHooks() {

        $pluginPublic = new PostGalleryPublic( $this->getPostGallery(), $this->getVersion() );

        $this->loader->addAction( 'wp_enqueue_scripts', $pluginPublic, 'enqueueStyles' );
        $this->loader->addAction( 'wp_enqueue_scripts', $pluginPublic, 'enqueueScripts' );

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function getPostGallery() {
        return $this->pluginName;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    PostGalleryLoader    Orchestrates the hooks of the plugin.
     */
    public function getLoader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function getVersion() {
        return $this->version;
    }


    /**
     * Sorting an image-array
     *
     * @param {array} $images
     * @return {array}
     */
    public static function sortImages( $images, $postid ) {
        // get post in default language
        $orgPost = PostGallery::getOrgPost( $postid );
        if ( !empty( $orgPost ) ) {
            $post = $orgPost;
            $postid = $orgPost->ID;
        }
        $sort = get_post_meta( $postid, 'postgalleryImagesort', true );

        $sortimages = array();

        if ( !empty( $sort ) ) {
            $count = 0;
            $sort_array = explode( ',', $sort );
            foreach ( $sort_array as $key ) {
                if ( !empty( $images[$key] ) ) {
                    $sortimages[$key] = $images[$key];
                    unset( $images[$key] );
                }
                $count += 1;
            }
        }
        $sortimages = array_merge( $sortimages, $images );

        return $sortimages;
    }

    /**
     * Return an image-array
     *
     * @param int $postid
     * @return array
     */
    public static function getImages( $postid = null ) {
        if ( empty( $postid ) && empty( $GLOBALS['post'] ) ) {
            return;
        }
        if ( empty( $postid ) ) {
            $postid = $GLOBALS['post']->ID;
            $post = $GLOBALS['post'];
        }

        // check if image list is in cache
        if ( isset( PostGallery::$cachedImages[$postid] ) ) {
            return PostGallery::$cachedImages[$postid];
        }

        if ( empty( $post ) ) {
            $post = get_post( $postid );
        }
        // get post in default language
        $orgPost = PostGallery::getOrgPost( $postid );
        if ( !empty( $orgPost ) ) {
            $post = $orgPost;
            $postid = $orgPost->ID;
            if ( isset( PostGallery::$cachedImages[$postid] ) ) {
                // check if image list is in cache
                return PostGallery::$cachedImages[$postid];
            }
        }

        if ( empty( $post ) ) {
            return;
        }

        $uploads = wp_upload_dir();

        //$imageDir = strtolower(str_replace('http://', '', esc_url($post->post_title)));
        $imageDir = PostGallery::getImageDir( $post );
        $uploadDir = $uploads['basedir'] . '/gallery/' . $imageDir;
        $uploadFullUrl = $uploads['baseurl'] . '/gallery/' . $imageDir;
        $uploadUrl = str_replace( get_bloginfo( 'wpurl' ), '', $uploadFullUrl );
        $images = array();

        if ( file_exists( $uploadDir ) && is_dir( $uploadDir ) ) {
            $titles = get_post_meta( $postid, 'postgalleryTitles', true );
            $descs = get_post_meta( $postid, 'postgalleryDescs', true );
            $alts = get_post_meta( $postid, 'postgalleryAltAttributes', true );
            $imageOptions = get_post_meta( $postid, 'postgalleryImageOptions', true );
            $dir = scandir( $uploadDir );

            if ( !is_array( $titles ) ) {
                $titles = json_decode( json_encode( $titles ), true );
            }
            if ( !is_array( $descs ) ) {
                $descs = json_decode( json_encode( $descs ), true );
            }
            if ( !is_array( $alts ) ) {
                $alts = json_decode( json_encode( $alts ), true );
            }
            if ( !is_array( $imageOptions ) ) {
                $imageOptions = json_decode( json_encode( $imageOptions ), true );
            }

            foreach ( $dir as $file ) {
                if ( !is_dir( $uploadDir . '/' . $file ) ) {

                    $images[$file] = array(
                        'filename' => $file,
                        'path' => $uploadUrl . '/' . $file,
                        'url' => $uploadFullUrl . '/' . $file,
                        'thumbURL' => get_bloginfo( 'wpurl' ) . '/?loadThumb&amp;path=' . $uploadUrl . '/' . $file,
                        'title' => !empty( $titles[$file] ) ? $titles[$file] : '',
                        'desc' => !empty( $descs[$file] ) ? $descs[$file] : '',
                        'alt' => !empty( $alts[$file] ) ? $alts[$file] : '',
                        'post_id' => $postid,
                        'post_title' => get_the_title( $postid ),
                        'imageOptions' => !empty( $imageOptions[$file] ) ? $imageOptions[$file] : '',
                    );
                }
            }
        }

        $images = PostGallery::sortImages( $images, $postid );
        PostGallery::$cachedImages[$postid] = $images;
        return $images;
    }

    /**
     * Return an image-array with resized images
     *
     * @param int $postid
     * @param array $args
     * @return array
     */
    public static function getImagesResized( $postid = 0, $args = array() ) {
        $images = PostGallery::getImages( $postid );

        return PostGallery::getPicsResized( $images, $args );
    }

    /**
     * Returns a comma seperated list with images
     *
     * @param {int} $postid
     * @param {array} $args (singlequotes, quotes)
     * @return {string}
     */
    public static function getImageString( $postid = null, $args = array() ) {
        if ( empty( $postid ) ) {
            global $postid;
        }
        $images = PostGallery::getImages( $postid );
        if ( empty( $images ) ) {
            return '';
        }
        $imageList = array();
        foreach ( $images as $image ) {
            $imageList[] = $image['path'];
        }
        $imageString = '';
        if ( !empty( $args['quotes'] ) ) {
            $imageString = '"' . implode( '","', $imageList ) . '"';
        } elseif ( !empty( $args['singlequotes'] ) ) {
            $imageString = "'" . implode( "','", $imageList ) . "'";
        } else {
            $imageString = implode( ',', $imageList );
        }

        return $imageString;
    }

    /**
     * Returns a post in default language
     *
     * @param {int} $post_id
     * @return boolean|object
     */
    public static function getOrgPost( $currentPostId ) {
        if ( class_exists( 'SitePress' ) ) {
            global $locale, $sitepress;

            $orgPostId = icl_object_id( $currentPostId, 'any', true, $sitepress->get_default_language() );
            //icl_ob
            if ( $currentPostId !== $orgPostId ) {
                $mainLangPost = get_post( $orgPostId );
                return $mainLangPost;
            }
        }
        return false;
    }

    /**
     * Get path to thumb.php
     *
     * @param string $filepath
     * @param array $args
     * @return string
     */
    static function getThumbUrl( $filepath, $args = array() ) {
        $thumb = PostGallery::getThumb( $filepath, $args );
        $thumbUrl = ( !empty( $thumb['url'] ) ? $thumb['url'] : get_bloginfo( 'wpurl' ) . '/' . $thumb['path'] );
        $thumbUrl = str_replace( '//wp-content', '/wp-content', $thumbUrl );

        return $thumbUrl;
    }

    /**
     * Get thumb (wrapper for Thumb->getThumb()
     *
     * @param string $filepath
     * @param array $args
     * @return array
     */
    static function getThumb( $filepath, $args = array() ) {
        if ( empty( $args['width'] ) ) {
            $args['width'] = 1000;
        }
        if ( empty( $args['height'] ) ) {
            $args['height'] = 1000;
        }
        if ( !isset( $args['scale'] ) ) {
            $args['scale'] = 1;
        }
        $args['path'] = str_replace( get_bloginfo( 'wpurl' ), '', $filepath );

        $thumbInstance = Thumb::getInstance();
        $thumb = $thumbInstance->getThumb( $args );

        return $thumb;
    }

    /**
     * Returns the foldername for the gallery
     *
     * @param object $wpost
     * @return string
     */
    static function getImageDir( $wpost ) {
        $postName = $wpost->post_title;
        $postId = $wpost->ID;

        if ( isset( PostGallery::$cachedFolders[$postId] ) ) {
            return PostGallery::$cachedFolders[$postId];
        }

        $search = array( 'ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', '°', '+', '&amp;', '&', '€', 'ß', '–' );
        $replace = array( 'ae', 'ue', 'oe', 'ae', 'ue', 'oe', '', '-', '-', '-', 'E', 'ss', '-' );

        $postName = str_replace( $search, $replace, $postName );

        $uploads = wp_upload_dir();
        $oldImageDir = strtolower( str_replace( 'http://', '', esc_url( $postName ) ) );
        $newImageDir = strtolower(
            sanitize_file_name( str_replace( '&amp;', '-', $postName )
            )
        );

        $baseDir = $uploads['basedir'] . '/gallery/';

        if ( empty( $newImageDir ) ) {
            return false;
        }

        // for very old postgallery who used wrong dir
        PostGallery::renameDir( $baseDir . $oldImageDir, $baseDir . $newImageDir );

        // for old postgallery who dont uses post-id in folder
        $oldImageDir = $newImageDir;
        $newImageDir = $newImageDir . '_' . $postId;
        PostGallery::renameDir( $baseDir . $oldImageDir, $baseDir . $newImageDir );

        PostGallery::$cachedFolders[$postId] = $newImageDir;

        return $newImageDir;
    }

    static function renameDir( $oldDir, $newDir ) {
        if ( $newDir == $oldDir ) {
            return;
        }
        if ( is_dir( $oldDir ) && !is_dir( $newDir ) ) {
            //rename($old_dir, $new_dir);
            if ( file_exists( $oldDir ) ) {
                $files = scandir( $oldDir );
                @mkdir( $newDir );
                @chmod( $newDir, octdec( '0777' ) );

                foreach ( $files as $file ) {
                    if ( !is_dir( $oldDir . '/' . $file ) ) {
                        copy( $oldDir . '/' . $file, $newDir . '/' . $file );
                        unlink( $oldDir . '/' . $file );
                    }
                }
                @rmdir( $oldDir );

                return $newDir;
            }
        }

        // fail
        return $oldDir;
    }


    /**
     * Generate thumb-path for an array of pics
     *
     * @param array $pics
     * @param array $args
     * @return array
     */
    static function getPicsResized( $pics, $args ) {
        if ( !is_array( $pics ) ) {
            return $pics;
        }
        $newPics = array();
        foreach ( $pics as $pic ) {
            // create resized image
            if ( is_array( $pic ) ) {
                if ( !empty( $pic['url'] ) ) {
                    $newPic = PostGallery::getThumb( $pic['url'], $args );
                } else if ( !empty( $pic['path'] ) ) {
                    $newPic = PostGallery::getThumb( $pic['path'], $args );
                }
            } else {
                $newPic = PostGallery::getThumb( $pic, $args );
            }
            if ( !empty( $newPic ) && is_array( $pic ) ) {
                // add info (title and description)
                $newPics[] = array_merge( $pic, $newPic );
            } else if ( !empty( $newPic ) ) {
                $newPics[] = $newPic;
            } else {
                $newPics[] = $pic;
            }
        }

        return $newPics;
    }

    /**
     * Check if post has a thumb or a postgallery-image
     *
     * @param int $postid
     * @return int
     */
    static function hasPostThumbnail( $postid = 0 ) {
        if ( empty( $postid ) && empty( $GLOBALS['post'] ) ) {
            return;
        }
        if ( empty( $postid ) ) {
            $postid = $GLOBALS['post']->ID;
        }

        if ( empty( $postid ) ) {
            return false;
        }

        if ( has_post_thumbnail( $postid ) || is_admin() ) {
            return has_post_thumbnail( $postid );
        } else {
            return count( PostGallery::getImages( $postid ) );
        }
    }

    /**
     * Adds post-type gallery
     */
    public function addPostTypeGallery() {
        register_post_type( 'gallery', array(
            'labels' => array(
                'name' => __( 'Galleries', $this->textdomain ),
                'singular_name' => __( 'Gallery', $this->textdomain ),
            ),
            'taxonomies' => array( 'category' ),
            'menu_icon' => 'dashicons-format-gallery',
            'public' => true,
            'has_archive' => true,
            'show_in_nav_menus' => true,
            'show_ui' => true,
            'capability_type' => 'post',
            'hierarchical' => true,
            'supports' => array(
                'title',
                'author',
                'editor',
                'thumbnail',
                'trackbacks',
                'custom-fields',
                'revisions',
            ),
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'excerpt' => true,
        ) );
    }

    public static function getOptions() {
        return array(
            'debugmode' => get_theme_mod( 'postgallery_postgalleryDebugmode', false ),
            'sliderType' => get_theme_mod( 'postgallery_sliderType', 'owl' ),
            'globalPosition' => get_theme_mod( 'postgallery_globalPosition', 'bottom' ),

            'globalTemplate' => get_theme_mod( 'postgallery_globalTemplate' ),
            'thumbWidth' => get_theme_mod( 'postgallery_thumbWidth', 150 ),
            'thumbHeight' => get_theme_mod( 'postgallery_thumbHeight', 150 ),
            'thumbScale' => get_theme_mod( 'postgallery_thumbScale', '1' ),
            'sliderOwlConfig' => get_theme_mod( 'postgallery_thumbScale', "items: 1,\nnav: 1,\ndots: 1,\nloop: 1," ),
            'stretchImages' => get_theme_mod( 'postgallery_stretchImages', false ),

            'enableLitebox' => get_theme_mod( 'postgallery_enableLitebox', true ),
            'liteboxTemplate' => get_theme_mod( 'postgallery_liteboxTemplate', 'default' ),
            'owlTheme' => get_theme_mod( 'postgallery_owlTheme', 'default' ),
            'clickEvents' => get_theme_mod( 'postgallery_clickEvents', true ),
            'keyEvents' => get_theme_mod( 'postgallery_keyEvents', true ),
            'asBg' => get_theme_mod( 'postgallery_asBg', false ),
            'owlConfig' => get_theme_mod( 'postgallery_owlConfig', 'items: 1' ),
            'owlThumbConfig' => get_theme_mod( 'postgallery_owlThumbConfig', '' ),
        );
    }

}
