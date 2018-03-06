(function () {
  tinymce.create('tinymce.plugins.PostGallerySlider', {
    init: function (editor, url) {

      editor.addCommand('mcePostGallerySlider', function () {
        editor.windowManager.open({
          title: 'Slider',
          width: 210,
          height: 85,
          inline: 1,
          body: [{
            type: 'listbox',
            name: 'postgallerysliderselect',
            label: 'Slider',
            'values': postgallerySliders,
          }],


          onsubmit: function( e ) {
            editor.insertContent( '[slider ' + e.data.postgallerysliderselect + ']');
          }
        }, {
          plugin_url: url
        });
      });

      editor.addButton('PostGallerySlider', {
        title: 'Slider',
        icon: 'icon dashicons-images-alt mce-i-dashicon',
        cmd: 'mcePostGallerySlider',
        text: ' Slider'
      });
    },

    createControl: function (n, cm) {
      return null;
    },

    getInfo: function () {
      return {
        longname: 'Slider',
        author: 'Crazypsycho',
        authorurl: 'https://github.com/RTO-Websites/post-gallery',
        infourl: 'https://github.com/RTO-Websites/post-gallery/post-gallery',
        version: '1.0'
      }
    }
  });

  tinymce.PluginManager.add('PostGallerySlider', tinymce.plugins.PostGallerySlider);
})();