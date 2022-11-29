var swiper = new Swiper('.swiper1', {
    slidesPerView: 4,
    spaceBetween: 10,
    preventClicks: true,
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
});

var swiper_3 = new Swiper('.swiper2', {
    slidesPerView: 4,
    slidesPerColumn: 2,
    spaceBetween: 30,
    navigation: {
        nextEl: '.swiper-button-next2',
        prevEl: '.swiper-button-prev2',
    },
});

if ($('.blog-main .swiper3')[0]) {
    var mySwiper = new Swiper ('.swiper3', {
        paginationClickable: true,
        effect: 'coverflow',
        slidesPerView: 3,
        centeredSlides: true,
        preventClicks: false,
        autoHeight: true,
        coverflow: {
            rotate: 50,
            stretch: 0,
            depth: 100,
            modifier: 1,
            slideShadows : true,
        },
        navigation: {
            nextEl: '.swiper-button-next3',
            prevEl: '.swiper-button-prev3',
        },
    });
    mySwiper.slideTo(1);
} else {
    // nothing
}

