# Javascript-Functions

### Open Litebox with json-string
You give a list of images and the litebox will open with these pics
```
var picString = 'image1.jpg,image2.jpg,image3.jpg';
liteboxGalleryInstance.openGalleryByPics( picString );
```

### Close Litebox
```
liteboxGalleryInstance.closeGallery();
```

### Classes
Prevent litebox:
```
<a class="no-litebox" href="image.jpg">...</a>
```


### Callbacks
Box init
```
jQuery('#gallery_image').trigger('box-init', {
    state: 'before', // complete
    container: galleryContainer,
    picLinks: items,
    pics: pics,
    startPic: startPic
});
```

On arrow-click:
```
jQuery('#gallery_image').trigger('box-swap', {
    state: 'complete',
    direction: 'prev' // 'next'
});
```

On touch (start, move, end)
```
jQuery('#gallery_image').trigger('box-touch', {
    state: 'start', // 'move', 'end'
    event: event
});
```

On close of litebox
```
jQuery('#gallery_image').trigger('box-close', { 
    state: 'complete'  // begin
});
```

