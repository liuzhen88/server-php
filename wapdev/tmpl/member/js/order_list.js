var beginX,lastX,beginY,lastY;

var this_key;
//var this_key="f8c773e0d34bb1ed322df22112e28d83";

$(document).ready(function() {
	key = request('key');
        var type = request('client_type');
        addcookie('type', type);
        type = getcookie('type');
        if (key == '') {
            if (request('client_type') == 'ios' || request('client_type') == 'android' || request('client_type') == 'iOS') {
                if (getcookie("key") != '') {
                    key = getcookie('key');
                } else {
                    addcookie("key", "");
                }
            } else {
                key = getcookie('key');
            }
        } else {
            addcookie('key', key);
            key = getcookie('key');
        }
		this_key=key;
		if(type=='android'){
			$("#list_left").hide();
			$("#list_right").css("border-bottom","none");
			$("#list_right").css("width","100%");
			$("#list_right").css("color","#999999");
		}else if(type=='ios'||type=='iOS'){
			$("#list_left").hide();
			$("#list_right").css("border-bottom","none");
			$("#list_right").css("width","100%");
			$("#list_right").css("color","#999999");
		}
	if(this_key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
		$("#list_right").click(function(){
			$("#online_list").show();
			$("#local_list").hide();
			$("#list_right").css("color","#EE534F");
			$("#list_right").css("border-bottom","#EE534F solid 2px");
			$("#list_left").css("color","#999999");
			$("#list_left").css("border","none");
			$("#select").show();
			$("#local_select").hide();
		});
		$("#list_left").click(function(){
			$("#local_list").show();
			$("#online_list").hide();
			$("#list_left").css("color","#EE534F");
			$("#list_left").css("border-bottom","#EE534F solid 2px");
			$("#list_right").css("color","#999999");
			$("#list_right").css("border","none");
			$("#select").hide();
			$("#local_select").show();
		});
		var flag=request("flag");
		if(flag==''){

		}else{
			$("#list_left").click();
		}
		unPaid();//待付款
		has_pay();//已付款，待发货
		unreceived();//已发货,待收货
		unevaluated();/*已收货，待评价*/
		hasEvaluated();//已经评价
		get_refundList();//退货记录
		
		//本土的未付款订单
		localUnPay();//未付款
		unConsumption();//本土预售付款但是未消费的
		local_unevaluated();//本土的已经付款但是未评价的
		local_refund();//本土的退款订单

		local_evaluated();//本土的已经付款已经评价订单
		
		
		
		$("#select").click(function(){
			$("#listUl").toggle();
			if(type=='ios'||type=='iOS'){
				$("#list_right").css("border-bottom","none");
				$("#list_right").css("color","#999999");
			}else if(type=='android'){
				$("#list_right").css("border-bottom","none");
				$("#list_right").css("color","#999999");
			}
		});
		$("#local_select").click(function(){
			$("#local_listUl").toggle();
			if(type=='ios'||type=='iOS'){
				$("#list_right").css("border-bottom","none");
				$("#list_right").css("color","#999999");
			}else if(type=='android'){
				$("#list_right").css("border-bottom","none");
				$("#list_right").css("color","#999999");
			}
		});
		$(".refund-list").click(function(){
			$("#listUl").toggle();
			var index=$(".refund-list").index(this);//获得下标值
			if(index==0){$(".unreceived").remove();$(".lz_list").remove();$(".refundList").remove();unPaid();has_pay();unreceived();unevaluated();hasEvaluated();get_refundList();}//全部
			if(index==1){$(".unreceived").remove();$(".lz_list").remove();$(".refundList").remove();
			$(".refundList").remove();unPaid();}//待付款
			if(index==2){$(".unreceived").remove();$(".lz_list").remove();$(".refundList").remove();
			$(".refundList").remove();has_pay();}	//已付款，待发货
			if(index==3){$(".unreceived").remove();$(".lz_list").remove();$(".refundList").remove();
			$(".refundList").remove();unreceived();}//已发货,待收货
			if(index==4){$(".unreceived").remove();$(".lz_list").remove();$(".refundList").remove();
			$(".refundList").remove();unevaluated();}/*已收货，待评价*/
			if(index==5){$(".unreceived").remove();$(".lz_list").remove();$(".refundList").remove();
			$(".refundList").remove();hasEvaluated();}//已经评价
			if(index==6){$(".unreceived").remove();$(".lz_list").remove();get_refundList();}//退款记录
		});
		$(".local_refund-list").click(function(){
			var index=$(".local_refund-list").index(this);//获得下标值
			if(index==0){$(".local_box").remove();$(".jianxi").remove();localUnPay();unConsumption();local_unevaluated();local_refund();local_evaluated();}
			if(index==1){$(".local_box").remove();$(".jianxi").remove();localUnPay();}
			if(index==2){$(".local_box").remove();$(".jianxi").remove();unConsumption();}
			if(index==3){$(".local_box").remove();$(".jianxi").remove();local_unevaluated();}
			if(index==4){$(".local_box").remove();$(".jianxi").remove();local_refund();}
		});
	}
});
	//封装请求商城订单
	/*待付款*/
	function unPaid(){
		var order_id=new Array(),shipping_fee=new Array(),store_name=new Array(),state_desc=new Array(),pay_amount=new Array(),goods_image_url=new Array(),goods_name=new Array(),rec_id=new Array(),goods_num=new Array(),goods_price=new Array(),pay_sn=new Array(),order_id=new Array(),goods_id=new Array();
		var total_num=0;
		$.ajax({
			url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=10&order_type=2",
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					
					$(data.data.datas.order_group_list).each(function(k,v){
						 
						pay_amount[k]=v.pay_amount;
						pay_sn[k]=v.pay_sn;
						$(v.order_list).each(function(kk,vv){
							order_id[kk]=vv.order_id; 
							store_name[kk]=vv.store_name;
							state_desc[kk]=vv.state_desc;
							shipping_fee[kk]=vv.shipping_fee;
				var listDiv="<section class='unreceived'>"
						       +"<div id='unreceived_header'>"
								    +"<div id='store_name'>"+store_name[kk]+"</div>"
									+"<div id='store_state'>"+state_desc[kk]+"</div>"
								+"</div>"
							+"</section>"
							+"<div id='lz_list1"+k+"' class='lz_list'></div>";
							
							
				
				$("#online_list").append(listDiv);
							 $(vv.extend_order_goods).each(function(index,value){
								  
								 goods_image_url[index]=value.goods_image_url;
								 goods_name[index]=value.goods_name;
								 rec_id[index]=value.rec_id;
								 goods_id[index]=value.goods_id;
								 goods_num[index]=value.goods_num;
								 goods_price[index]=value.goods_price;
								 total_num+=parseInt(goods_num[index]);
				var div="<div class='unreceived_list'>"
						+		"<div class='goods_list'>"
						+			"<div class='unreceived_list_pic' onclick='goods_details(this)'><img src='"+goods_image_url[index]+"' width='65px' height='65px'/><span style='display:none'>"+order_id[kk]+"</span></div>"
						+			"<div class='unreceived_list_info' onclick='goods_details(this)'>"
						+				"<div class='good_info'>"+goods_name[index]+"</div>"
						+				"<div class='goods'></div>"
						+				"<span style='display:none'>"+order_id[kk]+"</span>"				
						+			"</div>"
						+			"<div class='price'>￥"+goods_price[index]+"</div>"
						+			"<div class='num'>X"+goods_num[index]+"</div>"
						+			"<div class='state' style='color:#F29820'></div>"
						+		"</div>"
								
						+"</div>";
						$("#lz_list1"+k).append(div);
						
						
				
				
							 });
							 var footer="<div class='box'>"
								+"<div class='total'>共<span class='total_num'>"+total_num+"</span>件商品 合计：￥<span class='total_money'>"+pay_amount[k]+"</span>(含运费￥<span class='yunfei'>"+shipping_fee[kk]+"</span>)</div>"
							+"</div>"
							+"<div class='select'>"
							+	"<ul>"
							+		"<li></li>"
							+		"<li></li>"
							+		"<li style='background:#F29820; color:#FFF;' onclick='cancle_order(this)'>取消订单"
							+"<span class='cancel' style='display:none'>"+order_id[kk]+"</span>"
							+"</li>"
							+		"<li style='background:#F29820; color:#FFF;' onclick='pay(this)'>付款"
							+ "<span class='pay' style='display:none;'>"+pay_sn[k]+"</span>"
							+"</li>"
							+	"</ul>"
							+"</div>";
							$("#lz_list1"+k).append(footer);
							$(".lz_list").css("background","#fff");
				$(".box").css("background","#fff");	
				$(".select").css("background","#fff");
				total_num=0;
						});
					});
				}
			}	
		});
		
	}
	/*已付款，待发货*/
	function has_pay(){
		var order_id=new Array(),shipping_fee=new Array(),store_name=new Array(),state_desc=new Array(),pay_amount=new Array(),goods_image_url=new Array(),goods_name=new Array(),rec_id=new Array(),goods_num=new Array(),goods_price=new Array(),goods_id=new Array(),order_amount=new Array();
		var total_num=0;
		$.ajax({
			url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=20&order_type=2",
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					$(data.data.datas.order_group_list).each(function(k,v){
						 
						pay_amount[k]=v.pay_amount; 
						$(v.order_list).each(function(kk,vv){
							order_amount[kk]=vv.order_amount;
							order_id[kk]=vv.order_id; 
							store_name[kk]=vv.store_name;
							state_desc[kk]=vv.state_desc;
							shipping_fee[kk]=vv.shipping_fee;
				var listDiv="<section class='unreceived'>"
						       +"<div id='unreceived_header'>"
								    +"<div id='store_name'>"+store_name[kk]+"</div>"
									+"<div id='store_state'>"+state_desc[kk]+"</div>"
								+"</div>"
							+"</section>"
							+"<div id='lz_list"+k+"' class='lz_list'></div>";
							
							
				
				$("#online_list").append(listDiv);
							 $(vv.extend_order_goods).each(function(index,value){
								  
								 goods_image_url[index]=value.goods_image_url;
								 goods_name[index]=value.goods_name;
								 rec_id[index]=value.rec_id;
								 goods_id[index]=value.goods_id;
								 goods_num[index]=value.goods_num;
								 goods_price[index]=value.goods_price;
								 total_num+=parseInt(goods_num[index]);
				var div="<div class='unreceived_list'>"
						+		"<div class='goods_list'>"
						+			"<div class='unreceived_list_pic' onclick='goods_details(this)'><img src='"+goods_image_url[index]+"' width='65px' height='65px'/><span style='display:none'>"+order_id[kk]+"</span></div>"
						+			"<div class='unreceived_list_info' onclick='goods_details(this)'>"
						+				"<div class='good_info'>"+goods_name[index]+"</div>"
						+				"<div class='goods'></div>"
						+				"<span style='display:none'>"+order_id[kk]+"</span>"
										
						+			"</div>"
						+			"<div class='price'>￥"+goods_price[index]+"</div>"
						+			"<div class='num'>X"+goods_num[index]+"</div>"
						+			"<div class='state' style='color:#F29820'></div>"
						+		"</div>"
								
						+"</div>"
						$("#lz_list"+k).append(div);
							 });
				var footer="<div class='box'>"
								+"<div class='total'>共<span class='total_num'>"+total_num+"</span>件商品 合计：￥<span class='total_money'>"+order_amount[kk]+"</span>(含运费￥<span class='yunfei'>"+shipping_fee[kk]+"</span>)</div>"
							+"</div>"
							+"<div class='select'>"
							+	"<ul>"
							+		"<li></li>"
							+		"<li></li>"
							+		"<li></li>"
							+		"<li style='background:#F29820; color:#FFF;' onclick='refundGoods(this)'>申请退款<span style='display:none;'>"+order_id[kk]+"</span></li>"
							
							
							+	"</ul>"
							+"</div>";
				$("#lz_list"+k).append(footer);
				$(".lz_list").css("background","#fff");
				$(".box").css("background","#fff");	
				$(".select").css("background","#fff");		
				total_num=0;
				 
						});
					});
				}
			}	
		});	
	}
	/*已发货,待收货*/
	function unreceived(){
		var order_id=new Array(),shipping_fee=new Array(),store_name=new Array(),state_desc=new Array(),pay_amount=new Array(),goods_image_url=new Array(),goods_name=new Array(),rec_id=new Array(),goods_num=new Array(),goods_price=new Array(),goods_id=new Array(),lock_state=new Array(),order_amount=new Array();
		var lockState=new Array();
		var total_num=0;
		$.ajax({
			url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=30&order_type=2",
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					$(data.data.datas.order_group_list).each(function(k,v){
						 
						pay_amount[k]=v.pay_amount; 
						$(v.order_list).each(function(kk,vv){
							order_amount[kk]=vv.order_amount;
							order_id[kk]=vv.order_id;
							lock_state[kk]=vv.lock_state;
							store_name[kk]=vv.store_name;
							state_desc[kk]=vv.state_desc;
							shipping_fee[kk]=vv.shipping_fee;
				var listDiv="<section class='unreceived'>"
						       +"<div id='unreceived_header'>"
								    +"<div id='store_name'>"+store_name[kk]+"</div>"
									+"<div id='store_state'>"+state_desc[kk]+"</div>"
								+"</div>"
							+"</section>"
							+"<div id='lz_list2"+k+"' class='lz_list'></div>";
							
							
				
				$("#online_list").append(listDiv);
							 $(vv.extend_order_goods).each(function(index,value){
								  
								 goods_image_url[index]=value.goods_image_url;
								 goods_name[index]=value.goods_name;
								 rec_id[index]=value.rec_id;
								 goods_num[index]=value.goods_num;
								 goods_id[index]=value.goods_id;
								 goods_price[index]=value.goods_price;
								 total_num+=parseInt(goods_num[index]);
				var div="<div class='unreceived_list'>"
						+		"<div class='goods_list'>"
						+			"<div class='unreceived_list_pic' onclick='goods_details(this)'><img src='"+goods_image_url[index]+"' width='65px' height='65px'/><span style='display:none'>"+order_id[kk]+"</span></div>"
						+			"<div class='unreceived_list_info' onclick='goods_details(this)'>"
						+				"<div class='good_info'>"+goods_name[index]+"</div>"
						+				"<div class='goods'></div>"
						+			"<span style='display:none'>"+order_id[kk]+"</span>"
										
						+			"</div>"
						+			"<div class='price'>￥"+goods_price[index]+"</div>"
						+			"<div class='num'>X"+goods_num[index]+"</div>"
						+			"<div class='state' style='color:#F29820'></div>"
						+		"</div>"
								
						+"</div>";
					$("#lz_list2"+k).append(div);	
							 });
			var footer="<div class='box'>"
								+"<div class='total'>共<span class='total_num'>"+total_num+"</span>件商品 合计：￥<span class='total_money'>"+order_amount[kk]+"</span>(含运费￥<span class='yunfei'>"+shipping_fee[kk]+"</span>)</div>"
							+"</div>"
							+"<div class='select'>"
							+	"<ul>"
							+		"<li></li>"
							+		"<li></li>"
							+		"<li style='background:#F29820; color:#FFF;' onclick='refundGoods(this)'>退货退款<span style='display:none;'>"+order_id[kk]+"</span></li>"
							
							+		"<li class='lock' style='background:#F29820; color:#FFF;' onclick='confirm_order(this)'>确认收货"
							+"<span class='confirm' style='display:none;'>"+order_id[kk]+"</span>"
							+"<span class='lockState' style='display:none;'>"+lock_state[kk]+"</span>"
							+"</li>"
							+	"</ul>"
							+"</div>";
				$("#lz_list2"+k).append(footer);
				$(".lz_list").css("background","#fff");
				$(".box").css("background","#fff");	
				$(".select").css("background","#fff");		
				
				total_num=0;
			
				
				lockState[k]=$(".lockState").eq(k).html();
				if(lockState[k]>0){
					$(".lock").eq(k).css("background","#e5e5e5");
					$(".lock").eq(k).css("color","#999999");
				}
						});
					});
				}
			}	
		});
	}
	/*已收货，待评价*/
	function unevaluated(){
		var order_id=new Array(),shipping_fee=new Array(),store_name=new Array(),state_desc=new Array(),pay_amount=new Array(),goods_image_url=new Array(),goods_name=new Array(),rec_id=new Array(),goods_num=new Array(),goods_price=new Array(),goods_id=new Array(),order_amount=new Array();
		var total_num=0;
		$.ajax({
			url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=41&order_type=2",
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					 $(data.data.datas.order_group_list).each(function(k,v){
						 
						pay_amount[k]=v.pay_amount; 
						$(v.order_list).each(function(kk,vv){
							order_amount[kk]=vv.order_amount;
							order_id[kk]=vv.order_id;
							store_name[kk]=vv.store_name;
							state_desc[kk]=vv.state_desc;
							shipping_fee[kk]=vv.shipping_fee;
				var listDiv="<section class='unreceived'>"
						       +"<div id='unreceived_header'>"
								    +"<div id='store_name'>"+store_name[kk]+"</div>"
									+"<div id='store_state'>待评价</div>"
								+"</div>"
							+"</section>"
							+"<div id='lz_list3"+k+"' class='lz_list'></div>";
							
							
				
				$("#online_list").append(listDiv);
							 $(vv.extend_order_goods).each(function(index,value){
								  
								 goods_image_url[index]=value.goods_image_url;
								 goods_name[index]=value.goods_name;
								 rec_id[index]=value.rec_id;
								 goods_num[index]=value.goods_num;
								 goods_id[index]=value.goods_id;
								 goods_price[index]=value.goods_price;
								 total_num+=parseInt(goods_num[index]);
				var div="<div class='unreceived_list'>"
						+		"<div class='goods_list'>"
						+			"<div class='unreceived_list_pic' onclick='goods_details(this)'><img src='"+goods_image_url[index]+"' width='65px' height='65px'/><span style='display:none'>"+order_id[kk]+"</span></div>"
						+			"<div class='unreceived_list_info' onclick='goods_details(this)'>"
						+				"<div class='good_info'>"+goods_name[index]+"</div>"
						+				"<div class='goods'></div>"
						+			"<span style='display:none'>"+order_id[kk]+"</span>"
										
						+			"</div>"
						+			"<div class='price'>￥"+goods_price[index]+"</div>"
						+			"<div class='num'>X"+goods_num[index]+"</div>"
						+			"<div class='state' style='color:#F29820'></div>"
						+		"</div>"
								
						+"</div>";
						$("#lz_list3"+k).append(div);
							 });
			var footer="<div class='box'>"
								+"<div class='total'>共<span class='total_num'>"+total_num+"</span>件商品 合计：￥<span class='total_money'>"+order_amount[kk]+"</span>(含运费￥<span class='yunfei'>"+shipping_fee[kk]+"</span>)</div>"
							+"</div>"
							+"<div class='select'>"
							+	"<ul>"
							+		"<li></li>"
							+		"<li></li>"
							+		"<li style='background:#F29820; color:#FFF;' onclick='del_order(this)'>删除订单<span style='display:none'>"+order_id[kk]+"</span></li>"
							
							
							+		"<li style='background:#F29820; color:#FFF;' onclick='online_evaluate(this)'>评价/晒单<span style='display:none;'>"+order_id[kk]+"</span></li>"
							+	"</ul>"
							+"</div>";
				$("#lz_list3"+k).append(footer);
				$(".lz_list").css("background","#fff");
				$(".box").css("background","#fff");	
				$(".select").css("background","#fff");		
				
				total_num=0;
						});
					});
				}
			}	
		});	
	}
	
	//已经评价，全部完成
	function hasEvaluated(){
		var order_id=new Array(),shipping_fee=new Array(),store_name=new Array(),state_desc=new Array(),pay_amount=new Array(),goods_image_url=new Array(),goods_name=new Array(),rec_id=new Array(),goods_num=new Array(),goods_price=new Array(),goods_id=new Array(),order_amount=new Array();
		var total_num=0;
		$.ajax({
			url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=42&order_type=2",
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					 $(data.data.datas.order_group_list).each(function(k,v){
						 
						pay_amount[k]=v.pay_amount; 
						$(v.order_list).each(function(kk,vv){
							order_amount[kk]=vv.order_amount;
							order_id[kk]=vv.order_id;
							store_name[kk]=vv.store_name;
							state_desc[kk]=vv.state_desc;
							shipping_fee[kk]=vv.shipping_fee;
				var listDiv="<section class='unreceived'>"
						       +"<div id='unreceived_header'>"
								    +"<div id='store_name'>"+store_name[kk]+"</div>"
									+"<div id='store_state'>"+state_desc[kk]+"</div>"
								+"</div>"
							+"</section>"
							+"<div id='lz_list4"+k+"' class='lz_list'></div>";
							
							
				
				$("#online_list").append(listDiv);
							 $(vv.extend_order_goods).each(function(index,value){
								  
								 goods_image_url[index]=value.goods_image_url;
								 goods_name[index]=value.goods_name;
								 rec_id[index]=value.rec_id;
								 goods_num[index]=value.goods_num;
								 goods_id[index]=value.goods_id;
								 goods_price[index]=value.goods_price;
								 total_num+=parseInt(goods_num[index]);
				var div="<div class='unreceived_list'>"
						+		"<div class='goods_list'>"
						+			"<div class='unreceived_list_pic' onclick='goods_details(this)'><img src='"+goods_image_url[index]+"' width='65px' height='65px'/><span style='display:none'>"+order_id[kk]+"</span></div>"
						+			"<div class='unreceived_list_info' onclick='goods_details(this)'>"
						+				"<div class='good_info'>"+goods_name[index]+"</div>"
						+				"<div class='goods'></div>"
						+			"<span style='display:none'>"+order_id[kk]+"</span>"
										
						+			"</div>"
						+			"<div class='price'>￥"+goods_price[index]+"</div>"
						+			"<div class='num'>X"+goods_num[index]+"</div>"
						+			"<div class='state' style='color:#F29820'></div>"
						+		"</div>"
								
						+"</div>";
						$("#lz_list4"+k).append(div);
							 });
		var footer="<div class='box'>"
								+"<div class='total'>共<span class='total_num'>"+total_num+"</span>件商品 合计：￥<span class='total_money'>"+order_amount[kk]+"</span>(含运费￥<span class='yunfei'>"+shipping_fee[kk]+"</span>)</div>"
							+"</div>"
							+"<div class='select'>"
							+	"<ul>"
							+		"<li></li>"
							+		"<li></li>"
							+		"<li style='background:#F29820; color:#FFF;' onclick='see_evaluate(this)'>查看评价<span style='display:none;'>"+order_id[kk]+"</span></li>"
							+		"<li style='background:#F29820; color:#FFF;' onclick='del_order(this)'>删除订单<span style='display:none;'>"+order_id[kk]+"</span></li>"
							
							+	"</ul>"
							+"</div>";
				$("#lz_list4"+k).append(footer);
				$(".lz_list").css("background","#fff");
				$(".box").css("background","#fff");	
				$(".select").css("background","#fff");		
				
				total_num=0;
						});
					});
				}
			}	
		});	
	}
	//退货记录
