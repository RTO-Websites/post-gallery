# Thumb-Generation

### Load thumb via url

If you want one single image you can call

http://mySite.com/?loadThumb&width=300&height=300&scale=0&path=/wp-content/uploads/myimage.jpg

### If you want a list of images:

http://mySite.com/?getThumbList&width=300&height=300&scale=0&pics[]=/wp-content/uploads/myimage.jpg&pics[]=/wp-content/uploads/myimage2.jpg

It returns a json-string with the cached images (url, width, height and path)


### Load thumb with function

```
$args = array(
  'width' => 300,
  'height' => 300,
  'scale' => 0, // 0 = crop, 1 = scale 1:1
  'bw' => false, // optional, true returns greyscale images
}
$thumb = Inc\PostGallery::getThumb( $args );
```

It returns an array:
```
'content-type' => 'image/jpg',
'path'
'url',
'width',
'height'
```

