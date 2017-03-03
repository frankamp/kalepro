<?php
/**
 * Serializable models
 *
 * @link       http://www.joshuafrankamp.com
 * @since      1.0.0
 *
 * @package    recipe-pro
 * @subpackage recipe-pro/includes
 */

class Recipe_Pro_Recipe_View_Helper {
	public static function prettyInterval(\DateInterval $interval) {
		$doPlural = function($nb,$str){return $nb>1?$str.'s':$str;}; // adds plurals

		$format = array();
		if($interval->y !== 0) {
			$format[] = "%y ".$doPlural($interval->y, "year");
		}
		if($interval->m !== 0) {
			$format[] = "%m ".$doPlural($interval->m, "month");
		}
		if($interval->d !== 0) {
			$format[] = "%d ".$doPlural($interval->d, "day");
		}
		if($interval->h !== 0) {
			$format[] = "%h ".$doPlural($interval->h, "hour");
		}
		if($interval->i !== 0) {
			$format[] = "%i ".$doPlural($interval->i, "min");
		}
		if($interval->s !== 0) {
			if(!count($format)) {
				return "";
			} else {
				$format[] = "%s ".$doPlural($interval->s, "second");
			}
		}

		// We use the two biggest parts
		if(count($format) > 1) {
			$format = array_shift($format)." ".array_shift($format);
		} else {
			$format = array_pop($format);
		}

		// Prepend 'since ' or whatever you like
		return $interval->format($format);
	}

	/**
	 * @param \DateInterval $interval
	 *
	 * @return string
	 */
	public static function interval(\DateInterval $interval) {
		// Reading all non-zero date parts.
		$date = array_filter(array(
			'Y' => $interval->y,
			'M' => $interval->m,
			'D' => $interval->d
		));
		// Reading all non-zero time parts.
		$time = array_filter(array(
			'H' => $interval->h,
			'M' => $interval->i,
			'S' => $interval->s
		));
		$specString = 'P';
		// Adding each part to the spec-string.
		foreach ($date as $key => $value) {
			$specString .= $value . $key;
		}
		if (count($time) == 0) {
			$time = array('M' => '0');
		}
		if (count($time) > 0) {
			$specString .= 'T';
			foreach ($time as $key => $value) {
				$specString .= $value . $key;
			}
		}
		return $specString;
	}

