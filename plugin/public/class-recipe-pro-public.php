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
	 * Implements the 'comment_form_after_fields' and 'comment_form_logged_in_after' action
	 *
	 * @since    1.0.0
	 */
	public function render_rating_field() {
		echo '<p class="comment-form-rating">'.
	  '<label for="recipepro-rating">'. __('Rating') . '<span class="required">*</span></label>
	  <span class="commentratingbox">';
	    //Current rating scale is 1 to 5. If you want the scale to be 1 to 10, then set the value of $i to 10.
	    for( $i=1; $i <= 5; $i++ )
	    echo '<span class="commentrating"><input type="radio" name="recipepro_rating" id="recipepro-rating" value="'. $i .'"/>'. $i .'</span>';

	  echo'</span></p>';
	}

	/**
	 * Implements the 'comment_post' action
	 *
	 * @since    1.0.0
	 */
	public function save_rating_meta_data( $comment_id ) {
		$rating = null;
		if ( ( isset( $_POST['rating'] ) ) && ( $_POST['rating'] != '') && intval($_POST['rating']) > 0 && intval($_POST['rating']) < 6 ) {
			$rating = strval( intval( $_POST['rating'] ) );
		}
		if ( $rating ) {
			add_comment_meta( $comment_id, 'recipepro_rating', $rating );
			// TODO: trigger recalc/rerender
		}
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
