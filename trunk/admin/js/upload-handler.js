function pgInitUpload() {
  if (!$) {
    var $ = jQuery;
  }
  if (!$('.postgallery-uploader').length) {
    return;
  }

  var options,
    uploader,
    container = jQuery('.postgallery-uploader');


  options = {
    multipart_params: {
      _ajax_nonce: container.find('.ajaxnonce').attr('id'),
      action: 'postgalleryAjaxUpload',
      uploadFolder: container.data('uploadfolder'),
      postid: container.data('postid'),
    },
    browse_button: container.find('.postgallery-uploader-button')[0],
    url: ajaxurl,
    debug: true,
    multi_selection: container.hasClass('multiple'),
    drop_element: container.find('.drop-zone')[0],
    //chunk_size: '1kb',
  };


  uploader = new plupload.Uploader(options);
  uploader.init();
  console.info('init upload', uploader);

  // EVENTS
  // init
  uploader.bind('Init', function (up) {

  });

  // file added
  uploader.bind('FilesAdded', function (up, files) {
    $.each(files, function (i, file) {
      console.log('File Added', i, file);
    });

    container.addClass('progress');

    up.refresh();
    up.start();
  });

  // upload progress
  uploader.bind('UploadProgress', function (up, file) {
    console.log('Progress', up, file)
  });

  // file uploaded
  uploader.bind('FileUploaded', function (up, file, response) {
    response = $.parseJSON(response.response);

    if (response['success']) {
      console.log('Success', up, file, response);
      $('.sortable-pics').append(response.itemHtml);
    } else {
      console.log('Error', up, file, response);
      $('.postgallery-upload-error').append('<span>Error: ' + response.filename + ':<br />' + response.msg + '</span><br />');
    }

  });

  // all files uploaded
  uploader.bind('UploadComplete', function () {
    console.info('all complete');
    container.removeClass('progress');
  });
}

jQuery(document).ready(function () {
  pgInitUpload();
});