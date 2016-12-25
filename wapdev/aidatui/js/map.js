$(function(){
	var name = request('name');
	var mob = request('mob');
	var addrNum = request('addrNum');
	var lat = request('lat');
	var lng = request('lng');
	var status = request('status');
	var addressId=request('address_id');
	var order = request ('order');
	var cityId;

	$('.my-main').height($(window).height()-344);
	$('#searchBtn').focus(function(){
		$('.head-back').hide();
		$('.head-search').css('padding','7px 60px 7px 15px');
		$('.headR').show();
		$('.my-main').css('top','44px').height($(window).height()-44);
	}).on('input propertychange',function(){
		var keywords = $('#searchBtn').val();
		seachAddressList(keywords, cityId);
	});
	$('.headR').on('click',function(){
		$('.my-main').height($(window).height()-344).css('top','344px');
		$('.headR').hide();
		$('.head-search').css('padding','7px 15px 7px 44px');
		$('.head-back').show();
	});
	function addressList(latitude, longitude) {
		$.ajax({
			url: 'http://api.map.baidu.com/geocoder/v2/?ak=btsVVWf0TM1zUBEbzFz6QqWF&callback=renderReverse&location=' + latitude + ',' + longitude + '&output=json&pois=1',
			type: "get",
			dataType: "jsonp",
			jsonp: "callback",
			success: function (data) {
				cityId = data.result.cityCode;
				data.result.name = name;
				data.result.mob = mob;
				data.result.addrNum = addrNum;
				data.result.order = order;
				if(status == 'add'){
					var addrListTmpl = doT.template($("#addrListTmpl").html());
					$(".addrList").html(addrListTmpl(data.result));
				}else if(status == 'edit'){
					data.result.addressId = addressId;
					var editListTmpl = doT.template($("#editListTmpl").html());
					$(".addrList").html(editListTmpl(data.result));
				}

			}
		});
	}
	function seachAddressList(keywords, region) {
		$.ajax({
			url: 'http://api.map.baidu.com/place/v2/suggestion?ak=btsVVWf0TM1zUBEbzFz6QqWF&query='+ keywords +'&region='+ region +'&output=json',
			type: "get",
			dataType: "jsonp",
			jsonp: "callback",
			success: function (data) {
				data.name = name;
				data.mob = mob;
				data.addrNum = addrNum;
				data.order = order;
				if(status == 'add'){
					var searchListTmpl = doT.template($("#searchListTmpl").html());
					$(".addrList").html(searchListTmpl(data));
				}else if(status == 'edit'){
					data.addressId = addressId;
					var searcheditListTmpl = doT.template($("#searcheditListTmpl").html());
					$(".addrList").html(searcheditListTmpl(data));
				}
			}
		});
	}
	var map = new BMap.Map("allmap");
	function initMap(lng,lat){
		var point = new BMap.Point(lng,lat);
		map.centerAndZoom(point,16);
		map.enableDragging();
		map.addEventListener("dragend", function(){
			var center = map.getCenter();
			addressList(center.lat,center.lng);
		});
		addressList(lat,lng);
		map.enableScrollWheelZoom();
		$('.mapBtn').on('click',function(){
			map.panTo(point);
			addressList(lat,lng);
		});

	}
	if(lat == '' || lng == ''){ //判断地址链接上是否带有经纬度参数
		lat=sessionStorage.getItem('wx_lat');
		lng=sessionStorage.getItem('wx_lng');
		if(lat == 'null'||lng == 'null'||lat == null||lng == null||lat == '' || lng == ''){
			var timestamp=new Date().getTime()+"";
			timestamp=timestamp.substring(0,10);
			var ranStr=randomString();
			var nurl=document.URL;

			nurl=nurl.replace(/&/g,",,,");
			nurl=encodeURI(nurl);

			function randomString(len) {
				len = len || 20;
				var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
				var maxPos = $chars.length;
				var pwd = '';
				for (i = 0; i < len; i++) {
					pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
				}
				return pwd;
			}

			$.ajax({
				url:"http://www.51aigegou.cn/aigegou/ws/webGetTicketSignCommon?timestamp="+timestamp+"&url="+nurl+"&nonceStr="+ranStr,
				type:'get',
				dataType:'jsonp',
				cache : false,
				jsonp:"jsonpcallback",
				success:function(data){
					wx.config({
						debug: false,
						appId: 'wxa0641282049ed265',
						timestamp:timestamp,
						nonceStr: ranStr,
						signature: data.sign.toLowerCase(),
						jsApiList: [
							'getLocation'
						]
					});
				},
				error:function(){
					console.log("错了");
				}
			});
			wx.ready(function(){
				wx.getLocation({
					type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
					success: function (res) {
						lat = res.latitude; // 纬度，浮点数，范围为90 ~ -90
						lng = res.longitude; // 经度，浮点数，范围为180 ~ -180。
						var point = new BMap.Point(lng,lat);
						var convertor = new BMap.Convertor();
						var pointArr = [];
						pointArr.push(point);
						convertor.translate(pointArr, 1, 5, translateCallback)
					}
				});
			});
			wx.error(function(res){
				alert("error");
			});

			//坐标转换完之后的回调函数
			translateCallback = function (data){
				if(data.status === 0) {
					initMap(data.points[0].lng,lat = data.points[0].lat);
				}
			};
		}else{
			initMap(lng,lat);
		}
	}else{
		initMap(lng,lat);
	}

});