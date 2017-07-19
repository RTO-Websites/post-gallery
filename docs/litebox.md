# Litebox
If litebox is enabled, it will open all hrefs to images in the litebox.

It search also in the parent (or ‘.gallery’) for more hrefs to images and add that to the litebox, so you can easily create a litebox-gallery.
It looks also for the attribute data-pg-gallery in gallery-container.
Useful if you have pagination for thumbnails.

Example:
```
<figure class="gallery">
  <a href="/wp-content/uploads/image1.jpg">Image 1</a><br />
  <a href="/wp-content/uploads/image2.jpg">Image 2</a><br />
  <a href="/wp-content/uploads/image3.jpg">Image 3</a><br />
  <a href="/wp-content/uploads/image4.jpg">Image 4</a><br />
</figure>
```
If you click one of these links, it will open the litebox with the four images.

You can add title and description to every image, litebox will show it in .pic-title and .pic-desc:
```
 <a href="/wp-content/uploads/image1.jpg" data-title="MyImageTitle" data-desc="My Description">Image 1</a>
```


### Open with data-pg-gallery
```
<figure class="gallery" data-pg-gallery='<?php echo json_encode($images); ?>'>
  <a href="/wp-content/uploads/image1.jpg">Image 1</a><br />
  <a href="/wp-content/uploads/image2.jpg">Image 2</a><br />
  <a href="/wp-content/uploads/image3.jpg">Image 3</a><br />
  <a href="/wp-content/uploads/image4.jpg">Image 4</a><br />
</figure>
```


### Open with javascript
You can also open the litebox with an array of pics
```
litebox.openGalleryByPics( [
    'wp-content/uploads/image1.jpg', 
    'wp-content/uploads/image2.jpg'
] );
```

Also with image title and description:
```
litebox.openGalleryByPics( [
    {
      url: 'wp-content/uploads/image1.jpg',
      title: 'MyImageTitle',
      desc: 'My Image-Description'
    },
    {
      url: 'wp-content/uploads/image2.jpg',
      title: 'MyImageTitle2',
      desc: 'My Image-Description'
    },
] );
```

### Combine with PostGallery
You can load the image list from PostGallery and then open the litebox
```
<?php
  $images = \Inc\PostGallery::getImageString( $post_id );
?>
 
<div onclick="litebox.openGalleryByPics( <?php echo $images; ?> ) ">Blub</div>
```

Or open it with image title and description:
```
<?php
  $images = \Inc\PostGallery::getImages( $post_id );
?>
 
<div onclick="litebox.openGalleryByPics( <?php echo json_encode( $images ); ?> ) ">Blub</div>
```