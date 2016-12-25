var key=getcookie("key");
var num=request("num");
$(document).ready(function(){
    var refund_id=request("refund_id");
	var rec_id=request("rec_id");
	
	if(key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
	get_info(refund_id,rec_id);
	}
});
function get_info(refund_id,rec_id){
	$.ajax({
		url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=detail&key="+key+"&refund_id="+refund_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success: function(data){
			if(data.code==200){
				var seller_name=data.data.order_info.extend_store.seller_name;
				var order_id=data.data.refund_info.order_id;
				var refund_sn=data.data.refund_info.refund_sn;
				var add_time=data.data.refund_info.add_time;
				var refund_amount=data.data.refund_info.refund_amount;
				var reason_info=data.data.refund_info.reason_info;
				var refund_type=data.data.refund_info.refund_type;
				var refund;
				
				if(refund_type==1){
					refund="退款";
				}else if(refund_type==2){
					refund="退货";	
				}
				$("#sellerName").html(seller_name);
				$("#order_num").html(refund_sn);
				$("#order_money").html("￥"+refund_amount);
				$(".reason_right").html(reason_info);
				$("#order_reason").html(refund);
				$("#left").click(function(){
					window.location.href=WapSiteUrl+"/tmpl/member/tuihuo.html?rec_id="+rec_id+"&order_id="+order_id+"&num="+num+"&money="+refund_amount+"&refund_id="+refund_id+"&flag=1";				  
				});
				
				 var stopTime=dateAddDay(add_time*1000,8);
            setInterval(function(){ $("#time").html(countTime(stopTime));},500);
			}else if(data.code==80001){
				alert("请登录");
			window.location.href = WapSiteUrl + "/tmpl/member/login.html";
			}
		}
	});
}

 //获取当前日期加几天后的时间戳
        function dateAddDay(t,n){
            var t2;

            t=parseInt(t);
            n=parseInt(n);

            t2 = n * 1000 * 3600 * 24;
            t+= t2;

            return t;
        }

        //获取当前日期，计算差值
        function countTime(stopTime){
            var nowTime = new Date();
            var stopTime=new Date(stopTime);

            var date=stopTime.getTime()-nowTime.getTime();  //时间差的毫秒数

            //计算出相差天数
            var days=Math.floor(date/(24*3600*1000));

            //计算出小时数
            var leave1=date%(24*3600*1000)    //计算天数后剩余的毫秒数
            var hours=Math.floor(leave1/(3600*1000));
            //计算相差分钟数
            var leave2=leave1%(3600*1000);        //计算小时数后剩余的毫秒数
            var minutes=Math.floor(leave2/(60*1000));

            //计算相差秒数
            var leave3=leave2%(60*1000);      //计算分钟数后剩余的毫秒数
            var seconds=Math.round(leave3/1000);

            var subDiv="还有 <span class='time-color'>"+days+"</span> 天<span class='time-color'> "+hours+"</span> 时<span class='time-color'> "+minutes+"</span> 分 <span class='time-color'>"+seconds+"</span> 秒";

            return subDiv;
        }