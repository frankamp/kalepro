<?php
/**
 * Class AdminTest
 *
 * @package 
 */

 /**
 * Import tests
 */

require_once RECIPE_PRO_PLUGIN_DIR . '/import/class-recipe-pro-easyrecipe-importer.php';
class ImportTest extends WP_UnitTestCase {

	function get_er_post() {
		$post = $this->factory->post->create_and_get(array(
			"post_title" => "My Title BLERRRRG",
			"post_content" => "<div class=\"item ERName\"><img class=\"alignnone size-large wp-image-10886\" src=\"http://test1.recipeproplugin.com/wp-content/uploads/2016/09/AMAZING-1-Bowl-Banana-Bread-Cinnamon-Rolls-9-ingredients-fast-and-entirely-vegan-683x1024.jpg\" alt=\"amazing-1-bowl-banana-bread-cinnamon-rolls-9-ingredients-fast-and-entirely-vegan\" width=\"683\" height=\"1024\" /></div>
				<div class=\"item ERName\"></div>
				<div class=\"item ERName\"></div>
				<div></div>
				&nbsp;
				<div class=\"easyrecipe\" data-rating=\"0\"> 	<link itemprop=\"image\" href=\"http://test2.recipeproplugin.com/wp-content/uploads/2014/12/AMAZING-10-ingredient-Tofu-Quiche-with-roasted-veggies-and-a-HASH-BROWN-CRUST-vegan-glutenfree-You-wont-miss-the-eggs-one-bit-300x300.jpg\" />
				<div class=\"item ERName\">Test as Recipe on Page Type</div>
				<div class=\"ERClear\"></div>
				<div class=\"ERHead\"><span class=\"xlate\">Recipe Type</span>: <span class=\"type\">Dessert</span></div>
				<div class=\"ERHead\">Cuisine: <span class=\"cuisine\">Vegan</span></div>
				<div class=\"ERHead\">Author: <span class=\"author\">Minimalist Baker</span></div>
				<div class=\"ERHead\">Prep time: <time itemprop=\"prepTime\" datetime=\"PT1H\">1 hour</time></div>
				<div class=\"ERHead\">Cook time: <time itemprop=\"cookTime\" datetime=\"PT15M\">15 mins</time></div>
				<div class=\"ERHead\">Total time: <time itemprop=\"totalTime\" datetime=\"PT1H15M\">1 hour 15 mins</time></div>
				<div class=\"ERHead\">Serves: <span class=\"yield\">7</span></div>
				<div class=\"ERSummary summary\">Vegan Pumpkin Pie Ice Cream Recipe Type : Dessert Cuisine: Vegan Author: Minimalist Baker Prep time: 1 hour Cook time: 15 mins Total time: 1 hour 15 mins Serves: 7 Creamy 10 ingredient vegan pumpkin pie ice cream with tons of creamy pumpkin puree. Simple to make, perfectly sweetened, and subtly spiced.</div>
				<div class=\"ERIngredients\">
				<div class=\"ERIngredientsHeader\">Ingredients</div>
				<ul class=\"ingredients\">
				 	<li class=\"ingredient\">ICE CREAM</li>
				 	<li class=\"ingredient\">1.5 cups raw cashews, soaked for 4-6 hours, or in boiling hot water for 1-2 hours*</li>
				 	<li class=\"ingredient\">1 cup dairy-free milk (such as unsweetened almond, light coconut or rice)</li>
				 	<li class=\"ingredient\">1/4 cup maple syrup (sub agave or honey if not vegan)</li>
				 	<li class=\"ingredient\">1/4 cup + 2 Tbsp brown sugar</li>
				 	</ul>
				</div>
				<div class=\"ERInstructions\">
				<div class=\"ERInstructionsHeader\">Instructions</div>
				<div class=\"instructions\">
				<ol>
				 	<li class=\"instruction\">Set your churning bowl in the freezer the night before to chill. Soak your cashews the night before as well, or for at least 4-6 hour before blending. Alternatively soak in boiling water for 1-2 hours (see notes).</li>
				 	<li class=\"instruction\">Once soaked, add well-drained cashews and remaining ingredients to a blender and blend until creamy and smooth - about 3-4 minutes, using the \"liquify\" or \"puree\" setting if you have the option to get it really creamy. Taste and adjust sweetness/flavors as needed.</li>
				 	<li class=\"instruction\">Add mixture to your chilled ice cream maker bowl and churn according to manufacturer’s instructions until thoroughly chilled - about 45 minutes. It should resemble thick soft serve.</li>
				 	<li class=\"instruction\">Transfer to a freezer-safe container, cover and freeze until hard - at least 6 hours, preferably overnight. Will keep in the freezer for up to a week.</li>
				 	<li class=\"instruction\">Take out of the freezer and thaw for 30-40 minutes - or microwave (gasp!) for 15-20 seconds - before serving to soften. Serve with brown sugar roasted pecans (see next step) and [url href=\"http://minimalistbaker.com/creamy-no-bake-pumpkin-pie/\" target=\"_blank\"]coconut whipped cream[/url] for extra oomph.</li>
				 	<li class=\"instruction\">[b]FOR THE PECANS:[/b] Preheat oven to 350 degrees F and place pecans on a foil-lined baking sheet. Toast for about 8 minutes.</li>
				 	<li class=\"instruction\">In the meantime, melt butter in a small skillet or in the microwave and stir in brown sugar, sea salt, cinnamon and cayenne.</li>
				 	<li class=\"instruction\">Remove toasted pecans from oven and toss with butter and spice mixture. Spread back onto the baking sheet and toast for another 4-7 minutes or until fragrant and golden brown, being careful not to burn.</li>
				 	<li class=\"instruction\">Let cool completely. Store leftovers in a jar for up to 1 week.</li>
				 	</ol>
				</div>
				</div>
				<div class=\"ERNutrition\">Serving size: <span class=\"servingSize\">1/7th of recipe</span> Calories: <span class=\"calories\">296</span> Sugar: <span class=\"sugar\">27.6g</span> Sodium: <span class=\"sodium\">16.7g</span></div>
				<div>
				<div class=\"ERNotesHeader\">Notes</div>
				<div class=\"ERNotes\">*For soaking cashews in boiling water, simply place raw cashews in a dish or jar, bring a large pot of water to a boil, then pour over and soak at least 1 hour, no longer than 2. Drain as usual.[br]*Prep time does not include soaking cashews or freezing.[br]*Nutrition information is a rough estimate for 1 of 7 1/2-cup servings without toppings or pecans.[br]*Adapted from [url href=\"http://www.theppk.com/2013/10/pumpkin-pie-ice-cream-video/\" target=\"_blank\"]Post Punk Kitchen[/url].</div>
				</div>
				<div class=\"endeasyrecipe\" style=\"display: none;\">3.5.3208</div>
				</div>"
		));
		return $post;
	}

