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

  if (typeof(elementor) !== 'undefined') {
    // init postgallery on elementor widget open
    elementor.hooks.addAction('panel/open_editor/widget/postgallery', function (panel, model, view) {
      initPostGallery();
    });
  }
});


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

  /**
   * Write titles, desc, alt to elementor fields
   */
  $(document).on('change, keydown', '.sortable-pics .details input,.sortable-pics .details textarea', function (e) {
    updateElementorFields();
  });

  // make pics sortable
  if ($.fn.sortable) {
    $(".sortable-pics").sortable();
    $(".sortable-pics").on("sortupdate", function (event, ui) {
      pgCloseDetails();
      var input = jQuery("#postgalleryImagesort"),
        elementorInput = jQuery('input[data-setting="pgsort"]'),
        value = [],
        count = 0;

      $(".sortable-pics > li > img").each(function (index, element) {
        value[count] = jQuery(element).data("src");
        count += 1;
      });
      input.val(value.join(","));

      // change elementor control
      if (elementorInput.length) {
        elementorInput.val(value.join(","));
        elementorInput.trigger('input'); // triggers update, so it can be saved
      }
    });
  }

  if (typeof(postgalleryLang) !== 'undefined') {
    // add upload
    checkForUpload();
    $(".qq-upload-drop-area span").html(postgalleryLang.moveHere);
    $(".qq-upload-button").addClass("button");
  }

  updateElementorFields();
};

function updateElementorFields() {
  var postgalleryTitles = {},
    postgalleryDescs = {},
    postgalleryAltAttributes = {},
    postgalleryImageOptions = {};

  var data = [];
  var form = $('.sortable-pics .details input,.sortable-pics .details textarea');
  form.each(function (index, element) {
    var value = '';
    element = $(element);
    value = element.val();

    data[element.attr('name')] = value;
    eval(element.attr('name').replace("[", "['").replace("]", "']") + ' = `' + value + '`;');
  });

  $('input[data-setting="pgimgtitles"]').val(JSON.stringify(postgalleryTitles));
  $('input[data-setting="pgimgdescs"]').val(JSON.stringify(postgalleryDescs));
  $('input[data-setting="pgimgoptions"]').val(JSON.stringify(postgalleryImageOptions));
  $('input[data-setting="pgimgalts"]').val(JSON.stringify(postgalleryAltAttributes));
  $('input[data-setting="pgimgtitles"]').trigger('input');
  $('input[data-setting="pgimgdescs"]').trigger('input');
  $('input[data-setting="pgimgoptions"]').trigger('input');
  $('input[data-setting="pgimgalts"]').trigger('input');
}

function deleteImages(path) {
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
}

function deleteImage(element, path) {
  pgCloseDetails();
  jQuery.post(ajaxurl + "?action=postgalleryDeleteimage&path=" + path,
    function (data, textStatus) {
      deleteImageComplete(data, textStatus, element);
    }
  );
}

function deleteImageComplete(result, status, element) {
  if (result == 1) {
    jQuery(element.remove());
  }
}

function pgToggleDetails(buttonElement) {
  var detailElement = jQuery(buttonElement).parent().find('.details'),
    allDetailElements = jQuery('.sortable-pics .details');

  if (detailElement.hasClass('active')) {
    allDetailElements.removeClass('active');
  } else {
    allDetailElements.removeClass('active');
    detailElement.addClass('active');
  }
}

function pgCloseDetails() {
  var allDetailElements = jQuery('.sortable-pics .details');
  allDetailElements.removeClass('active');
}
