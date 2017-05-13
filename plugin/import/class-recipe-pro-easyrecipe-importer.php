<?php

require_once __DIR__."/../includes/class-recipe-pro-service.php";
require_once __DIR__."/class-recipe-pro-import-log.php";

class Recipe_Pro_EasyRecipe_Importer {
	public static $shortname = 'easyrecipe';
	/**
	* I return a boolean: whether or not the wppost is an instance of the foreign recipe type
	* e.g. an ER recipe. Semantically this should return true before a convert() for a 
	* potential future Recipe Pro recipe, and false after because the recipe has been converted.
	**/
	static public function is_instance($wppost) {
		$erdoc = new EasyRecipeDocument( $wppost->post_content );
		return $erdoc->isEasyRecipe;
	}


	static public function undo( $wppost ) {
		$undo = Recipe_Pro_Service::getUndoInformation( $wppost->ID );
		if ( $undo['importer'] != self::$shortname ) {
			return false;
		} else {
			$content = str_replace( '[recipepro]', $undo['old_recipe'], $wppost->post_content );
			$update_content = array(
				'ID' => $wppost->ID,
				'post_content' => $content,
			);
			wp_update_post( $update_content );
			Recipe_Pro_Service::removeUndoInformation( $wppost->ID );
			Recipe_Pro_Service::removeRecipe( $wppost->ID );
			return true;
		}
	}

	/**
	* I take a wordpress post and transform the content from the source to the target
	* It should not be assumed that the object can be used after this function is called
	* but rather re-retrieved from wordpress api. Destructive.
	**/
	static public function convert($wppost) {
		// run extract on the post
		try {
			$result = self::extract( $wppost );
			$extracted = $result->recipe;
		} catch (Exception $e) {
			$result = new Recipe_Pro_Import_Log();
			$result->success = false;
			$result->addNote("Error extracting old recipe: " . $e->getMessage() );
		}
		// take the model and write it to the post's metadata
		try {
			$comments = get_comments( array( 'post_id' => $wppost->ID ) );
			$ratings = 0;
			$aggregate = 0;
			foreach ( $comments as $comment ) {
				$comment_rating = intval( get_comment_meta( $comment->comment_ID, 'ERRating', true ) );
				if ( $comment_rating && $comment_rating > 0 && $comment_rating < 6 ) {
					update_comment_meta( $comment->comment_ID, 'recipepro_rating', strval( $comment_rating ) );
					$ratings += 1;
					$aggregate += $comment_rating;
				}
			}
			if ( $ratings > 0 ) {
				$extracted->ratingValue = round( $aggregate / $ratings, 1);
				$extracted->ratingCount = $ratings;
			}
			Recipe_Pro_Service::saveRecipe( $wppost->ID, $extracted );
		} catch (Exception $e) {
			$result->success = false;
			$result->addNote("Error saving new recipe: " . $e->getMessage() );
		}

		try {
			$erdoc = new EasyRecipeDocument( $wppost->post_content );
			// remove the ERP bits from the post + add the shortcode that renders the other bits
			

			$replacement = $erdoc->createTextNode( "[recipepro]" );
			$old_recipe = $erdoc->saveHTML($erdoc->getRecipe());
			$erdoc->getRecipe()->parentNode->replaceChild( $replacement, $erdoc->getRecipe() );
			$post_changes = array(
			      'ID'           => $wppost->ID,
			      'post_content' => $erdoc->getHTML( true ),
			);
			$updated = wp_update_post( $post_changes );
			Recipe_Pro_Service::saveUndoInformation( $wppost->ID, array( 'importer'=> self::$shortname, 'old_recipe' => $old_recipe, 'notes' => $result->notes ));
		} catch (Exception $e) {
			$result->success = false;
			$result->addNote("Error replacing old recipe: " . $e->getMessage() );
		}

		if ( $updated == 0 ) {
			return $result;
		}
		$result->success = true;
		return $result;
	}

	/**
	* I convert ER ingredient array to RP ingredient array
	* 	       array(16) {
	*	         [0]=>
	*	         object(stdClass)#163 (2) {
	*	           ["ingredient"]=>
	*	           string(9) "ICE CREAM"
	*	           ["isImage"]=>
	*	           bool(false)
	*	         }
	*	         [1]=>
	*	         object(stdClass)#181 (2) {
	*	           ["ingredient"]=>
	*	           string(82) "1.5 cups raw cashews, soaked for 4-6 hours, or in boiling hot water for 1-2 hours*"
	*	           ["isImage"]=>
	*	           bool(false)
	*	         }
	**/
	static private function convertIngredients($ingredients) {
		$collection = array();
		foreach ( $ingredients as $ingredient ) {
			array_push($collection, array(
				"description" => EasyRecipeDocument::convertShortcodes( $ingredient->ingredient ),
				"quantity" => "",
				"unit" => "",
				"name" => ""
			));
		}
		return $collection;
	} 

	/**
	* I take a wordpress post and transform its first recipe into a Recipe Pro Recipe and return it 
	* non destructively.
	**/
	static public function extract($wppost) {
		// var_log( $wppost->post_content );
		$erdoc = new EasyRecipeDocument( $wppost->post_content );
		$settings = new stdClass();
		$settings->lblHour = "hour";
		$settings->lblHours = "hours";
		$settings->lblMinute = "min";
		$settings->lblMinutes = "mins";
		$erdoc->setSettings( $settings );
		$data = $erdoc->extractData( $erdoc->getRecipe(), new stdClass() );
		$recipe = new Recipe_Pro_Recipe();
		$result = new Recipe_Pro_Import_Log();
		$result->recipe = $recipe;
		$recipe->title = $data->name;
		$recipe->description = $data->summary;
		$recipe->author = $data->author;
		$recipe->imageUrl = $data->photoURL;
		$recipe->type = $data->type;
		$recipe->cuisine = $data->cuisine;
		if ( substr_count( $data->yield, '-') > 0 ) {
			$result->addNote('Yield contained a range with a dash, the first number will be used.');
		}
		$recipe->yield = $data->yield;
		$recipe->servingSize = $data->servingSize;
		$recipe->calories = preg_replace('/[^0-9\.]/', '', $data->calories) ?: null;
		$recipe->cholesterolContent = preg_replace('/[^0-9\.]/', '', $data->cholesterol) ?: null;
		$recipe->fatContent = preg_replace('/[^0-9\.]/', '', $data->fat) ?: null;
		$recipe->saturatedFatContent = preg_replace('/[^0-9\.]/', '', $data->saturatedFat) ?: null;
		$recipe->unsaturatedFatContent = preg_replace('/[^0-9\.]/', '', $data->unsaturatedFat) ?: null;
		$recipe->transFatContent = preg_replace('/[^0-9\.]/', '', $data->transFat) ?: null;
		$recipe->carbohydrateContent = preg_replace('/[^0-9\.]/', '', $data->carbohydrates) ?: null;
		$recipe->sugarContent = preg_replace('/[^0-9\.]/', '', $data->sugar) ?: null;
		$recipe->sodiumContent = preg_replace('/[^0-9\.]/', '', $data->sodium) ?: null;
		$recipe->fiberContent = preg_replace('/[^0-9\.]/', '', $data->fiber) ?: null;
		$recipe->proteinContent = preg_replace('/[^0-9\.]/', '', $data->protein) ?: null;
		if ( $data->preptimeISO ) {
			$recipe->setPrepTimeByValue( $data->preptimeISO );
		}
		if ( $data->cooktimeISO ) {
			$recipe->setCookTimeByValue( $data->cooktimeISO );
		}
		if ( $data->INGREDIENTSECTIONS ) {
			foreach ( $data->INGREDIENTSECTIONS as $section ) {
				$name = '';
				if ( property_exists( $section, 'heading' ) && strlen( $section->heading ) > 0 ) {
					$name = $section->heading;
				}
				if ( property_exists( $section, 'INGREDIENTS' ) && count( $section->INGREDIENTS ) > 0 ) {
					array_push( $recipe->ingredientSections, new Recipe_Pro_Ingredient_Section(
						$name,
						self::convertIngredients( $section->INGREDIENTS )
					));
				}
			}
		}
		//   ["INSTRUCTIONSTEPS"]=>
		//   array(1) {
		//     [0]=>
		//     object(stdClass)#245 (1) {
		//       ["INSTRUCTIONS"]=>
		//       array(10) {
		//         [0]=>
		//         object(stdClass)#212 (2) {
		//           ["instruction"]=>
		//           string(213) "Set your churning bowl in the freezer the night before to chill. Soak your cashews the night before as well, or for at least 4-6 hour before blending. Alternatively soak in boiling water for 1-2 hours (see notes)."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		if ( $data->INSTRUCTIONSTEPS ) {
			foreach ( $data->INSTRUCTIONSTEPS as $step ) {
				foreach ( $step as $name => $instructions ) {
					foreach ( $instructions as $instruction) {
						array_push( $recipe->instructions, new Recipe_Pro_Instruction(
							EasyRecipeDocument::convertShortcodes(  $instruction->instruction )
						));	
					}
				}
			}
		}
		
