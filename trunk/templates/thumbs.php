<?php
/**
 * Template Page for the thumbs
 *
 * Follow variables are useable:
 *        $images
 *            -> filename, path, thumbURL
 */
?>
    <figure role="group"
            class="gallery pg-theme-thumbs pg-theme-list <?php echo $this->option( 'containerClass' ); ?>">
        <?php foreach ( $images as $image ): ?>
            <?php
            $thumbUrl = \Lib\PostGalleryImage::getThumbUrl( $image['path'],
                [
                    'width' => $this->option( 'thumbWidth' ),
                    'height' => $this->option( 'thumbHeight' ),
                    'scale' => $this->option( 'thumbScale' ),
                ] );
            ?>
            <div class="item" <?php echo $image['imageOptionsParsed']; ?>>
                <figure class="inner">
                    <a href="<?php echo $image['url'] ?>">
                        <?php if ( $this->option( 'useSrcset' ) ): ?>
                            <img class="post-gallery_thumb"
                                    src="<?php echo $image['url'] ?>"
                                    data-title="<?php echo $image['title'] ?>"
                                    data-desc="<?php echo $image['desc'] ?>"
                                    alt="<?php echo $image['alt'] ?>"
                                    srcset="<?php echo $image['srcset']; ?>"
                                    sizes="<?php echo $srcsetSizes; ?>"
                            />
                        <?php else: ?>
                            <img class="post-gallery_thumb"
                                    src="<?php echo $thumbUrl ?>"
                                    data-title="<?php echo $image['title'] ?>"
                                    data-desc="<?php echo $image['desc'] ?>"
                                    alt="<?php echo $image['alt'] ?>"
                                    data-scale="<?php echo $this->option( 'thumbScale' ); ?>"/>
                        <?php endif; ?>

                    </a>
                    <div class="bg-image" style="background-image: url('<?php echo $thumbUrl; ?>');"></div>

                    <?php if ( !empty( $this->option( 'showCaptions' ) ) ): ?>
                        <?php
                        $caption = $this->getCaption( $image );
                        if ( !empty( $caption ) ): ?>
                            <figcaption class="caption-wrapper"><?php echo $caption; ?></figcaption>
                        <?php endif; ?>
                    <?php endif; ?>
                </figure>
            </div>
        <?php endforeach; ?>
    </figure>
<?php if ( $this->option( 'imageAnimation' ) ): ?>
    <script>
      jQuery(function () {
        window.registerPgImageAnimation('<?php echo $id; ?>', <?php echo $this->option( 'imageAnimationTimeBetween' ); ?>);
      });
    </script>
<?php endif; ?>


<?php if ( $this->option( 'connectedWith' ) ): ?>
    <script>
      jQuery(function ($) {
        $('#<?php echo $id; ?>.postgallery-wrapper a').each(function (index, element) {
          element = $(element);
          element.addClass('no-litebox');
          element.on('click', function (e) {
            e.preventDefault();
            $('#<?php echo $id; ?>')[0].connectedSwiper.slideTo(element.closest('.item').index() + 1);
          });
        });


        $(window).on('load', function () {
          $('#<?php echo $id; ?>')[0].connectedSwiper = document.querySelector('.elementor-element-<?php echo $this->option( 'connectedWith' ); ?> .elementor-main-swiper').swiper;
          $('#<?php echo $id; ?>')[0].connectedSwiper.on('slideChange', function () {
            setActiveSlide('<?php echo $id; ?>');
          });
          setActiveSlide('<?php echo $id; ?>');
        });
      }, jQuery);
    </script>
<?php endif; ?>