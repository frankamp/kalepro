<script type="application/ld+json"><?=$viewhelper::ldjson($recipe)?></script>
<div class="recipe-pro-recipe rp" itemscope="" itemtype="http://schema.org/Recipe"> 
	<div class="rp-name" itemprop="name"><?= $recipe->title ?></div> 
	<div class="recipe-pro-print"><button class="rp-print"><?= $labels['print'] ?></button></div>
	<div class="rp-topright">
		<div class="rp-previewimage"> 
			<img itemprop="image" src="<?= $recipe->imageUrl ?>" width="205"> 
		</div>
		<div class="rp-ratings" itemprop="aggregateRating" itemscope="" itemtype="http://schema.org/AggregateRating"> 
			<div>
				<div><span ><span itemprop="ratingValue"><?= number_format($recipe->ratingValue, 1) ?></span> from <span itemprop="ratingCount"><?= $recipe->ratingCount ?></span> reviews</span>
				</div> 
			</div> 
		</div> 
	</div>
	<div class="rp-times">
		<div class="rp-preptime">
			<div><?= $labels['prep_time'] ?></div>
			<div> <time itemprop="prepTime" datetime="<?= $viewhelper::interval( $recipe->prepTime ) ?>"><?= $viewhelper::prettyInterval( $recipe->prepTime ) ?></time> </div>
		</div>
		<div class="rp-cooktime">
			<div><?= $labels['cook_time'] ?></div>
			<div> <time itemprop="cookTime" datetime="<?=  $viewhelper::interval( $recipe->cookTime ) ?>"><?= $viewhelper::prettyInterval( $recipe->cookTime ) ?></time> </div>
		</div>
		<div class="rp-totaltime">
			<div><?= $labels['total_time'] ?></div>
			<div> <time itemprop="totalTime" datetime="<?= $viewhelper::interval( $recipe->totalTime() ) ?>"><?= $viewhelper::prettyInterval( $recipe->totalTime() ) ?></time> </div>
		</div>
	</div>
	<div class="rp-overview">
	<div class="rp-cuisine"><?= $labels['cuisine'] ?>: <span itemprop="recipeCuisine"><?= $recipe->cuisine ?></span></div>
	<div class="rp-serving">Serves: <span itemprop="recipeYield"><?= $recipe->yield ?></span></div>
		<div class="rp-description"><span itemprop="description"><?= $recipe->description ?></span></div>
	</div>
	<div> 
		<div class="rp-ingredientstitle"><?= $labels['ingredients'] ?></div> 
<?php foreach( $recipe->ingredientSections as $section ): ?>
		<?php if ( $section->name ): ?><div class="rp-subheading" itemprop="recipeIngredient"><?= $section->name ?></div> 
<?php endif; ?>
		<ul> 
<?php foreach($section->items as $ingredient): ?>
			<li class="rp-ingredients" itemprop="recipeIngredient"><?= $ingredient->description ?></li>
<?php endforeach; ?>
		</ul> 
<?php endforeach; ?>
	</div> 
	<div> 
		<div class="rp-instructionstitle"><?= $labels['instructions'] ?></div> 
		<ol> 
<?php foreach( $recipe->instructions as $instruction ): ?>
			<li class="rp-instructions" itemprop="recipeInstructions"><?= $instruction->description ?></li>
<?php endforeach; ?>
		</ol> 
	</div> 
	<div> 
		<div class="rp-notestitle"><?= $labels['notes'] ?></div> 
<?php foreach( $recipe->notes as $note ): ?>
		<div><?= $note->description ?></div> 
<?php endforeach; ?>
	</div> 
	<div class="rp-nutrition" itemprop="nutrition" itemscope="" itemtype="http://schema.org/NutritionInformation"> 
		<div class="rp-nutritionstitle"><?= $labels['nutrition_information'] ?></div> 
		<div> 
			<?= $labels['serving_size'] ?>:&nbsp;<span itemprop="servingSize"><?= $recipe->servingSize ?></span> 
			<?= $labels['calories'] ?>:&nbsp;<span itemprop="calories"><?= $recipe->calories ?></span> 
			<?= $labels['total_fat'] ?>:&nbsp;<span itemprop="fatContent"><?= $recipe->fatContent ?></span> 
			<?= $labels['trans_fat'] ?>:&nbsp;<span itemprop="transFatContent"><?= $recipe->transFatContent ?></span> 
			<?= $labels['cholesterol'] ?>:&nbsp;<span itemprop="cholesterolContent"><?= $recipe->cholesterolContent ?></span> 
			<?= $labels['saturated_fat'] ?>:&nbsp;<span itemprop="saturatedFatContent"><?= $recipe->saturatedFatContent ?></span> 
			<?= $labels['unsaturated_fat'] ?>:&nbsp;<span itemprop="unsaturatedFatContent"><?= $recipe->unsaturatedFatContent ?></span> 
			<?= $labels['carbohydrates'] ?>:&nbsp;<span itemprop="carbohydrateContent"><?= $recipe->carbohydrateContent ?></span> 
			<?= $labels['sugars'] ?>:&nbsp;<span itemprop="sugarContent"><?= $recipe->sugarContent ?></span> 
			<?= $labels['sodium'] ?>:&nbsp;<span itemprop="sodiumContent"><?= $recipe->sodiumContent ?></span> 
			<?= $labels['fiber'] ?>:&nbsp;<span itemprop="fiberContent"><?= $recipe->fiberContent ?></span> 
			<?= $labels['protein'] ?>:&nbsp;<span itemprop="proteinContent"><?= $recipe->proteinContent ?></span> 
		</div> 
		<div></div> 
	</div>
	<div class="rp-author"><?= $labels['author'] ?>: <span itemprop="author"><?= $recipe->author ?></span></div>
	<div class="rp-recipetype"><?= $labels['recipe_type'] ?>: <span itemprop="recipeCategory"><?= $recipe->type ?></span></div>	
</div>