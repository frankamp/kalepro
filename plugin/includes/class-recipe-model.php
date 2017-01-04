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