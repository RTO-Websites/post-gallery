<div class="postgallery-uploader multiple"
        data-uploadfolder="<?php echo $imageDir; ?>"
        data-pluginurl="<?php echo POSTGALLERY_URL; ?>"
        data-postid="<?php echo $currentLangPost->ID; ?>">
    <div class="postgallery-uploader-content">
        <h2 class="upload-instructions drop-instructions"><?php _e( 'Drop files anywhere to upload' ); ?></h2>
        <p class="upload-instructions drop-instructions"><?php _ex( 'or', 'Uploader: Drop files here - or - Select Files' ); ?></p>
        <input type="button" value="<?php _e( 'Select Files' ); ?>" class="postgallery-uploader-button browser button button-hero">
    </div>
    <span class="ajaxnonce" id="<?php echo wp_create_nonce( __FILE__ ); ?>"></span>
    <div class="drop-zone"
            ondragenter="$(this).addClass('active');"
            ondragleave="$(this).removeClass('active');"
            ondrop="$(this).removeClass('active');"></div>
</div>

<div class="postgallery-uploader-queue"></div>