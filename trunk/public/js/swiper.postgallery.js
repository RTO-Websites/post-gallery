/**
 * Created by shennemann on 06.06.2017.
 */

/**
 * Created by shennemann on 22.11.2016.
 */


LiteboxGallery.sliders.swiper = {
  init: function (pg, galleryStartPic) {
    pg.args.sliderArgs.lazyLoading = true;
    pg.galleryContainer.append('<div class="swiper-wrapper" />');

    if (pg.args.sliderArgs == 1) {
      pg.args.sliderArgs = 3000;
    }

    pg.galleryContainer.swiper(pg.args.sliderArgs);
  },

  initThumbs: function (pg, pics) {
  },

  galleryClick: function (pg) {
    $(document).on('click', '.litebox-owlslider .swiper-wrapper, .litebox-owlslider .swiper-wrapper', function (e) {
      var xPos,
        yPos;

      yPos = e.pageY - window.scrollY;
      xPos = e.pageX;

      if (xPos > $(document).width() / 2) {
        // next
        pg.galleryContainer.slideNext();
      } else {
        // prev
        pg.galleryContainer.slidePrev();
      }
    });
  },

  next: function (pg) {
    pg.galleryContainer.slideNext();
  },

  prev: function (pg) {
    pg.galleryContainer.slidePrev();
  },

  destroy: function (pg) {

  }
};