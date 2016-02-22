# Create an own litebox template

To create an own template, you only have to create a /litebox folder in your theme-folder:
**wp-content/themes/yourTheme/litebox**

There can you create a new php-file.
The template will filled with images by javascript, so it is necessary that your template have the following base-structure:
```
<div id="litebox-gallery" class="litebox-gallery">
	<div id="litebox-owlslider" class="litebox-owlslider owl-carousel owl-theme">

	</div>
	<div class="close-button"></div>
</div>
```

If you want thumbnails in your litebox, you only have to add an element with the class "thumb-container":
```
<div id="litebox-gallery" class="litebox-gallery">
	<div id="litebox-owlslider" class="litebox-owlslider owl-carousel owl-theme">

	</div>
	<div class="close-button"></div>
	<div class="thumb-container"></div>
</div>
```

