<?php namespace Inc;

use Admin\PostGalleryAdmin;
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
class PostGallery
{
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

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {

        $this->pluginName = 'post-gallery';
        $this->version = '1.0.0';

        $this->loadDependencies();
        $this->setLocale();
        $this->defineAdminHooks();
        $this->definePublicHooks();

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
    private function loadDependencies()
    {

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
    private function setLocale()
    {

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
    private function defineAdminHooks()
    {

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
    private function definePublicHooks()
    {

        $pluginPublic = new PostGalleryPublic( $this->getPostGallery(), $this->getVersion() );

        $this->loader->addAction( 'wp_enqueue_scripts', $pluginPublic, 'enqueueStyles' );
        $this->loader->addAction( 'wp_enqueue_scripts', $pluginPublic, 'enqueueScripts' );

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function getPostGallery()
    {
        return $this->pluginName;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    PostGalleryLoader    Orchestrates the hooks of the plugin.
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function getVersion()
    {
        return $this->version;
    }


    /**
     * Sorting an image-array
     *
     * @param {array} $images
     * @return {array}
     */
    public static function sortImages( $images, $postid )
    {
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
                if ( !empty( $images[ $key ] ) ) {
                    $sortimages[ $key ] = $images[ $key ];
                    unset( $images[ $key ] );
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
     * @param type $postid
     * @return type
     */
    public static function getImages( $postid = null )
    {
        if ( empty( $postid ) && empty( $GLOBALS[ 'post' ] ) ) {
            return;
        }
        if ( empty( $postid ) ) {
            $postid = $GLOBALS[ 'post' ]->ID;
            $post = $GLOBALS[ 'post' ];
        }

        // check if image list is in cache
        if ( isset( PostGallery::$cachedImages[ $postid ] ) ) {
            return PostGallery::$cachedImages[ $postid ];
        }

        if ( empty( $post ) ) {
            $post = get_post( $postid );
        }
        // get post in default language
        $orgPost = PostGallery::getOrgPost( $postid );
        if ( !empty( $orgPost ) ) {
            $post = $orgPost;
            $postid = $orgPost->ID;
            if ( isset( PostGallery::$cachedImages[ $postid ] ) ) {
                // check if image list is in cache
                return PostGallery::$cachedImages[ $postid ];
            }
        }

        if ( empty( $post ) ) {
            return;
        }

        $uploads = wp_upload_dir();

        //$imageDir = strtolower(str_replace('http://', '', esc_url($post->post_title)));
        $imageDir = PostGallery::getImageDir( $post );
        $uploadDir = $uploads[ 'basedir' ] . '/gallery/' . $imageDir;
        $uploadFullUrl = $uploads[ 'baseurl' ] . '/gallery/' . $imageDir;
        $uploadUrl = str_replace( get_bloginfo( 'wpurl' ), '', $uploadFullUrl );
        $images = array();

        if ( file_exists( $uploadDir ) && is_dir( $uploadDir ) ) {
            $dir = scandir( $uploadDir );
            foreach ( $dir as $file ) {
                if ( !is_dir( $uploadDir . '/' . $file ) ) {
                    $images[ $file ] = array(
                        'filename' => $file,
                        'path'     => $uploadUrl . '/' . $file,
                        'url'      => $uploadFullUrl . '/' . $file,
                        'thumbURL' => get_bloginfo( 'wpurl' ) . '/?loadThumb&amp;path=' . $uploadUrl . '/' . $file,
                    );
                }
            }
        }
        $images = PostGallery::sortImages( $images, $postid );
        PostGallery::$cachedImages[ $postid ] = $images;
        return $images;
    }

    /**
     * Return an image-array with resized images
     *
     * @param type $postid
     * @return type
     */
    public static function getImagesResized( $postid = 0, $args )
    {
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
    public static function getImageString( $postid = null, $args = array() )
    {
        $images = PostGallery::getImages( $postid );
        if ( empty( $images ) ) {
            return '';
        }
        $imageList = array();
        foreach ( $images as $image ) {
            $imageList[] = $image[ 'path' ];
        }
        $imageString = '';
        if ( !empty( $args[ 'quotes' ] ) ) {
            $imageString = '"' . implode( '","', $imageList ) . '"';
        } elseif ( !empty( $args[ 'singlequotes' ] ) ) {
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
    public static function getOrgPost( $currentPostId )
    {
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
     * @param type $filepath
     * @param type $args
     * @return type
     */
    static function getThumbUrl( $filepath, $args = array() )
    {
        $thumb = PostGallery::getThumb( $filepath, $args );
        $thumbUrl = ( !empty( $thumb[ 'url' ] ) ? $thumb[ 'url' ] : get_bloginfo( 'wpurl' ) . '/' . $args[ 'path' ] );
        $thumbUrl = str_replace( '//wp-content', '/wp-content', $thumbUrl );

        return $thumbUrl;
    }

    /**
     * Get thumb (wrapper for Thumb->getThumb()
     *
     * @param type $filepath
     * @param type $args
     * @return type
     */
    static function getThumb( $filepath, $args = array() )
    {
        if ( empty( $args[ 'width' ] ) ) {
            $args[ 'width' ] = 1000;
        }
        if ( empty( $args[ 'height' ] ) ) {
            $args[ 'height' ] = 1000;
        }
        if ( !isset( $args[ 'scale' ] ) ) {
            $args[ 'scale' ] = 1;
        }
        $args[ 'path' ] = str_replace( get_bloginfo( 'wpurl' ), '', $filepath );

        $thumbInstance = Thumb::getInstance();
        $thumb = $thumbInstance->getThumb( $args );

        return $thumb;
    }

    /**
     * Returns the foldername for the gallery
     *
     * @param type $post_name
     * @return string
     */
    static function getImageDir( $wpost )
    {
        $postName = $wpost->post_title;
        $postId = $wpost->ID;

        if ( isset( PostGallery::$cachedFolders[ $postId ] ) ) {
            return PostGallery::$cachedFolders[ $postId ];
        }

        $search = array( 'ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', '°', '+', '&amp;', '&' );
        $replace = array( 'ae', 'ue', 'oe', 'ae', 'ue', 'oe', '', '-', '-', '-' );
        $uploads = wp_upload_dir();
        $oldImageDir = strtolower( str_replace( 'http://', '', esc_url( $postName ) ) );
        $newImageDir = str_replace(
            $search, $replace, strtolower(
                sanitize_file_name( str_replace( '&amp;', '-', $postName )
                )
            )
        );

        $baseDir = $uploads[ 'basedir' ] . '/gallery/';

        if ( empty( $newImageDir ) ) {
            return false;
        }

        // for very old swapper who used wrong dir
        PostGallery::renameDir( $baseDir . $oldImageDir, $baseDir . $newImageDir );

        // for old swapper who dont uses post-id in folder
        $oldImageDir = $newImageDir;
        $newImageDir = $newImageDir . '_' . $postId;
        PostGallery::renameDir( $baseDir . $oldImageDir, $baseDir . $newImageDir );

        PostGallery::$cachedFolders[ $postId ] = $newImageDir;

        return $newImageDir;
    }

    static function renameDir( $oldDir, $newDir )
    {
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
     * @param type $pics
     * @param type $args
     * @return type
     */
    static function getPicsResized( $pics, $args )
    {
        if ( !is_array( $pics ) ) {
            return $pics;
        }
        $newPics = array();
        foreach ( $pics as $pic ) {
            // create resized image
            if ( is_array( $pic ) ) {
                if ( !empty( $pic[ 'url' ] ) ) {
                    $newPic = PostGallery::getThumb( $pic[ 'url' ], $args );
                } else if ( !empty( $pic[ 'path' ] ) ) {
                    $newPic = PostGallery::getThumb( $pic[ 'path' ], $args );
                }
            } else {
                $newPic = PostGallery::getThumb( $pic, $args );
            }
            if ( !empty( $newPic ) ) {
                // add info (title and description)
                if ( is_array( $pic ) ) {
                    $newPic[ 'info' ] = $pic[ 'info' ];
                    $newPics[] = $newPic;
                }
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
     * @param type $postid
     * @return boolean
     */
    static function hasPostThumbnail( $postid = 0 )
    {
        if ( empty( $postid ) && empty( $GLOBALS[ 'post' ] ) ) {
            return;
        }
        if ( empty( $postid ) ) {
            $postid = $GLOBALS[ 'post' ]->ID;
        }

        if ( empty( $postid ) ) {
            return false;
        }

        if ( has_post_thumbnail( $postid ) ) {
            return has_post_thumbnail( $postid );
        } else {
            return count( PostGallery::getImages( $postid ) );
        }
    }

}