function get_refundList(){
		$.ajax({
			url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=index&key="+this_key,
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					var refund_sn=new Array(),goods_image=new Array(),goods_name=new Array(),refund_amount=new Array(),goods_num=new Array(),seller_state=new Array(),refund_state=new Array();
					$(data.data.datas.refund_list).each(function(index,value){
						refund_sn[index]=value.refund_sn;
						goods_image[index]=value.goods_image;
						goods_name[index]=value.goods_name;
						refund_amount[index]=value.refund_amount;
						goods_num[index]=value.goods_num;
						seller_state[index]=value.seller_state;
						refund_state[index]=value.refund_state;
						var state;
						if(seller_state[index]==1&&refund_state[index]==1){
							state="退款中";	
						}
						if(seller_state[index]==2){
							if(refund_state[index]==1||refund_state[index]==2){
								state="退款中";	
							}else if(refund_state[index]==3){
								state="退款成功";
							}	
						}
						if(seller_state[index]==3){
							state="退款失败";
						}
var list="<section class='refundList'>"
			+"<section class='refundList-top'>退款编号:"+refund_sn[index]+"</section>"
    		+"<section class='refundList-content'>"
				+"<section class='refundList-img'><img src='"+goods_image[index]+"' width='65px' height='65px'/></section>"
				+"<section class='refundList-info'>"+goods_name[index]+"</section>"
				+"<div class='refundList-money'>￥"+refund_amount[index]+"</div>"
				+"<div class='refundList-num'>X"+goods_num[index]+"</div>"
				+"<div class='refundList-state'>"+state+"</div>"
    		+"</section>"
    
		+"</section>";
		$("#online_list").append(list);
					});
				}
			}	
		});	
	}	
