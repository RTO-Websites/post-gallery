# Create an own template

To create an own template, you only have to create a /post-gallery folder in your theme-folder:
**wp-content/themes/yourTheme/post-gallery**

There can you create a new php-file.
All image-data is in the variable **$images**

$images has the following information:

```
array(1) {
  ["image1.jpg"]=>
  array(7) {
    ["filename"]=>
    string(10) "image1.jpg"
    ["path"]=>
    string(50) "/wp-content/uploads/gallery/myGallery_47/image1.jpg"
    ["url"]=>
    string(86) "http://yourdomain.com/wp-content/uploads/gallery/myGallery_47/image1.jpg"
    ["thumbURL"]=>
    string(107) "http://yourdomain.com/?loadThumb&amp;path=/wp-content/uploads/gallery/myGallery_47/image1.jpg"
    ["title"]=>
    string(0) ""
    ["desc"]=>
    string(0) ""
    ["alt"]=>
    string(0) ""
  }
}
```

### Select own template

There are two ways. 
First you can set the template global for all posts.
The only thing you have to do, is to go in wp-admin under PostGallery and select your new template as global template.

Or you can set it for a single post only.
Go in wp-admin in your post.
Scroll down to gallery-settings. There can you select your template.

