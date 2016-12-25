$(document).ready(function(){
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
	
	var order_id=request("order_id");
	if(key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
	$.ajax({
		url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=order_detail&key="+key+"&order_id="+order_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success: function(data){
			if(data.code==200){
				var order_state=data.data.order_state;
				var img_src=new Array(),goods_name=new Array(),goods_price=new Array(),goods_num=new Array(),refund=new Array(),seller_state=new Array(),refund_state=new Array(),refund_state_tip=new Array(),rec_id=new Array(),order_id=new Array(),refund_id=new Array(),isrefund=new Array(),refund_state=new Array(),refund_type=new Array(),goods_state=new Array(),extend_refund=new Array();
				var order_sn=data.data.order_sn;
				var add_time=data.data.add_time;
				var time=get_time(add_time);
				$(".order_num").html(order_sn);
				$(".time").html(time);
				$(data.data.extend_order_goods).each(function(index,value){
					img_src[index]=value.goods_image;
					goods_name[index]=value.goods_name;
					goods_price[index]=value.goods_price;
					goods_num[index]=value.goods_num;
					refund[index]=value.refund;
					rec_id[index]=value.rec_id;
					order_id[index]=value.order_id;
					isrefund[index]=value.isrefund;
					extend_refund[index]=value.extend_refund;
					
					if(refund[index]==0){
						if(value.extend_refund==null){
							refund_state_tip[index]="退货退款";
						}else{
					refund_type[index]=value.extend_refund.refund_type;
					seller_state[index]=value.extend_refund.seller_state;
					refund_state[index]=value.extend_refund.refund_state;
					refund_state_tip[index]=value.extend_refund.refund_state_tip;
					refund_id[index]=value.extend_refund.refund_id;
					
						if(refund_type[index]==2){
							goods_state[index]=value.extend_refund.goods_state;
							if(seller_state[index]==1){
								refund_state_tip[index]="审核中";
							}
							if(seller_state[index]==2){
								if(goods_state[index]==1){
									refund_state_tip[index]="同意退货";
								}
								if(goods_state[index]==2||goods_state[index]==4){
									if(refund_state[index]==3){
										refund_state_tip[index]="退货成功";
									}else{
										refund_state_tip[index]="退货中";
									}
								}
							}
							if(seller_state[index]==3){
								refund_state_tip[index]="申请失败";
							}
							
							 
						}
						}
					}else{
						refund_id[index]=value.refund_id;
						refund_state[index]=value.refund_state;
						if(isrefund[index]==1){
							if(refund_state[index]==1){
								refund_state_tip[index]="退款中";
							}else{
							refund_state_tip[index]="退款失败";
							}
						}else{
							refund_state_tip[index]="退货退款";
						}
					}
if(order_state==20){
	var list="<section class='contain'>"
			+	"<section class='shop_list'>"
			+		"<section class='pic'><img src='"+img_src[index]+"'/></section>"
			+		"<section class='shop_info'>"
			+			"<section class='detail'>"+goods_name[index]+"</section>"
			+			"<section class='det'></section>"
			+		"</section>"
			+		"<div class='money'>￥"+goods_price[index]+"</div>"
			+		"<div class='num'>X"+goods_num[index]+"</div>"
			
			+	"</section>"
			+"</section>";
			$(".sec").append(list);
	
}else{
var list="<section class='contain'>"
		+	"<section class='shop_list'>"
		+		"<section class='pic'><img src='"+img_src[index]+"'/></section>"
		+		"<section class='shop_info'>"
		+			"<section class='detail'>"+goods_name[index]+"</section>"
		+			"<section class='det'></section>"
		+		"</section>"
		+		"<div class='money'>￥"+goods_price[index]+"</div>"
		+		"<div class='num'>X"+goods_num[index]+"</div>"
		+		"<div class='state'>"+refund_state_tip[index]+"<span style='display:none;' class='refund'>"+refund[index]+"</span><span class='extend_refund' style='display:none;'>"+extend_refund[index]+"</span><span style='display:none;' class='seller_state'>"+seller_state[index]+"</span><span style='display:none;' class='refund_state'>"+refund_state[index]+"</span><span style='display:none;' class='this_money'>"+goods_price[index]+"</span><span style='display:none;' class='this_order'>"+order_id[index]+"</span><span style='display:none;' class='this_rec'>"+rec_id[index]+"</span><span style='display:none;' class='this_refund'>"+refund_id[index]+"</span><span style='display:none;' class='next_num'>"+goods_num[index]+"</span><span style='display:none;' class='isrefund'>"+isrefund[index]+"</span><span style='display:none;' class='refundState'>"+refund_state[index]+"</span><span style='display:none;' class='refundType'>"+refund_type[index]+"</span><span style='display:none;' class='goodsState'>"+goods_state[index]+"</span></div>"
		+	"</section>"
		+"</section>";
		$(".sec").append(list);
}
		
		 
				});//each end
if(order_state==20){
	var state_footer="<div class='stateBox'><div class='state' id='state_footer'>"+refund_state_tip[0]+"<span style='display:none;' class='refund'>"+refund[0]+"</span><span style='display:none;' class='seller_state'>"+seller_state[0]+"</span><span style='display:none;' class='refund_state'>"+refund_state[0]+"</span><span style='display:none;' class='this_money'>"+goods_price[0]+"</span><span style='display:none;' class='this_order'>"+order_id[0]+"</span><span style='display:none;' class='this_rec'>"+rec_id[0]+"</span><span style='display:none;' class='this_refund'>"+refund_id[0]+"</span><span style='display:none;' class='next_num'>"+goods_num[0]+"</span><span style='display:none;' class='isrefund'>"+isrefund[0]+"</span><span style='display:none;' class='refundState'>"+refund_state[0]+"</span><span style='display:none;' class='refundType'>"+refund_type[0]+"</span><span style='display:none;' class='goodsState'>"+goods_state[0]+"</span></div></div>";
	$(".sec").append(state_footer);
}
				$(".state").click(function(){
					 
					var this_refund=$(this).find(".refund").html();
					var this_seller_state=$(this).find(".seller_state").html();       
					var this_refund_state=$(this).find(".refund_state").html();
					var this_money=$(this).find(".this_money").html();
					var order_id=$(this).find(".this_order").html();
					var rec_id=$(this).find(".this_rec").html();
					var refund_id=$(this).find(".this_refund").html();
					var num=$(this).find(".next_num").html();
					var isrefund=$(this).find(".isrefund").html();
					var refundState=$(this).find(".refundState").html();
					var refundType=$(this).find(".refundType").html();
					var goodsState=$(this).find(".goodsState").html();
					var extend_refund=$(this).find(".extend_refund").html();
					 
				if(this_refund==1||this_refund=='undefined'){
					 
					if(extend_refund==null){
						 
						window.location.href=WapSiteUrl+"/tmpl/member/tuihuo.html?rec_id="+rec_id+"&order_id="+order_id+"&num="+num+"&money="+this_money;
					}
					if(isrefund==1){
						//拒绝后再次申请
						if(refundState==1){
							window.location.href=WapSiteUrl+"/tmpl/member/applyRefund.html?rec_id="+rec_id+"&num="+num+"&refund_id="+refund_id;
						}else{
						//商家拒绝
						window.location.href=WapSiteUrl+"/tmpl/member/sellersRefuse.html?rec_id="+rec_id+"&order_id="+order_id+"&num="+num+"&money="+this_money+"&refund_id="+refund_id;	
						}
					}else{
						window.location.href=WapSiteUrl+"/tmpl/member/tuihuo.html?rec_id="+rec_id+"&order_id="+order_id+"&num="+num+"&money="+this_money;
					}
				}else{
					 
					if(extend_refund==null||extend_refund=='undefined'||extend_refund=="null"){
						 
						window.location.href=WapSiteUrl+"/tmpl/member/tuihuo.html?rec_id="+rec_id+"&order_id="+order_id+"&num="+num+"&money="+this_money;
					}
					if(this_seller_state==1){
						//卖家处理中
						window.location.href=WapSiteUrl+"/tmpl/member/applyRefund.html?rec_id="+rec_id+"&num="+num+"&refund_id="+refund_id;
					}
					if(this_seller_state==2){
							//如果是卖家同意，但是平台在处理中
						if(this_refund_state==1||this_refund_state==2){
							if(refundType==2){
								
								//这是退货卖家同意填写退货物流
								if(goodsState==1){
								window.location.href=WapSiteUrl+"/tmpl/return_by_voucher.html?refund_id="+refund_id;
								}
								if(goodsState==2){
									//这是商家同意用户退货，用户也填写了申请等待商家收货
									window.location.href=WapSiteUrl+"/tmpl/member/sellerAgree.html?refund_id="+refund_id;	
								}
								if(goodsState==3){
									//用户退货了，但是商家没有收到货
									//后台暂时无此功能	
								}
								
								if(goodsState==4){
									//商家已经收货,待确认
									//这是商家同意了，用户也填写了申请，商家也收货确认了
									if(this_refund_state==2){
										//商家确认收货了，但是平台还没有打款
										window.location.href=WapSiteUrl+"/tmpl/member/waitPay.html";
									}
									if(this_refund_state==3){
										//商家确认收货了，平台也同意了打款了	
										window.location.href=WapSiteUrl+"/tmpl/member/refundSuccess.html?refund_id="+refund_id;
									}
									 	
								}
								
							}else{
								//退款类型
								//这是退款卖家同意的
							//window.location.href=WapSiteUrl+"/tmpl/member/sellerAgree.html?refund_id="+refund_id;	
							window.location.href=WapSiteUrl+"/tmpl/member/waitPay.html";
							}
						}else if(this_refund_state==3){
							//退款成功
							window.location.href=WapSiteUrl+"/tmpl/member/refundSuccess.html?refund_id="+refund_id;
						}
					}
					if(this_seller_state==3){
						//卖家拒绝
						window.location.href=WapSiteUrl+"/tmpl/member/sellersRefuse.html?refund_id="+refund_id;
					}
				}
			});//state click end
				
		}else if(data.code==80001){
			alert("请登录");
			window.location.href = WapSiteUrl + "/tmpl/member/login.html";
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
//解析时间轴
function get_time(this_time){
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
var cc=new Date(parseInt(this_time)*1000);
var aa=cc.format('yyyy-MM-dd h:m:s');
return aa;
}