# Litebox
If litebox is enabled, it will open all hrefs to images in the litebox.

It search also in the parent (or ‘.gallery’) for more hrefs to images and add that to the litebox, so you can easily create a litebox-gallery.

Example:
```
<section class="gallery">
  <a href="/wp-content/uploads/image1.jpg">Bild 1</a><br />
  <a href="/wp-content/uploads/image2.jpg">Bild 2</a><br />
  <a href="/wp-content/uploads/image3.jpg">Bild 3</a><br />
  <a href="/wp-content/uploads/image4.jpg">Bild 4</a><br />
</section>
```
If you click one of these links, it will open the litebox with the four images.

### Open with javascript
You can also open the litebox with an array of pics
```
litebox.openGalleryByPics( [
    'wp-content/uploads/image1.jpg', 
    'wp-content/uploads/image2.jpg'
] );
```

### Combine with PostGallery
You can load the image list from PortGallery and then open the litebox
```
<?php
  $images = \Inc\PostGallery::getImageString( $post_id );
?>
 
<div onclick="litebox.openGalleryByPics( <?php echo $images; ?> ) ">Blub</div>
```
