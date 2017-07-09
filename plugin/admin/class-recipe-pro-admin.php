<?php
require_once __DIR__."/../includes/class-option-defaults.php";
require_once __DIR__."/../includes/class-recipe-pro-service.php";
require_once __DIR__."/class-recipe-pro-main-page.php";
require_once __DIR__."/class-recipe-pro-label-page.php";
require_once __DIR__."/class-recipe-pro-licensing-page.php";
require_once __DIR__."/class-recipe-pro-import-page.php";

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version,
 * enqueues the admin-specific stylesheet and JavaScript.
 * Implements nearly all of the admin wp hooks, sets up the menu
 * and the rest is directly supporting the edit flow.
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
		$this->main_page = new Recipe_Pro_Main_Page();
		$this->import_page = new Recipe_Pro_Import_Page( $plugin_name, $version );
		$this->label_page = new Recipe_Pro_Label_Page();
		$this->licensing_page = new Recipe_Pro_Licensing_Page();
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
		$post = get_post();
		$recipe = Recipe_Pro_Service::getRecipe( $post->ID );
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
			array(&$this->main_page, 'page_display'),// The name of the function to call when rendering the target page
			'dashicons-carrot'
		);
		add_submenu_page( 
			'recipepro',
			'Label Overrides',
			'Label Overrides',
			'manage_options',
			'recipepro-labels',
			array(&$this->label_page, 'page_display')
		);
		add_submenu_page( 
			'recipepro',
			'License',
			'License',
			'manage_options',
			RECIPE_PRO_LICENSE_PAGE,
			array(&$this->licensing_page, 'page_display')
		);
		add_submenu_page( 
			'recipepro',
			'Import Recipes From Other Plugins',
			'Import Recipes',
			'manage_options',
			'recipepro-import',
			array(&$this->import_page, 'page_display')
		);
	}

	/**
	 * Implements the admin_init action
	 *
	 * @since    1.0.0
	 */
	public function on_admin_init(  ) { 
		$this->licensing_page->init();
		$this->label_page->register_page();
		$this->main_page->register_page();
	}
	
	/**
	 * Adds a meta box for the current screen (by omitting the screen arg)
	 * The screen types are chosen by hooking the screen types directly in the main plugin
	 *
	 * @since    1.0.0
	 */
	public function add_meta_box ( ) {
		add_meta_box( 'recipe-pro-recipe-data', __( 'Recipe', 'recipe-pro' ), array( $this, "render_editor_markup" ), null, 'normal', 'high' );
	}

	public function ajax_get_recipe ( ) {
		header ( "Content-Type: application/json" );
		$postid = str_replace('/', '', $_GET['postid']);
		$payload = get_post_meta( (int) $postid, (string) 'recipepro_recipe', true );
		if( ! $payload ) {
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
				'external_plugins' => "{'recipeproingredient': '" . plugin_dir_url( __FILE__ ) . "js/mce-recipe-pro-ingredient/plugin.min.js'}",
				'toolbar1' => 'bold,italic,link,unlink,removeformat,recipepro_setasheader,recipepro_removeasheader',
				'toolbar2' => '',
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
					args.content = '<' + tag + '>' + args.content.replace(/<p>/g,'').replace(/<\/p>/g, '<br />').replace(/<br>/g, '<br />').split('<br />').join('</' + tag + '><' + tag + '>') + '</' + tag +'>';
					args.content = args.content.replace(new RegExp('<' + tag + '>\\s*<\/' + tag + '>','g'),'');
				}"
				,"content_style" => "body#tinymce p { margin-bottom: 5px; } body#tinymce h4 { margin: 20px 0 10px; }"
			),
			'media_buttons' => false,
			'editor_css'    => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
		);

