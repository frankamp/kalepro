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

function var_log() {
    ob_start();
    call_user_func_array( 'var_dump', func_get_args() );
    error_log( ob_get_clean() );
}

//var_log(get_bloginfo('version'));

/**
 * Sample test case.
 */
class AdminTest extends WP_UnitTestCase {

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
		$this->assertEquals( false, array_key_exists( 'recipepro_settings_group', $new_whitelist_options ));
		do_action( 'admin_init' );
		$this->assertEquals( true, array_key_exists( 'recipepro_settings_group', $new_whitelist_options ));
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

	function test_render_recipe() {
		$plugin_admin = new Recipe_Pro_Admin("", "");
		$recipe = new Recipe_Pro_Recipe(json_decode('{"title":"bannana bread","ingredients":[],"author":"","type":"","cuisine":"","instructions":[],"servingSize":"","calories":"","fatContent":"","saturatedFatContent":"","carbohydrateContent":"","sugarContent":"","sodiumContent":"","fiberContent":"","proteinContent":""}', true));
		$recipe_result = $plugin_admin->render_recipe($recipe);
		$this->assertEquals("<div><p>bannana bread</p></div>", $recipe_result);
	}

	function test_save_post() {
		global $_POST;
		$_POST['doc'] = "hello!";
		$post = $this->factory->post->create_and_get(array("post_title" => "My Title BLERRRRG"));
		$meta = get_post_meta( $post->ID, 'recipepro_recipe', true);
		$this->assertEquals( $_POST['doc'], $meta );
	}

	function test_post_factory_current_post() {
		$post = $this->factory->post->create_and_get(array("post_title" => "My Title BLERRRRG"));
		$GLOBALS['post'] = $post; // this is required to get the current post set
		$this->assertEquals( $post, get_post());
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

