(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */

	/**
	 * DOM-Ready
	 */
	$(function() {
		new LiteboxGallery({owlConfig: liteboxOwlConfig});
	});



	window.getFullsizeThumbs = function(pics, swapperId, callback) {
		var sizes = postGalleryCheckImageSize();
		//sizes[0] =sizes[0] /2;
		//sizes[1] = sizes[0] / 16 *9;

		jQuery.ajax({
			'type' : 'POST',
			'url' : websiteUrl + '/?get_fullsize_thumbs',
			'data' : { 'pics' : pics, 'width' : sizes[0], 'height' : sizes[1] },
			'success' : function(data, textStatus) {
				if (typeof(callback) === 'function') {
					callback(jQuery.parseJSON(data));
				}
			}
		});
	};

	window.getThumbs = function(pics, width, height, callback) {
		jQuery.ajax({
			'type' : 'POST',
			'url' : websiteUrl + '/?get_fullsize_thumbs',
			'data' : { 'pics' : pics, 'width' : width, 'height' : height },
			'success' : function(data, textStatus) {
				if (typeof(callback) === 'function') {
					callback(jQuery.parseJSON(data));
				}
			}
		});
	};


	window.postGalleryCheckImageSize = function() {
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

})( jQuery );
