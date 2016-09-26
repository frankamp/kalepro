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
    public $title;
    public $ingredients;

    public function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if ( $i == 1 && $i && isset($a[0]) && is_array($a[0])) {
            $this->inflate($a[0]);
        }
        $this->title = "";
        $this->ingredients = array();
        $this->servingSize = "";
        $this->calories = "";
        $this->fatContent = "";
        $this->saturatedFatContent = "";
        $this->carbohydrateContent = "";
        $this->sugarContent = "";
        $this->sodiumContent = "";
        $this->fiberContent = "";
        $this->proteinContent = "";
        $this->saturatedFatContent = "";

    }

    private function inflate( $jsonObj ) {
        $this->title = $jsonObj['title'];
        $this->ingredients = array();
        foreach ($jsonObj['ingredients'] as $ingredient) {
            array_push($this->ingredients, new Recipe_Pro_Ingredient(
                $ingredient['quantity'],
                $ingredient['unit'],
                $ingredient['name'],
                $ingredient['html']
            ));
        }
    }

    /**
     * This class implements JsonSerializable as the deflation side of the serialization
     */
    public function jsonSerialize()
    {
        return $this;
    }
}

class Recipe_Pro_Ingredient {
    public $quantity;
    public $unit;
    public $name;
    public $html;

    public function __construct($quantity, $unit, $name, $html) {
        $this->quantity = $quantity;
        $this->unit =  $unit;
        $this->name = $name;
        $this->html = $html;
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