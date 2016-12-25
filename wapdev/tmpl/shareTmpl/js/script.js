//var ApiUrl="http://devshop.aigegou.com/mobile";
//var WapSiteUrl="http://devshop.aigegou.com/wapdev";
var goods_id=request("goods_id");
var dis_member_id=request("dis_member_id");
var dis_store_id=request("dis_store_id");
var shareUrl;
var goodsId=goods_id;
var goods_name;
var goodsImageUrl;
var nScrollHight = 0; //滚动距离总长(注意不是滚动条的长度)
var nScrollTop = 0; //滚动到的当前位置

if(dis_store_id==''||dis_store_id=='undefined'){
    shareUrl=WapSiteUrl+'/tmpl/shareTmpl/index.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id;
}else{
    shareUrl=WapSiteUrl+'/tmpl/shareTmpl/index.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id+'&dis_store_id='+dis_store_id;
}
$.ajax({

    //url: "http://testshop.aigegou.com/mobile/index.php?act=goods&op=goods_detail&goods_id=10621&client_type=wap&callback=jQuery1800690318496947277_1446616141957&_=1446616141991",
    url:ApiUrl+"/index.php?act=goods&op=goods_detail&goods_id="+goods_id+"&client_type=wap",
    type: "get",
    dataType:"jsonp",
    jsonP:"callback",
    success: function(data){
        if(data.code==200) {
            goods_name=data.data.goods_info.goods_name;
            goodsImageUrl=data.data.goods_image;
            var Temp1 = doT.template($("#first").html());
            $(".product").append(Temp1(data));
            var Temp2 = doT.template($("#sec").html());
            $(".explain-text").append(Temp2(data));
            var Temp3 = doT.template($("#thr").html());
            //判断是否有数据 ，如果没有就隐藏第二页
            if(data.data.goods_info.mobile_body_pre.length==0){
                $("#move").css('display','none');
            };
            $(".thr").append(Temp3(data));
            var Temp4 = doT.template($("#four").html());
            $(".four").append(Temp4(data));



            //1:1
            var width=$(".product-image").width()-40;
            $(".product-image").css('height',width);
            //����explain�߶�
            var windowHeight=$(window).height();
            var height = windowHeight-width-60;
            $(".explain ").css('height',height);
            //����explain-text�߶�
            var height2=height*7/10;
            $(".explain-text").css('height',height2);
            $(function() {
                var swiper = new Swiper('.swiper-container', {
                    watchSlidesProgress : true,
                    paginationClickable: true,
                    slidesPerView: 1,
                    noSwiping: true,
                    direction: 'vertical',

                    onTouchEnd: function () {
                        var info1 =$('.product');
                        var info2 = $('.product-border');
                        var info4 = $('.explain');
                        var info5 = $('.explain-tittle');
                        var info6 = $('.explain-tittle2');

                        var info11 = $('#share');//分享
                        info1.addClass("bounceInDown");
                        info2.addClass("bounceInDown");
                        info4.addClass("rotateIn");
                        info5.addClass("fadeInUp");
                        info6.addClass("fadeInUp");

                        var nDivHight = $("#move").height();
                        var productHeight = $(".thr").height();
                        if(productHeight<=nDivHight){
                            $("#move").removeClass("swiper-no-swiping");
                        }else if(productHeight>nDivHight){
                            $("#move").addClass("swiper-no-swiping");
                            $("#move").scroll(function(){

                                nScrollHight = $(this)[0].scrollHeight;
                                nScrollTop = $(this)[0].scrollTop;
                                if(nScrollTop + nDivHight >= nScrollHight||nScrollTop==0){
                                    $("#move").removeClass("swiper-no-swiping");
                                }
                            });

                        }
                        //最后一页首次加载动画
                        setTimeout(function(){
                            if(swiper.progress==0.5){
                                var div = document.getElementById('move');
                                div.scrollTop = 1;


                            }
                            if(swiper.progress==1){

                                $('.tops1').addClass("bounceInDown bottom");
                                $('.tops2').addClass("bounceInDown bottom");
                                $('.tops3').addClass("bounceInDown bottom");
                                $('.tops4').addClass("bounceInDown bottom");
                            }
                            if(swiper.progress!=1){
                                info11.hide();
                            }
                        },10);




                    },
                })




            });
            //scroll
            $("#shareFriend").click(function(){
                $("#share").show();
                share_wx();
            });
            $(".share").click(function(){

                $("#prompt").show();
                $("#prompt").width($(window).width());
                $("#prompt").height($(window).height());
                $("#prompt").click(function(){
                    $("#prompt").hide();
                });
            });
            $("#share_bottom").click(function(){
                $("#share_bottom").hide();
            });
            $(".img1").click(function(){
                $(".tips").css("display","none");
            });
            $("#buy").click(function(){
                if(data.data.goods_info.goods_state!=1){
                    $(".tips").css("display","block");
                }else if(dis_store_id==''||dis_store_id=='undefined'){
                    window.location.href=WapSiteUrl+"/tmpl/productdetail.html?goods_id="+goodsId+"&dis_member_id="+dis_member_id;
                }else{
                    window.location.href=WapSiteUrl+"/tmpl/productdetail.html?goods_id="+goodsId+"&dis_member_id="+dis_member_id+"&dis_store_id="+dis_store_id;
                }
                
            });
        }else{
            alert(data.message);
        }
    }
});
//音乐开关
$("#audio_btn").click(function(){
    var music = document.getElementById("music");
    if(music.paused){
        music.play();
        $(".music").addClass("on");
    }else{
        music.pause();
        $(".music").removeClass("on");
    }
});
//判断是否是微信打开的
function is_weixin(){

    if(ua.match(/MicroMessenger/i)=="micromessenger") {
        return true;
    } else {
        return false;
    }
}
//��ȡurl����
function request(paras) {
    var url = location.href;
    url = decodeURI(url);
    var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
    var paraObj = {};
    for (var i = 0; j = paraString[i]; i++) {
        paraObj[j.substring(0, j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=") + 1, j.length);
    }
    var returnValue = paraObj[paras.toLowerCase()];
    if (typeof(returnValue) == "undefined") {
        return "";
    } else {
        return returnValue;
    }
}

function share_wx(){

    var timestamp=new Date().getTime()+"";
    timestamp=timestamp.substring(0,10);
    var ranStr=randomString();
    var nurl=document.URL;
    //var nurl=window.location.href;

    function randomString(len) {
        len = len || 20;
        var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
        var maxPos = $chars.length;
        var pwd = '';
        for (i = 0; i < len; i++) {
            pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
        }
        return pwd;
    }
     
    $.ajax({
        url:"http://www.51aigegou.cn/aigegou/ws/webGetTicketSignFx2?timestamp="+timestamp+"&url="+nurl+"&nonceStr="+ranStr,
        type:'get',
        dataType:'jsonp',
        cache : false,
        jsonp:"jsonpcallback",
        success:function(data){
            wx.config({
                debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: 'wxa0641282049ed265', // 必填，公众号的唯一标识
                timestamp:timestamp, // 必填，生成签名的时间戳
                nonceStr: ranStr, // 必填，生成签名的随机串
                signature: data.sign.toLowerCase(),// 必填，签名，见附录1
                jsApiList: [    'checkJsApi',
                    'onMenuShareTimeline',
                    'onMenuShareAppMessage',
                    'onMenuShareQQ',
                    'onMenuShareQZone'
                ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });
        },
        error:function(){
            console.log("错了");
        }
    });
    wx.ready(function(){

        //分享给朋友
        wx.onMenuShareAppMessage({
            title: '分销',
            desc: goods_name,
            link: shareUrl,
            imgUrl: goodsImageUrl,
            trigger: function (res) {
            },
            success: function (res) {
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });

        //分享到朋友圈
        wx.onMenuShareTimeline({
            title: '分销',
            desc: goods_name,
            link: shareUrl,
            imgUrl: goodsImageUrl,
            trigger: function (res) {
            },
            success: function (res) {
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });

        //分享到qq
        wx.onMenuShareQQ({
            title: '分销',
            desc: goods_name,
            link: shareUrl,
            imgUrl: goodsImageUrl,
            success: function () {

            },
            cancel: function () {

            }
        });

        //分享到QQ空间
        wx.onMenuShareQZone({
            title: '分销',
            desc: goods_name,
            link: shareUrl,
            imgUrl: goodsImageUrl,
            success: function () {

            },
            cancel: function () {

            }
        });


    });
    wx.error(function(res){
        alert("error");
    });



}


