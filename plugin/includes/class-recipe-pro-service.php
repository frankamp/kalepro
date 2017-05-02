<?php

class Recipe_Pro_Service {
	/**
	* Static business methods
	**/

	public static $recipe_meta_key = 'recipepro_recipe';

	static public function saveRecipe( $post_id, $recipe ) {
		return update_post_meta( (int) $post_id, (string) self::$recipe_meta_key, wp_slash( json_encode( $recipe ) ) );
	}

	static public function getRecipe( $post_id ) {
		$meta_result = get_post_meta( (int) $post_id, (string) self::$recipe_meta_key, true );
		if( ! $meta_result ) {
			$recipe = new Recipe_Pro_Recipe();
		} else {
			$recipe = new Recipe_Pro_Recipe(json_decode($meta_result, true));
		}
		return $recipe;
	}

	// save undo data?
	// get undo data? -> importer revert() -> calls importer impl revert()
}