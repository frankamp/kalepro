<?php
/**
 * Class SampleTest
 *
 * @package 
 */

class BasicPluginTests extends WP_UnitTestCase {

	function test_activation_sets_label_options() {
		$options = get_option( 'recipepro_settings', false );
		$this->assertEquals( false, $options );
		activate_recipe_pro();
		$options = get_option( 'recipepro_settings', false );
		$this->assertEquals( "Overview", $options['recipepro_text_label_overview'] );
	}

	function test_activation_sets_css_option() {
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( false, $options );
		activate_recipe_pro();
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( "simple.css", $options['css'] );
	}

	function test_activation_corrects_bad_css_option() {
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( false, $options );
		update_option( 'recipepro_main_settings', array('css' => 'other.css'));
		activate_recipe_pro();
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( "simple.css", $options['css'] );
	}

	function test_activation_sets_rating_option() {
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( false, $options );
		activate_recipe_pro();
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( 'true', $options['ratingsEnabled'] );
	}

	function test_activation_corrects_bad_rating_option() {
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( false, $options );
		update_option( 'recipepro_main_settings', array('ratingsEnabled' => 'other'));
		activate_recipe_pro();
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( 'true', $options['ratingsEnabled'] );
	}

	function test_activation_doesnt_correct_good_rating_option() {
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( false, $options );
		update_option( 'recipepro_main_settings', array('ratingsEnabled' => 'false') );
		activate_recipe_pro();
		$options = get_option( 'recipepro_main_settings', false );
		$this->assertEquals( 'false', $options['ratingsEnabled'] );
	}

	function test_activation_doesnt_override_on_reactivate() {
		activate_recipe_pro();
		$options = get_option( 'recipepro_settings', false );
		$this->assertEquals( "Overview", $options['recipepro_text_label_overview'] );
		$options['recipepro_text_label_overview'] = "Overizzle";
		update_option( 'recipepro_settings', $options);
		deactivate_recipe_pro();
		activate_recipe_pro();
		$options = get_option( 'recipepro_settings', false );
		$this->assertEquals( "Overizzle", $options['recipepro_text_label_overview'] );
	}
	// /**
	//  * @expectedException PHPUnit_Framework_Error
	//  */
	// function test_fail_activate_because of php version() {
	// fake a lower version?
	// 	activate_recipe_pro();
	// }

	function test_translate_() {
		require_once dirname( dirname( __FILE__ ) ) . '/includes/class-recipe-model.php';
		$this->assertEquals("PT0M", Recipe_Pro_Recipe_View_Helper::interval(new DateInterval("PT0M")));
		
	}
    // public function test_help_dispatch_for_options_page() {
    //     //set up the variables
    //     $contextual_help = '';
    //     global $mystyle_hook;
    //     $mystyle_hook = 'mock-hook';
    //     $screen_id = 'toplevel_page_' . $mystyle_hook;
    //     $screen = WP_Screen::get( $mystyle_hook );
        
    //     //Assert that the MyStyle help is not in the screen.
    //     $this->assertNotContains( 'MyStyle Custom Product Designer Help', serialize( $screen ) );
        
    //     //run the function
    //     mystyle_help_dispatch( $contextual_help, $screen_id, $screen );
        
    //     //Asset that the MyStyle help is now in the screen.
    //     $this->assertContains( 'MyStyle Custom Product Designer Help', serialize( $screen ) );
    // }

// /**
// 	 * The plugin should be installed and activated.
// 	 */
// 	function test_plugin_activated() {
// 		$directory = basename( dirname( dirname( __FILE__ ) ) );
// 		$this->assertTrue( is_plugin_active( $directory . '/plugin.php' ) );
// 	}
// 	*
// 	 * The json_api_init hook should have been registered with init, and should
// 	 * have a default priority of 10.
	 
// 	function test_init_action_added() {
// 		$this->assertEquals( 10, has_action( 'init', 'json_api_init' ) );
// 	}
// 	/**
// 	 * The json_route query variable should be registered.
// 	 */
// 	function test_json_route_query_var() {
// 		global $wp;
// 		$this->assertTrue( in_array( 'json_route', $wp->public_query_vars ) );
// 	}


	function assertHookIsRegistered($hook, $component, $function)
    {
        global $wp_filter;
        $found = false;
        if (isset($wp_filter[$hook])) {
            foreach ($wp_filter[$hook] as $hook) {
                foreach ($hook as $key => $hook) {
                    $found = $key === $component.'::'.$function;
                    $found = $found && $hook['function'][0] === $component;
                    $found = $found && $hook['function'][1] === $function;
                    if ($found) {
                        goto ret;
                    }
                }
            }
        }
        ret:
            $this->assertTrue($found);
    }

	function test_post_create() {	 
	 	$post = $this->factory->post->create_and_get(array("post_title" => "My Title BLERRRRG"));
	 	$this->assertEquals( 'My Title BLERRRRG', $post->post_title );
	}
}

