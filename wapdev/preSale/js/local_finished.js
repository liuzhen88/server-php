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
                    console.log(result.data.store.store_name);
                    var Temp1Product = doT.template($("#product").html());
                    $(".product").append(Temp1Product(result.data));
                    var Temp1Address = doT.template($("#address").html());
                    $(".address").append(Temp1Address(result.data));
                    var Temp1evaluate = doT.template($("#evaluate").html());
                    $(".evaluate").append(Temp1evaluate(result.data));
                    var TempOrder = doT.template($("#order").html());
                    $(".order").append(TempOrder(result.data));
                }

            }
        });
    }
});