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
				'currentTab': 'recipe-pro-tab-overview',
				'title' : 'my cool recipe'
			},
			urlRoot: ajaxurl + '?action=recipepro_recipe&postid=',
			ingestIngredients: function(ingredientDocument) {
				var target = this.get('ingredients');
				target.reset();
				var extracted = $(ingredientDocument).children('p').each(function(){
					target.add(new Ingredient({id: generateUUID(), description: $(this).text()}));
				});
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
				"change input" : "change"
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
				if (this.model.get('currentTab') == 'recipe-pro-tab-ingredient') {
					tinyMCE.EditorManager.remove('#recipe-pro-editor-ingredient');
				}
				if (this.model.get('currentTab') == 'recipe-pro-tab-instruction') {
					tinyMCE.EditorManager.remove('#recipe-pro-editor-instruction');
				}
				this.model.set({'currentTab': toggleTo});
				if (toggleTo == 'recipe-pro-tab-ingredient') {
					tinyMCEPreInit.mceInit['recipe-pro-editor-ingredient'].init_instance_callback = function(inst) {
						inst.setContent('<p>' + this.model.get('ingredientSections')[0].items[0].description + '</p>');
					}.bind(this);
					tinyMCE.init(tinyMCEPreInit.mceInit['recipe-pro-editor-ingredient']);
				}
				if (toggleTo == 'recipe-pro-tab-instruction') {
					tinyMCEPreInit.mceInit['recipe-pro-editor-instruction'].init_instance_callback = function(inst) {
						inst.setContent('<p>' + this.model.get('instructions')[0].description + '</p>');
					}.bind(this);
					tinyMCE.init(tinyMCEPreInit.mceInit['recipe-pro-editor-instruction']);
				}
				//$('#' + toggleTo).show().siblings('.recipe-pro-tab').hide();
			},
			change : function(e) {
				var element = $(e.currentTarget);
				var input = element.val();
				var name = element.attr('name');
				if ( input !== this.model.get( name ) ) {
					this.model.set(name, input);
				}
				return true;
			},
			template: _.template( $('#recipe-pro-recipe-template').html() )
		});
		new RecipeView({
			model: recipe,
			el: container
		});
	});
})( jQuery );