<?php
/**
 * Class SampleTest
 *
 * @package 
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

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

	function test_sample_string() {	 
	 	$post = $this->factory->post->create_and_get();
	 	$this->assertEquals( 'Post title 18', $post->post_title );
	}
}

