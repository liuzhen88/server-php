$(document).ready(function(){
	var refund_id=request("refund_id");
	var key=getcookie("key");
	if(key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
	$.ajax({
		url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=detail&key="+key+"&refund_id="+refund_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				var refund_amount=data.data.refund_info.refund_amount;
				var refund_sn=data.data.refund_info.refund_sn;
				var refund_type=data.data.refund_info.refund_type;
				var add_time=data.data.refund_info.add_time;
				var admin_time=data.data.refund_info.admin_time;
				var refund;
				if(refund_type==1){
					refund="退款";
				}else if(refund_type==2){
					refund="退货退款";	
				}
				$("#money").html("￥"+refund_amount);
				$("#refund_num").html(refund_sn);
				$("#reason_type").html(refund);
				var cc=new Date(parseInt(add_time)*1000);
				var aa=cc.format('yyyy-MM-dd h:m:s');
			 	var n=new Date(parseInt(admin_time)*1000);
				var m=n.format('yyyy-MM-dd h:m:s');
				$("#refund_time").html(aa);
				$("#success_time").html(m);
			}
		}
	});	
	}
});
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