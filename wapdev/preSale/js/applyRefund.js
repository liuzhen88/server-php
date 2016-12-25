/**
 * Created by Administrator on 2015/11/30.
 */
$(document).ready(function(){
    var key=getcookie("key");
   // key="23673ea2fc64193d77d8ba48abcb140a";
    var lat=localStorage.getItem("latitude");
    var long=localStorage.getItem("longitude");
    var location=long+","+lat;
    //var location="120.616634,31.337882";
    if(key==''){
        window. location.href = WapSiteUrl + '/tmpl/member/login.html';
    }else {
        var order_sn=request("order_sn");
        var pay_sn=request("pay_sn");
        var order_id=request("order_id");
        $.ajax({
            //url:  "http://devshop.aigegou.com/mobile/index.php?act=member_order&op=order_detail&key="+key+"&order_sn="+order_sn+"&pay_sn="+pay_sn+"&location="+location+"&client_type=wap",
            url:  ApiUrl+"/index.php?act=member_order&op=order_detail&key="+key+"&order_sn="+order_sn+"&pay_sn="+pay_sn+"&location="+location+"&client_type=wap",
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(result){
                if(result.code==200){
                    var TempOrder = doT.template($("#order").html());
                    $(".order").append(TempOrder(result.data));
                    var TempRefund = doT.template($("#refund").html());
                    $(".refund").append(TempRefund(result.data));
                    $("#submit").click(function(){
                        $.ajax({
                            url:ApiUrl+"/index.php?act=member_refund&op=changeMemberMoney&key="+key+"&order_id="+order_id+"&client_type=wap",
                            type:"get",
                            dataType:"jsonp",
                            jsonp:"callback",
                            success:function(data){
                                if(data.code==200){
                                    window.location.href="local_success.html?order_sn="+order_sn+"&pay_sn="+pay_sn;
                                }
                            }
                        });

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