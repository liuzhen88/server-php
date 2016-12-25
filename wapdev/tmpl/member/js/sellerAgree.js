$(document).ready(function(){
	var key=getcookie("key");
	 var refund_id=request("refund_id");
	  
	if(key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
		var refund_type=new Array();
		$.ajax({
			   url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=detail&key="+key+"&refund_id="+refund_id,
				type:"get",
				dataType:"jsonp",
				jsonp:"callback",
				success: function(data){
					if(data.code==200){
						var invoice_no=data.data.refund_info.invoice_no;
						var express_name=data.data.refund_info.express_name; 
						var add_time=data.data.refund_info.add_time;
						var order_id=data.data.refund_info.order_id;
						$("#express_name").html(express_name);
						$(".express_name").html(express_name);
						$(".shipping_code").html(invoice_no);
						$("#incNum").html(invoice_no);
						var stopTime=dateAddDay(add_time*1000,8);
							setInterval(function(){ $("#time").html(countTime(stopTime));},500);
						var state=data.data.refund_info.refund_type;
						if(state==1){
							$("#expressState").hide();	
						}
						
						
	/*获取物流*/
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=search_deliver&key="+key+"&order_id="+order_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				var time=new Array(),info=new Array();
				var express_name=data.data.express_name;
				var shipping_code=data.data.shipping_code;
				$(".express_name").html(express_name);
				$(".shipping_code").html(shipping_code);
				/*append物流*/
				/*$(data.data.deliver_info).each(function(k,v){
														
				});*/
				if(data.data.deliver_info==null||data.data.deliver_info==''){
					$(".getInfo").click(function(){
						$(".footer").show();	
					});
				}else{
				for(var i=data.data.deliver_info.length-1;i>=0;i--){
					
					time[i]=data.data.deliver_info[i].substr(0,19);
					info[i]=data.data.deliver_info[i].substr(20,data.data.deliver_info[i].length-1);
var list="<li>"
			+"<section>"
			+	"<section class='float-left'>"
			+		"<img src='../images/logist_2.jpg'/>"
			+	"</section>"
			+	"<section class='float-left border-bottom lo-per-text'>"
			+		"<section class='margin-top-7 lo-per-t'>"+info[i]+"</section>"
			+		"<section>"+time[i]+"</section>"
			+	"</section>"
			+	"<section class='clear-float'></section>"
			+"</section>"
		+"</li>";
		$("#list").append(list);
		$("#list li").eq(0).addClass("font-color-fd8900");
		$("#list li").eq(0).find("img").attr("src","../images/logist_1.jpg");
		$(".lo-per-text").css("width",$(window).width()-55);
		$(".lo-per-t").css("width",$(window).width()-55);
				}
				$(".getInfo").click(function(){
					$(".footer").show();	
				});
				}
			}
		}
	});
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

            var subDiv="如果 <span class='time-color'>"+days+"</span> 天<span class='time-color'> "+hours+"</span> 小时<span class='time-color'> "+minutes+"</span> 分钟 <span class='time-color'>"+seconds+"</span> 秒内商家未确认，退款将自动完成并退款至您的积分账号中";

            return subDiv;
        }
});