/*待付款的支付*/
function pay(obj){
	var type=request('client_type');
	var pay_sn=$(obj).find(".pay").html();
	if(type=='android'||type=='ios'||type=='iOS'){
		window.location.href = WapSiteUrl + "/tmpl/pay.html?key=" + this_key + "&pay_sn=" + pay_sn + "&payment_code=alipay";
	}else{
		//window.location.href = WapSiteUrl + "/tmpl/pay.html?key=" + this_key + "&pay_sn=" + pay_sn + "&payment_code=wxpay";
		window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+this_key+"&pay_sn="+pay_sn+"&payment_code=wxpay";
	}
}	

//取消订单
function cancle_order(obj){
	var orderId=$(obj).find(".cancel").html(); 
	var r=confirm("确定要取消吗?");
	if(r==true){
		$.ajax({
			url:ApiUrl+"/index.php?act=member_order&op=order_cancel&key="+this_key+"&order_id="+orderId,
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
	}else{
	
	}
}

//确认订单
function confirm_order(obj){
	var order_id=$(obj).find(".confirm").html();
	var lockState=$(obj).find(".lockState").html();
	if(lockState==0){
	var r=confirm("确定要确认订单吗?");
	
	
		if(r==true){
			$.ajax({
				url:ApiUrl+"/index.php?act=member_order&op=order_receive&key="+this_key+"&order_id="+order_id,
				type:"get",
				dataType:"jsonp",
				jsonp:"callback",
				success:function(data){ 
					if(data.code==200){
						alert("确认成功");
						window.location.reload();
					}	
				}
			});
		}else{
		
		}
	}else{
		//订单处于锁定状态	
	}
}

//请求本土的未付款的订单
function localUnPay(){
	var order_id=new Array(),order_sn=new Array(),add_time=new Array(),state_desc=new Array(),store_avatar=new Array(),store_name=new Array(),thisTime=new Array(),invitation=new Array(),pay_sn=new Array(),goods_type=new Array();
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=10&order_type=1",
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				$(data.data.datas.order_group_list).each(function(k,v){
					$(v.order_list).each(function(kk,vv){
						order_id[kk]=vv.order_id;
						order_sn[kk]=vv.order_sn;
						pay_sn[kk]=vv.pay_sn;
						goods_type[kk]=vv.goods_type;
						add_time[kk]=vv.add_time;
						state_desc[kk]=vv.state_desc;
						store_avatar[kk]=vv.store_info.store_avatar;
						store_name[kk]=vv.store_info.store_name;
						thisTime[kk]=get_time(add_time[kk]);
						invitation[kk]=vv.extend_member.invitation;
				if(vv.extend_order_goods!=null){
						var goods_id=vv.extend_order_goods[0].goods_id;
						var store_id=vv.extend_order_goods[0].store_id;
						var goods_pay_price=vv.extend_order_goods[0].goods_pay_price;
				}
		var localdiv="<section class='local_box' id='box"+k+"'>"
        			+	"<section class='local'>"
          			+  		"<div class='local_pic'><img src='"+store_avatar[kk]+"' width='80px' height='65px'/>"
					+		"</div>"
            		+		"<div class='local_info' onclick='jumpUnPay(this)'>"
					+			"<div class='shop_info'>"+store_name[kk]+"</div>"
					+			"<div class='order_num'>订单号:"+order_sn[kk]+"</div>"
					+			"<div class='order_time'>"+thisTime[kk]+"</div>"
                	+			"<span class='local-order-sn'>"+order_sn[kk]+"</span>"
					+			"<span class='local-pay-sn'>"+pay_sn[kk]+"</span>"
					+			"<span class='goods_type'>"+goods_type[kk]+"</span>"
            		+		"</div>"
            		+		"<div class='local_state' ontouchstart='zhifu(this)'>"+state_desc[kk]+"<span style='display:none;' class='invitation'>"+invitation[kk]+"</span><span class='goodsId' style='display:none;'>"+goods_id+"</span><span class='storeId' style='display:none;'>"+store_id+"</span><span class='goods_pay_price' style='display:none;'>"+goods_pay_price+"</span><span style='display: none' class='paySn'>"+pay_sn[kk]+"</span></div>"
					+		"<div class='local-del' onclick='deleteGoods(this)'><img src='../../images/ic-delete.png'/><span style='display:none;'>"+order_id[kk]+"</span></div>"
        			+	"</section>"
					+	"<section class='del' ontouchstart='deleteGoods(this)'>删除<span style='display:none;'>"+order_id[kk]+"</span></section>"
    				+"</section>"
    				+"<div class='jianxi'></div>";
					$("#local_list").append(localdiv);
					/* var p=document.getElementById("box"+k+"");
					p.addEventListener("touchstart",function(event){
						//event.preventDefault();
						beginX=event.targetTouches[0].screenX;
						beginY=event.targetTouches[0].screenY;
						
					},false);
					p.addEventListener("touchend",function(event){
					//event.preventDefault();
					lastX=event.changedTouches[0].screenX;
					lastY=event.changedTouches[0].screenY;
								
					if(Number(lastX-beginX)<0&&Math.abs(Number(lastY-beginY))<30){
						event.preventDefault();
						$(this).animate({left:"-60px"},200);
						//$(this).animate({overflow:"visible"},200);
						 $(this).find(".del").animate({width:"60px"},200);
						}else if(Number(lastX-beginX)>0&&Math.abs(Number(lastY-beginY))<30){
						event.preventDefault();
							$(this).animate({left:"0px"},200);
							//$(this).animate({overflow:"hidden"},200);
							 $(this).find(".del").animate({width:"0px"},200);
						}
					},false);*/
				});
																							
																							
			});	
 				
			 
			}
		}
	});
}
//请求本土的未消费订单
function unConsumption(){
	var order_id=new Array(),order_sn=new Array(),add_time=new Array(),state_desc=new Array(),store_avatar=new Array(),store_name=new Array(),thisTime=new Array(),invitation=new Array(),pay_sn=new Array(),goods_type=new Array(),consume_code=new Array();
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=20&order_type=1",
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				$(data.data.datas.order_group_list).each(function(k,v){
					$(v.order_list).each(function(kk,vv){
						order_id[kk]=vv.order_id;
						order_sn[kk]=vv.order_sn;
						pay_sn[kk]=vv.pay_sn;
						consume_code[kk]=vv.consume_code;
						goods_type[kk]=vv.goods_type;
						add_time[kk]=vv.add_time;
						state_desc[kk]=vv.state_desc;
						store_avatar[kk]=vv.store_info.store_avatar;
						store_name[kk]=vv.store_info.store_name;
						thisTime[kk]=get_time(add_time[kk]);
						invitation[kk]=vv.extend_member.invitation;
						if(vv.extend_order_goods!=null){
							var goods_id=vv.extend_order_goods[0].goods_id;
							var store_id=vv.extend_order_goods[0].store_id;
							var goods_pay_price=vv.extend_order_goods[0].goods_pay_price;
						}
						var localdiv="<section class='local_box' id='bok"+k+"'>"
							+	"<section class='local'>"
							+  		"<div class='local_pic'><img src='"+store_avatar[kk]+"' width='80px' height='65px'/>"
							+		"</div>"
							+		"<div class='local_info' onclick='jumpUnConsumption(this)'>"
							+			"<div class='shop_info'>"+store_name[kk]+"</div>"
							+			"<div class='order_num'>订单号:"+order_sn[kk]+"</div>"
							+			"<div class='order_time'>"+thisTime[kk]+"</div>"
							+			"<span class='unConsumption-order-sn'>"+order_sn[kk]+"</span>"
							+			"<span class='unConsumption-pay-sn'>"+pay_sn[kk]+"</span>"
							+			"<span class='goods_type'>"+goods_type[kk]+"</span>"
							+			"<span class='consume_code'>"+consume_code[kk]+"</span>"
							+		"</div>"
							+		"<div class='local_state'>未消费</div>"

							+	"</section>"
							+	"<section class='del' ontouchstart='deleteGoods(this)'>删除<span style='display:none;'>"+order_id[kk]+"</span></section>"
							+"</section>"
							+"<div class='jianxi'></div>";
						$("#local_list").append(localdiv);
						/*var p=document.getElementById("bok"+k+"");
						p.addEventListener("touchstart",function(event){
							//event.preventDefault();
							beginX=event.targetTouches[0].screenX;
							beginY=event.targetTouches[0].screenY;

						},false);
						p.addEventListener("touchend",function(event){
							//event.preventDefault();
							lastX=event.changedTouches[0].screenX;
							lastY=event.changedTouches[0].screenY;

							if(Number(lastX-beginX)<0&&Math.abs(Number(lastY-beginY))<30){
								event.preventDefault();
								$(this).animate({left:"-60px"},200);
								//$(this).animate({overflow:"visible"},200);
								$(this).find(".del").animate({width:"60px"},200);
							}else if(Number(lastX-beginX)>0&&Math.abs(Number(lastY-beginY))<30){
								event.preventDefault();
								$(this).animate({left:"0px"},200);
								//$(this).animate({overflow:"hidden"},200);
								$(this).find(".del").animate({width:"0px"},200);
							}
						},false);*/
					});


				});


			}
		}
	});
}