	public static function ldjson($recipe) {
		$json_object = array();
		$json_object['@context'] = "http://schema.org/";
		$json_object['@type'] = "Recipe";
		$json_object['name'] = $recipe->title;
		$json_object['author'] = array();
		$json_object['author']['@type'] = "Person";
		$json_object['author']['name'] = "$recipe->author";
		$json_object['datePublished'] = get_the_date('Y-m-d');
		$json_object['image'] = $recipe->imageUrl;
		$json_object['description'] = $recipe->description;
		$json_object['prepTime'] = Recipe_Pro_Recipe_View_Helper::interval( $recipe->prepTime );
		$json_object['cookTime'] = Recipe_Pro_Recipe_View_Helper::interval( $recipe->cookTime );
		$json_object['totalTime'] = Recipe_Pro_Recipe_View_Helper::interval( $recipe->cookTime );
		$json_object['aggregateRating'] = array();
		$json_object['aggregateRating']['@type'] = 'AggregateRating';
		$json_object['aggregateRating']['ratingValue'] = $recipe->ratingValue;
		$json_object['aggregateRating']['ratingCount'] = $recipe->ratingCount;
		$json_object['recipeCategory'] = $recipe->type;
		$json_object['recipeCuisine'] = $recipe->cuisine;
		$json_object['recipeYield'] = $recipe->yield;
		$json_object['recipeIngredient'] = array();
		foreach ( $recipe->ingredientSections as $section ) {
			if ( $section->name ) {
				$json_object['recipeIngredient'][] = $section->name;
	 		}
		 	foreach ( $section->items as $ingredient) {
				$json_object['recipeIngredient'][] = $ingredient->description;
		 	}
		}
		$json_object['recipeInstructions'] = array();
		foreach( $recipe->instructions as $instruction ) {
			$json_object['recipeInstructions'][] = $instruction->description;
		}
		$json_object['nutrition'] = array();
		$json_object['nutrition']['@type'] = 'NutritionInformation';
		$json_object['nutrition']['servingSize'] = $recipe->servingSize;
		$json_object['nutrition']['calories'] = $recipe->calories;
		$json_object['nutrition']['fatContent'] = $recipe->fatContent;
		$json_object['nutrition']['transFatContent'] = $recipe->transFatContent;
		$json_object['nutrition']['cholesterolContent'] = $recipe->cholesterolContent;
		$json_object['nutrition']['saturatedFatContent'] = $recipe->saturatedFatContent;
		$json_object['nutrition']['unsaturatedFatContent'] = $recipe->unsaturatedFatContent;
		$json_object['nutrition']['carbohydrateContent'] = $recipe->carbohydrateContent;
		$json_object['nutrition']['sugarContent'] = $recipe->sugarContent;
		$json_object['nutrition']['sodiumContent'] = $recipe->sodiumContent;
		$json_object['nutrition']['fiberContent'] = $recipe->fiberContent;
		$json_object['nutrition']['proteinContent'] = $recipe->proteinContent;
		return json_encode( $json_object );
	}
// 	"recipeIngredient": [
// 		"ICE CREAM",
// 		"1.5 cups raw cashews (soaked for 4-6 hours, or in boiling hot water for 1-2 hours*)",
// 		"1 cup dairy-free milk (such as unsweetened almond (light coconut or rice))",
// 		"3  Tbsp olive oil",
// 		"3\/4 cup pumpkin puree",
// 		"1\/4 cup maple syrup (sub agave or honey if not vegan)",
// 		"1\/4 cup + 2 Tbsp brown sugar",
// 		"1.5 tsp pure vanilla extract",
// 		"1\/4 tsp sea salt",
// 		"1 1\/2 tsp pumpkin pie spice",
// 		"3\/4 tsp ground cinnamon",
// 		"ROASTED PECANS (optional)",
// 		"1\/2 cup raw pecan halves",
// 		"1  Tbsp vegan butter (such as Earth Balance | or sub olive or grape seed oil)",
// 		"1  Tbsp brown sugar",
// 		"pinch each sea salt (cinnamon and cayenne pepper)"
// 	],
// 	"recipeInstructions": [
// 		"Set your churning bowl in the freezer the night before to chill. Soak your cashews the night before as well, or for at least 4-6 hour before blending. Alternatively soak in boiling water for 1-2 hours (see notes).",
// 		"Once soaked, add well-drained cashews and remaining ingredients to a blender and blend until creamy and smooth - about 3-4 minutes, using the \"liquify\" or \"puree\" setting if you have the option to get it really creamy. Taste and adjust sweetness\/flavors as needed.",
// 		"Add mixture to your chilled ice cream maker bowl and churn according to manufacturer\u2019s instructions until thoroughly chilled - about 45 minutes. It should resemble thick soft serve.",
// 		"Transfer to a freezer-safe container, cover and freeze until hard - at least 6 hours, preferably overnight. Will keep in the freezer for up to a week.",
// 		"Take out of the freezer and thaw for 30-40 minutes - or microwave (gasp!) for 15-20 seconds - before serving to soften. Serve with brown sugar roasted pecans (see next step) and [url href=\"http:\/\/minimalistbaker.com\/creamy-no-bake-pumpkin-pie\/\" target=\"_blank\"]coconut whipped cream[\/url] for extra oomph.",
// 		"[b]FOR THE PECANS:[\/b] Preheat oven to 350 degrees F and place pecans on a foil-lined baking sheet. Toast for about 8 minutes.",
// 		"In the meantime, melt butter in a small skillet or in the microwave and stir in brown sugar, sea salt, cinnamon and cayenne.",
// 		"Remove toasted pecans from oven and toss with butter and spice mixture. Spread back onto the baking sheet and toast for another 4-7 minutes or until fragrant and golden brown, being careful not to burn.",
// 		"Let cool completely. Store leftovers in a jar for up to 1 week."
// 	]
// }

}


class Recipe_Pro_Recipe implements JsonSerializable {

	public function __construct() {
		$this->title = "";
		$this->description = "";
		$this->imageUrl = "";
		$this->author = "";
		$this->type = "";
		$this->cuisine = "";
		$this->yield = "";
		$this->ingredientSections = array();
		$this->instructions = array();
		$this->notes = array();
		$this->servingSize = "";
		$this->calories = "";
		$this->cholesterolContent = "";
		$this->fatContent = "";
		$this->transFatContent = "";
		$this->saturatedFatContent = "";
		$this->unsaturatedFatContent = "";
		$this->carbohydrateContent = "";
		$this->sugarContent = "";
		$this->sodiumContent = "";
		$this->fiberContent = "";
		$this->proteinContent = "";
		$this->prepTime = new DateInterval("PT0M");
		$this->cookTime = new DateInterval("PT0M");
		$this->ratingValue = 0.0;
		$this->ratingCount = 0;
		$a = func_get_args();
		$i = func_num_args();
		if ( $i == 1 && $i && isset( $a[0] ) && is_array( $a[0]) ) {
			$this->inflate($a[0]);
		}
	}

