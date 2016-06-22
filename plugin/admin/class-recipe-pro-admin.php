<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.joshuafrankamp.com
 * @since      1.0.0
 *
 * @package    recipe-pro
 * @subpackage recipe-pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    recipe-pro
 * @subpackage recipe-pro/admin
 * @author     Josh Frankamp <frankamp@gmail.com>
 */
class Recipe_Pro_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->get_labels();

	}

	private $_labels;

	private function get_labels() {
		if (!$this->_labels) {
			$this->_labels = array(
				'ingredients' => __('Ingredients', 'recipe-pro'),
				'instructions' => __('Instructions', 'recipe-pro'),
				'notes' => __('Notes', 'recipe-pro'),
				'nutrition_information' => __('Nutrition Information', 'recipe-pro'),
				'prep_time' => __('Prep time', 'recipe-pro'),
				'cook_time' => __('Cook time', 'recipe-pro'),
				'total_time' => __('Total time', 'recipe-pro'),
				'serving_size' => __('Serving size', 'recipe-pro'),
				'hour' => __('Hour', 'recipe-pro'),
				'hours' => __('Hours', 'recipe-pro'),
				'minute' => __('Minute', 'recipe-pro'),
				'minutes' => __('Minutes', 'recipe-pro'),
				'author' => __('Author', 'recipe-pro'),
				'recipe_type' => __('Recipe Type', 'recipe-pro'),
				'cuisine' => __('Cuisine', 'recipe-pro'),
				'yield' => __('Yield', 'recipe-pro'),
				'calories' => __('Calories', 'recipe-pro'),
				'total_fat' => __('Total Fat', 'recipe-pro'),
				'saturated_fat' => __('Saturated fat', 'recipe-pro'),
				'unsaturated_fat' => __('Unsaturated fat', 'recipe-pro'),
				'trans_fat' => __('Trans fat', 'recipe-pro'),
				'cholesterol' => __('Cholesterol', 'recipe-pro'),
				'sodium' => __('Sodium', 'recipe-pro'),
				'carbohydrates' => __('Carbohydrates', 'recipe-pro'),
				'fiber' => __('Fiber', 'recipe-pro'),
				'sugars' => __('Sugars', 'recipe-pro'),
				'protein' => __('Protein', 'recipe-pro'),
				'rate_this_recipe' => __('Rate this recipe', 'recipe-pro')
			);
		}
		return $this->_labels;
	}

	/**
	 * Gets a label value. This will be i18n default, or if the user
	 * has overridden the label in the admin, it will be that value.
	 *
	 * @since    1.0.0
	 */
	public function get_label( $key ) {
		$options = get_option( 'recipepro_settings', null );
		if ( isset($options) && array_key_exists('recipepro_text_label_' . $key, $options) ) {
			return $options['recipepro_text_label_' . $key];
		}
		$options = $this->get_labels();
		if ( array_key_exists($key, $options) ) {
			return $options[$key];
		};
		return "";
	}

	public function register_shortcodes() {
		add_shortcode('recipepro', array($this, 'render_recipe'));
	}

	public function create_menu() {
		add_menu_page(
			'Recipe Pro',          // The title to be displayed on the corresponding page for this menu
			'Recipe Pro',                  // The text to be displayed for this actual menu item
			'manage_options',            // Which type of users can see this menu
			'recipepro',                  // The unique ID - that is, the slug - for this menu item
			array(&$this, 'menu_page_display'),// The name of the function to call when rendering the menu for this page
			'dashicons-carrot'
		);
	}

	public function GUIDv4 ()
	{
		// Windows
		if (function_exists('com_create_guid') === true) {
			return trim(com_create_guid(), '{}');
		}

		// OSX/Linux
		if (function_exists('openssl_random_pseudo_bytes') === true) {
			$data = openssl_random_pseudo_bytes(16);
			$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
			$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
			return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		}

		// Fallback (PHP 4.2+)
		mt_srand((double)microtime() * 10000);
		$charid = strtolower(md5(uniqid(rand(), true)));
		return substr($charid,  0,  8).chr(45).
		          substr($charid,  8,  4).chr(45).
		          substr($charid, 12,  4).chr(45).
		          substr($charid, 16,  4).chr(45).
		          substr($charid, 20, 12);
	}

	public function settings_init(  ) { 

		register_setting( 'pluginPage', 'recipepro_settings' );

		add_settings_section(
			'recipepro_pluginPage_section', 
			__( 'Labels', 'recipe-pro' ),
			array(&$this, 'recipepro_settings_section_callback'), 
			'pluginPage'
		);

		foreach ( $this->get_labels() as $key => $value ) {
			add_settings_field(
				'recipepro_text_label_' . $key,
				$value,
				array(&$this, 'recipepro_text_label_render'),
				'pluginPage',
				'recipepro_pluginPage_section',
				array('label' => $key)
			);
		}
	}

	public function recipepro_text_label_render( $args ) {
		$options = get_option( 'recipepro_settings' );
		?>
		<input type='text' name='recipepro_settings[recipepro_text_label_<?php echo $args['label'] ?>]' value='<?php echo $options['recipepro_text_label_' . $args['label']]; ?>'>
		<?php

	}


	public function recipepro_settings_section_callback(  ) { 
		echo __( 'Label overrides', 'recipe-pro' );
	}

	public function menu_page_display () {
		$html = '';
		?>
		<div class="wrap">
			<form action='options.php' method='post'>
				<h2>Recipe Pro</h2>
				<?php
				settings_fields( 'pluginPage' );
				do_settings_sections( 'pluginPage' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
	
	public function add_meta_box ( ) {
		add_meta_box( 'recipe-pro-recipe-data', __( 'Recipe', 'recipe-pro' ), array( $this, "render_editor_markup" ), 'post', 'normal', 'high' );
	}

	public function ajax_get_recipe ( ) {
		header ( "Content-Type: application/json" );
		$postid = str_replace('/', '', $_GET['postid']);
		error_log( "getting recipe for " . $postid  );
		$payload = get_post_meta( (int) $postid, (string) 'recipepro_recipe', true );
		if( ! $payload ) {
			error_log("defaulting, no payload");
			$payload = json_encode( new Recipe_Pro_Recipe() );
		}
		echo $payload;
		wp_die();
	}

	public function render_editor_markup ( $post ) {
//		$post_id = $post->ID;
//		$hits = get_post_meta( $post_id, 'hits2', true );
//		echo 'hits while hits are ' . $hits;
		?>
		<script type="text/template" id="recipe-pro-recipe-template">
			<ul id="recipe-pro-tabs">
				<li><label for="recipe-pro-tab-ingredient"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('ingredients') ?></button></label></li>
				<li><label for="recipe-pro-tab-nutrition"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('nutrition_information') ?></button></label></li>
			</ul>
			<div id="recipe-pro-tab-ingredient" class="recipe-pro-tab" style="display: block;">
				<p><input name="title" type="text" value="<%= _.escape(title) %>" /></p>
				<span><?= $this->get_label('ingredients') ?></span>
				<ul>
					<% _.each(ingredients, function(ing){ %>
					<li>
						<input name="quantity" type="text" value="<%= _.escape(ing.quantity) %>" />
						<input name="unit" type="text" value="<%= _.escape(ing.unit) %>" />
						<input name="name" type="text" value="<%= _.escape(ing.name) %>" />
					</li>
					<% }); %>
					<button type="button" id="add-ingredient">Add Ingredient</button>
				</ul>
			</div>
			<div id="recipe-pro-tab-nutrition" class="recipe-pro-tab" style="display: none;">
				matrix
			</div>
			<input type="hidden" name="doc" value="<%= _.escape(doc) %>" />
		</script>
		<div id="recipe-pro-admin-container" data-post="<?= $post->ID ?>"></div>
		<?php
	}

	public function save_meta_box ( $post_id, $post ) {
		// todo: sanitize and validate the input
		// todo: nonce


		$success = update_post_meta( (int) $post_id, (string) 'recipepro_recipe', $_POST['doc']);
		error_log( "save meta called" );
//		$hits = get_post_meta( (int) $post_id, (string) 'hits2', true );
//		error_log( "hits are " . $hits . " but type is " . gettype($hits));
//		$hits += 1;
//		error_log( "hits are " . $hits . " after incrementing type is " . gettype($hits));
//		$success = update_post_meta( (int) $post_id, (string) 'hits2', (string) $hits );
		if ($success) {
			error_log( "you are successful" );
		} else {
			error_log( "you not successful" );
		}
//		error_log( "some success metrics for your update are: " . strval($success) . "type is " . gettype($success));
//		$hits = get_post_meta( (int) $post_id, (string) 'hits2', true );
//		error_log( "after update hits are " . $hits . " but type is " . gettype($hits));
	}

	public function render_recipe( $atts ) {
		$html = '<h3>oh look its a recipe</h3>';
		return $html;
	}

	public function add_button( $plugin_array ) {
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/recipe-pro-button.js', array( 'jquery' ), $this->version, false );

		$plugin_array['recipe-pro'] = plugin_dir_url( __FILE__ ) . 'js/recipe-pro-button.js';
		return $plugin_array;
	}

	public function register_button( $buttons ) {
		array_push( $buttons, 'addeditrecipe' ); // dropcap', 'recentposts
		return $buttons;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name . "jquerymodal", plugin_dir_url( __FILE__ ) . 'css/jquery.modal.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/recipe-pro-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/recipe-pro-admin.js', array( 'jquery', 'backbone', 'underscore' ), $this->version, false );
	}

}
