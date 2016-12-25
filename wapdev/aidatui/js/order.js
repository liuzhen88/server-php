$(function(){
	var more;
	var curpage = 1;
	app.order={
		init:function(){
			FastClick.attach(document.body);
			this.ajaxDate();
		},
		key:getcookie('key'),
		ajaxDate:function(){
			var self = this;
			$.ajax({
				url: ApiUrl + "/index.php?act=member_order&op=adt_order_list&client_type=wap&key="+self.key+"&curpage="+curpage,
				type: "get",
				dataType: "jsonp",
				success: function (data) {
					if(data.code==200){
						var orderListTmpl = doT.template($("#orderListTmpl").html());
						$(".order-list").append(orderListTmpl(data.data.order_group_list));
						var orderId=[];
						$(data.data.order_group_list).each(function (k, v) {
							var goodsNum = 0;
							orderId[k] = v.order_id;
							$(v.extend_order_goods).each(function (kk, vv) {
								goodsNum = goodsNum + parseInt(vv.goods_num);
							});
							$('#'+orderId[k]).text("共"+goodsNum + "件商品");
						});
						if(data.data.order_group_list.length==8){
							curpage ++;
							more = true;
						}else{
							more = false;
						}
					}
				}
			});
		}
	};
	$(window).scroll(function () {
		var doc_h = $(document).height();
		var win_h = $(window).height();
		var scroll_top = $(window).scrollTop();
		if (scroll_top >= doc_h - win_h) {
			if(more == true){
				app.order.ajaxDate(curpage);
			}

		}
	});
	app.order.init();

});