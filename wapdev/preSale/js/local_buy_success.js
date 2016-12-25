$(function () {
    FastClick.attach(document.body);

    var pay_sn=request("pay_sn");
    var order_sn=request("order_sn");
    var faceToFace=request("flag");
    var location=getcookie("lng")+","+getcookie("lat");

    if(getcookie("lng")=="" || getcookie("lat")==""){
        location="120.616634,31.337882";//经纬度给个默认值
    }

    if(faceToFace!="faceToFace"){
        $(".face-box").show();
        $(".face-text").text("支付成功");
    }else{
        $(".face-text").width($(window).width()-65);
        $(".face-text").text("订单已确认，请现金或刷卡买单，如您已买单，请忽略此提示");
    }

    $(".btn-success").click(function(){
        window.location.href=WapSiteUrl+"/tmpl/member/order_list.html?flag=fromBT";
    });

    $.ajax({
        url:ApiUrl+"/index.php?act=member_order&op=order_detail&client_type=wap&key="+getcookie("key")+"&pay_sn="+pay_sn+"&order_sn="+order_sn+"&location="+location,
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(result){
            if(result.code==200){
                $(".aj-order_sn").text(result.data.order.order_sn);
                $(".aj-payment_time").text(result.data.order.payment_time);
                $(".aj-order_amount").text(result.data.order.order_amount);

                if(result.data.order.order_state==10){
                    $(".face-text").text("支付失败");
                    $(".sheader h2").text("支付失败");
                    $("title").text("支付失败");
                }

                if(result.data.order.goods_num!=undefined){
                    $(".to-num-box").show();//不是从店铺过来的
                    $(".aj-goods_num").text(result.data.order.goods_num);
                }

                $(".aj-rebate").text(result.data.order.rebate);

                if(result.data.order.validate_time!=undefined) {
                    $(".to-time-box").show();//不是从店铺过来的
                    if(result.data.order.validate_time==0){
                        $(".aj-validate_time").text("永久");
                    }else{
                        $(".aj-validate_time").text(changeTimeFormat(result.data.order.validate_time));
                    }
                }

                $(".aj-store_name").text(result.data.store.store_name);
                $(".aj-area_info").text(result.data.store.area_info);

                $(".aj-mobile").click(function(){
                    //打电话
                    alert("商家号码为："+result.data.store.mobile);
                });

                $(".aj-distance").text(result.data.store.distance);

            }else if(result.code==80001){
                window.location.href=WapSiteUrl+"/tmpl/member/login.html";
            }else if(result.code==80002){
                alert(result.message);
            }
        }
    });

    function changeTimeFormat(thisTime){//时间戳转换
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

        var thisDate=new Date(parseInt(thisTime)*1000);
        var thisDateFor=thisDate.format('yyyy-MM-dd hh:mm');

        return thisDateFor;
    }
});
