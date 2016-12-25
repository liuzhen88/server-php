var order_id=request("order_id");
	var order_sn=request("order_sn");
	var key=request("key");
	var type=request("client_type");
	if(type=='android'){
		addcookie("type",type);	
	}
	if(key==''){
		key=getcookie("key");	
	}else{
		addcookie("key",key);
		key=getcookie("key");
	}
$(document).ready(function(){
	if($("#header").css("display")=="none"){
		$(".order-sn-box").css("margin-top","10px");
	}else{
		$(".order-sn-box").css("margin-top","60px");
	}

	
	
	
	if(key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
	$.ajax({
		url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=order_detail&key="+key+"&order_id="+order_id, 
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				var total_num=0;
				var order_sn=data.data.order_sn;
				var add_time=data.data.add_time;
				var store_name=data.data.store_name;
				var state_desc=data.data.state_desc;
				var order_time=get_time(add_time);
				var shipping_fee=data.data.shipping_fee;
				var order_amount=data.data.order_amount;
				var lock_state=data.data.lock_state;
				var mob_phone=data.data.extend_order_common.reciver_info.mob_phone;
			var reciver_name=data.data.extend_order_common.reciver_name;
			var address=data.data.extend_order_common.reciver_info.address;
				$("#order-sn").html(order_sn);
				$("#order-time").html(order_time);
				$("#receive-tel").html(mob_phone);
				$("#receive-people").html(reciver_name);
				$("#receive-address").html(address);
				/*根据不同order_state显示不同订单操作*/
				var order_state=data.data.order_state;
				if(order_state==10){
					var bottom="<section class='float-right common' onclick='cancle_order(this)'>取消订单<span class='cancel' style='display:none'>"+data.data.order_id+"</span></section>"
+	"<section class='float-right common' onclick='pay(this)'>付款<span class='pay' style='display:none;'>"+data.data.pay_sn+"</span></section>";
					$("#state").append(bottom);
					state_desc="待付款";
				}
				if(order_state==20){
					var bottom="<section class='float-right common' onclick='refundGoods(this)'>申请退款<span style='display:none;'>"+data.data.order_id+"</span></section>";
					$("#state").append(bottom);
					state_desc="待发货";
				}
				if(order_state==30){
					var bottom="<section class='float-right common' onclick='refundGoods(this)'>退货退款<span style='display:none;'>"+data.data.order_id+"</span></section><section class='float-right common' id='lock' onclick='confirm_order(this)'>确认收货<span style='display:none;' class='orderId'>"+data.data.order_id+"</span><span style='display:none;' class='lockState'>"+lock_state+"</span></section>";
					$("#state").append(bottom);
					state_desc="待收货";
					if(lock_state>0){
						$("#lock").css("background","#e5e5e5");
						$("#lock").css("color","#999999");
					}
				}
				if(order_state==41){
					var bottom="<section class='float-right common' onclick='del_order(this)'>删除订单<span style='display:none;'>"+data.data.order_id+"</span></section>"
+	"<section class='float-right common' onclick='online_evaluate(this)'>评价/晒单<span style='display:none;'>"+data.data.order_id+"</span></section>";
					$("#state").append(bottom);
					state_desc="待评价";
				}
				if(order_state==42){
					var bottom="<section class='float-right common' onclick='del_order(this)'>删除订单<span style='display:none;'>"+data.data.order_id+"</span></section>";
					$("#state").append(bottom);
					state_desc="完成";
				}
var header="<section class=' border-top border-bottom store-box'>"	
				+ "<section class='font-color-333 float-left' id='store-name'>"+store_name+"</section>"
				+ "<section class='font-color-ff4846 float-right' id='store-status'>"+state_desc+"</section>"
				+ "<section class='clear-float'></section>"
		+"</section>";
				$("#head").append(header);
		var img_src=new Array(),goods_name=new Array(),goods_price=new Array(),goods_num=new Array(),goods_id=new Array(),refund_state_tip=new Array(),refund=new Array();
		$(data.data.extend_order_goods).each(function(index,value){
				img_src[index]=value.goods_image;												  				goods_name[index]=value.goods_name;
				goods_price[index]=value.goods_price;
				goods_num[index]=value.goods_num;
				goods_id[index]=value.goods_id;
				refund[index]=value.refund;
				if(order_state==20||order_state==30){
					
					if(value.extend_refund){
					 
						refund_state_tip[index]=value.extend_refund.refund_state_tip;
					
					}else{
						refund_state_tip[index]='';
					}
				}
				
var content="<li class='border-bottom'>"
			+	"<section class='sec' onclick='goodsDetails(this)'>"
			+		"<section class='float-left goods-img-box'><img class='goods-img' src='"+img_src[index]+"' /></section>"
			
			+	"<section class='float-right font-color-333 text-box' style='position:relative;'>"
			
			+		"<section class='text-box-title'>"+goods_name[index]+"</section>"
			+		"<section class='text-box-price'><span class='text-price'>¥"+goods_price[index]+"</span><span class='text-num'>×"+goods_num[index]+"</span></section>"
			+	"</section>"
			+	"<section class='clear-float'></section>"
			+	"<span style='display:none;' class='good'>"+goods_id[index]+"</span>"
			+"</section>"
		+"</li>";
		$("#content_ul").append(content);
		$(".text-box").width($(window).width()-100);
		
		total_num+=Number(goods_num[index]);
		});
		
		var footer="<span class='pick-num'>共"+total_num+"件商品</span>"
	+"<span class='pick-price'>合计：¥"+order_amount+"（含运费¥"+shipping_fee+"）</span>";
	$("#foot").append(footer);
			}
		}
	});
	/*获取物流*/
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=search_deliver&key="+key+"&order_id="+order_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				var time=new Array(),info=new Array();
				var express_name=data.data.express_name;
				var shipping_code=data.data.shipping_code;
				$(".express_name").html(express_name);
				$(".shipping_code").html(shipping_code);
				/*append物流*/
				/*$(data.data.deliver_info).each(function(k,v){
														
				});*/
				if(data.data.deliver_info==null||data.data.deliver_info=='undefined'){
					$("#expressInfo").hide();
					$("#expressList").hide();
				}else{
				for(var i=data.data.deliver_info.length-1;i>=0;i--){
					
					time[i]=data.data.deliver_info[i].substr(0,19);
					info[i]=data.data.deliver_info[i].substr(25,data.data.deliver_info[i].length-1);
var list="<li>"
			+"<section>"
			+	"<section class='float-left'>"
			+		"<img src='../images/logist_2.jpg'/>"
			+	"</section>"
			+	"<section class='float-left border-bottom lo-per-text'>"
			+		"<section class='margin-top-7 lo-per-t'>"+info[i]+"</section>"
			+		"<section>"+time[i]+"</section>"
			+	"</section>"
			+	"<section class='clear-float'></section>"
			+"</section>"
		+"</li>";
		$("#list").append(list);
		$("#list li").eq(0).addClass("font-color-fd8900");
		$("#list li").eq(0).find("img").attr("src","../images/logist_1.jpg");
		$(".lo-per-text").css("width",$(window).width()-55);
		$(".lo-per-t").css("width",$(window).width()-55);
				}
				
				}
				/*退换货*/
				/*$("#confirm-return").click(function(){
					window.location.href=WapSiteUrl+"/tmpl/member/refundDetails.html?order_id="+order_id;							
				});*/
				/*确认收货*/
				/*$("#confirm-receive").click(function(){
					$.ajax({
						url:ApiUrl+"/index.php?act=member_order&op=order_receive&key="+key+"&order_id="+order_id,
						type:"get",
						dataType:"jsonp",
						jsonp:"callback",
						success:function(data){
							if(data.code==200){
								alert("确认成功");	
							}	
						}
					});
										 
				});*/
			}
		}
	});
	}
	var username=getcookie("username");
	var password=getcookie("password");
    $(function(){
        $(".header-back").on("click", function () {
            if (type == "iOS"||type == "ios") {
                pop();
            } else if (type == "android") {
                app.pop();
            } else {
                history.back();
            }
        });
    });
});

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

