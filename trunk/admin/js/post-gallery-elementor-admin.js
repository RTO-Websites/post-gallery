function initPostGalleryElementor() {

  /*
   change elementor image-source, load new upload
    */
  jQuery(document).on('change', 'select[data-setting="pgimgsource"]', loadUpload);

  jQuery(document).on('change', 'select[data-setting="imageSize"]', checkThumbsize);

  // change gap or item-ratio
  jQuery(document).on('change', 'input[data-setting="size"],' +
    '.elementor-control-column_gap input,' +
    '.elementor-control-columns input,' +
    '.elementor-control-no_grid input,' +
    '.elementor-control-row_gap input', checkItemRatio);

  // legacy elementor
  jQuery(document).on('slide', '.elementor-control-item_ratio .ui-slider,' +
    '.elementor-control-column_gap .ui-slider,' +
    '.elementor-control-row_gap .ui-slider', checkItemRatio);

  // elementor change slider
  jQuery(document).on('mousemove', '.elementor-control-item_ratio .noUi-handle,' +
    '.elementor-control-column_gap .noUi-handle,' +
    '.elementor-control-row_gap .noUi-handle', checkItemRatio);

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
    let element = jQuery(e.target),
      container = element.closest('.ps'),
      field = container.find('input[data-setting="pgsort"]');

    if (field.length) {
      loadUpload();
      initElementorAddButton();
      checkMasonry();
    }
  });
}

/**
 * Triggers on item-ratio change
 *  Relayout masonry if masonry is active.
 */
function checkItemRatio() {
  if (checkMasonry()) {
    let galleries = jQuery('#elementor-preview-iframe')[0].contentWindow.jQuery('.elementor-image-gallery.with-js-masonry .gallery');
    galleries.masonry('layout');
  }
}

/**
 * Checks if website in preview-frame has masonry
 *
 * @returns {boolean}
 */
function checkMasonry() {
  if (typeof (jQuery('#elementor-preview-iframe')[0].contentWindow.jQuery.fn.masonry) !== 'undefined') {
    return true;
  }

  // hide options for js-masonry in widget
  jQuery('.elementor-control-masonry select option').each(function (index, element) {
    if (element.value == 'on' || element.value == 'horizontal') {
      jQuery(element).hide();
    }
  });

  return false;
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
    let newTitle = jQuery('.pg-new-gallery').val();
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
        let post = JSON.parse(data);

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
  let postid = jQuery('select[data-setting="pgimgsource"]').val();
  if (postid == 0) {
    postid = ElementorConfig.document.id;
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
  if (typeof (postgalleryLang) !== 'undefined') {
    // add upload
    pgInitUpload();
  }
}

/**
 * Trigger update on elementor hidden fields
 *  Need to make saveable
 */
function updateElementorFields() {

  if (typeof (elementor) == 'undefined') {
    return;
  }
  let data = [],
    form = jQuery('.sortable-pics .details input,.sortable-pics .details textarea');

  form.each(function (index, element) {
    element = jQuery(element);
    let value = element.val();

    data[element.attr('name')] = value;
    eval(element.attr('name').replace("[", "['").replace("]", "']") + ' = `' + value + '`;');
  });
}

/**
 * Checks image-size and set width&height
 */
function checkThumbsize() {
  let select = jQuery('.elementor-control-imageSize select'),
    selectedVal = select.val();

  if (!select.length || typeof (selectedVal) === 'undefined') {
    return;
  }

  let sizes = selectedVal.split('x');

  if (selectedVal == 0) {
    // custom size
    $('.elementor-control-pgthumbwidth').show();
    $('.elementor-control-pgthumbheight').show();
    $('.elementor-control-pgthumbscale').show();
  } else {
    //$('.elementor-control-pgthumbwidth input').val(sizes[0]);
    //$('.elementor-control-pgthumbheight input').val(sizes[1]);
    $('.elementor-control-pgthumbwidth').hide();
    $('.elementor-control-pgthumbheight').hide();
    $('.elementor-control-pgthumbscale').hide();
  }
}