//请求本土的退款订单
function local_refund(){
	var order_id=new Array(),order_sn=new Array(),add_time=new Array(),state_desc=new Array(),store_avatar=new Array(),store_name=new Array(),thisTime=new Array(),invitation=new Array(),pay_sn=new Array(),goods_type=new Array();
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=51&order_type=1",
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				$(data.data.datas.order_group_list).each(function(k,v){
					$(v.order_list).each(function(kk,vv){
						order_id[kk]=vv.order_id;
						order_sn[kk]=vv.order_sn;
						pay_sn[kk]=vv.pay_sn;
						goods_type[kk]=vv.goods_type;
						add_time[kk]=vv.add_time;
						state_desc[kk]=vv.state_desc;
						store_avatar[kk]=vv.store_info.store_avatar;
						store_name[kk]=vv.store_info.store_name;
						thisTime[kk]=get_time(add_time[kk]);
						invitation[kk]=vv.extend_member.invitation;
						if(vv.extend_order_goods!=null){
							var goods_id=vv.extend_order_goods[0].goods_id;
							var store_id=vv.extend_order_goods[0].store_id;
							var goods_pay_price=vv.extend_order_goods[0].goods_pay_price;
						}
						var localdiv="<section class='local_box' id='bot"+k+"'>"
							+	"<section class='local'>"
							+  		"<div class='local_pic'><img src='"+store_avatar[kk]+"' width='80px' height='65px'/>"
							+		"</div>"
							+		"<div class='local_info' onclick='hasRefund(this)'>"
							+			"<div class='shop_info'>"+store_name[kk]+"</div>"
							+			"<div class='order_num'>订单号:"+order_sn[kk]+"</div>"
							+			"<div class='order_time'>"+thisTime[kk]+"</div>"
							+			"<span class='unConsumption-order-sn'>"+order_sn[kk]+"</span>"
							+			"<span class='unConsumption-pay-sn'>"+pay_sn[kk]+"</span>"
							+			"<span class='goods_type'>"+goods_type[kk]+"</span>"
							+		"</div>"
							+		"<div class='local_state'>已退款</div>"
							+		"<div class='local-del' onclick='del_order(this)'><img src='../../images/ic-delete.png'/><span style='display:none;'>"+order_id[kk]+"</span></div>"
							+	"</section>"
							+	"<section class='del' ontouchstart='deleteGoods(this)'>删除<span style='display:none;'>"+order_id[kk]+"</span></section>"
							+"</section>"
							+"<div class='jianxi'></div>";
						$("#local_list").append(localdiv);
						/*var p=document.getElementById("bot"+k+"");
						p.addEventListener("touchstart",function(event){
							//event.preventDefault();
							beginX=event.targetTouches[0].screenX;
							beginY=event.targetTouches[0].screenY;

						},false);
						p.addEventListener("touchend",function(event){
							//event.preventDefault();
							lastX=event.changedTouches[0].screenX;
							lastY=event.changedTouches[0].screenY;

							if(Number(lastX-beginX)<0&&Math.abs(Number(lastY-beginY))<30){
								event.preventDefault();
								$(this).animate({left:"-60px"},200);
								//$(this).animate({overflow:"visible"},200);
								$(this).find(".del").animate({width:"60px"},200);
							}else if(Number(lastX-beginX)>0&&Math.abs(Number(lastY-beginY))<30){
								event.preventDefault();
								$(this).animate({left:"0px"},200);
								//$(this).animate({overflow:"hidden"},200);
								$(this).find(".del").animate({width:"0px"},200);
							}
						},false);*/
					});


				});


			}
		}
	});
}