		array_push( $recipe->notes, new Recipe_Pro_Note(
			EasyRecipeDocument::convertShortcodes( $data->notes )
		));
		
		//$recipe->totalTime = $data->totaltimeISO ?: "";
		// data looks like
		// 	 object(stdClass)#258 (32) {
		//   ["hasPhoto"]=>
		//   bool(true)
		//   ["recipeIX"]=>
		//   int(0)
		//   ["version"]=>
		//   string(8) "3.5.3208"
		//   ["preptime"]=>
		//   NULL
		//   ["cooktime"]=>
		//   NULL
		//   ["totaltime"]=>
		//   NULL
		//   ["hasTimes"]=>
		//   bool(false)
		//   ["calories"]=>
		//   string(3) "296"
		//   ["fat"]=>
		//   bool(false)
		//   ["saturatedFat"]=>
		//   bool(false)
		//   ["unsaturatedFat"]=>
		//   bool(false)
		//   ["transFat"]=>
		//   bool(false)
		//   ["carbohydrates"]=>
		//   bool(false)
		//   ["sugar"]=>
		//   string(5) "27.6g"
		//   ["sodium"]=>
		//   string(5) "16.7g"
		//   ["fiber"]=>
		//   bool(false)
		//   ["protein"]=>
		//   bool(false)
		//   ["cholesterol"]=>
		//   bool(false)
		//   ["hasNutrition"]=>
		//   bool(true)
		//   ["notes"]=>
		//   string(485) "*For soaking cashews in boiling water, simply place raw cashews in a dish or jar, bring a large pot of water to a boil, then pour over and soak at least 1 hour, no longer than 2. Drain as usual.[br]*Prep time does not include soaking cashews or freezing.[br]*Nutrition information is a rough estimate for 1 of 7 1/2-cup servings without toppings or pecans.[br]*Adapted from [url href="http://www.theppk.com/2013/10/pumpkin-pie-ice-cream-video/" target="_blank"]Post Punk Kitchen[/url]."
		//   ["INGREDIENTSECTIONS"]=>
		//   array(1) {
		//     [0]=>
		//     object(stdClass)#180 (1) {
		//       ["INGREDIENTS"]=>
		//       array(16) {
		//         [0]=>
		//         object(stdClass)#163 (2) {
		//           ["ingredient"]=>
		//           string(9) "ICE CREAM"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [1]=>
		//         object(stdClass)#181 (2) {
		//           ["ingredient"]=>
		//           string(82) "1.5 cups raw cashews, soaked for 4-6 hours, or in boiling hot water for 1-2 hours*"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [2]=>
		//         object(stdClass)#182 (2) {
		//           ["ingredient"]=>
		//           string(73) "1 cup dairy-free milk (such as unsweetened almond, light coconut or rice)"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [3]=>
		//         object(stdClass)#183 (2) {
		//           ["ingredient"]=>
		//           string(16) "3 Tbsp olive oil"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [4]=>
		//         object(stdClass)#184 (2) {
		//           ["ingredient"]=>
		//           string(21) "3/4 cup pumpkin puree"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [5]=>
		//         object(stdClass)#185 (2) {
		//           ["ingredient"]=>
		//           string(53) "1/4 cup maple syrup (sub agave or honey if not vegan)"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [6]=>
		//         object(stdClass)#186 (2) {
		//           ["ingredient"]=>
		//           string(28) "1/4 cup + 2 Tbsp brown sugar"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [7]=>
		//         object(stdClass)#187 (2) {
		//           ["ingredient"]=>
		//           string(28) "1.5 tsp pure vanilla extract"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [8]=>
		//         object(stdClass)#188 (2) {
		//           ["ingredient"]=>
		//           string(16) "1/4 tsp sea salt"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [9]=>
		//         object(stdClass)#189 (2) {
		//           ["ingredient"]=>
		//           string(27) "1 1/2 tsp pumpkin pie spice"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [10]=>
		//         object(stdClass)#190 (2) {
		//           ["ingredient"]=>
		//           string(23) "3/4 tsp ground cinnamon"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [11]=>
		//         object(stdClass)#191 (2) {
		//           ["ingredient"]=>
		//           string(25) "ROASTED PECANS (optional)"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [12]=>
		//         object(stdClass)#192 (2) {
		//           ["ingredient"]=>
		//           string(24) "1/2 cup raw pecan halves"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [13]=>
		//         object(stdClass)#193 (2) {
		//           ["ingredient"]=>
		//           string(76) "1 Tbsp vegan butter (such as Earth Balance | or sub olive or grape seed oil)"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [14]=>
		//         object(stdClass)#194 (2) {
		//           ["ingredient"]=>
		//           string(18) "1 Tbsp brown sugar"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [15]=>
		//         object(stdClass)#195 (2) {
		//           ["ingredient"]=>
		//           string(48) "pinch each sea salt, cinnamon and cayenne pepper"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//       }
		//     }
		//   }
		//   ["hasIngredients"]=>
		//   bool(true)
		//   ["INSTRUCTIONSTEPS"]=>
		//   array(1) {
		//     [0]=>
		//     object(stdClass)#245 (1) {
		//       ["INSTRUCTIONS"]=>
		//       array(10) {
		//         [0]=>
		//         object(stdClass)#212 (2) {
		//           ["instruction"]=>
		//           string(213) "Set your churning bowl in the freezer the night before to chill. Soak your cashews the night before as well, or for at least 4-6 hour before blending. Alternatively soak in boiling water for 1-2 hours (see notes)."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [1]=>
		//         object(stdClass)#246 (2) {
		//           ["instruction"]=>
		//           string(264) "Once soaked, add well-drained cashews and remaining ingredients to a blender and blend until creamy and smooth - about 3-4 minutes, using the "liquify" or "puree" setting if you have the option to get it really creamy. Taste and adjust sweetness/flavors as needed."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [2]=>
		//         object(stdClass)#244 (2) {
		//           ["instruction"]=>
		//           string(183) "Add mixture to your chilled ice cream maker bowl and churn according to manufacturer’s instructions until thoroughly chilled - about 45 minutes. It should resemble thick soft serve."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [3]=>
		//         object(stdClass)#243 (2) {
		//           ["instruction"]=>
		//           string(150) "Transfer to a freezer-safe container, cover and freeze until hard - at least 6 hours, preferably overnight. Will keep in the freezer for up to a week."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [4]=>
		//         object(stdClass)#242 (2) {
		//           ["instruction"]=>
		//           string(305) "Take out of the freezer and thaw for 30-40 minutes - or microwave (gasp!) for 15-20 seconds - before serving to soften. Serve with brown sugar roasted pecans (see next step) and [url href="http://minimalistbaker.com/creamy-no-bake-pumpkin-pie/" target="_blank"]coconut whipped cream[/url] for extra oomph."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [5]=>
		//         object(stdClass)#241 (2) {
		//           ["instruction"]=>
		//           string(126) "[b]FOR THE PECANS:[/b] Preheat oven to 350 degrees F and place pecans on a foil-lined baking sheet. Toast for about 8 minutes."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [6]=>
		//         object(stdClass)#240 (2) {
		//           ["instruction"]=>
		//           string(124) "In the meantime, melt butter in a small skillet or in the microwave and stir in brown sugar, sea salt, cinnamon and cayenne."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [7]=>
		//         object(stdClass)#239 (2) {
		//           ["instruction"]=>
		//           string(202) "Remove toasted pecans from oven and toss with butter and spice mixture. Spread back onto the baking sheet and toast for another 4-7 minutes or until fragrant and golden brown, being careful not to burn."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [8]=>
		//         object(stdClass)#238 (2) {
		//           ["instruction"]=>
		//           string(63) "Let cool completely. Store leftovers in a jar for up to 1 week."
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//         [9]=>
		//         object(stdClass)#254 (2) {
		//           ["instruction"]=>
		//           string(144) "Serving size: 1/7th of recipe Calories: 296 Fat: 20.1g Saturated fat: 3.6g Carbohydrates: 27.6g Sugar: 16.7g Sodium: 102mg Fiber: 2g Protein: 5g"
		//           ["isImage"]=>
		//           bool(false)
		//         }
		//       }
		//     }
		//   }
		//   ["hasInstructions"]=>
		//   bool(true)
		// }
		return $result;
	}
}

