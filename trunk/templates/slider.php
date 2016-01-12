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
    <img id="gallery_image" onclick="nextImage();" style="max-width:100%;"
        src="<?php echo \Inc\PostGallery::getThumbUrl( $first_image[ 'path' ], array( 'width' => 1024, 'height' => 768 ) ) ?>"
        alt="<?php echo $first_image[ 'filename' ] ?>"/>
    <script type="text/javascript">
        var currentPic = 0;
        var picList = [];
        <?php $count = 0; foreach ($images as $image) {?>
        picList[<?php echo $count?>] = '<?php echo \Inc\PostGallery::getThumbUrl($image['path'], array('width'=>1024, 'height'=>768))?>';


        <?php $count+= 1; }?>
        function nextImage() {
            currentPic += 1;
            if (currentPic >= picList.length) {
                currentPic = 0;
            }
            jQuery('#gallery_image').attr ('src', picList[currentPic]);
        }
    </script>
</figure>