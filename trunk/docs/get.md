# Get image functions
These functions returns all images of a post

### get_images
Returns all images of a post (in fullsize, for smaller pics use PostGallery::get_images_resized() )
```
PostGallery::get_images( $postid );
```
Returns an array with:
'filename'
'path'
'thumbURL'

### get_images_resized
Returns all images of a post, resized
```
$args = array(
  'width' => 300,
  'height' => 300,
  'scale' => 0, // 0 = crop, 1 = scale 1:1
  'bw' => false, // optional, true returns greyscale images
}
$images = PostGallery::get_images_resized( $postid, $args );
```

### get_image_string
Return all images (resized) of a post in a json-string.
```
$args = array(
  'width' => 300,
  'height' => 300,
  'scale' => 0, // 0 = crop, 1 = scale 1:1
  'bw' => false, // optional, true returns greyscale images
  'quotes' => false,
  'singlequotes' => false
}
$images = get_image_string( $postid, $args );
```

### get_pics_resized
Resize an array of pics.
```
$args = array(
  'width' => 300,
  'height' => 300,
  'scale' => 0, // 0 = crop, 1 = scale 1:1
  'bw' => false, // optional, true returns greyscale images
}
$images = PostGallery::get_pics_resized( $pics, $args );
```


### Get titles, alt and description
```
$titles = get_post_meta ( $postid, 'postgallery_titles', true );
$descs = get_post_meta ($postid, 'postgallery_descs', true );
$alt_attributes = get_post_meta ($postid, 'postgallery_alt_attributes', true );

echo $titles[ 'filename.jpg'];
```

