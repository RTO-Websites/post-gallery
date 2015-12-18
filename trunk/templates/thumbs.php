<?php
    /**
     * Template Page for the thumbs
     *
     * Follow variables are useable:
     *        $images
     *            -> filename, path, thumbURL
     */
?>
<style type="text/css">
    .post-gallery_thumb {
        display: inline-block;
        margin: 10px;
        max-width: 150px;
    }
</style>
<section class="gallery">
    <?php foreach ( $images as $image ) { ?>
        <a href="<?php echo $image[ 'path' ] ?>">
            <img class="post-gallery_thumb"
                src="<?php echo \Inc\PostGallery::getThumbUrl( $image[ 'path' ], array( 'width' => 150, 'height' => 150 ) ) ?>"
                alt="<?php echo $image[ 'alt' ] ?>"/>
        </a>
    <?php } ?>
</section>