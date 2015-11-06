/************************************
* Author: shennemann
*
* Last change: 13.10.2015 09:44
************************************/
var LiteboxGallery = function(args) {
	var win = window,
		doc = win.document,
		self = this,
		galleryInstance = null,
		liteboxContainer = null,
		config = {
			galleryContainer : "#litebox-owlslider",
			liteboxContainer: "#litebox-gallery",
			owlConfig: {},
			owlThumbConfig: {},
			debug: false
		},
		startCoords = null,
		linkSelector = 'a[href*=".jpg"], a[href*=".jpeg"], a[href*=".png"], a[href*=".gif"], a[href*=".JPG"], a[href*=".GIF"], a[href*=".PNG"], a[href*=".JPEG"]',

		// internal/private functions
		debug,
		setEvents,
		init;


	init = function() {
		if ( !win.$ ) {
			win.$ = jQuery;
		}
		if (typeof(newConfig) !== "undefined") {
			self.setConfig(newConfig);
			newConfig = null; // if not set twice
		}


		config = jQuery.extend( config, args );

		liteboxContainer = jQuery(config.liteboxContainer);
		galleryContainer = jQuery(config.galleryContainer);

		// set onload events
		$(function () {
			setEvents();
		});
	};

	setEvents = function() {
		// Find links with jpg/gif/png
		$(doc).on('click', linkSelector, function(event) {
			$(linkSelector).addClass('no-ajax');
			if (! $(this).hasClass('no-litebox')) {
				event.preventDefault();
				self.openGallery(this);
			}
		});

		$(linkSelector).addClass('no-ajax');
	};

	/**
	 * Opens the gallery-litebox with an array of pics
	 *
	 * @param {array} pics
	 * @returns {undefined}
	 */
	this.openGalleryByPics = function(pics) {
		var thumbPics = [];

		debug('openByPics', pics);

		for (var i=0; i < pics.length; i+=1) {
			thumbPics[i] = encodeURI(pics[i]).replace(websiteUrl,'');
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
	this.openGallery = function(clickElement) {
		debug('openGallery', clickElement);

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
		var items = imageContainer.find(linkSelector ).filter(':not(.no-litebox)');
		items.each(function(index) {
			if ($(this).attr('href').indexOf('/uploads/') !== -1
				|| $(this).attr('href').indexOf('/gallery/') !== -1
				|| $(this).hasClass('show-in-litebox')
				|| $(this).attr('href').indexOf('bilder.ladies.de') !== -1
			) {
				// set startImage
				if ($(this).attr('href') === clickElement.attr('href')) {
					startPic = count;
				}
				pics[count] = encodeURI($(this).attr('href')).replace(websiteUrl,'');
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
	this.initGallery = function(pics, startPic) {
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

		var galleryStartPic = startPic;

		// TODO embed thumbs ins litebox-theme
		// Thumbs
		if ( liteboxContainer.find('.thumb-slider').length ) {
			debug('load-thumbs');
			getThumbs(pics, 100, 100, function(pics) {
				var thumbSlider = liteboxContainer.find('.thumb-slider');
				thumbSlider.html('');
				for (var i = 0; i < pics.length; i+=1 ) {
					var thumb = jQuery('<div class="litebox-thumb"><img class="lazyload" data-src="' + pics[i] + '" alt="" /></div>');
					thumb[0].liteboxIndex = i;
					thumb.on('click', function() {
						galleryContainer.trigger('to.owl.carousel', this.liteboxIndex);
					});
					thumbSlider.append(thumb);
				}
				owlThumbConfig.lazyLoad = true;
				jQuery('.thumb-container').owlCarousel(owlThumbConfig);
			});

			// TODO: highlight current thumb
		}

		// Gallery
		getFullsizeThumbs(pics, 'gallery-image', function(pics) {
			debug('images-loaded', pics);
			jQuery('body').removeClass('litebox-gallery-loading');
			jQuery('body').addClass('liteboxgallery-open');


			// destroy old gallery
			galleryContainer.trigger('destroy.owl.carousel');

			// add pics to container
			for (var i = 0; i < pics.length; i+=1 ) {
				var thumb = jQuery('<div class="litebox-image"><img class="lazyload" data-src="' + pics[i] + '" alt="" /></div>');
				galleryContainer.append(thumb);
			}

			config.owlConfig.startPosition = galleryStartPic;
			config.owlConfig.loop = true;

			galleryContainer.owlCarousel(config.owlConfig);

			// open popup
			liteboxContainer.addClass('open').css({'display': 'block'}).animate({'opacity': '1'}, 500);
		});

		jQuery('body').addClass('litebox-gallery-loading');


		// Trigger
		liteboxContainer.trigger('box-init', {
			state: 'complete',
			pics: pics,
			startPic: startPic
		});
	};

	/**
	 * Close the litebox
	 *
	 * @returns {undefined}
	 */
	this.closeGallery = function() {
		debug('close-gallery');
		liteboxContainer.trigger('box-close', { state: 'begin' });

		liteboxContainer.removeClass('open').animate({'opacity':'0'}, 500, function() {
			debug('close-end');
			liteboxContainer.css({'display': 'none'});+

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
		liteboxContainer.trigger('box-close', { state: 'complete' });
	};


	debug = function() {
		if ( config.debug ) {
			console.info( 'litebox', new Date().getTime(), arguments );
		}
	};

	init();
};