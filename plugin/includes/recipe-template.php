<script type="application/ld+json"><?=$viewhelper::ldjson($recipe, $ratingsEnabled)?></script>
<div class="recipe-pro-recipe rp" itemscope="" itemtype="http://schema.org/Recipe"> 
	<h2 class="rp-name" itemprop="name"><?= $recipe->title ?></h2>
	<div class="rp-topright">
		<div class="recipe-pro-print"><button class="rp-print" style="display: inline-block"><?= $labels['print'] ?></button></div>
<?php if ( strlen( $recipe->getImageUrl()) ): ?>
		<div class="rp-previewimage">
			<img itemprop="image" src="<?= $recipe->getImageUrl() ?>" width="205" />
		</div>
<?php endif; ?>
<?php if ($ratingsEnabled): ?>
		<div class="rp-ratings" itemprop="aggregateRating" itemscope="" itemtype="http://schema.org/AggregateRating">
					<div class="rp-ratings-total">
					  <div class="rp-ratings-total-top" style="width:<?=($recipe->ratingValue/5)*100?>%"><span class="rp-rating-top"></span><span class="rp-rating-top"></span><span class="rp-rating-top"></span><span class="rp-rating-top"></span><span class="rp-rating-top"></span></div>
					  <div class="rp-ratings-total-bottom"><span class="rp-rating-bottom"></span><span class="rp-rating-bottom"></span><span class="rp-rating-bottom"></span><span class="rp-rating-bottom"></span><span class="rp-rating-bottom"></span></div>
					</div>
					<div class="rp-ratingvalue"><span itemprop="ratingValue"><?= number_format($recipe->ratingValue, 1) ?></span> from <span itemprop="ratingCount"><?= $recipe->ratingCount ?></span> reviews</div>
		</div>
<?php endif; ?>
	</div>
	<div class="rp-times">
<?php if ( !$viewhelper::intervalsAreEqual( $recipe->prepTime, new DateInterval("PT0M") ) ): ?>
	<div class="rp-preptime">
		<div class="rp-timetitle"><?= $labels['prep_time'] ?></div>
		<div class="rp-time"><time itemprop="prepTime" datetime="<?= $viewhelper::interval( $recipe->prepTime ) ?>"><?= $viewhelper::prettyInterval( $recipe->prepTime ) ?></time> </div>
	</div>
<?php endif; ?>
<?php if ( !$viewhelper::intervalsAreEqual( $recipe->cookTime, new DateInterval("PT0M") ) ): ?>
	<div class="rp-cooktime">
		<div class="rp-timetitle"><?= $labels['cook_time'] ?></div>
		<div class="rp-time"><time itemprop="cookTime" datetime="<?=  $viewhelper::interval( $recipe->cookTime ) ?>"><?= $viewhelper::prettyInterval( $recipe->cookTime ) ?></time> </div>
	</div>
<?php endif; ?>
<?php if ( !$viewhelper::intervalsAreEqual( $recipe->totalTime(), new DateInterval("PT0M") ) ): ?>
	<div class="rp-totaltime">
		<div class="rp-timetitle"><?= $labels['total_time'] ?></div>
		<div class="rp-time"><time itemprop="totalTime" datetime="<?= $viewhelper::interval( $recipe->totalTime() ) ?>"><?= $viewhelper::prettyInterval( $recipe->totalTime() ) ?></time> </div>
	</div>
<?php endif; ?>
	</div>
	<div class="rp-overview">
	<div class="rp-cuisine"><span class="rp-cuisinetitle"><?= $labels['cuisine'] ?></span>: <span itemprop="recipeCuisine"><?= $recipe->cuisine ?></span></div>
	<div class="rp-serving"><span class="rp-servingtitle">Serves</span>: <span itemprop="recipeYield"><?= $recipe->yield ?></span></div>
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
<?php if ( $recipe->notes ): ?>
	<h3 class="rp-notestitle"><?= $labels['notes'] ?></h3>
