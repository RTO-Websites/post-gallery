/**
 * All of the code for your admin-specific JavaScript source
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
jQuery(function () {
  initPostGallery();

  if (typeof (elementor) !== 'undefined') {
    // init postgallery on elementor widget open
    elementor.hooks.addAction('panel/open_editor/widget/postgallery', function (panel, model, view) {
      initPostGallery();
      loadUpload();
      checkMasonry();
      checkThumbsize();
    });
  }
});


/**
 * Onload
 */
jQuery(window).on('load', function () {
  initCustomizer();
});


/**
 * Add actions to postgallery-fields, adds sortable, init upload
 */
window.initPostGallery = function () {
  if (!$) {
    var $ = jQuery;
  }

  /**
   * Set owl-config via presets
   */
  $(document).on('change', '.owl-slider-presets', function (e) {
    var selectBox = jQuery(e.target),
      owlContainerElement = selectBox.closest('.customize-control').find('textarea');

    if (!owlContainerElement.length) {
      owlContainerElement = selectBox.parent().find('textarea');
    }

    switch (selectBox.val()) {
      case 'fade':
        owlContainerElement.val("items: 1, \nanimateOut: 'fadeOut',\nanimateIn: 'fadeIn',");
        break;
      case 'slidevertical':
        owlContainerElement.val("items: 1, \nanimateOut: 'slideOutDown',\nanimateIn: 'slideInDown',");
        break;
      case 'zoominout':
        owlContainerElement.val("items: 1, \nanimateOut: 'zoomOut',\nanimateIn: 'zoomIn',");
        break;
      case '':
        owlContainerElement.val('items: 1, ');
        break;

    }
  });


  initPostGalleryElementor();

  // make pics sortable
  initSortable();

  initUpload();
  initElementorAddButton();
};

/**
 * Add actions to customizer fields
 */
window.initCustomizer = function () {
  // show/hide children of equal-height
  $('#customize-control-postgallery_equalHeight-control').on('change', function (e) {
    var target = $(this),
      children = [
        '#customize-control-postgallery_itemRatio-control'
      ];

    if (target.find('input').is(':checked')) {
      $(children.join(',')).css({display: 'list-item'});
    } else {
      $(children.join(',')).css({display: 'none'});
    }
  });
  $('#customize-control-postgallery_equalHeight-control').trigger('change');

  // show/hide children of no grid
  $('#customize-control-postgallery_noGrid-control').on('change', function (e) {
    var target = $(this),
      children = [
        '#customize-control-postgallery_columns-control'
      ];

    if (target.find('input').is(':checked')) {
      $(children.join(',')).css({display: 'none'});
    } else {
      $(children.join(',')).css({display: 'list-item'});
    }
  });
  $('#customize-control-postgallery_noGrid-control').trigger('change');

  // show/hide children of image-animation
  $('#customize-control-postgallery_imageAnimation-control').on('change', function (e) {
    var target = $(this),
      children = [
        '#customize-control-postgallery_imageAnimationDuration-control',
        '#customize-control-postgallery_imageAnimationTimeBetween-control',
        '#customize-control-postgallery_imageAnimationCss-control',
        '#customize-control-postgallery_imageAnimationCssAnimated-control'
      ];

    if (target.find('input').is(':checked')) {
      $(children.join(',')).css({display: 'list-item'});
    } else {
      $(children.join(',')).css({display: 'none'});
    }
  });
  $('#customize-control-postgallery_imageAnimation-control').trigger('change');

  // show/hide children of useSrcset
  $('#customize-control-postgallery_useSrcset-control').on('change', function (e) {
    var target = $(this);

    if (target.find('input').is(':checked')) {
      $('#customize-control-postgallery_thumbWidth-control').css({display: 'none'});
      $('#customize-control-postgallery_thumbHeight-control').css({display: 'none'});
      $('#customize-control-postgallery_thumbScale-control').css({display: 'none'});
      $('#customize-control-postgallery_equalHeight-control').css({display: 'none'});
      $('#customize-control-postgallery_itemRatio-control').css({display: 'none'});
      $('#customize-control-postgallery_imageViewportWidth-control').css({display: 'list-item'});
    } else {
      $('#customize-control-postgallery_thumbWidth-control').css({display: 'list-item'});
      $('#customize-control-postgallery_thumbHeight-control').css({display: 'list-item'});
      $('#customize-control-postgallery_thumbScale-control').css({display: 'list-item'});
      $('#customize-control-postgallery_equalHeight-control').css({display: 'list-item'});
      $('#customize-control-postgallery_itemRatio-control').css({display: 'list-item'});
      $('#customize-control-postgallery_imageViewportWidth-control').css({display: 'none'});
    }
  });
  $('#customize-control-postgallery_useSrcset-control').trigger('change');

  // add element to show range-input value
  $('#customize-control-postgallery_itemRatio-control').on('input', function (e) {
    var target = $(this),
      input = target.find('input'),
      rangeValueElement = target.find('.range-value');

    if (!rangeValueElement.length) {
      rangeValueElement = $('<div class="range-value" />');
      target.append(rangeValueElement);
    }

    rangeValueElement.html(input.val());
  });
  $('#customize-control-postgallery_itemRatio-control').trigger('input');
};

