$(function(){
	FastClick.attach(document.body);
	app.checkLogin();
	var windowH= $(window).height()-10;

	$(window).resize(function() {
		var thisHeight= $(window).height();
		if(thisHeight >= windowH){
			$('.order-btn').show();
		}else{
			$('.order-btn').hide();
		}
	});
	$('#message').focus(function(){
		$('.order-btn').hide();
	}).blur(function(){
		setTimeout(function(){
			$('.order-btn').show();
		},500);
	});
	var key = getcookie('key');
	var lat = localStorage.getItem("latitude");
	var lng = localStorage.getItem("longitude");
	var store_id=localStorage.getItem("store_id");
	var total;
	var goodsMoney;
	var allBuy = 1;

	// 获取购物车信息
	var cart_info_json=localStorage.getItem("saveObj");
	var jsonobj=JSON.parse(cart_info_json);
	var cart_info="";
	for(var k in jsonobj){
		cart_info+=k+"|"+jsonobj[k]+","
	}
	cart_info=cart_info.substring(0,cart_info.length-1);

	// 获取地址id
	var addrId = request('address_id');
	if (addrId == ''){
		if(localStorage.getItem("addrId") != null && localStorage.getItem("addrId") != "null"){
			addrId = localStorage.getItem("addrId");
		}
	}else{
		localStorage.setItem("addrId",addrId);
	}

	//时间选择
	var d = new Date();
	var startTime = 9;
	var endTime = 21;
	var startMinute=0;
	var endMinute=0;
	var hoursNow = parseInt(d.getHours());                              // 当前时间
	var minutesNow = parseInt(d.getMinutes());                           // 当前时间(分钟)
	var receiptDay = 0;
	var receiptTime = 0;
	var shopHours;
	function timeSelect(){
		var timeTable= [];                                            // 时间表
		var timeTableNow= [];                                         // 当前时间表

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
	}

	$.ajax({
		url:ApiUrl+"/index.php?act=member_buy_league&op=adb_buy_step1_nocart&key="+key+"&store_id="+store_id+"&lat="+lat+"&lng="+lng+"&cart_info="+cart_info+"&client_type=wap&address_id=" + addrId,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				if(data.data.cart_info.cart_list.length==0){
					alert('订单已生成，请在我的订单中付款');
					window.location.href = WapSiteUrl + "/aidatui/index1.html";
				}
				//地址信息
				if(data.data.address_info.address_id != undefined ){
					addrId = data.data.address_info.address_id;
					localStorage.setItem("latitude", data.data.address_info.lat);
					localStorage.setItem("longitude", data.data.address_info.lng);
					localStorage.setItem("description", data.data.address_info.address);
				}
				var addrTmpl = doT.template($("#addrTmpl").html());
				$("#addrInfo").html(addrTmpl(data.data));

				//计算总价
				var carriageFree = parseFloat(data.data.cart_info.adt_free_carriage_leave);
				var carriage = parseFloat(data.data.cart_info.adt_carriage_pre);
				goodsMoney = parseFloat(data.data.cart_info.money_goods);
				if(carriageFree > goodsMoney){
					total = app.accAdd(goodsMoney,carriage);
				}else{
					total = goodsMoney;
				}
				var totalTmpl = doT.template($("#totalTmpl").html());
				$("#totalList").html(totalTmpl(data.data));
				$('#total').text('￥'+total);

				//订单详细信息
				var orderTmpl = doT.template($("#orderTmpl").html());
				$("#orderInfo").html(orderTmpl(data.data));

				if(data.data.cart_info.all_buyalbe == 0 ){
					$('#orderBtn').css('background','#ccc');
					allBuy = 0;
					alert('您所购买的商品存在缺货哦');
				}

				// 加
				$(".numAdd").on("touchend",function(e){
					e.preventDefault;
					var $self = $(this);
					doAddorSub(1,$self,carriageFree,carriage);

				});
				// 减
				$(".numDel").on("touchend",function(e){
					e.preventDefault;
					var $self = $(this);
					doAddorSub(-1,$self,carriageFree,carriage);

				});
				// 删除
				$(".goodsDel").on("touchend",function(e){
					e.preventDefault;
					delete jsonobj[$(this).parent().attr("data-id")];
					var objTmp=JSON.stringify(jsonobj);
					localStorage.setItem("saveObj",objTmp);
					$(this).parents('li').remove();
					cart_info = '';
					for(var k in jsonobj){
						cart_info+=k+"|"+jsonobj[k]+","
					}
					cart_info=cart_info.substring(0,cart_info.length-1);
					if($("#orderInfo li").length == 0){
						window.location.href = WapSiteUrl + "/aidatui/index1.html";
					}
					$.ajax({
						url: ApiUrl + "/index.php?act=member_buy_league&op=adb_buy_step1_nocart&key=" + key + "&store_id=" + store_id + "&lat=" + lat + "&lng=" + lng + "&cart_info=" + cart_info + "&client_type=wap&address_id=" + addrId,
						type: "get",
						dataType: "jsonp",
						jsonp: "callback",
						success: function (data) {
							if (data.code == 200) {
								if(data.data.cart_info.all_buyalbe == 1 ){
									$('#orderBtn').css('background','#ff4946');
									allBuy = 1;
								}
							}
						}
					})
				});
				//营业时间
				if(data.data.store_info.ship_time != ""){
					shopHours = data.data.store_info.ship_time;
					startTime=parseInt(shopHours.split("-")[0].split(":")[0]);
					startMinute=parseInt(shopHours.split("-")[0].split(":")[1]);
					endTime=parseInt(shopHours.split("-")[1].split(":")[0]);
					endMinute=parseInt(shopHours.split("-")[1].split(":")[1]);
				}else{
					shopHours = "9:00-21:00";
				}

				//营业状态
				if(data.data.store_info.open_state==1){
					if(hoursNow<startTime||hoursNow>endTime||(hoursNow==startTime&&minutesNow<startMinute)||(hoursNow==endTime&&minutesNow>=endMinute)){
						$('#time').text('营业时间：'+shopHours);
						$('#orderBtn').css('background','#ccc').text("打烊啦").on('click',function(){
							alert('不在营业时间内，不能下单');
						})
					}else{
						//确认下单
						timeSelect();
						$('#orderBtn').on('click',function(){
							var message = $('#message').val();
							if(addrId == ''){
								alert('请选择收货地址');
							}else if(allBuy == 1){
								$.ajax({
									url: ApiUrl + "/index.php?act=member_buy_league&op=adt_buy_step2&client_type=wap&key=" + key +"&address_id=" + addrId +"&goods_buy=" +cart_info +"&date=" + receiptDay +"&time=" + receiptTime + "&order_message=" + message,
									type: "get",
									dataType: "jsonp",
									jsonp: "callback",
									success: function (data) {
										if(data.code==200){
											localStorage.removeItem("saveObj");
											window.location.href = WapSiteUrl + "/aidatui/pay1.html?pay_sn="+data.data.pay_sn+"&all_money="+total+"&order_sn="+data.data.order_sn;
										}else {
											alert(data.message);
										}
									}
								});
							}

						})
					}
				}else{
					$('#time').text('店铺休息中');
					$('#orderBtn').css('background','#ccc').text("打烊啦").on('click',function(){
						alert('店铺休息中，不能下单');
					})
				}
			}else if(data.code == 80001){
				alert('账号已失效，请重新登录');
				window.location.href = WapSiteUrl + "/aidatui/login1.html";
			}else {
				alert(data.message);
			}
		}
    });

	function doAddorSub(diff,that,carriageFree,carriage){
		//judge add or sub
		var numbPosition;
		var thisMoney = that.parent().attr("data-money");
		if(diff>0){
			numbPosition = that.prev();
			goodsMoney = app.accAdd(goodsMoney,thisMoney);
		} else{
			numbPosition = that.next();
			goodsMoney = app.accSub(goodsMoney,thisMoney);
		}
		if(goodsMoney<carriageFree){
			total = app.accAdd(goodsMoney,carriage);
			$('#carriage').text('￥'+carriage);
		}else{
			total = goodsMoney;
			$('#carriage').text('￥0');
		}
		$('#goodsMoney').text('￥'+goodsMoney);
		$('#total').text('￥'+total);
		var num =parseInt(numbPosition.text());
		var nums = num + diff;
		var id = that.parent().attr("data-id");
		if(nums > 0){
			numbPosition.text(nums);
			jsonobj[id]=nums;
		}else {//<=0
			delete jsonobj[id];
			that.parents('li').remove();
			if($("#orderInfo li").length == 0){
				window.location.href = WapSiteUrl + "/aidatui/index1.html";
			}
		}
		cart_info = '';
		for(var k in jsonobj){
			cart_info+=k+"|"+jsonobj[k]+","
		}
		cart_info=cart_info.substring(0,cart_info.length-1);

		var objTmp=JSON.stringify(jsonobj);
		localStorage.setItem("saveObj",objTmp);
	}
});
