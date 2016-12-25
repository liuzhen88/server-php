$(window).ready(function(){
	if($("#header").css("display")!="none"){
		$(".total-title").css("margin-top","50px");
	}

	$(".return-addr-con").width($(window).width()-95);

	$(".up-voucher-content select").width($(window).width()-100);
	$(".up-voucher-content input").width($(window).width()-100);
	$(".up-voucher-content textarea").width($(window).width()-100);
	$(".up-voucher-content textarea").css("max-width",$(window).width()-100);
	$(".up-voucher-content textarea").css("min-width",$(window).width()-100);
	$(".up-v-img ul").width($(window).width()-100);

	$(".up-v-imgdef img").click(function(){
		$("#send_img").click();
	});

});
$(document).ready(function(){
	var refund_id=request("refund_id");
	var express_id='';
	var this_key=getcookie("key");
	//获取express_id 物流公司列表
	if(this_key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
		$.ajax({
			url:ApiUrl+"/index.php?act=member_refund&op=express_list&client_type=wap&key="+this_key,
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					var expressName=new Array(),expressId=new Array();
					var num=0;
					var selectName;
					for(var key in data.data){
						var name=data.data[key].e_name;
						var id=data.data[key].id;
						expressName[num]=name;
						expressId[num]=id;
						num++;
						$("#select").append("<option>"+name+"</option>");
					}
					$("#select").change(function(){
						selectName=$("#select").val();
						var index=$.inArray(selectName,expressName);
						//expressId[index]这是选择的express_id
						express_id=expressId[index];
					});
					$(".return-v-commit").click(function(){
						var invoice_no=$("#invoice_no").val();

						if(express_id!=''&&invoice_no!=''){
							$.ajax({
								url:ApiUrl+"/index.php?act=member_refund&op=refund_ship&client_type=wap&&key="+this_key+"&refund_id="+refund_id+"&express_id="+express_id+"&invoice_no="+invoice_no,
								type:"get",
								dataType:"jsonp",
								jsonp:"callback",
								success: function(data){
									if(data.code==200){
										alert("提交成功");
										window.location.href=WapSiteUrl+"/tmpl/member/sellerAgree.html?refund_id="+refund_id;
									}else{
										alert(data.message);
									}
								}
							});
						}else{
							alert("物流单号或公司不能为空");
						}
					});

				}else if(data.code==80001){
					window.location.href = WapSiteUrl + "/tmpl/member/login.html";
				}
			}
		});
	
		$.ajax({
		   url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=detail&key="+this_key+"&refund_id="+refund_id,
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					$("#returnAddrCon").html(data.data.refund_info.rr_address);

					var add_time=data.data.refund_info.add_time;
					var stopTime=dateAddDay(add_time*1000,8);
					setInterval(function(){ $("#time").html(countTime(stopTime));},500);


				}
			}
		});

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

            var subDiv="卖家操作时间剩余 <span class='time-color'>"+days+"</span> 天<span class='time-color'> "+hours+"</span> 小时<span class='time-color'> "+minutes+"</span> 分钟 <span class='time-color'>"+seconds+"</span> 秒";

            return subDiv;
        }
	}
		
});

