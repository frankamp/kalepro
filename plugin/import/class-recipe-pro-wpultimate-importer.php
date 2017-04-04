<?php

require_once __DIR__."/../includes/class-recipe-pro-service.php";


class Recipe_Pro_WPUltimate_Importer {
	public static $shortcodePattern = "/.*\[ultimate-recipe.*?id=\"([0-9]+)\".*?\].*/";
	/**
	* I return a boolean: whether or not the wppost is an instance of the foreign recipe type
	* e.g. an ER recipe. Semantically this should return true before a convert() for a 
	* potential future Recipe Pro recipe, and false after because the recipe has been converted.
	**/
	static public function is_instance($wppost) {
		if ( count( self::search_instance( $wppost ) ) > 1 ) {
			return true;
		} else {
			return false;
		}
	}

	static private function search_instance($wppost) {
		$matches = array();
	 	preg_match( self::$shortcodePattern, $wppost->post_content, $matches);
	 	return $matches;
	}

	/**
	* I take a wordpress post and transform the content from the source to the target
	* It should not be assumed that the object can be used after this function is called
	* but rather re-retrieved from wordpress api. Destructive.
	**/
	static public function convert($wppost) {
		$matches = self::search_instance( $wppost );
	 	if ( count($matches) > 1 ){
	 		$new_recipe = self::extract($wppost);
	 		// persist new recipe
	 		// do replacement
	 		return true;
	 	} else {
	 		return false;
	 	}
	}

