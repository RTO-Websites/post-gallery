<?php namespace Inc;

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/RTO-Websites/post-gallery
 * @since      1.0.0
 *
 * @package    PostGallery
 * @subpackage PostGallery/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    PostGallery
 * @subpackage PostGallery/includes
 * @author     RTO GmbH
 */
class PostGalleryActivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// add cron to delete old cache-images
		if ( !wp_next_scheduled( 'cronPostGalleryDeleteCachedImages' ) ) {
			wp_schedule_event( time(), 'daily', 'cronPostGalleryDeleteCachedImages' );
		}
	}
}
