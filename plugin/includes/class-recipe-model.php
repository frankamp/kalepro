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
	
class Recipe_Pro_Recipe implements JsonSerializable {

	public function __construct() {
		$this->title = "";
		$this->description = "";
		$this->author = "";
		$this->type = "";
		$this->cuisine = "";
		$this->yield = "";
		$this->ingredientSections = array();
		$this->instructions = array();
		$this->notes = array();
		$this->servingSize = "";
		$this->calories = "";
		$this->fatContent = "";
		$this->saturatedFatContent = "";
		$this->carbohydrateContent = "";
		$this->sugarContent = "";
		$this->sodiumContent = "";
		$this->fiberContent = "";
		$this->proteinContent = "";
		$a = func_get_args();
		$i = func_num_args();
		if ( $i == 1 && $i && isset( $a[0] ) && is_array( $a[0]) ) {
			$this->inflate($a[0]);
		}

	}

	private function inflate( $jsonObj ) {
		foreach ( array( "title", "description", "author", "type",
						 "cuisine", "yield", "servingSize", "servingSize",
						 "calories", "fatContent", "saturatedFatContent",
						 "carbohydrateContent", "sugarContent", "sodiumContent",
						 "fiberContent", "proteinContent" ) as $prop ) {
			if ( array_key_exists( $prop, $jsonObj ) ) {
				$this->{$prop} = $jsonObj[$prop];
			}
		}
		$this->ingredientSections = array();
		foreach ( $jsonObj['ingredients'] as $section ) {
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

	public function render() {
		ob_start();
		$recipe = $this;
		include('recipe-template.php');
		return ob_get_clean();
	}

	/**
	 * This class implements JsonSerializable as the deflation side of the serialization
	 */
	public function jsonSerialize()
	{
		return $this;
	}
}


class Recipe_Pro_Instruction {
	public function __construct($description) {
		$this->description = $description;
	}
}

class Recipe_Pro_Note {
	public function __construct($description) {
		$this->description = $description;
	}
}

class Recipe_Pro_Ingredient_Section {
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
}

class Recipe_Pro_Ingredient {
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
}

class Recipe_Pro_Volume_Unit {
	public $key;
	public $volume;
	public function __construct($key, $volume) {
		$this->key = $key;
		$this->volume = $volume;
	}
}