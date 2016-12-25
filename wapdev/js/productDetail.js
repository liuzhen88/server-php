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
var browser = {
		versions : function() {
			var u = navigator.userAgent, app = navigator.appVersion;
			return {//移动终端浏览器版本信息
				trident : u.indexOf('Trident') > -1, //IE内核
				presto : u.indexOf('Presto') > -1, //opera内核
				webKit : u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
				gecko : u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
				mobile : !!u.match(/AppleWebKit.*Mobile.*/)
						|| !!u.match(/AppleWebKit/), //是否为移动终端
				ios : !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
				android : u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
				iPhone : u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, //是否为iPhone或者QQHD浏览器
				iPad : u.indexOf('iPad') > -1, //是否iPad
				webApp : u.indexOf('Safari') == -1 //是否web程序，没有头部与底部
			};
		}(),
		language : (navigator.browserLanguage || navigator.language)
				.toLowerCase()
	}
$(window).load(function(e){	
	
});

$(window).ready(function(e) {
	var goods_id=request("good_id");
	window.location.href="http://shop.aigegou.com/agg/wap/wap_product_details.html?goods_id="+goods_id;
	var x=$(window).width();
	FastClick.attach(document.body);
	
	var lat=request("lat");
	var lng=request("lng");
	
	var thisGoodID=request("good_id");
	var type=request("client_type");
	 
	/*if(type=='android'){
		 
		$(".onlinepay").hide();

	}*/
	if (browser.versions.ios || browser.versions.iPhone
			|| browser.versions.iPad) {
		$(".onlinepay").show();
	} else if (browser.versions.android) {
		$(".mdmPay").css("width","50%");
		$(".btOrder").css("width","49%");
		$(".onlinepay").hide();
		
	}
	$.ajax({
		url:ApiUrl+"/index.php?act=unlimited_invitation&op=good_detail&client_type=wap&good_id="+thisGoodID+"&lat="+lat+"&lng="+lng+"&callback=callback",
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){   
				//var str=jQuery.parseJSON(data.data.good_info.buy_notes);	//后台添加的购买须知  xuping   2015年8月31日15:38:28
				/*
			    $arr['r_remind']= isset($_POST['r_remind']) ?  $_POST['r_remind'] : '';  //提醒
	            $arr['t_remind']= isset($_POST['r_remind']) ?  $_POST['t_remind'] : '';  //限购使用提醒
	            $arr['r_remark']= isset($_POST['r_remark']) ?  $_POST['r_remark'] : '';  //温馨提示
	            $arr['r_server']= isset($_POST['r_server']) ?  $_POST['r_server'] : '';  //商家服务
				 */
				// if(str.r_remind !=''){
				// 	$(".rgoodsName").text('提醒:'+str.t_remind);
				// }
				// if(str.t_remind !=''){
				// 	$(".rgoodsPriceN").text('限购使用提醒:'+str.t_remind);
				// }
				// if(str.r_remark !=''){
				// 	$(".rgoodsPriceNa").text('温馨提示:'+str.r_remark);
				// }
				// if(str.r_server !=''){
				// 	$(".rgoodsPriceNb").text('商家服务:'+str.r_server);
				// }
				var windowWidth=$(window).width();
				$(".shopDImg_boxPa").css("height",windowWidth);
				
				$(".shopAddr").css("width",windowWidth-100);
				var goodsId=data.data.good_info.goods_id;
				if(goodsId<8161){
					 
					windowWidth=x/2.4;
					$(".shopDImg_boxPa").css("height",windowWidth);
				}
				$(".goodsName").text(data.data.good_info.goods_name);
				$(".goodsPriceN").text("¥"+data.data.good_info.goods_price);
				$(".goodsPriceF").text("¥"+data.data.good_info.goods_marketprice);
				$(".activDetailCon").html(data.data.good_info.mobile_body);
				if(data.data.good_info.goods_price==data.data.good_info.goods_marketprice){
					$(".goodsPriceF").hide();	
				}
				//人均消费和折扣
				if(data.data.store_info.whole_discount==10.0||data.data.store_info.per_consumption==0.00){
					$(".zkBox").hide();
				}else{
					$(".shopQCZK_span").text(data.data.store_info.whole_discount);
					$(".shopRJXF_span").text(data.data.store_info.per_consumption);
				}
				
				$(data.data.good_image).each(function(index, thisGoodImg) {
					var subDiv="<li class='shopDImg_box_li'><div class='pLBox'><div id='container' class='container'><ul id='scene' class='scene'><li class='layer' data-depth='0.30'><img src='"+thisGoodImg+"'/></li></ul></div></div></li>";
					
					$(".shopDImg_box_ul").append(subDiv);
					
					var windowWidth=$(window).width();
					if(goodsId<8161){
						 
						windowWidth=x/2.4;
						 
					}
					$(".shopDImg_box_li").css("width",$(window).width());
					$(".shopDImg_box_li").css("height",windowWidth);
					$(".shopDImg_box_li img").css("height",windowWidth);
					
					/*$('.scene').parallax();*/
					 
                });
				
				if($(".shopDImg_box_li").length>1){
					var tabRight=setInterval(function(){tabRigth(".shopDImg_box");},2500);
				}
				
				$(".shopName a").text(data.data.store_info.store_name);
				var addrText=data.data.store_info.area_info+" "+data.data.store_info.store_address;
				$(".shopAddr .overflow").text(addrText);
				
				$(".shopTX img").attr("src",data.data.store_info.store_avatar);
				$(".shopTX a").attr("href","shop?store_id="+data.data.store_info.store_id);
				$(".shopName a").attr("href","shop?store_id="+data.data.store_info.store_id);
				
				var invitation=data.data.store_info.invitation;
				$("#cash").click(function(){
					window.location.href="cash?invitation="+invitation;
				});
							
				$(".onlinepay a").attr("href","onlinePay?good_id="+thisGoodID);
				
				if(addrText.length<=20){
					$(".shopAddr").css("line-height","50px");	
				}
				
				var distText=data.data.store_info.distance;
				var pointText="";
				
				if(distText.length>3){
					
					pointText=distText.substr(distText.length-3,1);
					distText=distText.substr(0,distText.length-3);
					distText=distText+"."+pointText+"k";
					
				}
				
				$(".shopDistanceValue").text(distText+"m");
				
				$("#callToStore").attr("href","phone:"+data.data.store_info.store_phone);
				//点击获取经纬度
				$(".distanceBox").click(function(){
					var store_lng=data.data.store_info.lng;
					var store_lat=data.data.store_info.lat;	
					var store_address=data.data.store_info.store_address;
					var store_id= data.data.store_info.store_id;
					window.location.href="navigation?lng="+store_lng+"&lat="+store_lat+"&store_address="+store_address;
				});
			}
		}
	});
	
});

function　tabRigth(className){
	var scrollWidth=$(className).width();
	var scrollLength=$(className).find(".shopDImg_box_li").length;
	$(className).animate({scrollLeft:scrollWidth},500,function(){
		$(className).find(".shopDImg_box_li").eq(scrollLength-1).after($(className).find(".shopDImg_box_li").eq(0));
		$(className).animate({scrollLeft:0},0);
	});
}

