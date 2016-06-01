(function( $ ) {
	'use strict';

	$(function() {
		var targetElement = $('#recipe-pro-admin-container');
		if (targetElement.length) {
			var Ingredient = Backbone.Model.extend({
				defaults : {
					quantity: 0,
					unit: 'cup',
					name : 'carrot'
				}
			});
			var Ingredients = Backbone.Collection.extend({
				model: Ingredient
			});
			var BaseNestedModel = Backbone.Model.extend({
				model: {},
				parse: function(response) {
					for(var key in this.model)
					{
						var embeddedClass = this.model[key];
						var embeddedData = response[key];
						response[key] = new embeddedClass(embeddedData, {parse:true});
					}
					return response;
				}
			});
			BaseNestedModel.prototype.toJSON = function() {
				if (this._isSerializing) {
					return this.id || this.cid;
				}
				this._isSerializing = true;
				var json = _.clone(this.attributes);
				_.each(json, function(value, name) {
					_.isFunction(value.toJSON) && (json[name] = value.toJSON());
				});
				this._isSerializing = false;
				return json;
			};
			var Recipe = BaseNestedModel.extend({
				model: {
					ingredients: Ingredients
				},
				defaults : {
					'title' : 'my cool recipe'
				},
				urlRoot : ajaxurl + '?action=recipepro_recipe&postid='
			});
			var recipe = new Recipe({id: targetElement.attr('data-post')});
			recipe.fetch();
			var RecipeView = Backbone.View.extend({
				events: {
					"click #add-ingredient"         : "addIngredient"
				},
				initialize: function(){
					_.bindAll(this, "render");
					this.model.bind('change', this.render);
				},
				render: function() {
					this.$el.html(this.template(this.model.toJSON()));
					return this;
				},
				addIngredient: function() {
					this.model.get('ingredients').add(new Ingredient());
					this.model.set({'title': 'othertitle'});
				},
				template: _.template( $('#recipe-pro-recipe-template').html() )
			});
			new RecipeView({
				model: recipe,
				el: targetElement
			});
		}
	});
})( jQuery );
