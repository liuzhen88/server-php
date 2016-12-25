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
                //banner广告位渲染
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

                //热点文字
                $("#marqueebox").html(doT.template($("#hotspotTmpl").html())(result.data.hot));
                startmarquee(15,4000,'marqueebox');

                //分类商品
                $(".classify").html(doT.template($("#classifyTmpl").html())(result.data.adv_2));

                //中部广告
                $(".adv-box").html(doT.template($("#advTmpl").html())(result.data.adv_3));

                //抢购商品渲染
                $("#panicBuyingBox").html(doT.template($("#panicTmpl").html())(result.data.goods_rush));

                //热门分销渲染
                $("#distribute").html(doT.template($("#distributeTmpl").html())(result.data.hot_distribution));

                //热门专题渲染
                $(".hot-topic").html(doT.template($("#topicTmpl").html())(result.data.special_fixed));

                //var imgArr=[];
                //Array.prototype.push.apply(imgArr,$(".lazyLoad"));
                //AGG.optimize.lazyLoadSelf(imgArr);
                echo.init({
                    offset: 10,
                    throttle: 100,
                    unload: false,
                    callback: function (element, op) {
                        //console.log(element, 'has been', op + 'ed')
                    }
                })
            }
        }
    });


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
function startmarquee(speed,delay,idName){
    var t;
    var p=false;
    var o=document.getElementById(idName);
    var lh= $('#marqueebox').find('li').height();
    o.innerHTML+=o.innerHTML;
    o.onmouseover=function(){p=true};
    o.onmouseout=function(){p=false};
    o.scrollTop = 0;
    function start(){
        t=setInterval(scrolling,speed);
        if(!p){ o.scrollTop += 1;}
    }
    function scrolling(){
        if(o.scrollTop%lh!=0){
            o.scrollTop += 1;
            if(o.scrollTop>=o.scrollHeight/2) o.scrollTop = 0;
        }else{
            clearInterval(t);
            setTimeout(start,delay);
        }
    }
    setTimeout(start,delay);
}