<?php
/**
 * Class WPUltimageImportTest
 *
 * @package 
 */

 /**
 * Import tests
 */

require_once RECIPE_PRO_PLUGIN_DIR . '/import/class-recipe-pro-wpultimate-importer.php';
class WPUltimageImportTest extends WP_UnitTestCase {

	function get_post_without() {
		$post = $this->factory->post->create_and_get(array(
			"post_title" => "My Title BLERRRRG",
			"post_content" => " blerg blerg ")
		);
		return $post;
	}

	function get_post() {
		$recipe = $this->factory->post->create_and_get(array(
			"post_title" => "My recipe BLERRRRG",
			"post_content" => " blerg blerg ")
		);
		add_post_meta($recipe->ID, 'recipe_title', 'My amazing recipe');
		add_post_meta($recipe->ID, 'recipe_description', 'My amazing recipe description');
		add_post_meta($recipe->ID, 'recipe_rating', '3');
		add_post_meta($recipe->ID, 'recipe_servings', '7');
		add_post_meta($recipe->ID, 'recipe_servings_type', 'ice creams');
		add_post_meta($recipe->ID, 'recipe_prep_time', '1');
		add_post_meta($recipe->ID, 'recipe_prep_time_text', 'hour');
		add_post_meta($recipe->ID, 'recipe_cook_time', '10');
		add_post_meta($recipe->ID, 'recipe_cook_time_text', 'mins');
		add_post_meta($recipe->ID, 'recipe_passive_time', '3');
		add_post_meta($recipe->ID, 'recipe_passive_time_text', 'hrs');
		//add_post_meta($recipe->ID, 'recipe_alternate_image', 'My amazing recipe'); TODO

		$post = $this->factory->post->create_and_get(array(
			"post_title" => "My Title BLERRRRG",
			"post_content" => " blerg [ultimate-recipe id=\"" . $recipe->ID . "\" template=\"default\"] blerg ")
		);
		return $post;
	}

	function test_is_instance() {
		wp_set_current_user( null, 'admin' );
		$this->assertEquals( false, Recipe_Pro_WPUltimate_Importer::is_instance( $this->get_post_without() ) );
		$this->assertEquals( true, Recipe_Pro_WPUltimate_Importer::is_instance( $this->get_post() ) );
	}

	function test_extract() {
		register_post_type( 'recipe' );
		import_data( 'wpudata.xml' );
		$post = get_post(10403);
		wp_set_current_user( null, 'admin' );
		$log = Recipe_Pro_WPUltimate_Importer::extract( $post );
		$recipe = $log->recipe;
		$this->assertEquals( "Simple Tofu Quiche", $recipe->title );
		$this->assertEquals( "The simplest tofu quiche on the block with just 10 basic ingredients and no fancy methods required. A hash brown crust keeps this dish gluten free as well as vegan! Perfect for lunch, brunch and even brinner.", $recipe->description );
		$this->assertEquals( "8 people", $recipe->servingSize );
		$this->assertEquals( new DateInterval("PT15M"), $recipe->prepTime );
		$this->assertEquals( new DateInterval("PT90M"), $recipe->cookTime );
		$this->assertEquals( "Passive time 5 hours was removed.", $log->notes[0] );
	}

	// function test_convert() {
	// 	wp_set_current_user( null, 'admin' );
	// 	$this->assertEquals( false, Recipe_Pro_WPUltimate_Importer::convert( $this->get_post_without() ) );
	// 	$this->assertEquals( true, Recipe_Pro_WPUltimate_Importer::convert( $this->get_post() ) );

	// }

}