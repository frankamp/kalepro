<script type="application/ld+json"><?=$viewhelper::ldjson($recipe)?></script>
<div id="recipe-pro-recipe" class="rp" itemscope="" itemtype="http://schema.org/Recipe"> 
	<div itemprop="aggregateRating" itemscope="" itemtype="http://schema.org/AggregateRating"> 
		<div> 
			<div style="width: 100%"></div> 
			<div><span ><span itemprop="ratingValue"><?= number_format($recipe->ratingValue, 1) ?></span> from <span itemprop="ratingCount"><?= $recipe->ratingCount ?></span> reviews</span>
			</div> 
		</div> 
	</div> 
	<div class="name" itemprop="name"><?= $recipe->title ?></div> 
	<div> 
		<img itemprop="image" src="<?= $recipe->imageUrl ?>" width="205"> 
	</div> 
	<div><button id="recipe-pro-print">Print</button></div> 
	<div> 
		<div> 
			<div>Prep time</div> 
			<div> <time itemprop="prepTime" datetime="<?= $viewhelper::interval( $recipe->prepTime ) ?>"><?= $viewhelper::prettyInterval( $recipe->prepTime ) ?></time> </div> 
		</div> 
		<div> 
			<div>Cook time</div> 
			<div> <time itemprop="cookTime" datetime="<?=  $viewhelper::interval( $recipe->cookTime ) ?>"><?= $viewhelper::prettyInterval( $recipe->cookTime ) ?></time> </div> 
		</div> 
		<div>
			<div>Total time</div> 
			<div> <time itemprop="totalTime" datetime="<?= $viewhelper::interval( $recipe->totalTime() ) ?>"><?= $viewhelper::prettyInterval( $recipe->totalTime() ) ?></time> </div> 
		</div> 
	</div> 
	<div itemprop="description"><?= $recipe->description ?></div> 
	<div> <div>Author: <span itemprop="author"><?= $recipe->author ?></span></div> 
	<div>Recipe type: <span itemprop="recipeCategory"><?= $recipe->type ?></span></div> 
	<div>Cuisine: <span itemprop="recipeCuisine"><?= $recipe->cuisine ?></span></div> 
	<div>Serves: <span itemprop="recipeYield"><?= $recipe->yield ?></span></div> </div> 
	<div> 
		<div class="ingredientstitle">Ingredients</div> 
<?php foreach( $recipe->ingredientSections as $section ): ?>
		<?php if ( $section->name ): ?><div class="subheading" itemprop="recipeIngredient"><?= $section->name ?></div> 
<?php endif; ?>
		<ul> 
<?php foreach($section->items as $ingredient): ?>
			<li class="ingredients" itemprop="recipeIngredient"><?= $ingredient->description ?></li>
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
			Trans fat:&nbsp;<span itemprop="transFatContent"><?= $recipe->transFatContent ?></span> 
			Cholesterol:&nbsp;<span itemprop="cholesterolContent"><?= $recipe->cholesterolContent ?></span> 
			Saturated fat:&nbsp;<span itemprop="saturatedFatContent"><?= $recipe->saturatedFatContent ?></span> 
			Unsaturated fat:&nbsp;<span itemprop="unsaturatedFatContent"><?= $recipe->unsaturatedFatContent ?></span> 
			Carbohydrates:&nbsp;<span itemprop="carbohydrateContent"><?= $recipe->carbohydrateContent ?></span> 
			Sugar:&nbsp;<span itemprop="sugarContent"><?= $recipe->sugarContent ?></span> 
			Sodium:&nbsp;<span itemprop="sodiumContent"><?= $recipe->sodiumContent ?></span> 
			Fiber:&nbsp;<span itemprop="fiberContent"><?= $recipe->fiberContent ?></span> 
			Protein:&nbsp;<span itemprop="proteinContent"><?= $recipe->proteinContent ?></span> 
		</div> 
		<div></div> 
	</div>
</div>