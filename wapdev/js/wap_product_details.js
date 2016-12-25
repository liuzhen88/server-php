
$(function() {
    var x=$(window).width();
    var lat=localStorage.getItem("latitude");
    var lng=localStorage.getItem("longitude");
    var thisGoodID=request("goods_id");
    var key=getcookie('key');

    $.ajax({
        url:ApiUrl+"/index.php?act=unlimited_invitation&op=good_detail&client_type=wap&good_id="+thisGoodID+"&lat="+lat+"&lng="+lng+"&callback=callback",
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(result){
            if(result.code==200){
                result.data.lat=lat;
                result.data.lng=lng;

                var goodsDoTmpl = doT.template($("#goodstmpl").html());
                $("#goodsMain").show().html(goodsDoTmpl(result.data));

                var thisInvitation=result.data.store_info.invitation;
                var moneyBTCon=result.data.good_info.goods_price;
                var storeTelPhone=result.data.store_info.store_phone;

                //懒加载
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
                });

                $(".h-r-text").width($(window).width()-120);

                //头部轮播
                $('.swiper-container').each(function() {
                    if ($(this).find('.swiper-slide').length < 2) {
                        return;
                    }
                    Swipe(this, {
                        startSlide: 1,
                        speed: 400,
                        auto: 3000,
                        continuous: true,
                        disableScroll: false,
                        stopPropagation: false,
                        callback: function(index, elem) {},
                        transitionEnd: function(index, elem) {}
                    });
                });
                //收藏商品
                $.ajax({
                    url:ApiUrl+"/index.php?act=user_action&op=is_favorites&key="+ key +"&client_type=wap&good_id="+thisGoodID,
                    type:"get",
                    dataType:"jsonp",
                    jsonp:"callback",
                    success:function(data){
                        if(data.data=="yes"){
                            $('.bottom-btn-favorites').addClass('f_on');
                        }
                    }
                });
                $(".bottom-btn-favorites").click(function(){

                    $.ajax({
                        url:ApiUrl+"/index.php?act=user_action&op=is_favorites&key="+ key +"&client_type=wap&good_id="+thisGoodID+"&token_member_id="+getcookie("user_id"),
                        type:"get",
                        dataType:"jsonp",
                        jsonp:"callback",
                        success:function(data){
                            if(data.code==80001){
                                alert(data.message);
                                window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                            }
                            if(data.data=="yes"){
                                $.ajax({
                                    url:ApiUrl+"/index.php?act=member_favorites&op=favorites_del&fav_id="+thisGoodID+"&key="+key+"&client_type=wap&type=goods",
                                    type:"get",
                                    dataType:"jsonp",
                                    jsonp:"callback",
                                    success:function(data){
                                        if(data.code==200){
                                            $('.bottom-btn-favorites').removeClass("f_on");
                                            alert("成功取消收藏！");
                                            return;
                                        }
                                    }
                                });
                            }else{
                                $.ajax({
                                    url:ApiUrl+"/index.php?act=member_favorites&op=favorites_add&goods_id="+thisGoodID+"&key="+key+"&client_type=wap&is_online=1",
                                    type:"get",
                                    dataType:"jsonp",
                                    jsonp:"callback",
                                    success:function(data){
                                        if(data.code==200){
                                            $('.bottom-btn-favorites').addClass('f_on');
                                            alert("收藏成功！");
                                            return;
                                        }
                                    }
                                });
                            }
                        }
                    });

                })
                //分享指示
                $('.bottom-btn-share').click(function(){
                    $('.dialog-weichat-share').show();
                })
                $('.dialog-weichat-share').click(function(){
                    $('.dialog-weichat-share').hide();
                })
                //评论列表接口
                if($('#goodsEvaluate').length>0){
                    $.ajax({
                        url:ApiUrl+"/index.php?act=unlimited_invitation&op=get_evaluate_goods_list_v2&geval_goodsid="+thisGoodID+"&client_type=wap&curpage=1",
                        type:"get",
                        dataType:"jsonp",
                        jsonp:"callback",
                        success:function(result){
                            if(result.code==200){
                                var evaluateDoTmpl = doT.template($("#evaluatetmpl").html());
                                result.goods_id=thisGoodID;
                                $("#goodsEvaluate").show().html(evaluateDoTmpl(result));
                                var evaluateListDoTmpl = doT.template($("#evaluateListtmpl").html());
                                $("#evaluateList").show().html(evaluateListDoTmpl(result));
                                //评论列表图片展示
                                $('.preview-list').MobilePhotoPreview({
                                    trigger: '.preview',
                                    show: function(c) {}
                                });
                                return;
                            }
                        }
                    });
                }

                /*$(".btOrder").click(function(){
                    window.location.href="preSale/local_commit_order.html?goods_id="+thisGoodID+"&money="+moneyBTCon+"&invitation="+thisInvitation;
                });*/

                $(".btOrder").click(function(){
                    var wHeight=$(window).height();
                    var mdmHeight=176;

                    $(".mdmPayLay").css("display","block");
                    $("#moneyBT").val("");
                    $(".mdmPayLayBox").css("margin-top",(wHeight-mdmHeight)/2);
                });

                $(".lay-quit").click(function(){
                    $(".mdmPayLay").css("display","none");
                });

                $(window).resize(function(){
                    var wHeight=$(window).height();
                    var mdmHeight=200;
                    $(".mdmPayLayBox").css("margin-top",(wHeight-mdmHeight)/2);
                });

                $(".btnLayB1 a").attr("href","tel:"+storeTelPhone);

                $(".btnLayB2").click(function(){
                    window.location.href="preSale/local_commit_order.html?goods_id="+thisGoodID+"&money="+moneyBTCon+"&invitation="+thisInvitation+"&o2o_order_type=2";
                });

                $(".mdmPay a").click(function(){
                    window.location.href="preSale/local_commit_order.html?goods_id="+thisGoodID+"&money="+moneyBTCon+"&invitation="+thisInvitation+"&o2o_order_type=1";
                });

            }
        }
    });

});

function evaluateNum(num){
    var evaN;
    evaN=num*20;
    return evaN;

}

function get_time(this_time){
    Date.prototype.format = function(format) {
        var date = {
            "M+": this.getMonth() + 1,
            "d+": this.getDate(),
            "h+": this.getHours(),
            "m+": this.getMinutes(),
            "s+": this.getSeconds(),
            "q+": Math.floor((this.getMonth() + 3) / 3),
            "S+": this.getMilliseconds()
        };
        if (/(y+)/i.test(format)) {
            format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
        }
        for (var k in date) {
            if (new RegExp("(" + k + ")").test(format)) {
                format = format.replace(RegExp.$1, RegExp.$1.length == 1
                    ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
            }
        }
        return format;
    }
    var cc=new Date(parseInt(this_time)*1000);
    var aa=cc.format('yyyy-MM-dd hh:mm:ss');
    return aa;
}

