<?php

class Recipe_Pro_Service {
	/**
	* Static business methods
	**/

	static public function saveRecipe( $post_id, $recipe ) {
		return update_post_meta( (int) $post_id, (string) 'recipepro_recipe', wp_slash( json_encode( $recipe ) ) );
	}
}