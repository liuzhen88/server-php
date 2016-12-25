/*function tabRigth(className) {
	var scrollWidth = $(className).width();
	var scrollLength = $(className).find("li").length;
	$(className).animate({scrollLeft: scrollWidth}, 500, function () {
		$(className).find("li").eq(scrollLength - 1).after($(className).find("li").eq(0));
		$(className).animate({scrollLeft: 0}, 0);
	});
}*/

$(document).ready(function () {

	var id_url = window.location.search.substr(1);
	var gc_id = request("goods_id");
	if(gc_id==''||gc_id=='undefined'){
		gc_id=request("gc_id");
	}


	key=request("key");
	type=request("client_type").toLowerCase()||getcookie('type');
	addcookie("type",type);
	var version=request("version_name");
	if(version){
		version = version.match(/\d+/g).join('');
		addcookie("appVersion",version);
	}else{
		version = getcookie('appVersion');
	}

//分销旧版本隐藏按钮
	if( (type.toLowerCase() =='ios'||type=='android')&&(!(parseInt(version)>=211)) ){
		$('.shareSale_btn').hide();
		$('.car_btn').css('width','42%');
		$('.pay_btn').css('width','42%');
	}


	if (key == '' || key == 'undefined') {
		key = getcookie("key");
	} else {
		addcookie("key", key);
	}

	
	var dis_store_id=request("dis_store_id");
	var goods_image;
	var goods_name;
	var goodsImageUrl;
	var shareUrl;
	var goodsId;
	var quantity;
	var page = 1;
	var store_id;
	var pic_details;
	var storeName;
	var goods_marketprice, goods_price, goods_salenum;
	var goods_id = new Array(), goods_name = new Array(), goods_price = new Array(), goods_image_url = new Array(), lz_goods_id = new Array(), lz_goods_name = new Array(), lz_goods_price = new Array(), lz_goods_image_url = new Array();
	var hot_id = new Array(), hot_goods_name = new Array(), hot_goods_image = new Array(), hot_productPrice = new Array(), hot_store_id = new Array(), goods_image = new Array();
	var flag = 0;
	var is_distribution; /*表示能不能分销0不可1可以*/
	var dis_member_id=request("dis_member_id");
	if(dis_member_id==''||dis_member_id=='undefined'){
		//除h5页面过来的
		dis_member_id=getcookie("user_id");
	}


	$("#evaluate_Url").click(function () {
		window.location.href = WapSiteUrl + "/tmpl/evaluate/evaluate_list.html?good_id=" + gc_id;
	});

	$.ajax({
		url: ApiUrl + "/index.php?act=goods&op=goods_detail&goods_id=" + gc_id + "&client_type=wap",
		type: "get",
		dataType: "jsonp",
		jsonp: "callback",
		success: function (data) {
			if (data.code == 200) {
				goodsImageUrl=data.data.goods_image;
				goodsId=data.data.goods_info.goods_id;
				store_id = data.data.store_info.store_id;
				pic_details = data.data.goods_info.mobile_body;
				//goods_image=data.data.goods_image;
				goods_name = data.data.goods_info.goods_name;
				goods_marketprice = data.data.goods_info.goods_marketprice;
				goods_price = data.data.goods_info.goods_price;
				goods_salenum = data.data.goods_info.goods_salenum;
				goods_storage = data.data.goods_info.goods_storage;
				storeName=data.data.store_info.store_name;
				is_distribution=data.data.goods_info.is_distribution;
				$("#g_evaluate_span").text("(" + data.data.goods_info.evaluation_count + ")");
				if(is_distribution==0){
					$(".shareSale_btn").css("display","none");
					$(".car_btn").css("width","42%");
					$(".pay_btn").css("width","42%");	
				}
				if (data.data.goods_image.indexOf(",") > 0) {
					goods_image = data.data.goods_image.split(",");
					for (var k = 0; k < goods_image.length; k++) {
						$(".my_ul").append("<li><img src='" + goods_image[k] + "'/></li>");
					}
					goodsImageUrl=data.data.goods_image.split(",")[0];

				} else {
					goods_image = data.data.goods_image;
					$(".my_ul").append("<li><img src='" + goods_image + "'/></li>");
					goodsImageUrl=data.data.goods_image;
				}
				console.log(goodsImageUrl);
				//$(".my_productBox ul li img").css({
				//	"width": $(".my_productBox").width(),
				//	"height": $(".my_productBox").width()
				//});


				/*if ($(".my_productBox ul li").length > 1) {
					var tabRight = setInterval(function () {
						tabRigth(".my_productBox");
					}, 4500);
				}*/
				$("#select_color").click(function () {
					$(".data-more").toggle();
				});
				//$(".my_ul").append("<li><img src='"+goods_image+"'/></li>");
				$(".my_p").html(goods_name);
				$("#mark_price").html("市场价¥" + goods_marketprice);
				$("#lss").html("¥" + goods_price);
				$("#sale_num").html(goods_salenum);

				$(data.data.goods_commend_list).each(function (index, lmy) {
					lz_goods_id[index] = lmy.goods_id;
					lz_goods_name[index] = lmy.goods_name;
					lz_goods_price[index] = lmy.goods_price;
					lz_goods_image_url[index] = lmy.goods_image_url;
					var subdiv = "<div class='goods-item box-shw b-radius'><a><div class='goods-item-pic'><img src='" + lz_goods_image_url[index] + "'></div><div class='goods-item-name'>" + lz_goods_name[index] + "</div><div class='goods-item-price'>¥" + lz_goods_price[index] + "</div></a><span class='kk_id' style='display:none;'>" + lz_goods_id[index] + "</span></div>";
					$(".content").append(subdiv);
				});

				//点击推荐商品显示商品详情
				$(".goods-item").click(function () {
					var kk_goods_id = $(this).find(".kk_id").text();//获取到了商品的id
					//alert(kk_goods_id);
					window.location.href = "productdetail.html?goods_id=" + kk_goods_id;
				});

				$("#store_detailsUrl").click(function () {
					window.location.href = "shopDetail.html?store_id=" + store_id;
				});

				$("#pic_details").html(pic_details);
				//$("#pic_details img").css("width", "100%");
				//$("#pic_details img").css("overflow", "hidden");
				//添加库存数据
				if (goods_storage > 10000) {
					$(".g-num").html("库存：9999+");
				} else {
					$(".g-num").html("库存：" + goods_storage);
				}
				;
				//添加规格数据
				if (data.data.goods_info.spec_name == false || data.data.goods_info.spec_name == null) {
					$('.data-more').css('opacity', '0');
				} else {
					for (var spec_k in data.data.goods_info.spec_name) {
						var spec_name = '<dl id="data' + spec_k + '"><dt>' + data.data.goods_info.spec_name[spec_k] + '：</dt></dl>';
						$('.data-more').append(spec_name);
						$(data.data.goods_info.spec_value[spec_k]).each(function (k, v) {
							for (var kk in v) {
								var spec_value = '<dd id="' + kk + '">' + v[kk] + '</dd>';
								$('#data' + spec_k).append(spec_value);
							}

						})
					}

					//初始化规格
					var initailArray=new Array();
					var initailIndex=0;
					for (var spec_k in data.data.goods_info.goods_spec) {
						initailArray[initailIndex++]=spec_k;
					}

					$(".data-more dl").each(function (indexDL, thisDataDL) {
						//alert(thisDataDL+" "+indexDL);
						$(thisDataDL).find("dd").each(function(indexDD,thisDataDD){
							//alert(indexDD+" "+thisDataDD);
							if(initailArray[indexDL]==$(thisDataDD).attr("id")){
								$(thisDataDD).addClass("on");
							}
						});
					});


					$('.data-more dd').click(function () {
						var thisb = $(this);

						thisb.siblings("dd").removeClass('on');
						thisb.addClass("on");
						all_data = [];

						for (var i = 0; i < $('.data-more dl').length; i++) {
							$('.data-more dl').eq([i]).find('dd').each(function () {
								var this_id = $(this).attr('id');
								if ($(this).hasClass('on')) {
									data_id = this_id;
								}
							})
							all_data.push(data_id);
						}
						alld = all_data.join('|');
						for (var spec_k in data.data.spec_list) {
							if (spec_k == alld) {
								gc_id = data.data.spec_list[spec_k];
							}

						}

						$.ajax({
							url: ApiUrl + "/index.php?act=goods&op=goods_detail&goods_id=" + gc_id + "&client_type=wap",
							type: "get",
							dataType: "jsonp",
							jsonp: "callback",
							success: function (result){
								//更新数据
								$(".my_p").html(result.data.goods_info.goods_name);
								$("#mark_price").html("市场价¥" +result.data.goods_info.goods_marketprice);
								$("#lss").html("¥" + result.data.goods_info.goods_price);
								$("#sale_num").html(result.data.goods_info.goods_salenum);

								var gNum=result.data.goods_info.goods_storage;
								//添加库存数据
								if (gNum > 10000) {
									$(".g-num").html("库存：9999+");
								} else {
									$(".g-num").html("库存：" + gNum);
								}
							}
						});
					});
				}
				var data_id; //规格id
				var all_data = new Array(), alld = new Array();

				function repeat(a) {
					return /(\x0f[^\x0f]+)\x0f[\s\S]*\1/.test("\x0f" + a.join("\x0f\x0f") + "\x0f");
				}

				$("#buy").click(function () {
					if (key == '') {
						window.location.href = WapSiteUrl + "/tmpl/member/login.html";
					} else {
						if (data.data.goods_info.spec_name != null) {
							for (var i = 0; i < $('.data-more dl').length; i++) {
								$('.data-more dl').eq([i]).find('dd').each(function () {
									var this_id = $(this).attr('id');
									if ($(this).hasClass('on')) {
										data_id = this_id;
									}
								})
								all_data.push(data_id);
							}
							alld = all_data.join('|');
							for (var spec_k in data.data.spec_list) {
								if (spec_k == alld) {
									gc_id = data.data.spec_list[spec_k];
								}

							}

							if(dis_store_id==''||dis_store_id=='undefined'){
								window.location.href = WapSiteUrl + "/tmpl/confirmOrder.html?gc_id="+gc_id+"&dis_member_id="+dis_member_id;
							}else{
								window.location.href = WapSiteUrl + "/tmpl/confirmOrder.html?gc_id="+gc_id+"&dis_member_id="+dis_member_id+"&dis_store_id="+dis_store_id;
							}

							all_data = [];
						} else {
							if(dis_store_id==''||dis_store_id=='undefined'){
								window.location.href = WapSiteUrl + "/tmpl/confirmOrder.html?gc_id="+gc_id+"&dis_member_id="+dis_member_id;
							}else{
								window.location.href = WapSiteUrl + "/tmpl/confirmOrder.html?gc_id="+gc_id+"&dis_member_id="+dis_member_id+"&dis_store_id="+dis_store_id;
							}
						}

					}
				});



				//点击加入购物车
				$(".car_btn").click(function () {
					if (key == '') {
						window.location.href = WapSiteUrl + "/tmpl/member/login.html";
					} else {
						if (data.data.goods_info.spec_name != null) {
							for (var i = 0; i < $('.data-more dl').length; i++) {
								$('.data-more dl').eq([i]).find('dd').each(function () {
									var this_id = $(this).attr('id');
									if ($(this).hasClass('on')) {
										data_id = this_id;
									}
								})
								all_data.push(data_id);
							}
							alld = all_data.join('|');
							for (var spec_k in data.data.spec_list) {
								if (spec_k == alld) {
									gc_id = data.data.spec_list[spec_k];
								}

							}

							if(dis_member_id==''||dis_member_id=='undefined'){
								//说明是从app进的
									$.ajax({
										url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+key,
										type:"get",
										dataType:"jsonp",
										jsonp:"callback",
										success:function(data){
											if(data.code==200){
												dis_member_id=data.data.member_info.member_id;
												$.ajax({
													url: ApiUrl + "/index.php?act=member_cart&op=cart_add&quantity=1&goods_id=" + gc_id + "&client_type=wap&dis_member_id="+dis_member_id+"&dis_store_id="+dis_store_id+"&key=" + key,
													type: "get",
													dataType: "jsonp",
													jsonp: "callback",
													success: function (data) {
														console.log(dis_member_id);
														//加入购物车成功
														if(data.code==200){
															if (flag == 0) {
																alert("添加成功!");
																flag = 1;
															} else {
																alert("亲，您已经添加过啦!");
															}
														}else{
															alert(data.message);
														}

													}
												});
											}else if(data.code==80001){
													window.location.href = WapSiteUrl + "/tmpl/member/login.html";
											}
										}
									});

							}else{
								$.ajax({
											url: ApiUrl + "/index.php?act=member_cart&op=cart_add&quantity=1&goods_id=" + gc_id + "&client_type=wap&dis_member_id="+dis_member_id+"&dis_store_id="+dis_store_id+"&key=" + key,
											type: "get",
											dataType: "jsonp",
											jsonp: "callback",
											success: function (data) {
												console.log(dis_member_id);
											//加入购物车成功
												if(data.code==200){
													if (flag == 0) {
														alert("添加成功!");
														flag = 1;
													} else {
														alert("亲，您已经添加过啦!");
													}
												}else if(data.code==80001){
													 window.location.href = WapSiteUrl + "/tmpl/member/login.html";
												}else{
													alert(data.message);
												}

											}
									});
							}
							all_data = [];

						} else {
							if(dis_member_id==''||dis_member_id=='undefined'){

								//说明是从app进的
								$.ajax({
										url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+key,
										type:"get",
										dataType:"jsonp",
										jsonp:"callback",
										success:function(data){
											if(data.code==200){
												dis_member_id=data.data.member_info.member_id;
												$.ajax({
													url: ApiUrl + "/index.php?act=member_cart&op=cart_add&quantity=1&goods_id=" + gc_id + "&client_type=wap&dis_member_id="+dis_member_id+"&dis_store_id="+dis_store_id+"&key=" + key,
													type: "get",
													dataType: "jsonp",
													jsonp: "callback",
													success: function (data) {
														//加入购物车成功
														if(data.code==200){
															if (flag == 0) {
																alert("添加成功!");
																flag = 1;
															} else {
																alert("亲，您已经添加过啦!");
															}
														}else if(data.code==80001){
									                        window.location.href = WapSiteUrl + "/tmpl/member/login.html";
									                    }else{
															alert(data.message);
														}
													}
												});
											}
										}
								});
								
							}else{
								$.ajax({
										url: ApiUrl + "/index.php?act=member_cart&op=cart_add&quantity=1&goods_id=" + gc_id + "&client_type=wap&dis_member_id="+dis_member_id+"&dis_store_id="+dis_store_id+"&key=" + key,
										type: "get",
										dataType: "jsonp",
										jsonp: "callback",
										success: function (data) {
											//加入购物车成功
											if(data.code==200){
												if (flag == 0) {
													alert("添加成功!");
													flag = 1;
												} else {
													alert("亲，您已经添加过啦!");
												}
											}else{
												alert(data.message);
											}
										}
								});
							}
						}
					}

				});
				//分销
				$(".shareSale_btn a").click(function(){
					if(is_distribution==0){
						//alert("该商品暂不支持分销!");
					}else{
							var thisKey = getcookie('key');
							dis_member_id=getcookie("user_id");
							$.ajax({
								url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+thisKey,
								type:"get",
								dataType:"jsonp",
								jsonp:"callback",
								success:function(data){
									if(data.code == 200){
										//点击分销的取消
										$("#share_bottom").click(function(){

											$("#share").hide();
										});

										if(data.data.member_info.is_distribution==0){

											window.location.href=WapSiteUrl+"/tmpl/shareSale/shareFunct.html?dis_store_id="+dis_store_id+"&goods_id="+gc_id;

										}else{

											if(dis_member_id==''||dis_member_id=='undefined'){
												//说明是从app进的
												$.ajax({
													url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+thisKey,
													type:"get",
													dataType:"jsonp",
													jsonp:"callback",
													success:function(data){
														if(data.code==200){
															dis_member_id=data.data.member_info.member_id;
															 
															if(dis_store_id==''||dis_store_id=='undefined'){
																 
																//shareUrl=WapSiteUrl+'/tmpl/shareTmpl/index.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id;
																shareUrl=WapSiteUrl+'/tmpl/productdetail.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id;
															}else{
																//shareUrl=WapSiteUrl+'/tmpl/shareTmpl/index.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id+'&dis_store_id='+dis_store_id;
																shareUrl=WapSiteUrl+'/tmpl/productdetail.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id+'&dis_store_id='+dis_store_id;
															}

															if(type=='android'){

																app.share(storeName,goods_name,goodsImageUrl,shareUrl);
															}else if(type=='ios'||type=='iOS'){
																share(storeName,goods_name,goodsImageUrl,shareUrl);
															}else{
																$("#share").show();
																 
																share_wx();
																$(".share_img").click(function(){
																	$("#prompt").width($(window).width());
																	$("#prompt").height($(window).height());
																	$("#prompt").show();
																	$("#prompt").click(function(){
																		$("#prompt").hide();
																	});
																});
															}
														}
													}
												});
											}else{
												if(dis_store_id==''||dis_store_id=='undefined'){
													 
													//shareUrl=WapSiteUrl+'/tmpl/shareTmpl/index.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id;
													shareUrl=WapSiteUrl+'/tmpl/productdetail.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id;
												}else{
													//shareUrl=WapSiteUrl+'/tmpl/shareTmpl/index.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id+'&dis_store_id='+dis_store_id;
													shareUrl=WapSiteUrl+'/tmpl/productdetail.html?goods_id='+goodsId+'&dis_member_id='+dis_member_id+'&dis_store_id='+dis_store_id;
												}

												if(type=='android'){

													app.share(storeName,goods_name,goodsImageUrl,shareUrl);
												}else if(type=='ios'||type=='iOS'){
													share(storeName,goods_name,goodsImageUrl,shareUrl);
												}else{
													$("#share").show();
													 
													share_wx();
													$(".share").click(function(){
														$("#prompt").width($(window).width());
														$("#prompt").height($(window).height());
														$("#prompt").show();
														$("#prompt").click(function(){
															$("#prompt").hide();
														});
													});
												}
											}

												
											
										}
									}else if(data.code == 80001){
										alert(data.message);
										window.location.href=WapSiteUrl+"/tmpl/member/login.html";
									}else if(data.code == 80002){
										alert(data.message);
									}
								}
							});
					}
				});

				//懒加载
				//var imgArr=[];
				//Array.prototype.push.apply(imgArr,$(".lazyLoad"));
				//AGG.optimize.lazyLoadSelf(imgArr);
				echo.init({
					offset: 10,
					throttle: 100,
					unload: false,
					callback: function (element, op) {
						//console.log(element, 'has been', op + 'ed')
					}
				})

			}else if(data.code==80001){
                window.location.href = WapSiteUrl + "/tmpl/member/login.html";
            }
		}
	});
	//收藏商品
	$.ajax({
		url: ApiUrl + "/index.php?act=user_action&op=is_favorites&key=" + key + "&client_type=wap&good_id=" + gc_id,
		type: "get",
		dataType: "jsonp",
		jsonp: "callback",
		success: function (data) {
			if (data.data == "yes") {
				$('.collect').addClass("on");
			} else {
				$('.collect').removeClass("on");
			}
		}
	});
	$(".collect").click(function () {
		var this_collect = $(this);
		if (key == '') {
			alert('请先登录！');
			window.location.href = WapSiteUrl + "/tmpl/member/login.html";
		} else {
			$.ajax({
				url: ApiUrl + "/index.php?act=user_action&op=is_favorites&key=" + key + "&client_type=wap&good_id=" + gc_id,
				type: "get",
				dataType: "jsonp",
				jsonp: "callback",
				success: function (data) {
					console.log(data);
					if (data.data == "yes") {
						$('.collect').addClass("on");
						$.ajax({
							url: ApiUrl + "/index.php?act=member_favorites&op=favorites_del&fav_id=" + gc_id + "&key=" + key + "&client_type=wap&type=goods",
							type: "get",
							dataType: "jsonp",
							jsonp: "callback",
							success: function (data) {
								if (data.code == 200) {
									this_collect.removeClass("on");

									alert("成功取消收藏！");
									return;
								}
							}
						});
					} else {
						$('.collect').removeClass("on");
						$.ajax({
							url: ApiUrl + "/index.php?act=member_favorites&op=favorites_add&goods_id=" + gc_id + "&key=" + key + "&client_type=wap&is_online=2",
							type: "get",
							dataType: "jsonp",
							jsonp: "callback",
							success: function (data) {
								if (data.code == 200) {
									this_collect.addClass("on");

									alert("收藏成功！");
									return;
								}
							}
						});
					}
				}
			});
		}
	})



	//请求热门商品推荐
	function get_hot(curpage) {
		$.ajax({
			url: ApiUrl + "/index.php?act=unlimited_invitation&op=get_recommend_goods&curpage=" + curpage + "&client_type=wap",
			type: "get",
			dataType: "jsonp",
			jsonp: "callback",
			success: function (data) {
				if (data.code == 200) {
					$(data.data).each(function (index, hot) {
						hot_id[index] = hot.id;
						hot_goods_name[index] = hot.goods_name;
						hot_goods_image[index] = hot.goods_image;
						hot_productPrice[index] = hot.goods_price;
						hot_store_id[index] = hot.store_id;
						var subdiv = "<div class='goods-item box-shw b-radius'><a><div class='goods-item-pic'><img src='" + hot_goods_image[index] + "'></div><div class='goods-item-name'>" + hot_goods_name[index] + "</div><div class='goods-item-price'>￥" + hot_productPrice[index] + "</div></a><span style='display:none;' class='kk_id'>" + hot_id[index] + "</span><span style='display:none;'>" + hot_store_id[index] + "</span></div>";
						$(".content").append(subdiv);
					});

					//点击推荐商品显示商品详情
					$(".goods-item").click(function () {
						var kk_goods_id = $(this).find(".kk_id").text();//获取到了商品的id
						//alert(kk_goods_id);
						window.location.href = "productdetail.html?goods_id=" + kk_goods_id;

					});
				}
			}
		});
	}

	get_hot(page);
	$(window).scroll(function () {
		var doc_w = $(document).width();
		var doc_h = $(document).height();
		var height = $(window).height();
		var scroll_top = $(window).scrollTop();
		if (scroll_top >= doc_h - height) {
			page++;
			get_hot(page);
		}
	});
	function share_wx(){
		var timestamp=new Date().getTime()+"";
		timestamp=timestamp.substring(0,10);
		var ranStr=randomString();
		var nurl=document.URL;
		//var nurl=window.location.href;

		function randomString(len) {
			len = len || 20;
			var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';    /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
			var maxPos = $chars.length;
			var pwd = '';
			for (i = 0; i < len; i++) {
				pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
			}
			return pwd;
		}

		$.ajax({
			url:"http://www.51aigegou.cn/aigegou/ws/webGetTicketSignFx2?timestamp="+timestamp+"&url="+nurl+"&nonceStr="+ranStr,
			type:'get',
			dataType:'jsonp',
			cache : false,
			jsonp:"jsonpcallback",
			success:function(data){
				wx.config({
					debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
					appId: 'wxa0641282049ed265', // 必填，公众号的唯一标识
					timestamp:timestamp, // 必填，生成签名的时间戳
					nonceStr: ranStr, // 必填，生成签名的随机串
					signature: data.sign.toLowerCase(),// 必填，签名，见附录1
					jsApiList: [    'checkJsApi',
						'onMenuShareTimeline',
						'onMenuShareAppMessage',
						'onMenuShareQQ',
						'onMenuShareQZone'
					] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
				});
			},
			error:function(){
				console.log("错了");
			}
		});
		wx.ready(function(){

			//分享给朋友
			wx.onMenuShareAppMessage({
				title: '分销',
				desc: goods_name,
				link: shareUrl,
				imgUrl: goodsImageUrl,
				trigger: function (res) {
				},
				success: function (res) {
				},
				cancel: function (res) {
				},
				fail: function (res) {
					alert(JSON.stringify(res));
				}
			});

			//分享到朋友圈
			wx.onMenuShareTimeline({
				title: goods_name,
				desc: goods_name,
				link: shareUrl,
				imgUrl: goodsImageUrl,
				trigger: function (res) {
				},
				success: function (res) {
				},
				cancel: function (res) {
				},
				fail: function (res) {
					alert(JSON.stringify(res));
				}
			});

			//分享到qq
			wx.onMenuShareQQ({
				title: '分销',
				desc: goods_name,
				link: shareUrl,
				imgUrl: goodsImageUrl,
				success: function () {

				},
				cancel: function () {

				}
			});

			//分享到QQ空间
			wx.onMenuShareQZone({
				title: '分销',
				desc: goods_name,
				link: shareUrl,
				imgUrl: goodsImageUrl,
				success: function () {

				},
				cancel: function () {

				}
			});


		});
		wx.error(function(res){
			alert("error");
		});



	}

});
