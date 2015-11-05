<?php
/**
 * Template Page for the gallery slider with thumbs
 * 
 * Follow variables are useable:
 *		$images
 *			-> filename, path, thumbURL
 */

$first_image = array_shift($images);
array_unshift($images, $first_image);
?>

<section class="gallery">
	<img id="gallery_image" onclick="nextImage();" style="max-width:100%;" src="<?php echo \Inc\PostGallery::get_thumb($first_image['path'], array('width'=>1024, 'height'=>768))?>" alt="<?php echo $first_image['filename']?>" />
	
	<div class="thumb_container">
		<?php
			$count = 0;
			foreach ($images as $image) {
				echo '<div class="thumb" onclick="changeImage('.$count.');">';
				echo '<img class="gallery_thumb" src="'.\Inc\PostGallery::get_thumb($image['path'], array('width'=>150, 'height'=>150)).'" alt="'.$image['filename'].'" />';
				echo '</div>';
				$count += 1;
			}
		?>
	</div>
	
	<script type="text/javascript">
		var currentPic = 0;
		var picList = [];
		<?php $count = 0; foreach ($images as $image) {?>
			picList[<?php echo $count?>] = '<?php echo \Inc\PostGallery::get_thumb($first_image['path'], array('width'=>1024, 'height'=>768))?>';


		<?php $count+= 1; }?>
		function nextImage() {
			currentPic += 1;
			if (currentPic >= picList.length) {
				currentPic = 0;
			}
			jQuery('#gallery_image').attr('src', picList[currentPic]);
		}
		function changeImage(imageNo) {
			currentPic = imageNo;
			jQuery('#gallery_image').attr('src', picList[currentPic]);
		}
	</script>
	
	<style type="text/css">
		.thumb {
			display:inline-block;
			position:relative;
			width:150px;
			margin-right:10px;
		}
	</style>
	
</section>