(function ($) {
  'use strict';

  /**
   * DOM-Ready
   */
  $(function () {
    window.litebox = new LiteboxGallery(window.pgConfig.liteboxArgs);
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
    var sizes = pgCheckImageSize();

    jQuery.ajax({
      'type': 'POST',
      'url': window.pgConfig.websiteUrl + '/?getThumbList',
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
      'url': window.pgConfig.websiteUrl + '/?getThumbList',
      'data': {'pics': pics, 'width': width, 'height': height, scale: scale},
      'success': function (data, textStatus) {
        if (typeof(callback) === 'function') {
          callback(jQuery.parseJSON(data));
        }
      }
    });
  };


  window.pgCheckImageSize = function () {
    var gWidth = jQuery(window).width();
    var gHeight = jQuery(window).height();

    if (gHeight == 0) {
      gHeight = 1080;
      gWidth = 1920;
    }

    if (gHeight > 1920) {
      gHeight = 2560;
    }
    if (gHeight <= 1920 && gHeight > 1600) {
      gHeight = 1920;
    }
    if (gHeight <= 1600 && gHeight > 1280) {
      gHeight = 1600;
    }
    if (gHeight <= 1280 && gHeight > 1080) {
      gHeight = 1280;
    }
    if (gHeight <= 1080 && gHeight > 800) {
      gHeight = 1080;
    }
    if (gHeight <= 800 && gHeight > 600) {
      gHeight = 800;
    }
    if (gHeight <= 600 && gHeight > 480) {
      gHeight = 600;
    }
    if (gHeight <= 480 && gHeight > 320) {
      gHeight = 480;
    }
    if (gHeight <= 320) {
      gHeight = 320;
    }


    if (gWidth > 1920) {
      gWidth = 2560;
    }
    if (gWidth <= 1920 && gWidth > 1600) {
      gWidth = 1920;
    }
    if (gWidth <= 1600 && gWidth > 1280) {
      gWidth = 1600;
    }
    if (gWidth <= 1280 && gWidth > 1080) {
      gWidth = 1280;
    }
    if (gWidth <= 1080 && gWidth > 800) {
      gWidth = 1080;
    }
    if (gWidth <= 800 && gWidth > 600) {
      gWidth = 800;
    }
    if (gWidth <= 600 && gWidth > 480) {
      gWidth = 600;
    }
    if (gWidth <= 480 && gWidth > 320) {
      gWidth = 480;
    }
    if (gWidth <= 320) {
      gWidth = 320;
    }

    return [gWidth, gHeight];
  };

})(jQuery);

function stopOwlPropagation(element) {
  jQuery(element).on('to.owl.carousel', function(e) { e.stopPropagation(); });
  jQuery(element).on('next.owl.carousel', function(e) { e.stopPropagation(); });
  jQuery(element).on('prev.owl.carousel', function(e) { e.stopPropagation(); });
  jQuery(element).on('destroy.owl.carousel', function(e) { e.stopPropagation(); });
}