//请求本土订单已经付款但未评价的
function local_unevaluated(){
	var order_id=new Array(),order_sn=new Array(),add_time=new Array(),state_desc=new Array(),store_avatar=new Array(),store_name=new Array(),thisTime=new Array(),goods_id=new Array(),pay_sn=new Array(),goods_type=new Array();
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=41&order_type=1",
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				$(data.data.datas.order_group_list).each(function(k,v){
					 									  									$(v.order_list).each(function(kk,vv){
						order_id[kk]=vv.order_id;
						order_sn[kk]=vv.order_sn;
						pay_sn[kk]=vv.pay_sn;
						goods_type[kk]=vv.goods_type;
						add_time[kk]=vv.add_time;
						state_desc[kk]=vv.state_desc;
						store_avatar[kk]=vv.store_info.store_avatar;
						store_name[kk]=vv.store_info.store_name;
						goods_id[kk]=vv.extend_order_goods[0].goods_id;
						thisTime[kk]=get_time(add_time[kk]);
		var localdiv="<section class='local_box' id='bok"+k+"'>"
        			+	"<section class='local'>"
          			+  		"<div class='local_pic'><img src='"+store_avatar[kk]+"' width='80px' height='65px'/>"
					+		"</div>"
            		+		"<div class='local_info' onclick='jumpUnEvaluated(this)'>"
					+			"<div class='shop_info'>"+store_name[kk]+"</div>"
					+			"<div class='order_num'>订单号:"+order_sn[kk]+"</div>"
					+			"<div class='order_time'>"+thisTime[kk]+"</div>"
                	+			"<span class='unevaluated-order-sn'>"+order_sn[kk]+"</span>"
					+			"<span class='unevaluated-pay-sn'>"+pay_sn[kk]+"</span>"
					+			"<span class='goods_type'>"+goods_type[kk]+"</span>"
            		+		"</div>"
            		+		"<div class='local_state' ontouchstart='pingjia(this)'>评价<span style='display:none;' class='thisOrderId'>"+order_id[kk]+"</span><span style='display:none;' class='thisGoodsId'>"+goods_id[kk]+"</span></div>"
					+		"<div class='local-del' onclick='del_order(this)'><img src='../../images/ic-delete.png'/><span style='display:none;'>"+order_id[kk]+"</span></div>"
					+	"</section>"
					+	"<section class='del' ontouchstart='deleteGoods(this)'>删除<span style='display:none;'>"+order_id[kk]+"</span></section>"
    				+"</section>"
    				+"<div class='jianxi'></div>";
					$("#local_list").append(localdiv);
					/*var p=document.getElementById("bok"+k+"");
					p.addEventListener("touchstart",function(event){
						//event.preventDefault();
						beginX=event.targetTouches[0].screenX;
						beginY=event.targetTouches[0].screenY;
						
					},false);
					p.addEventListener("touchend",function(event){
					//event.preventDefault();
					lastX=event.changedTouches[0].screenX;
					lastY=event.changedTouches[0].screenY;
								
					if(Number(lastX-beginX)<0&&Math.abs(Number(lastY-beginY))<30){
						event.preventDefault();
						$(this).animate({left:"-60px"},200);
						//$(this).animate({overflow:"visible"},200);
						 $(this).find(".del").animate({width:"60px"},200);
						}else if(Number(lastX-beginX)>0&&Math.abs(Number(lastY-beginY))<30){
						event.preventDefault();
							$(this).animate({left:"0px"},200);
							//$(this).animate({overflow:"hidden"},200);
							 $(this).find(".del").animate({width:"0px"},200);
						}
					},false);*/
					});
				});
				
			}
		}
	});
}

