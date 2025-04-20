(function ($) {
    'use strict';
// 更多下载：https://www.bootstrapmb.com 

     //Teasti List Home One
    $('.testi-list-1').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 10000,
        dots: true,
        nav:true,
        navText: ["<i class='bi bi-arrow-left''></i>", "<i class='bi bi-arrow-right''></i>"],
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 1
            },
            600:{
                items:1
            },
            768: {
                items: 1
            },
            992: {
                items: 1
            },
            1000: {
                items: 1
            },
            1920: {
                items: 1
            }
        }
    }) 

    //Teasti List Home two
    $('.testi-list-2').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 10000,
        dots: true,
        nav:false,
        margin:10,
        navText: ["<i class='bi bi-arrow-left''></i>", "<i class='bi bi-arrow-right''></i>"],
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 1
            },
            600:{
                items:1
            },
            768: {
                items: 2
            },
            992: {
                items: 2
            },
            1000: {
                items: 3
            },
            1920: {
                items: 4
            }
        }
    }) 

     //Teasti List Home three
     $('.testi-list-3').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 10000,
        dots:false,
        nav:true,
        margin:30,
        navText: ["<i class='bi bi-arrow-left''></i>", "<i class='bi bi-arrow-right''></i>"],
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 1
            },
            600:{
                items:1
            },
            768: {
                items: 1
            },
            992: {
                items: 2
            },
            1000: {
                items: 2
            },
            1920: {
                items: 2
            }
        }
    }) 


     //Brand List Home One
    $('.brand-list-1').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 10000,
        dots: false,
        nav: false,
        navText: ["<i class='bi bi-arrow-left''></i>", "<i class='bi bi-arrow-right''></i>"],
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 2
            },
            600:{
                items:2
            },
            768: {
                items: 3
            },
            992: {
                items: 4
            },
            1000: {
                items: 5
            },
            1920: {
                items: 6
            }
        }
    }) 

       //Brand List Home three
       $('.brand-list-2').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 10000,
        dots: false,
        nav: false,
        navText: ["<i class='bi bi-arrow-left''></i>", "<i class='bi bi-arrow-right''></i>"],
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 2
            },
            600:{
                items:2
            },
            768: {
                items: 3
            },
            992: {
                items: 4
            },
            1000: {
                items: 5
            },
            1920: {
                items: 5
            }
        }
    }) 

     //project list Home two
     $('.project-list').owlCarousel({
        loop: true,
        dots: false,
        nav: false,
        margin:30,
        navText: ["<i class='bi bi-arrow-left'></i>", "<i class='bi bi-arrow-right'></i>"], // Fixed extra single quote
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 1
            },
            600: {
                items: 1
            },
            768: {
                items: 2
            },
            992: {
                items: 3
            },
            1000: {
                items: 3
            },
            1365: {
                items: 4
            },
            1920: {
                items: 4
            }
        }
        
        
    });

     //Blog List Home two
     $('.blog-list-2').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 10000,
        dots: false,
        nav:true,
        margin:30,
        navText: ["<i class='bi bi-arrow-left''></i>", "<i class='bi bi-arrow-right''></i>"],
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 1
            },
            600:{
                items:1
            },
            768: {
                items: 2
            },
            992: {
                items: 2
            },
            1000: {
                items: 3
            },
            1920: {
                items: 3
            }
        }
    }) 

      //Blog List Home three
      $('.blog-list-3').owlCarousel({
        loop: true,
        autoplay: true,
        autoplayTimeout: 10000,
        dots:true,
        nav:false,
        margin:30,
        navText: ["<i class='bi bi-arrow-left''></i>", "<i class='bi bi-arrow-right''></i>"],
        responsive: {
            0: {
                items: 1
            },
            480: {
                items: 1
            },
            600:{
                items:1
            },
            768: {
                items: 2
            },
            992: {
                items: 2
            },
            1000: {
                items: 3
            },
            1920: {
                items: 3
            }
        }
    }) 

    // Portfolio Isotope 
$('.image_load').imagesLoaded(function () {

    if ($.fn.isotope) {

        var $portfolio = $('.image_load');

        $portfolio.isotope({

            itemSelector: '.grid-item',

            filter: '*',

            resizesContainer: true,

            layoutMode: 'masonry',

            transitionDuration: '0.8s'

        });
        $('.menu-filtering li').on('click', function () {

            $('.menu-filtering li').removeClass('current_menu_item');

            $(this).addClass('current_menu_item');

            var selector = $(this).attr('data-filter');

            $portfolio.isotope({

                filter: selector,

            });

        });

    };
   
       

});
    
})(jQuery);

