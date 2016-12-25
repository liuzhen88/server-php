$(function() {
    $('.adv_list').each(function() {
        Swipe(this, {
            startSlide: 2,
            speed: 400,
            auto: 3000,
            continuous: true,
            disableScroll: false,
            stopPropagation: false,
            callback: function(index, elem) {},
            transitionEnd: function(index, elem) {}
        });
    });
});
