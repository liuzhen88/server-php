$(function () {
	FastClick.attach(document.body);

	var key = getcookie('key');

	if (key != '') {
		//地址模板渲染
		$.ajax({
			url: ApiUrl + "/index.php?act=member_address_league&op=adt_address_list&client_type=wap&key=" + key  ,
			type: "get",
			dataType: "jsonp",
			jsonp: "callback",
			success: function (data) {
				if(data.code==200){
					var addrListTmpl = doT.template($("#addrListTmpl").html());
					data.data.address_id = localStorage.getItem("addrId");
					$("#addressList").append($(addrListTmpl(data.data)));
				}else if(data.code == 80001){
					alert('账号已失效，请重新登录~');
					window.location.href = WapSiteUrl + "/aidatui/login1.html";
				}else {
					alert(data.message);
				}
				//删除地址
				$('.del').on('click',function(){
					if(confirm("确定要删除地址吗？")) {
						var addrId = $(this).attr('data-addrId');
						var parentLi = $(this).parents('li');
						$.ajax({
							url: ApiUrl + "/index.php?act=member_address_league&op=adt_address_del&client_type=wap&key=" + key + "&address_id=" + addrId,
							type: "get",
							dataType: "jsonp",
							jsonp: "callback",
							success: function (data) {
								if (data.code == 200) {
									parentLi.remove();
								} else {
									alert(data.message);
								}
							}
						});
					}
				})
			}
		});
	}

	$('.position').on('click',function(){
		var iconP=$('.position').find('span');
		iconP.addClass('on');
		wx.ready(function(){
			wx.getLocation({
				type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
				success: function (res) {
					var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
					var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
					$.ajax({
						url: 'http://api.map.baidu.com/geocoder/v2/?ak=btsVVWf0TM1zUBEbzFz6QqWF&callback=renderReverse&location=' + latitude + ',' + longitude + '&output=json&pois=0&coordtype=wgs84ll',
						type: "get",
						dataType: "jsonp",
						jsonp: "callback",
						success: function (data) {
							if (data.status == 0) {
								latitude = data.result.location.lat;
								longitude = data.result.location.lng;
								var description = data.result.sematic_description;
								sessionStorage.setItem("wx_lat", latitude);
								sessionStorage.setItem("wx_lng", longitude);
								sessionStorage.setItem("wx_sematic_description", description);
								localStorage.setItem("latitude", latitude);
								localStorage.setItem("longitude", longitude);
								localStorage.setItem("description", description);
								window.location.href= WapSiteUrl + "/aidatui/index1.html";
							} else {
								alert('地址获取失败！');
								window.location.href = "position1.html"
							}
						}
					});
				},
				error:function(){
					alert('微信定位失败');
				}
			});
		});
	});
});