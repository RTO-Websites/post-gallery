(function ($) {
  'use strict';

  /**
   * DOM-Ready
   */
  $(function () {
    window.litebox = new LiteboxGallery(window.pgConfig.liteboxArgs);
    // init masonry
    window.pgInitMasonry();
    window.pgImageAnimationTimeout = setTimeout(function () {
      window.startPgImageAnimation();
    }, 200);
  });

  $(window).on('resize', function () {
    clearTimeout(window.pgRefreshTimeout);
    window.pgRefreshTimeout = setTimeout(function () {
      $('.postgallery-slider').each(function (index, element) {
        $(element).trigger('refresh.owl.carousel');
      });
    }, 100);
  });


  // restart image animation on widget change
  $(window).on('elementor/frontend/init', function () {
    elementorFrontend.hooks.addAction('frontend/element_ready/postgallery.default', function () {
      window.startPgImageAnimation();
      window.pgInitMasonry();
    });
  });

  // init js masonry
  window.pgInitMasonry = function () {
    if (!$.fn.masonry) {
      return;
    }
    $('.postgallery-wrapper.with-js-masonry .gallery').each(function (index, element) {
      if (element.postgalleryMasonry) {
        $(element).masonry('destroy');
      }

      element.postgalleryMasonry = $(element).masonry({
        // set itemSelector so .grid-sizer is not used in layout
        itemSelector: '.item',
        // use element for option
        columnWidth: '.item',
        percentPosition: true,
        horizontalOrder: $(element).parent().hasClass('js-masonry-horizontal'),
      });

      element.postgalleryMasonry.imagesLoaded().progress(function () {
        element.postgalleryMasonry.masonry('layout');
      });
    });
  };

  window.getFullsizeThumbs = function (pics, owlSliderId, callback) {
    var sizes = pgCheckImageSize();

    $.ajax({
      'type': 'get',
      'url': window.pgConfig.websiteUrl + '/?getThumbList',
      'data': {'pics': pics, 'width': sizes[0], 'height': sizes[1]},
      'success': function (data, textStatus) {
        try {
          window.litebox.picsData = $.parseJSON(data);
        } catch (e) {
          window.litebox.picsData = pics;
        }
        if (typeof (callback) === 'function') {
          callback();
        }
      },
      'error': function (jqXHR, textStatus, errorThrown) {
        console.log('pg load fail', jqXHR, textStatus, errorThrown);
        if (typeof (callback) === 'function') {
          callback();
        }
      }
    });
  };

  window.getThumbs = function (pics, width, height, callback, scale) {
    if (typeof (scale) === 'undefined') {
      scale = 0;
    }
    $.ajax({
      'type': 'get',
      'url': window.pgConfig.websiteUrl + '/?getThumbList',
      'data': {'pics': pics, 'width': width, 'height': height, scale: scale},
      'success': function (data, textStatus) {
        try {
          window.litebox.picsData = $.parseJSON(data);
        } catch (e) {
          window.litebox.picsData = pics;
        }
        if (typeof (callback) === 'function') {
          callback();
        }
      }
    });
  };

  window.pgCheckImageSize = function () {
    var gWidth = $(window).width(),
      gHeight = $(window).height(),
      sizes = [
        [1920, 1600],
        [1600, 1280],
        [1280, 1080],
        [1080, 800],
        [800, 600],
        [600, 480],
        [480, 320]
      ];

    if (gHeight == 0) {
      gHeight = 1080;
      gWidth = 1920;
    }

    if (gWidth > 1920) {
      gWidth = 2560;
    }
    if (gHeight > 1920) {
      gHeight = 2560;
    }

    for (var i in sizes) {
      if (gHeight <= sizes[i][0] && gHeight > sizes[i][1]) {
        gHeight = sizes[i][0];
      }
      if (gWidth <= sizes[i][0] && gWidth > sizes[i][1]) {
        gWidth = sizes[i][0];
      }
    }

    if (gHeight <= 320) {
      gHeight = 320;
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
    if (typeof (window.pgImageAnimations) === 'undefined') {
      window.pgImageAnimations = {};
    }
    window.pgImageAnimations[id] = timeBetween;
  };


  /**
   * Start imageAnimation
   */
  window.startPgImageAnimation = function () {
    if (typeof (window.pgImageAnimations) === 'undefined' || !Object.keys(window.pgImageAnimations).length) {
      return;
    }

    // loop all container
    for (var id in window.pgImageAnimations) {
      var initialDelay = $('#' + id).data('animationdelay');
      if (isNaN(initialDelay)) {
        initialDelay = 0;
      }
      if ($('#' + id).isVisible()) {
        var items = $('#' + id + ' .item'),
          timeBetween = window.pgImageAnimations[id];

        items.each(function (index, element) {
          element = $(element);
          // loop items
          setTimeout(function () {
            element.addClass('show');
          }, timeBetween * element.index() + initialDelay);
        });

        delete (window.pgImageAnimations[id]);
      }
    }
  };

  /**
   * Set active-class to current image from media-carousel
   *
   * @param id
   */
  window.setActiveSlide = function(id) {
    var items = $('#' + id + ' .item'),
      currentItem = $('#' + id + ' .item:nth-child(' + ($('#' + id)[0].connectedSwiper.realIndex + 1) + ')');

    items.removeClass('current');
    currentItem.addClass('current');
  };

  /**
   * Set pg image animation on scroll and load event
   */
  $(document).on('scroll', function () {
    clearTimeout(window.pgImageAnimationTimeout);

    window.pgImageAnimationTimeout = setTimeout(function () {
      window.startPgImageAnimation();
    }, 200);
  });

  //window.startPgImageAnimation();
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

jQuery.fn.isVisible = function (offsetTop, offsetLeft, offsetBottom, offsetRight) {
  // Am I visible?
  // Height and Width are not explicitly necessary in visibility detection, the bottom, right, top and left are the
  // essential checks. If an image is 0x0, it is technically not visible, so it should not be marked as such.
  // That is why either width or height have to be > 0.
  if (typeof (this) === 'undefined' || typeof (this[0]) === 'undefined') {
    return false;
  }

  if (typeof (offsetTop) === 'undefined') {
    offsetTop = 0;
  }
  if (typeof (offsetLeft) === 'undefined') {
    offsetLeft = 0;
  }
  if (typeof (offsetBottom) === 'undefined') {
    offsetBottom = 0;
  }
  if (typeof (offsetRight) === 'undefined') {
    offsetRight = 0;
  }

  var rect = this[0].getBoundingClientRect();
  return (
    (rect.height > 0 || rect.width > 0) &&
    rect.bottom + offsetBottom >= 0 &&
    rect.right + offsetRight >= 0 &&
    rect.top + offsetTop <= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.left + offsetLeft <= (window.innerWidth || document.documentElement.clientWidth)
  );
};

