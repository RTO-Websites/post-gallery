/************************************
 * Author: shennemann
 *
 * Last change: 09.01.2018 09:58
 ************************************/
var LiteboxGallery = function (args) {
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

    linkSelector = 'a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"], a[href*=".JPG"], a[href*=".GIF"], a[href*=".PNG"], a[href*=".JPEG"]',

    // internal/private functions
    debug,
    setEvents,
    getUrlFromPics,
    init;


  init = function () {
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
    $(function () {
      setEvents();
    });

    $(doc).on('click', '[data-pgimages]', function(e) { self.openByData(e.currentTarget); } );


    liteboxContainer.find('.close-button').on('click touchend', function(e) {
      e.stopPropagation();
      e.preventDefault();
      self.closeGallery();
    });
  };

  setEvents = function () {
    // Find links with jpg/gif/png
    $(doc).on('click', linkSelector, function (event) {
      $(linkSelector).addClass('no-ajax');
      if (!$(this).hasClass('no-litebox')) {
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
      $(document).on('keyup', function (e) {
        /*
         * up: 38
         * down: 40
         * left: 37
         * right: 39
         */
        switch (e.keyCode) {
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
  self.openByData = function (element) {
    element = jQuery(element);
    var pics = element.data('pgimages');
    if (!pics.length) {
      pics = element.closest('.gallery').data('pgimages');
    }

    self.openGalleryByPics(pics, 0, element);
  };

  /**
   * Opens the gallery-litebox with an array of pics
   *
   * @param {array} pics
   * @returns {undefined}
   */
  self.openGalleryByPics = function (pics, startImage, clickElement) {
    var thumbPics = [];

    if (typeof(startImage) == 'undefined') {
      startImage = 0;
    }

    debug('openByPics', pics);

    if (typeof(pics) == 'string') {
      pics = pics.split(',');
    }

    pics = getUrlFromPics(pics);

    for (var i = 0; i < pics.length; i += 1) {
      thumbPics[i] = encodeURI(pics[i]).replace(window.pgConfig.websiteUrl, '');
    }

    // init gallery
    self.initGallery(thumbPics, startImage, clickElement);
  };

  /**
   * Opens the gallery-litebox with an object of pics
   *
   * @param {object} picsObject
   * @returns {undefined}
   */
  getUrlFromPics = function (pics) {
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
  self.openGallery = function (clickElement) {
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

    var pics = [],
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
          url: items[index]['url'],
          title: items[index]['title'],
          desc: items[index]['desc'],
        };
        pics[count] = encodeURI(items[index]['url']).replace(window.pgConfig.websiteUrl, '');
        count += 1;
      }
    } else {
      // search image-urls in hrefs
      items = imageContainer.find(linkSelector).filter(':not(.no-litebox)');
      items.each(function (index) {
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
          pics[count] = encodeURI(item.attr('href')).replace(window.pgConfig.websiteUrl, '');

          // set pic-data
          self.picsData[count] = {
            url: pics[count],
            title: item.data('title'),
            desc: item.data('desc'),
          };
          count += 1;
        }
      });
    }

    // init gallery
    self.initGallery(pics, startPic, clickElement);
  };

  /**
   * init the gallery
   *
   * @param {type} pics
   * @param {type} startPic
   * @returns {undefined}
   */
  self.initGallery = function (pics, startPic, clickElement) {
    debug('init-start', pics, startPic);
    // Trigger
    liteboxContainer.trigger('box-init', {
      state: 'begin',
      pics: pics,
      startPic: startPic,
      clickElement: clickElement,
    });

    if (!pics.length) {
      window.liteboxOpenProgress = false;
      return false;
    }

    galleryContainer.stop(true);
    liteboxContainer.stop(true);

    galleryContainer.html('');

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

    $('body').addClass('litebox-gallery-loading');

    // Trigger
    liteboxContainer.trigger('box-init', {
      state: 'complete',
      pics: pics,
      startPic: startPic,
      clickElement: clickElement,
    });
  };

  /**
   * Create html with images
   *
   * @param pics
   * @param galleryStartPic
   */
  self.createGallery = function (pics, galleryStartPic) {
    getFullsizeThumbs(pics, 'gallery-image', function (pics) {
      debug('images-loaded', pics);
      $('body').removeClass('litebox-gallery-loading');
      $('body').addClass('liteboxgallery-open');


      // destroy old gallery
      LiteboxGallery.sliders[args.sliderType].destroy(sliderArgs);

      // add pics to container
      for (var i = 0; i < pics.length; i += 1) {
        var pic = null,
          width = 'auto',
          height = 'auto',
          orientation = ' wide';

        if (pics[i]['width'] >= pics[i]['height']) {
          width = pics[i]['width'];
        } else if (typeof(pics[i]['height']) !== 'undefined') {
          height = pics[i]['height'];
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

        if (args.asBg) {
          // embed images as background
          pic = $('<div class="litebox-image owl-lazy" data-src="' + pics[i]['url']  + '">' +
            picTitleDesc +
            '</div>');
        } else {
          // embed images as <img>
          pic = $('<div class="litebox-image">' +
            '<img width="' + width + '" height="' + height + '" class="owl-lazy '
            + orientation + '" data-src="' + pics[i]['url'] + '" alt="" />' +
            picTitleDesc +
            '</div>');
        }
        galleryContainer.append(pic);
      }

      self.pics = null;

      // init slider
      LiteboxGallery.sliders[args.sliderType].init(sliderArgs, galleryStartPic);

      // open popup
      liteboxContainer.addClass('open'); //.css({'display': 'block'}).animate({'opacity': '1'}, 500);

      window.liteboxOpenProgress = false;
    });
  };


  /**
   * Load and create thumbnails via ajax
   * @param pics
   */
  self.initThumbs = function (pics) {
    // Thumbs
    if (liteboxContainer.find('.thumb-container').length &&
      $(window).width() > 720 && $(window).height() > 360 &&
      args.owlVersion !== 'noslider'
    ) {
      debug('load-thumbs');
      getThumbs(pics, 150, 150, function (pics) {
        LiteboxGallery.sliders[args.sliderType].initThumbs(sliderArgs, pics);
      }, 0);

      // TODO: highlight current thumb
    }
  };

  /**
   * Close the litebox
   *
   * @returns {undefined}
   */
  self.closeGallery = function () {
    debug('close-gallery');
    liteboxContainer.trigger('box-close', {state: 'begin'});

    liteboxContainer.removeClass('open');//.animate({'opacity': '0'}, 500, function () {
    setTimeout(function() {
      debug('close-end');
      //liteboxContainer.css({'display': 'none'});
      liteboxContainer.trigger('box-close', {state: 'afterAnimation'});

      // destroy gallery
      LiteboxGallery.sliders[args.sliderType].destroy(sliderArgs);
    }, 500);

    $('body').removeClass('liteboxgallery-open');
    $('body').removeClass('liteboxgallery-loading');

    // Callback
    if (typeof(cb_closeGallery) === 'function') {
      cb_closeGallery();
    }
    // Trigger
    liteboxContainer.trigger('box-close', {state: 'complete'});
  };


  debug = function (message) {
    if (args.debug) {
      console.info('litebox', message, new Date().getTime(), arguments);
    }
  };

  init();
};

LiteboxGallery.sliders = {};
