(function($) {
  var hadContent = false;
  tinymce.create('tinymce.plugins.recipepro', {
    data: {
      hadShortcode: false
    },
    /**
     * Initializes the plugin, this will be executed after the plugin has been created.
     * This call is done before the editor instance has finished it's initialization so use the onInit event
     * of the editor instance to intercept that event.
     *
     * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
     * @param {string} url Absolute URL to where the plugin is located.
     */        
    init : function(ed, url) {
      if (ed.id != 'content') {return;}
      // eheck to see if this editor has this particular button (recipepro_addeditrecipe) enabled in a toolbar, if not, lets not bother
      // registering the button or command or callbacks for change events etc.
      var getAttr = function (s, n) {
          n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
          return n ?  window.decodeURIComponent(n[1]) : '';
      };

      var html = function ( cls, data, con ) {
          //var placeholder = url + '/img/recipepro.jpg';
          data = window.encodeURIComponent( data );
          content = window.encodeURIComponent( con );

          return '<img src="' + tinymce.Env.transparentSrc + '" class="mceItem recipeproplaceholder ' + cls + '" data-mce-resize="false" data-mce-placeholder="1" />';
      };

      var replaceShortcodes = function ( content ) {
          return content.replace( /\[recipepro\]/g, function( all,attr,con) {
              return html( 'recipeproMceItem', attr , con);
          });
      };

      var restoreShortcodes = function ( content ) {
          return content.replace( /(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function( match, image ) {
              var data = getAttr( image, 'class' );
              if ( data.indexOf('recipeproMceItem') != -1) {
                  return '[recipepro]';
              }
              return match;
          });
      };

      //replace from shortcode to an placeholder image
      ed.on('BeforeSetcontent', function(event){
          event.content = replaceShortcodes( event.content );
      });
      
      //replace from placeholder image to shortcode
      ed.on('GetContent', function(event){
          event.content = restoreShortcodes(event.content);
      });

      ed.addButton('recipepro_addeditrecipe', {
        title : 'Add Recipe',
        cmd : 'recipepro_addeditrecipe',
        icon: 'recipe_pro_carrot'
      });
      ed.addCommand('recipepro_addeditrecipe', function() {
        shortcode = '[recipepro]';
        ed.execCommand('mceInsertContent', 0, shortcode);
      });
      // ed.addButton( 'recipepro_media', {
      //  tooltip: 'Add photo',
      //  onclick: function() {
      //    ed.execCommand( 'WP_Medialib' );
      //  }
      // });
      var contentHasCode = function () {
        return this.getContent().indexOf("[recipepro]") != -1;
      }.bind(ed);
      var updateShortcodeTracking = function () {
        var currentlyHasCode = contentHasCode();
        if (!currentlyHasCode && this.plugins.recipepro.data.hadShortcode) {
          window.RecipePro.currentRecipe.disableForMissingShortcode(true);
        } else if (!currentlyHasCode) {
          window.RecipePro.currentRecipe.disableForMissingShortcode(false);
        } else {
          this.plugins.recipepro.data.hadShortcode = true;
          window.RecipePro.currentRecipe.enableForFoundShortcode();
        }
      }.bind(ed);
      ed.on('Init', function (e) {
        updateShortcodeTracking();
      });
      ed.on('Change', function (e) {
        updateShortcodeTracking();
      });
    },
    /**
     * Creates control instances based in the incomming name. This method is normally not
     * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
     * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
     * method can be used to create those.
     *
     * @param {String} n Name of the control to create.
     * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
     * @return {tinymce.ui.Control} New control instance or null if no control was created.
     */
    createControl : function(n, cm) {
      return null;
    },
 
    /**
     * Returns information about the plugin as a name/value array.
     * The current keys are longname, author, authorurl, infourl and version.
     *
     * @return {Object} Name/value array containing information about the plugin.
     */
    getInfo : function() {
      return {
        longname : 'RecipePro Buttons',
        author : '',
        authorurl : '',
        infourl : '',
        version : "0.1"
      };
    }
  });
 
  // Register plugin
  tinymce.PluginManager.add( 'recipepro', tinymce.plugins.recipepro );
})(jQuery);