function initPostGalleryElementor() {

  /*
   change elementor image-source, load new upload
    */
  jQuery(document).on('change', 'select[data-setting="pgimgsource"]', loadUpload);

  jQuery(document).on('change', 'select[data-setting="imageSize"]', checkThumbsize);


  /**
   * Write titles, desc, alt to elementor fields
   */
  jQuery(document).on('change, keyup', '.sortable-pics .details input,.sortable-pics .details textarea', function (e) {
    updateElementorFields();
  });

  /*
   reload upload if tab is switched
    */
  jQuery(document).on('click', '.elementor-tab-control-content a', function (e) {
    var element = jQuery(e.target),
      container = element.closest('.ps-container'),
      field = container.find('input[data-setting="pgsort"]');

    if (field.length) {
      loadUpload();
      initElementorAddButton();
      checkMasonry();
    }
  });
}


function checkMasonry() {
  if (typeof(jQuery('#elementor-preview-iframe')[0].contentWindow.jQuery.fn.masonry) !== 'undefined') {
    return true;
  }

  jQuery('.elementor-control-masonry').hide();
}

/**
 * add input and button to add new gallery
 */
function initElementorAddButton() {
  jQuery('.pg-new-gallery').remove();
  jQuery('.pg-new-gallery-button').remove();
  // add input and button
  jQuery('select[data-setting="pgimgsource"]').after('<input type="text" placeholder="New Gallery" class="pg-new-gallery" />'
    + '<button class="elementor-button elementor-button-default pg-new-gallery-button" type="button">' +
    '<span class="eicon-plus"></span></button>');

  // add click event
  jQuery(document).on('click', '.pg-new-gallery-button', function (e) {
    var newTitle = jQuery('.pg-new-gallery').val();
    if (!newTitle.length) {
      return;
    }

    if (window.pgCreateProgress) {
      return;
    }

    window.pgCreateProgress = true;

    // create new post via ajax
    jQuery.post(ajaxurl + "?action=postgalleryNewGallery&title=" + encodeURI(newTitle),
      function (data, textStatus) {
        jQuery('.pg-new-gallery').val('');
        var post = JSON.parse(data);

        if (post.ID) {
          // add to source select
          jQuery('select[data-setting="pgimgsource"]').append('<option value="' + post.ID + '">'
            + post.post_title + '</option>');

          // select new post
          jQuery('select[data-setting="pgimgsource"]').val(post.ID);
          jQuery('select[data-setting="pgimgsource"]').trigger('input');
          jQuery('select[data-setting="pgimgsource"]').trigger('select');
          jQuery('select[data-setting="pgimgsource"]').trigger('change');
          loadUpload();
        }

        window.pgCreateProgress = false;
      }
    );
  });
}

/**
 * Get images and upload-field with ajax
 */
function loadUpload() {
  var postid = jQuery('select[data-setting="pgimgsource"]').val();
  if (postid == 0) {
    postid = ElementorConfig.post_id;
  }
  jQuery.post(ajaxurl + "?action=postgalleryGetImageUpload&post=" + postid,
    function (data, textStatus) {
      jQuery('.pg-image-container').html(data);
      initUpload();
      initSortable();
      updateElementorFields();
    }
  );
}

/**
 * Init drag&drop upload
 */
function initUpload() {
  if (typeof(postgalleryLang) !== 'undefined') {
    // add upload
    checkForUpload();
    jQuery(".qq-upload-drop-area span").html(postgalleryLang.moveHere);
    jQuery(".qq-upload-button").addClass("button");
  }
}

/**
 * Trigger update on elementor hidden fields
 *  Need to make saveable
 */
function updateElementorFields() {

  if (typeof(elementor) == 'undefined') {
    return;
  }
  var postgalleryTitles = {},
    postgalleryDescs = {},
    postgalleryAltAttributes = {},
    postgalleryImageOptions = {};

  var data = [];
  var form = jQuery('.sortable-pics .details input,.sortable-pics .details textarea');
  form.each(function (index, element) {
    element = jQuery(element);
    var value = element.val();

    data[element.attr('name')] = value;
    eval(element.attr('name').replace("[", "['").replace("]", "']") + ' = `' + value + '`;');
  });

  jQuery('input[data-setting="pgimgtitles"]').val(JSON.stringify(postgalleryTitles));
  jQuery('input[data-setting="pgimgdescs"]').val(JSON.stringify(postgalleryDescs));
  jQuery('input[data-setting="pgimgoptions"]').val(JSON.stringify(postgalleryImageOptions));
  jQuery('input[data-setting="pgimgalts"]').val(JSON.stringify(postgalleryAltAttributes));
  jQuery('input[data-setting="pgimgtitles"]').trigger('input');
  jQuery('input[data-setting="pgimgdescs"]').trigger('input');
  jQuery('input[data-setting="pgimgoptions"]').trigger('input');
  jQuery('input[data-setting="pgimgalts"]').trigger('input');
}

/**
 * Checks image-size and set width&height
 */
function checkThumbsize() {
  var select = jQuery('.elementor-control-imageSize select'),
    selectedVal = select.val(),
    sizes = selectedVal.split('x');

  if (selectedVal == 0) {
    // custom size
    $('.elementor-control-pgthumbwidth').show();
    $('.elementor-control-pgthumbheight').show();
  } else {
    $('.elementor-control-pgthumbwidth input').val(sizes[0]);
    $('.elementor-control-pgthumbheight input').val(sizes[1]);
    $('.elementor-control-pgthumbwidth').hide();
    $('.elementor-control-pgthumbheight').hide();
  }
}