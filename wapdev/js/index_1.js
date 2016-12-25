if (typeof AGG == "undefined") {
    var AGG = {};
}

$(function () {
    FastClick.attach(document.body);

    if(!(getcookie("client_type")=="android" || (getcookie("client_type")).toLowerCase()=="ios")){
        $(".main").css("margin-bottom","44px");
    }

    $.ajax({
        url: ApiUrl + "/index.php?act=home&op=online&client_type=wap&limit_goods=5&limit_special=2",
        type: 'get',
        dataType: 'jsonp',
        success: function (result) {
            if (result.code == 200) {
                //首屏banner渲染
                var swipeDoTmpl = doT.template($("#swipe-tmpl").html());
                $("#swipeBox").html(swipeDoTmpl(result.data.adv));
                $('.adv_list').each(function () {
                    if ($(this).find('.item').length < 2) {
                        return;
                    }
                    Swipe(this, {
                        startSlide: 2,
                        speed: 400,
                        auto: 3000,
                        continuous: true,
                        disableScroll: false,
                        stopPropagation: false,
                        callback: function (index, elem) {
                        },
                        transitionEnd: function (index, elem) {
                        }
                    });
                });

                //抢购商品渲染
                var panicDoTmpl = doT.template($("#panic-tmpl").html());
                $("#panicBuyingBox").html(panicDoTmpl(result.data.goods_recommed));
                $(".hot_img").height($(".hot_img").width());

                //专题渲染
                var specialDoTmpl = doT.template($("#special-tmpl").html());
                $("#specialBox").html(specialDoTmpl(result.data.special_fixed));
                $(".img2").height($(".img2").width() * 3 /5);
                var swiper = new Swiper('.y-swiper-container', {
                    pagination: '.swiper-pagination',
                    slidesPerView: 2,
                    paginationClickable: true,
                    spaceBetween: 10,
                    freeMode: true
                });
            }
        }
    });

    //猜你喜欢随机模板
    var actNumber = 0;
    var yLIndex;
    var yLProductNum = [9, 9, 9, 7, 10, 12];
    var yLActiveNum = [0, 0, 0, 1, 1, 0];
    var eliminate_goods = "",eliminate_special = "";

    $(window).scroll(function () {
        var scrollTop = $(window).scrollTop();
        var scrollHeight = $(document).height();
        var windowHeight = $(window).height();
        if (scrollTop + windowHeight == scrollHeight) {
            scrollForYouLike();
        }
    });

    function likeItemCSS() {
        $(".like-box-1l .like-item").height($(".like-box-1l .like-item").width());
        $(".like-box-1l .like-item-2l").height($(".like-box-1l .like-item").width());
        $(".like-box-2l .like-left .like-item").height($(".like-box-2l .like-left .like-item").width());
        $(".like-box-2l .like-right .like-item").height($(".like-box-2l .like-right .like-item").width());
        $(".like-box-3l .like-left .like-item").height($(".like-box-3l .like-left .like-item").width());
        $(".like-box-3l .like-right .like-item").height($(".like-box-3l .like-right .like-item").width());
    }

    function getRandomN() {
        var randomNumber = Math.floor(Math.random() * 6);
        if ((actNumber >= 5 && (randomNumber == 3 || randomNumber == 4)) || randomNumber == undefined) {
            getRandomN();
        } else {
            if (randomNumber == 3 || randomNumber == 4) {
                actNumber = actNumber + 1;
            }
            return randomNumber;
        }
    }

    function scrollForYouLike() {
        yLIndex = getRandomN();
        $(".get-more-box").text("加载中...");
        $.ajax({
            url: ApiUrl + "/index.php?act=home&op=online_goods_special_random&client_type=wap&limit_goods="+yLProductNum[yLIndex]+"&limit_special=" + yLActiveNum[yLIndex],
            type: "post",
            data: {"eliminate_goods": eliminate_goods,"eliminate_special": eliminate_special},
            dataType: "json",
            success: function (imgData) {
                if(yLProductNum[yLIndex] != imgData.data.goods_random.length){
                    $(".get-more-box").text("");
                }
                if (imgData.code == 200 && yLProductNum[yLIndex] == imgData.data.goods_random.length&&yLActiveNum[yLIndex]==imgData.data.special_random.length) {
                    var tmplNum = '#like-tmpl-' + (yLIndex + 1);
                    $(imgData.data.goods_random).each(function (indexG, thisG) {
                        eliminate_goods += thisG.goods_commonid + ",";
                    });
                    //取专题
                    if (yLActiveNum[yLIndex] > 0) {
                        $(imgData.data.special_random).each(function (indexS, thisS) {
                            eliminate_special += thisS.special_id + ",";
                        });
                    }
                    var likeDoTmpl = doT.template($(tmplNum).html());
                    $("#likeBox").append(likeDoTmpl(imgData.data));
                    likeItemCSS();

                }
            }
        });
    }

    var key = request("key");
    var version = request("version_name");
    if (version) {
        version = version.match(/\d+/g).join('');
        addcookie("appVersion", version);
    } else {
        version = getcookie('version');
    }

    AGG.client.type();
    if (AGG.client.isApp()) {
        addcookie("appVersion", version);
        $('#footer').hide();
        $('.new-header').css({'padding-top': '20px', 'display': 'none'});
        $('.new-header-margin').css('padding-top', '64px');
        $(".new-header-app").css("display", "block");
    } else {
        $('#footer').show();
        $(".new-header-app").css("display", "none");
        $(".new-header").css("display", "block");
    }

    $(".header-menuBtn").click(function () {
        if (AGG.client.type() == "ios") {
            pop();
        } else if (AGG.client.type() == "android") {
            app.pop();
        } else {
            window.history.back();
        }
    });

    if (key == '' || key == 'undefined') {
        key = getcookie("key");
    } else {
        addcookie("key", key);
    }
});
