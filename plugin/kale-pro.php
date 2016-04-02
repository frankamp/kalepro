<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.joshuafrankamp.com
 * @since             1.0.0
 * @package           Kale_Pro
 *
 * @wordpress-plugin
 * Plugin Name:       KalePro
 * Plugin URI:        kalepro.com
 * Description:       The best recipe plugin for Wordpress.
 * Version:           1.0.0
 * Author:            Josh Frankamp
 * Author URI:        http://www.joshuafrankamp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kale-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-kale-pro-activator.php
 */
function activate_kale_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kale-pro-activator.php';
	Kale_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-kale-pro-deactivator.php
 */
function deactivate_kale_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kale-pro-deactivator.php';
	Kale_Pro_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_kale_pro' );
register_deactivation_hook( __FILE__, 'deactivate_kale_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-kale-pro.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_kale_pro() {

	$plugin = new Kale_Pro();
	$plugin->run();

}
run_kale_pro();
