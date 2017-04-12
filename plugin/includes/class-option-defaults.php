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
class Recipe_Pro_Option_Defaults {
	private static $_labels;
    /**
     * A set of lables with internationalized defaults
     *
     * @since    1.0.0
     */
	public static function get_labels() {
		if (!self::$_labels) {
			self::$_labels = array(
				'overview' => __('Overview', 'recipe-pro'),
				'title' => __('Title', 'recipe-pro'),
				'ingredients' => __('Ingredients', 'recipe-pro'),
				'instructions' => __('Instructions', 'recipe-pro'),
				'notes' => __('Notes', 'recipe-pro'),
				'nutrition_information' => __('Nutrition Information', 'recipe-pro'),
				'prep_time' => __('Prep time', 'recipe-pro'),
				'cook_time' => __('Cook time', 'recipe-pro'),
				'total_time' => __('Total time', 'recipe-pro'),
				'serving_size' => __('Serving size', 'recipe-pro'),
				'hour' => __('Hour', 'recipe-pro'),
				'hours' => __('Hours', 'recipe-pro'),
				'minute' => __('Minute', 'recipe-pro'),
				'minutes' => __('Minutes', 'recipe-pro'),
				'author' => __('Author', 'recipe-pro'),
				'recipe_type' => __('Recipe Type', 'recipe-pro'),
				'cuisine' => __('Cuisine', 'recipe-pro'),
				'yield' => __('Yield', 'recipe-pro'),
				'calories' => __('Calories', 'recipe-pro'),
				'total_fat' => __('Total Fat', 'recipe-pro'),
				'saturated_fat' => __('Saturated fat', 'recipe-pro'),
				'unsaturated_fat' => __('Unsaturated fat', 'recipe-pro'),
				'trans_fat' => __('Trans fat', 'recipe-pro'),
				'cholesterol' => __('Cholesterol', 'recipe-pro'),
				'sodium' => __('Sodium', 'recipe-pro'),
				'carbohydrates' => __('Carbohydrates', 'recipe-pro'),
				'fiber' => __('Fiber', 'recipe-pro'),
				'sugars' => __('Sugars', 'recipe-pro'),
				'protein' => __('Protein', 'recipe-pro'),
				'rate_this_recipe' => __('Rate this recipe', 'recipe-pro'),
				'print' => __('Print', 'recipe-pro'),
			);
		}
		return self::$_labels;
	}

}
