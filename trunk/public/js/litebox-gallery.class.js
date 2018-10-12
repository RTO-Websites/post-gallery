/************************************
 * Author: shennemann
 *
 * Last change: 12.10.2018 16:45
 ************************************/
var LiteboxGallery = function(args) {
  var win = window,
    doc = win.document,
    self = this,
    liteboxContainer = null,
    galleryContainer = null,
    sliderArgs = {},
    defaultArgs = {
      galleryContainer: "#litebox-owlslider",
      liteboxContainer: "#litebox-gallery",
      sliderArgs: {},
      owlThumbArgs: {},
      owlVersion: 2,
      sliderType: 'owl',
      debug: false,
    },

    linkSelector = 'a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"], a[href*=".JPG"], a[href*=".GIF"], a[href*=".PNG"], a[href*=".JPEG"],a.show-in-litebox',

    // internal/private functions
    debug,
    setEvents,
    getUrlFromPics,
    init;


  init = function() {
    self.picsData = [];

    win.liteboxOpenProgress = false;

    if (!win.$) {
      win.$ = jQuery;
    }

    args = jQuery.extend(defaultArgs, args);

    liteboxContainer = $(args.liteboxContainer);
    galleryContainer = $(args.galleryContainer);

    // args for slider
    sliderArgs = {
      liteboxContainer: liteboxContainer,
      galleryContainer: galleryContainer,
      args: args,
    };

    // set onload events
    $(function() {
      setEvents();
    });

    $(doc).on('click', '[data-pgimages]', function(e) {
      self.openByData(e.currentTarget);
    });


    liteboxContainer.find('.close-button').on('click touchend', function(e) {
      e.stopPropagation();
      e.preventDefault();
      self.closeGallery();
    });
  };

  setEvents = function() {
    // Find links with jpg/gif/png
    $(doc).on('click', linkSelector, function(event) {
      $(linkSelector).addClass('no-ajax');
      if (!$(this).hasClass('no-litebox') && !$(this).data('elementor-lightbox-slideshow')) {
        event.preventDefault();
        self.openGallery(event.currentTarget);
      }
    });

    $(linkSelector).addClass('no-ajax');


    if (args.clickEvents) {
      /**
       * Gallery click
       */
      LiteboxGallery.sliders[args.sliderType].galleryClick();
    }

    if (args.keyEvents) {
      /**
       * Gallery keypress
       */
      $(document).on('keyup', function(e) {
        /*
         * up: 38
         * down: 40
         * left: 37
         * right: 39
         */
        switch(e.keyCode) {
          case 37:
            // prev
            LiteboxGallery.sliders[args.sliderType].prev(sliderArgs);
            break;
          case 39:
            // next
            LiteboxGallery.sliders[args.sliderType].next(sliderArgs);
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
   * Opens the gallery-litebox by data-attribute (data-pgimages)
   *
   * @param e
   */
  self.openByData = function(element) {
    element = jQuery(element);
    var pics = element.data('pgimages');
    if (!pics.length) {
      pics = element.closest('.gallery').data('pgimages');
    }

    self.openGalleryByPics(pics, 0, element);
  };

  /**
   * Opens the gallery-litebox with an array or comma-seperated string of pics
   *
   * @param {array} pics
   * @returns {undefined}
   */
  self.openGalleryByPics = function(pics, startImage, clickElement) {
    if (typeof(startImage) == 'undefined') {
      startImage = 0;
    }

    debug('openByPics', pics);

    if (typeof(pics) == 'string') {
      pics = pics.split(',');
    }

    pics = getUrlFromPics(pics);

    for (var i = 0; i < pics.length; i += 1) {
      self.picsData[i] = {url: encodeURI(pics[i]).replace(window.pgConfig.websiteUrl, '')};
    }

    // init gallery
    self.initGallery(startImage, clickElement);
  };

  /**
   * Opens the gallery-litebox with an object of pics
   *
   * @param {object} picsObject
   * @returns {undefined}
   */
  getUrlFromPics = function(pics) {
    var newPics = [],
      i = 0;
    self.picsData = [];

    for (var index in pics) {
      if (typeof(pics[index]['url']) !== 'undefined') {
        newPics[i] = pics[index]['url'];
      } else {
        newPics[i] = pics[index];
      }
      self.picsData[i] = {
        url: newPics[i],
        title: pics[index]['title'],
        desc: pics[index]['desc'],
      };

      i += 1;
    }

    return newPics;
  };

  /**
   * Opens the litebox and get images from parentparent of clickElement
   *
   * @param {type} clickElement
   * @returns {undefined}
   */
  self.openGallery = function(clickElement) {
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

    debug('openGallery imageContainer', imageContainer);

    var //pics = [],
      count = 0,
      startPic = 0,
      items = [];

    self.picsData = [];

    if (typeof(imageContainer.data('pg-gallery')) !== 'undefined') {
      // get urls from container-data
      items = imageContainer.data('pg-gallery');
      for (var index in items) {
        if (items[index]['path'] === clickElement.attr('href')) {
          startPic = count;
        }
        self.picsData[count] = {
          //url: items[index]['url'],
          url: encodeURI(items[index]['url']).replace(window.pgConfig.websiteUrl, ''),
          title: items[index]['title'],
          desc: items[index]['desc'],
          embed: typeof(items[index]['embed']) !== 'undefined' ? items[index]['embed'] : '',
          thumb: typeof(items[index]['thumb']) !== 'undefined' ? items[index]['thumb'] : items[index]['url'],
        };
        //pics[count] = encodeURI(items[index]['url']).replace(window.pgConfig.websiteUrl, '');
        count += 1;
      }
    } else {
      // search image-urls in hrefs
      items = imageContainer.find(linkSelector).filter(':not(.no-litebox)');
      items.each(function(index) {
        var item = $(this);
        if (item.attr('href').indexOf('/uploads/') !== -1
          || item.attr('href').indexOf('/gallery/') !== -1
          || item.attr('href').indexOf('/bilder/galerie/') !== -1
          || item.hasClass('show-in-litebox')
          || item.attr('href').indexOf('bilder1.ladies.de') !== -1
        ) {
          // set startImage
          if (item.attr('href') === clickElement.attr('href')) {
            startPic = count;
          }
          //pics[count] = encodeURI(item.attr('href')).replace(window.pgConfig.websiteUrl, '');

          // set pic-data
          self.picsData[count] = {
            //url: pics[count],
            url: encodeURI(item.attr('href')).replace(window.pgConfig.websiteUrl, ''),
            title: item.data('title'),
            desc: item.data('desc'),
            embed: item.data('embed'),
            thumb: item.data('thumb'),
          };
          count += 1;
        }
      });
    }

    // init gallery
    self.initGallery(startPic, clickElement);
  };

  /**
   * init the gallery
   *
   * @param {type} startPic
   * @returns {undefined}
   */
  self.initGallery = function(startPic, clickElement) {
    debug('init-start', self.picsData, startPic);
    // Trigger
    liteboxContainer.trigger('box-init', {
      state: 'begin',
      pics: self.picsData,
      startPic: startPic,
      clickElement: clickElement,
    });

    if (!self.picsData.length) {
      window.liteboxOpenProgress = false;
      return false;
    }

    galleryContainer.stop(true);
    liteboxContainer.stop(true);

    galleryContainer.html('');

    // add some usefull classes
    liteboxContainer.removeClass('one-pic, under-five-pics, under-ten-pics, over-ten-pics');
    if (self.picsData.length <= 1) {
      liteboxContainer.addClass('one-pic');
    }
    if (self.picsData.length <= 5) {
      liteboxContainer.addClass('under-five-pics');
    }
    if (self.picsData.length <= 10) {
      liteboxContainer.addClass('under-ten-pics');
    }
    if (self.picsData.length > 10) {
      liteboxContainer.addClass('over-ten-pics');
    }

    liteboxContainer.data('pic-count', self.picsData.length);

    // Thumbs
    self.initThumbs();

    // Gallery
    self.createGallery(startPic);

    $('body').addClass('litebox-gallery-loading');

    // Trigger
    liteboxContainer.trigger('box-init', {
      state: 'complete',
      pics: self.picsData,
      startPic: startPic,
      clickElement: clickElement,
    });
  };

  /**
   * Create html with images
   *
   * @param galleryStartPic
   */
  self.createGallery = function(galleryStartPic) {
    getFullsizeThumbs(self.picsData, 'gallery-image', function() {
      debug('images-loaded', self.picsData);
      $('body').removeClass('litebox-gallery-loading');
      $('body').addClass('liteboxgallery-open');

      // destroy old gallery
      LiteboxGallery.sliders[args.sliderType].destroy(sliderArgs);

      // add pics to container
      for (var i = 0; i < self.picsData.length; i += 1) {
        var item = null,
          width = 'auto',
          height = 'auto',
          orientation = ' wide';

        if (self.picsData[i]['width'] >= self.picsData[i]['height']) {
          width = self.picsData[i]['width'];
        } else if (typeof(self.picsData[i]['height']) !== 'undefined') {
          height = self.picsData[i]['height'];
          orientation = ' upright';
        }

        // add pic title and desc
        picTitleDesc = '';
        if (typeof(self.picsData[i]['title']) !== 'undefined') {
          picTitleDesc += '<div class="pic-title">' + self.picsData[i]['title'] + '</div>';
        }
        if (typeof(self.picsData[i]['desc']) !== 'undefined') {
          picTitleDesc += '<div class="pic-desc">' + self.picsData[i]['desc'] + '</div>';
        }

        // add wrapper around desc and title
        if (picTitleDesc.length) {
          picTitleDesc = '<div class="pic-info">' + picTitleDesc + '</div>';
        }

        switch(self.picsData[i].embed) {
          case 'iframe':
            item = $('<div class="litebox-image litebox-iframe-wrapper">' +
              '<iframe class="litebox-iframe" src="' + self.picsData[i]['url'] + '"></iframe>' +
              '</div>');

            break;

          default:
            // normal image
            if (args.asBg) {
              // embed images as background
              item = $('<div class="litebox-image owl-lazy" data-src="' + self.picsData[i]['url'] + '">' +
                picTitleDesc +
                '</div>');
            } else {
              // embed images as <img>
              item = $('<div class="litebox-image">' +
                '<img width="' + width + '" height="' + height + '" class="owl-lazy '
                + orientation + '" data-src="' + self.picsData[i]['url'] + '" alt="" />' +
                picTitleDesc +
                '</div>');
            }

            break;
        }
        galleryContainer.append(item);
      }

      //self.pics = null;

      // init slider
      LiteboxGallery.sliders[args.sliderType].init(sliderArgs, galleryStartPic);

      // open popup
      liteboxContainer.addClass('open');

      window.liteboxOpenProgress = false;
    });
  };


  /**
   * Load and create thumbnails via ajax
   */
  self.initThumbs = function() {
    // Thumbs
    if (liteboxContainer.find('.thumb-container').length &&
      $(window).width() > 720 && $(window).height() > 360 &&
      args.owlVersion !== 'noslider'
    ) {
      debug('load-thumbs');

      getThumbs(self.picsData, 150, 150, function() {
        LiteboxGallery.sliders[args.sliderType].initThumbs(sliderArgs, self.picsData);
      }, 0);

      // TODO: highlight current thumb
    }
  };

  /**
   * Close the litebox
   *
   * @returns {undefined}
   */
  self.closeGallery = function() {
    debug('close-gallery');
    liteboxContainer.trigger('box-close', {state: 'begin'});

    liteboxContainer.on('transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd', function(e) {
      debug('close-end');
      //liteboxContainer.css({'display': 'none'});
      liteboxContainer.trigger('box-close', {state: 'afterAnimation'});

      // destroy gallery
      LiteboxGallery.sliders[args.sliderType].destroy(sliderArgs);

      $(this).off(e);
    });

    liteboxContainer.removeClass('open');

    $('body').removeClass('liteboxgallery-open');
    $('body').removeClass('liteboxgallery-loading');

    // Callback
    if (typeof(cb_closeGallery) === 'function') {
      cb_closeGallery();
    }
    // Trigger
    liteboxContainer.trigger('box-close', {state: 'complete'});
  };


  debug = function(message) {
    if (args.debug) {
      console.info('litebox', message, new Date().getTime(), arguments);
    }
  };

  init();
};

LiteboxGallery.sliders = {};