/**
 * Init sortable images
 */
window.initSortable = function () {
  if (!$) {
    var $ = jQuery;
  }
  if ($.fn.sortable) {
    $(".sortable-pics").sortable({
      cursor: "move"
    });
    $(".sortable-pics").on("sortupdate", function (event, ui) {
      console.info('sortupdate');
      pgCloseDetails();
      var input = jQuery("#postgalleryImagesort"),
        elementorInput = jQuery('input[data-setting="pgsort"]'),
        value = [],
        count = 0;

      $(".sortable-pics > li > img").each(function (index, element) {
        console.info('sortupdate img', jQuery(element).data("src"));
        value[count] = jQuery(element).data("src");
        count += 1;
      });
      input.val(value.join(","));

      // change elementor control
      if (elementorInput.length) {
        elementorInput.val(value.join(","));
        elementorInput.trigger('input'); // triggers update, so it can be saved
        //elementor.reloadPreview();
      }
    });
  }
};

/**
 * Delete multiple images by path
 *
 * @param path
 */
window.deleteImages = function (path) {
  var answer = confirm(postgalleryLang.askDeleteAll);
  pgCloseDetails();

  // Check if user confirmed the deletion of all images
  if (answer) {
    jQuery.post(ajaxurl + "?action=postgalleryDeleteimage&path=" + path,
      function (data) {
        jQuery(".sortable-pics").remove();
      }
    );
  }
};

/**
 * Delete an image by path
 *
 * @param element
 * @param path
 */
window.deleteImage = function (element, path) {
  pgCloseDetails();
  jQuery.post(ajaxurl + "?action=postgalleryDeleteimage&path=" + path,
    function (data, textStatus) {
      deleteImageComplete(data, textStatus, element);
    }
  );
};

/**
 * Deleting finished
 *
 * @param result
 * @param status
 * @param element
 */
window.deleteImageComplete = function (result, status, element) {
  if (result == 1) {
    jQuery(element.remove());
  }
};

/**
 * Open detail-modal
 *
 * @param buttonElement
 */
window.pgToggleDetails = function (buttonElement) {
  var detailElement = jQuery(buttonElement).parent().find('.details'),
    allDetailElements = jQuery('.sortable-pics .details');

  if (detailElement.hasClass('active')) {
    allDetailElements.removeClass('active');
  } else {
    allDetailElements.removeClass('active');
    detailElement.addClass('active');
  }
};

/**
 * Close detail-modal
 */
window.pgCloseDetails = function () {
  var allDetailElements = jQuery('.sortable-pics .details');
  allDetailElements.removeClass('active');
};

window.triggerFilenameChange = function (inputElement) {
  inputElement = $(inputElement);
  var item = inputElement.closest('li'),
    img = item.find('img'),
    titleDiv = item.find('.img-title'),
    attachmentId = img.data('attachmentid');

  titleDiv.addClass('changed');
};

window.renameImage = function (buttonElement) {
  buttonElement = $(buttonElement);
  var item = buttonElement.closest('li'),
    img = item.find('img'),
    titleDiv = item.find('.img-title'),
    input = titleDiv.find('input'),
    attachmentId = img.data('attachmentid');

  jQuery.ajax({
    type: "POST",
    url: ajaxurl,
    data: {
      action: 'postgalleryRenameimage',
      attachmentId: attachmentId,
      newfilename: input.val()
    },
    success: function (data, textStatus) {
      renameImageComplete(data, textStatus, item);
    },
    dataType: 'json'
  });

  titleDiv.addClass('changed');
};

window.renameImageComplete = function (result, status, item) {
  var img = item.find('img'),
    titleDiv = item.find('.img-title'),
    input = titleDiv.find('input'),
    attachmentId = img.data('attachmentid');

  if (result.success) {
    titleDiv.removeClass('changed');
    input.val(result.newFilename);
    input.data('filename', result.newFilename);
    img.data('src', result.newFullFilename);
    $(".sortable-pics").trigger("sortupdate");
  } else {
    titleDiv.removeClass('changed');
    input.val(input.data('filename'));
  }
};

window.multiRename = function () {
  var items = $('.sortable-pics li'),
    prefix = $('.postgallery-multireplace-prefix').val();

  items.each(function (index, element) {
    element = $(element);
    var input = element.find('.img-title input'),
      button = element.find('.img-title .save-rename-button');
    input.val(prefix + (index + 1));
    button.trigger('click');
  });
};