# Thumb-Generation

### Load thumb via url

If you want one single image you can call

http://mySite.com/?load_thumb&width=300&height=300&scale=0&path=/wp-content/uploads/myimage.jpg

If you want a list of images:

http://mySite.com/?get_thumb_list&width=300&height=300&scale=0&pics[]=/wp-content/uploads/myimage.jpg&pics[]=/wp-content/uploads/myimage2.jpg

It returns a json-string with the Urls to the cached images.


### Load thumb with function

```
$args = array(
  'width' => 300,
  'height' => 300,
  'scale' => 0, // 0 = crop, 1 = scale 1:1
  'bw' => false, // optional, true returns greyscale images
}
$thumb = PostGallery::get_thumb( $args );
```

It returns an array:
```
'content-type' => 'image/jpg',
'path'
'url'
```

