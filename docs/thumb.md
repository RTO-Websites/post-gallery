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
  'blur' => array // optional, only for imagick
  'ownFunc' => string
}
$thumb = Inc\PostGallery::getThumb( $path, $args );
```

It returns an array:
```
'content-type' => 'image/jpg',
'path'
'url',
'width',
'height'
```

If you use blur, look at
http://php.net/manual/en/imagick.blurimage.php


If you have imagick, you can use ownFunc to write custom imagick-filters, like this:

```
function filterArticleBg( $im ) {
    $im->colorizeImage("#ea6D33",1);

    return $im;
}
$args = array(
  'width' => 300,
  'height' => 300,
  'ownFunc' => 'filterArticleBg'
}
$thumb = Inc\PostGallery::getThumb( $path, $args );
```