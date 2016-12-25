// JavaScript Document

$(window).load(function(e) {
	$(".class_item ul li").css("width",$(".class_item").width());
	$(".class_item ul li img").css("width",$(".class_item").width());
	
	$(".class_item ul li").css("height",$(".class_item ul li img").eq(0).height());
	
	//$(".first_class").css("height",$(".second_class").height()+10);
	
	if($(".class_item ul li").length>1){
		var tabRight=setInterval(function(){tabRigth(".class_item");},2500);
	}
	
	

});

$(window).ready(function(e) {
	$(".class_item img").on("swipeleft",function(){
		alert("向左划！");	
	});
	
	$(".class_item img").on("swiperight",function(){
  		alert("向右划!");
	});

});

function　tabRigth(className){
	var scrollWidth=$(className).width();
	var scrollLength=$(className).find("li").length;
	$(className).animate({scrollLeft:scrollWidth},500,function(){
		$(className).find("li").eq(scrollLength-1).after($(className).find("li").eq(0));
		$(className).animate({scrollLeft:0},0);
	});
}