/**
 * Easy Recipe Modelling Classes used from EasyRecipe free edition GPLV2
 */

class EasyRecipeDocument extends EasyRecipeDOMDocument {
    public $isEasyRecipe = false;
    public $recipeVersion = 0;
    public $isFormatted;
    private $easyrecipes = array();
    private $easyrecipesHTML = array();


    /** @var EasyRecipeSettings */
    private $settings;

    private $preEasyRecipe;
    private $postEasyRecipe;

    private $recipeData = array();

    const regexEasyRecipe = '/<div\s+class\s*=\s*["\'](?:[^>]*\s+)?easyrecipe[ \'"]/si';
    const regexDOCTYPE = '%^<!DOCTYPE.*?</head>\s*<body>\s*(.*?)</body>\s*</html>\s*%si';
    const regexTime = '/^(?:([0-9]+) *(?:hours|hour|hrs|hr|h))? *(?:([0-9]+) *(?:minutes|minute|mins|min|mns|mn|m))?$/i';
    const regexImg = '%<img ([^>]*?)/?>%si';
    const regexPhotoClass = '/class\s*=\s*["\'](?:[a-z0-9-_]+ )*?photo[ \'"]/si';
    const regexShortCodes = '%(?:\[(i|b|u)\](.*?)\[/\1\])|(?:\[(img)(?:&nbsp; *| +|\p{Zs}+)(.*?) */?\])|(?:\[(url|a)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/url\])|(?:\[(cap)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/cap\])%iu';

    private $fractions = array(
        1 => array(2 => '&frac12;', 3 => '&#8531;', 4 => '&frac14;', 5 => '&#8533;', 6 => '&#8537;', 8 => '&#8539;'),
        2 => array(3 => '&#8532;'),
        3 => array(4 => '&frac34;', 8 => '&#8540;'),
        4 => array(5 => '&#8536;'),
        5 => array(6 => '&#8538;', 8 => '&#8541;'),
        7 => array(8 => '&#8542;'));

    /**
     * If there's an EasyRecipe in the content, load the HTML and pre-process, else just return
     *
     * @param      $content
     * @param bool $load
     */
    public function __construct($content, $load = true) {
        /**
         * If there's no EasyRecipe, just return
         */
        if (!@preg_match(self::regexEasyRecipe, $content)) {
            return;
        }

        /**
         * Load the html - make sure we could parse it
         */
        parent::__construct($content, $load);

        if (!$this->isValid()) {
            return;
        }

        /**
         * Find the easyrecipe(s)
         */
        $this->easyrecipes = $this->getElementsByClassName('easyrecipe');

        /**
         * Sanity check - make sure we could actually find at least one
         */
        if (count($this->easyrecipes) == 0) {
            // echo "<!-- ER COUNT = 0 -->\n";
            return;
        }

        /**
         * This is a valid easyrecipe post
         * Find a version number - the version will be the same for every recipe in a multi recipe post so just get the first
         */
        $this->isEasyRecipe = true;

        /* @var $node DOMElement */
        $node = $this->getElementByClassName("endeasyrecipe", "div", $this->easyrecipes[0], false);

        $this->recipeVersion = $node->nodeValue;

        /*
         * See if this post has already been formatted.
         * Wordpress replaces the parent post_content with the autosave post content (as already formatted by us) on a preview.
         * so we need to know if this post has already been formatted. This is a pretty icky way of doing it since it relies
         * on the style template having a specific title attribute on the endeasyrecipe div - need to make this more robust
         */
        $this->isFormatted = ($node !== null && $node->hasAttribute('title'));
    }

    function setSettings($settings) {
        $this->settings = $settings;
    }


