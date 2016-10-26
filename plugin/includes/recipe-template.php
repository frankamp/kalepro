<div itemscope="" itemtype="http://schema.org/Recipe"> 
	<div itemprop="aggregateRating" itemscope="" itemtype="http://schema.org/AggregateRating"> 
		<div> 
			<div style="width: 100%"></div> 
			<div><span ><span itemprop="ratingValue">5.0</span> from <span itemprop="ratingCount">5</span> reviews</span>
			</div> 
		</div> 
	</div> 
	<div itemprop="name"><?= $recipe->title ?></div> 
	<div>&nbsp;</div> 
	<div> 
		<img itemprop="image" src="http://cdn3.minimalistbaker.com/wp-content/uploads/2016/09/Curry-Ramen-SQUARE.jpg" width="205"> 
		<div> <span ><a href="http://minimalistbaker.com/easyrecipe-print/21524-0/" rel="nofollow" target="_blank" role="button"><span ></span><span >Print Friendly Version</span></a></span> 
		</div> 
	</div> 
	<div> 
		<div> 
			<div>Prep time</div> 
			<div> <time itemprop="prepTime" datetime="PT15M">15 mins</time> </div> 
		</div> 
		<div> 
			<div>Cook time</div> 
			<div> <time itemprop="cookTime" datetime="PT1H15M">1 hour 15 mins</time> </div> 
		</div> 
		<div>
			<div>Total time</div> 
			<div> <time itemprop="totalTime" datetime="PT1H30M">1 hour 30 mins</time> </div> 
		</div> 
		<div>&nbsp;</div> 
	</div> 
	<div itemprop="description"><?= $recipe->description ?></div> 
	<div> <div>Author: <span itemprop="author"><?= $recipe->author ?></span></div> 
	<div>Recipe type: <span itemprop="recipeCategory"><?= $recipe->type ?></span></div> 
	<div>Cuisine: <span itemprop="recipeCuisine"><?= $recipe->cuisine ?></span></div> 
	<div>Serves: <span itemprop="recipeYield"><?= $recipe->yield ?></span></div> </div> 
	<div> 
		<div>Ingredients</div> 
<?php foreach( $recipe->ingredientSections as $section ): ?>
		<?php if ( $section->name ): ?><div><?= $section->name ?></div> 
<?php endif; ?>
		<ul> 
<?php foreach($section->items as $ingredient): ?>
			<li itemprop="ingredients"><?= $ingredient->description ?></li>
<?php endforeach; ?>
		</ul> 
<?php endforeach; ?>
	</div> 
	<div> 
		<div>Instructions</div> 
		<ol> 
<?php foreach( $recipe->instructions as $instruction ): ?>
			<li itemprop="recipeInstructions"><?= $instruction->description ?></li>
<?php endforeach; ?>
		</ol> 
	</div> 
	<div> 
		<div>Notes</div> 
<?php foreach( $recipe->notes as $note ): ?>
		<div><?= $note->description ?></div> 
<?php endforeach; ?>
	</div> 
	<div itemprop="nutrition" itemscope="" itemtype="http://schema.org/NutritionInformation"> 
		<div>Nutrition Information</div> 
		<div> 
			Serving size:&nbsp;<span itemprop="servingSize"><?= $recipe->servingSize ?></span> 
			Calories:&nbsp;<span itemprop="calories"><?= $recipe->calories ?></span> 
			Fat:&nbsp;<span itemprop="fatContent"><?= $recipe->fatContent ?></span> 
			Saturated fat:&nbsp;<span itemprop="saturatedFatContent"><?= $recipe->saturatedFatContent ?></span> 
			Carbohydrates:&nbsp;<span itemprop="carbohydrateContent"><?= $recipe->carbohydrateContent ?></span> 
			Sugar:&nbsp;<span itemprop="sugarContent"><?= $recipe->sugarContent ?></span> 
			Sodium:&nbsp;<span itemprop="sodiumContent"><?= $recipe->sodiumContent ?></span> 
			Fiber:&nbsp;<span itemprop="fiberContent"><?= $recipe->fiberContent ?></span> 
			Protein:&nbsp;<span itemprop="proteinContent"><?= $recipe->proteinContent ?></span> 
		</div> 
		<div></div> 
	</div>
</div>