	function get_test_recipe_json() {
		return '
			{
				"title":"Test as Recipe on Page Type",
				"description":"Vegan Pumpkin Pie Ice Cream Recipe Type : Dessert Cuisine: Vegan Author: Minimalist Baker Prep time: 1 hour Cook time: 15 mins Total time: 1 hour 15 mins Serves: 7 Creamy 10 ingredient vegan pumpkin pie ice cream with tons of creamy pumpkin puree. Simple to make, perfectly sweetened, and subtly spiced.",
				"imageUrl":"http://test2.recipeproplugin.com/wp-content/uploads/2014/12/AMAZING-10-ingredient-Tofu-Quiche-with-roasted-veggies-and-a-HASH-BROWN-CRUST-vegan-glutenfree-You-wont-miss-the-eggs-one-bit-300x300.jpg",
				"author":"Minimalist Baker",
				"type":"Dessert",
				"cuisine":"Vegan",
				"yield":"7",
				"ingredientSections": [
					{
						"name": "INGREDIENTS",
						"items": [
							{"quantity":"", "unit":"", "name":"", "description":"ICE CREAM"},
							{"quantity":"", "unit":"", "name":"", "description":"1.5 cups raw cashews, soaked for 4-6 hours, or in boiling hot water for 1-2 hours*"},
							{"quantity":"", "unit":"", "name":"", "description":"1 cup dairy-free milk (such as unsweetened almond, light coconut or rice)"},
							{"quantity":"", "unit":"", "name":"", "description":"1/4 cup maple syrup (sub agave or honey if not vegan)"},
							{"quantity":"", "unit":"", "name":"", "description":"1/4 cup + 2 Tbsp brown sugar"}
						]
					}
				],
				"instructions": [
				 	{"description": "Set your churning bowl in the freezer the night before to chill. Soak your cashews the night before as well, or for at least 4-6 hour before blending. Alternatively soak in boiling water for 1-2 hours (see notes)."},
					{"description": "Once soaked, add well-drained cashews and remaining ingredients to a blender and blend until creamy and smooth - about 3-4 minutes, using the \"liquify\" or \"puree\" setting if you have the option to get it really creamy. Taste and adjust sweetness/flavors as needed."},
					{"description": "Add mixture to your chilled ice cream maker bowl and churn according to manufacturer’s instructions until thoroughly chilled - about 45 minutes. It should resemble thick soft serve."},
					{"description": "Transfer to a freezer-safe container, cover and freeze until hard - at least 6 hours, preferably overnight. Will keep in the freezer for up to a week."},
					{"description": "Take out of the freezer and thaw for 30-40 minutes - or microwave (gasp!) for 15-20 seconds - before serving to soften. Serve with brown sugar roasted pecans (see next step) and <a href=\"http://minimalistbaker.com/creamy-no-bake-pumpkin-pie/\" target=\"_blank\">coconut whipped cream</a> for extra oomph."},
					{"description": "<strong>FOR THE PECANS:</strong> Preheat oven to 350 degrees F and place pecans on a foil-lined baking sheet. Toast for about 8 minutes."},
					{"description": "In the meantime, melt butter in a small skillet or in the microwave and stir in brown sugar, sea salt, cinnamon and cayenne."},
					{"description": "Remove toasted pecans from oven and toss with butter and spice mixture. Spread back onto the baking sheet and toast for another 4-7 minutes or until fragrant and golden brown, being careful not to burn."},
					{"description": "Let cool completely. Store leftovers in a jar for up to 1 week."}
				],
				"notes": [
					{"description": "*For soaking cashews in boiling water, simply place raw cashews in a dish or jar, bring a large pot of water to a boil, then pour over and soak at least 1 hour, no longer than 2. Drain as usual.<br>*Prep time does not include soaking cashews or freezing.<br>*Nutrition information is a rough estimate for 1 of 7 1/2-cup servings without toppings or pecans.<br>*Adapted from <a href=\"http://www.theppk.com/2013/10/pumpkin-pie-ice-cream-video/\" target=\"_blank\">Post Punk Kitchen</a>."}
				],
				"servingSize":"1/7th of recipe",
				"calories":"296",
				"cholesterolContent":"",
				"fatContent":"",
				"transFatContent":"",
				"saturatedFatContent":"",
				"unsaturatedFatContent":"",
				"carbohydrateContent":"",
				"sugarContent":"27.6g",
				"sodiumContent":"16.7g",
				"fiberContent":"",
				"proteinContent":"",
				"prepTime": "PT1H",
				"cookTime": "PT15M",
				"ratingValue": 0.0,
				"ratingCount": 0
		}';
	}

