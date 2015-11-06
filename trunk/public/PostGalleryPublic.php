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
	 * @var      string    $pluginName    The ID of this plugin.
	 */
	private $pluginName;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;


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
	 * @param      string    $pluginName       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $pluginName, $version ) {

		$this->pluginName = $pluginName;
		$this->version = $version;
		$this->options = MagicAdminPage::getOption('post-gallery');


		add_filter( 'the_content', array ( $this, 'add_gallery_to_content' ) );
		add_shortcode( 'postgallery', array ( $this, 'postgallery_shortcode' ) );
		add_action( 'plugins_loaded', array ( $this, 'postgallery_thumb' ) );
		add_action( 'plugins_loaded', array ( $this, 'get_thumb_list' ) );

		// Embed headerscript
		add_action( 'wp_head', array ( $this, 'insert_headerscript' ) );

		// Embed footer-html
		add_action('wp_footer', array($this, 'insert_footer_html'));

		add_filter( 'post_thumbnail_html', array ( $this, 'postgallery_thumbnail' ), 10, 5 );

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

		$owlPath = plugin_dir_url( __FILE__ ) . '../bower_components/owl.carousel/dist';
		wp_enqueue_style( 'owl.carousel', $owlPath . '/assets/owl.carousel.min.css' );
		wp_enqueue_style( 'owl.carousel.theme', $owlPath . '/assets/owl.theme.default.min.css' );
		wp_enqueue_style( 'animate.css', plugin_dir_url( __FILE__ ) . '../bower_components/animate.css/animate.min.css' );
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

		wp_enqueue_script( 'lazysizes', plugin_dir_url( __FILE__ )
				. '../bower_components/lazysizes'
				. '/lazysizes.min.js' );

		$owlPath = plugin_dir_url( __FILE__ ) . '../bower_components/owl.carousel/dist';
		wp_enqueue_script( 'owl.carousel', $owlPath . '/owl.carousel.min.js', array ( 'jquery' ) );

		wp_enqueue_script( $this->pluginName, plugin_dir_url( __FILE__ ) . 'js/post-gallery-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->pluginName . '-litebox', plugin_dir_url( __FILE__ ) . 'js/litebox-gallery.class.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Register request for thumbnails
	 */
	public function postgallery_thumb()
	{
		if ( isset( $_REQUEST[ 'load_thumb' ] ) ) {
			Thumb::the_thumb();
			exit();
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
	 */
	public function postgallery_thumbnail( $html, $post_id, $post_thumbnail_id, $size, $attr )
	{
		if ( '' == $html ) {
			// get id from main-language post
			if ( class_exists( 'SitePress' ) ) {
				global $sitepress;

				$post_id = icl_object_id( $post_id, 'any', true, $sitepress->get_default_language() );
			}

			$post_gallery_images = PostGallery::get_images( $post_id );
			if ( !count( $post_gallery_images ) ) {
				return $html;
			}

			$first_thumb = array_shift( $post_gallery_images );

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
					!empty( $_wp_additional_image_sizes[ $size ] )
				) {
					$width = $_wp_additional_image_sizes[ $size ][ 'width' ];
					$height = $_wp_additional_image_sizes[ $size ][ 'height' ];
				}
			}

			if ( empty( $width ) ) {
				$width = '1920';
			}
			if ( empty( $height ) ) {
				$height = '1080';
			}

			$path = $first_thumb[ 'path' ];
			$path = explode( '/wp-content/', $path );
			$path = '/wp-content/' . array_pop( $path );

			$thumb = PostGallery::get_thumb( $path, array (
				'width'  => $width,
				'height' => $height,
				'scale'  => '0'
			) );

			$html = '<img width="auto" height="auto" src="'
				. $thumb
				. '" alt="" class="attachment-' . $size . ' wp-post-image  post-image-from-postgallery" />';
		}

		return $html;
	}

	/**
	 * Adds the gallery to the_content
	 *
	 * @param type $content
	 * @return type
	 */
	public function add_gallery_to_content( $content )
	{
		$position = get_post_meta( $GLOBALS[ 'post' ]->ID, 'postgallery_position', true );
		$template = get_post_meta( $GLOBALS[ 'post' ]->ID, 'postgallery_template', true );
		if ( empty( $position ) || $position == 'global' ) {
			$position = ( !empty( $this->options[ 'global_position' ] ) ? $this->options[ 'global_position' ] : 'bottom' );
		}

		// from global
		if ( empty( $template ) || $template == 'global' ) {
			$template = ( !empty( $this->options[ 'global_template' ] ) ? $this->options[ 'global_template' ] : 'thumbs' );
		}

		if ( $position === 'top' ) {
			$content = $this->return_gallery_html( $template ) . $content;
		} else if ( $position === 'bottom' ) {
			$content = $content . $this->return_gallery_html( $template );
		}

		return $content;
	}

	/**
	 * Return the gallery-html
	 *
	 * @param type $template
	 * @return type
	 */
	public function return_gallery_html( $template, $postid = 0, $args = array () )
	{
		$custom_template_dir = get_stylesheet_directory() . '/post-gallery';
		$custom_template_dir2 = get_stylesheet_directory() . '/plugins/post-gallery';
		$default_template_dir = POSTGALLERY_DIR . '/templates';

		$images = PostGallery::get_images( $postid );
		$titles = get_post_meta( $postid, 'postgallery_titles', true );
		$descs = get_post_meta( $postid, 'postgallery_descs', true );
		$alts = get_post_meta( $postid, 'postgallery_alt_attributes', true );

		if ( empty( $template ) || $template == 'global' ) {
			$template = $this->options[ 'global_template' ];
		}

		ob_start();
		if ( file_exists( $custom_template_dir . '/' . $template . '.php' ) ) {
			require( $custom_template_dir . '/' . $template . '.php' );
		} else if ( file_exists( $custom_template_dir2 . '/' . $template . '.php' ) ) {
			require( $custom_template_dir2 . '/' . $template . '.php' );
		} else if ( file_exists( $default_template_dir . '/' . $template . '.php' ) ) {
			require( $default_template_dir . '/' . $template . '.php' );
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
	public function insert_footer_html($footer) {
		$options = $this->options;
		$template = (!empty($options['template']) ? $options['template'] : 'default');

		$custom_template_dir = get_stylesheet_directory().'/litebox';
		$default_template_dir = POSTGALLERY_DIR.'/litebox-templates';

		if (file_exists($custom_template_dir.'/'.$template.'.php')) {
			require($custom_template_dir.'/'.$template.'.php');
		} else if (file_exists($default_template_dir.'/'.$template.'.php')) {
			require($default_template_dir.'/'.$template.'.php');
		}
	}

	/**
	 * Adds shortcode for custom gallery-position
	 *
	 * @param type $args
	 * @param type $content
	 * @return {string}
	 */
	public function postgallery_shortcode( $args, $content = '' )
	{
		if ( empty( $args[ 'template' ] ) ) {
			$template = get_post_meta( $GLOBALS[ 'post' ]->ID, 'postgallery_template', true );
		} else {
			$template = $args[ 'template' ];
		}
		$postid = 0;
		if ( !empty( $args[ 'post' ] ) ) {
			$postid = $args[ 'post' ];
		}

		return $this->return_gallery_html( $template, $postid, $args );
	}


	/**
	 * Gives a url from cache
	 */
	public function get_thumb_list()
	{
		if ( isset( $_REQUEST[ 'get_fullsize_thumbs' ] ) || isset( $_REQUEST[ 'get_thumb_list' ] ) ) {

			$_SESSION[ 'swapper_window_size' ] = array (
				'width'  => $_REQUEST[ 'width' ],
				'height' => $_REQUEST[ 'height' ]
			);

			if ( empty( $_REQUEST[ 'pics' ] ) ) {
				die( '{}' );
			}
			$pics = ( $_REQUEST[ 'pics' ] );

			if ( !empty( $pics ) ) {
				$pics = PostGallery::get_pics_resized( $pics, array (
					'width'  => $_REQUEST[ 'width' ],
					'height' => $_REQUEST[ 'height' ],
					'scale'  => ( !isset( $_REQUEST[ 'scale' ] ) ? 1 : $_REQUEST[ 'scale' ] ),
				) );
			}
			echo json_encode( $pics );

			exit();
		}
	}

	public function insert_headerscript( $header )
	{

		// script for websiteurl
		$script = '<script type="text/javascript">';
		$script .= 'var websiteUrl = "' . get_bloginfo( 'wpurl' ) . '";';
		$script .= 'var pluginUrl = "' . WP_PLUGIN_URL . '";';
		$script .= 'var liteboxOwlConfig = {' . $this->options['owlConfig'] . '};';
		$script .= '</script>';

		$header = $header . $script;

		echo $header;
	}
}
