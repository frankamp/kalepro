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

class Recipe_Pro_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        $options = get_option( 'recipepro_settings', array() );
        foreach ( Recipe_Pro_Option_Defaults::get_labels() as $key => $value ) {
            $options['recipepro_text_label_' . $key] = $value;
        }
        add_option('recipepro_settings', $options);
    }

}
