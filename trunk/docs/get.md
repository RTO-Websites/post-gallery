# Get image functions
These functions returns all images of a post

### getImages
Returns all images of a post (in fullsize, for smaller pics use PostGallery::getImagesResized() )
```
PostGallery::getImages( $postid );
```
Returns an array with:
'filename'
'path'
'thumbURL',
'alt',
'title',
'desc'

### getImagesResized
Returns all images of a post, resized
```
$args = array(
  'width' => 300,
  'height' => 300,
  'scale' => 0, // 0 = crop, 1 = scale 1:1
  'bw' => false, // optional, true returns greyscale images
}
$images = PostGallery::getImagesResized( $postid, $args );
```

Returns an array with:
'filename'
'path'
'url',
'width',
'height',
'alt',
'title',
'desc'

### getImageString
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
$images = getImageString( $postid, $args );
```

### getPicsResized
Resize an array of pics.
```
$args = array(
  'width' => 300,
  'height' => 300,
  'scale' => 0, // 0 = crop, 1 = scale 1:1
  'bw' => false, // optional, true returns greyscale images
}
$images = PostGallery::getPicsResized( $pics, $args );
```

Returns an array with:
'filename'
'path'
'url',
'width',
'height'


### Get titles, alt and description
```
$titles = get_post_meta ( $postid, 'postgalleryTitles', true );
$descs = get_post_meta ($postid, 'postgalleryDescs', true );
$altAttributes = get_post_meta ($postid, 'postgalleryAltAttributes', true );

echo $titles[ 'filename.jpg'];
```