	/**
	* I take a wordpress post and transform its first recipe into a Recipe Pro Recipe and return it 
	* non destructively. The post should already pass is_instance before this is called
	**/
	static public function extract($wppost) {
		// need a compliment to all the logic here
		// $post = get_post( $id );
		// $post_meta = get_post_custom( $id );
		$matches = self::search_instance( $wppost );
 		$recipe_post_id = $matches[1];
 		$wpu_recipe = new Recipe_Pro_WPURP_Recipe(get_post( (int) $recipe_post_id ));
		// $import_type = isset( $post_data['wpurp-import-type'] ) ? $post_data['wpurp-import-type'] : '';

		// // If the import type is not set, redirect back.
		// if ( ! in_array( $import_type, array( 'convert', 'hide' ), true ) ) {
		// 	wp_safe_redirect( add_query_arg( array( 'from' => $this->get_uid(), 'error' => rawurlencode( 'You need to select an import type.' ) ), admin_url( 'admin.php?page=wprm_import' ) ) );
		// 	exit();
		// }

 		
		// // If we're converting the WPURP recipe to a normal post we want the import ID to be 0.
		// $import_id = 'convert' === $import_type ? 0 : $id;

		// $recipe = array(
		// 	'import_id' => $import_id,
		// 	'import_backup' => array(
		// 		'wpultimaterecipe_recipe_id' => $id,
		// 		'wpultimaterecipe_import_type' => $import_type,
		// 	),
		// );
		$recipe = new Recipe_Pro_Recipe();

		// $alternate_image = isset( $post_meta['recipe_alternate_image'] ) ? $post_meta['recipe_alternate_image'][0] : false;
		// $recipe['image_id'] = $alternate_image ? $alternate_image : get_post_thumbnail_id( $id );
		$image_id = $wpu_recipe->alternate_image() ?:  $wpu_recipe->featured_image();
		$recipe->imageUrl = wp_get_attachment_url($image_id);

		// $recipe_title = isset( $post_meta['recipe_title'] ) ? $post_meta['recipe_title'][0] : false;
		// $recipe['name'] = $recipe_title ? $recipe_title : $post->post_title;
		$recipe->title = $wpu_recipe->title();		

		// $recipe['summary'] = isset( $post_meta['recipe_description'] ) ? $post_meta['recipe_description'][0] : '';
		$recipe->description = $wpu_recipe->description() ?: '';

		// $recipe['servings'] = isset( $post_meta['recipe_servings'] ) ? $post_meta['recipe_servings'][0] : '';
		// $recipe['servings_unit'] = isset( $post_meta['recipe_servings_type'] ) ? $post_meta['recipe_servings_type'][0] : '';
		$recipe->servingSize = ($wpu_recipe->servings() ?: '1') . ' ' . ($wpu_recipe->servings_type() ?: 'serving');

		// $recipe['notes'] = isset( $post_meta['recipe_notes'] ) ? $post_meta['recipe_notes'][0] : '';
		if ( $wpu_recipe->notes() ) {
			array_push( $recipe->notes, new Recipe_Pro_Note(
				$wpu_recipe->notes()
			));
		}

		// // Recipe Times.
		// $prep_time = isset( $post_meta['recipe_prep_time'] ) ? $post_meta['recipe_prep_time'][0] : '';
		// $prep_time_unit = isset( $post_meta['recipe_prep_time_text'] ) ? $post_meta['recipe_prep_time_text'][0] : '';
		// $recipe['prep_time'] = self::get_time_in_minutes( $prep_time, $prep_time_unit );

		// $cook_time = isset( $post_meta['recipe_cook_time'] ) ? $post_meta['recipe_cook_time'][0] : '';
		// $cook_time_unit = isset( $post_meta['recipe_cook_time_text'] ) ? $post_meta['recipe_cook_time_text'][0] : '';
		// $recipe['cook_time'] = self::get_time_in_minutes( $cook_time, $cook_time_unit );

		// $passive_time = isset( $post_meta['recipe_passive_time'] ) ? $post_meta['recipe_passive_time'][0] : '';
		// $passive_time_unit = isset( $post_meta['recipe_passive_time_text'] ) ? $post_meta['recipe_passive_time_text'][0] : '';
		// $passive_time_minutes = self::get_time_in_minutes( $passive_time, $passive_time_unit );

		// $recipe['total_time'] = $recipe['prep_time'] + $recipe['cook_time'] + $passive_time_minutes;

		// // Recipe Tags.
		// $recipe['tags'] = array();

		// $wprm_taxonomies = WPRM_Taxonomies::get_taxonomies();
		// foreach ( $wprm_taxonomies as $wprm_taxonomy => $options ) {
		// 	$wprm_key = substr( $wprm_taxonomy, 5 );
		// 	$tag = isset( $post_data[ 'wpurp-tags-' . $wprm_key ] ) ? $post_data[ 'wpurp-tags-' . $wprm_key ] : false;

		// 	if ( $tag ) {
		// 		$terms = get_the_terms( $id, $tag );
		// 		if ( $terms && ! is_wp_error( $terms ) ) {
		// 			foreach ( $terms as $term ) {
		// 				$recipe['tags'][ $wprm_key ][] = $term->name;
		// 			}
		// 		}
		// 	}
		// }

		// // Recipe Ingredients.
		// $ingredients = isset( $post_meta['recipe_ingredients'] ) ? maybe_unserialize( $post_meta['recipe_ingredients'][0] ) : array();
		// $recipe['ingredients'] = array();

		// $current_group = array(
		// 	'name' => '',
		// 	'ingredients' => array(),
		// );
		// foreach ( $ingredients as $ingredient ) {
		// 	if ( isset( $ingredient['group'] ) && $ingredient['group'] !== $current_group['name'] ) {
		// 		$recipe['ingredients'][] = $current_group;
		// 		$current_group = array(
		// 			'name' => $ingredient['group'],
		// 			'ingredients' => array(),
		// 		);
		// 	}

		// 	$current_group['ingredients'][] = array(
		// 		'amount' => $ingredient['amount'],
		// 		'unit' => $ingredient['unit'],
		// 		'name' => $ingredient['ingredient'],
		// 		'notes' => $ingredient['notes'],
		// 	);
		// }
		// $recipe['ingredients'][] = $current_group;

		// // Recipe Instructions.
		// $instructions = isset( $post_meta['recipe_instructions'] ) ? maybe_unserialize( $post_meta['recipe_instructions'][0] ) : array();
		// $recipe['instructions'] = array();

		// $current_group = array(
		// 	'name' => '',
		// 	'instructions' => array(),
		// );
		// foreach ( $instructions as $instruction ) {
		// 	if ( isset( $instruction['group'] ) && $instruction['group'] !== $current_group['name'] ) {
		// 		$recipe['instructions'][] = $current_group;
		// 		$current_group = array(
		// 			'name' => $instruction['group'],
		// 			'instructions' => array(),
		// 		);
		// 	}

		// 	$current_group['instructions'][] = array(
		// 		'text' => $instruction['description'],
		// 		'image' => $instruction['image'],
		// 	);
		// }
		// $recipe['instructions'][] = $current_group;

		// // Recipe Nutrition.
		// $recipe['nutrition'] = array();

		// $nutrition_mapping = array(
		// 	'serving_size'          => 'serving_size',
		// 	'calories'              => 'calories',
		// 	'carbohydrate'          => 'carbohydrates',
		// 	'protein'               => 'protein',
		// 	'fat'                   => 'fat',
		// 	'saturated_fat'         => 'saturated_fat',
		// 	'polyunsaturated_fat'   => 'polyunsaturated_fat',
		// 	'monounsaturated_fat'   => 'monounsaturated_fat',
		// 	'trans_fat'             => 'trans_fat',
		// 	'cholesterol'           => 'cholesterol',
		// 	'sodium'                => 'sodium',
		// 	'potassium'             => 'potassium',
		// 	'fiber'                 => 'fiber',
		// 	'sugar'                 => 'sugar',
		// 	'vitamin_a'             => 'vitamin_a',
		// 	'vitamin_c'             => 'vitamin_c',
		// 	'calcium'               => 'calcium',
		// 	'iron'                  => 'iron',
		// );

		// $nutrition = isset( $post_meta['recipe_nutritional'] ) ? maybe_unserialize( $post_meta['recipe_nutritional'][0] ) : array();

		// foreach ( $nutrition_mapping as $wpurp_field => $wprm_field ) {
		// 	$recipe['nutrition'][ $wprm_field ] = isset( $nutrition[ $wpurp_field ] ) ? $nutrition[ $wpurp_field ] : '';
		// }

		// return $recipe;
		
		
		
		
		// $recipe->author = $wpu_recipe->author();
		// $recipe->type = $data->type;
		// $recipe->cuisine = $data->cuisine;
		// $recipe->yield = $data->yield;
		
		// $recipe->calories = $data->calories ?: "";
		// $recipe->cholesterolContent = $data->cholesterol ?: "";
		// $recipe->fatContent = $data->fat ?: "";
		// $recipe->saturatedFatContent = $data->saturatedFat ?: "";
		// $recipe->unsaturatedFatContent = $data->unsaturatedFat ?: "";
		// $recipe->transFatContent = $data->transFat ?: "";
		// $recipe->carbohydrateContent = $data->carbohydrates ?: "";
		// $recipe->sugarContent = $data->sugar ?: "";
		// $recipe->sodiumContent = $data->sodium ?: "";
		// $recipe->fiberContent = $data->fiber ?: "";
		// $recipe->proteinContent = $data->protein ?: "";
		// if ( $data->preptimeISO ) {
		// 	$recipe->setPrepTimeByValue( $data->preptimeISO );
		// }
		// if ( $data->cooktimeISO ) {
		// 	$recipe->setCookTimeByValue( $data->cooktimeISO );
		// }
		// <!-- r.recipe_title //(override from recipe post title)
		// r.recipe_description
		// r.recipe_rating // apparently possible values are 0-5 (for a range of 6)
		// r.recipe_servings // or r.recipe_servings_normalized?
		// r.recipe_servings_type // the ice creams in '7 ice creams'
		// r.recipe_prep_time // like 1
		// r.recipe_prep_time_text // like hour
		// r.recipe_cook_time 
		// r.recipe_cook_time_text
		// r.recipe_passive_time // what to do
		// r.recipe_passive_time_text // what to do
		// wp_get_attachment_url( r.recipe_alternate_image ) -->
		// otherwise default it to the post's image

			     //    if( $recipe->has_ingredients() ) {
        //     $metadata_ingredients = array();

        //     foreach( $recipe->ingredients() as $ingredient ) {
        //         $metadata_ingredient = $ingredient['amount'] . ' ' . $ingredient['unit'] . ' ' . $ingredient['ingredient'];
        //         if( trim( $ingredient['notes'] ) !== '' ) {
        //             $metadata_ingredient .= ' (' . $ingredient['notes'] . ')';
        //         }

        //         $metadata_ingredients[] = $metadata_ingredient;
        //     }

        //     $metadata['recipeIngredient'] = $metadata_ingredients;
        // }


        // // Instructions
        // if( $recipe->has_instructions() ) {
        //     $metadata_instructions = array();

        //     foreach( $recipe->instructions() as $instruction ) {
        //         $metadata_instructions[] = $instruction['description'];
        //     }

        //     $metadata['recipeInstructions'] = $metadata_instructions;
        // }
	 	return $recipe;
	 	//$meta_result = get_post_meta( (int) $post->ID, (string) 'recipepro_recipe', true );
	}
}

