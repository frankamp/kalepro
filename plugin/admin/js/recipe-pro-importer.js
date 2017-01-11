(function($) {
  'use strict';
  $(function() {
    console.log("importer");
    var app = new Vue({
      el: '#importer',
      data: {
        importers: [
          { name: 'EasyRecipe' }
        ]
      },
      methods: {
        beginImport: function (event) {
          console.log(event.currentTarget.name);
          return false;
        }
      }
    });
  });
})(jQuery);