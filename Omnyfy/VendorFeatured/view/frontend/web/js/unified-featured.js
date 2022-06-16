define(
    ['jquery', 'owl.carousel/owl.carousel.min'],
    function ($) {
        return function (config) {
            var element = config.elementClass;

            var widthScreen = (window.innerWidth > 0) ? window.innerWidth : screen.width;
            var qtyLoopItems = 2;
            var loop = config.options.loop;
            if (widthScreen >= 1200){
                qtyLoopItems = config.options.responsive.items_1200;
            }else if(992 <= widthScreen < 1200){
                qtyLoopItems = config.options.responsive.items_992;
            }else if(768 <= widthScreen < 992){
                qtyLoopItems = config.options.responsive.items_768;
            }else if(640 <= widthScreen < 768){
                qtyLoopItems = config.options.responsive.items_640;
            }else{
                qtyLoopItems = config.options.responsive.items_0;
            }

            if (qtyLoopItems >= config.options.totalVendor){
                loop = !1;
            }

            $(element).owlCarousel({
                autoplay: config.options.autoplay,
                autoplayTimeout: config.options.autoplayTimeout,
                autoplayHoverPause: config.options.autoplayHoverPause,
                margin: config.options.margin,
                nav: config.options.nav,
                navText: [config.options.navTextPrev, config.options.navTextNext],
                dots:config.options.dots,
                loop: loop,
                responsive: {
                    0: {
                        items: config.options.responsive.items_0
                    },
                    640: {
                        items: config.options.responsive.items_640
                    },
                    768: {
                        items: config.options.responsive.items_768
                    },
                    992: {
                        items: config.options.responsive.items_992
                    },
                    1200: {
                        items: config.options.responsive.items_1200
                    }
                }
            });

        }
    }
);