class Recipe_Pro_WPURP_Recipe {

    private $post;
    private $meta;
    private $fields = array(
        'recipe_custom_template',
        'recipe_title',
        'recipe_alternate_image',
        'recipe_description',
        'recipe_rating',
        'recipe_servings',
        'recipe_servings_type',
        'recipe_prep_time',
        'recipe_prep_time_text',
        'recipe_cook_time',
        'recipe_cook_time_text',
        'recipe_passive_time',
        'recipe_passive_time_text',
        'recipe_ingredients',
        'recipe_instructions',
        'recipe_notes',
    );

    public function __construct( $post )
    {
        // Get associated post
        if( is_object( $post ) && $post instanceof WP_Post ) {
            $this->post = $post;
        } else if( is_numeric( $post ) ) {
            $this->post = get_post( $post );
        } else {
            throw new InvalidArgumentException( 'Recipes can only be instantiated with a Post object or Post ID.' );
        }

        // Get metadata
        $this->meta = get_post_custom( $this->post->ID );
    }

    public function is_present( $field )
    {
        $nutrition_field = '';
        if( substr( $field, 0, 16 ) == 'recipe_nutrition' ) {
            $nutrition_field = substr( $field, 17 );
            $field = 'recipe_nutrition';
        }
        
        switch( $field ) {
            case 'recipe_image':
                return $this->image_ID();

            case 'recipe_featured_image':
                return get_post_thumbnail_id( $this->ID() ) != '';

            case 'recipe_ingredients':
                return $this->has_ingredients();

            case 'recipe_instructions':
                return $this->has_instructions();

            case 'recipe_post_content':
                return trim( $this->post_content() ) != '';

            case 'recipe_rating':
                // Not present if rating = 0 (not displayed)
                $val = $this->meta($field);
                return isset( $val ) && trim( $val ) != '' && $val != '0';

            case 'recipe_rating_author':
                $val = $this->meta( 'recipe_rating' );
                return isset( $val ) && trim( $val ) != '' && $val != '0';

            case 'recipe_nutrition':
                $val = $this->nutritional( $nutrition_field );
                return isset( $val ) && trim( $val ) != '';

            default:
                $val = $this->meta($field);
                return isset( $val ) && trim( $val ) != '';
        }
    }