	public function totalTime() {
		$reference = new DateTime();
		$endTime = clone $reference;
		$endTime->add($this->prepTime);
		$endTime->add($this->cookTime);
		return $reference->diff($endTime);
	}

	private function inflate( $jsonObj ) {
		foreach ( array( "title", "description", "imageUrl", "author", "type",
						 "cuisine", "yield", "servingSize", "servingSize",
						 "calories", "cholesterolContent", "fatContent", "transFatContent", "saturatedFatContent", "unsaturatedFatContent",
						 "carbohydrateContent", "sugarContent", "sodiumContent",
						 "fiberContent", "proteinContent", "ratingCount", "ratingValue" ) as $prop ) {
			if ( array_key_exists( $prop, $jsonObj ) ) {
				$this->{$prop} = $jsonObj[$prop];
			}
		}
		foreach ( array( "prepTime", "cookTime" ) as $prop ) {
			if ( array_key_exists( $prop, $jsonObj ) ) {
				$this->{$prop} = new DateInterval( $jsonObj[$prop] );
			}
		}
		$this->ingredientSections = array();
		foreach ( $jsonObj['ingredientSections'] as $section ) {
			array_push( $this->ingredientSections, new Recipe_Pro_Ingredient_Section(
				$section['name'],
				$section['items']
			));
		}
		$this->instructions = array();
		foreach ( $jsonObj['instructions'] as $instruction ) {
			array_push( $this->instructions, new Recipe_Pro_Instruction(
				$instruction['description']
			));
		}
		$this->notes = array();
		foreach ( $jsonObj['notes'] as $note ) {
			array_push( $this->notes, new Recipe_Pro_Note(
				$note['description']
			));
		}
	}

	public function setPrepTimeByValue( $timeString ) {
		$this->prepTime = new DateInterval( $timeString );
	}

	public function setCookTimeByValue( $timeString ) {
		$this->cookTime = new DateInterval( $timeString );
	}

	public function render() {
		$recipe = $this;
		$viewhelper = new Recipe_Pro_Recipe_View_Helper();
		ob_start();
		include('recipe-template.php');
		return ob_get_clean();
	}

	/**
	 * This class implements JsonSerializable as the deflation side of the serialization
	 */
	public function jsonSerialize() {
		$copy = array();
		foreach( $this as $key => $val ) { 
			$copy[$key] = $val;
		}
		foreach ( array( "prepTime", "cookTime" ) as $prop ) {
			$copy[$prop] = Recipe_Pro_Recipe_View_Helper::interval( $copy[$prop] );
		}
		return $copy;
	}
}


class Recipe_Pro_Instruction implements JsonSerializable {
	public function __construct($description) {
		$this->description = $description;
	}

	public function jsonSerialize() {
		return $this;
	}
}

class Recipe_Pro_Note implements JsonSerializable {
	public function __construct($description) {
		$this->description = $description;
	}

	public function jsonSerialize() {
		return $this;
	}
}

class Recipe_Pro_Ingredient_Section implements JsonSerializable {
	public function __construct($name, $items) {
		$this->name = $name;
		$this->items = array();
		foreach ( $items as $ingredient ) {
			array_push( $this->items, new Recipe_Pro_Ingredient(
				$ingredient['quantity'],
				$ingredient['unit'],
				$ingredient['name'],
				$ingredient['description']
			));
		}
	}

	public function jsonSerialize() {
		return $this;
	}
}

class Recipe_Pro_Ingredient implements JsonSerializable {
	public function __construct($quantity, $unit, $name, $description) {
		$this->quantity = $quantity;
		$this->unit =  $unit;
		$this->name = $name;
		$this->description = $description;
	}

	/**
	 * The list of all valid units we support
	 */
	public static function units() {
		return array(
			'teaspoon' => new Recipe_Pro_Volume_Unit('teaspoon', '')
		);
	}

	public function jsonSerialize() {
		return $this;
	}
}

class Recipe_Pro_Volume_Unit {
	public $key;
	public $volume;
	public function __construct($key, $volume) {
		$this->key = $key;
		$this->volume = $volume;
	}
}