var type;
var key;

$(function () {

    key = request("key") || getcookie('key');

    //商品详情新头部
    $(".p_header").html([
        '<a  class="p-goback" href="javascript:history.back();"><img src="../images/icon-back.png"/> </a>',
        '<a href="' + WapSiteUrl + '/tmpl/shoppingCart.html"  class="p-shopcart"><img src="../images/icon-shopping-cart.png"/> </a>',
        '<a href="' + WapSiteUrl + '/tmpl/search.html"  class="p-psearch"><img src="../images/icon-search.png"/> </a>'//,
    ].join(''));

    if(request('backbtn') =='pop'){
        $('.p-goback').attr('href','javascript:;').on('click',function(){
            if (AGG.client.type() == "ios") {
                pop();
            } else if (AGG.client.type() == "android") {
                app.pop();
            }else{
                window.history.back();
            }
        });
    }

    $(window).scroll(function () {
        if ($(window).scrollTop() >= $(window).width()) {
            $(".p_header").css("background", "#ef5350");
        } else if ($(window).scrollTop() < $(window).width()) {
            $(".p_header").css("background", "url('../images/zhezhao_p_head.png') repeat-x");
            if (AGG.client.isApp()) {
                $(".p_header").css("background-size", "auto 60px");
            } else {
                $(".p_header").css("background-size", "auto 44px");
            }
        }
    });

    $("#header").html(['<div class="header-wrap">',
        '<a href="javascript:history.back();" class="header-back"><span>返回</span></a>',
        '<h2><span id="headerTitle" style="display:none;">' + document.title + '</span></h2>',
        '<a href="javascript:;" class="header-nav_2"><span></span></a>',
        '<a href="javascript:;" class="header-nav_3"><span></span></a></div>'
    ].join(''));

    if (AGG.client.type() == "ios") {
        $(".header-off").click(function () {
            pop();
        });
    } else if (AGG.client.type() == "android") {
        $(".header-off").click(function () {
            app.pop();
        });
    } else {
        $(".header-off").click(function () {
            window.location.href = WapSiteUrl + "/index_o2o.html";
        });
    }

    if (AGG.client.isApp()) {
        $('#headerTitle').show();
        $(".p_header").addClass("p_header_app");
        $('#main-container').css('padding', '64px 0 0 0');
        $('#main-container2').css('padding', '64px 0 0 0');
        $(".header-wrap").css({"background-color": "#ef5350", "opacity": 1,'padding-top':'20px'});
    } else {
        $('#header').hide();
    }
});


