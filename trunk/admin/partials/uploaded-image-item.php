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
    <div class="del" onclick="deleteImage(this.parentNode, <?php echo $attachmentId; ?> );"></div>
    <div class="edit-details" onclick="pgOpenDetailWindow(this.parentNode);"></div>
</li>
