

function checkForUpload() {
	jQuery('#imageupload_image').each(function (index, element) {
		var uploaderConfig = {},
			uploaderElement = null;
		//uploaderConfig.debug = true;

		uploaderConfig.dragText         = '';
		uploaderConfig.uploadButtonText = 'Upload';
		uploaderConfig.cancelButtonText = 'Abort';
		uploaderConfig.failUploadText   = '';

		uploaderConfig.areText  = "are";
		uploaderConfig.isText   = "is";
		uploaderConfig.messages = {
			typeError:    "Das Format der Datei '{file}' ist unzulässig. Erlaubte Dateitypen {isAre} {extensions}.",
			sizeError:    "Die Datei '{file}' ist zu groß. Es ist maximal {sizeLimit} erlaubt.",
			noFilesError: "Es sind keine Dateien ausgewählt.",
			onLeave:      "Es werden noch Dateien hochgeladen. Verlassen sie die Seite wird der Vorgang abgebrochen."
		};

		uploaderConfig.element = element;
		uploaderConfig.allowedExtensions = ['JPG', 'PNG', 'GIF'];
		uploaderConfig.sizeLimit = 8048576;
		uploaderConfig.multiple = true;

		uploaderConfig.action = ajaxurl + '/?action=postgallery_upload&uploadfolder=' + jQuery(element).data('uploadfolder');

		//uploaderConfig.extraDropzones = jQuery('#imageupload_bild');

		uploaderConfig.onComplete = checkForUploadComplete;
		uploaderConfig.onProgress = uploadProgress;

		uploader = new qq.FileUploader(uploaderConfig);

		return true;
	});
}

function uploadProgress(id, fileName, loaded, total) {
	jQuery('#imageupload_image').css({'background-image': 'url(' + jQuery('#imageupload_image').data('pluginurl') + '/images/loader.gif)'});
}

function checkForUploadComplete(id, fileName, result) {
	var uploadParent = null,
		tempTimestamp = new Date(),
		thumbWidth = 150,
		thumbHeight = 150;

	jQuery('#imageupload_image').css({'background-image': ''});

	if (result.success) {
		var imageURL = result.thumb_url;
		jQuery('#sortable_pics').append('<li><img data-src="' + result.filename + '" src="' + imageURL + '" /><div class="img_title">' + result.filename + '</div></li>');
	} else {
		var error = '';
		if (typeof(result.error) !== 'undefined') {
			error = result.error;
		} else if (typeof(result.errorMsg) !== 'undefined') {
			error = result.errorMsg;
		}
		jQuery('#upload_error').append('<span>E: ' + fileName + ': ' + error + '</span>');
	}
}