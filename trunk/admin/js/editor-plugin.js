(function () {
  tinymce.create('tinymce.plugins.PostGallery', {
    init: function (editor, url) {

      editor.addCommand('mcePostGallery', function () {
        editor.windowManager.open({
          title: 'Slider',
          width: 300,
          height: 85,
          inline: 1,
          body: [{
            type: 'listbox',
            name: 'postgalleryselect',
            label: 'Galleries',
            'values': postgalleryPosts,
          }],


          onsubmit: function( e ) {
            editor.insertContent( '[postgallery post=' + e.data.postgalleryselect + ']');
          }
        }, {
          plugin_url: url
        });
      });

      editor.addButton('PostGallery', {
        title: 'PostGallery',
        icon: 'icon dashicons-images-alt mce-i-dashicon',
        cmd: 'mcePostGallery',
        text: ' PostGallery'
      });
    },

    createControl: function (n, cm) {
      return null;
    },

    getInfo: function () {
      return {
        longname: 'PostGallery',
        author: 'RTO GmbH',
        authorurl: 'https://github.com/RTO-Websites/post-gallery',
        infourl: 'https://github.com/RTO-Websites/post-gallery/post-gallery',
        version: '1.0'
      }
    }
  });

  tinymce.PluginManager.add('PostGallery', tinymce.plugins.PostGallery);
})();