//请求本土订单已经付款也已经评价的
function local_evaluated(){
	var order_id=new Array(),order_sn=new Array(),add_time=new Array(),state_desc=new Array(),store_avatar=new Array(),store_name=new Array(),thisTime=new Array(),goods_type=new Array();
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&client_type=wap&op=order_list&key="+this_key+"&order_status=42&order_type=1",
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				$(data.data.datas.order_group_list).each(function(k,v){
					 									  									$(v.order_list).each(function(kk,vv){
						order_id[kk]=vv.order_id;
						order_sn[kk]=vv.order_sn;
						add_time[kk]=vv.add_time;
						state_desc[kk]=vv.state_desc;
						store_avatar[kk]=vv.store_info.store_avatar;
						store_name[kk]=vv.store_info.store_name;
						thisTime[kk]=get_time(add_time[kk]);
						goods_type[kk]=vv.goods_type;
		var localdiv="<section class='local_box' id='bom"+k+"'>"
        			+	"<section class='local'>"
          			+  		"<div class='local_pic'><img src='"+store_avatar[kk]+"' width='80px' height='65px'/>"
					+		"</div>"
            		+		"<div class='local_info' onclick='over(this)'>"
					+			"<div class='shop_info'>"+store_name[kk]+"</div>"
					+			"<div class='order_num'>订单号:"+order_sn[kk]+"</div>"
					+			"<div class='order_time'>"+thisTime[kk]+"</div>"
					+			"<span class='goods_type'>"+goods_type[kk]+"</span>"
            		+		"</div>"
            		+		"<div class='local_state_over'>完成</div>"
					+		"<div class='local-del' onclick='del_order(this)'><img src='../../images/ic-delete.png'/><span style='display:none;'>"+order_id[kk]+"</span></div>"
        			+	"</section>"
					+	"<section class='del' ontouchstart='deleteGoods(this)'>删除<span style='display:none;'>"+order_id[kk]+"</span></section>"
    				+"</section>"
    				+"<div class='jianxi'></div>";
					$("#local_list").append(localdiv);
					/*var p=document.getElementById("bom"+k+"");
					p.addEventListener("touchstart",function(event){
						//event.preventDefault();
						beginX=event.targetTouches[0].screenX;
						beginY=event.targetTouches[0].screenY;
						
					},false);
					p.addEventListener("touchend",function(event){
					//event.preventDefault();
					lastX=event.changedTouches[0].screenX;
					lastY=event.changedTouches[0].screenY;
								
					if(Number(lastX-beginX)<0&&Math.abs(Number(lastY-beginY))<30){

						$(this).animate({left:"-60px"},200);
						//$(this).animate({overflow:"visible"},200);
						 $(this).find(".del").animate({width:"60px"},200);
						}else if(Number(lastX-beginX)>0&&Math.abs(Number(lastY-beginY))<30){

							$(this).animate({left:"0px"},200);
							//$(this).animate({overflow:"hidden"},200);
							 $(this).find(".del").animate({width:"0px"},200);
						}
					},false);*/
					});
				});
				
			}
		}
	});
}
//取消本土订单的商品
function deleteGoods(obj){
	var order_id=$(obj).find("span").html();
	var r=confirm("确定要取消吗?");
	if(r==true){
		$.ajax({
			url:ApiUrl+"/index.php?act=member_order&op=order_cancel&key="+this_key+"&order_id="+order_id,
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success:function(data){
				if(data.code==200){
					alert("取消成功");
					$(obj).parent().remove();
					$(obj).parent().next().remove();
				}
			}
		});
	}else{
		
	}
	
}

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
//商城订单评价
function online_evaluate(obj){
	var order_id=$(obj).find("span").html();
	window.location.href=WapSiteUrl+"/tmpl/evaluate/evaluate_store.html?order_id="+order_id;
}