    public function meta( $field )
    {
        if( isset( $this->meta[$field] ) ) {
            return $this->meta[$field][0];
        }

        return null;
    }

    public function fields()
    {
        return $this->fields;
    }

    // public function output( $type = 'recipe', $template = 'default' )
    // {
    //     if( $type == 'recipe' && $template == 'default' && !is_null( $this->template() ) ) {
    //         $template = $this->template();
    //     }

    //     $template = WPUltimateRecipe::get()->template( $type, $template );
    //     $template->output( $this, $type );
    // }

    public function output_string( $type = 'recipe', $template = 'default' )
    {
        ob_start();
        $this->output( $type, $template );
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function has_ingredients()
    {
        $ingredients = $this->ingredients();
        return !empty( $ingredients );
    }

    public function has_instructions()
    {
        $instructions = $this->instructions();
        return !empty( $instructions );
    }

    public function get_time_meta_string( $amount, $unit )
    {
        $meta = false;

        if( strtolower( $unit ) == strtolower( __( 'minute', 'wp-ultimate-recipe' ) )
            || strtolower( $unit ) == strtolower( __( 'minutes', 'wp-ultimate-recipe' ) )
            || strtolower( $unit ) == 'min'
            || strtolower( $unit ) == 'mins' ) {
            $meta = 'PT' . $amount . 'M';
        } elseif( strtolower( $unit ) == strtolower( __( 'hour', 'wp-ultimate-recipe' ) )
            || strtolower( $unit ) == strtolower( __( 'hours', 'wp-ultimate-recipe' ) )
            || strtolower( $unit ) == 'hr'
            || strtolower( $unit ) == 'hrs' ) {
            $meta = 'PT' . $amount . 'H';
        }
        
        return $meta;
    }

    // Ingredient Fields

    public function alternate_image()
    {
        return $this->meta( 'recipe_alternate_image' );
    }

    public function alternate_image_url( $type )
    {
        $thumb = wp_get_attachment_image_src( $this->alternate_image(), $type );
        return $thumb ? $thumb['0'] : '';
    }

    public function author()
    {
        $author_id = $this->post->post_author;

        if( $author_id == 0 ) {
            return $this->meta( 'recipe-author' );
        } else {
            $author = get_userdata( $this->post->post_author );

            return $author->data->display_name;
        }
    }

    public function cook_time()
    {
        return $this->meta( 'recipe_cook_time' );
    }

    public function cook_time_meta()
    {
        $meta = false;

        $amount = esc_attr( $this->cook_time() );
        $unit = strtolower( $this->cook_time_text() );

        $meta = $this->get_time_meta_string( $amount, $unit );

        return $meta;
    }

    public function cook_time_text()
    {
        return $this->meta( 'recipe_cook_time_text' );
    }

    public function date()
    {
        return $this->post->post_date;
    }

    public function description()
    {
        return $this->meta( 'recipe_description' );
    }

    public function excerpt()
    {
        return $this->post->post_excerpt;
    }

    public function featured_image()
    {
        return get_post_thumbnail_id( $this->ID() );
    }

    public function featured_image_url( $type )
    {
        $thumb = wp_get_attachment_image_src( $this->featured_image(), $type );
        return $thumb['0'];
    }

    public function ID()
    {
        return $this->post->ID;
    }

    public function image_url( $type )
    {
        $thumb = wp_get_attachment_image_src( $this->image_ID(), $type );
        return $thumb['0'];
    }

    public function image_ID()
    {
        if( WPUltimateRecipe::option( 'recipe_alternate_image', '1' ) == '1' ) {
            $image_id = $this->alternate_image() ? $this->alternate_image() : $this->featured_image();
        } else {
            $image_id = $this->featured_image();
        }
        return $image_id;
    }

    public function ingredients()
    {
        $ingredients = @unserialize( $this->meta( 'recipe_ingredients' ) );

        // Try to fix serialize offset issues
        if( $ingredients === false ) {
            $ingredients = unserialize( preg_replace_callback ( '!s:(\d+):"(.*?)";!', array( $this, 'regex_replace_serialize' ), $this->meta( 'recipe_ingredients' ) ) );
        }

        return apply_filters( 'wpurp_recipe_field_ingredients', $ingredients, $this );
    }

    public function instructions()
    {
        $instructions = @unserialize( $this->meta( 'recipe_instructions' ) );

        // Try to fix serialize offset issues
        if( $instructions === false ) {
            $instructions = unserialize( preg_replace_callback ( '!s:(\d+):"(.*?)";!', array( $this, 'regex_replace_serialize' ), $this->meta( 'recipe_instructions' ) ) );
        }

        return apply_filters( 'wpurp_recipe_field_instructions', $instructions, $this );
    }

    public function regex_replace_serialize( $match )
    {
        return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
    }

    public function link()
    {
        return get_permalink( $this->ID() );
    }

    // public function link_print()
    // {
    //     $keyword = urlencode( WPUltimateRecipe::option( 'print_template_keyword', 'print' ) );
    //     if( strlen( $keyword ) <= 0 ) {
    //         $keyword = 'print';
    //     }

    //     $link = $this->link();

    //     if( get_option('permalink_structure') ) {
    //         if( substr( $link, -1) != '/' ) {
    //             $link .= '/';
    //         }
    //         $link .= $keyword;

    //         // Make sure slug is present
    //         if( WPUltimateRecipe::option( 'remove_recipe_slug', '0' ) == '1' ) {

    //             $recipe_slug = $this->post->post_name;
    //             $post_type_slug = WPUltimateRecipe::option( 'recipe_slug', 'recipe' );
    //             $link = str_replace( '/' . $recipe_slug . '/', '/' . $post_type_slug . '/' . $recipe_slug . '/', $link );
    //         }
    //     } else {
    //         $link .= '&' . $keyword;
    //     }

    //     return $link;
    // }

    public function notes()
    {
        return $this->meta( 'recipe_notes' );
    }

    public function nutritional( $field = false )
    {
        $nutritional = apply_filters( 'wpurp_recipe_field_nutritional', unserialize( $this->meta( 'recipe_nutritional' ) ), $this );

        if( $field ) {
            $nutritional = isset( $nutritional[$field] ) ? $nutritional[$field] : '';
        }

        return $nutritional;
    }

    public function passive_time()
    {
        return $this->meta( 'recipe_passive_time' );
    }

    public function passive_time_text()
    {
        return $this->meta( 'recipe_passive_time_text' );
    }

    public function post_content()
    {
        return $this->post->post_content;
    }

    public function post_status()
    {
        return $this->post->post_status;
    }

    public function prep_time()
    {
        return $this->meta( 'recipe_prep_time' );
    }

    public function prep_time_meta()
    {
        $meta = false;

        $amount = esc_attr( $this->prep_time() );
        $unit = strtolower( $this->prep_time_text() );

        $meta = $this->get_time_meta_string( $amount, $unit );

        return $meta;
    }

    public function prep_time_text()
    {
        return $this->meta( 'recipe_prep_time_text' );
    }

    // public function rating()
    // {
    //     if( WPUltimateRecipe::is_addon_active( 'user-ratings' ) && WPUltimateRecipe::option( 'user_ratings_enable', 'everyone' ) != 'disabled' ) {
    //         $user_rating = WPURP_User_Ratings::get_recipe_rating( $this->ID() );
    //         return $user_rating['rating'];
    //     } else {
    //         return $this->rating_author();
    //     }
    // }

    public function rating_author()
    {
        return $this->meta( 'recipe_rating' );
    }

    public function servings()
    {
        return $this->meta( 'recipe_servings' );
    }

    public function servings_normalized()
    {
        return $this->meta( 'recipe_servings_normalized' );
    }

    public function servings_type()
    {
        return $this->meta( 'recipe_servings_type' );
    }

    public function template()
    {
        $template = $this->meta( 'recipe_custom_template' );
        return is_null( $template ) ? 'default' : $template;
    }

    public function terms()
    {
        return unserialize( $this->meta( 'recipe_terms' ) );
    }

    public function terms_with_parents()
    {
        return unserialize( $this->meta( 'recipe_terms_with_parents' ) );
    }

    public function title()
    {
        if ( $this->meta( 'recipe_title' ) ) {
            return $this->meta( 'recipe_title' );
        } else {
            return $this->post->post_title;
        }
    }

    // Custom fields
    public function custom_field( $field )
    {
        return $this->meta( $field );
    }

}