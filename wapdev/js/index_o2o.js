if (typeof AGG == "undefined") {
    var AGG = {};
}


$(function () {
    //判断是否是微信打开
    //if (!AGG.isWeixin()) {
    //    return;
    //}

    FastClick.attach(document.body);

    AGG.latitude = localStorage.getItem("latitude");
    AGG.longitude = localStorage.getItem("longitude");
    AGG.cityname = localStorage.getItem("cityname");
    AGG.district = localStorage.getItem("district");
    var setCity = request("cityname") || localStorage.getItem("setcity");

    //判断用户是否手动选择过城市
    if (setCity && (setCity != "undefined")) {
        ajaxDataTmpl(setCity, '','31.337882', '120.616634');
        localStorage.setItem("setcity", setCity);
    //取上次定位过的城市位置
    } else if (AGG.latitude && AGG.longitude && AGG.cityname) {
        ajaxDataTmpl(AGG.cityname,AGG.district, AGG.latitude, AGG.longitude);
        AGG.getLocation.refresh();
    //获取经纬度和城市
    } else {
        $(".get-location").show();
        AGG.getLocation.latAndLon(
            function (data) {
                AGG.getLocation.cityname(data.latitude, data.longitude, function (datas) {
                    $(".get-location").hide();
                    ajaxDataTmpl(datas.cityname,AGG.district, datas.latitude, datas.longitude);
                });
            },
            function () {
                AGG.getLocation.setDefaultCity(
                    function (defaultData) {
                        $(".get-location").hide();
                        ajaxDataTmpl(defaultData.cityname,defaultData.district,defaultData.latitude, defaultData.longitude);
                    }
                );
            });
    }

    addcookie("lat", AGG.latitude);
    addcookie("lng", AGG.longitude);
    addcookie("cityname", AGG.cityname);
    addcookie("district", AGG.district);

    //获取分类图标
    $.ajax({
        url:ApiUrl +"/index.php?act=unlimited_invitation&op=get_class_in_home",
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(data){
            if(data.code==200){
                var typeDoTmpl = doT.template($("#typePBox-tmpl").html());
                $(".type ul").html(typeDoTmpl(data));
            }
        }
    });


    function ajaxDataTmpl(cityname, district_name,latitude, longitude) {
        $("#get_area").html(cityname);
        if(cityname == '苏州市'){
            $('.share-module').show();
            //$('.paotuib-module').show();
        }else{
            $('.share-module').hide();
            //$('.paotuib-module').hide();
        }
        //轮播图和专题推荐
        $.ajax({
            url: ApiUrl + "/index.php?act=index&op=special&special_id=2&client_type=wap&city_name="+cityname+"&district_name="+district_name,
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if (data.code == 200) {
                    //轮播图
                    var swiperDoTmpl = doT.template($("#swiper-tmpl").html());
                    $(".swiper-wrapper").html(swiperDoTmpl(data.data[0].adv_list.item)).removeClass("loading");
                    //专题推荐
                    var specialDoTmpl = doT.template($("#special-tmpl").html());
                    $(".topics .list").html(specialDoTmpl(data.data[1].home3.item)).removeClass("loading");
                    $('.swiper-container').each(function() {
                        if ($(this).find('.swiper-slide').length < 2) {
                            return;
                        }
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

                }
            }
        });
        //今日推荐
        $.ajax({
            url: ApiUrl + "/index.php?act=unlimited_invitation&op=get_random_products&city_name=" + cityname + "&client_type=wap&limit=4",
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (datap) {
                if (datap.code == 200) {
                    datap.latitude = latitude;
                    datap.longitude = longitude;
                    var todayDoTmpl = doT.template($("#today-tmpl").html());
                    $("#todayList").html(todayDoTmpl(datap)).removeClass("loading");
                }
            }
        });
        //猜你喜欢
        $.ajax({
            url: ApiUrl + "/index.php?act=unlimited_invitation&op=guess_your_like_goods&lat=" + latitude + "&lng=" + longitude + "&client_type=wap&city_name=" + cityname + "&curpage=1",
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if (data.code == 200) {
                    data.latitude = latitude;
                    data.longitude = longitude;
                    var likeDoTmpl = doT.template($("#like-tmpl").html());
                    $(".like ul").html(likeDoTmpl(data)).removeClass("loading");
                }
            }
        });
    }


    window.onscroll = function () {
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        if (scrollTop > 100) {
            $(".head_bg").css({opacity: '1'});
        } else if (scrollTop < 100) {
            $(".head_bg").css({opacity: '0.3'});
        }
    };



    AGG.weichatShareCustom.init({
            title:"爱个购",
            link:"http://shop.aigegou.com/agg/wap/index_o2o.html",
            imgUrl:"http://shop.aigegou.com/agg/wap/images/smallLogo.png",
            desc:"中国第一家本土化生活消费购物手机分享平台，全新的商业模式，全新的消费理念，全方位的平台支持。"
        });

});



