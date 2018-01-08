
function initPostGalleryElementor() {

  /*
   change elementor image-source, load new upload
    */
  $(document).on('change', 'select[data-setting="pgimgsource"]', loadUpload);

  /**
   * Write titles, desc, alt to elementor fields
   */
  $(document).on('change, keyup', '.sortable-pics .details input,.sortable-pics .details textarea', function (e) {
    updateElementorFields();
  });

  /*
   reload upload if tab is switched
    */
  $(document).on('click', '.elementor-tab-control-content a', function(e) {
    var element = $(e.target),
      container = element.closest('.ps-container'),
      field = container.find('input[data-setting="pgsort"]');

    if (field.length) {
      loadUpload();
      initElementorAddButton();
    }
  });
}

/**
 * add input and button to add new gallery
 */
function initElementorAddButton() {
  $('.pg-new-gallery').remove();
  $('.pg-new-gallery-button').remove();
  // add input and button
  $('select[data-setting="pgimgsource"]').after('<input type="text" placeholder="New Gallery" class="pg-new-gallery" />'
    + '<button class="elementor-button elementor-button-default pg-new-gallery-button" type="button">' +
    '<span class="eicon-plus"></span></button>');

  // add click event
  $(document).on('click', '.pg-new-gallery-button', function(e) {
    var newTitle = $('.pg-new-gallery').val();
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
        $('.pg-new-gallery').val('');
        var post = JSON.parse(data);

        if (post.ID) {
          // add to source select
          $('select[data-setting="pgimgsource"]').append('<option value="' + post.ID + '">'
            + post.post_title + '</option>');

          // select new post
          $('select[data-setting="pgimgsource"]').val(post.ID);
          $('select[data-setting="pgimgsource"]').trigger('input');
          $('select[data-setting="pgimgsource"]').trigger('select');
          $('select[data-setting="pgimgsource"]').trigger('change');
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
  var postid = $('select[data-setting="pgimgsource"]').val();
  jQuery.post(ajaxurl + "?action=postgalleryGetImageUpload&post=" + postid,
    function (data, textStatus) {
      $('.pg-image-container').html(data);
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
    $(".qq-upload-drop-area span").html(postgalleryLang.moveHere);
    $(".qq-upload-button").addClass("button");
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
  var form = $('.sortable-pics .details input,.sortable-pics .details textarea');
  form.each(function (index, element) {
    element = $(element);
    var value = element.val();

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

  //elementor.reloadPreview();
}