    static public function convertShortcodes($html) {
		/**
         * Handle our own shortcodes because Wordpress's braindead implementation doesn't handle consecutive shortcodes properly
         */
        $html = str_replace("[br]", "<br>", $html);

        /**
         * Do our own shortcode handling
         * Don't bother with the regex's if there's no need - saves a few cycles
         * Not a great way of doing these - shortcodes embedded in shortcodes aren't always handled all that well
         * TODO - Would be better implemented using a stack so we we can absolutely match beginning and end codes and eliminate the possibilty of infinite recursion
         */
        if (strpos($html, "[") !== false) {
            if (preg_match(self::regexShortCodes, $html)) {
                $html = preg_replace_callback('%\[(i|b|u)\](.*?)\[/\1\]%si', array('EasyRecipeDocument', 'shortCodes'), $html);
                $html = preg_replace_callback('%\[(img)(?:&nbsp; *| +|\p{Zs}+)(.*?) */?\]%iu', array('EasyRecipeDocument', "shortCodes"), $html);
                $html = preg_replace_callback('%\[(url|a)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/url\]%iu', array('EasyRecipeDocument', "shortCodes"), $html);
                $html = preg_replace_callback('%\[(cap)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/cap\]%iu', array('EasyRecipeDocument', "shortCodes"), $html);
            }
        }
        return $html;
	}

    /**
     * Process the shortcodes.
     * Called as the preg_replace callback
     * TODO - this is a pretty naive implementation. It doesn't handle markdown embedded in markdown very well
     * e.g. [b]bold[b]another bold[/b][/b] won't work
     * It may not be worthwhile fixing this
     *
     * @param array $match The match array returned by the regex
     *
     * @return string The replacement code, or the original complete match if we don't recognise the shortcode
     */
    static public function shortCodes($match) {
        switch ($match[1]) {
            case "i" :
                $replacement = "<em>{$match[2]}</em>";
                break;

            case "u" :
                $replacement = "<u>{$match[2]}</u>";
                break;

            case "b" :
                $replacement = "<strong>{$match[2]}</strong>";
                break;

            case "img" :
                $replacement = "<img {$match[2]} />";
                break;

            case "a" :
            case "url" :
                $replacement = "<a {$match[2]}>{$match[3]}</a>";
                break;

            case "cap" :
                $replacement = "[caption {$match[2]}]{$match[3]}[/caption]";
                break;

            default:
                return $match[0];

        }
        while (preg_match(self::regexShortCodes, $replacement)) {
            $replacement = preg_replace_callback('%\[(i|b|u)\](.*?)\[/\1\]%si', array($this, "shortCodes"), $replacement);
            $replacement = preg_replace_callback('%\[(img)(?:&nbsp; *| +|\p{Zs}+)(.*?) */?\]%iu', array($this, "shortCodes"), $replacement);
            $replacement = preg_replace_callback('%\[(url|a)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/url\]%iu', array($this, "shortCodes"), $replacement);
            $replacement = preg_replace_callback('%\[(cap)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/cap\]%iu', array($this, "shortCodes"), $replacement);

        }

        return $replacement;
    }

    /**
     * The original ER template didn't explicitly identify by class the individual
     * labels for various significant tags, just the tags themselves.
     * This method modifies the labels for those tags
     *
     * @param $className    string
     *                      The class of the tag
     * @param $value        string
     *                      The text value to set for the label (which will be the parent of $className)
     * @param $currentValue string
     *                      The value to replace
     */
    public function setParentValueByClassName($className, $value, $currentValue = "") {
        $nodes = $this->getElementsByClassName($className);
        for ($i = 0; $i < count($nodes); $i++) {
            $nodes[$i] = $nodes[$i]->parentNode;
        }
        for ($i = 0; $i < count($nodes); $i++) {
            if ($currentValue == "") {
                $nodes[$i]->nodeValue = $value;
            } else {
                if (preg_match("/^$currentValue(.*)$/", $nodes[$i]->firstChild->nodeValue, $regs)) {
                    $nodes[$i]->firstChild->nodeValue = $value . $regs[1];
                }
            }
        }
    }


    /**
     * Sets the URL in the print button <a> tag href
     *
     * Later versions of tinyMCE may silently remove the <a> tag altogether, so we need to put it back if it's not there
     *
     * @param     $recipe
     * @param     $template
     * @param     $data
     * @param int $nRecipe
     *
     * @return string
     */
    function formatRecipe($recipe, EasyRecipeTemplate $template, $data, $nRecipe = 0) {
        $data = $this->extractData($recipe, $data, $nRecipe);

        $html = $template->replace($data);


        /**
         * Convert fractions if asked to
         */
        if ($data->convertFractions) {
            $html = preg_replace_callback('%(. |^|>)([1-457])/([2-68])([^\d]|$)%', array($this, 'convertFractionsCallback'), $html);
        }

        /**
         * Handle our own shortcodes because Wordpress's braindead implementation doesn't handle consecutive shortcodes properly
         */
        $html = str_replace("[br]", "<br>", $html);

        /**
         * Do our own shortcode handling
         * Don't bother with the regex's if there's no need - saves a few cycles
         * Not a great way of doing these - shortcodes embedded in shortcodes aren't always handled all that well
         * TODO - Would be better implemented using a stack so we we can absolutely match beginning and end codes and eliminate the possibilty of infinite recursion
         */
        if (strpos($html, "[") !== false) {
            if (preg_match(self::regexShortCodes, $html)) {
                $html = preg_replace_callback('%\[(i|b|u)\](.*?)\[/\1\]%si', array($this, "shortCodes"), $html);
                $html = preg_replace_callback('%\[(img)(?:&nbsp; *| +|\p{Zs}+)(.*?) */?\]%iu', array($this, "shortCodes"), $html);
                $html = preg_replace_callback('%\[(url|a)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/url\]%iu', array($this, "shortCodes"), $html);
                $html = preg_replace_callback('%\[(cap)(?:&nbsp; *| +|\p{Zs}+)([^\]]+?)\](.*?)\[/cap\]%iu', array($this, "shortCodes"), $html);
            }
        }

        /**
         * Process possible captions that have been exposed by the easyrecipe shortcode expansion
         */
        if (strpos($html, '[caption ') !== false) {
            $html = do_shortcode($html);
        }

        /**
         * Decode any quotes that have possibly been "double encoded" when we inserted an image
         */
        $html = str_replace("&amp;quot;", '&quot;', $html);

        /**
         * Remove leftover template comments and then remove linebreaks and blank lines
         */

        $html = preg_replace('/<!-- .*? -->/', '', $html);
        $lines = explode("\n", $html);
        $html = '';
        foreach ($lines as $line) {
            if (($trimmed = trim($line)) != '') {
                $html .= "$trimmed ";
            }
        }

        return $html;
    }

