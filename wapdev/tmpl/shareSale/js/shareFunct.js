// JavaScript Document
var imgFlag=request("imgFlag");
var from_member=request("from_member");
var dis_store_id=request("dis_store_id");
var goods_id=request("goods_id");
var type=request("client_type");
var key=request("key");
	if(key==''){
		key=getcookie("key");	
	}else{
		addcookie("key",key);
		key=getcookie("key");	
	}
	if(type=='android'){
		addcookie("type",type);
		type=getcookie("type");
	}else if(type=='ios'||type=='iOS'){
		addcookie("type",type);
		type=getcookie("type");
	}else{
		//wap
	}
$(window).load(function(e){
	if(imgFlag!="" && imgFlag!=null){
		$("#toSaleRole span").text(imgFlag);
		if(imgFlag==0){
			$("#toSaleRole img").attr("src","images/uncheck@2x.png");
		}else{
			$("#toSaleRole img").attr("src","images/checkn@2x.png");
		}
	}

	$("#goShareSale").click(function(){
		if(from_member==1){
			//跳回个人中心
			window.location.href=WapSiteUrl+"/member.html";
		}else{
			//跳回商城商品详情
			window.location.href=WapSiteUrl+"/tmpl/productdetail.html?goods_id="+goods_id+"&dis_store_id="+dis_store_id;
			//alert("跳回商品详情！");
		}
	});
});

$(window).ready(function(e){

	$("#shaRAccept").click(function(){

		//判断是否选中同意分销协议
		if($("#toSaleRole img").attr("src")=="images/uncheck@2x.png"){
			alert("请先同意爱个购分销用户协议！");
		}else {

			//调成为二级分销商接口
			toBeSecondSaler();

		}
	});

	$("#shaFCClose").click(function(){
		$("#shaFConLay").css("display","none");
	});

	$("#toSaleRole img").click(function(){
		if($(this).attr("src")=="images/uncheck@2x.png"){
			$(this).attr("src","images/checkn@2x.png");
			$(this).next("span").text(1);
		}else{
			$(this).attr("src","images/uncheck@2x.png");
			$(this).next("span").text(0);
		}
	});

	$("#toSaleRole a").click(function(){
		window.location.href="shareRole.html?imgFlag="+$("#toSaleRole span").text()+"&from_member="+from_member+"&dis_store_id="+dis_store_id+"&goods_id="+goods_id+"&p_click=1";
	});

	$("#header a").click(function(){
		if(from_member==1){
			window.location.href=WapSiteUrl+"/member.html";
		}else{
			window.location.href=WapSiteUrl+"/tmpl/productdetail.html?goods_id="+goods_id+"&dis_store_id="+dis_store_id;
		}
	});

	//var type=getcookie("type");
	if(type=='android'){
		$("#header").hide();
		$("#toSaleRole").hide();
		$("#shaRAccept").hide();
		$("#shaFCon").css("margin-top","0");

		$("#shaRAcceptForAnd").css("height","60px");
	}else if(type=='ios'||type=='iOS'){
		$("#header").hide();
		$("#toSaleRole").hide();
		$("#shaRAccept").hide();
		$("#shaFCon").css("margin-top","0");

		$("#shaRAcceptForAnd").css("height","60px");
	}
	
	 

});

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

function toBeSecondSaler(){

	$.ajax({
		url:ApiUrl+"/index.php?act=member_index&op=upgrade_distribution&key="+key+"&client_type=wap",
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				$("#shaFConLay").css("display", "block");
				$("#shaFCongraBox").css("height", $("#shaFCongraBox").width());

				var mTop = ($(window).height() - $("#shaFCongraBox").height()) / 2;
				$("#shaFCongraBox").css("margin-top", mTop);

				$("#shaFCConB").css("padding-top", ($("#shaFCCon").height() - 120) / 2);

				 $("#shaFCImg img").css("width","100%");
				 $("#shaFCImg img").css("height","100%");
			}else{
				alert(data.message);
			}
		}
	});
}

