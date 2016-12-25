$(function(){
	FastClick.attach(document.body);
	app.checkLogin();
	var windowH= $(window).height();
	$('.my-main').height(windowH - 204);
	var key = getcookie('key');
	var lat = getcookie('latitude');
	var lng = getcookie('longitude');
	var storeId = request('store_id');
	var addrId = request('address_id');
	$.ajax({
		url: ApiUrl + "/index.php?act=member_buy_league&op=adt_buy_step1&client_type=wap&key=" + key +"&store_id=" + storeId + "&address_id=" + addrId +"&lat="+lat+"&lng="+lng,
		type: "get",
		dataType: "jsonp",
		jsonp: "callback",
		success: function (data) {
			if(data.code==200){
				if(data.data.cart_info.cart_list.length==0){
					alert('您目前没有等待提交的订单哦~返回首页再次下单');
					window.location.href = WapSiteUrl + "/aidatui/index.html";
				}
				//地址信息
				addrId = data.data.address_info.address_id;
				var addrTmpl = doT.template($("#addrTmpl").html());
				$("#addrInfo").html(addrTmpl(data.data));

				// 计算运费和总价
				var total;
				data.data.total = data.data.cart_info.money_goods;
				data.data.carriage = 0;
				if(data.data.cart_info.adt_free_carriage_leave > parseFloat(data.data.cart_info.money_goods)){
					data.data.total = parseFloat(data.data.cart_info.money_goods) + data.data.cart_info.adt_carriage_pre;
					data.data.carriage = 1;
				}
				total = data.data.total;
				$(".total span").text('￥'+total);

				//订单详细信息
				var orderTmpl = doT.template($("#orderTmpl").html());
				$("#orderInfo").html(orderTmpl(data.data));
				$("#carriage").text('￥'+data.data.cart_info.adt_carriage_pre);

				// 商品列表
				var goods = '' ;
				var noGoods = 0 ;
				$(data.data.cart_info.cart_list).each(function (k, v) {
					if(v.buyalbe ==1){
						goods=goods + v.goods_id+'|'+v.goods_num+',';
					}else if(v.buyalbe ==0){
						noGoods ++;
					}
				});
				goods=goods.substring(0,goods.length-1);

				//时间选择
				var d = new Date();
				var hoursNow = parseInt(d.getHours());                              // 当前时间
				var minutesNow = parseInt(d.getMinutes());                           // 当前时间(分钟)
				var timeTable= [];                                            // 时间表
				var timeTableNow= [];                                         // 当前时间表
				var startTime = 9;
				var endTime = 21;
				var receiptDay = 0;
				var receiptTime = 0;

				function setTable(minutes,startTime,endTime,Table){
					var i , j;
					if(minutes >= 30){
						startTime = startTime + 1;
					}
					for(i=startTime;i<endTime;i++ ){
						j= i + 1;
						Table.push(i+':00-'+j+':00');
					}
				}

				function setSelect(Table){
					for(var i=0; i<Table.length;i++){
						var objdiv = "<option>" + Table[i] + "</option>";
						$(".sel2").append(objdiv);
					}
				}

				function setTime(time){
					if(time == 0){
						$(".sel2").append('<option>及时达(1小时内)</option>');
						setSelect(timeTableNow);
					}else {
						setSelect(timeTable);
					}
				}

				function selVal(){
					var selVal=$(".sel2").find("option:selected").text();
					if(selVal == '及时达(1小时内)'){
						receiptTime = 0;
					}else {
						receiptTime =selVal;
					}
				}

				setTable(minutesNow,hoursNow,endTime,timeTableNow);
				setTable(0,startTime,endTime,timeTable);
				setTime(0);

				$(".sel1").change(function () {
					receiptDay = $(".sel1").val();
					$(".sel2 option").remove();
					setTime(receiptDay);
					selVal();
				});
				$(".sel2").change(function () {
					selVal();
				});
				function confirmAjax(){
					var message = $('#message').val();
					$.ajax({
						url: ApiUrl + "/index.php?act=member_buy_league&op=adt_buy_step2&client_type=wap&key=" + key +"&address_id=" + addrId +"&goods_buy=" +goods +"&date=" + receiptDay +"&time=" + receiptTime + "&order_message=" + message,
						type: "get",
						dataType: "jsonp",
						jsonp: "callback",
						success: function (data) {
							console.log(data);
							if(data.code==200){
								window.location.href = WapSiteUrl + "/aidatui/pay.html?pay_sn="+data.data.pay_sn+"&all_money="+total+"&order_sn="+data.data.order_sn;
							}else {
								alert(data.message);
							}
						}
					})
				}
				$('#orderBtn').on('click',function(){
					if(data.data.cart_info.cart_list.length == noGoods){
						alert('非常抱歉，您选购的商品目前缺货，您可以选择其他类似商品。');
						window.location.href = WapSiteUrl + "/aidatui/index.html?set_location="+ data.data.address_info.address +"&lat="+ data.data.address_info.lat +"&lng="+ data.data.address_info.lng +"&address_id="+ addrId;
					}else if(data.data.cart_info.all_buyalbe == 0){
						alert('您的订单内包含缺货的商品，是否删除该商品继续购买？');
						confirmAjax();
					}else{
						confirmAjax();
					}
				});
			}else if(data.code == 80001){
				alert('账号已失效，请重新登录');
				window.location.href = WapSiteUrl + "/aidatui/login.html";
			}else {
				alert(data.message);
			}
		}
	});

});