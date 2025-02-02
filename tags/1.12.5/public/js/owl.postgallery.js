/************************************
 * Author: RTO GmbH
 *
 * Last change: 29.11.2018 09:13
 ************************************/
LiteboxGallery.sliders.owl = {

  thumbDefaultArgs: {
    lazyLoad: true,
    autoWidth: true,
    dots: false,

    // owl 1
    pagination: false,
  },


  init: function(pg, galleryStartPic) {
    pg.args.sliderArgs.startPosition = galleryStartPic;
    pg.args.sliderArgs.loop = true;
    pg.args.sliderArgs.addClassActive = true;
    pg.args.sliderArgs.lazyLoad = true;

    if (pg.args.owlVersion !== 'noslider') {
      pg.galleryContainer.owlCarousel(pg.args.sliderArgs);
    }

    if (pg.args.owlVersion == 1 && galleryStartPic) { // only needed for v1
      pg.galleryContainer.data('owlCarousel').goTo(galleryStartPic);
    }
  },

  initThumbs: function(pg, pics) {
    var thumbSlider = pg.liteboxContainer.find('.thumb-container'),
      thumbArgs = jQuery.extend(LiteboxGallery.sliders.owl.thumbDefaultArgs, pg.args.owlThumbArgs);

    thumbArgs.addClassActive = true;

    // destroy old gallery
    if (pg.args.owlVersion == 1) {
      // owl v1
      if (thumbSlider.data('owlCarousel')) {
        thumbSlider.data('owlCarousel').destroy();
      }
    } else {
      // owl v2
      thumbSlider.trigger('destroy.owl.carousel');
    }
    thumbSlider.html('');
    thumbSlider.addClass('owl-carousel owl-theme');

    for (var i = 0; i < pics.length; i += 1) {
      var thumbUrl = pics[i]['url'],
        thumb = null;

      if (typeof(pics[i]['thumb']) !== 'undefined' && pics[i]['thumb'] && pics[i]['thumb'].length) {
        thumbUrl = pics[i]['thumb'];
      }

      if (thumbArgs.loop) {
        thumb = $('<div class="litebox-thumb"><img class="owl-lazy" data-src="' + thumbUrl + '" alt="" /></div>');
      } else {
        thumb = $('<div class="litebox-thumb"><img src="' + thumbUrl + '" alt="" /></div>');
      }
      thumb[0].liteboxIndex = i;
      thumb.on('click', function() {
        if (pg.args.owlVersion == 1) {
          pg.galleryContainer.data('owlCarousel').goTo(this.liteboxIndex); // v1
        } else {
          pg.galleryContainer.trigger('to.owl.carousel', this.liteboxIndex);
        }
      });
      thumbSlider.append(thumb);
    }

    // dirty hotfix
    var thumb = $('<div class="litebox-thumb placeholder"></div>');
    thumbSlider.append(thumb);

    $('.thumb-container').owlCarousel(thumbArgs);

    // jump to thumb on change
    pg.galleryContainer.on('changed.owl.carousel', function(event) {
      if (event.page.index !== null) {
        setTimeout(function() {
          $('.thumb-container').trigger('to.owl.carousel', event.page.index);
          $('.thumb-container').find('.owl-item:not(.cloned)').removeClass('current-img');
          $($('.thumb-container').find('.owl-item:not(.cloned)').get(event.page.index)).addClass('current-img');
        }, 50)
      }
    });
  },

  galleryClick: function(pg) {
    $(document).on('click', '.litebox-owlslider .owl-stage-outer, .litebox-owlslider .owl-wrapper-outer', function(e) {
      var xPos,
        yPos,
        oldOwl = $('.litebox-gallery .owl-carousel').data('owlCarousel');

      yPos = e.pageY - window.scrollY;
      xPos = e.pageX;

      if (xPos > $(document).width() / 2) {
        // next
        if (oldOwl) {
          oldOwl.next();
        } else {
          $('.litebox-gallery .owl-next').trigger('click');
        }
      } else {
        // prev
        if (oldOwl) {
          oldOwl.prev();
        } else {
          $('.litebox-gallery .owl-prev').trigger('click');
        }
      }
    });
  },

  next: function(pg) {
    var oldOwl = $('.litebox-gallery .owl-carousel').data('owlCarousel');

    if (oldOwl) {
      oldOwl.next();
    } else {
      $('.litebox-gallery .owl-next').trigger('click');
    }
  },

  prev: function(pg) {
    var oldOwl = $('.litebox-gallery .owl-carousel').data('owlCarousel');

    if (oldOwl) {
      // need double because it wont work single?!
      oldOwl.prev();
      oldOwl.prev();
    } else {
      $('.litebox-gallery .owl-prev').trigger('click');
    }
  },

  destroy: function(pg) {
    switch(pg.args.owlVersion) {
      case 1:
        // owl v1
        if (pg.galleryContainer.data('owlCarousel')) {
          pg.galleryContainer.data('owlCarousel').destroy();
        }
        break;
      case 'noslider':
        // do nothing
        break;
      default:
        // owl v2
        pg.galleryContainer.trigger('destroy.owl.carousel');
        break;
    }
  }
};