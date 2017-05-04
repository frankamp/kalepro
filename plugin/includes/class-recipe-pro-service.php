<?php

class Recipe_Pro_Service {
	/**
	* Static business methods
	**/

	public static $recipe_meta_key = 'recipepro_recipe';
	public static $recipe_meta_undo_key = 'recipepro_undo';

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

	static public function removeRecipe( $post_id ) {
		delete_post_meta( (int) $post_id, (string) self::$recipe_meta_key);
	}

	static public function saveUndoInformation( $post_id, $undo_data ) {
		return update_post_meta( (int) $post_id, (string) self::$recipe_meta_undo_key, $undo_data);
	}
	
	static public function getUndoInformation( $post_id ) {
		$meta_result = get_post_meta( (int) $post_id, (string) self::$recipe_meta_undo_key, true );
		if( ! $meta_result ) {
			return false;
		} else {
			return $meta_result;
		}
	}
	
	static public function removeUndoInformation( $post_id ) {
		delete_post_meta( (int) $post_id, (string) self::$recipe_meta_undo_key);
	}

}