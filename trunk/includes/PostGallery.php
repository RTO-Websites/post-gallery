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
class PostGallery {
	static $cached_images = array ();
	static $cached_folders = array ();

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      PostGalleryLoader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $pluginName    The string used to uniquely identify this plugin.
	 */
	protected $pluginName;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
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
	public function __construct() {

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
	public static function sort_images ( $images, $postid )
	{
		// get post in default language
		$org_post = PostGallery::get_org_post ( $postid );
		if ( !empty( $org_post ) ) {
			$post = $org_post;
			$postid = $org_post->ID;
		}
		$sort = get_post_meta ( $postid, 'postgallery_imagesort', true );
		if ( empty( $sort ) ) {
			// for compatibility with old version
			$sort = get_post_meta ( $postid, 'imagesort', true );
		}
		$sortimages = array ();

		if ( !empty( $sort ) ) {
			$count = 0;
			$sort_array = explode ( ',', $sort );
			foreach ( $sort_array as $key ) {
				if ( !empty( $images[ $key ] ) ) {
					$sortimages[ $key ] = $images[ $key ];
					unset( $images[ $key ] );
				}
				$count += 1;
			}
		}
		$sortimages = array_merge ( $sortimages, $images );

		return $sortimages;
	}

	/**
	 * Return an image-array
	 *
	 * @param type $postid
	 * @return type
	 */
	public static function get_images ( $postid = null )
	{
		if ( empty( $postid ) && empty( $GLOBALS[ 'post' ] ) ) {
			return;
		}
		if ( empty( $postid ) ) {
			$postid = $GLOBALS[ 'post' ]->ID;
			$post = $GLOBALS[ 'post' ];
		}

		// check if image list is in cache
		if ( isset( PostGallery::$cached_images[ $postid ] ) ) {
			return PostGallery::$cached_images[ $postid ];
		}

		if ( empty( $post ) ) {
			$post = get_post ( $postid );
		}
		// get post in default language
		$org_post = PostGallery::get_org_post ( $postid );
		if ( !empty( $org_post ) ) {
			$post = $org_post;
			$postid = $org_post->ID;
			if ( isset( PostGallery::$cached_images[ $postid ] ) ) {
				// check if image list is in cache
				return PostGallery::$cached_images[ $postid ];
			}
		}

		if ( empty( $post ) ) {
			return;
		}

		$uploads = wp_upload_dir ();

		//$image_dir = strtolower(str_replace('http://', '', esc_url($post->post_title)));
		$image_dir = PostGallery::get_image_dir ( $post );
		$upload_dir = $uploads[ 'basedir' ] . '/gallery/' . $image_dir;
		$upload_full_url = $uploads[ 'baseurl' ] . '/gallery/' . $image_dir;
		$upload_url = str_replace ( get_bloginfo ( 'wpurl' ), '', $upload_full_url );
		$images = array ();

		if ( file_exists ( $upload_dir ) && is_dir ( $upload_dir ) ) {
			$dir = scandir ( $upload_dir );
			foreach ( $dir as $file ) {
				if ( !is_dir ( $upload_dir . '/' . $file ) ) {
					$images[ $file ] = array (
						'filename' => $file,
						'path'     => $upload_url . '/' . $file,
						'url'     => $upload_full_url . '/' . $file,
						'thumbURL' => get_bloginfo ( 'wpurl' ) . '/?load_thumb&amp;path=' . $upload_url . '/' . $file,
					);
				}
			}
		}
		$images = PostGallery::sort_images ( $images, $postid );
		PostGallery::$cached_images[ $postid ] = $images;
		return $images;
	}

	/**
	 * Return an image-array with resized images
	 *
	 * @param type $postid
	 * @return type
	 */
	public static function get_images_resized ( $postid = 0, $args )
	{
		$images = PostGallery::get_images ( $postid );

		return PostGallery::get_pics_resized ( $images, $args );
	}

	/**
	 * Returns a comma seperated list with images
	 *
	 * @param {int} $postid
	 * @param {array} $args (singlequotes, quotes)
	 * @return {string}
	 */
	public static function get_image_string ( $postid = null, $args = array () )
	{
		$images = PostGallery::get_images ( $postid );
		if ( empty( $images ) ) {
			return '';
		}
		$image_list = array ();
		foreach ( $images as $image ) {
			$image_list[] = $image[ 'path' ];
		}
		$image_string = '';
		if ( !empty( $args[ 'quotes' ] ) ) {
			$image_string = '"' . implode ( '","', $image_list ) . '"';
		} elseif ( !empty( $args[ 'singlequotes' ] ) ) {
			$image_string = "'" . implode ( "','", $image_list ) . "'";
		} else {
			$image_string = implode ( ',', $image_list );
		}

		return $image_string;
	}

	/**
	 * Returns a post in default language
	 *
	 * @param {int} $post_id
	 * @return boolean|object
	 */
	public static function get_org_post ( $cur_post_id )
	{
		if ( class_exists ( 'SitePress' ) ) {
			global $locale, $sitepress;

			$org_post_id = icl_object_id ( $cur_post_id, 'any', true, $sitepress->get_default_language () );
			//icl_ob
			if ( $cur_post_id !== $org_post_id ) {
				$main_lang_post = get_post ( $org_post_id );
				return $main_lang_post;
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
	static function get_thumb ( $filepath, $args = array () )
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
		$args[ 'path' ] = str_replace ( get_bloginfo ( 'wpurl' ), '', $filepath );

		$thumb_instance = Thumb::get_instance ();
		$thumb = $thumb_instance->get_thumb ( $args );

		$thumb_url = ( !empty( $thumb[ 'url' ] ) ? $thumb[ 'url' ] : get_bloginfo ( 'wpurl' ) . '/' . $args[ 'path' ] );
		$thumb_url = str_replace ( '//wp-content', '/wp-content', $thumb_url );

		return $thumb_url;
	}

	/**
	 * Returns the foldername for the gallery
	 *
	 * @param type $post_name
	 * @return string
	 */
	static function get_image_dir ( $wpost )
	{
		$post_name = $wpost->post_title;
		$post_id = $wpost->ID;

		if ( isset( PostGallery::$cached_folders[ $post_id ] ) ) {
			return PostGallery::$cached_folders[ $post_id ];
		}

		$search = array ( 'ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', '°', '+', '&amp;', '&' );
		$replace = array ( 'ae', 'ue', 'oe', 'ae', 'ue', 'oe', '', '-', '-', '-' );
		$uploads = wp_upload_dir ();
		$old_image_dir = strtolower ( str_replace ( 'http://', '', esc_url ( $post_name ) ) );
		$new_image_dir = str_replace (
			$search, $replace, strtolower (
				sanitize_file_name ( str_replace ( '&amp;', '-', $post_name )
				)
			)
		);

		$base_dir = $uploads[ 'basedir' ] . '/gallery/';

		if ( empty( $new_image_dir ) ) {
			return false;
		}

		// for very old swapper who used wrong dir
		PostGallery::rename_dir ( $base_dir . $old_image_dir, $base_dir . $new_image_dir );

		// for old swapper who dont uses post-id in folder
		$old_image_dir = $new_image_dir;
		$new_image_dir = $new_image_dir . '_' . $post_id;
		PostGallery::rename_dir ( $base_dir . $old_image_dir, $base_dir . $new_image_dir );

		PostGallery::$cached_folders[ $post_id ] = $new_image_dir;

		return $new_image_dir;
	}

	static function rename_dir ( $old_dir, $new_dir )
	{
		if ( $new_dir == $old_dir ) {
			return;
		}
		if ( is_dir ( $old_dir ) && !is_dir ( $new_dir ) ) {
			//rename($old_dir, $new_dir);
			if ( file_exists ( $old_dir ) ) {
				$files = scandir ( $old_dir );
				@mkdir ( $new_dir );
				@chmod ( $new_dir, octdec ( '0777' ) );

				foreach ( $files as $file ) {
					if ( !is_dir ( $old_dir . '/' . $file ) ) {
						copy ( $old_dir . '/' . $file, $new_dir . '/' . $file );
						unlink ( $old_dir . '/' . $file );
					}
				}
				@rmdir ( $old_dir );

				return $new_dir;
			}
		}

		// fail
		return $old_dir;
	}


	/**
	 * Generate thumb-path for an array of pics
	 *
	 * @param type $pics
	 * @param type $args
	 * @return type
	 */
	static function get_pics_resized ( $pics, $args )
	{
		if ( !is_array ( $pics ) ) {
			return $pics;
		}
		$new_pics = array ();
		foreach ( $pics as $pic ) {
			if ( is_array ( $pic ) ) {
				if ( !empty( $pic[ 'url' ] ) ) {
					$new_pic = PostGallery::get_thumb ( $pic[ 'url' ], $args );
				} else if ( !empty( $pic[ 'path' ] ) ) {
					$new_pic = PostGallery::get_thumb ( $pic[ 'path' ], $args );
				}
			} else {
				$new_pic = PostGallery::get_thumb ( $pic, $args );
			}
			if ( !empty( $new_pic ) ) {
				if ( is_array ( $pic ) ) {
					$new_pics[] = array ( 'url' => $new_pic, 'info' => $pic[ 'info' ] );
				} else {
					$new_pics[] = $new_pic;
				}
			} else {
				$new_pics[] = $pic;
			}
		}

		return $new_pics;
	}

	/**
	 * Check if post has a thumb or a postgallery-image
	 *
	 * @param type $postid
	 * @return boolean
	 */
	static function has_post_thumbnail ( $postid = 0 )
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

		if ( has_post_thumbnail ( $postid ) ) {
			return has_post_thumbnail ( $postid );
		} else {
			return count ( PostGallery::get_images ( $postid ) );
		}
	}

}
