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
	 * Implements the 'comment_text' filter
	 *
	 * @since    1.0.0
	 */
	public function display_rating( $comment_text ) {
		$rating = get_comment_meta( get_comment_ID(), 'recipepro_rating', true );
		if ( intval( $rating ) > 0 && intval( $rating ) < 6 ) {
			$rating = intval( $rating );
			$comment_text = $comment_text . '<div class="rp-stars">' ;
			for( $i=1; $i <= 5; $i++ ) {
				$comment_text = $comment_text . '<span class="rp-star '. ($rating >= $i ? 'rp-star-active' : '') .'" title="'. $rating . ' star'. ($rating > 1 ? 's' : '') . '"></span>';
			}
			$comment_text = $comment_text . '</div>';
		}
		return $comment_text;
	}

	/**
	 * Implements the 'comment_form_after_fields' and 'comment_form_logged_in_after' action
	 *
	 * @since    1.0.0
	 */
	public function render_rating_field() {
	  echo '<p class="comment-form-rating"><label>Please rate:</label><div class="rp-stars">';
	  for( $i=5; $i > 0; $i-- ) {
	    echo '<input type="radio" class="rp-star rp-star-'. $i .'" style="display:none;" id="rp-star'. $i .'" name="recipepro_rating" value="'. $i .'" /><label class="rp-star rp-star'. $i .'" for="rp-star'. $i .'" title="'. $i .' star'. ($i > 1 ? 's' : '') .'"></label>';
	  }
	  echo'</div></p>';
	}

	/**
	 * Implements the 'comment_post' action
	 *
	 * @since    1.0.0
	 */
	public function save_rating_meta_data( $comment_id ) {
		$rating = null;
		if ( ( isset( $_POST['recipepro_rating'] ) ) && ( $_POST['recipepro_rating'] != '') && intval($_POST['recipepro_rating']) > 0 && intval($_POST['recipepro_rating']) < 6 ) {
			$rating = strval( intval( $_POST['recipepro_rating'] ) );
		}
		if ( $rating ) {
			add_comment_meta( $comment_id, 'recipepro_rating', $rating );
			$comment = get_comment( $comment_id );
			$post_comments = get_comments( array('post_id'=> $comment->comment_post_ID ) );
			$ratings = array();
			foreach ( $post_comments as $potential_rating_comment ) {
				$potential_rating = get_comment_meta($potential_rating_comment->comment_ID, 'recipepro_rating', true);
				if ( $potential_rating && intval($potential_rating) > 0 && intval($potential_rating) < 6 ) {
					$ratings[] = intval($potential_rating);
				}
			}
			$total = 0;
			foreach ( $ratings as $value ) {
				$total += $value;
			}
			$recipe = Recipe_Pro_Service::getRecipe( $comment->comment_post_ID );
			$recipe->ratingCount = count( $ratings );
			$recipe->ratingValue = round( $total / $recipe->ratingCount, 1);
			$recipe = Recipe_Pro_Service::saveRecipe( $comment->comment_post_ID, $recipe );
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
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/' . $options['css'], array( 'dashicons' ), $this->version, 'all' );	
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
