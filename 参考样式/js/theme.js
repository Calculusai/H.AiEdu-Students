(function ($) {
  "use strict";

  // Mobile Menu
  $(".mobile-menu nav").meanmenu({
    meanScreenWidth: "991",
    meanMenuContainer: ".mobile-menu",
    meanMenuOpen: "<span></span> <span></span> <span></span>",
    onePage: false,
  });

  // Outer box

  $(document).on("click", ".search-box-outer", function () {
    $("body").addClass("search-active");
  });
  $(document).on("click", ".close-search", function () {
    $("body").removeClass("search-active");
  });

// 

  function throttle(func, limit) {
    let lastFunc;
    let lastRan;
    return function () {
      const context = this,
        args = arguments;
      if (!lastRan) {
        func.apply(context, args);
        lastRan = Date.now();
      } else {
        clearTimeout(lastFunc);
        lastFunc = setTimeout(function () {
          if (Date.now() - lastRan >= limit) {
            func.apply(context, args);
            lastRan = Date.now();
          }
        }, limit - (Date.now() - lastRan));
      }
    };
  }

  // Use this instead of _.throttle
  $(window).on(
    "scroll",
    throttle(function () {
      var scroll = $(this).scrollTop();
      $("#sticky-header").toggleClass("sticky", scroll >= 100);
    }, 200)
  );

  $(window).on("load", function () {
    $("body").addClass("loaded");
    $(".loader").fadeOut(500); // Hide the loader smoothly
  });

  // Venubox
  $(".venobox").venobox({
    numeratio: true,
    infinigall: true,
  });

  // Loader

  

  //Wow Js
  new WOW().init();

  // counterUp
  $(".counter").counterUp({
    delay: 10,
    time: 1000,
  });
})(jQuery);
