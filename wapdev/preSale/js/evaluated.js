/**
 * Created by Administrator on 2015/11/30.
 */
$(document).ready(function(){
    var key=getcookie("key");
    //key="23673ea2fc64193d77d8ba48abcb140a";
    var lat=localStorage.getItem("latitude");
    var long=localStorage.getItem("longitude");
    var location=long+","+lat;
    //var location="120.616634,31.337882";
    if(key==''){
        window. location.href = WapSiteUrl + '/tmpl/member/login.html';
    }else {
        var order_sn=request("order_sn");
        var pay_sn=request("pay_sn");
        $.ajax({
            //url:  "http://devshop.aigegou.com/mobile/index.php?act=member_order&op=order_detail&key="+key+"&order_sn="+order_sn+"&pay_sn="+pay_sn+"&location="+location+"&client_type=wap",
            url:  ApiUrl+"/index.php?act=member_order&op=order_detail&key="+key+"&order_sn="+order_sn+"&pay_sn="+pay_sn+"&location="+location+"&client_type=wap",
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(result){
                if(result.code==200){
                    var validate_time;
                    var Temp = doT.template($("#content").html());
                    $(".content").append(Temp(result.data));
                    //有效期判断
                    if(result.data.order.validate_time==0){
                        validate_time="无限制";
                    }else{
                        validate_time=result.data.order.validate_time/1000/60/24+"天";
                    }
                    $("#validate_time").html(validate_time);
                    if(result.data.order.order_amount<1){
                        $("#table-tips").html("支付金额过小，不可评价");
                    }
                }
            }
        });
    }
});