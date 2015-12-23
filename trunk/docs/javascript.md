# Javascript-Functions

### Open Litebox with json-string
You give a list of images and the litebox will open with these pics
```
var picString = 'image1.jpg,image2.jpg,image3.jpg';
window.litebox.openGalleryByPics( picString );
```

### Close Litebox
```
window.litebox.closeGallery();
```

### Classes
Prevent litebox:
```
<a class="no-litebox" href="image.jpg">...</a>
```


### Callbacks
Box init
```
jQuery('#litebox-gallery').trigger('box-init', {
    state: 'before', // complete
    container: galleryContainer,
    picLinks: items,
    pics: pics,
    startPic: startPic
});
```

On close of litebox
```
jQuery('#litebox-gallery').trigger('box-close', { 
    state: 'complete'  // begin
});
```

