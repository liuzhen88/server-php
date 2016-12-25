(function () {
	var len=0;
	var reason_id='';
	var key=getcookie("key");
	//key= "khasgfjhasgfkjasdfkjasdgbjkkklklhmnkdfsjj";
	if(key==''){
		window.location.href = WapSiteUrl + "/tmpl/member/login.html";
	}else{
		var input = document.getElementById('send_img');
		var refund=0;//用来标记用户选中的退款类型
		var rec_id=request("rec_id");
		var order_id=request("order_id");
		var num=request("num");
		var flag=request("flag");
		var goods_num=$("#num").html();
		var base=new Array();
		var i=0;
		var doc_w=$(document).width();
		var doc_h=$(document).height();
		var win_h=$(window).height();
		$("#screen").width(doc_w);
		$("#screen").height(doc_h);
		$("#tishi").css("left",(doc_w-200)/2);
		$("#tishi").css("top",(win_h-50)/2);
		$(".has").click(function(){

			$(".has").attr("src","images/ic_unchoosen@2x.png");
			$(this).attr("src","images/ic_choosen@2x.png");
			refund=$(".has").index(this);
		});
//获取退款原因
		$.ajax({
			url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=reasonlist&key="+key,
			type:"get",
			dataType:"jsonp",
			jsonp:"callback",
			success: function(data){
				if(data.code==200){
					var reasonId=new Array(),reasonInfo=new Array();
					$(data.data).each(function(k,v){
						reasonId[k]=v.reason_id;
						reasonInfo[k]=v.reason_info;
						var option="<option>"+reasonInfo[k]+"</option><span style='display:none'>"+reasonId[v]+"</span>";
						$("#select").append(option);
					});
					$("#select").change(function(){
						var reason=$("#select").val();
						var index=$.inArray(reason,reasonInfo);
						reason_id=reasonId[index];
					});

				}
			}
		});

		input.onchange = function(event) {
			var img = event.target.files[0];
			// 也可以传入图片路径：lrz('../demo.jpg', ...
			/*  lrz(this.files[0], {
			 width:300,
			 height:300,
			 before: function() {
			 console.log('压缩开始');
			 alert("压缩开始");
			 },
			 fail: function(err) {
			 console.error(err);
			 alert("压缩出错");
			 },
			 always: function() {
			 console.log('压缩结束');
			 alert("压缩结束");
			 },
			 done: function (results) {
			 // 你需要的数据都在这里，可以以字符串的形式传送base64给服务端转存为图片。
			 console.log(results.base64);
			 console.log(base.length);
			 // 以下为演示用内容
			 /* var tip = document.querySelector('#tip'),
			 report = document.querySelector('.y-pin'),
			 footer = document.querySelector('footer');
			 report.innerHTML = footer.innerHTML =  '';*/
			//tip.innerHTML = '<p>正在生成和上传..</p> <small class="text-muted">演示未优化移动端内存占用，可能会造成几秒内卡顿或闪退，不代表真实表现，请亲测。</small>';
			//demo_report('原始图片', results.origin, results.origin.size);

			/*setTimeout(function () {
			 demo_report('', results.base64, results.base64.length * 0.8);
			 // 发送到后端
			 var xhr = new XMLHttpRequest();
			 var data = {
			 base64: results.base64,
			 size: results.base64.length // 校验用，防止未完整接收
			 };
			 }, 100);
			 }*/
			var reader = new FileReader();
			reader.readAsDataURL(img);
			reader.onload = function(e){
				demo_report('',this.result,'');
			}
		};


		$("#sub").click(function(){
			var refuse_money=$("#money").html();
			var refund_type=refund+1;
			var message=$("#message").val();
			//base[]=results.base64;
			if(reason_id==''||reason_id=='undefined'){
				alert("请选择退款原因!");
			}else{
				$("#tishi").show();
				$("#screen").show();
				if(flag==1){
					//这里是申请修改的分支
					var refund_id=request("refund_id");
					$.ajax({
						url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=add_refund&key="+key+"&refund_amount="+refuse_money+"&order_id="+order_id+"&rec_id="+rec_id+"&goods_num="+goods_num+"&reason_id="+reason_id+"&refund_type="+refund_type+"&buyer_message="+message+"&refund_id="+refund_id,
						type:"post",
						data:{
							"refund_pic1":$(".y-pin li").eq(1).find("img").attr("src"),
							"refund_pic2":$(".y-pin li").eq(2).find("img").attr("src"),
							"refund_pic3":$(".y-pin li").eq(3).find("img").attr("src"),
						},
						dataType:"json",

						success: function(data){
							if(data.code==200){
								$("#tishi").hide();
								$("#screen").hide();
								alert("提交成功");
								var refund_id=data.data.refund_id;
								window.location.href="applyRefund.html?rec_id="+rec_id+"&num="+num+"&refund_id="+refund_id;
							}else{
								alert(data.message);
								$("#tishi").hide();
								$("#screen").hide();
							}
						}
					});
				}else{

					//这是新增的分支
					$.ajax({
						url:ApiUrl+"/index.php?act=member_refund&client_type=wap&op=add_refund&key="+key+"&refund_amount="+refuse_money+"&order_id="+order_id+"&rec_id="+rec_id+"&goods_num="+goods_num+"&reason_id="+reason_id+"&refund_type="+refund_type+"&buyer_message="+message,
						type:"post",
						data:{
							"refund_pic1":$(".y-pin li").eq(1).find("img").attr("src"),
							"refund_pic2":$(".y-pin li").eq(2).find("img").attr("src"),
							"refund_pic3":$(".y-pin li").eq(3).find("img").attr("src"),
						},
						dataType:"json",

						success: function(data){
							if(data.code==200){
								$("#tishi").hide();
								$("#screen").hide();
								alert("提交成功");
								var refund_id=data.data.refund_id;
								window.location.href="applyRefund.html?rec_id="+rec_id+"&num="+num+"&refund_id="+refund_id;
							}else{
								alert(data.message);
								$("#tishi").hide();
								$("#screen").hide();
							}
						}
					});
				}
			}
		});

		function demo_report(title, src, size) {
			var img = new Image(),
				li = document.createElement('li'),
				size = (size / 1024).toFixed(2) + 'KB';

			if(size === 'NaNKB') size = '';

			img.onload = function () {


				//li.className = 'item';
				//li.innerHTML = content;
				li.appendChild(img);

				document.querySelector('.y-pin').appendChild(li);
				var div="<div style='color:red' class='close'>X</div>";
				$(".y-pin li").css("position","relative");
				$(".y-pin li").append(div);
				$(".y-pin li").eq(0).find(".close").css("display","none");
				$(".close").click(function(){
					$(this).parent().remove();
					len=$(".y-pin li").length;
					if(len<4){
						$("#top_div").hide();
					}
				});
				len=$(".y-pin li").length;

				if(len==4){
					$("#top_div").show();
				}
			};


			img.src = typeof src === 'string' ? src : URL.createObjectURL(src);

			//$(".y-pin").append("<li><img src="+img.src+"/></li>");

		}

		$("#top_div").click(function(){
			alert("亲，最多上传3张哦!");
			len=$(".y-pin li").length;

		});

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
	}
})();


$(function(){
	//调安卓的上传图片
	$("#send_img").click(function(){
		if(AGG.client.type()=="android"){
			app.chosePic();
		}
	});
});

function transmitPic(pic){
	alert('图片选择成功！');

	var html = '<li style="position: relative;"><img src="'+ pic +'"><div style="color:red" class="close">X</div></li>';

	$('.y-pin').append(html);
	$(".close").click(function(){
		$(this).parent().remove();
		var lens = $('.y-pin li').length;
		if(lens>=4){
			$('#sendimgLi').hide();
		}else{
			$('#sendimgLi').show();
		}
	});

	var lens = $('.y-pin li').length;
	if(lens>=4){
		$('#sendimgLi').hide();
	}else{
		$('#sendimgLi').show();
	}

}