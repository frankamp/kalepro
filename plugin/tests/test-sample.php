<?php
/**
 * Class SampleTest
 *
 * @package 
 */

/**
 * Sample test case.
 */
class BasicPluginTests extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_activation_sets_label_options() {
		$options = get_option( 'recipepro_settings', false );
		$this->assertEquals( false, $options );
		activate_recipe_pro();
		$options = get_option( 'recipepro_settings', false );
		$this->assertEquals( "Overview", $options['recipepro_text_label_overview'] );
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

	function test_post_create() {	 
	 	$post = $this->factory->post->create_and_get(array("post_title" => "My Title BLERRRRG"));
	 	$this->assertEquals( 'My Title BLERRRRG', $post->post_title );
	}
}

