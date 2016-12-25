$(document).ready(function(){
    var key=getcookie("key");
    //key="65174c8446317b1c78056799a215ef9e";
    var lat=localStorage.getItem("latitude");
    var long=localStorage.getItem("longitude");
    var location=long+","+lat;
    var goods_type=request("goods_type");
    if(key==''){
        window. location.href = WapSiteUrl + '/tmpl/member/login.html';
    }else {
        var order_sn=request("order_sn");
        var pay_sn=request("pay_sn");
        $.ajax({
            url:  ApiUrl+"/index.php?act=member_order&op=order_detail&key="+key+"&order_sn="+order_sn+"&pay_sn="+pay_sn+"&location="+location+"&client_type=wap",
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(result){
                if(result.code==200){
                    var orderDetails=doT.template($("#orderDetails").html());
                    $("#goodsMain").show().html(orderDetails(result.data));
                    var mobile=result.data.store.mobile;
                    var order_id=result.data.order.order_id;
                    var store_id=result.data.store.store_id;
                    var goods_id=result.data.order.goods_id;
                    $("#tel").attr("href","tel:"+mobile);
                   $(".go-evaluated").click(function(){
                        if(goods_type==0){
                            window.location.href=WapSiteUrl+"/evaluate.html?order_id="+order_id+"&store_id="+store_id;
                        }else if(goods_type==1){
                            window.location.href=WapSiteUrl+"/evaluate.html?order_id="+order_id+"&goods_id="+goods_id;
                        }
                   });
                }

            }
        });
    }
});

function evaluateNum(num){
    var evaN;
    evaN=num*20;
    return evaN;

}
