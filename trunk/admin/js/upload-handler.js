function pgInitUpload() {
  if (!$) {
    var $ = jQuery;
  }
  if (!$('.postgallery-uploader').length) {
    return;
  }

  var options,
    uploader,
    container = $('.postgallery-uploader:not(.is-initialized)'),
    queue = container.parent().find('.postgallery-uploader-queue');

  if (!container.length) {
    return;
  }

  options = {
    multipart_params: {
      _ajax_nonce: container.find('.ajaxnonce').attr('id'),
      action: 'postgalleryAjaxUpload',
      uploadFolder: container.data('uploadfolder'),
      postid: container.data('postid'),
    },
    browse_button: container.find('.postgallery-uploader-button')[0],
    url: ajaxurl,
    multi_selection: container.hasClass('multiple'),
    drop_element: container.find('.drop-zone')[0],
    chunk_size: '1536kb',
    filters: {
      mime_types: [
        {
          title: "Image files",
          extensions: "jpg,jpeg,gif,png"
        }
      ]
    }
  };


  uploader = new plupload.Uploader(options);
  uploader.init();
  container.addClass('is-initialized');

  // EVENTS
  // init
  uploader.bind('Init', function (up) {

  });

  // file added
  uploader.bind('FilesAdded', function (up, files) {
    queue.html('');
    $.each(files, function (i, file) {
      queue.append('<div class="postgallery-queue-item" id="queue-item-'
        + file.id + '"><div class="filename">' + file.name
        + '</div><div class="progress-bar"></div><div class="percent"></div></div>');
    });

    container.addClass('progress');

    up.refresh();
    up.start();
  });

  // upload progress
  uploader.bind('UploadProgress', function (up, file) {
    var item = $('#queue-item-' + file.id);
    item.find('.progress-bar').css({width: file.percent + '%'});
    item.find('.percent').html(file.percent);
  });

  // file uploaded
  uploader.bind('FileUploaded', function (up, file, response) {
    response = $.parseJSON(response.response);

    if (response['success']) {
      $('.sortable-pics').append(response.itemHtml);
      var queueItem = $('#queue-item-' + file.id);
      // remove element from queue
      setTimeout(function () {
        queueItem.animate({
          opacity: 0,
          height: 0,
        }, function () {
          queueItem.remove();
        });
      }, 600);
    } else {
      $('#queue-item-' + file.id).after('<span>Error: ' + response.msg + '</span>');
      $('#queue-item-' + file.id).addClass('error');
    }
  });

  // all files uploaded
  uploader.bind('UploadComplete', function () {
    $('.sortable-pics').trigger('sortupdate');
    container.removeClass('progress');
  });
}

jQuery(document).ready(function () {
  pgInitUpload();
});