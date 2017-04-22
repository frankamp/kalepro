<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.joshuafrankamp.com
 * @since      1.0.0
 *
 * @package    recipe-pro
 * @subpackage recipe-pro/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    recipe-pro
 * @subpackage recipe-pro/public
 * @author     Josh Frankamp <frankamp@gmail.com>
 */
class Recipe_Pro_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$options = get_option( 'recipepro_main_settings', array() ); //TODO: replace with service call
		if (strlen( $options['css'] ) > 0 ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/' . $options['css'], array(), $this->version, 'all' );	
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name . "main", plugin_dir_url( __FILE__ ) . 'js/recipe-pro-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . "print", plugin_dir_url( __FILE__ ) . 'js/printThis.js', array( 'jquery' ), $this->version, false );

	}

}
