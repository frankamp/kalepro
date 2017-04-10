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

	function load_wpudata() {
		wp_set_current_user( null, 'admin' );
		register_post_type( 'recipe' );
		register_taxonomy('course', 'recipe');
		register_taxonomy('cuisine', 'recipe');
		register_taxonomy_for_object_type( 'cuisine', 'recipe' );
		register_taxonomy_for_object_type( 'course', 'recipe' );
		import_data( 'wpudata.xml' );
		
	}

	function test_extract() {
		$this->load_wpudata();
		$post = get_post(10403);
		$log = Recipe_Pro_WPUltimate_Importer::extract( $post );
		$this->assertEquals( "Passive time 5 hours was removed.", $log->notes[0] );
		$this->do_recipe_assertions( $log->recipe );
	}

	function do_recipe_assertions( $recipe ) {
		$this->assertEquals( "Simple Tofu Quiche", $recipe->title );
		$this->assertEquals( "The simplest tofu quiche on the block with just 10 basic ingredients and no fancy methods required. A hash brown crust keeps this dish gluten free as well as vegan! Perfect for lunch, brunch and even brinner.", $recipe->description );
		$this->assertEquals( "", $recipe->servingSize );
		$this->assertEquals( "8 people", $recipe->yield );
		$this->assertEquals( new DateInterval("PT15M"), $recipe->prepTime );
		$this->assertEquals( new DateInterval("PT90M"), $recipe->cookTime );
		$this->assertEquals( "Crust", $recipe->ingredientSections[0]->name );
		$this->assertEquals( "3 medium-large potatoes", $recipe->ingredientSections[0]->items[0]->description );
		$this->assertEquals( "2 tbsp melted vegan butter (or sub olive oil with varied results)", $recipe->ingredientSections[0]->items[1]->description );
		$this->assertEquals( 3, count( $recipe->ingredientSections[0]->items) );
		$this->assertEquals( "Filling", $recipe->ingredientSections[1]->name );
		$this->assertEquals( "12.3 ounces extra firm silken tofu patted dry", $recipe->ingredientSections[1]->items[0]->description );
		$this->assertEquals( 8, count( $recipe->ingredientSections[1]->items) );
		$this->assertEquals( 2, count( $recipe->ingredientSections) );
		$this->assertEquals( "Preheat oven to 450 degrees F and lightly spritz a 9.5 inch pie pan with non-stick spray.", $recipe->instructions[0]->description );
		$this->assertEquals( 8, count( $recipe->instructions) );
		$this->assertEquals( "Vegan", $recipe->cuisine );
		$this->assertEquals( "Breakfast", $recipe->type );
	}

	function test_convert() {
		$this->load_wpudata();
		$post = get_post(10403);
		$this->assertEquals( true, Recipe_Pro_WPUltimate_Importer::is_instance( $post ) );
		$log = Recipe_Pro_WPUltimate_Importer::convert( $post );
		$this->assertEquals( true, $log->success );
		$post = get_post(10403);
		$this->assertEquals( false, Recipe_Pro_WPUltimate_Importer::is_instance( $post ) );
		$recipe = Recipe_Pro_Service::getRecipe( $post->ID );
		$this->do_recipe_assertions( $recipe );
	}
}	