    /**
     * Replaces the raw easyrecipe(s) with the formatted version
     *
     * @param EasyRecipeTemplate $template
     * @param object $originalData
     * @param null $recipe
     *
     * @return string
     */
    function applyStyle(EasyRecipeTemplate $template, $originalData, $recipe = null) {
        $nRecipe = 0;
        $recipes = ($recipe == null) ? $this->easyrecipes : array($recipe);

        foreach ($recipes as $recipe) {
            /**
             * Get a fresh copy of the original data because we may mess with it
             */
            $data = clone $originalData;
            /**
             * If no rating data has been passed in AND there's a self-rating, get and use the self rating
             * This badly needs to be rewritten. It's a hack to get over the problems caused by not originally allowing
             * for multiple recipes in a post and self rating.
             * $data-hasRating will be:
             *   true  - Using EasyRecipe ratings and ratings exist
             *   false - Using EasyRecipe ratings and ratings do NOT exist OR ratings are disabled
             *   not set - possibly using self rating
             */
            if (!isset($data->hasRating)) {
                $rating = $this->getElementAttributeByClassName('easyrecipe', 'data-rating');
                if (!empty($rating) && is_numeric($rating) && $rating > 0) {
                    $data->ratingCount = 1;
                    $data->ratingValue = $rating;
                    $data->ratingPC = $rating * 100 / 5;
                    $data->hasRating = true;
                }
            }
            /**
             * Format the recipe and save the formatted recipe HTML
             */
            $this->easyrecipesHTML[$nRecipe] = trim($this->formatRecipe($recipe, $template, $data, $nRecipe));

            /**
             * Insert a shortcode placeholder for the recipe. We need to remove the recipe from the content before wpauto() mangles it
             * It gets re-inserted during the "the_content" hook. The placeholder stores the postID and the index of the recipe on the post
             */

            /**
             * Replace the original recipe (the unformatted version from the post) with a place holder
             */
            $placeHolder = $this->createElement("div");
            $placeHolder->setAttribute("id", "_easyrecipe_" . $nRecipe);

            try {
                /** @var $recipe DOMNode */
                $recipe->parentNode->replaceChild($placeHolder, $recipe);
            } catch (Exception $e) {
            }

            $nRecipe++;
        }

        /**
         * Get the post's HTML which now has placeholders where the formatted recipes should be inserted
         */
        $html = $this->getHTML();

        /**
         * Return the content (now has shortcode placeholders for recipes) and the recipe HTML itself
         * Try plan C. Some themes don't call the_content() so we can't rely on hooking in to that to supply the formatted recipe HTML
         */
//        $result = new stdClass();
//        $result->html = $html;
//        $result->recipesHTML = $this->easyrecipesHTML;
//        return $result;

        /**
         * Replace the placeholders with the formatted recipe HTML
         * FIXME - why are we doing this?
         */
        for ($i = 0; $i < $nRecipe; $i++) {
            $html = str_replace("<div id=\"_easyrecipe_$i\"></div>", $this->easyrecipesHTML[$i], $html);
        }
        return $html;
    }

    /**
     * Find the first <img> in $html and add the class name "photo" to it
     *
     * If no <img> is found, returns false
     *
     * @param $html string
     *              The html to search
     *
     * @return boolean/string The adjusted html if an <img> was found, else false
     */
    private function makePhotoClass($html) {
        if (!@preg_match('/^(.*?)<img ([^>]+>)(.*)$/si', $html, $regs)) {
            return false;
        }
        $preamble = $regs[1];
        $imgTag = $regs[2];
        $postscript = $regs[3];
        /*
       * If there's no "class", add one else add "photo" to the existing one
       * Don't bother checking if "photo" already exists if there's an existing class
       */
        if (@preg_match('/^(.*)class="([^"]*".*)$/si', $imgTag, $regs)) {
            $imgTag = "<img " . $regs[1] . 'class="photo ' . $regs[2];
        } else {
            $imgTag = '<img class="photo" ' . $imgTag;
        }
        /*
       * Re-assemble the content
       */
        return "$preamble$imgTag$postscript";
    }

    /**
     * Add the "photo" class name to the first image in the html inside or outside the EasyRecipe
     * Check first to see if there is already an image anywhere in the post with the "photo" class
     */
    public function addPhotoClass() {
        /*
       * Check to see if there's an image anywhere in the post that already has a photo class
       */
        @preg_match_all(self::regexImg, $this->preEasyRecipe, $result, PREG_PATTERN_ORDER);
        foreach ($result[1] as $img) {
            if (preg_match(self::regexPhotoClass, $img)) {
                return;
            }
        }

        @preg_match_all(self::regexImg, $this->postEasyRecipe, $result, PREG_PATTERN_ORDER);
        foreach ($result[1] as $img) {
            if (preg_match(self::regexPhotoClass, $img)) {
                return;
            }
        }

        // if (@preg_match(self::regexPhotoClass, $this->preEasyRecipe)) {
        // return;
        // }
        // if (@preg_match(self::regexPhotoClass, $this->postEasyRecipe)) {
        // return;
        // }
        $photo = $this->getElementsByClassName("photo", "img");
        if (count($photo) > 0) {
            return;
        }
        /*
       * Search for the first image and if there is one, add the photo class to it
       */
        $html = $this->makePhotoClass($this->preEasyRecipe);
        if ($html !== false) {
            $this->preEasyRecipe = $html;
        } else {
            $photos = $this->getElementsByTagName("img");
            if ($photos && $photos->length > 0) {
                /** @noinspection PhpParamsInspection */
                $this->addClass($photos->item(0), "photo");
            } else {
                $html = $this->makePhotoClass($this->postEasyRecipe);
                if ($html !== false) {
                    $this->postEasyRecipe = $html;
                }
            }
        }
    }

    /**
     * WP 3.2.1 had a version of tinyMCE that removes without warning perfectly valid HTML which resolved to whitespace
     * (What do they think that "class" stuff is in there for???)
     *
     * This repairs the value-title classes necessary for times
     *
     * @param $timeElement
     */
    function fixTimes($timeElement) {
        foreach ($this->easyrecipes as $recipe) {
            $node = $this->getElementByClassName($timeElement, "span", $recipe);
            if (!$node || is_array($node)) {
                continue;
            }

            $hasNode = false;
            $h = $m = 0;
            /** @var $node DOMNode */
            /** @var $child  DOMElement */
            for ($child = $node->firstChild; $child; $child = $child->nextSibling) {
                if ($child->nodeName == "#text") {
                    if (preg_match('/(?:([0-9]+) *hours?)?(?: *([0-9]+) *min)?/i', $node->nodeValue, $regs)) {
                        $h = $regs[1];
                        $m = isset($regs[2]) ? $regs[2] : 0;
                    }
                } else if ($child->nodeName == "span") {
                    if ($child->getAttribute("class") == "value-title") {
                        $hasNode = true;
                        break;
                    }
                }
            }

            if (!$hasNode) {
                $valueElement = new DOMElement('span', ' ');
                $node->appendChild($valueElement);
                $valueElement->setAttribute("class", "value-title");
                $ISOTime = "PT";
                if ($h > 0) {
                    $ISOTime .= $h . "H";
                }
                if ($m > 0) {
                    $ISOTime .= $m . "M";
                }

                $valueElement->setAttribute("title", $ISOTime);
            }
        }
    }

    private function convertFractionsCallback($match) {
        if (isset($this->fractions[$match[2]][$match[3]])) {
            $pre = $match[1] != '' && is_numeric($match[1][0]) ? $match[1][0] : $match[1];
            return $pre . $this->fractions[$match[2]][$match[3]] . $match[4];
        }
        return $match[1] . $match[2] . '/' . $match[3] . $match[4];
    }

    /**
     * Get the processed html for the post.
     * Needs to remove the extra stuff saveHTML adds
     * The rtrim is needed because pcre regex's can't pick up repeated spaces after repeated "any character"
     *
     *         TODO - standardise the way body only is done!
     *
     * @param bool $bodyOnly
     *
     * @return bool|string
     */
    public function getHTML($bodyOnly = false) {
        $html = $this->saveHTML();
        return rtrim(preg_replace(self::regexDOCTYPE, '$1', $html));
    }

