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
					var addrListDefaultTmpl = doT.template($("#addrListDefaultTmpl").html());
					$("#addressList").append($(addrListDefaultTmpl(data.data.address_list)));
					var addrListTmpl = doT.template($("#addrListTmpl").html());
					$("#addressList").append($(addrListTmpl(data.data.address_list)));
				}else if(data.code == 80001){
					alert('账号已失效，请重新登录~');
					window.location.href = WapSiteUrl + "/aidatui/login.html";
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
		app.getLocation.latAndLon(
			function (data) {
				app.getLocation.cityname(data.latitude, data.longitude, function (datas) {
					iconP.removeClass('on');
					console.log(datas.cityname,datas.street, datas.latitude, datas.longitude,datas.sematic_description);
					window.location.href= WapSiteUrl + "/aidatui/index.html?set_location=" + datas.sematic_description + "&lat=" + datas.latitude + "&lng=" + datas.longitude;
				});
			},
			function () {
				alert('您拒绝了位置共享服务，请清空缓存重新尝试');
				iconP.removeClass('on');
				window.location.href= WapSiteUrl + "/aidatui/index.html";
			}
		);
	});
});