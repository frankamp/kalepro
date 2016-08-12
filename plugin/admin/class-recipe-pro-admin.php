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
		//bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator
		
		$settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => false,
			'tinymce'       => array(
				// 'selector' => '#recipe-pro-editor',
				// 'inline' => true,
				'plugins' => 'paste',
				'external_plugins' => "{'recipeproingredient': '" . plugin_dir_url( __FILE__ ) . "js/mce-recipe-pro-ingredient/plugin.min.js'}",
				'toolbar' => false,
				'toolbar1' => '',
				'statusbar' => false,
				'theme_advanced_buttons1' => '',
				'theme_advanced_buttons2' => '',
				'force_p_newlines' => true,
				'paste_remove_styles' => true,
				'paste_remove_spans' => true,
				'paste_strip_class_attributes' => 'none',
				'paste_as_text' => true,
				'paste_preprocess' =>  "function(plugin, args) {
					console.log('before its: ' + args.content);
					var tag = 'p';
				    args.content = '<' + tag + '>' + args.content.replace(/<p>/g,'').replace(/<\/p>/g, '<br />').split('<br />').join('</' + tag + '><' + tag + '>') + '</' + tag +'>';
				    args.content = args.content.replace(new RegExp('<' + tag + '>\\s*<\/' + tag + '>','g'),'');
				    args.content = args.content.replace(new RegExp('<\/' + tag + '>','g'), \"<div class='mceNonEditable'><input type='text' value='editme' /></div>\");
				    console.log(args.content);
				}",
				"content_style" => "body#tinymce p {background-image: url(" . plugin_dir_url( __FILE__ ) . "css/carrot.svg); background-position: right center; background-repeat: no-repeat; padding-right: 50px; margin-bottom: 5px; }"
				//,'protect' => "[/<div class='helper'>.*?<\/div>/g]"
			),
			'media_buttons' => false,
			'editor_css'    => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
		);
		?>
		<!--
		aggregateRating
			ratingValue span
			ratingCount span
		name
		image -> eg: <img itemprop="image" src="http://cdn3.minimalistbaker.com/wp-content/uploads/2016/07/Go-to-Smoothie-Bowl-SQUARE.jpg" width="205">
		prepTime -> <time itemprop="prepTime" datetime="PT5M">
		totalTime -> <time itemprop="totalTime" datetime="PT5M">
		description
		author
		recipeCategory span "recipe type" aka breakfast
		recipeCuisine span aka Thai food
		recipeYield
		ingredients (many)
		recipeInstructions (many)
 		-->
		<script type="text/template" id="recipe-pro-recipe-template">
			<ul id="recipe-pro-tabs">
				<li class="<%= currentTab == 'recipe-pro-tab-ingredient' ? 'active' : '' %>"><label for="recipe-pro-tab-ingredient"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('ingredients') ?></button></label></li>
				<li class="<%= currentTab == 'recipe-pro-tab-nutrition' ? 'active' : '' %>"><label for="recipe-pro-tab-nutrition"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('nutrition_information') ?></button></label></li>
			</ul>
			<div id="recipe-pro-content">
				<div id="recipe-pro-tab-ingredient" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-ingredient' ? 'block' : 'none' %>;">
					<?= wp_editor( "", "recipe-pro-editor", $settings  ) ?>
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
					</ul>
				</div>
				<div id="recipe-pro-tab-nutrition" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-nutrition' ? 'block' : 'none' %>;">
					<div class="left">
						<p><label for="recipepro_servingSize"><?= $this->get_label('serving_size') ?></label> <input id="recipepro_servingSize" name="servingSize" type="text" value="<%= _.escape(servingSize) %>" /></p>
						<p><label for="recipepro_calories"><?= $this->get_label('calories') ?></label> <input id="recipepro_calories" name="calories" type="text" value="<%= _.escape(calories) %>" /></p>
						<p><label for="recipepro_fatContent"><?= $this->get_label('total_fat') ?></label> <input id="recipepro_fatContent" name="fatContent" type="text" value="<%= _.escape(fatContent) %>" /></p>
						<p><label for="recipepro_saturatedFatContent"><?= $this->get_label('saturated_fat') ?></label> <input id="recipepro_saturatedFatContent" name="saturatedFatContent" type="text" value="<%= _.escape(saturatedFatContent) %>" /></p>
						<p><label for="recipepro_carbohydrateContent"><?= $this->get_label('carbohydrates') ?></label> <input id="recipepro_carbohydrateContent" name="carbohydrateContent" type="text" value="<%= _.escape(carbohydrateContent) %>" /></p>
					</div>
					<div class="right">
						<p><label for="recipepro_sugarContent"><?= $this->get_label('sugars') ?></label> <input id="recipepro_sugarContent" name="sugarContent" type="text" value="<%= _.escape(sugarContent) %>" /></p>
						<p><label for="recipepro_sodiumContent"><?= $this->get_label('sodium') ?></label> <input id="recipepro_sodiumContent" name="sodiumContent" type="text" value="<%= _.escape(sodiumContent) %>" /></p>
						<p><label for="recipepro_fiberContent"><?= $this->get_label('fiber') ?></label> <input id="recipepro_fiberContent" name="fiberContent" type="text" value="<%= _.escape(fiberContent) %>" /></p>
						<p><label for="recipepro_proteinContent"><?= $this->get_label('protein') ?></label> <input id="recipepro_proteinContent" name="proteinContent" type="text" value="<%= _.escape(proteinContent) %>" /></p>
					</div>
					<div class="clear"/>
				</div>
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
