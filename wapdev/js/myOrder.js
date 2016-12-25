// JavaScript Document
$(window).load(function(e){	
	$(".myOrder_header ul").css("width",374);
	$(".myOrder_header ul li").eq(0).css("border-bottom-color","#f44336");
	
	//$(".o_list_cs").css("width",$(window).width()-135);	

});

/*$(window).ready(function(e) {
	
	//计算商品件数和总额
    $(".order_list").each(function(index, element) {
		var numTotal=0;
		var totalMoney=0;
        $(this).find(".order_money").each(function(index, element) {
			var danNum=parseInt($(this).find("span").text().split("×")[1]);
            numTotal=parseInt($(this).find("span").text().split("×")[1])+numTotal;
			
			var danjia=$(this).text().split("×")[0];
			totalMoney=parseInt(danjia.split("¥")[1])*danNum+totalMoney;
        });
		var yunfeiStr=$(this).parents(".myOrder_box").find(".myO_t_black").text().split("¥")[1];
		
		var yunfei=parseInt(yunfeiStr.split("）")[0]);
		
		var mostTotal=totalMoney+yunfei;
		
		$(this).parents(".myOrder_box").find(".myO_t_jian").text(numTotal);
		$(this).parents(".myOrder_box").find(".myO_t_red").text("¥"+mostTotal);
    });

	//订单头部选中高亮
	$(".myOrder_header ul li").click(function(){
		$(".myOrder_header ul li").css("border-bottom-color","#fff");
		$(this).css("border-bottom-color","#f44336");
		
		//改变订单内容
		var notNFlag=0;
		if($(this).text()=="全部订单"){
			$(".myOrder_box").css("display","block");
			notNFlag=1;
		}else{
			var li_text=$(this).text();
			$(".myOrder_box").css("display","none");
			$(".myOrder_box").each(function(index, element) {
                var this_text=$(this).find(".orderBox_header span").text();
				
				if(li_text.indexOf(this_text)>=0){
					$(this).css("display","block");
					notNFlag=1;
				}
            });
		}
		if(notNFlag==0){
			alert("该栏目没有任何订单哦！");
			$(".myOrder_header ul li").eq(0).click();
		}
	});
	
	
});*/
