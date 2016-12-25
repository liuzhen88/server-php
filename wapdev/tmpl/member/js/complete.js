$(document).ready(function(){
	var reason_id;
	var key=getcookie("key");
	var rec_id=request("rec_id");
	var money=request("money");
	var num=request("num");
	var refund=0;//用来标记用户选中的退款类型
	/*if(key==''){
	 //window.location.href=WapSiteUrl+"/tmpl/member/login.html";
	 }else{*/
	var order_id=request("order_id");
	var goods_num=$("#num").html();
	$("#money").html(money);
	$("#add").click(function(){
		if(goods_num<num){
			goods_num++;
			$("#num").html(goods_num);
		}else{
			alert("退款数量不可超过所购数量");
		}
	});
	$("#reduce").click(function(){
		if(goods_num>1){
			goods_num--;
			$("#num").html(goods_num);
		}else{
			alert("退款数量不能为零!");
		}

	});






	/*	}*/
});