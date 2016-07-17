(function( $ ) {
	'use strict';

	$(function() {
		var container = $('#recipe-pro-admin-container');
		if (!container.length) {
			return;
		}
		var generateUUID = function (){
			var d = new Date().getTime();
			if(window.performance && typeof window.performance.now === "function"){
				d += performance.now(); //use high-precision timer if available
			}
			return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
				var r = (d + Math.random()*16)%16 | 0;
				d = Math.floor(d/16);
				return (c=='x' ? r : (r&0x3|0x8)).toString(16);
			});
		};
		var Ingredient = Backbone.Model.extend({
			defaults : {
				quantity: 0,
				unit: 'cup',
				name : 'carrot',
				description: ''
			}
		});
		var Ingredients = Backbone.Collection.extend({
			model: Ingredient
		});
		var BaseNestedModel = Backbone.Model.extend({
			blacklist: [],
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
			blacklist: ['currentTab'],
			model: {
				ingredients: Ingredients
			},
			defaults: {
				'currentTab': 'recipe-pro-tab-ingredient',
				'title' : 'my cool recipe'
			},
			urlRoot: ajaxurl + '?action=recipepro_recipe&postid=',
			ingestIngredients: function(ingredientDocument) {
				var target = this.get('ingredients');
				var extracted = $(ingredientDocument).children('p').each(function(){
					target.add(new Ingredient({id: generateUUID(), description: $(this).text()}));
				});
				this.bump();
				console.log("done");
			},
			bump: function() {
				this.set({'update': generateUUID()});
			}
		});
		var recipe = new Recipe({id: container.attr('data-post')});
		recipe.fetch();
		window.RecipePro = {
			currentRecipe: recipe
		};
		var RecipeView = Backbone.View.extend({
			events: {
				"click .recipe-pro-tab-button": "tabClick",
				"click #add-ingredient" : "addIngredient",
				"keypress input" : "blur"
			},
			initialize: function(){
				_.bindAll(this, "render");
				this.model.bind('change', this.render);
			},
			render: function() {
				var jsonable = this.model.toJSON();
				jsonable['doc'] = JSON.stringify(_.omit(jsonable, this.model.blacklist));
				this.$el.html(this.template(jsonable));
				return this;
			},
			tabClick: function (e) {
				var toggleTo = $(e.currentTarget).parent().attr('for');
				this.model.set({'currentTab': toggleTo});
				//$('#' + toggleTo).show().siblings('.recipe-pro-tab').hide();
			},
			blur : function() {
				var input = this.$('input').val();
				var name = this.$('input').attr('name');
				if ( input !== this.model.get( name ) ) {
					this.model.set(name, input);
				}
				return true;
			},
			addIngredient: function() {
				this.model.get('ingredients').add(new Ingredient({id: generateUUID()}));
				this.model.bump();
			},
			template: _.template( $('#recipe-pro-recipe-template').html() )
		});
		new RecipeView({
			model: recipe,
			el: container
		});
	});
})( jQuery );
//# sourceURL=recipe-pro-admin.js