# Javascript-Functions

### Open Litebox with string or array
You give an array or string of images and the litebox will open with these pics
```
var picString = 'image1.jpg,image2.jpg,image3.jpg';
window.litebox.openGalleryByPics( picString );
```
or with an array
```
var pics = ['image1.jpg', 'image2.jpg', 'image3.jpg'];
window.litebox.openGalleryByPics( pics );
```


### Open Litebox from data-attribute
All elements with the data-pgimages Attribute will open a litebox
```
<div data-pgimages="image1.jpg,image2.jpg,image3.jpg">
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

