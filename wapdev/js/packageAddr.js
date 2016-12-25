// JavaScript Document
$(window).load(function(e){
	$(".addr_box").css("width",$(window).width()-90);
	
	var urladdr=window.location.search.substr(1);
	
	if(urladdr==null || urladdr==""){
		$(".addr_list ul li").find(".addr_check").attr("class","addr_check");
		$(".addr_list ul li").find(".addr_check").eq(0).attr("class","addr_check addr_checkon");
		
	}else{
		var urlIndex=urladdr.split('=')[1];
		$(".addr_list ul li").find(".addr_check").attr("class","addr_check");
		$(".addr_list ul li").find(".addr_check").eq(urlIndex).attr("class","addr_check addr_checkon");
	}
	
});

$(window).ready(function(e) {
	
   	$(".addr_list ul li").click(function(){
		$(".addr_list ul li").find(".addr_check").attr("class","addr_check");
		$(this).find(".addr_check").attr("class","addr_check addr_checkon");
		
		var choose=$(".addr_list ul li").index(this)+1;
		alert("您选择了第"+choose+"个收货地址！");
		window.location.href="confirmOrder.html?addrIndex="+$(".addr_list ul li").index(this);
	});

});