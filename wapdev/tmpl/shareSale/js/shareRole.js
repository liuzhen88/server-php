// JavaScript Document
var imgFlag=request("imgFlag");
var from_member=request("from_member");
var dis_store_id=request("dis_store_id");
var goods_id=request("goods_id");
var p_click=request("p_click");

$(window).load(function(e){
	//$("#shaBgColor").css("height",$(window).height()-120);
	$("#shaBgImg").css("height",$("#shaBgColor").height()-20);
	$("#shaBgImg").css("width",$("#shaBgColor").width()-20);
	
	$("#shaRCon").css("height",$("#shaBgColor").height()-60);
	$("#shaRCon").css("width",$("#shaBgColor").width()-60);
	
});


$(document).ready(function(e) {
    $("#shaRAccept").click(function(){
		window.location.href="shareFunct.html?imgFlag="+imgFlag+"&from_member="+from_member+"&dis_store_id="+dis_store_id+"&goods_id="+goods_id;
	});
	//var type=getcookie("type");
	var type=getcookie("type");
	/*if(type=='android'){
		$("#shaBgColor").css("height",$(window).height()-110);
		$("#header").hide();
		$("#toSaleRole").hide();
		$("#shaRAccept").hide();
		$("#shaBgColor").css("margin-top","0px");

		$("#shaBgColor").css("height",$(window).height()-20);
		$("#shaBgImg").css("height",$("#shaBgColor").height()-20);
		$("#shaRCon").css("height",$("#shaBgColor").height()-60);
	}else if(type=='ios'||type=='iOS'){
		$("#shaBgColor").css("height",$(window).height()-110);
		$("#header").hide();
		$("#toSaleRole").hide();
		$("#shaRAccept").hide();
		$("#shaBgColor").css("margin-top","0px");
		$("#shaBgColor").css("height",$(window).height()-20);
		$("#shaBgImg").css("height",$("#shaBgColor").height()-20);
		$("#shaRCon").css("height",$("#shaBgColor").height()-60);
	}else{
		$("#shaBgColor").css("height",$(window).height()-110);
	}*/

	if(p_click!=1){
		$("#header").css("display","none");
		$("#shaRAccept").css("display","none");
		$("#shaBgColor").css("margin-top","0px");
		$("#shaBgColor").css("height",$(window).height()-10);
	}else{
		$("#shaBgColor").css("height",$(window).height()-110);
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
