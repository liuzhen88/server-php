$(function(){
	var name = request('name');
	var mob = request('mob');
	var addr = request('addr');
	var addrNum = request('addrNum');
	var lat = request('lat');
	var lng = request('lng');
	var status = request('status');
	var addressId=request('address_id');
	var order = request ('order');
	var cityId;


	$('#searchBtn').on('input propertychange',function(){
		var keywords = $('#searchBtn').val();
		seachAddressList(keywords, cityId);
	});
	function addressList(latitude, longitude) {
		$.ajax({
			url: 'http://api.map.baidu.com/geocoder/v2/?ak=btsVVWf0TM1zUBEbzFz6QqWF&callback=renderReverse&location=' + latitude + ',' + longitude + '&output=json&pois=1',
			type: "get",
			dataType: "jsonp",
			jsonp: "callback",
			success: function (data) {
				cityId = data.result.cityCode;
				if(addr != ""){
					$('#searchBtn').val(addr);
					seachAddressList(addr, cityId);
				}else{
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
	var geolocation = new BMap.Geolocation();
	geolocation.getCurrentPosition(function(r){
		var statusLocation = this.getStatus();
		if(lat == '' || lng == ''){
			if(statusLocation == BMAP_STATUS_SUCCESS){
				addressList(r.point.lat,r.point.lng);
			}else {
				alert('地址获取失败！');
				addressList(31.2782525,120.535357);
			}
		}else {
			addressList(lat,lng);
		}
	},{enableHighAccuracy: true});

});