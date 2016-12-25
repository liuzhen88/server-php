

$(window).ready(function(e) {

	FastClick.attach(document.body);

	var thisIdForStore=request("store_id");
	var key=getcookie('key');
	var thisInvitation;
	
    $.ajax({
		url:ApiUrl+"/index.php?act=unlimited_invitation&op=store_detail&client_type=wap&store_id="+thisIdForStore+"&callback=callback",
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(result){
			if(result.code==200){
				var storeDoTmpl = doT.template($("#storetmpl").html());
	            $("#storeMain").show().html(storeDoTmpl(result));
				
				thisInvitation=result.data.store_info.invitation;

				$(".star-box").click(function(){
					window.location.href=WapSiteUrl+"/evaluate_list.html?store_id="+result.data.store_info.store_id;
				});

				//分销列表
				$.ajax({
					url:ApiUrl+"/index.php?act=distribute&op=get_goods_list_by_store&store_id="+thisIdForStore+"&client_type=wap",
					type:"get",
					dataType:"jsonp",
					jsonp:"callback",
					success:function(result){
						if(result.code==200 && result.data!="\"null\"") {

							var shareListT = doT.template($("#shareListTmpl").html());
							$("#shareList").show().html(shareListT(result));

							$(".share-list").css("display", "none");

							if (result.data.data_list.length > 0) {
								$("#li-sharelist").css("display","inline-block");
								$(".store-nav li").css("width",$(window).width()*0.333);
							}

						}



					}
				});
				
				//收藏商品
				$.ajax({
					url:ApiUrl+"/index.php?act=user_action&op=is_favorites_store&key="+ key +"&client_type=wap&store_id="+thisIdForStore,
					type:"get",
					dataType:"jsonp",
					jsonp:"callback",
					success:function(data){
						if(data.data=="yes"){
							$('.favorites').addClass('f_on');
						}
					}
				});
				$(".favorites").click(function(){

					$.ajax({
						url:ApiUrl+"/index.php?act=user_action&op=is_favorites_store&key="+ key +"&client_type=wap&store_id="+thisIdForStore+"&token_member_id="+getcookie("user_id"),
						type:"get",
						dataType:"jsonp",
						jsonp:"callback",
						success:function(data){
							if(data.message==80001){
								alert(data.message);
								window.location.href=WapSiteUrl+"/tmpl/member/login.html";
							}
							if(data.data=="yes"){
								$.ajax({
									url:ApiUrl+"/index.php?act=member_favorites&op=favorites_del&fav_id="+thisIdForStore+"&key="+key+"&client_type=wap&type=store",
									type:"get",
									dataType:"jsonp",
									jsonp:"callback",
									success:function(data){
										if(data.code==200){
											$('.favorites').removeClass("f_on");
											alert("成功取消收藏！");
											return;
										}
									}
								});
							}else{
								$.ajax({
									url:ApiUrl+"/index.php?act=member_favorites&op=favorites_sotre_add&store_id="+thisIdForStore+"&key="+key+"&client_type=wap",
									type:"get",
									dataType:"jsonp",
									jsonp:"callback",
									success:function(data){
										if(data.code==200){
											$('.favorites').addClass('f_on');
											alert("收藏成功！");
											return;
										}
									}
								});
							}
						}
					});

				})
				
				$(".mdmPay").click(function(){
					var wHeight=$(window).height();
					var mdmHeight=200;
					
					$(".mdmPayLay").css("display","block");
					$("#moneyBT").val("");
					$(".mdmPayLayBox").css("margin-top",(wHeight-mdmHeight)/2);
				});
				
				$(".btnLayB1").click(function(){
					$(".mdmPayLay").css("display","none");
				});

				$(window).resize(function(){
					var wHeight=$(window).height();
					var mdmHeight=200;
					$(".mdmPayLayBox").css("margin-top",(wHeight-mdmHeight)/2);
				});

				$(".btnLayB2").click(function(){
					var exp = /^([1-9][\d]{0,7}|0)(\.[\d]{1})?$/;

					var moneyBTCon=$("#moneyBT").val();
					if(moneyBTCon==null || moneyBTCon=="" || moneyBTCon=="undefined"){
						alert("请输入消费金额！");
					}else if(isNaN(moneyBTCon)){
						alert("请输入正确的数字！");
					}else if(!exp.test(moneyBTCon)){
						var exp2 = /^([1-9][\d]{0,7}|0)(\.[\d]{1,100})?$/;
						if(exp2.test(moneyBTCon)){
							alert("请至多输入小数点后1位！");
						}else{
							alert("请输入正确的金额！");
						}
					}else{
						window.location.href="preSale/local_commit_order.html?store_id="+thisIdForStore+"&money="+moneyBTCon+"&invitation="+thisInvitation+"&o2o_order_type=1";
					}	
				});

			}
		}	
	});

});
function changMenu(thisID,obj_name){
	$(thisID).addClass('on').siblings().attr('class','');
	$("."+obj_name).siblings('div').hide();
	$("."+obj_name).show();

	//懒加载
	echo.init({
		offset: 10,
		throttle: 100,
		unload: false,
		callback: function (element, op) {
			//console.log(element, 'has been', op + 'ed')
		}
	});
}

function evaluateNum(num){
	var evaN;
	evaN=num*20;
	return evaN;

}

function toFixedTwo(price){
	return parseFloat(price).toFixed(2);
}


