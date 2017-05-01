<script type="application/ld+json"><?=$viewhelper::ldjson($recipe)?></script>
<div class="recipe-pro-recipe rp" itemscope="" itemtype="http://schema.org/Recipe"> 
	<h2 class="rp-name" itemprop="name"><?= $recipe->title ?></h2>
	<div class="rp-topright">
		<div class="recipe-pro-print"><button class="rp-print" style="display: inline-block"><?= $labels['print'] ?></button></div>
		<div class="rp-previewimage">
			<img itemprop="image" src="<?= $recipe->imageUrl ?>" width="205" />
		</div>
		<div class="rp-ratings" itemprop="aggregateRating" itemscope="" itemtype="http://schema.org/AggregateRating">
					<div class="rp-stars">
<?php for( $i=1; $i <= 5; $i++ ) { ?>
					<span class="rp-star <?=($recipe->ratingValue >= $i ? 'rp-star-active' : '')?>" title="<?= $i ?> star<?=($i > 1 ? 's' : '')?>"></span>
<?php } ?>
					</div>
					<div class="rp-ratingvalue"><span itemprop="ratingValue"><?= number_format($recipe->ratingValue, 1) ?></span> from <span itemprop="ratingCount"><?= $recipe->ratingCount ?></span> reviews</div>
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
		<h3 class="rp-ingredientstitle"><?= $labels['ingredients'] ?></h3> 
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
		<h3 class="rp-instructionstitle"><?= $labels['instructions'] ?></h3> 
		<ol> 
<?php foreach( $recipe->instructions as $instruction ): ?>
			<li class="rp-instructions" itemprop="recipeInstructions"><?= $instruction->description ?></li>
<?php endforeach; ?>
		</ol> 
	</div> 
	<div> 
		<h3 class="rp-notestitle"><?= $labels['notes'] ?></h3> 
<?php foreach( $recipe->notes as $note ): ?>
		<div><?= $note->description ?></div> 
<?php endforeach; ?>
	</div> 
<?php if ( strlen($recipe->servingSize)
		|| strlen($recipe->calories)
		|| strlen($recipe->fatContent)
		|| strlen($recipe->transFatContent)
		|| strlen($recipe->cholesterolContent)
		|| strlen($recipe->cholesterolContent)
		|| strlen($recipe->saturatedFatContent)
		|| strlen($recipe->unsaturatedFatContent)
		|| strlen($recipe->carbohydrateContent)
		|| strlen($recipe->sugarContent)
		|| strlen($recipe->sodiumContent)
		|| strlen($recipe->fiberContent)
		|| strlen($recipe->proteinContent) ) {?>
	<div class="rp-nutrition" itemprop="nutrition" itemscope="" itemtype="http://schema.org/NutritionInformation"> 
		<h3 class="rp-nutritiontitle"><?= $labels['nutrition_information'] ?></h3> 
<?php if (strlen($recipe->servingSize)) {?><?= $labels['serving_size'] ?>:&nbsp;<span class="servingsize" itemprop="servingSize"><?= $recipe->servingSize ?></span><?php } ?>
<?php if (strlen($recipe->calories)) {?><?= $labels['calories'] ?>:&nbsp;<span class="calories" itemprop="calories"><?= $recipe->calories ?></span><?php } ?>
<?php if (strlen($recipe->fatContent)) {?><?= $labels['total_fat'] ?>:&nbsp;<span class="fat" itemprop="fatContent"><?= $recipe->fatContent ?></span><?php } ?>
<?php if (strlen($recipe->transFatContent)) {?><?= $labels['trans_fat'] ?>:&nbsp;<span class="transfat" itemprop="transFatContent"><?= $recipe->transFatContent ?></span><?php } ?>
<?php if (strlen($recipe->cholesterolContent)) {?><?= $labels['cholesterol'] ?>:&nbsp;<span class="cholesterol" itemprop="cholesterolContent"><?= $recipe->cholesterolContent ?></span><?php } ?>
<?php if (strlen($recipe->saturatedFatContent)) {?><?= $labels['saturated_fat'] ?>:&nbsp;<span class="saturatedfat" itemprop="saturatedFatContent"><?= $recipe->saturatedFatContent ?></span><?php } ?>
<?php if (strlen($recipe->unsaturatedFatContent)) {?><?= $labels['unsaturated_fat'] ?>:&nbsp;<span class="unsaturatedfat" itemprop="unsaturatedFatContent"><?= $recipe->unsaturatedFatContent ?></span><?php } ?>
<?php if (strlen($recipe->carbohydrateContent)) {?><?= $labels['carbohydrates'] ?>:&nbsp;<span class="carbohydrate" itemprop="carbohydrateContent"><?= $recipe->carbohydrateContent ?></span><?php } ?>
<?php if (strlen($recipe->sugarContent)) {?><?= $labels['sugars'] ?>:&nbsp;<span class="sugar" itemprop="sugarContent"><?= $recipe->sugarContent ?></span><?php } ?>
<?php if (strlen($recipe->sodiumContent)) {?><?= $labels['sodium'] ?>:&nbsp;<span class="sodium" itemprop="sodiumContent"><?= $recipe->sodiumContent ?></span><?php } ?>
<?php if (strlen($recipe->fiberContent)) {?><?= $labels['fiber'] ?>:&nbsp;<span class="fiber" itemprop="fiberContent"><?= $recipe->fiberContent ?></span><?php } ?>
<?php if (strlen($recipe->proteinContent)) {?><?= $labels['protein'] ?>:&nbsp;<span class="protein" itemprop="proteinContent"><?= $recipe->proteinContent ?></span><?php } ?>

	</div>
<?php } //end if any nutrition information ?>
	<div class="rp-detailsafter">
		<div class="rp-author"><?= $labels['author'] ?>: <span itemprop="author"><?= $recipe->author ?></span></div>
		<div class="rp-recipetype"><?= $labels['recipe_type'] ?>: <span itemprop="recipeCategory"><?= $recipe->type ?></span></div>
	</div>
	<div class="rp-source" style="display: none;"><?= $labels['print'] ?>Recipe from <?= $labels['author'] ?> here: <?=get_permalink() ?></div>
</div>