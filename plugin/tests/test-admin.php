<?php
/**
 * Class AdminTest
 *
 * @package 
 */

class Simple_Render_Admin extends Recipe_Pro_Admin {
	public function render_recipe( $recipe ) {
		return "rendered " . $recipe->title;
	}
}

function test_content( $name ) {
	ob_start();
	include( $name . ".html" );
	return ob_get_clean();
}

//var_log(get_bloginfo('version'));

/**
 * Sample test case.
 */
class AdminTest extends WP_UnitTestCase {

	function prevent_api_checks() {
		global $wp_version;
		$current = new stdClass();
		$current->last_checked = time();
		$current->version_checked = $wp_version;
		set_site_transient( 'update_core', $current ); //prevent contacting wordpress api
		set_site_transient( 'update_plugins', $current );
		set_site_transient( 'update_themes', $current );
	}

	// function test_import() {
	// 	$file = dirname( dirname( __FILE__ ) ) . "/tests/testdata.xml";

	// 	error_log( "attempting to import $file" );
	// 	if ( class_exists('WP_Import') && defined('WP_LOAD_IMPORTERS')  ) {
	// 		$WP_Import = new WP_Import();
	// 		// Not sure why these wouldn't be loaded
	// 		if ( ! function_exists ( 'wp_insert_category' ) )
	// 			include ( ABSPATH . 'wp-admin/includes/taxonomy.php' );
	// 		if ( ! function_exists ( 'post_exists' ) )
	// 			include ( ABSPATH . 'wp-admin/includes/post.php' );
	// 		if ( ! function_exists ( 'comment_exists' ) )
	// 			include ( ABSPATH . 'wp-admin/includes/comment.php' );
	// 		ob_start();
	// 		// if ( defined('WORDPRESS_IMPORTER_EXTENDED_FETCH_ATTACHMENTS') && WORDPRESS_IMPORTER_EXTENDED_FETCH_ATTACHMENTS == true ) {
	// 		// 	$WP_Import->fetch_attachments = true;
	// 		// 	$WP_Import->allow_fetch_attachments();
	// 		// }
	// 		$WP_Import->import( $file );
	// 		ob_end_clean();
	// 	}
	// }

	function test_settings() {
		register_setting( "somesetting", "someoption" );
		// results in  in new_whitelist_options
			 // array(1) {
			 //   ["somesetting"]=>
			 //   array(1) {
			 //     [0]=>
			 //     string(10) "someoption"
			 //   }
			 // }
		global $new_whitelist_options;
		$this->assertEquals( false, array_key_exists( 'recipepro-labels', $new_whitelist_options ));
		$this->prevent_api_checks(); // these trigger from admin_init
		do_action( 'admin_init' );
		$this->assertEquals( true, array_key_exists( 'recipepro-labels', $new_whitelist_options ));
	}

	function test_create_menu() {
		global $admin_page_hooks;
		do_action( 'admin_menu' );
		$this->assertEquals( true, array_key_exists( 'recipepro', $admin_page_hooks ));
	}

	function test_create_shortcode() {
		global $shortcode_tags;
		do_action( 'init' );
		$this->assertEquals( true, array_key_exists( 'recipepro', $shortcode_tags ));
	}

	function test_shortcode_dispatch() {
		$plugin_admin = new Simple_Render_Admin( "", "" );
		$plugin_admin->register_shortcodes();
		$post = $this->factory->post->create_and_get(array("post_title" => "My Title BLERRRRG"));
		$content = $plugin_admin->render_recipe($post); // dummy implementation
		$GLOBALS['post'] = $post;
		$filtered = do_shortcode( "my [recipepro] is");
		$this->assertEquals( "my " . $content . " is", $filtered );
	}

