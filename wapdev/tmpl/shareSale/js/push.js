// JavaScript Document
$(window).ready(function(e){
	$("#shaFConLay").css("display","block");
	$("#shaFCongraBox").css("height",$("#shaFCongraBox").width());
	
	var mTop=($(window).height()-$("#shaFCongraBox").height())/2;
	$("#shaFCongraBox").css("margin-top",mTop);
	
	$("#shaFCConB").css("padding-top",($("#shaFCCon").height()-140)/2);
	
	$("#shaFCClose").click(function(){
		$("#shaFConLay").css("display","none");
	});

});