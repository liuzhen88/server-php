$(document).ready(function(){
	var key=getcookie("key");
	var refund_id=request("refund_id");
	var rec_id=request("rec_id");
	var order_id=request("order_id");
	var refund_amount=request("money");
	var num=request("num");
	if(key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
	$.ajax({
		url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=detail&key="+key+"&refund_id="+refund_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success: function(data){
			if(data.code==200){
				var seller_name=data.data.order_info.extend_store.seller_name;
				var refund_sn=data.data.refund_info.refund_sn;
				var add_time=data.data.refund_info.add_time;
				var refund_amount=data.data.refund_info.refund_amount;
				var reason_info=data.data.refund_info.reason_info;
				var refund_type=data.data.refund_info.refund_type;
				var seller_message=data.data.refund_info.seller_message;
				var refund;
				if(refund_type==1){
					refund="退款";
				}else if(refund_type==2){
					refund="退货";	
				}
				$("#sellerName").html(seller_name);
				$("#order_num").html(refund_sn);
				$("#order_money").html("￥"+refund_amount);
				$("#reason_right").html(reason_info);
				$("#order_reason").html(refund);
				$("#refuse_right").html(seller_message);
				$("#btn_left").click(function(){
					window.location.href=WapSiteUrl+"/tmpl/member/tuihuo.html?rec_id="+rec_id+"&order_id="+order_id+"&num="+num+"&money="+refund_amount+"&refund_id="+refund_id+"&flag=1";					  
				});
			}
		}
	});
	var username=getcookie("username");
	var password=getcookie("password");
    $(function(){
        $(".header-back").on("click", function () {
            if (type == "iOS"||type == "ios") {
                pop();
            } else if (type == "android") {
                app.pop();
            } else {
                history.back();
            }
        });
    });
	}
});