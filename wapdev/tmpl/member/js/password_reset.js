// JavaScript Document
var thisMobile=request("mobile");
var thisVertifyCode=request("vertify_code");

$(window).load(function(e){
	var docHeight=$(document).height();
	var windowWidth=$(window).width();
	$(".my_registBody").css("background-size",windowWidth+"px "+docHeight+"px");
});

$(window).ready(function(e){
	$("#confirm").click(function(){
		var password=$("#password").val();
		var repassword=$("#repassword").val();

		if(password==repassword){
			$.ajax({
				url:ApiUrl+"/index.php?act=member_login&client_type=wap&op=reset_password",
				type:"post",
				dataType:"jsonp",
				data:{mobile:thisMobile,password:password,vertify_code:thisVertifyCode},
				success: function(data){
					if(data.code!=200){
						var message=data.message;
						$(".messageTips").text(message);
					}else{
						$(".messageTips").text("重置密码成功！3秒后跳转回登录页面！");
						window.setTimeout(function(){window.location.href=WapSiteUrl+"/tmpl/member/login.html?fromRegister=1";},3000);
					}
				}	
			});
		}else{
			$(".messageTips").text("两次密码输入不一致！");
		}	
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



