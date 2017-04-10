<?php
    /**
     * Template Page for the thumbs
     *
     * Follow variables are useable:
     *        $images
     *            -> filename, path, thumbURL
     */

    var_dump($this->options);
?>
<figure class="gallery pg-theme-thumbs pg-theme-list">
    <?php foreach ( $images as $image ) { ?>
        <a href="<?php echo $image[ 'path' ] ?>">
            <img class="post-gallery_thumb"
                src="<?php echo \Inc\PostGallery::getThumbUrl( $image[ 'path' ],
                    array(
                        'width' => $this->option( 'thumbWidth' ),
                        'height' => $this->option( 'thumbHeight' ),
                        'scale' => $this->option('thumbScale'),
                    )); ?>"
                alt="<?php echo $image[ 'alt' ] ?>"  data-width="<?php echo $this->option( 'thumbScale' ); ?>" />
        </a>
    <?php } ?>
</figure>