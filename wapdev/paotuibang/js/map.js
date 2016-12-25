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
					data.result.addressId = addressId;
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
		map.addEventListener("dragend", function(){
			var center = map.getCenter();
			addressList(center.lat,center.lng);
		});
		addressList(lat,lng);
		map.enableScrollWheelZoom(true);
	}
	var geolocation = new BMap.Geolocation();
	geolocation.getCurrentPosition(function(r){
		var statusLocation = this.getStatus();
		if(lat == '' || lng == ''){
			if(statusLocation == BMAP_STATUS_SUCCESS){
				initMap(r.point.lng,r.point.lat);
			}else {
				alert('地址获取失败！');
				initMap(120.535357, 31.2782525);
			}
		}else {
			initMap(lng,lat);
		}
		$('.mapBtn').on('click',function(){
			if(statusLocation == BMAP_STATUS_SUCCESS){
				map.panTo(r.point);
				addressList(r.point.lat,r.point.lng);
			}else{
				alert('定位失败，请拖动地图选择你的位置');
			}
		})
	},{enableHighAccuracy: true});

});