function get_time(obj){
	Date.prototype.format = function(format) {
		var date = {
			   "M+": this.getMonth() + 1,
			   "d+": this.getDate(),
			   "h+": this.getHours(),
			   "m+": this.getMinutes(),
			   "s+": this.getSeconds(),
			   "q+": Math.floor((this.getMonth() + 3) / 3),
			   "S+": this.getMilliseconds()
		};
		if (/(y+)/i.test(format)) {
			   format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
		}
		for (var k in date) {
			   if (new RegExp("(" + k + ")").test(format)) {
					  format = format.replace(RegExp.$1, RegExp.$1.length == 1
							 ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
			   }
		}
		return format;
	}
	var cc=new Date(parseInt(obj)*1000);
	var aa=cc.format('yyyy-MM-dd h:m:s');
	return aa;
}
function goodsDetails(obj){
	var goods_id=$(obj).find(".good").html();
	window.location.href=WapSiteUrl+"/tmpl/productdetail.html?goods_id="+goods_id;
}

//取消订单
function cancle_order(obj){
	var orderId=$(obj).find(".cancel").html(); 
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&op=order_cancel&key="+key+"&order_id="+orderId,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				alert("取消成功");
				/*$(obj).parents(".lz_list").fadeOut(1000);
				$(obj).parents(".lz_list").prev("#unreceived").fadeOut(1000);*/
			}
		}
		
	});
}
/*待付款的支付*/
function pay(obj){
	var type=request('client_type');
	var pay_sn=$(obj).find(".pay").html();
	if(type=='android'||type=='ios'||type=='iOS'){
		window.location.href = WapSiteUrl + "/tmpl/pay.html?key=" +key+ "&pay_sn=" + pay_sn + "&payment_code=alipay";
	}else{
		window.location.href = WapSiteUrl + "/tmpl/pay.html?key=" + key + "&pay_sn=" + pay_sn + "&payment_code=wxpay";
	}
}
//退换货点击跳转当前订单的退换信息
function refundGoods(obj){
	var orderId=$(obj).find("span").html();
	window.location.href=WapSiteUrl+"/tmpl/member/refundDetails.html?order_id="+orderId;
}

//删除商品订单
function del_order(obj){
	var order_id=$(obj).find("span").html();
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&op=order_delete&key="+key+"&order_id="+order_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				alert("删除成功");	
			}
		}
	});
}
//确认订单
function confirm_order(obj){
	var order_id=$(obj).find(".orderId").html();
	var lock_state=$(obj).find(".lockState").html();
	if(lock_state==0){
		var r=confirm("确定要确认订单吗?");
		if(r==true){
			$.ajax({
				url:ApiUrl+"/index.php?act=member_order&op=order_receive&key="+key+"&order_id="+order_id,
				type:"get",
				dataType:"jsonp",
				jsonp:"callback",
				success:function(data){ 
					if(data.code==200){
						alert("确认成功");
					}	
				}
			});
		}else{
			//取消	
		}
	}else{
		//订单处于锁定状态	
	}
}

//商城订单评价
function online_evaluate(obj){
	var order_id=$(obj).find("span").html();
	window.location.href=WapSiteUrl+"/tmpl/evaluate/evaluate_store.html?order_id="+order_id;
}