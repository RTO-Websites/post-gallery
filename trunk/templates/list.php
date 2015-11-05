<?php
/**
 * Template Page for the gallery list
 * 
 * Follow variables are useable:
 *		$images
 *			-> filename, path, thumbURL
 */
?>

<section class="gallery">
	<?php foreach ($images as $image) { ?>
		<a href="<?php echo $image['path']?>">
			<img style="max-width:100%;" src="<?php echo \Inc\PostGallery::get_thumb($image['path'], array('width'=>1024, 'height'=>768)) ?>" alt="<?php echo $image['filename']?>" />
		</a>
	<?php } ?>
</section>