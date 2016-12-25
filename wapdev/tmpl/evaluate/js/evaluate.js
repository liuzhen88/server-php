$(function (){
    $(".jl_eva_chk").click(function(){
        if($(this).hasClass("on")){
            $(this).removeClass("on").find("input").prop("checked",false);
        }else{
            $(this).addClass("on").find("input").prop("checked",true);
        }
    })
    $("span.del").on('click',function(){
        $(this).parent("li").remove();
    })
    $(".jl_star li").on('click',function(){
    	$(this).siblings().removeClass('on');
    	$(this).prevAll().andSelf().addClass('on');
    })
});

var thisGoodId=request("goodId");
var thisOrderId=request("orderId");
var thisGoodName=request("goodName");
var thisGoodPrice=request("goodPrice");
var thisGoodNum=request("goodNum");
var thisGoodSrc=request("goodImg");

var thisKey=getcookie("key");

var thisGoodScore=-1;
var showRealName=1;

//alert(thisGoodId+" "+thisOrderId+" "+thisGoodName+" "+thisGoodPrice+" "+thisGoodNum+" "+thisGoodSrc);



//测试数据
/*thisKey="a9ed04b6d0e153e84b3f33c47aa2549a";
thisGoodId=237;
thisOrderId=7000000000150801;
thisGoodName="Rio薄荷糖水果无糖压片糖果零食礼盒薄荷糖水果无糖压片糖果零食礼盒";
thisGoodPrice="¥100.0";
thisGoodNum=1;
thisGoodSrc="http://7xl2n7.com2.z0.glb.qiniucdn.com/shop/store/goods/35/2015/08/20/35_04933825313328536.jpg?imageView2/1/w/360/h/360";*/

var base64Img=new Array();
base64Img[0]="";
base64Img[1]="";
base64Img[2]="";
base64Img[3]="";

$(window).ready(function(e) {
    $("#eGoodSrc").attr("src",thisGoodSrc);
	$(".my_goodName").text(thisGoodName);
	$(".my_price").text("¥"+thisGoodPrice);
	$(".my_num").text("×"+thisGoodNum);

	$(".lmyStar li").click(function(){
		var starIndex=$(".lmyStar li").index(this);
		thisGoodScore=starIndex+1;
	});

	$(".isShowReal").click(function(){
		if(showRealName==0){
			$(".isShowReal").attr("src","images/ic_no-name@2x.png");
			showRealName=1;
		}else{
			$(".isShowReal").attr("src","images/ic_name@2x.png");
			showRealName=0
		}
	});

	$("#eSendComm").click(function(){

		if(thisGoodScore==-1){
			alert("请填写商品评分！");
		}else if($("#eGoodCont").val()==null || $("#eGoodCont").val()==""){
			alert("请填写商品评价内容！");
		}else{

			$(".y-firstmargin").each(function(index, element) {
                var base64Index=$(".y-firstmargin").index(this);
				base64Img[base64Index]=$(this).find(".imgToBase64").text();
            });

            //base64Img=base64Img.substr(0,base64Img.length-1);
			//console.log(base64Img);

			sendAjax();
		}


	});

});

function sendAjax(){
	var thisGoodCont=$("#eGoodCont").val();
	
	$.ajax({
		url:ApiUrl+"/index.php?act=member_order&op=good_evaluate&client_type=wap&key="+thisKey+"&order_id="+thisOrderId+"&goods_id="+thisGoodId+"&geval_isanonymous="+showRealName+"&geval_scores="+thisGoodScore+"&geval_content="+thisGoodCont,
		dataType:"json",
		type:"post",
		data:{
			"image_1":base64Img[0],
			"image_2":base64Img[1],
			"image_3":base64Img[2],
			"image_4":base64Img[3]
		},
		callback:"callback",
		success: function(data){
			if(data.code==200){
				alert('评价成功！');
				window.location.href=WapSiteUrl+"/tmpl/evaluate/evaluate_store.html?order_id="+thisOrderId;
			}else{
				alert(data.message);
				if(data.code==80001){
					window.location.href=WapSiteUrl+"/tmpl/member/login.html";
				}else if(data.code==80002){
					window.location.href=WapSiteUrl+"/tmpl/evaluate/evaluate_store.html?order_id="+thisOrderId;
				}
			}
		}
	});
}

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


//调安卓的上传图片
$("#send_img").click(function(){
	if(request("client_type")=="android"){
		app.chosePic();
	}
});

function transmitPic(pic){

	if($('.y-pin').find(".y-firstmargin").length>=3){
		//$("#send_img").css("display","none");
		$("#send_imgBox").css("display","none");
	}else{
		//$("#send_img").css("display","block");
		$("#send_imgBox").css("display","block");
	}

	if((pic).split("/")[0]!="data:image"){
		if((pic).split(";")[0]!="data:"){
			//alert((this.result).split("/")[0]);

			alert("图片格式不对，请重新上传！");

			return;
		}else{

			alert("尚不支持该机型图片上传，敬请谅解！");
			return;
		}
	}else{
		$('.y-pin').append("<li class='y-firstmargin'><img src='"+pic+"'/><span class='del'></span><span class='imgToBase64'>"+pic+"</span></li>");
		$(".y-pin").find("li").eq(0).removeClass("y-firstmargin");

		$(".del").click(function(){

			$(this).parents(".y-firstmargin").remove();

			//$("#send_img").css("display","block");
			$("#send_imgBox").css("display","block");

		});
	}

}