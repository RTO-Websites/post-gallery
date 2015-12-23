/************************************
 * Author: shennemann
 *
 * Last change: 10.11.2015 10:55
 ************************************/
var LiteboxGallery = function (args) {
  var win = window,
    doc = win.document,
    self = this,
    liteboxContainer = null,
    galleryContainer = null,
    defaultArgs = {
      galleryContainer: "#litebox-owlslider",
      liteboxContainer: "#litebox-gallery",
      owlArgs: {},
      owlThumbArgs: {},
      owlVersion: 2,
      debug: false,
    },

    thumbDefaultArgs = {
      lazyLoad: true,
      autoWidth: true,
      dots: false,

      // owl 1
      pagination: false,
    },
    thumbArgs = null,
    linkSelector = 'a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"], a[href*=".JPG"], a[href*=".GIF"], a[href*=".PNG"], a[href*=".JPEG"]',

  // internal/private functions
    debug,
    setEvents,
    init;


  init = function () {
    window.liteboxOpenProgress = false;

    if (!win.$) {
      win.$ = jQuery;
    }

    args = jQuery.extend(defaultArgs, args);
    thumbArgs = jQuery.extend(thumbDefaultArgs, args.owlThumbArgs);
    args.owlArgs.addClassActive = true;
    thumbArgs.addClassActive = true;

    liteboxContainer = jQuery(args.liteboxContainer);
    galleryContainer = jQuery(args.galleryContainer);

    // set onload events
    $(function () {
      setEvents();
    });
  };

  setEvents = function () {
    // Find links with jpg/gif/png
    $(doc).on('click', linkSelector, function (event) {
      $(linkSelector).addClass('no-ajax');
      if (!$(this).hasClass('no-litebox')) {
        event.preventDefault();
        self.openGallery(this);
      }
    });

    $(linkSelector).addClass('no-ajax');


    if (args.clickEvents) {
      /**
       * Gallery click
       */
      $(document).on('click', '.litebox-owlslider .owl-stage-outer, litebox-owlslider .owl-wrapper-outer', function (e) {
        var xPos,
          yPos,
          oldOwl = $('.litebox-gallery .owl-carousel').data('owlCarousel');

        yPos = e.pageY - window.scrollY;
        xPos = e.pageX;

        if (xPos > $(document).width() / 2) {
          // next
          if (oldOwl) {
            oldOwl.next();
          } else {
            $('.litebox-gallery .owl-next').trigger('click');
          }
        } else {
          // prev
          if (oldOwl) {
            oldOwl.prev();
          } else {
            $('.litebox-gallery .owl-prev').trigger('click');
          }
        }
      });
    }

    if (args.keyEvents) {
      /**
       * Gallery keypress
       */
      $(document).on('keyup', function (e) {
        var oldOwl = $('.litebox-gallery .owl-carousel').data('owlCarousel');
        /*
         * up: 38
         * down: 40
         * left: 37
         * right: 39
         */
        switch (e.keyCode) {
          case 37:
            // prev
            if (oldOwl) {
              // need double because it wont work single?!
              oldOwl.prev();
              oldOwl.prev();
            } else {
              $('.litebox-gallery .owl-prev').trigger('click');
            }
            break;
          case 39:
            // next
            if (oldOwl) {
              oldOwl.next();
            } else {
              $('.litebox-gallery .owl-next').trigger('click');
            }
            break;
          case 27:
            // ESC
            self.closeGallery();
            break;
        }
      });
    }
  };

  /**
   * Opens the gallery-litebox with an array of pics
   *
   * @param {array} pics
   * @returns {undefined}
   */
  this.openGalleryByPics = function (pics) {
    var thumbPics = [];

    debug('openByPics', pics);

    for (var i = 0; i < pics.length; i += 1) {
      thumbPics[i] = encodeURI(pics[i]).replace(websiteUrl, '');
    }

    // init gallery
    self.initGallery(thumbPics, 0);
  };

  /**
   * Opens the litebox and get images from parentparent of clickElement
   *
   * @param {type} clickElement
   * @returns {undefined}
   */
  this.openGallery = function (clickElement) {
    debug('openGallery', clickElement);

    if (window.liteboxOpenProgress) {
      return;
    }
    window.liteboxOpenProgress = true;

    // get image container
    clickElement = $(clickElement);
    var imageContainer = clickElement.closest('.gallery_container, .gallery-container');
    if (!imageContainer.length) {
      imageContainer = clickElement.closest('.gallery');
    }
    if (!imageContainer.length) {
      imageContainer = clickElement.parent().parent().parent();
    }
    if (!imageContainer.length) {
      imageContainer = clickElement.parent().parent();
    }
    if (!imageContainer.length) {
      imageContainer = clickElement.parent();
    }

    var pics = [];
    var count = 0;
    var startPic = 0;
    // search image-urls in hrefs
    var items = imageContainer.find(linkSelector).filter(':not(.no-litebox)');
    items.each(function (index) {
      if ($(this).attr('href').indexOf('/uploads/') !== -1
        || $(this).attr('href').indexOf('/gallery/') !== -1
        || $(this).hasClass('show-in-litebox')
        || $(this).attr('href').indexOf('bilder.ladies.de') !== -1
      ) {
        // set startImage
        if ($(this).attr('href') === clickElement.attr('href')) {
          startPic = count;
        }
        pics[count] = encodeURI($(this).attr('href')).replace(websiteUrl, '');
        count += 1;
      }
    });

    // init gallery
    self.initGallery(pics, startPic);
  };

  /**
   * init the gallery
   *
   * @param {type} pics
   * @param {type} startPic
   * @returns {undefined}
   */
  this.initGallery = function (pics, startPic) {
    debug('init-start', pics, startPic);
    // Trigger
    liteboxContainer.trigger('box-init', {
      state: 'begin',
      pics: pics,
      startPic: startPic
    });

    galleryContainer.stop(true);
    liteboxContainer.stop(true);

    galleryContainer.html('');
    liteboxContainer.find('.close-button').on('click touchend', self.closeGallery);

    // add some usefull classes
    liteboxContainer.removeClass('one-pic, under-five-pics, under-ten-pics, over-ten-pics');
    if (pics.length <= 1) {
      liteboxContainer.addClass('one-pic');
    }
    if (pics.length <= 5) {
      liteboxContainer.addClass('under-five-pics');
    }
    if (pics.length <= 10) {
      liteboxContainer.addClass('under-ten-pics');
    }
    if (pics.length > 10) {
      liteboxContainer.addClass('over-ten-pics');
    }

    liteboxContainer.data('pic-count', pics.length);

    // Thumbs
    self.initThumbs(pics);

    // Gallery
    self.createGallery(pics, startPic);

    jQuery('body').addClass('litebox-gallery-loading');


    // Trigger
    liteboxContainer.trigger('box-init', {
      state: 'complete',
      pics: pics,
      startPic: startPic
    });
  };

  this.createGallery = function (pics, galleryStartPic) {
    getFullsizeThumbs(pics, 'gallery-image', function (pics) {
      debug('images-loaded', pics);
      jQuery('body').removeClass('litebox-gallery-loading');
      jQuery('body').addClass('liteboxgallery-open');


      // destroy old gallery
      if (args.owlVersion == 1) {
        // owl v1
        if (galleryContainer.data('owlCarousel')) {
          galleryContainer.data('owlCarousel').destroy();
        }
      } else {
        // owl v2
        galleryContainer.trigger('destroy.owl.carousel');
      }

      // add pics to container
      for (var i = 0; i < pics.length; i += 1) {
        var thumb = null,
          width = 'auto',
          height = 'auto',
          orientation = ' wide';

        if (pics[i]['width'] > pics[i]['height']) {
          width = pics[i]['width'];
        } else if (typeof(pics[i]['height']) !== 'undefined') {
          height = pics[i]['height'];
          orientation = ' upright';
        }

        thumb = jQuery('<div class="litebox-image">' +
          '<img width="' + width + '" height="' + height + '" class="lazyload '
          + orientation + '" data-src="' + pics[i]['url'] + '" alt="" />' +
          '</div>');
        galleryContainer.append(thumb);
      }

      args.owlArgs.startPosition = galleryStartPic;
      args.owlArgs.loop = true;

      galleryContainer.owlCarousel(args.owlArgs);

      if (args.owlVersion == 1 && galleryStartPic) { // only needed for v1
        galleryContainer.data('owlCarousel').goTo(galleryStartPic);
      }

      // open popup
      liteboxContainer.addClass('open').css({'display': 'block'}).animate({'opacity': '1'}, 500);

      window.liteboxOpenProgress = false;
    });
  }

  this.initThumbs = function (pics) {
    // Thumbs
    if (liteboxContainer.find('.thumb-container').length &&
      $(window).width() > 720 && $(window).height() > 360
    ) {
      debug('load-thumbs');
      getThumbs(pics, 150, 150, function (pics) {
        var thumbSlider = liteboxContainer.find('.thumb-container');

        // destroy old gallery
        if (args.owlVersion == 1) {
          // owl v1
          if (thumbSlider.data('owlCarousel')) {
            thumbSlider.data('owlCarousel').destroy();
          }
        } else {
          // owl v2
          thumbSlider.trigger('destroy.owl.carousel');
        }
        thumbSlider.html('');
        thumbSlider.addClass('owl-carousel owl-theme');

        for (var i = 0; i < pics.length; i += 1) {
          var thumb = jQuery('<div class="litebox-thumb"><img src="' + pics[i]['url'] + '" alt="" /></div>');
          thumb[0].liteboxIndex = i;
          thumb.on('click', function () {
            if (args.owlVersion == 1) {
              galleryContainer.data('owlCarousel').goTo(this.liteboxIndex); // v1
            } else {
              galleryContainer.trigger('to.owl.carousel', this.liteboxIndex);
            }
          });
          thumbSlider.append(thumb);
        }

        jQuery('.thumb-container').owlCarousel(thumbArgs);
      }, 0);

      // TODO: highlight current thumb
    }
  };

  /**
   * Close the litebox
   *
   * @returns {undefined}
   */
  this.closeGallery = function () {
    debug('close-gallery');
    liteboxContainer.trigger('box-close', {state: 'begin'});

    liteboxContainer.removeClass('open').animate({'opacity': '0'}, 500, function () {
      debug('close-end');
      liteboxContainer.css({'display': 'none'});
      +

        // destroy gallery
        galleryContainer.trigger('destroy.owl.carousel');
    });

    jQuery('body').removeClass('liteboxgallery-open');
    jQuery('body').removeClass('liteboxgallery-loading');

    // Callback
    if (typeof(cb_closeGallery) === 'function') {
      cb_closeGallery();
    }
    // Trigger
    liteboxContainer.trigger('box-close', {state: 'complete'});
  };


  debug = function () {
    if (args.debug) {
      console.info('litebox', new Date().getTime(), arguments);
    }
  };

  init();
};