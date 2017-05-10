<?php

class Recipe_Pro_Service {
	/**
	* Static business methods
	**/

	public static $recipe_meta_key = 'recipepro_recipe';
	public static $recipe_meta_undo_key = 'recipepro_undo';
	public static $recipe_comment_rating_key = 'recipepro_rating';

	static public function isRatingsEnabled() {
		$options = get_option( 'recipepro_main_settings', array() );
		if ( array_key_exists( 'ratingsEnabled', $options) && $options['ratingsEnabled'] == 'false') {
			return false;
		} else {
			return true;
		}
	}

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
		$comments = get_comments( array( 'post_id' => $post_id ) );
		foreach ( $comments as $comment ) {
			delete_comment_meta( $comment->comment_ID, self::$recipe_comment_rating_key );
		}
	}

	static public function saveRating( $comment_id, $rating ) {
		add_comment_meta( $comment_id, self::$recipe_comment_rating_key, $rating, true );
	}
	// may return false
	static public function getRating( $comment_id ) {
		$rating = get_comment_meta( $comment_id, self::$recipe_comment_rating_key, true );
		return $rating;
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