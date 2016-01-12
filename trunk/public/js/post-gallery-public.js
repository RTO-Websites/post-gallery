(function ($) {
  'use strict';

  /**
   * DOM-Ready
   */
  $(function () {
    window.litebox = new LiteboxGallery(liteboxArgs);
  });

  $(window).on('resize', function () {
    clearTimeout(window.pgRefreshTimeout);
    window.pgRefreshTimeout = setTimeout(function () {
      $('.postgallery-slider').each(function (index, element) {
        $(element).trigger('refresh.owl.carousel');
      });
    }, 100);
  });

  window.getFullsizeThumbs = function (pics, owlSliderId, callback) {
    var sizes = postGalleryCheckImageSize();

    jQuery.ajax({
      'type': 'POST',
      'url': websiteUrl + '/?getThumbList',
      'data': {'pics': pics, 'width': sizes[0], 'height': sizes[1]},
      'success': function (data, textStatus) {
        if (typeof(callback) === 'function') {
          callback(jQuery.parseJSON(data));
        }
      }
    });
  };

  window.getThumbs = function (pics, width, height, callback, scale) {
    if (typeof(scale) === 'undefined') {
      scale = 0;
    }
    jQuery.ajax({
      'type': 'POST',
      'url': websiteUrl + '/?getThumbList',
      'data': {'pics': pics, 'width': width, 'height': height, scale: scale},
      'success': function (data, textStatus) {
        if (typeof(callback) === 'function') {
          callback(jQuery.parseJSON(data));
        }
      }
    });
  };


  window.postGalleryCheckImageSize = function () {
    var galleryWidth = jQuery(window).width();
    var galleryHeight = jQuery(window).height();
    if (galleryHeight == 0) {
      galleryHeight = 1080;
      galleryWidth = 1920;
    }
    if (galleryHeight <= 1920 && galleryHeight > 1600) {
      galleryHeight = 1920;
    }
    if (galleryHeight <= 1600 && galleryHeight > 1280) {
      galleryHeight = 1600;
    }
    if (galleryHeight <= 1280 && galleryHeight > 1080) {
      galleryHeight = 1280;
    }
    if (galleryHeight <= 1080 && galleryHeight > 800) {
      galleryHeight = 1080;
    }
    if (galleryHeight <= 800 && galleryHeight > 600) {
      galleryHeight = 800;
    }
    if (galleryHeight <= 600 && galleryHeight > 480) {
      galleryHeight = 600;
    }
    if (galleryHeight <= 480 && galleryHeight > 320) {
      galleryHeight = 480;
    }
    if (galleryHeight <= 320) {
      galleryHeight = 320;
    }


    if (galleryWidth <= 1920 && galleryWidth > 1600) {
      galleryWidth = 1920;
    }
    if (galleryWidth <= 1600 && galleryWidth > 1280) {
      galleryWidth = 1600;
    }
    if (galleryWidth <= 1280 && galleryWidth > 1080) {
      galleryWidth = 1280;
    }
    if (galleryWidth <= 1080 && galleryWidth > 800) {
      galleryWidth = 1080;
    }
    if (galleryWidth <= 800 && galleryWidth > 600) {
      galleryWidth = 800;
    }
    if (galleryWidth <= 600 && galleryWidth > 480) {
      galleryWidth = 600;
    }
    if (galleryWidth <= 480 && galleryWidth > 320) {
      galleryWidth = 480;
    }
    if (galleryWidth <= 320) {
      galleryWidth = 320;
    }

    return [galleryWidth, galleryHeight];
  };

})(jQuery);
