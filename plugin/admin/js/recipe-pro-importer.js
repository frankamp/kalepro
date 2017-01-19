(function($) {
  'use strict';
  $(function() {
    if (!$('#importer').length) {
      return;
    }
    var statusValues = {
      ready: "Ready to Import",
      importing: "Importing..."
    };
    var app = new Vue({
      el: '#importer',
      data: {
        importers: [
          { name: 'EasyRecipe', tag: 'easyrecipe', description: "Converts the recipe markup to RecipePro data and replaces the markup with a shortcode." }
        ],
        importer: null,
        status: 'ready',
        statusValues: statusValues
      },
      created: function () {
        this.doImportWork();
      },
      methods: {
        importerByTag: function(tag) {
          return this.importers.find(function(element) {return element.tag === tag});
        },
        beginImport: function (event) {
          event.preventDefault();
          $.post({
            url: ajaxurl + '?action=recipepro_begin_import',
            data: {importerName: event.target.attributes.tag.value},
            context: this,
            dataType: 'json',
            success: function(response) {
              this.status = response.status;
              setTimeout(this.doImportWork, 100);
            }
          });
        },
        doImportWork: function (event) {
          event ? event.preventDefault() : null;
          $.post({
              url: ajaxurl + '?action=recipepro_do_import_work',
              data: {},
              context: this,
              dataType: 'json',
              success: function(response) {
                //alert('Got this from the server: ' + response);
                this.status = response.status;
                this.importer = this.importerByTag(response.importer);
                var progress = Math.round((response.position/response.total) * 100);
                $('#progressbar div').width(progress + "%");
                if (this.status == 'importing' && response.position != response.total) {
                  setTimeout(this.doImportWork, 100);
                }
              }
          });
        },
        cancel: function (event) {
          event ? event.preventDefault() : null;
          $.post({
              url: ajaxurl + '?action=recipepro_cancel_import',
              data: {},
              context: this,
              dataType: 'json',
              success: function(response) {
                //alert('Got this from the server: ' + response);
                this.status = response.status;
                this.importer = null;
              }
          });
        }
      }
    });
  });
})(jQuery);