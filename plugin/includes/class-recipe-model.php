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
	public function prettyInterval(\DateInterval $interval) {
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
	public function interval(\DateInterval $interval) {
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
		if (count($time) > 0) {
			$specString .= 'T';
			foreach ($time as $key => $value) {
				$specString .= $value . $key;
			}
		}
		return $specString;
	}

}


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
		$this->prepTime = new DateInterval("PT0M");
		$this->cookTime = new DateInterval("PT0M");
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
		foreach ( array( "title", "description", "author", "type",
						 "cuisine", "yield", "servingSize", "servingSize",
						 "calories", "fatContent", "saturatedFatContent",
						 "carbohydrateContent", "sugarContent", "sodiumContent",
						 "fiberContent", "proteinContent" ) as $prop ) {
			if ( array_key_exists( $prop, $jsonObj ) ) {
				$this->{$prop} = $jsonObj[$prop];
			}
		}
		foreach ( array( "prepTime", "cookTime" ) as $prop ) {
			if ( array_key_exists( $prop, $jsonObj ) ) {
				$this->{$prop} = new DateInterval($jsonObj[$prop]);
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
		$viewhelper = new Recipe_Pro_Recipe_View_Helper();
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