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
 * @package           Recipe_Pro
 *
 * @wordpress-plugin
 * Plugin Name:       RecipePro
 * Plugin URI:        recipepro.com
 * Description:       The best recipe plugin for Wordpress.
 * Version:           1.0.0
 * Author:            Josh Frankamp
 * Author URI:        http://www.joshuafrankamp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       recipe-pro
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function setup_constants() {
	// Plugin version.
	if ( ! defined( 'RECIPE_PRO_VERSION' ) ) {
		define( 'RECIPE_PRO_VERSION', '1.0.0' );
	}

	// Plugin Folder Path.
	if ( ! defined( 'RECIPE_PRO_PLUGIN_DIR' ) ) {
		define( 'RECIPE_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	// Plugin Folder URL.
	if ( ! defined( 'RECIPE_PRO_PLUGIN_URL' ) ) {
		define( 'RECIPE_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	// Plugin Root File.
	if ( ! defined( 'RECIPE_PRO_PLUGIN_FILE' ) ) {
		define( 'RECIPE_PRO_PLUGIN_FILE', __FILE__ );
	}
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-recipe-pro-activator.php
 */
function activate_recipe_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-recipe-pro-activator.php';
	Recipe_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-recipe-pro-deactivator.php
 */
function deactivate_recipe_pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-recipe-pro-deactivator.php';
	Recipe_Pro_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_recipe_pro' );
register_deactivation_hook( __FILE__, 'deactivate_recipe_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-recipe-pro.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_recipe_pro() {
	setup_constants();
	$plugin = new Recipe_Pro();
	$plugin->run();
}
run_recipe_pro();