    public static function getPrintRecipe($content) {
        if (!@preg_match(self::regexEasyRecipe, $content, $regs)) {
            return "";
        }
        return $regs[3];
    }

    function getPostVersion() {
        return $this->getElementValueByClassName("endeasyrecipe", "div");
    }

    private function getISOTime($t) {
        if (!preg_match(self::regexTime, $t, $regs)) {
            return false;
        }
        $hr = isset($regs[1]) ? (int)$regs[1] : 0;
        $mn = isset($regs[2]) ? (int)$regs[2] : 0;

        $shr = $hr > 0 ? $hr . "H" : "";
        $smn = $mn > 0 ? $mn . "M" : "";
        return "PT$shr$smn";
    }

    function getRecipe($nRecipe = 0) {
        return $this->easyrecipes[$nRecipe];
    }

    function findPhotoURL($recipe) {
        $photoURL = false;
        if ($this->recipeVersion > '3') {
            $photoURL = $this->getElementAttributeByTagName('link', 'href', "itemprop", 'image', $recipe);
        }
        if (!$photoURL) {
            $photoURL = $this->getElementAttributeByClassName('photo', 'src');
            if (!$photoURL) {
                $images = $this->getElementsByTagName("img");
                if ($images->length > 0) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $photoURL = $images->item(0)->getAttribute('src');
                }
            }
        }
        return $photoURL;
    }

    /**
     * Translate the time labels to custome labels (if they're different)
     *
     * @param $time
     *
     * @return mixed
     */
    private function timeTranslate($time) {
        if ($this->settings->lblHours != 'hours') {
            $time = preg_replace('/\bhours\b/', $this->settings->lblHours, $time);
        }
        if ($this->settings->lblHour != 'hour') {
            $time = preg_replace('/\bhour\b/', $this->settings->lblHour, $time);
        }
        if ($this->settings->lblMinutes != 'mins') {
            $time = preg_replace('/\bmins\b/', $this->settings->lblMinutes, $time);
        }
        if ($this->settings->lblMinute != 'min') {
            $time = preg_replace('/\bmin\b/', $this->settings->lblMinute, $time);
        }
        return $time;
    }

    function extractData($recipe, $data, $nRecipe = 0) {
        $photoURL = $this->findPhotoURL($recipe);
        if ($photoURL) {
            $data->hasPhoto = true;
            $data->photoURL = $photoURL;
        }
        $data->recipeIX = $nRecipe;

        $data->version = $this->recipeVersion;

        $data->name = $this->getElementValueByClassName("ERName", "*", $recipe);
        $data->cuisine = $this->getElementValueByClassName("cuisine", "span", $recipe);

        $data->type = $this->getElementValueByClassName("type", "span", $recipe);
        if (!$data->type) {
            $data->type = $this->getElementValueByClassName("tag", "span", $recipe);
        }
        $data->author = $this->getElementValueByClassName("author", "span", $recipe);

        if ($this->recipeVersion < '3') {
            $data->preptime = $this->getElementValueByClassName("preptime", "span", $recipe);
            $data->cooktime = $this->getElementValueByClassName("cooktime", "span", $recipe);
            $data->totaltime = $this->getElementValueByClassName("duration", "span", $recipe);
        } else {
            $data->preptime = $this->getElementValueByProperty('time', 'itemprop', 'prepTime', $recipe);
            $data->cooktime = $this->getElementValueByProperty('time', 'itemprop', 'cookTime', $recipe);
            $data->totaltime = $this->getElementValueByProperty('time', 'itemprop', 'totalTime', $recipe);
        }

        /**
         * Hack for awkward convert of times from Ziplist
         */
        if ($data->preptime == '0 min') {
            unset($data->preptime);
        }
        if ($data->cooktime == '0 min') {
            unset($data->cooktime);
        }
        if ($data->totaltime == '0 min') {
            unset($data->totaltime);
        }

        $data->hasTimes = (!empty($data->preptime) || !empty($data->cooktime) || !empty($data->totaltime));

        if ($data->hasTimes) {
            $data->preptimeISO = $this->getISOTime($data->preptime);
            $data->cooktimeISO = $this->getISOTime($data->cooktime);
            $data->totaltimeISO = $this->getISOTime($data->totaltime);

            $data->preptime = $this->timeTranslate($data->preptime);
            $data->cooktime = $this->timeTranslate($data->cooktime);
            $data->totaltime = $this->timeTranslate($data->totaltime);
        }

        $data->yield = $this->getElementValueByClassName("yield", "span", $recipe);
        $data->summary = $this->getElementValueByClassName("summary", "*", $recipe);

        $data->servingSize = $this->getElementValueByClassName("servingSize", "span", $recipe);
        $data->calories = $this->getElementValueByClassName("calories", "span", $recipe);
        $data->fat = $this->getElementValueByClassName("fat", "span", $recipe);
        $data->saturatedFat = $this->getElementValueByClassName("saturatedFat", "span", $recipe);
        $data->unsaturatedFat = $this->getElementValueByClassName("unsaturatedFat", "span", $recipe);
        $data->transFat = $this->getElementValueByClassName("transFat", "span", $recipe);
        $data->carbohydrates = $this->getElementValueByClassName("carbohydrates", "span", $recipe);
        $data->sugar = $this->getElementValueByClassName("sugar", "span", $recipe);
        $data->sodium = $this->getElementValueByClassName("sodium", "span", $recipe);
        $data->fiber = $this->getElementValueByClassName("fiber", "span", $recipe);
        $data->protein = $this->getElementValueByClassName("protein", "span", $recipe);
        $data->cholesterol = $this->getElementValueByClassName("cholesterol", "span", $recipe);
        $data->hasNutrition =
            $data->servingSize || $data->calories || $data->fat || $data->saturatedFat || $data->unsaturatedFat || $data->carbohydrates || $data->sugar || $data->fiber || $data->protein || $data->cholesterol || $data->sodium || $data->transFat;

        $data->notes = $this->getElementValueByClassName("ERNotes", "div", $recipe);

        $data->INGREDIENTSECTIONS = array();
        $section = null;
        // $ingredientsList = $this->getElementByClassName('ingredients', 'ul', $recipe);
        $ingredientsLists = $this->getElementsByClassName('ingredients', 'ul', $recipe);

        foreach ($ingredientsLists as $ingredientsList) {
            $ingredients = $this->getElementsByClassName("ingredient|ERSeparator", "*", $ingredientsList);

            foreach ($ingredients as $ingredient) {
                $hasHeading = $this->hasClass($ingredient, 'ERSeparator');
                if ($hasHeading || $section == null) {
                    if ($section != null) {
                        $data->INGREDIENTSECTIONS[] = $section;
                    }
                    $section = new stdClass();
                    $section->INGREDIENTS = array();
                    if ($hasHeading) {
                        $section->heading = $ingredient->nodeValue;
                        continue;
                    }
                }
                $item = new stdClass();
                $item->ingredient = $ingredient->nodeValue;
                $item->isImage = preg_match('/^\s*(?:\[[^]]+\])*\s*\[img /i', $ingredient->nodeValue) != 0;
                $section->INGREDIENTS[] = $item;
            }
        }
        $data->hasIngredients = count($ingredientsLists) > 0;
        if ($data->hasIngredients) {
            $data->INGREDIENTSECTIONS[] = $section;
        }

        $data->INSTRUCTIONSTEPS = array();
        $section = null;
        // $instructionsList = $this->getElementByClassName('instructions', 'div', $recipe);
        $instructionsLists = $this->getElementsByClassName('instructions', 'div', $recipe);
        foreach ($instructionsLists as $instructionsList) {
            $instructions = $this->getElementsByClassName("instruction|ERSeparator", "*", $instructionsList);
            foreach ($instructions as $instruction) {
                $hasHeading = $this->hasClass($instruction, 'ERSeparator');
                if ($hasHeading || $section == null) {
                    if ($section != null) {
                        $data->INSTRUCTIONSTEPS[] = $section;
                    }
                    $section = new stdClass();
                    $section->INSTRUCTIONS = array();
                    if ($hasHeading) {
                        $section->heading = $instruction->nodeValue;
                        continue;
                    }
                }
                $item = new stdClass();
                $item->instruction = $instruction->nodeValue;
                $item->isImage = preg_match('/^\s*(?:\[[^]]+\])*\s*\[img /i', $instruction->nodeValue) != 0;
                $section->INSTRUCTIONS[] = $item;
            }
        }

        $data->hasInstructions = $section != null;
        if ($data->hasInstructions) {
            $data->INSTRUCTIONSTEPS[] = $section;
        }

        return $data;
    }

    /**
     * Strips wrappers around recipes that the entry javascript added to enable inserting lines before and after a recipe
     *
     * @return string The post content with the wrappers stripped out or null if there were errors
     */
    function stripWrappers() {
        $wrappers = $this->getElementsByClassName("easyrecipeWrapper");
        foreach ($wrappers as $wrapper) {
            /** @var $wrapper DOMNode */
            /*
             * First take out possible "above" and "below" divs
             */
            $nodes = $this->getElementsByClassName("easyrecipeAbove", "div", $wrapper);
            foreach ($nodes as $node) {
                try {
                    $wrapper->removeChild($node);
                } catch (Exception $e) {
                    return null;
                }
            }
            $nodes = $this->getElementsByClassName("easyrecipeBelow", "div", $wrapper);
            foreach ($nodes as $node) {
                try {
                    $wrapper->removeChild($node);
                } catch (Exception $e) {
                    return null;
                }
            }
            /*
             * Then insert the recipe itself into the DOM just above the wrapper
             */
            $recipe = $this->getElementByClassName("easyrecipe", "div", $wrapper);
            try {
                $wrapper->parentNode->insertBefore($recipe, $wrapper);
            } catch (Exception $e) {
                return null;
            }

            /*
             * Finally remove the wrapper itself
             */
            try {
                $wrapper->parentNode->removeChild($wrapper);
            } catch (Exception $e) {
                return null;
            }
        }
        return $this->getHTML(true);
    }
}

