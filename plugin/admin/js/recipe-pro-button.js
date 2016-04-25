(function($) {
    console.log("getting tinymce loaded up");
    tinymce.create('tinymce.plugins.recipepro', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            console.log("initializing RecipePro at " + url)
            ed.addButton('addeditrecipe', {
                title : 'Add Recipe',
                cmd : 'addeditrecipe',
                icon: 'recipe_pro_carrot'
            });
            ed.addCommand('addeditrecipe', function() {
                console.log("attempting to modal");
                $('#recipeproeditor').modal();
                $('#recipeproeditor').attr("tabindex",-1).focus();
                // var number = prompt("How many posts you want to show ? ");
                // var shortcode;
                // if (number !== null) {
                //     number = parseInt(number);
                //     if (number > 0 && number <= 20) {
                //         shortcode = '[recipepro number="' + number + '"/]';
                //         ed.execCommand('mceInsertContent', 0, shortcode);
                //     }
                //     else {
                //         alert("The number value is invalid. It should be from 0 to 20.");
                //     }
                // }
                console.log("attempted to modal");
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