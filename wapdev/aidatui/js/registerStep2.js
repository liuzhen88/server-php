// JavaScript Document
var thisCode=request("code");
var thisMobile=request("mobile");
var thisVertifyCode=request("vertify_code");
var registerFrom = request("register_from"); //是否为扫码注册


$(window).ready(function() {
	FastClick.attach(document.body);
	$("#confirm").click(function(){
		var nickname=$("#nickname").val();
		var password=$("#password").val();
		var repassword=$("#repassword").val();
		
		if(password==repassword){
			$.ajax({
				url:ApiUrl+"/index.php?act=member_login&client_type=wap&op=register",
				type:"post",
				dataType:"json",
				data:{mobile:thisMobile,password:password,password_confirm:repassword,nick_name:nickname,code:thisCode,vertify_code:thisVertifyCode,register_from:registerFrom},
				success: function(data){
					if(data.code!=200){
						var message=data.message;
						$(".messageTips").text(message);
					}else{
						$(".messageTips").text("注册成功！3秒后跳转回登录页面！");
						window.setTimeout(function(){window.location.href=WapSiteUrl+"/aidatui/login1.html?fromRegister=1";},3000);
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

