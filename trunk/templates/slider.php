<?php
/**
 * Template Page for the gallery slider
 *
 * Follow variables are useable:
 *        $images
 *            -> filename, path, thumbURL
 */

$first_image = array_shift( $images );
array_unshift( $images, $first_image );
?>
<figure class="gallery pg-theme-slider">

    <div class="pg-slider owl-theme ow-carousel">
        <?php foreach ( $images as $image ) { ?>
            <img class="gallery-image"
                    src="<?php echo \Inc\PostGallery::getThumbUrl( $image['path'],
                        array(
                            'width' => $this->option( 'thumbWidth' ),
                            'height' => $this->option( 'thumbHeight' ),
                            'scale' => $this->option('thumbScale'),
                    ));
                    ?>"
                    alt="<?php echo $image['filename'] ?>" />
        <?php } ?>
    </div>

    <script>
      jQuery('.pg-slider').owlCarousel({
        <?php echo $this->option( 'sliderOwlConfig' ); ?>
      });
    </script>
</figure>