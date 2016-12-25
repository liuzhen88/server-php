$(function () {
    FastClick.attach(document.body);

    $(".store-box-right").width($(window).width()-114);
	 var key=getcookie("key");
    var lat=localStorage.getItem("latitude");
    var lng=localStorage.getItem("longitude");
    var location=lng+","+lat;
    if(key==""){
        window. location.href = WapSiteUrl + '/tmpl/member/login.html';
    }else{
        var order_sn=request("order_sn");
        var pay_sn=request("pay_sn");
        var consume_code=request("consume_code");
        $.ajax({
            url:  ApiUrl+"/index.php?act=member_order&op=order_detail&key="+key+"&order_sn="+order_sn+"&pay_sn="+pay_sn+"&location="+location+"&client_type=wap",
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(result){
                if(result.code==200){
                    var orderDetails=doT.template($("#orderDetails").html());
                    $("#goodsMain").show().html(orderDetails(result.data));
                    $(".store-box-right").width($(window).width()-114);
                    var money=result.data.order.order_amount;
                    var invitation=result.data.store.store_invitation;
                    var order_id=result.data.order.order_id;
                    var consume_code=result.data.order.consume_code;
                    var img_src="https://sp0.baidu.com/5aU_bSa9KgQFm2e88IuM_a/micxp1.duapp.com/qr.php?value="+consume_code;
                    $("#local_code").attr("src",img_src);
                    $("#apply-refund").click(function(){
                        window.location.href=WapSiteUrl+"/preSale/local_apply_refund.html?order_sn="+order_sn+"&pay_sn="+pay_sn+"&order_id="+order_id;
                    });
                    $(".btn-confirm").click(function(){
                        var code=$("#code").val();
                        if(code==''){
                            alert("请输入员工确认码");
                        }else{
                            $.ajax({
                                url:ApiUrl+"/index.php?act=member_order&op=local_order_consume_code_sure&key="+key+"&consume_code="+consume_code+"&code="+code+"&invitation="+invitation+"&client_type=wap",
                                type:"get",
                                dataType:"jsonp",
                                jsonp:"callback",
                                success:function(data){
                                    if(data.code==200){
                                        alert(data.message);
                                        // history.go(-2);
                                    }else{
                                        alert(data.message);
                                    }
                                }
                            })
                        }

                    });

                }

            }
        });
    }

});
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
    var aa=cc.format('yyyy-MM-dd h:m:s');
    var t=aa.replace("0:0:0","00:00:00");
    t=t.replace(" 0:"," 00:");
    return t;
}