// // 'selector' => '#recipe-pro-editor',
// 				// 'inline' => true,
// 				'plugins' => 'paste',
// 				//'toolbar' => false,
// 				//'toolbar1' => 'bold italic | link image',
// 				'statusbar' => false,
// 				'theme_advanced_buttons1' => 'bold',
// 				//'theme_advanced_buttons2' => '',
// 				'force_p_newlines' => true,
// 				'paste_remove_styles' => true,
// 				'paste_remove_spans' => true,
// 				'paste_strip_class_attributes' => 'none',
// 				'paste_as_text' => true,
// 				'paste_preprocess' =>  "function(plugin, args) {
// 					console.log('before its: ' + args.content);
// 					var tag = 'p';
// 					args.content = '<' + tag + '>' + args.content.replace(/<p>/g,'').replace(/<\/p>/g, '<br />').split('<br />').join('</' + tag + '><' + tag + '>') + '</' + tag +'>';
// 					args.content = args.content.replace(new RegExp('<' + tag + '>\\s*<\/' + tag + '>','g'),'');
// 					args.content = args.content.replace(new RegExp('<\/' + tag + '>','g'), \"<div class='mceNonEditable'><input type='text' value='editme' /></div>\");
// 					console.log(args.content);
// 				}"

		$simple_edit_settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => false,
			'tinymce'       => array(
				'toolbar1' => 'bold,italic,link,unlink,removeformat',
				'external_plugins' => "{'recipeprosimpleedit': '" . plugin_dir_url( __FILE__ ) . "js/mce-recipe-pro-simple-edit/plugin.min.js'}",
				"content_style" => "body#tinymce p { margin-bottom: 5px; }",
				'force_p_newlines' => true,
				'paste_remove_styles' => true,
				'paste_remove_spans' => true,
				'paste_strip_class_attributes' => 'none',
				'paste_as_text' => true,
				'paste_preprocess' =>  "function(plugin, args) {
					console.log('before its: ' + args.content);
					var tag = 'p';
					args.content = '<' + tag + '>' + args.content.replace(/<p>/g,'').replace(/<\/p>/g, '<br />').replace(/<br>/g, '<br />').split('<br />').join('</' + tag + '><' + tag + '>') + '</' + tag +'>';
					args.content = args.content.replace(new RegExp('<' + tag + '>\\s*<\/' + tag + '>','g'),'');
				}"
			),
			'media_buttons' => false,
			'editor_css'    => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
		);
		wp_enqueue_media();
		?>
		<script type="text/template" id="recipe-pro-recipe-template">
			<div class="notice notice-info" style="display: <%= missingShortcode ? 'block' : 'none' %>;"> 
				<p><strong>Looks like you haven&#39;t added this recipe to your post yet. Place your cursor in the post and click the "Add Recipe" button <i class="mce-i-recipe_pro_carrot recipe-pro-page-icon" /> to add your recipe!</strong></p>
			</div>
			<div class="notice notice-warning" style="display: <%= deletedShortcode ? 'block' : 'none' %>;"> 
				<p><strong>Oops! Looks like you have recipe information stored but not placed in this post. Click the "Add Recipe" button <i class="mce-i-recipe_pro_carrot recipe-pro-page-icon" /> in the main editor to insert the recipe into your post!</strong></p>
			</div>
			<ul id="recipe-pro-tabs">
				<li class="<%= currentTab == 'recipe-pro-tab-overview' ? 'active' : '' %>">
					<label for="recipe-pro-tab-overview"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('overview') ?></button></label>
				</li>
				<li class="<%= currentTab == 'recipe-pro-tab-ingredient' ? 'active' : '' %>">
					<label for="recipe-pro-tab-ingredient"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('ingredients') ?></button></label>
				</li>
				<li class="<%= currentTab == 'recipe-pro-tab-instruction' ? 'active' : '' %>">
					<label for="recipe-pro-tab-instruction"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('instructions') ?></button></label>
				</li>
				<li class="<%= currentTab == 'recipe-pro-tab-nutrition' ? 'active' : '' %>">
					<label for="recipe-pro-tab-nutrition"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('nutrition_information') ?></button></label>
				</li>
				<li class="<%= currentTab == 'recipe-pro-tab-notes' ? 'active' : '' %>">
					<label for="recipe-pro-tab-notes"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('notes') ?></button></label>
				</li>
			</ul>
			<div id="recipe-pro-content">
				<div id="recipe-pro-tab-overview" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-overview' ? 'block' : 'none' %>;">
					<div class="left">
						<p><label for="recipepro_title"><?= $this->get_label('title') ?></label><input class="rp-input" id="recipepro_title" name="title" type="text" value="<%= _.escape(title) %>" /></p>
						<p><label for="recipepro_author"><?= $this->get_label('author') ?></label><input class="rp-input" id="recipepro_author" name="author" type="text" value="<%= _.escape(author) %>" /></p>
						<p><label for="recipepro_type"><?= $this->get_label('recipe_type') ?></label><input class="rp-input" id="recipepro_type" name="type" type="text" value="<%= _.escape(type) %>" /></p>
						<p><label for="recipepro_cuisine"><?= $this->get_label('cuisine') ?></label><input class="rp-input" id="recipepro_cuisine" name="cuisine" type="text" value="<%= _.escape(cuisine) %>" /></p>
						<p><label for="recipepro_preptime"><?= $this->get_label('prep_time') ?></label><input class="rp-input" id="recipepro_preptime" name="prepTime" type="text" value="<%= _.escape(prepTime) %>" /></p>
						<p><label for="recipepro_preptime"><?= $this->get_label('cook_time') ?></label><input class="rp-input" id="recipepro_cooktime" name="cookTime" type="text" value="<%= _.escape(cookTime) %>" /></p>
					</div>
					<div class="right">
						<p>
						<div class='image-preview-wrapper'>
							<% if (imageUrl.length > 0) { %>
							<img id='image-preview' class='rp-image-admin' src='<%= _.escape(imageUrl) %>'>
							<% } %>
						</div>
						<input id="upload_image_button" type="button" class="button rp-image-select" value="<?php _e( 'Select Recipe Image', 'recipe-pro' ); ?>" />
						<input type='hidden' name='recipepro_imageurl' id='imageUrl' value='<%= _.escape(imageUrl) %>' />
						</p>
					</div>
					<div class="clear"/>
				</div>
				<div id="recipe-pro-tab-ingredient" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-ingredient' ? 'block' : 'none' %>;">
					<?= wp_editor( "", "recipe-pro-editor-ingredient", $ingredient_settings  ) ?>
				</div>
				<div id="recipe-pro-tab-instruction" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-instruction' ? 'block' : 'none' %>;">
					<?= wp_editor( "", "recipe-pro-editor-instruction", $simple_edit_settings  ) ?>
				</div>
				<div id="recipe-pro-tab-nutrition" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-nutrition' ? 'block' : 'none' %>;">
					<div class="left">
						<p><label for="recipepro_yield"><?= $this->get_label('yield') ?></label> <input id="recipepro_yield" name="yield" type="number" placeholder="e.g 6 (number only)" value="<%= _.escape(yield) %>" /></p>
						<p><label for="recipepro_servingSize"><?= $this->get_label('serving_size') ?></label> <input id="recipepro_servingSize" placeholder="e.g. 1 slice" name="servingSize" type="text" value="<%= _.escape(servingSize) %>" /></p>
						<p><label for="recipepro_calories"><?= $this->get_label('calories') ?></label> <input id="recipepro_calories" name="calories" type="number" value="<%= _.escape(calories) %>" /></p>
						<p><label for="recipepro_fatContent"><?= $this->get_label('total_fat') ?>  (g)</label> <input id="recipepro_fatContent" name="fatContent" type="number" step="0.1" value="<%= _.escape(fatContent) %>" /></p>
						<p><label for="recipepro_transFatContent"><?= $this->get_label('trans_fat') ?>  (g)</label> <input id="recipepro_transFatContent" name="transFatContent" step="0.1" type="number" value="<%= _.escape(transFatContent) %>" /></p>
						<p><label for="recipepro_saturatedFatContent"><?= $this->get_label('saturated_fat') ?>  (g)</label> <input id="recipepro_saturatedFatContent" name="saturatedFatContent" type="number" step="0.1" value="<%= _.escape(saturatedFatContent) %>" /></p>
					</div>
					<div class="right">
						<p><label for="recipepro_cholesterolContent"><?= $this->get_label('cholesterol') ?>  (mg)</label> <input id="recipepro_cholesterolContent" name="cholesterolContent" type="number" step="0.1" value="<%= _.escape(cholesterolContent) %>" /></p>
						<p><label for="recipepro_carbohydrateContent"><?= $this->get_label('carbohydrates') ?> (g)</label> <input id="recipepro_carbohydrateContent" name="carbohydrateContent" type="number" step="0.1" value="<%= _.escape(carbohydrateContent) %>" /></p>
						<p><label for="recipepro_sugarContent"><?= $this->get_label('sugars') ?> (g)</label> <input id="recipepro_sugarContent" name="sugarContent" type="number" step="0.1" value="<%= _.escape(sugarContent) %>" /></p>
						<p><label for="recipepro_sodiumContent"><?= $this->get_label('sodium') ?> (mg)</label> <input id="recipepro_sodiumContent" name="sodiumContent" type="number" value="<%= _.escape(sodiumContent) %>" /></p>
						<p><label for="recipepro_fiberContent"><?= $this->get_label('fiber') ?> (g)</label> <input id="recipepro_fiberContent" name="fiberContent" type="number" step="0.1" value="<%= _.escape(fiberContent) %>" /></p>
						<p><label for="recipepro_proteinContent"><?= $this->get_label('protein') ?> (g)</label> <input id="recipepro_proteinContent" name="proteinContent" type="number" step="0.1" value="<%= _.escape(proteinContent) %>" /></p>
					</div>
					<div class="clear"/>
				</div>
				<div id="recipe-pro-tab-notes" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-notes' ? 'block' : 'none' %>;">
					<?= wp_editor( "", "recipe-pro-editor-notes", $simple_edit_settings  ) ?>
				</div>
			</div>
		</script>
		<div id="recipe-pro-admin-container" data-post="<?= $post->ID ?>"></div>
		<script type="text/template" id="recipe-pro-recipe-output-template">
			<input type="hidden" name="doc" value="<%= _.escape(doc) %>" />
		</script>
		<div id="recipe-pro-admin-container-output"></div>
		<?php
	}

	public function save_meta_box ( $post_id, $post ) {
		// todo: sanitize and validate the input
		// todo: nonce
		if (isset($_POST['doc'])) {
			$json = json_decode( stripslashes( $_POST['doc'] ), true );
			$recipe = new Recipe_Pro_Recipe( $json );
			
			if ($recipe->imageId == 0) {
				$thumb = get_post_thumbnail_id( get_post( $post_id ) );
				if ( $thumb ) {
					$recipe->imageId = $thumb;
				}
			}
			
			$success = Recipe_Pro_Service::saveRecipe( $post_id, $recipe );
			if ($success) {
				//error_log( "you are successful" );
			} else {
				//error_log( "you not successful" );
			}
		} else {
			//error_log( "save_meta_box called with no doc" );
		}
	}

	public function register_mce_carrot_button( $buttons ) {
		array_push( $buttons, 'recipepro_addeditrecipe' );
		return $buttons;
	}

	public function add_mce_carrot_button_action( $plugin_array ) {
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/recipe-pro-button.js', array( 'jquery' ), $this->version, false );
		$plugin_array['recipe-pro'] = plugin_dir_url( __FILE__ ) . 'js/recipe-pro-button.js';
		return $plugin_array;
	}

	public function add_mce_css( $mce_css ) {
		if ( ! empty( $mce_css ) )
			$mce_css .= ',';
		$mce_css .= plugin_dir_url( __FILE__ ) . 'css/recipe-pro-mce.css';
		return $mce_css;
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
		$this->import_page->enqueue_scripts();
	}

}
