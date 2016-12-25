
$(window).ready(function(e){
	var wWidth=$(window).width();
	$(".eGNaneBox").width(wWidth-200);

	var keyFromApp=request("key");
	if(keyFromApp!="" && keyFromApp!=null){
		//从客户端进来并登录
		delCookie("key");
		addcookie("key",keyFromApp);
	}
	
	var thisKey=getcookie("key");
	var thisOrderId=request("order_id");
	var thisClientType=request("client_type");
	
	/*thisKey="a9ed04b6d0e153e84b3f33c47aa2549a";
	var thisOrderId="14342";*/
	
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&op=order_evaluate&client_type=wap&key="+thisKey+"&order_id="+thisOrderId,
		type:"get",
		dataType:"jsonp",
		callback:"callback",
		success: function(data){
			if(data.code==200){
				var eState=data.data.evaluation_state;
				if(eState==0){
					$(".eState").text("未评价");	
				}else if(eState==1){
					$(".eState").text("已完成");
				}else{
					//eState==2
					$(".eState").text("已过期未评价");
				}

				var ePState=data.data.has_evaluate_store;
				if(ePState==0){
					$(".ePState").text("未评价");
					
					$(".epScoreBox").css("display","block");
					$(".btnCommit").css("display","block");
					
				}else if(ePState==1){
					$(".ePState").text("已评价");
				}else{
					//ePState==2
					$(".ePState").text("已过期未评价");
				}

				$(".eStoreName").text(data.data.store_name);
				
				$(data.data.extend_order_goods).each(function(index, thisData){
					   var has_evaluated=thisData.has_evaluated;
					   var strBtnE="";
					   
					   if(has_evaluated==0){

						   	if(thisClientType=="android"){
								strBtnE="<a href='evaluate.html?goodName="+thisData.goods_name+"&goodImg="+thisData.goods_image+"&goodPrice="+thisData.goods_pay_price+"&goodNum="+thisData.goods_num+"&goodId="+thisData.goods_id+"&orderId="+data.data.order_id+"&client_type=android'>评价晒单</a>";
							}else{
								strBtnE="<a href='evaluate.html?goodName="+thisData.goods_name+"&goodImg="+thisData.goods_image+"&goodPrice="+thisData.goods_pay_price+"&goodNum="+thisData.goods_num+"&goodId="+thisData.goods_id+"&orderId="+data.data.order_id+"'>评价晒单</a>";
							}
					   }else if(has_evaluated==1){
					   		strBtnE="<a href='evaluate_simple.html?order_id="+thisOrderId+"&good_id="+thisData.goods_id+"&key="+thisKey+"'>查看评论</a>";
					   }
					
                	   var subDiv="<li><section class='eGImageBox'><img src='"+thisData.goods_image+"'/></section><section class='eGNaneBox'><section class='eGNameS1'>"+thisData.goods_name+"</section><section class='eGNameS2' style='display:none;'>白色 16G 移动版</section></section><section class='eGOBox'><section class='eGPrice'>¥"+thisData.goods_pay_price+"</section><section class='eGNum'>×"+thisData.goods_num+"</section><section class='eGOption'>"+strBtnE+"</section></section><section class='clearFloat'></section></li>"; 
					   
					   $(".eSGoodsBox ul").append(subDiv);
                });
			}else if(data.code==80001){
				alert(data.message);
				window.location.href=WapSiteUrl+"/tmpl/member/login.html";	
			}
		}
		
	});
	
	var starScore=new Array();
	starScore[0]=0;//描述相符
	starScore[1]=0;//服务态度
	starScore[2]=0;//发货速度
	starScore[3]=0;//物流速度

	$(".epScore").each(function(scoreIndex, element) {
        $(this).find("img").click(function(){
			$(".epScore").eq(scoreIndex).find("img").attr("src","images/star_normal@2x.png");
			var sNowIndex=$(".epScore").eq(scoreIndex).find("img").index(this);
			starScore[scoreIndex]=sNowIndex+1;
			$(".epScore").eq(scoreIndex).find("img").each(function(index, element) {
				if(index<=sNowIndex){
					$(".epScore").eq(scoreIndex).find("img").eq(index).attr("src","images/star_orange@2x.png");
				}
			});
		});
    });
	
	$(".btnCommit").click(function(){
		if(starScore[0]==0){
			alert("请填写描述相符程度！");
		}else if(starScore[1]==0){
			alert("请填写服务态度！");
		}else if(starScore[2]==0){
			alert("请填写发货速度！");
		}else if(starScore[3]==0){
			alert("请填写物流速度！");
		}else{
			$.ajax({
				url:ApiUrl+"/index.php?act=member_order&op=store_evaluate&client_type=wap&key="+thisKey+"&order_id="+thisOrderId+"&seval_desccredit="+starScore[0]+"&seval_servicecredit="+starScore[1]+"&seval_deliverycredit="+starScore[2]+"&seval_logistics="+starScore[3],
				type:"get",
				dataType:"jsonp",
				callback:"callback",
				success:function(data){
					if(data.code==200){
						alert("评价成功！");
						
						$(".ePState").text("已评价");
					
						$(".epScoreBox").css("display","none");
						$(".btnCommit").css("display","none");

						//调安卓函数
						if(thisClientType=="android"){
							app.orderCommentDone();
						}

					}else if(data.code==80002){
						alert(data.message);
					}else if(data.code==80001){
						alert(data.message);
						window.location.href=WapSiteUrl+"/tmpl/member/login.html";
					}
				}
			});
		}
	});

	//app返回按钮
	var username = getcookie("username");
	var password = getcookie("password");
	$(function () {
		$(".header-back").on("click", function () {
			if (type == "iOS" || type == "ios") {
				pop();
			} else if (type == "android") {
				app.pop();
			} else {
				history.back();
			}
		});
	});

});

//获取url参数
function request(paras)
{ 
	var url = location.href; 
	url=decodeURI(url);
	var paraString = url.substring(url.indexOf("?")+1,url.length).split("&"); 
	var paraObj = {}; 
	for (var i=0; j=paraString[i]; i++){ 
		paraObj[j.substring(0,j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=")+1,j.length); 
	} 
	var returnValue = paraObj[paras.toLowerCase()]; 
	if(typeof(returnValue)=="undefined"){ 
		return ""; 
	}else{ 
		return returnValue; 
	} 
}