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
				// 'selector' => '#recipe-pro-editor',
				// 'inline' => true,
				//'plugins' => 'paste',
				'external_plugins' => "{'recipeproingredient': '" . plugin_dir_url( __FILE__ ) . "js/mce-recipe-pro-ingredient/plugin.min.js'}",
				//'toolbar' => false,
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
					args.content = '<' + tag + '>' + args.content.replace(/<p>/g,'').replace(/<\/p>/g, '<br />').split('<br />').join('</' + tag + '><' + tag + '>') + '</' + tag +'>';
					args.content = args.content.replace(new RegExp('<' + tag + '>\\s*<\/' + tag + '>','g'),'');
					args.content = args.content.replace(new RegExp('<\/' + tag + '>','g'), \"<div class='mceNonEditable'><input type='text' value='editme' /></div>\");
					console.log(args.content);
				}"
				,"content_style" => "body#tinymce p { margin-bottom: 5px; } body#tinymce h4 { margin: 20px 0 10px; }"
				//,'protect' => "[/<div class='helper'>.*?<\/div>/g]"
				//background-image: url(" . plugin_dir_url( __FILE__ ) . "css/carrot.svg); background-position: right center; background-repeat: no-repeat;
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

		$instruction_settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => false,
			'tinymce'       => array(
				'toolbar1' => 'bold,italic,link,unlink,removeformat',
				'external_plugins' => "{'recipeproinstruction': '" . plugin_dir_url( __FILE__ ) . "js/mce-recipe-pro-instruction/plugin.min.js'}",
				"content_style" => "body#tinymce p { margin-bottom: 5px; }"
			),
			'media_buttons' => false,
			'editor_css'    => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
		);
		wp_enqueue_media();
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
							<img id='image-preview' class='rp-image-admin' src='<%= _.escape(imageUrl) %>'>
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
					<?= wp_editor( "", "recipe-pro-editor-instruction", $instruction_settings  ) ?>
				</div>
				<div id="recipe-pro-tab-nutrition" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-nutrition' ? 'block' : 'none' %>;">
					<div class="left">
						<p><label for="recipepro_yield"><?= $this->get_label('yield') ?></label> <input id="recipepro_yield" name="yield" type="number" placeholder="e.g 6 (number only)" value="<%= _.escape(yield) %>" /></p>
						<p><label for="recipepro_servingSize"><?= $this->get_label('serving_size') ?></label> <input id="recipepro_servingSize" placeholder="e.g. 1 slice" name="servingSize" type="text" value="<%= _.escape(servingSize) %>" /></p>
						<p><label for="recipepro_calories"><?= $this->get_label('calories') ?></label> <input id="recipepro_calories" name="calories" type="number" value="<%= _.escape(calories) %>" /></p>
						<p><label for="recipepro_fatContent"><?= $this->get_label('total_fat') ?></label> <input id="recipepro_fatContent" name="fatContent" type="text" value="<%= _.escape(fatContent) %>" /></p>
						<p><label for="recipepro_saturatedFatContent"><?= $this->get_label('saturated_fat') ?></label> <input id="recipepro_saturatedFatContent" name="saturatedFatContent" type="text" value="<%= _.escape(saturatedFatContent) %>" /></p>
					</div>
					<div class="right">
						<p><label for="recipepro_carbohydrateContent"><?= $this->get_label('carbohydrates') ?></label> <input id="recipepro_carbohydrateContent" name="carbohydrateContent" type="text" value="<%= _.escape(carbohydrateContent) %>" /></p>
						<p><label for="recipepro_sugarContent"><?= $this->get_label('sugars') ?></label> <input id="recipepro_sugarContent" name="sugarContent" type="text" value="<%= _.escape(sugarContent) %>" /></p>
						<p><label for="recipepro_sodiumContent"><?= $this->get_label('sodium') ?></label> <input id="recipepro_sodiumContent" name="sodiumContent" type="text" value="<%= _.escape(sodiumContent) %>" /></p>
						<p><label for="recipepro_fiberContent"><?= $this->get_label('fiber') ?></label> <input id="recipepro_fiberContent" name="fiberContent" type="text" value="<%= _.escape(fiberContent) %>" /></p>
						<p><label for="recipepro_proteinContent"><?= $this->get_label('protein') ?></label> <input id="recipepro_proteinContent" name="proteinContent" type="text" value="<%= _.escape(proteinContent) %>" /></p>
					</div>
					<div class="clear"/>
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

	//todo: removeme
	private function get_test_recipe_json() {
		return '
			{
				"title":"Coconut Curry Ramen",
				"description":"Savory vegan ramen infused with curry and coconut milk. Serve with sautéed portobello mushrooms and gluten free noodles for the ultimate plant-based meal.",
				"imageUrl":"http://cdn3.minimalistbaker.com/wp-content/uploads/2016/09/Curry-Ramen-SQUARE.jpg",
				"author":"Minimalist Baker",
				"type":"Entrée, Soup",
				"cuisine":"Vegan, Gluten Free",
				"yield":"2-3",
				"ingredientSections": [
					{
						"name": "BROTH",
						"items": [
							{"quantity":"", "unit":"", "name":"", "description":"1 Tbsp (15 ml) toasted or untoasted sesame oil*"},
							{"quantity":"", "unit":"", "name":"", "description":"1 small knob ginger, sliced lengthwise (into long strips)"},
							{"quantity":"", "unit":"", "name":"", "description":"5 cloves garlic, chopped"},
							{"quantity":"", "unit":"", "name":"", "description":"1 large onion, chopped lengthwise"},
							{"quantity":"", "unit":"", "name":"", "description":"2 1/2 Tbsp (40 g) yellow or green curry paste"},
							{"quantity":"", "unit":"", "name":"", "description":"4 cups (960 ml) vegetable broth"},
							{"quantity":"", "unit":"", "name":"", "description":"2 cups (480 ml) light coconut milk"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional: </em>1-2 Tbsp coconut sugar (more to taste)"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional:</em> 1/2 tsp ground turmeric (for color and more curry flavor)"},
							{"quantity":"", "unit":"", "name":"", "description":"1 Tbsp (15 g) white or yellow miso paste"}						
						]
					},
					{
						"name": "FOR SERVING",
						"items": [
							{"quantity":"", "unit":"", "name":"", "description":"2-3 cups noodles of choice (i.e. <a href=\"http://minimalistbaker.com/zucchini-pasta-with-lentil-bolognese/\" target=\"_blank\">spiralized zucchini squash</a>, cooked <a href=\"http://minimalistbaker.com/easy-vegan-ramen/\" target=\"_blank\">ramen noodles</a>*, or cooked <a href=\"http://www.amazon.com/dp/B0048IAIOS/?tag=minimalistbaker-20\" target=\"_blank\" rel=\"nofollow\">brown rice noodles</a>)"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional:</em> 2 portobello mushrooms, stems removed, sliced into 1/2-inch pieces (+ sautéed in 1 Tbsp sesame oil + 1 Tbsp tamari + 1 tsp maple syrup)"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional:</em> Fresh green onion, chopped"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional:</em> Sriracha or <a href=\"http://www.amazon.com/dp/B000LO25RG/?tag=minimalistbaker-20\" target=\"_blank\" rel=\"nofollow\">chili garlic sauce</a>"}
						]
					}
				],
				"instructions": [
				 	{"description": "Heat a large pot over medium-high heat. Once hot, add oil, garlic, ginger and onion. Sauté, stirring occasionally for 5-8 minutes, or until the onion has developed a slight sear (browned edges)."},
					{"description": "Add curry paste and sauté for 1-2 minutes more, stirring frequently. Then add vegetable broth and coconut milk and stir to deglaze the bottom of the pan."},
					{"description": "Bring to a simmer over medium heat, then reduce heat to low and cover. Simmer on low for at least 1 hour, up to 2-3, stirring occasionally. The longer it cooks, the more the flavor will deepen and develop."},
					{"description": "Taste broth and adjust seasonings as needed, adding coconut sugar for a little sweetness, turmeric for more intense curry flavor, or more sesame oil for nuttiness."},
					{"description": "About 10 minutes before serving, prepare any desired toppings/sides, such as noodles, sautéed portobello mushrooms, or green onion (optional)."},
					{"description": "Just before serving, scoop out 1/2 cup of the broth and whisk in the miso paste. Once fully dissolved, add back to the pot and turn off the heat. Stir to combine."},
					{"description": "Either strain broth through a fine mesh strainer (discard onions and ginger or add back to the soup), or ladle out the broth and leave the onions and mushrooms behind."},
					{"description": "To serve, divide noodles of choice between 2-3 serving bowls. Top with broth and desired toppings. Serve with chili garlic sauce or sriracha for added heat."},
					{"description": "Best when fresh, though the broth can be stored (separate from sides/toppings) in the refrigerator for up to 5 days, or in the freezer for up to 1 month."}
				],
				"notes": [
					{"description": "*You can sub sesame oil for coconut, but the sesame adds a nice rich nutty flavor to the ramen that I prefer.<br>*Nutrition information is a rough estimate for 1 of 3 servings calculated using brown rice noodles and no additional toppings.<br>*If using ramen noodles, this recipe would not be gluten free."}
				],
				"servingSize":"1/3 of recipe*",
				"calories":"310",
				"cholesterolContent":"",
				"fatContent":"19.6 g",
				"transFatContent":"",
				"saturatedFatContent":"8.8 g",
				"unsaturatedFatContent":"",
				"carbohydrateContent":"26 g",
				"sugarContent":"5.3 g",
				"sodiumContent":"1253 mg",
				"fiberContent":"0.8 g",
				"proteinContent":"10.1 g",
				"prepTime": "PT15M",
				"cookTime": "PT1H15M",
				"ratingValue": 5.0,
				"ratingCount": 5
			}';
	}

	public function save_meta_box ( $post_id, $post ) {
		// todo: sanitize and validate the input
		// todo: nonce
		if (isset($_POST['doc'])) {
			// deserialize/serialize to prove we can prior to saving it to the database
			//error_log( "save meta called with: " . $_POST['doc'] ); //stripslashes( )
			if ( strpos($_POST['doc'], 'generatetestrecipe') !== false ) {
				error_log( "generating a test post " );
				$json = json_decode( $this->get_test_recipe_json(), true );
			} else {
				$json = json_decode( stripslashes( $_POST['doc'] ), true );
			}
			//error_log("Attempt decode json error: " . json_last_error() );
			$recipe = new Recipe_Pro_Recipe( $json );
			//error_log( "inflated");
			//error_log( "recipe back to json " . json_encode($recipe) );
			//error_log( "Preparing to save");
			if ($recipe->imageId == 0) {
				$thumb = get_post_thumbnail_id( get_post( $post_id ) );
				if ( $thumb ) {
					$recipe->imageId = $thumb;
				}
			}
			
			$success = Recipe_Pro_Service::saveRecipe( $post_id, $recipe );
	//		$hits = get_post_meta( (int) $post_id, (string) 'hits2', true );
	//		error_log( "hits are " . $hits . " but type is " . gettype($hits));
	//		$hits += 1;
	//		error_log( "hits are " . $hits . " after incrementing type is " . gettype($hits));
	//		$success = update_post_meta( (int) $post_id, (string) 'hits2', (string) $hits );
			if ($success) {
				//error_log( "you are successful" );
			} else {
				//error_log( "you not successful" );
			}
		} else {
			error_log( "save_meta_box called with no doc" );
		}
//		error_log( "some success metrics for your update are: " . strval($success) . "type is " . gettype($success));
//		$hits = get_post_meta( (int) $post_id, (string) 'hits2', true );
//		error_log( "after update hits are " . $hits . " but type is " . gettype($hits));
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
