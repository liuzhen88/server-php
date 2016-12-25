$(function(){
	app.order={
		init:function(){
			FastClick.attach(document.body);
			this.initDate();
			this.bindEven();

			$('.refresh').click(function(){
				window.location.reload();
			});
		},
		key:getcookie('key'),
		orderId:request('order_id'),
		initDate:function(){
			var self = this;
			$.ajax({
				url: ApiUrl + "/index.php?act=member_order&op=adt_order_detail&client_type=wap&key="+self.key+"&order_id="+self.orderId,
				type: "get",
				dataType: "jsonp",
				success: function (data) {
					if(data.code==200){
						var orderInfoTmpl = doT.template($("#orderInfoTmpl").html());
						$("#orderInfo").html(orderInfoTmpl(data.data));
						var orderDetailsTmpl = doT.template($("#orderDetailsTmpl").html());
						$("#details").html(orderDetailsTmpl(data.data.order));
					}
				}
			});
		},
		bindEven:function(){
			var self = this;
			$.ajax({
				//url: "http://devshop.aigegou.com/mobile/index.php?act=member_order&op=adt_order_detail&client_type=wap&key=89e995722b52ab5ed1bda4a090ee9f64&&order_id=19411",
				url: ApiUrl + "/index.php?act=member_order&op=adt_order_detail&client_type=wap&key="+self.key+"&order_id="+self.orderId,
				type: "get",
				dataType: "jsonp",
				success: function (data) {
					if(data.code==200){
						var all_money=data.data.order.goods_amount;
						var order_sn=data.data.order.order_sn;
						var pay_sn=data.data.order.pay_sn;
						var url="https://sp0.baidu.com/5aU_bSa9KgQFm2e88IuM_a/micxp1.duapp.com/qr.php?value="+data.data.order.consume_code+"|"+data.data.order.order_id;
						console.log(data.data.order.order_id);
						$("#getImage").attr("src",url);
						$(".shouhuoma span").html(data.data.order.consume_code);
						$(".phone").html(data.data.order.seller_mobile);
						$("#phone").attr("href","tel:"+data.data.order.seller_mobile);
						//订单状态接口
						$.ajax({
							//url:"http://devshop.aigegou.com/mobile/index.php?act=member_order&op=adt_order_state&client_type=wap&key=89e995722b52ab5ed1bda4a090ee9f64&order_id=19411",
							url: ApiUrl + "/index.php?act=member_order&op=adt_order_state&client_type=wap&key="+self.key+"&order_id="+self.orderId,
							type: "get",
							dataType: "jsonp",
							success: function (data) {
								if(data.code==200){
									var i=data.data.order.length-1;
									var line1=0;
									var line2=0;
									var line3=0;
									var line4=0;
									var top2=0;
									var top3=0;
									var top4=0;
									var time1,time2,time3,time4,time5,time6;//10,20,0,30,35,40
									/*状态*/
									if(data.data.order[i].log_orderstate==10){//未付款
										time1=data.data.order[i].log_time;
										$("#time1").html(time1);
										$("#step1").css('color','#777777').show().find(".tt").css('color','#000000');
										$(".foot1").show();
										$(".buy").click(function(){//付款跳转
											window.location.href=WapSiteUrl+"/aidatui/pay.html?pay_sn="+pay_sn+"&order_sn="+order_sn+"&all_money="+all_money;
										});
										$(".cancel2").click(function(){//未付款的订单取消跳转
											$(".zhezhao").show();
											$("#to-cancel").show();
											$("#cancel").click(function(){
												$(".zhezhao").hide();
												$("#to-cancel").hide();
											});
											$("#sure").click(function(){

												$.ajax({
													//url: "http://devshop.aigegou.com/mobile/index.php?act=member_order&op=adt_order_del&client_type=wap&key=89e995722b52ab5ed1bda4a090ee9f64&order_id=19411",
													url: ApiUrl + "/index.php?act=member_order&op=adt_order_del&client_type=wap&key="+self.key+"&order_id="+self.orderId,
													type: "get",
													dataType: "jsonp",
													success: function (data) {
														if(data.code==200){
															$(".foot1").hide();
															$(".zhezhao").hide();
															$("#to-cancel").hide();
															$("#step1").css('color','#999999').hide().find(".tt").css('color','#999999');
															$("#step4").css('color','#777777').show().find(".tt").css('color','#000000');
															time4=new Date();
															var Y=time4.getFullYear();
															var M=time4.getMonth()+1;
															var D=time4.getDate();
															var h=time4.getHours();
															var m=time4.getMinutes();
															var dates=Y+"-"+M+"-"+D+" "+h+":"+m;
															$("#time4").html(dates);
															$("#cancel-tip").html("订单已取消");
															//line1=$('.step1').height();
															//line2=$('.step4').height();
															//$(".line1").css('height',line2-30).css('top','145px');/*第一条线的高度*/
															$(".top1").css('top','115px');
															//$(".top2").css('top',line2+115);
														}

													}
												});

											});

										})
									}else if(data.data.order[i].log_orderstate==20){//已付款未接单
										time2=data.data.order[i].log_time;
										$("#time2").html(time2);
										$("#song-time").html(time2);
										$("#step2").css('color','#777777').show().find(".tt").css('color','#000000');
										$("#step3").hide();
										$(".foot2").show();
										$(".foot-cancel").click(function(){//订单取消跳转
											$(".zhezhao").show();
											$("#to-cancel").show();
											$("#cancel").click(function(){
												$(".zhezhao").hide();
												$("#to-cancel").hide();
											});
											$("#sure").click(function(){
												$.ajax({
													//url: "http://devshop.aigegou.com/mobile/index.php?act=member_order&op=adt_order_del&client_type=wap&key=89e995722b52ab5ed1bda4a090ee9f64&order_id=19411",
													url: ApiUrl + "/index.php?act=member_order&op=adt_order_del&client_type=wap&key="+self.key+"&order_id="+self.orderId,
													type: "get",
													dataType: "jsonp",
													success: function (data) {
														if(data.code==200){
															$(".foot2").hide();
															$(".zhezhao").hide();
															$("#to-cancel").hide();
															$("#step2").css('color','#999999').show().find(".tt").css('color','#999999');
															$("#step4").css('color','#777777').show().find(".tt").css('color','#000000').css('border-bottom','1px solid #d6d6d6');
															time4=new Date();
															var Y=time4.getFullYear();
															var M=time4.getMonth()+1;
															var D=time4.getDate();
															var h=time4.getHours();
															var m=time4.getMinutes();
															var datas=Y+"-"+M+"-"+D+" "+h+":"+m;
															$("#time4").html(datas);
															$("#cancel-tip").html("订单已取消，退款将以积分的形式退回至您的积分中，可以在我的积分中提现");
															line1=$('.step1').height();
															line2=$('.step2').height();
															line3=$('.step4').height();
															$(".line1").css('height',line3-30).css('top','145px');/*第一条线的高度*/
															//$(".line2").css('height',line2-30).css('top',line3+145);/*第二条线的高度*/
															$(".top1").css('top','115px');
															$(".top2").css('top',line3+115);
															//$(".top3").css('top',line2+115+line3);
														}

													}

												});
											});


										})
									}else if(data.data.order[i].log_orderstate==0){//取消订单状态
										time4=data.data.order[i].log_time;
										$("#time4").html(time4);
										$("#step4").css('color','#777777').show().find(".tt").css('color','#000000');
									}else if(data.data.order[i].log_orderstate==30){//已接单
										time3=data.data.order[i].log_time;
										time2=data.data.order[i-1].log_time;
										$("#time3").html(time3);
										$("#time2").html(time2);
										$("#step2").show();
										$("#step3").css('color','#777777').show().find(".tt").css('color','#000000');
										line1=$('.step2').height();
										line2=$('.step3').height();
										$(".line1").css('height',line2-30).css('top','145px');/*第一条线的高度*/
										$(".top1").css('top','115px');
										$(".top2").css('top',line2+115);

									}else if(data.data.order[i].log_orderstate==35){//已发货
										time2=data.data.order[i-2].log_time;
										time3=data.data.order[i-1].log_time;
										time5=data.data.order[i].log_time;
										var tt = time5.replace(/-/g,"/");
										var t=times(tt);//预计到达+1小时
										$("#time2").html(time2);
										$("#time3").html(time3);
										$("#time5").html(time5);
										$("#step2").show();
										$("#step3").show();
										$("#step5").css('color','#777777').show().find(".tt").css('color','#000000');
										line1=$('.step2').height();
										line2=$('.step3').height();
										line3=$('.step5').height();
										$(".line1").css('height',line3-30).css('top','145px');/*第一条线的高度*/
										$(".line2").css('height',line2-30).css('top',line3+145);/*第二条线的高度*/
										$(".top1").css('top','115px');
										$(".top2").css('top',line3+115);
										$(".top3").css('top',line2+115+line3);
										$(".foot3").show();
										$("#expected").html(t);
										$(".foot-sure").click(function(){//确认收货
											$.ajax({
												//url: "http://devshop.aigegou.com/mobile/index.php?act=member_order&op=adt_order_receive_end&client_type=wap&key=89e995722b52ab5ed1bda4a090ee9f64&order_id=19411",
												url: ApiUrl + "/index.php?act=member_order&op=adt_order_receive_end&client_type=wap&key="+self.key+"&order_id="+self.orderId,
												type: "get",
												dataType: "jsonp",
												success: function (data) {
													if(data.code==200){
														time4=new Date();
														var Y=time4.getFullYear();
														var M=time4.getMonth()+1;
														var D=time4.getDate();
														var h=time4.getHours();
														var m=time4.getMinutes();
														var datas=Y+"-"+M+"-"+D+" "+h+":"+m;
														$("#time6").html(datas);
														$("#step2").show();
														$("#step3").show();
														$(".shouhuoma").css('color','#999999');
														$("#step5").show();
														$("#step6").css('color','#777777').show().find(".tt").css('color','#000000');
														line1=$('.step2').height();
														line2=$('.step3').height();
														line3=$('.step5').height();
														line4=$('.step6').height();
														$(".line1").css('height',line4-30).css('top','145px');/*第一条线的高度*/
														$(".line2").css('height',line3-30).css('top',line4+145);/*第二条线的高度*/
														$(".line3").css('height',line2-30).css('top',line3+line4+145);/*第3条线的高度*/
														$(".top1").css('top','115px');
														$(".top2").css('top',line4+115);
														$(".top3").css('top',line4+115+line3);
														$(".top4").css('top',line4+115+line3+line2);
														$(".foot3").hide();
													}
												}
											});

										});
										$(".call").click(function(){//催单
											$(".zhezhao").show();
											$("#phone-tip").show();
										});
										$("#phone-cancel").click(function(){//取消拨号
											$(".zhezhao").hide();
											$("#phone-tip").hide();
										});
										$("#phone-call").click(function(){//拨号
											//获得电话呼叫
											$(".zhezhao").hide();
											$("#phone-tip").hide();
										});

									}else if(data.data.order[i].log_orderstate==40){//已收货
										time2=data.data.order[i-3].log_time;
										time3=data.data.order[i-2].log_time;
										time5=data.data.order[i-1].log_time;
										time6=data.data.order[i].log_time;
										time3 = time3.replace(/-/g,"/");
										time6 = time6.replace(/-/g,"/");
										var endTime = (new Date(time6)).getTime();
										var beginTime = (new Date(time3)).getTime();
										var yongshi = new Date(endTime-beginTime);
										var h=yongshi.getHours();//shi
										var m=yongshi.getMinutes();//分
										var s=yongshi.getSeconds();//秒
										//var strTime="用时"+m+"分"+s+"秒";//下个版本会用到
										$("#time2").html(time2);
										$("#time3").html(time3);
										$("#time5").html(time5);
										$("#time6").html(time6);
										$("#step2").show();
										$("#step3").show();
										$(".shouhuoma").css('color','#999999');
										$("#step5").show();
										$("#step6").css('color','#777777').show().find(".tt").css('color','#000000');
										line1=$('.step2').height();
										line2=$('.step3').height();
										line3=$('.step5').height();
										line4=$('.step6').height();
										$(".line1").css('height',line4-30).css('top','145px');/*第一条线的高度*/
										$(".line2").css('height',line3-30).css('top',line4+145);/*第二条线的高度*/
										$(".line3").css('height',line2-30).css('top',line3+line4+145);/*第3条线的高度*/
										$(".top1").css('top','115px');
										$(".top2").css('top',line4+115);
										$(".top3").css('top',line4+115+line3);
										$(".top4").css('top',line4+115+line3+line2);
									}

								}
							}
						});
					}
				}
			});



			$('.nav-header').on('click','li',function() {
				$('.nav-header li').removeClass('on');
				$(this).addClass('on');
				var navId=$(this).attr('id');
				if (navId == 'orderStatus') {
					$('.order-details').hide();
					$('.steps').show();
					$('.refresh').show();
				}else if(navId == 'orderDetails'){
					$('.steps').hide();
					$('.order-details').show();
					$('.refresh').hide();
				}
			})
		}
	};

	app.order.init();
});
function times(a){
	var oldTime = (new Date(a)).getTime();//haomiao值
	var newTime  =  new Date(oldTime+1000*60*60);
	var Y=newTime.getFullYear();
	var M=newTime.getMonth()+1;
	var D=newTime.getDate();
	var h=newTime.getHours();
	var m=newTime.getMinutes();
	var datas=Y+"-"+M+"-"+D+" "+h+":"+m;
	return datas;
};
