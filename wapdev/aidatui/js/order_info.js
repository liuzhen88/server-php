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
		time0:'',
		time10:'',
		time20:'',
		time30:'',
		time35:'',
		time40:'',
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
						var all_money=data.data.order.goods_amount;
						var order_sn=data.data.order.order_sn;
						var pay_sn=data.data.order.pay_sn;
						var evaluation_state=data.data.order.evaluation_state;
						var complain_state=data.data.order.complain_state;
						var url="https://sp0.baidu.com/5aU_bSa9KgQFm2e88IuM_a/micxp1.duapp.com/qr.php?value="+data.data.order.consume_code+"|"+self.orderId;
						$("#getImage").attr("src",url);
						$(".shouhuoma span").html(data.data.order.consume_code);
						$(".phone").html(data.data.order.seller_mobile);
						$("#phone").attr("href","tel:"+data.data.order.seller_mobile);
						//订单状态接口
						$.ajax({
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
									/*状态*/
									if(data.data.order[i].log_orderstate==10){//未付款
										self.time10=data.data.order[i].log_time;
										$("#time1").html(self.time10);
										$("#step1").css('color','#777777').show().find(".tt").css('color','#000000');
										$(".foot1").show();
										$(".buy").click(function(){//付款跳转
											window.location.href=WapSiteUrl+"/aidatui/pay1.html?pay_sn="+pay_sn+"&order_sn="+order_sn+"&all_money="+all_money;
										});
										self.cancelOrder(0);
									}else if(data.data.order[i].log_orderstate==20){//已付款未接单
										self.time20=data.data.order[i].log_time;
										$("#time2").html(self.time20);
										$("#song-time").html(self.time20);
										$("#step2").css('color','#777777').show().find(".tt").css('color','#000000');
										$("#step3").hide();
										$(".foot2").show();
										self.cancelOrder(1);
									}else if(data.data.order[i].log_orderstate==0){//取消订单状态
										if(data.data.order.length == 2){
											self.time0=data.data.order[i].log_time;
											self.time10=data.data.order[i-1].log_time;
											$("#time4").html(self.time0);
											$("#time1").html(self.time10);
											$("#step4").css('color','#777777').show().find(".tt").css('color','#000000');
											$("#step1").show();
											line1=$('.step4').height();
											$(".line1").css('height',line1-15).css('top','72px');/*第一条线的高度*/
											$(".top2").css('top',line1+57);
										}else if(data.data.order.length == 3){
											self.time0=data.data.order[i].log_time;
											self.time20=data.data.order[i-1].log_time;
											$("#time4").html(self.time0);
											$("#time2").html(self.time20);
											$("#step4").css('color','#777777').show().find(".tt").css('color','#000000');
											$("#cancel-tip").html("订单已取消，退款预计1-7个工作日内原路退还");
											$("#step2").show();
											line1=$('.step4').height();
											$(".line1").css('height',line1-15).css('top','72px');/*第一条线的高度*/
											$(".top2").css('top',line1+57);
										}else if(data.data.order.length == 4){
											var refundTime=data.data.order[i].log_time;
											self.time0=data.data.order[i-1].log_time;
											self.time20=data.data.order[i-2].log_time;
											$("#time7").html(refundTime);
											$("#time4").html(self.time0);
											$("#time2").html(self.time20);
											$("#step7").css('color','#777777').show().find(".tt").css('color','#000000');
											$("#refund").html("退款日期："+refundTime);
											$("#cancel-tip").html("订单已取消，退款预计1-7个工作日内原路退还");
											$("#step4").show();
											$("#step2").show();
											line1=$('.step7').height();
											line2=$('.step4').height();
											$(".line1").css('height',line1-15).css('top','72px');/*第一条线的高度*/
											$(".line2").css('height',line2-15).css('top',line1+72);/*第二条线的高度*/
											$(".top2").css('top',line1+57);
											$(".top3").css('top',line2+57+line1);
										}
									}else if(data.data.order[i].log_orderstate==30){//已接单
										self.time30=data.data.order[i].log_time;
										self.time20=data.data.order[i-1].log_time;
										$("#time3").html(self.time30);
										$("#time2").html(self.time20);
										$("#step2").show();
										$("#step3").css('color','#777777').show().find(".tt").css('color','#000000');
										line1=$('.step2').height();
										line2=$('.step3').height();
										$(".line1").css('height',line2-15).css('top','72px');/*第一条线的高度*/
										$(".top1").css('top','57px');
										$(".top2").css('top',line2+57);

									}else if(data.data.order[i].log_orderstate==35){//已发货
										self.time20=data.data.order[i-2].log_time;
										self.time30=data.data.order[i-1].log_time;
										self.time35=data.data.order[i].log_time;
										var tt = self.time35.replace(/-/g,"/");
										var t=times(tt);//预计到达+1小时
										$("#time2").html(self.time20);
										$("#time3").html(self.time30);
										$("#time5").html(self.time35);
										$("#step2").show();
										$("#step3").show();
										$("#step5").css('color','#777777').show().find(".tt").css('color','#000000');
										line1=$('.step2').height();
										line2=$('.step3').height();
										line3=$('.step5').height();
										$(".line1").css('height',line3-15).css('top','72px');/*第一条线的高度*/
										$(".line2").css('height',line2-15).css('top',line3+72);/*第二条线的高度*/
										$(".top1").css('top','57px');
										$(".top2").css('top',line3+57);
										$(".top3").css('top',line2+57+line3);
										$(".foot3").show();
										$("#expected").html(t);
										$(".foot-sure").click(function(){//确认收货
											var r=confirm("确定要确认收货吗?");
											if(r==true) {
												$.ajax({
													url: ApiUrl + "/index.php?act=member_order&op=adt_order_receive_end&client_type=wap&key=" + self.key + "&order_id=" + self.orderId,
													type: "get",
													dataType: "jsonp",
													success: function (data) {
														if (data.code == 200) {
															$("#time6").html(times(0));
															$("#step2").show();
															$("#step3").show();
															$(".shouhuoma").css('color', '#999999');
															$("#step5").show();
															$("#step6").css('color', '#777777').show().find(".tt").css('color', '#000000');
															line1 = $('.step2').height();
															line2 = $('.step3').height();
															line3 = $('.step5').height();
															line4 = $('.step6').height();
															$(".line1").css('height', line4 - 15).css('top', '72px');
															/*第一条线的高度*/
															$(".line2").css('height', line3 - 15).css('top', line4 + 72);
															/*第二条线的高度*/
															$(".line3").css('height', line2 - 15).css('top', line3 + line4 + 72);
															/*第3条线的高度*/
															$(".top1").css('top', '57px');
															$(".top2").css('top', line4 + 57);
															$(".top3").css('top', line4 + 57 + line3);
															$(".top4").css('top', line4 + 57 + line3 + line2);
															$(".foot3").hide();
														}
													}
												});
											}

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
										if(evaluation_state==1){
											$(".bottom-left").text("已评价");
										}
										if(evaluation_state==2){
											$(".bottom-left").text("已过期");
										}
										if(complain_state==1){
											$(".bottom-right").text("已投诉");
										}
										$(".bottom").show();
										self.time20=data.data.order[i-3].log_time;
										self.time30=data.data.order[i-2].log_time;
										self.time35=data.data.order[i-1].log_time;
										self.time40=data.data.order[i].log_time;
										$("#time2").html(self.time20);
										$("#time3").html(self.time30);
										$("#time5").html(self.time35);
										$("#time6").html(self.time40);
										$("#step2").show();
										$("#step3").show();
										$(".shouhuoma").css('color','#999999');
										$("#step5").show();
										$("#step6").css('color','#777777').show().find(".tt").css('color','#000000');
										line1=$('.step2').height();
										line2=$('.step3').height();
										line3=$('.step5').height();
										line4=$('.step6').height();
										$(".line1").css('height',line4-15).css('top','72px');/*第一条线的高度*/
										$(".line2").css('height',line3-15).css('top',line4+72);/*第二条线的高度*/
										$(".line3").css('height',line2-15).css('top',line3+line4+72);/*第3条线的高度*/
										$(".top1").css('top','57px');
										$(".top2").css('top',line4+57);
										$(".top3").css('top',line4+57+line3);
										$(".top4").css('top',line4+57+line3+line2);

										//评价
										$(".bottom-left").click(function(){
											if(evaluation_state==0){
												var is_get_quickly=request("is_get_quickly");
												window.location.href=WapSiteUrl+"/aidatui/evaluate1.html?order_id="+self.orderId+"&is_get_quickly="+is_get_quickly;
											}

										});
										//投诉
										$(".bottom-right").click(function(){
											if(complain_state==0){
												window.location.href=WapSiteUrl+"/aidatui/complaint1.html?order_sn="+order_sn+"&order_id="+self.orderId;
											}

										});

									}

								}
							}
						});
						//定时请求接口
						setInterval(function(){
							$.ajax({
								url: ApiUrl + "/index.php?act=member_order&op=adt_order_detail&client_type=wap&key="+self.key+"&order_id="+self.orderId,
								type:"get",
								dataType:"jsonp",
								jsonp:"callback",
								success:function(data){
									if(data.code==200){
										var order_state_change=data.data.order.order_state;
										if(localStorage.getItem("order_state_change")){
											if(order_state_change!=localStorage.getItem("order_state_change")){
												//变化了
												localStorage.setItem("order_state_change",order_state_change);
												window.location.reload();
											}else{
												console.log("没有变化");
											}
										}else{
											localStorage.setItem("order_state_change",order_state_change);
										}
									}
								}

							});
						},5000);

					}
				}

			});

		},
		bindEven:function(){
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
		},
		cancelOrder:function(paystatus){
			var self = this;
			$(".cancelOrder").click(function(){//未付款的订单取消跳转
				$(".zhezhao").show();
				$("#to-cancel").show();
				$("#cancel").click(function(){
					$(".zhezhao").hide();
					$("#to-cancel").hide();
				});
				$("#sure").click(function(){
					$.ajax({
						url: ApiUrl + "/index.php?act=member_order&op=adt_order_del&client_type=wap&key="+self.key+"&order_id="+self.orderId,
						type: "get",
						dataType: "jsonp",
						success: function (data) {
							if(data.code==200){
								$(".zhezhao").hide();
								$("#to-cancel").hide();
								if(paystatus == 0){
									$(".foot1").hide();
									$("#time4").html(times(0));
									$("#step4").css('color','#777777').show().find(".tt").css('color','#000000');
									$("#step1").css('color','#999999').find(".tt").css('color','#999999');
									var line1=$('.step4').height();
									$(".line1").css('height',line1-15).css('top','72px');/*第一条线的高度*/
									$(".top2").css('top',line1+57);
								}else{
									$(".foot2").hide();
									$("#time4").html(times(0));
									$("#time2").html(self.time20);
									$("#step4").css('color','#777777').show().find(".tt").css('color','#000000');
									$("#cancel-tip").html("订单已取消，退款预计1-7个工作日内原路退还");
									$("#step2").css('color','#999999').find(".tt").css('color','#999999');
									var line1=$('.step4').height();
									$(".line1").css('height',line1-15).css('top','72px');/*第一条线的高度*/
									$(".top2").css('top',line1+57);
								}
							}
						}
					});

				});

			});
		}
	};

	app.order.init();
});
function times(a){
	if(a == 0){
		var oldTime = (new Date()).getTime();
	}else{
		var oldTime = (new Date(a)).getTime();
	}
	var newTime  =  new Date(oldTime+1000*60*60);
	var Y=newTime.getFullYear();
	var M=newTime.getMonth()+1;
	var D=newTime.getDate();
	var h=newTime.getHours();
	var m=newTime.getMinutes();
	var datas=Y+"-"+M+"-"+D+" "+h+":"+m;
	return datas;
}