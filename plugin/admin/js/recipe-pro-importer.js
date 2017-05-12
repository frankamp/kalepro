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
    var Item = {
      template: '<div><a v-bind:href="link">{{name}}</a></div>',
      data: function() {
        return {
          name: '',
          link: ''
        }
      },
      props: ['itemId'],
      created: function () {
        $.post({
            url: ajaxurl + '?action=recipepro_item_data',
            data: {item_id: this.itemId},
            context: this,
            dataType: 'json',
            success: function(response) {
              this.name = response.name;
              this.link = response.link;
            }
          });
      }
    };
    var app = new Vue({
      el: '#importer',
      components: {
        'item': Item
      },
      data: {
        posts: [],
        status: 'ready',
        statusValues: statusValues
      },
      created: function () {
        this.doImportWork();
      },
      methods: {
        beginImport: function (event) {
          event.preventDefault();
          $.post({
            url: ajaxurl + '?action=recipepro_begin_import',
            data: {},
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
                var progress = Math.round((response.position/response.total) * 100);
                $('#progressbar div').width(progress + "%");
                if (this.status == 'importing' && response.position != response.total) {
                  setTimeout(this.doImportWork, 100);
                }
                var items = response.imported.slice(this.posts.length);
                for (var i = 0; i < items.length; i++) {
                  this.posts.push(items[i]);
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
              }
          });
        }
      }
    });
  });
})(jQuery);