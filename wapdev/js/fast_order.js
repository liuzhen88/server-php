//获取url参数
function request(paras)
	    { 
	        var url = location.href; 
	        url=decodeURI(url);
	        var paraString = url.substring(url.indexOf("?")+1,url.length).split("&"); 
	        var paraObj = {}; 
	        for (var i=0; j=paraString[i]; i++){ 
	        paraObj[j.substring(0,j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=")+1,j.length); 
	        } 
	        var returnValue = paraObj[paras.toLowerCase()]; 
	        if(typeof(returnValue)=="undefined"){ 
	        return ""; 
	        }else{ 
	        return returnValue; 
	        } 
	    }

$(document).ready(function(){
	var key=getcookie("key");
	var shop_invitation=request("invitation");
	var flag=request("flag");
	var invitation=request("invitation");
	$("#shop_invitation").html(shop_invitation+"-");
	//var key=request("key");
	if(key==''){
		window.location.href=WapSiteUrl+"/tmpl/member/login.html";
	}else{
		var w=$(window).width();
		var goods_id=request("goods_id");
		var store_id=request("store_id");
		
		if(goods_id!=null && goods_id!=""){
			goods_id="&goods_id="+goods_id;
		}else{
			goods_id="";
		}
		
		if(store_id!=null && store_id!=""){
			store_id="&store_id="+store_id;
		}else{
			store_id="";
		}
		
		
		var price=request("price");
		$("#get_money").val(price);
		$("#weixin").css("left",(w-180)/2);
		var money=$("#get_money").val();
		//获取系统时间
		var time=new Date();
		var year=time.getFullYear();
		var month=time.getMonth()+1;
		var day=time.getDate();


		if(flag==1){
			var order_sn=request("order_sn");
			$("#order_num").html(order_sn);
			var img_src = "https://sp0.baidu.com/5aU_bSa9KgQFm2e88IuM_a/micxp1.duapp.com/qr.php?value="+order_sn;
			$("#weixin").attr("src", img_src);
			$("#time").html(year + "/" + month + "/" + day);
			//店员确认订单
			$("#confirm").click(function () {
				var code = $("#get_invitation").val();
				$.ajax({
					url: ApiUrl + "/index.php?act=member_order&op=local_order_sure&key=" + key + "&client_type=wap&code=" + code + "&invitation=" + invitation + "&order_sn=" + order_sn,
					type: "get",
					dataType: "jsonp",
					jsonp: "callback",
					success: function (data) {
						if (data.code == 200) {
							alert(data.message);
							//history.go(-2);
							window.location.href=WapSiteUrl+"/preSale/local_buy_success.html?order_sn="+order_sn+"&flag=faceToFace";
						} else {
							alert(data.message);
						}
					}
				});
			});
		}else {
			//初始化生成订单和二维码
			$.ajax({
				url: ApiUrl + "/index.php?act=member_buy&op=local_buy&money=" + money + "&key=" + key + "&client_type=wap" + store_id + goods_id,
				type: "get",
				dataType: "jsonp",
				jsonp: "callback",
				success: function (data) {
					if (data.code == 200) {
						$("#order_num").html(data.data.order_sn);
						var order_sn = data.data.order_sn;

						var invitation = data.data.invitation;
						var img_src = "https://sp0.baidu.com/5aU_bSa9KgQFm2e88IuM_a/micxp1.duapp.com/qr.php?value=" + data.data.order_sn;
						$("#weixin").attr("src", img_src);
						$("#time").html(year + "/" + month + "/" + day);
						//店员确认订单
						$("#confirm").click(function () {
							var code = $("#get_invitation").val();
							$.ajax({
								url: ApiUrl + "/index.php?act=member_order&op=local_order_sure&key=" + key + "&client_type=wap&code=" + code + "&invitation=" + invitation + "&order_sn=" + order_sn,
								type: "get",
								dataType: "jsonp",
								jsonp: "callback",
								success: function (data) {
									if (data.code == 200) {
										alert(data.message);
										//history.go(-2);
										window.location.href=WapSiteUrl+"/preSale/local_buy_success.html?order_sn="+order_sn+"&flag=faceToFace";
									} else {
										alert(data.message);
									}
								}
							});
						});
					}
				}
			});
		}
		//如果用户手动点击生成订单
		$("#get_order").click(function(){
			money=$("#get_money").val();
			$.ajax({
				url:ApiUrl+"/index.php?act=member_buy&op=local_buy&money="+money+"&key="+key+"&client_type=wap"+store_id+goods_id,
				type:"get",
				dataType:"jsonp",
				jsonp:"callback",
				success: function(data){
					if(data.code==200){
						$("#order_num").html(data.data.order_sn);
						var order_sn=data.data.order_sn;
						if(flag==1){
							order_sn=request("order_sn");
						}
						var invitation=data.data.invitation;
						var img_src="https://sp0.baidu.com/5aU_bSa9KgQFm2e88IuM_a/micxp1.duapp.com/qr.php?value="+data.data.order_sn;
						$("#weixin").attr("src",img_src);
						$("#time").html(year+"/"+month+"/"+day);
						//店员确认订单
					$("#confirm").click(function(){
						var code=$("#get_invitation").val();
						$.ajax({
							url:ApiUrl+"/index.php?act=member_order&op=local_order_sure&key="+key+"&client_type=wap&code="+code+"&invitation="+invitation+"&order_sn="+order_sn,
							type:"get",
							dataType:"jsonp",
							jsonp:"callback",
							success: function(data){
								if(data.code==200){
									alert(data.message);
									//history.go(-2);
									window.location.href=WapSiteUrl+"/preSale/local_buy_success.html?order_sn="+order_sn+"&flag=faceToFace";
								}else{
									alert(data.message);
								}
							}
						});
					});
					}	
				}	
			});	
		});
	}
});
