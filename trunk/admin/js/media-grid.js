
/**
 * Onload
 */
jQuery(window).on('load', function () {
  wp.media.model.Query.defaultArgs.posts_per_page = -1;
  setTimeout(hookMediaGrid, 400);
  if (typeof (wp.media) !== 'undefined' && typeof (wp.media.frame) !== 'undefined') {
    wp.media.frame.on('open', hookMediaGrid);
  }

  setInterval(function () {
    if (jQuery('.attachments > .attachment:not([data-id="true"])').length) {
      hookMediaGrid();
    }
  }, 500);
});

jQuery(function() {
  if (typeof (wp.media) !== 'undefined' && typeof (wp.media.model) !== 'undefined') {
    wp.media.model.Query.defaultArgs.posts_per_page = -1;
  }
});


/**
 * Hooks in wordpress media and group images by parent-post
 */
window.hookMediaGrid = function () {
  if (window.groupMediaGridProgress) {
    return;
  }

  var containers = jQuery('.attachments-browser .attachments');

  if (!containers.length) {
    return;
  }

  containers.each(function (index, container) {
    container = jQuery(container);
    var children = container.find('.attachment'),
      directChildren = container.find('> .attachment'),
      attachmentIds = [];

    if (!directChildren.length) {
      return;
    }

    window.groupMediaGridProgress = true;

    if (container.find('.media-group-by-parent').length) {
      container.find('.media-group-by-parent').addClass('to-remove');
      container.find('.attachment.cloned').remove();
    }

    // collect all image-ids
    children.each(function (index, element) {
      attachmentIds.push(jQuery(element).data('id'));
    });

    if (!attachmentIds.length) {
      return;
    }

    // get ids grouped by parent
    jQuery.get(
      ajaxurl + '?action=postgalleryGetGroupedMedia&attachmentids=' + attachmentIds.join(','),
      function (data) {
        try {
          data = JSON.parse(data);
        } catch (e) {
          console.info('json parse fail', data);
          return;
        }

        // loop groups
        for (var index in data) {
          var parent = data[index],
            groupContainer = jQuery('<li class="media-group-by-parent" data-parent="' + index + '">'),
            groupContainerUl = jQuery('<ul class="media-group-ul" />'),
            headline = jQuery('<h2 class="media-group-headline" />');

          headline.append('<a class="media-group-adminlink" href="' + parent.adminlink + '">' + parent.title + '</a>');
          headline.append('<a class="media-group-permalink" href="' + parent.permalink + '" target="_blank"><span class="dashicons dashicons-visibility"></span></a>');

          groupContainer.append(headline);
          groupContainer.append(groupContainerUl);

          // loop attachments
          for (var attachmentIndex in data[index].posts) {
            var posts = data[index].posts,
              attachmentId = posts[attachmentIndex]['id'],
              element = container.find('.attachment[data-id="' + attachmentId + '"]:not(.cloned)'),
              url = posts[attachmentIndex]['url'];

            // add path to element
            window.addPathToMediaItem(element, url);

            // add labels to element
            window.addLabelsToMediaItem(element, parent, url);

            // add attachment to group-container
            element.appendTo(groupContainerUl);
          }

          // add post-thumbnail, also if it has not the post-parent
          window.addMediaGroupPostThumbnail(parent, groupContainerUl, container);

          groupContainer.appendTo(container);
        }

        container.find('.media-group-by-parent.to-remove').remove();

        window.groupMediaGridProgress = false;
      }
    );
  });
};

/**
 * Clone media-item to show it as post-thumbnail
 *
 * @param parent
 * @param groupContainerUl
 * @param container
 */
window.addMediaGroupPostThumbnail = function (parent, groupContainerUl, container) {
  if (parent.thumbnail && !groupContainerUl.find('.attachment[data-id="' + parent.thumbnail + '"]').length) {
    var thumbnail = container.find('.attachment[data-id="' + parent.thumbnail + '"]:not(.cloned)').clone(false);
    thumbnail.addClass('cloned is-post-thumbnail');
    thumbnail.removeClass('selected details');
    window.addLabelsToMediaItem(thumbnail, parent, '');

    thumbnail.on('click', function () {
      var original = container.find('.attachment[data-id="' + jQuery(this).data('id') + '"]:not(.cloned)');
      original.click();

      if (original.hasClass('selected')) {
        jQuery(this).addClass('selected');
        jQuery(this).addClass('details');
      } else {
        jQuery(this).removeClass('selected');
        jQuery(this).removeClass('details');
      }
    });
    thumbnail.prependTo(groupContainerUl);
  }
};

/**
 * Add label like postgallery or post-thumbnail to media-item
 *
 * @param element
 * @param parent
 * @param path
 */
window.addLabelsToMediaItem = function (element, parent, path) {
  element.find('.media-group-label').remove();
  // add thumbnail-class
  if (element.data('id') === parent.id || element.hasClass('cloned')) {
    element.addClass('is-post-thumbnail');
    element.append(jQuery('<span class="media-group-label is-post-thumbnail">Thumbnail</span>'));

  }

  // add post-gallery-class
  if (typeof (path) === 'string' && path.indexOf('/gallery/') !== -1 && !element.hasClass('cloned')) {
    element.addClass('is-postgallery-image');
    element.append(jQuery('<span class="media-group-label is-postgallery-image">PostGallery</span>'));
  }
};

/**
 * Adds a span with path and filename to every media-item
 *
 * @param element
 * @param path
 */
window.addPathToMediaItem = function (element, path) {
  element.find('.media-group-path').remove();

  if (!path) {
    return;
  }

  var pathSplit = path.split('wp-content/uploads/');
  if (typeof (pathSplit[1]) !== 'undefined') {
    path = pathSplit[1].replace('gallery/', '');
  } else {
    pathSplit = path.split('wp-includes/images/');
    if (typeof (pathSplit[1]) !== 'undefined') {
      path = pathSplit[1];
    }
  }
  element.attr('data-path', path);

  var pathElement = jQuery('<span class="media-group-path" />');
  pathSplit = path.split('/');
  var filename = pathSplit.pop(),
    pathOnly = pathSplit.join('/');
  pathElement.append('<span class="media-path">' + pathOnly + '/</span>');
  pathElement.append('<span class="media-filename">' + filename + '</span>');
  element.append(pathElement);
};