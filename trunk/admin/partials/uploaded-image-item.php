<li>
    <img style=""
        data-attachmentid="<?php echo $attachmentId; ?>"
        data-src="<?php echo $fullFilename; ?> "
        src="<?php echo $thumbUrl; ?>"
        alt=""/>
    <div class="img-title">
        <input onkeypress="triggerFilenameChange(this);"
            data-filename="<?php echo $filename; ?>"
            type="text"
            class="img-filename"
            value="<?php echo $filename; ?>"
            autocomplete="off"/>
        <div class="save-rename-button dashicons dashicons-yes" onclick="renameImage(this);"></div>
    </div>
    <div class="del" onclick="deleteImage(this.parentNode, <?php echo $attachmentId; ?> );">x</div>
    <div class="edit-details" onclick="pgToggleDetails(this);"></div>
    <div class="details">
        <div class="title"><input type="text"
                placeholder="<?php echo $placeholderTitle; ?>"
                name="postgalleryTitles[<?php echo $fullFilename; ?>]"
                value="<?php echo $title; ?>" /></div>
        <div class="desc"><textarea placeholder="<?php echo $placeholderDesc; ?>"
                name="postgalleryDescs[<?php echo $fullFilename; ?>]"><?php echo $desc; ?></textarea></div>
        <div class="image-options"><textarea placeholder="<?php echo $placeholderImgOptions; ?>"
                name="postgalleryImageOptions[<?php echo $fullFilename; ?>]"><?php echo $imgOptions; ?></textarea></div>
        <div class="alt-attribute"><input type="text"
                placeholder="<?php echo $placeholderAlt; ?>"
                name="postgalleryAltAttributes[<?php echo $fullFilename; ?>]"
                value="<?php echo $alt; ?>"/></div>
    </div>
</li>
