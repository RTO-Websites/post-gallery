<?php

use Lib\PostGallery;
use Lib\PostGalleryActivator;
use Lib\PostGalleryDeactivator;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/RTO-Websites/post-gallery
 * @since             1.0.0
 * @package           PostGallery
 *
 * @wordpress-plugin
 * Plugin Name:       PostGallery
 * Plugin URI:        https://github.com/RTO-Websites/post-gallery
 * Description:       Adds a gallery to every post with customizable templates, drag´n´drop upload und simple to use.
 * Version:           1.12.5
 * Author:            RTO GmbH
 * Author URI:        https://www.rto.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       postgallery
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'POSTGALLERY_VERSION', '1.12.5' );

define( 'POSTGALLERY_DIR', str_replace( '\\', '/', __DIR__ ) );
define( 'POSTGALLERY_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * The class responsible for auto loading classes.
 */
require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/PostGalleryActivator.php
 */
function activatePostGallery() {
    PostGalleryActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/PostGalleryDeactivator.php
 */
function deactivatePostGallery() {
    PostGalleryDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activatePostGallery' );
register_deactivation_hook( __FILE__, 'deactivatePostGallery' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function runPostGallery() {

    $plugin = new PostGallery();
    $plugin->run();

}
runPostGallery();
