<?php
/**
 * Class AdminTest
 *
 * @package 
 */

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
		$this->	assertEquals( true, array_key_exists( 'recipepro_settings_group', $new_whitelist_options ));
	}

	function test_create_menu() {
		global $admin_page_hooks;
		do_action( 'admin_menu' );
		$this->	assertEquals( true, array_key_exists( 'recipepro', $admin_page_hooks ));
	}

	function test_create_shortcode() {
		global $shortcode_tags;
		do_action( 'init' );
		$this->	assertEquals( true, array_key_exists( 'recipepro', $shortcode_tags ));
	}

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

	// function test_post_create() {	 
	//  	$post = $this->factory->post->create_and_get(array("post_title" => "My Title BLERRRRG"));
	//  	$this->assertEquals( 'My Title BLERRRRG', $post->post_title );
	// }
}

