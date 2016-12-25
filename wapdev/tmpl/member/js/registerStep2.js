// JavaScript Document
var thisCode=request("code");
var thisMobile=request("mobile");
var thisVertifyCode=request("vertify_code");

$(window).load(function(e){
	var windowHeight=$(window).height();
	var windowWidth=$(window).width();
	$(".my_registBody").css("background-size",windowWidth+"px "+windowHeight+"px");
});

$(window).ready(function(e) {
	$("#confirm").click(function(){
		//var nickname=$("#nickname").val();
		var nickname="agg_"+getRandomNum();

		var password=$("#password").val();
		var repassword=$("#repassword").val();

		if(password==repassword){
			$.ajax({
				url:ApiUrl+"/index.php?act=member_login&client_type=wap&op=register",
				type:"post",
				dataType:"jsonp",
				data:{mobile:thisMobile,password:password,password_confirm:repassword,nick_name:nickname,code:thisCode,vertify_code:thisVertifyCode},
				success: function(data){
					if(data.code!=200){
						var message=data.message;
						$(".messageTips").text(message);
					}else{
						$(".messageTips").text("注册成功！3秒后跳转回登录页面！");
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

function getRandomNum(){
	function only(ele,arr){
		if(arr.length==0){
			return true;
		}
		for(var j=0;j<arr.length;j++){
			if(ele==arr[j]){
				return false;
			}else{
				return true;
			}
		}
	}

	var arr=[0,1,2,3,4,5,6,7,8,9];//指定随机内容

	var randNum=null;
	var old=[];
	var str="";
	function done(){
		randNum=Math.floor(Math.random()*10);//指定随机内容个数
		if(only(randNum,old)){
			str=str+arr[randNum];
			old.push(randNum);
		}
		else{
			done();
		}
	}
	for(var index=0;index<8;index++){//要的长度
		done();
	}
	return str;
}
