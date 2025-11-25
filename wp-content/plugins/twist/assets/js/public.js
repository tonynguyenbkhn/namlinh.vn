(function ($) {
  'use strict';

  var cix_product_gallery_slider = {

    animation: (wpgs_js_data.slider_animation == 'true') ? true : false,
    lazyload: wpgs_js_data.slider_lazyload,
    adaptiveHeight: (wpgs_js_data.slider_adaptiveHeight == 'true') ? true : false,
    dots: (wpgs_js_data.slider_dots == 'true') ? true : false,
    dots_viewport_tablet: (wpgs_js_data.slider_dots == 'true' && wpgs_js_data.slider_dots_viewport.includes('tablet')) ? true : false,
    dots_viewport_mobile: (wpgs_js_data.slider_dots == 'true' && wpgs_js_data.slider_dots_viewport.includes('mobile')) ? true : false,
    dots_viewport_desktop: (wpgs_js_data.slider_dots == 'true' && wpgs_js_data.slider_dots_viewport.includes('desktop')) ? true : false,
    rtl: (wpgs_js_data.slider_rtl == 'true') ? true : false,
    infinity: (wpgs_js_data.slider_infinity == 'true') ? true : false,
    dragging: (wpgs_js_data.slider_dragging == 'true') ? true : false,
    nav: (wpgs_js_data.slider_nav == 'true') ? true : false,
    autoplay: (wpgs_js_data.slider_autoplay == 'true') ? true : false,
    OnHover: (wpgs_js_data.slider_autoplay_pause_on_hover == 'true') ? true : false,
    variableWidth: (wpgs_js_data.variableWidth == 1) ? true : false,
    centerMode: (wpgs_js_data.centerMode == 1) ? true : false,
    thumb_to_show: wpgs_js_data.thumb_to_show,
    carousel_mode: (wpgs_js_data.carousel_mode == 1) ? true : false,

    tp_horizontal: (wpgs_js_data.thumb_v != 'bottom') ? true : false,
    tpm_horizontal: (wpgs_js_data.thumb_position_mobile != 'bottom') ? true : false,
    tpt_horizontal: (wpgs_js_data.thumb_v_tablet != 'bottom') ? true : false,
    thumbnails_nav: (wpgs_js_data.thumbnails_nav == 1) ? true : false,


    slickGallery: function () {

      $('.wpgs-thumb').slick({
        slidesToShow: parseInt(wpgs_js_data.thumb_to_show),
        slidesToScroll: parseInt(wpgs_js_data.thumbnails_mobile_thumb_scroll_by),
        rtl: cix_product_gallery_slider.rtl,
        arrows: cix_product_gallery_slider.thumbnails_nav,
        prevArrow: '<span class="slick-prev slick-arrow" aria-label="prev"></span>',
        nextArrow: '<span class="slick-next slick-arrow" aria-label="Next"></span>',
        speed: wpgs_js_data.thumbnail_animation_speed,
        infinite: cix_product_gallery_slider.infinity,
        focusOnSelect: (cix_product_gallery_slider.carousel_mode) ? false : true,
        asNavFor: (wpgs_js_data.carousel_mode != 1) ? '.wpgs-image' : '',

        variableWidth: cix_product_gallery_slider.variableWidth,// $variableWidth
        centerMode: cix_product_gallery_slider.centerMode,// $centerMode
        vertical: cix_product_gallery_slider.tp_horizontal,
        verticalSwiping: (cix_product_gallery_slider.tp_horizontal) ? true : false,
        autoplaySpeed: wpgs_js_data.slider_autoplay_time,
        responsive: [

          {
            breakpoint: 1024,
            settings: {
              variableWidth: false,
              vertical: cix_product_gallery_slider.tpt_horizontal,
              verticalSwiping: (cix_product_gallery_slider.tpt_horizontal) ? true : false,
              rtl: cix_product_gallery_slider.rtl,
              slidesToShow: parseInt(wpgs_js_data.thumbnails_tabs_thumb_to_show),
              slidesToScroll: parseInt(wpgs_js_data.thumbnails_tabs_thumb_scroll_by),
              swipeToSlide: true,

            }
          },

          {
            breakpoint: 767,
            settings: {
              variableWidth: false,
              vertical: cix_product_gallery_slider.tpm_horizontal,
              verticalSwiping: (cix_product_gallery_slider.tpm_horizontal) ? true : false,
              rtl: cix_product_gallery_slider.rtl,
              slidesToShow: parseInt(wpgs_js_data.thumbnails_mobile_thumb_to_show),
              slidesToScroll: parseInt(wpgs_js_data.thumbnails_mobile_thumb_scroll_by),
              swipeToSlide: true,
            }
          }

        ]

      });
      if (cix_product_gallery_slider.carousel_mode) {

        return;
      }
      $('.wpgs-image').slick({

        fade: cix_product_gallery_slider.animation,
        asNavFor: '.wpgs-thumb',
        lazyLoad: cix_product_gallery_slider.lazyload,
        adaptiveHeight: cix_product_gallery_slider.adaptiveHeight,
        dots: cix_product_gallery_slider.dots_viewport_desktop,
        dotsClass: 'slick-dots wpgs-dots',
        focusOnSelect: false,
        rtl: cix_product_gallery_slider.rtl,
        infinite: cix_product_gallery_slider.infinity,
        draggable: cix_product_gallery_slider.dragging,
        arrows: cix_product_gallery_slider.nav,
        prevArrow: '<span class="slick-prev slick-arrow" aria-label="prev"></span>',
        nextArrow: '<span class="slick-next slick-arrow" aria-label="Next"></span>',
        speed: wpgs_js_data.slider_animation_speed,
        autoplay: cix_product_gallery_slider.autoplay,
        pauseOnHover: cix_product_gallery_slider.OnHover,
        pauseOnDotsHover: cix_product_gallery_slider.OnHover,
        autoplaySpeed: wpgs_js_data.slider_autoplay_time,
        responsive: [

          {
            breakpoint: 1024,
            settings: {
              dots: cix_product_gallery_slider.dots_viewport_tablet
            }
          },

          {
            breakpoint: 767,
            settings: {
              dots: cix_product_gallery_slider.dots_viewport_mobile
            }
          }

        ]
      });



    },
    lightBox: function () {
      if (typeof $.fn.fancybox == 'function') {
        // Customize icons
        var data_autostat = false;
        if (wpgs_js_data.thumb_autoStart == '1') {
          data_autostat = true;
        }
        $.fancybox.defaults = $.extend(true, {}, $.fancybox.defaults, {
          btnTpl: {

            // Arrows
            arrowLeft: '<button data-fancybox-prev class="fancybox-button fancybox-button--arrow fancybox-button--arrow_left" title="{{PREV}}">' +
              '<span class="arrow-prev"></span>' +
              "</button>",

            arrowRight: '<button data-fancybox-next class="fancybox-button fancybox-button--arrow fancybox-button--arrow_right" title="{{NEXT}}">' +
              '<span class="arrow-next"></span>' +
              "</button>",


          },
          thumbs: {
            autoStart: data_autostat,
            hideOnClose: true,
            parentEl: ".fancybox-container",
            axis: wpgs_js_data.thumb_axis
          },
          mobile: {
            clickContent: "close",
            clickSlide: "close",
            thumbs: {
              autoStart: false,
              axis: wpgs_js_data.thumb_axis
            }
          }
        });


        var selector = '.wpgs-wrapper .slick-slide:not(.slick-cloned) a';
        // fix multple thumb is if lightbox of thumbnails is on
        if (wpgs_js_data.thumbnails_lightbox == 1) {
          $('.slick-cloned').removeAttr('data-fancybox');
          var selector = '.wpgs-wrapper .slick-slide:not(.slick-cloned)';
          $('.wpgs-thumb').on('init', function (event, slick) {

            slick.$slider.find(".slick-cloned").removeAttr("data-fancybox").attr("data-trigger", slick.$slides.attr("data-fancybox")).each(function () {
              var $slide = $(this),
                clonedIndex = parseInt($slide.attr("data-slick-index")),
                originalIndex =
                  clonedIndex < 0
                    ? clonedIndex + slick.$slides.length
                    : clonedIndex - slick.$slides.length;
              $slide.attr("data-index", originalIndex);
            });
          });
        }
        // Skip cloned elements
        if (!cix_product_gallery_slider.carousel_mode) {
          $().fancybox({
            selector: selector,
            backFocus: false,

          });
        }


        // Attach custom click event on cloned elements, 
        // trigger click event on corresponding link
        $(document).on('click', '.wpgs-wrapper .slick-cloned a', function (e) {
          $(selector)
            .eq(($(e.currentTarget).attr("data-slick-index") || 0) % $(selector).length)
            .trigger("click.fb-start", {
              $trigger: $(this)
            });

          return false;
        });

      }
    },
    lazyLoad: function () {
      if (wpgs_js_data.slider_lazyload != 'disable')
        $('.wpgs-image .wpgs_image img').each(function () {
          $(this).removeAttr('srcset');
          $(this).removeAttr('sizes');

        });
    },
    misc: function () {
      var total_images = $('.wpgs-wrapper').data('item-count');
      $('#wpgs-prevent-thumbnail-shiting').remove();
      if (total_images > 2 && total_images <= wpgs_js_data.thumb_to_show && 'bottom' == wpgs_js_data.thumb_v) {
        $('head').append(`
            <style id="wpgs-prevent-thumbnail-shiting">
                @media only screen and (min-width: 767px) {
                    .wpgs-thumb .slick-track {
                        transform: inherit !important;
                    }
                }
            </style>
        `);
      }
      $('.wpgs-wrapper').hide();
      $('.wpgs-wrapper').css("opacity", "1");
      $('.wpgs-wrapper').show();
      $('.woocommerce-product-gallery__lightbox').css("opacity", "1");
      if (wpgs_js_data.lightbox_icon != 'none') {
        $('.woocommerce-product-gallery__lightbox').fadeIn();

      }
      $('.wpgs_image img').each(function () {

        // remove width and height


      });
      if (wpgs_js_data.zoom == 1) {


        $('.wpgs_image img').each(function () {
          $(this).wrap("<div class='zoomtoo-container' data-zoom-image=" + $(this).data("large_image") + "></div>");

        });

        if (wpgs_js_data.is_mobile == 1 && wpgs_js_data.mobile_zoom == 'false') {
          $('.wpgs_image > div').each(function () {
            $(this).removeClass('zoomtoo-container');
          });
        }

        // var imgUrl = $(this).data("zoom-image");
        if (typeof $.fn.zoom == 'function') {
          $('.zoomtoo-container').zoom({

            // Set zoom level from 1 to 5.
            magnify: wpgs_js_data.zoom_level,
            // Set what triggers the zoom. You can choose mouseover, click, grab, toggle.
            on: wpgs_js_data.zoom_action,
          });
        }
      }

      if (wpgs_js_data.lightbox_icon == 'none' && wpgs_js_data.zoom_action == 'mouseover') {
        $('.zoomtoo-container').on('click', function () {
          $(this).next().trigger("click");
        });

      }

      // Remove SRCSET for Thumbanils
      $('.wpgs-thumb img').each(function () {
        $(this).removeAttr('srcset', 'data-thumb_image');
        $(this).removeAttr('data-thumb_image');
        $(this).removeAttr('sizes');
        $(this).removeAttr('data-large_image');
      });

      function ZoomIconApperce() {
        setTimeout(function () {
          if (wpgs_js_data.lightbox_icon != 'none') {
            $('.woocommerce-product-gallery__lightbox').css({ "display": "block", "opacity": "1" });
          }
        }, 500);

      }

      // On swipe event
      $('.wpgs-image').on('swipe', function (event, slick, direction) {
        $('.woocommerce-product-gallery__lightbox').css({ "display": "none" });
        ZoomIconApperce();
      });
      // On edge hit
      $('.wpgs-image').on('afterChange', function (event, slick, direction) {
        ZoomIconApperce();
      });
      $('.wpgs-image').on('click', '.slick-arrow ,.slick-dots', function () {
        $('.woocommerce-product-gallery__lightbox').css({ "display": "none" });
        ZoomIconApperce();
      });
      $('.wpgs-image').on('init', function (event, slick) {
        ZoomIconApperce();
      });
      // if found prettyphoto rel then unbind click
      $(window).on('load', function () {
        $("a.woocommerce-product-gallery__lightbox").attr('rel', ''); // remove prettyphoto
        $("a.woocommerce-product-gallery__lightbox").removeAttr('data-rel'); // remove prettyphoto ("id")	
        $('a.woocommerce-product-gallery__lightbox').unbind('click.prettyphoto');

      });


    },
    resetImages: function (wrapper, parent) {

      if ($('.woosb-product-type-variable').find('.variations_form').length > 0 || $('body').find('.yith-wcpb-bundle-form').length > 0) {
        // if WPC Product Bundles for WooCommerce,YITH WooCommerce Product Bundles  active for product page
        return;
      }
      wrapper.find('.woocommerce-product-gallery').remove();
      parent.prepend(wpgs_js_data.variation_data[0]);

      cix_product_gallery_slider.lazyLoad();
      cix_product_gallery_slider.slickGallery();
      cix_product_gallery_slider.lightBox();
      cix_product_gallery_slider.misc();

    },
    variationImage: function () {
      if ($('.woosb-product-type-variable').find('.variations_form').length > 0) {
        // if WPC Product Bundles for WooCommerce is active
        return;
      }
      var variation_form = $('.variations_form'),
        i = 'input.variation_id',
        body_wrap = $('body'),
        wpgs_variation_list = wpgs_js_data.variation_data,
        DivParent = body_wrap.find('.woocommerce-product-gallery').parent();
      variation_form.on('found_variation', function (event, variation) {

        // wpgs_variation_list.hasOwnProperty(variation.variation_id)
        if (wpgs_variation_list.hasOwnProperty(variation.variation_id)) {

          body_wrap.find('.woocommerce-product-gallery').remove();
          DivParent.prepend(wpgs_variation_list[variation.variation_id]);
          cix_product_gallery_slider.lazyLoad();
          cix_product_gallery_slider.slickGallery();
          cix_product_gallery_slider.lightBox();
          cix_product_gallery_slider.misc();

        } else {


          if (variation.wavi_value) {
            // Set BlockUI on any element
            body_wrap.find('.woocommerce-product-gallery').block({
              message: null,
              overlayCSS: {
                cursor: 'none',
                background: '#fff',
                opacity: 0.6
              }
            });
            cix_product_gallery_slider.variationAjax(variation.variation_id, body_wrap, DivParent);
          } else {
            if (wpgs_js_data.thumbnails_lightbox != 1) {
              
             
              var gallery_slide_index = $('.wpgs-image').find('[data-attachment-id="' + variation.image_id + '"]').data('slick-index');
             
              if (gallery_slide_index !== undefined && wpgs_js_data.variation_mode == 'classic') {
                setTimeout(() => {
                  $('.wpgs-image').slick('slickGoTo', gallery_slide_index);
                }, 800);
                
              }else{
                $('.wpgs-image').slick('slickGoTo', 0);
                $('.wpgs-image').slick('refresh'); //TODO: need to add free version 

              }
              
             

            }

           

            $('.woocommerce-product-gallery__image .img-attr').each(function () {
              if (wpgs_js_data.zoom == 1) {
                setTimeout(() => {

                  $('.wpgs-image').slick('refresh');
                  $(this).parent().find('.zoomImg').attr("src", $(this).attr("src"));

                }, 500);
                // $(this).wrap("<div class='zoomtoo-container' data-zoom-image=" + $(this).data("large_image") + "></div>");
              }

              setTimeout(() => {
                $(this).parent().find('.wpgs-gallery-caption').html($(this).attr("title"));
              }, 500); // update caption text after 500ms if have any


            });
            

          }


        }


      })
        // On clicking the reset variation button
        .on('reset_data', function (event) {
          cix_product_gallery_slider.resetImages(body_wrap, DivParent);
         // console.log('reset_data');
        });

    },
    variationAjax: function ($variation_id, body_wrap, DivParent) {

      $.ajax({
        url: wpgs_js_data.ajax_url,
        type: 'post',
        data: {
          action: 'twist_variation_ajax',
          nonce: wpgs_js_data.ajax_nonce,
          product_id: wpgs_js_data.product_id,
          variation_id: $variation_id

        },

        success: function (res) {

          body_wrap.find('.woocommerce-product-gallery').remove();
          DivParent.prepend(res.data.variation_images);

          cix_product_gallery_slider.lazyLoad();
          cix_product_gallery_slider.slickGallery();
          cix_product_gallery_slider.lightBox();
          cix_product_gallery_slider.misc();
        },
        error: function () {
          console.log('Ajax Error: variationAjax');
        }
      });
    }
  };

  $(document).ready(function () {

    cix_product_gallery_slider.lazyLoad();
    cix_product_gallery_slider.slickGallery();
    cix_product_gallery_slider.lightBox();
    cix_product_gallery_slider.misc();

    cix_product_gallery_slider.variationImage();
   
    
  });



})(jQuery);

// Other code using $ as an alias to the other library