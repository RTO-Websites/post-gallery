<?php

$images[$file] = '<li>'
    . '<img style="" data-attachmentid="' . $attachmentId . '" data-src="' . $file . '" src="' . $thumb['url'] . '" alt="" />'
    . '<div class="img-title">'
    . '<input onkeypress="triggerFilenameChange(this);" data-filename="' . $filename . '" type="text" class="img-filename" value="' . $filename . '"  autocomplete="off"/>'
    . '<div class="save-rename-button dashicons dashicons-yes" onclick="renameImage(this);"></div>'
    . '</div>'
    . '<div class="del" onclick="deleteImage(this.parentNode, ' . $attachmentId . ');">x</div>'
    . '<div class="edit-details" onclick="pgToggleDetails(this);"></div>'
    . '<div class="details">'
    . '<div class="title"><input type="text" placeholder="' . __( 'Title' ) . '" name="postgalleryTitles[' . $file . ']" value="' . ( !empty( $titles[$file] ) ? $titles[$file] : '' ) . '" /></div>'
    . '<div class="desc"><textarea placeholder="' . __( 'Description' ) . '" name="postgalleryDescs[' . $file . ']">' . ( !empty( $descs[$file] ) ? $descs[$file] : '' ) . '</textarea></div>'
    . '<div class="image-options"><textarea placeholder="' . __( 'key|value' ) . '" name="postgalleryImageOptions[' . $file . ']">' . ( !empty( $imageOptions[$file] ) ? $imageOptions[$file] : '' ) . '</textarea></div>'
    . '<div class="alt-attribute"><input type="text" placeholder="' . __( 'Alt-Attribut' ) . '" name="postgalleryAltAttributes[' . $file . ']" value="' . ( !empty( $altAttributes[$file] ) ? $altAttributes[$file] : '' ) . '" /></div>'
    . '</div>'
    . '</li>';