class EasyRecipeDOMDocument extends DOMDocument {
    private $isValidHTML = false;

    public function __construct($content, $load = true, $encoding = "UTF-8") {
        parent::__construct("1.0", $encoding);

        libxml_use_internal_errors(true);

        if ($load && !@$this->loadHTML('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $content)) {
            return;
        }

        $this->isValidHTML = true;
    }

    /**
     * Returns TRUE if we successfully load the content
     */
    function isValid() {
        return $this->isValidHTML;
    }

    public function hasClass($node, $className) {
        $item = $node->attributes->getNamedItem('class');
        if ($item) {
            $classes = explode(" ", $item->nodeValue);
            for ($j = 0; $j < count($classes); $j++) {
                if ($classes[$j] == $className) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Removes class $class from $element
     *
     * @param DOMElement $element
     * @param string     $class
     */
    public function removeClass($element, $class) {
        $newClass = trim(preg_replace("/ *(?:$class)/i", '', $element->getAttribute('class')));
        if ($newClass != '') {
            $element->setAttribute('class', $newClass);
        } else {
            $element->removeAttribute('class');
        }
    }

    /**
     * Removes elements that have class $className
     *
     * @param string  $className
     * @param string  $tag
     * @param DOMNode $node
     */
    public function removeElementsByClassName($className, $tag = '*', $node = null) {

        if ($node == null) {
            $node = $this;
        }
        $elements = $node->getElementsByClassName($className, $tag);
        /** @var DOMNode $element */
        foreach ($elements as $element) {
            $element->parentNode->removeChild($element);
        }

    }

    public function innerHTML($node) {
        if (!isset($node->firstChild)) {
            return false;
        }
        $value = '';
        for ($child = $node->firstChild; $child; $child = $child->nextSibling) {
            $value .= $this->saveXML($child);
        }
        return $value;
    }

    /**
     * Get all elements that have a tag of $tag and class of $className
     *
     * @param        $className string The class name to search for
     * @param string $tag       string Tag of the items to search
     * @param DOMElement $node
     *
     * @return array
     */

    public function getElementsByClassName($className, $tag = "*", $node = null) {
        $classNames = explode('|', str_replace(' ', '', $className));
        $nodes = array();
        $domNodeList = ($node == null) ? $this->getElementsByTagName($tag) : $node->getElementsByTagName($tag);

        for ($i = 0; $i < $domNodeList->length; $i++) {
            /** @var DOMElement $element */
            $element = $domNodeList->item($i);
            if ($element->hasAttributes() && $element->hasAttribute('class')) {
                for ($j = 0; $j < count($classNames); $j++) {
                    if ($this->hasClass($element, $classNames[$j])) {
                        $nodes[] = $domNodeList->item($i);
                        break;
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * Convenience method to return a single element by class name when we know there's only going to be one
     * If there is actually more than 1, return the first
     *
     * @param        $className
     * @param string $tag
     * @param null   $node
     * @param bool   $deep
     *
     * @return null
     */
    public function getElementByClassName($className, $tag = "*", $node = null, $deep = true) {
        $nodes = $this->getElementsByClassName($className, $tag, $node, $deep);
        return count($nodes) > 0 ? $nodes[0] : null;
    }

    /**
     * @param             $tag
     * @param             $propertyName
     * @param             $propertyValue
     * @param DOMDocument $node
     *
     * @return array
     */
    public function getElementsByProperty($tag, $propertyName, $propertyValue, $node = null) {
        $nodes = $node == null ? $this->getElementsByTagName($tag) : $node->getElementsByTagName($tag);
        $result = array();
        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            if ($node->hasAttribute($propertyName)) {
                if ($node->getAttribute($propertyName) == $propertyValue) {
                    $result[] = $node;
                }
            }
        }
        return $result;
    }

    public function getElementValuesByProperty($tag, $propertyName, $propertyValue, $node = null) {
        $result = array();
        $nodes = $this->getElementsByProperty($tag, $propertyName, $propertyValue, $node);
        foreach ($nodes as $node) {
            $result[] = $this->innerHTML($node);
        }
        return $result;
    }

    public function getElementValueByProperty($tag, $propertyName, $propertyValue, $node = null) {
        $result = $this->getElementValuesByProperty($tag, $propertyName, $propertyValue, $node);
        return count($result) > 0 ? $result[0] : null;
    }

    public function getElementValuesByClassName($className, $tag = "*", $node = null) {
        $nodes = $this->getElementsByClassName($className, $tag, $node);
        $result = array();
        foreach ($nodes as $node) {
            $result[] = $this->innerHTML($node);
        }
        return $result;
    }

    public function getElementValueByClassName($className, $tag = "*", $node = null) {
        $node = $this->getElementByClassName($className, $tag, $node);
        return $this->innerHTML($node);
    }

    public function getElementAttributeByClassName($className, $attributeName, $tag = "*", $node = null) {
        $nodes = $this->getElementsByClassName($className, $tag, $node);
        $result = array();
        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            if (($attributeValue = $node->getAttribute($attributeName)) != '') {
                $result[] = $attributeValue;
            }
        }
        return count($result) > 0 ? $result[0] : false;
    }

    /**
     * @param        $node
     * @param string $tag
     *
     * @return array
     */
    public function getChildrenByTagName($node, $tag = "*") {
        $nodes = array();
        for ($child = $node->firstChild; $child; $child = $child->nextSibling) {
            if ($child instanceof DOMElement) {
                if ($tag == "*" || $tag == $child->tagName) {
                    $nodes[] = $child;
                }
            }
            $childNodes = $this->getChildrenByTagName($child, $tag);
            $nodes = array_merge($nodes, $childNodes);
        }
        return $nodes;
    }

    public function getElementAttributesByTagName($tag, $attributeName, $selector = "", $value = "", $baseNode = null) {
        $nodes = $baseNode == null ? $this->getElementsByTagName($tag) : $this->getChildrenByTagName($baseNode, $tag);
        $result = array();
        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            if ($baseNode != null) {
            }
            if ($selector != "") {
                if ($node->getAttribute($selector) != $value) {
                    continue;
                }
            }
            if (($attributeValue = $node->getAttribute($attributeName)) != '') {
                $result[] = $attributeValue;
            }
        }
        return $result;
    }

    public function getElementAttributeByTagName($tag, $attributeName, $selector = "", $value = "", $baseNode = null) {
        $result = $this->getElementAttributesByTagName($tag, $attributeName, $selector, $value, $baseNode);
        return count($result) > 0 ? $result[0] : false;
    }

    /**
     * Sets the text value for elements of class $className
     * The $currentValue both explicitly identifies an ambigous element, and the actual part of the text to be replaced by $value
     *
     * @param $className    string
     *                      The class name of the element(s) to adjust
     * @param $value        string
     *                      The value to set
     * @param $currentValue string
     *                      Disambiguator and also the part of the text that is to be replaced by $value
     */
    public function setValueByClassName($className, $value, $currentValue = "") {
        $nodes = $this->getElementsByClassName($className);
        for ($i = 0; $i < count($nodes); $i++) {
            if ($currentValue == "") {
                $nodes[$i]->nodeValue = $value;
            } else {
                if (preg_match("/^$currentValue(.*)$/", $nodes[$i]->firstChild->nodeValue, $regs)) {
                    $nodes[$i]->firstChild->nodeValue = $value . $regs[1];
                }
            }
        }
    }

    /**
     * Gets the styles of $element as an associative array of style property/value pairs
     *
     * @param $element DOMElement
     *                 The element for which to get the styles
     *
     * @return array An associative array of style property/values
     */
    public function getStyles(DOMElement $element) {
        $result = array();
        $styleString = $element->getAttribute("style");
        if ($styleString == "") {
            return $result;
        }
        $styles = explode(";", $styleString);
        for ($i = 0; $i < count($styles); $i++) {
            if ($styles[$i] != "") {
                $styleEntry = explode(":", $styles[$i]);
                $result[trim($styleEntry[0])] = trim($styleEntry[1]);
            }
        }
        return $result;
    }

    /**
     * Set style property $style to $value on $element
     *
     * @param $element DOMElement
     *                 The elemnt to set the style for
     * @param $style   string
     *                 The style property name
     * @param $value   string
     *                 The value to set
     */
    public function setStyle(DOMElement $element, $style, $value) {
        $styles = $this->getStyles($element);
        $styles[$style] = $value;
        $styleString = "";
        foreach ($styles as $property => $value) {
            $styleString .= $property . ":" . $value . ";";
        }
        $element->setAttribute("style", rtrim($styleString, ";"));
    }

    /**
     * Remove $style from $element
     *
     * @param $element DOMElement
     *                 The elemnt to remove the style from
     * @param $style   string
     *                 The style property to remove
     */
    public function removeStyle(DOMElement $element, $style) {
        $styles = $this->getStyles($element);
        if (!isset($styles[$style])) {
            return;
        }
        unset($styles[$style]);
        $styleString = "";
        foreach ($styles as $property => $value) {
            $styleString .= $property . ":" . $value . ";";
        }
        if ($styleString == "") {
            $element->removeAttribute("style");
        } else {
            $element->setAttribute("style", rtrim($styleString, ";"));
        }
    }

    /**
     * Adds the class $class to the element $element
     *
     * @param $element DOMElement
     *                 The element to use
     * @param $class   string
     *                 The class to add
     */
    public function addClass(DOMElement $element, $class) {
        $classes = $element->getAttribute("class");
        $classes .= " $class";
        $element->setAttribute("class", trim($classes));
    }


    /**
     * Get the processed html for the post.
     * Needs to remove the extra stuff saveHTML adds, and wrap it in the original surrounding code
     *
     * @param bool $bodyOnly
     *
     * @return bool|string
     */
    public function getHTML($bodyOnly = false) {
        if ($bodyOnly) {
            $body = $this->getElementsByTagName('body');
            return $this->innerHTML($body->item(0));
        }

        return $this->saveHTML();
    }

    /**
     * Retrieves a list of microdata items.
     *
     * @param string $schema
     *
     * @return DOMNodeList A DOMNodeList containing all top level microdata items.
     */
    public function getItems($schema = "") {
        if (empty($schema)) {
            return $this->xpath()->query('//*[@itemscope and not(@itemprop)]');
        } else {
            return $this->xpath()->query("//*[@itemscope and @itemtype='$schema' and not(@itemprop)]");
        }
    }

    /**
     * Creates a DOMXPath to query this document.
     *
     * @return DOMXPath object.
     */
    public function xpath() {
        return new DOMXPath($this);
    }

    private function dumpNode($node, $offset = 0) {
        $class = get_class($node);
        $id = '';
        if ($class == 'DOMElement') {
            foreach ($node->attributes as $attribute) {
                $id .= " $attribute->name=$attribute->value";
            }
        }

        $nodeName = isset($node->nodeName) ? $node->nodeName : 'noname';
        echo str_pad("", $offset) . "&lt;$nodeName$id&gt;\n";

        if ($class == 'DOMText') {
            $val = trim($node->nodeValue);
            if ($val != "\n") {
                echo str_pad("", $offset) . "'$val'\n";
            }
        }
        for ($n = $node->firstChild; $n; $n = $n->nextSibling) {
            $this->dumpNode($n, $offset + 2);
        }
    }

    public function dump($node = null) {
        echo "<pre>\n";
        $this->dumpNode($node ? $node : $this);
        echo "</pre>\n";
    }
}


