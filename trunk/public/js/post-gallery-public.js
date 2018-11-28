(function ($) {
  'use strict';

  /**
   * DOM-Ready
   */
  $(function () {
    window.litebox = new LiteboxGallery(window.pgConfig.liteboxArgs);
    // init masonry
    window.pgInitMasonry();
  });

  $(window).on('resize', function () {
    clearTimeout(window.pgRefreshTimeout);
    window.pgRefreshTimeout = setTimeout(function () {
      $('.postgallery-slider').each(function (index, element) {
        $(element).trigger('refresh.owl.carousel');
      });
    }, 100);
  });

  window.pgInitMasonry = function () {
    if (!jQuery.fn.masonry) {
      return;
    }
    $('.pg-theme-thumbs[data-pgmasonry]').each(function (index, element) {
      if (element.postgalleryMasonry) {
        $(element).masonry('destroy');
      }

      if (!$(element).data('pgmasonry')) {
        return;
      }

      element.postgalleryMasonry = $(element).masonry({
        // set itemSelector so .grid-sizer is not used in layout
        itemSelector: '.gallery-item',
        // use element for option
        columnWidth: '.gallery-item',
        percentPosition: true,
        horizontalOrder: $(element).data('pgmasonry') == 'horizontal',
      });

      element.postgalleryMasonry.imagesLoaded().progress(function () {
        element.postgalleryMasonry.masonry('layout');
      });
    });
  };

  window.getFullsizeThumbs = function (pics, owlSliderId, callback) {
    var sizes = pgCheckImageSize();

    jQuery.ajax({
      'type': 'get',
      'url': window.pgConfig.websiteUrl + '/?getThumbList',
      'data': {'pics': pics, 'width': sizes[0], 'height': sizes[1]},
      'success': function (data, textStatus) {
        window.litebox.picsData = jQuery.parseJSON(data);
        if (typeof(callback) === 'function') {
          callback();
        }
      },
      'error': function (jqXHR, textStatus, errorThrown) {
        console.log('pg load fail', jqXHR, textStatus, errorThrown);
        if (typeof(callback) === 'function') {
          callback();
        }
      }
    });
  };

  window.getThumbs = function (pics, width, height, callback, scale) {
    if (typeof(scale) === 'undefined') {
      scale = 0;
    }
    jQuery.ajax({
      'type': 'get',
      'url': window.pgConfig.websiteUrl + '/?getThumbList',
      'data': {'pics': pics, 'width': width, 'height': height, scale: scale},
      'success': function (data, textStatus) {
        window.litebox.picsData = jQuery.parseJSON(data);
        if (typeof(callback) === 'function') {
          callback();
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


  /**
   * Register an new imageAnimation
   *
   * @param id
   * @param timeBetween
   */
  window.registerPgImageAnimation = function (id, timeBetween) {
    if (typeof(window.pgImageAnimations) === 'undefined') {
      window.pgImageAnimations = {};
    }
    window.pgImageAnimations[id] = timeBetween;
  };


  /**
   * Start imageAnimation
   */
  window.startPgImageAnimation = function () {
    if (typeof(window.pgImageAnimations) === 'undefined' || !Object.keys(window.pgImageAnimations).length) {
      return;
    }

    // loop all container
    for (var id in window.pgImageAnimations) {
      if (jQuery('#' + id).isVisible()) {
        var items = jQuery('#' + id + ' .gallery-item'),
          timeBetween = window.pgImageAnimations[id];

        items.each(function (index, element) {
          // loop items
          setTimeout(function () {
            jQuery(element).addClass('show');
          }, timeBetween * index);
        });

        delete(window.pgImageAnimations[id]);
      }
    }
  };

  /**
   * Set pg image animation on scroll and load event
   */
  jQuery(function () {
    if (typeof(window.pgImageAnimations) === 'undefined' || !Object.keys(window.pgImageAnimations).length) {
      return;
    }

    window.startPgImageAnimation();

    jQuery(document).on('scroll', function () {
      clearTimeout(window.pgImageAnimationTimeout);

      window.pgImageAnimationTimeout = setTimeout(function () {
        window.startPgImageAnimation();
      }, 200);
    });
  });

})(jQuery);

function stopOwlPropagation(element) {
  jQuery(element).on('to.owl.carousel', function (e) {
    e.stopPropagation();
  });
  jQuery(element).on('next.owl.carousel', function (e) {
    e.stopPropagation();
  });
  jQuery(element).on('prev.owl.carousel', function (e) {
    e.stopPropagation();
  });
  jQuery(element).on('destroy.owl.carousel', function (e) {
    e.stopPropagation();
  });
}

jQuery.fn.isVisible = function() {
  // Am I visible?
  // Height and Width are not explicitly necessary in visibility detection, the bottom, right, top and left are the
  // essential checks. If an image is 0x0, it is technically not visible, so it should not be marked as such.
  // That is why either width or height have to be > 0.
  var rect = this[0].getBoundingClientRect();
  return (
    (rect.height > 0 || rect.width > 0) &&
    rect.bottom >= 0 &&
    rect.right >= 0 &&
    rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.left <= (window.innerWidth || document.documentElement.clientWidth)
  );
};

