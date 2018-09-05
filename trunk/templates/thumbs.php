<?php
/**
 * Template Page for the thumbs
 *
 * Follow variables are useable:
 *        $images
 *            -> filename, path, thumbURL
 */
?>
<figure class="gallery pg-theme-thumbs pg-theme-list">
    <?php foreach ( $images as $image ) { ?>
        <a href="<?php echo $image['url'] ?>">
            <img class="post-gallery_thumb"
                    src="<?php echo \Inc\PostGallery::getThumbUrl( $image['path'],
                        [
                            'width' => $this->option( 'thumbWidth' ),
                            'height' => $this->option( 'thumbHeight' ),
                            'scale' => $this->option( 'thumbScale' ),
                        ] ); ?>"
                    data-title="<?php echo $image['title'] ?>"
                    data-desc="<?php echo $image['desc'] ?>"
                    alt="<?php echo $image['alt'] ?>" data-scale="<?php echo $this->option( 'thumbScale' ); ?>"/>
        </a>
    <?php } ?>
</figure>