//商城商品点击订单跳转订单详情
function goods_details(obj){
	var order_id=$(obj).find("span").html();
	window.location.href=WapSiteUrl+"/tmpl/for_receipt.html?order_id="+order_id;
}

//退换货点击跳转当前订单的退换信息
function refundGoods(obj){
	var orderId=$(obj).find("span").html();
	window.location.href=WapSiteUrl+"/tmpl/member/refundDetails.html?order_id="+orderId;
}

//删除商品订单
function del_order(obj){
	var order_id=$(obj).find("span").html();
	var r=confirm("确定要删除吗?");
	if(r==true){
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&op=order_delete&key="+this_key+"&order_id="+order_id,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				alert("删除成功");
				$(obj).parent().remove();
				$(obj).parent().next().remove();
			}
		}
	});
	}else{
	
	}
}

//本土评价
function pingjia(obj){
	var order_id=$(obj).find(".thisOrderId").html();
	var goods_id=$(obj).find(".thisGoodsId").html();
	console.log(order_id);
	console.log(goods_id);
	window.location.href=WapSiteUrl+"/evaluate.html?order_id="+order_id+"&goods_id="+goods_id;
}
//本土商品支付
function zhifu(obj){
	var invitation=$(obj).find(".invitation").html();	
	var goods_id=$(obj).find(".goodsId").html();
	var store_id=$(obj).find(".storeId").html();
	var goods_pay_price=$(obj).find(".goods_pay_price").html();
	var pay_sn=$(obj).find(".paySn").html();
	window.location.href=WapSiteUrl+"/localPay.html?invitation="+invitation+"&goods_id="+goods_id+"&store_id="+store_id+"&money="+goods_pay_price+"&pay_sn="+pay_sn;
}
//商城订单查看评价
function see_evaluate(obj){
	var order_id=$(obj).find("span").html();
	window.location.href=WapSiteUrl+"/tmpl/evaluate/evaluate_store.html?order_id="+order_id;
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

//控制本土未付款订单跳转
function jumpUnPay(obj){
	var order_sn=$(obj).find(".local-order-sn").html();
	var pay_sn=$(obj).find(".local-pay-sn").html();
	var goods_type=$(obj).find(".goods_type").html();
	if(goods_type==0){
		window.location.href=WapSiteUrl+"/preSale/local_unpaid_store.html?order_sn="+order_sn+"&pay_sn="+pay_sn;
	}
	if(goods_type==1){
		window.location.href=WapSiteUrl+"/preSale/local_unpaid.html?order_sn="+order_sn+"&pay_sn="+pay_sn;
	}

}
//控制本土付款了但是未消费的跳转
function jumpUnConsumption(obj){
	var order_sn=$(obj).find(".unConsumption-order-sn").html();
	var pay_sn=$(obj).find(".unConsumption-pay-sn").html();
	var goods_type=$(obj).find(".goods_type").html();
	var consume_code=$(obj).find(".consume_code").html();
	if(goods_type==0){
		window.location.href=WapSiteUrl+"/preSale/local_store_unconsume.html?order_sn="+order_sn+"&pay_sn="+pay_sn+"&consume_code="+consume_code;
	}
	if(goods_type==1){
		window.location.href=WapSiteUrl+"/preSale/local_goods_unconsume.html?order_sn="+order_sn+"&pay_sn="+pay_sn+"&consume_code="+consume_code;
	}
}
//本土的待评价订单跳转
function jumpUnEvaluated(obj){
	var order_sn=$(obj).find(".unevaluated-order-sn").html();
	var pay_sn=$(obj).find(".unevaluated-pay-sn").html();
	var goods_type=$(obj).find(".goods_type").html();
	if(goods_type==0){
		window.location.href=WapSiteUrl+"/preSale/local_unEvaluated_store.html?order_sn="+order_sn+"&pay_sn="+pay_sn+"&goods_type="+goods_type;
	}
	if(goods_type==1){
		window.location.href=WapSiteUrl+"/preSale/local_unEvaluated.html?order_sn="+order_sn+"&pay_sn="+pay_sn+"&goods_type="+goods_type;
	}

}

//控制本土的已经退款的订单的页面跳转
function hasRefund(obj){
	var order_sn=$(obj).find(".unConsumption-order-sn").html();
	var pay_sn=$(obj).find(".unConsumption-pay-sn").html();
	var goods_type=$(obj).find(".goods_type").html();
	if(goods_type==0){
		window.location.href=WapSiteUrl+"/preSale/local_refund_success_store.html?order_sn="+order_sn+"&pay_sn="+pay_sn+"&goods_type="+goods_type;
	}
	if(goods_type==1){
		window.location.href=WapSiteUrl+"/preSale/local_refund_success.html?order_sn="+order_sn+"&pay_sn="+pay_sn+"&goods_type="+goods_type;
	}
}
//控制本土已完成订单跳转
function over(obj){
	var order_sn=$(obj).find(".order_num").html();
	var goods_type=$(obj).find(".goods_type").html();
	//window.location.href=WapSiteUrl+"/preSale/local_face_to_face.html?order_sn="+order_sn;

}