	function get_test_recipe_json() {
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
				"ratingCount": 5,
				"imageId": 0
			}';
	}

	function test_render_recipe() {
		$plugin_admin = new Recipe_Pro_Admin("", "");
		$test_data = json_decode( $this->get_test_recipe_json(), true );
		if ( json_last_error() != JSON_ERROR_NONE ) {
			$this->assertEquals( "bad json", "is bad because " . json_last_error_msg());
		}
		$recipe = new Recipe_Pro_Recipe( $test_data );
		$recipe_result = $plugin_admin->render_recipe( $recipe );
		$this->assertEquals( test_content( 'recipe' ), $recipe_result );
	}

	function test_save_post() {
		global $_POST;
		$_POST['doc'] = wp_slash($this->get_test_recipe_json());
		$post = $this->factory->post->create_and_get( array( "post_title" => "My Title BLERRRRG" ) );
		$meta = get_post_meta( $post->ID, 'recipepro_recipe', true );
		// error_log( "We got back some meta during test_save_post and it is " . $meta );
		// error_log( "lets decode it!" );
		// var_log( json_decode( $meta, true ) );
		// error_log( "but was there an errror? " . json_last_error() );
		// error_log( "for kicks here is the decoded test json" );
		// var_log( json_decode( $this->get_test_recipe_json(), true) ); 
		$this->assertEquals( json_decode( $this->get_test_recipe_json(), true), json_decode( $meta, true) );
	}

	function test_post_factory_current_post() {
		$post = $this->factory->post->create_and_get(array("post_title" => "My Title BLERRRRG"));
		$GLOBALS['post'] = $post; // this is required to get the current post set
		$this->assertEquals( $post, get_post());
	}

	function test_image_preference_from_id() {
		$filename = ( dirname( dirname( __FILE__ ) ) . "/tests/test-image.png" );
		$contents = file_get_contents($filename);

		$upload = wp_upload_bits( basename($filename), null, $contents );
		$this->assertTrue( empty($upload['error']) );

	    $id = _make_attachment($upload);
		$rawobj = json_decode( $this->get_test_recipe_json(), true );
		$rawobj['imageId'] = $id;
		$recipe = new Recipe_Pro_Recipe( $rawobj );
		$this->assertEquals( "http://example.org/wp-content/uploads/2017/04/test-image", substr( $recipe->getImageUrl(), 0, 56 ) );
		wp_delete_post( $id, true );
	}

	function test_image_preference_from_string() {
		$rawobj = json_decode( $this->get_test_recipe_json(), true );
		$recipe = new Recipe_Pro_Recipe( $rawobj );
		$this->assertEquals( "http://cdn3.minimalistbaker.com/wp-content/uploads/2016/09/Curry-Ramen-SQUARE.jpg", $recipe->getImageUrl() );
	}

	function test_image_from_post_gets_defaulted_on_save() {
		$post = $this->factory->post->create_and_get( array( "post_title" => "My Title BLERRRRG" ) );
		global $_POST;
		$filename = ( dirname( dirname( __FILE__ ) ) . "/tests/test-image.png" );
		$contents = file_get_contents($filename);
		$upload = wp_upload_bits( basename( $filename ), null, $contents );
		$this->assertTrue( empty($upload['error']) );
	    $id = _make_attachment( $upload );
		set_post_thumbnail( $post->ID, $id );
		$_POST['doc'] = wp_slash($this->get_test_recipe_json());
		wp_update_post($post);
		$meta = get_post_meta( $post->ID, 'recipepro_recipe', true );
		$data = json_decode( $meta, true);
		$this->assertEquals( $id, $data['imageId'] );
	}
	// $this->loader->add_action( 'add_meta_boxes_post', $plugin_admin, 'add_meta_box' );
	// $this->loader->add_action( 'wp_ajax_recipepro_recipe', $plugin_admin,  'ajax_get_recipe' );
	// $this->loader->add_filter( 'mce_external_plugins', $plugin_admin, 'add_button' );
	// $this->loader->add_filter( 'mce_buttons', $plugin_admin, 'register_button' );

	// /**
	//  * @expectedException PHPUnit_Framework_Error
	//  */
	// function test_fail_activate_because of php version() {
	// fake a lower version?
	// 	activate_recipe_pro();
	// }

// some bits initializing some global rewrite stuff in wp so the tested code's internals would work
		// 	parent::setUp();
		// global $wp_rewrite;
		// $wp_rewrite->init();
		// $wp_rewrite->set_permalink_structure('/archives/%post_id%');
		// presumably here something that relied on rewrites having a permalink structure is run

}