<?php endif; ?>
<?php foreach( $recipe->notes as $note ): ?>
	<div><?= $note->description ?></div> 
<?php endforeach; ?>
<?php if ( $recipe->servingSize
		|| $recipe->calories
		|| $recipe->fatContent
		|| $recipe->transFatContent
		|| $recipe->cholesterolContent
		|| $recipe->cholesterolContent
		|| $recipe->saturatedFatContent
		|| $recipe->unsaturatedFatContent
		|| $recipe->carbohydrateContent
		|| $recipe->sugarContent
		|| $recipe->sodiumContent
		|| $recipe->fiberContent
		|| $recipe->proteinContent ) {?>
	<div class="rp-nutrition" itemprop="nutrition" itemscope="" itemtype="http://schema.org/NutritionInformation"> 
		<h3 class="rp-nutritiontitle"><?= $labels['nutrition_information'] ?></h3> 
<span class="rp-nutrition-continuous"><?php if (strlen($recipe->servingSize)) {?><?= $labels['serving_size'] ?>:&nbsp;<span class="servingsize" itemprop="servingSize"><?= $recipe->servingSize ?></span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->calories) {?><?= $labels['calories'] ?>:&nbsp;<span class="calories" itemprop="calories"><?= $recipe->calories ?></span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->fatContent) {?><?= $labels['total_fat'] ?>:&nbsp;<span class="fat" itemprop="fatContent"><?= $recipe->fatContent ?> g</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->transFatContent) {?><?= $labels['trans_fat'] ?>:&nbsp;<span class="transfat" itemprop="transFatContent"><?= $recipe->transFatContent ?> g</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->cholesterolContent) {?><?= $labels['cholesterol'] ?>:&nbsp;<span class="cholesterol" itemprop="cholesterolContent"><?= $recipe->cholesterolContent ?> mg</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->saturatedFatContent) {?><?= $labels['saturated_fat'] ?>:&nbsp;<span class="saturatedfat" itemprop="saturatedFatContent"><?= $recipe->saturatedFatContent ?> g</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->unsaturatedFatContent) {?><?= $labels['unsaturated_fat'] ?>:&nbsp;<span class="unsaturatedfat" itemprop="unsaturatedFatContent"><?= $recipe->unsaturatedFatContent ?> g</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->carbohydrateContent) {?><?= $labels['carbohydrates'] ?>:&nbsp;<span class="carbohydrate" itemprop="carbohydrateContent"><?= $recipe->carbohydrateContent ?> g</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->sugarContent) {?><?= $labels['sugars'] ?>:&nbsp;<span class="sugar" itemprop="sugarContent"><?= $recipe->sugarContent ?> g</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->sodiumContent) {?><?= $labels['sodium'] ?>:&nbsp;<span class="sodium" itemprop="sodiumContent"><?= $recipe->sodiumContent ?> mg</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->fiberContent) {?><?= $labels['fiber'] ?>:&nbsp;<span class="fiber" itemprop="fiberContent"><?= $recipe->fiberContent ?> g</span><?php } ?></span>
<span class="rp-nutrition-continuous"><?php if ($recipe->proteinContent) {?><?= $labels['protein'] ?>:&nbsp;<span class="protein" itemprop="proteinContent"><?= $recipe->proteinContent ?> g</span><?php } ?></span>
	</div>
<?php } //end if any nutrition information ?>
	<div class="rp-detailsafter">
		<div class="rp-author"><?= $labels['author'] ?>: <span itemprop="author"><?= $recipe->author ?></span></div>
		<div class="rp-recipetype"><?= $labels['recipe_type'] ?>: <span itemprop="recipeCategory"><?= $recipe->type ?></span></div>
	</div>
	<div class="rp-source" style="display: none;">Recipe by <?= $recipe->author ?> at <?=get_permalink() ?></div>
</div>