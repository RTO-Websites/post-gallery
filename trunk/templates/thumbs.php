<?php
    /**
     * Template Page for the thumbs
     *
     * Follow variables are useable:
     *        $images
     *            -> filename, path, thumbURL
     */
?>
<section class="gallery pg-theme-thumbs">
    <?php foreach ( $images as $image ) { ?>
        <a href="<?php echo $image[ 'path' ] ?>">
            <img class="post-gallery_thumb"
                src="<?php echo \Inc\PostGallery::getThumbUrl( $image[ 'path' ], array( 'width' => 150, 'height' => 150 ) ) ?>"
                alt="<?php echo $image[ 'alt' ] ?>"/>
        </a>
    <?php } ?>
</section>