# Shortcodes

### PostGallery
```
[postgallery postlist=X,Y,Z template=my_template]
```
Insert the PostGallery.
Optional with custom-template. 

### Swapper
```
[slider] -> Bindet einen 500x300 Slider ein.
[slider width=600 height=200] -> Ändert die Größe auf 600x200px
[slider fullsize] -> Bindet den Slider mit 100% Höhe und Breite ein.
[slider notitle] -> Deaktiviert die Anzeige der Titel/Beschreibung.
[slider noimagetitle] -> Verwende Galerie-Titel und Beschreibung anstelle des Bild-Titels und Beschreibung.
[slider scale=0] -> Ändert die Skalierung der Bilder (0, 1, 2, 3, 5) 
```

Animation
```
[slider config=animateOut:'fadeOut',animateIn:'fadeIn] -> Wechselt die Animation auf fade anstatt slide
```
You can use a lot of different animations. It use animate.css.
http://daneden.github.io/animate.css/

Also every option of Owl-Carousel is usable:
http://www.owlcarousel.owlgraphic.com/


Slider
```
[slider slideshow=0] -> Deaktiviert die Slideshow.
[slider slideshow=10.0] -> Ändert die Dauer bis zum nächsten Bild in Sekunden.
```

Mediathek-Bilder einbinden:
```
[slider media] -> Läd alle Bilder aus der Mediathek.
```

Mediathek-Zusatzparameter
```
[slider media mediaitems=10] -> Maximal 10 Bilder.
[slider media medialist=210,201,129] -> Zeigt nur die Bilder 210, 201, 129 an.
[slider media mediaexlude=denied medialist=210,201,129] -> Zeigt alle Bilder ausser 210, 201, 129 an.
[slider media parent=120] -> Zeigt nur Bilder von Post 120 an.
[slider media mediaslug=blub] -> Zeigt nur Bilder vom Slug 'blub' an.
```

Binde Bilder aus der Post-Gallery ein:
```
[slider postlist=27,34,10] ->Zeigt alle Bilder aus den Beiträgen 27, 34 und 10.
```

Eigener Content
```
[slider content='<div class="tab">>Item 1</div><div class="tab">Item 2</div>']
```

Youtube-Video wird wiederholt
```
[slider youtube=SWP123456,SWP123457,SWP123458] -> Binde Youtube-Videos anhand der ID ein
[slider ytautoplay] -> Youtube-Video spielt sich automatisch ab
[slider ytloop] -> Youtube-Video wird wiederholt
[slider ythtml5] -> Erzwinge HTML5-Videos
[slider ytnocontrols] -> Verstecke Kontrollleiste
```