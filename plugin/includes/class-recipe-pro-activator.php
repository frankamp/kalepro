<?php

/**
 * Fired during plugin activation
 *
 * @link       http://www.joshuafrankamp.com
 * @since      1.0.0
 *
 * @package    recipe-pro
 * @subpackage recipe-pro/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    recipe-pro
 * @subpackage Recipe_Pro/includes
 * @author     Josh Frankamp <frankamp@gmail.com>
 */
require_once __DIR__.'/class-option-defaults.php';
require_once __DIR__.'/../admin/class-recipe-pro-main-page.php';

class Recipe_Pro_Activator {
    
    /**
     * Sets up label defaults if they don't exist.
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        if (!function_exists('version_compare') || !version_compare(PHP_VERSION, '5.6.0', '>=')) {
            deactivate_plugins(dirname( dirname( __FILE__ ) ) . '/recipe-pro.php');
            die("You must have PHP version 5.6 or higher to use this plugin.");
        }
        $main = new Recipe_Pro_Main_Page();
        $main->ensure_defaults();
        $options = get_option( 'recipepro_settings', array() );
        foreach ( Recipe_Pro_Option_Defaults::get_labels() as $key => $value ) {
            if (! array_key_exists('recipepro_text_label_' . $key, $options) || strlen($options['recipepro_text_label_' . $key]) == 0 ) {
                $options['recipepro_text_label_' . $key] = $value;
            }
        }
        update_option('recipepro_settings', $options, false);
    }

}