	function test_unfiltered_capability() {
		$this->assertEquals( false, current_user_can('unfiltered_html') );
		wp_set_current_user( null, 'admin' );
		$this->assertEquals( true, current_user_can('unfiltered_html') );
	}

	function test_parse_easyrecipe() {
		wp_set_current_user( null, 'admin' );
		$erdoc = new EasyRecipeDocument( $this->get_er_post()->post_content );
		$this->assertEquals( true, $erdoc->isEasyRecipe );
	}

	function test_is_instance() {
		wp_set_current_user( null, 'admin' );
		$this->assertEquals( true, Recipe_Pro_EasyRecipe_Importer::is_instance( $this->get_er_post() ) );
	}

	function test_extract_easyrecipe() {
		wp_set_current_user( null, 'admin' );
		$recipe = Recipe_Pro_EasyRecipe_Importer::extract( $this->get_er_post() );
		$this->assertEquals( json_decode( $this->get_test_recipe_json(), true ), json_decode(json_encode( $recipe ), true) );
	}

	function test_convert_easyrecipe() {
		wp_set_current_user( null, 'admin' );
		$post = $this->get_er_post();
		$this->assertEquals( true, Recipe_Pro_EasyRecipe_Importer::is_instance( $post ) );
		$result = Recipe_Pro_EasyRecipe_Importer::convert( $post );
		$this->assertEquals( true, $result );
		$post = get_post($post->ID);
		$this->assertEquals( false, Recipe_Pro_EasyRecipe_Importer::is_instance( $post ) );
		$this->assertContains( "[recipepro]", $post->post_content );
	}

}