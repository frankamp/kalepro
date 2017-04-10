<?php

class Recipe_Pro_Service {
	/**
	* Static business methods
	**/

	static public function saveRecipe( $post_id, $recipe ) {
		return update_post_meta( (int) $post_id, (string) 'recipepro_recipe', wp_slash( json_encode( $recipe ) ) );
	}

	static public function getRecipe( $post_id ) {
		$meta_result = get_post_meta( (int) $post_id, (string) 'recipepro_recipe', true );
		if( ! $meta_result ) {
			$recipe = new Recipe_Pro_Recipe();
		} else {
			$recipe = new Recipe_Pro_Recipe(json_decode($meta_result, true));
		}
		return $recipe;
	}
}