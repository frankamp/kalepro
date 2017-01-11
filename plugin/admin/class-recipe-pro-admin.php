<?php
require_once __DIR__."/../includes/class-option-defaults.php";
require_once __DIR__."/../includes/class-recipe-pro-service.php";
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

	}


	/**
	 * Gets a label value. This will be i18n default, or if the user
	 * has overridden the label in the admin, it will be that value.
	 *
	 * @since    1.0.0
	 */
	public function get_label( $key ) {
		$options = get_option( 'recipepro_settings', null );
		if ( isset( $options ) && array_key_exists( 'recipepro_text_label_' . $key, $options )) {
			return $options['recipepro_text_label_' . $key];
		}
		$options = Recipe_Pro_Option_Defaults::get_labels();
		if ( array_key_exists( $key, $options )) {
			return $options[$key];
		};
		return "";
	}

	public function register_shortcodes() {
		add_shortcode( 'recipepro', array( $this, 'render_recipe_shortcode' ) );
	}

	public function render_recipe_shortcode( $atts ) {
		// TODO: IT IS MY RESPONSIBILITY TO SECURE THE OUTPUT
		// https://developer.wordpress.org/plugins/security/securing-output/
		$post = get_post();
		$meta_result = get_post_meta( (int) $post->ID, (string) 'recipepro_recipe', true );
		if( ! $meta_result ) {
			$recipe = new Recipe_Pro_Recipe();
		} else {
			$recipe = new Recipe_Pro_Recipe(json_decode($meta_result, true));
		}
		return $this->render_recipe($recipe);
	}

	public function render_recipe( $recipe ) {
		return $recipe->render();
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
		add_submenu_page( 
			'recipepro',
			'Import Recipes From Other Plugins',
			'Import Recipes',
			'manage_options',
			'import-recipes-menu',
			array(&$this, 'menu_import_page_display')
		);
	}

	public function menu_page_display () {
		$html = '';
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">
			<form action='options.php' method='post'>
				<h2><?= __( 'Recipe', 'recipe-pro' ) ?></h2>
				<?php
				settings_fields( 'recipepro_settings_group' );
				do_settings_sections( 'recipepro_settings_group' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function menu_import_page_display () {
		$html = '';
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>

		<div class="wrap">
			<form action='options.php' method='post'>
				<h2><?= __( 'Things!', 'recipe-pro' ) ?></h2>
				<div id="importer">
					<li v-for="item in importers">
						{{ item.name }}
						<button v-on:click="beginImport" v-bind:name="item.name">{{ item.name }}</button>
					</li>

				</div>
			</form>
		</div>
		<?php
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

		register_setting( 'recipepro_settings_group', 'recipepro_settings' ); // could santize option values on save via callback here

		add_settings_section(
			'recipepro_settings_section_labels', 
			__( 'Labels', 'recipe-pro' ),
			array(&$this, 'recipepro_settings_section_callback_labels'), 
			'recipepro_settings_group'
		);

		foreach ( Recipe_Pro_Option_Defaults::get_labels() as $key => $value ) {
			add_settings_field(
				'recipepro_text_label_' . $key,
				$value,
				array(&$this, 'recipepro_text_label_render'),
				'recipepro_settings_group',
				'recipepro_settings_section_labels',
				array('label' => $key)
			);
		}
	}

	public function recipepro_text_label_render( $args ) {
		$options = get_option( 'recipepro_settings' );
		?>
		<input type='text' name='recipepro_settings[recipepro_text_label_<?= $args['label'] ?>]' value='<?= $options['recipepro_text_label_' . $args['label']]; ?>'>
		<?php

	}

	public function recipepro_settings_section_callback_labels(  ) { 
		echo __( 'Label overrides', 'recipe-pro' );
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
		
		$ingredient_settings = array(
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

		$instruction_settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => false,
			'tinymce'       => array(
				// 'selector' => '#recipe-pro-editor',
				// 'inline' => true,
				'plugins' => 'paste',
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
				}"
				//,
				//"content_style" => "body#tinymce p {background-image: url(" . plugin_dir_url( __FILE__ ) . "css/carrot.svg); background-position: right center; background-repeat: no-repeat; padding-right: 50px; margin-bottom: 5px; }"
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
				<li class="<%= currentTab == 'recipe-pro-tab-overview' ? 'active' : '' %>"><label for="recipe-pro-tab-overview"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('overview') ?></button></label></li>
				<li class="<%= currentTab == 'recipe-pro-tab-ingredient' ? 'active' : '' %>"><label for="recipe-pro-tab-ingredient"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('ingredients') ?></button></label></li>
				<li class="<%= currentTab == 'recipe-pro-tab-nutrition' ? 'active' : '' %>"><label for="recipe-pro-tab-nutrition"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('nutrition_information') ?></button></label></li>
				<li class="<%= currentTab == 'recipe-pro-tab-instruction' ? 'active' : '' %>"><label for="recipe-pro-tab-instruction"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('instructions') ?></button></label></li>
			</ul>
			<div id="recipe-pro-content">
				<div id="recipe-pro-tab-overview" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-overview' ? 'block' : 'none' %>;">
					<p><label for="recipepro_title"><?= $this->get_label('title') ?></label> <input id="recipepro_title" name="title" type="text" value="<%= _.escape(title) %>" /></p>
					<p><label for="recipepro_author"><?= $this->get_label('author') ?></label> <input id="recipepro_author" name="author" type="text" value="<%= _.escape(author) %>" /></p>
					<p><label for="recipepro_type"><?= $this->get_label('recipe_type') ?></label> <input id="recipepro_type" name="type" type="text" value="<%= _.escape(type) %>" />  </p>
					<p><label for="recipepro_cuisine"><?= $this->get_label('cuisine') ?></label><input id="recipepro_cuisine" name="cuisine" type="text" value="<%= _.escape(cuisine) %>" /></p>
				</div>
				<div id="recipe-pro-tab-ingredient" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-ingredient' ? 'block' : 'none' %>;">
					<?= wp_editor( "", "recipe-pro-editor-ingredient", $ingredient_settings  ) ?>
					<!-- <ul>
						<% _.each(ingredients, function(ing){ %>
						<li>
							<input name="quantity" type="text" value="<%= _.escape(ing.quantity) %>" />
							<input name="unit" type="text" value="<%= _.escape(ing.unit) %>" />
							<input name="name" type="text" value="<%= _.escape(ing.name) %>" />
						</li>
						<% }); %>
					</ul> -->
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
				<div id="recipe-pro-tab-instruction" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-instruction' ? 'block' : 'none' %>;">
					<?= wp_editor( "", "recipe-pro-editor-instruction", $instruction_settings  ) ?>
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
		if (isset($_POST['doc'])) {
			// deserialize/serialize to prove we can prior to saving it to the database
			//error_log( "save meta called with: " . $_POST['doc'] ); //stripslashes( )
			$json = json_decode( stripslashes( $_POST['doc'] ), true );
			//error_log("Attempt decode json error: " . json_last_error() );
			$recipe = new Recipe_Pro_Recipe( $json );
			//error_log( "inflated");
			//error_log( "recipe back to json " . json_encode($recipe) );
			//error_log( "Preparing to save");
			$success = Recipe_Pro_Service::saveRecipe( $post_id, $recipe );
	//		$hits = get_post_meta( (int) $post_id, (string) 'hits2', true );
	//		error_log( "hits are " . $hits . " but type is " . gettype($hits));
	//		$hits += 1;
	//		error_log( "hits are " . $hits . " after incrementing type is " . gettype($hits));
	//		$success = update_post_meta( (int) $post_id, (string) 'hits2', (string) $hits );
			if ($success) {
				//error_log( "you are successful" );
			} else {
				error_log( "you not successful" );
			}
		} else {
			error_log( "save_meta_box called with no doc" );
		}
//		error_log( "some success metrics for your update are: " . strval($success) . "type is " . gettype($success));
//		$hits = get_post_meta( (int) $post_id, (string) 'hits2', true );
//		error_log( "after update hits are " . $hits . " but type is " . gettype($hits));
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
		wp_enqueue_script( $this->plugin_name . "vue", plugin_dir_url( __FILE__ ) . 'js/vue.js', array(), $this->version, false );
		wp_enqueue_script( $this->plugin_name . "importer", plugin_dir_url( __FILE__ ) . 'js/recipe-pro-importer.js', array( 'jquery' ), $this->version, false );
	}

}
