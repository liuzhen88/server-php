var phone;
var password;
var repassword;
var yqmFlag = 1;
var vertifyCode = 00000000;  // 验证码
var thisCode=request("invite_code"); // 邀请码
var userId=request("user_id");
var key;
var user_id;

if(thisCode==""){
	thisCode=requestforShare("invite_code");
}

function isPhone() {
	phone = $('#phone').val();
	var reg = /^0?1[3|4|5|7|8][0-9]\d{8}$/;
	if(phone===''){
		$('.messageTips').html("请输入手机号码~");
		return false;
	}else if (!reg.test(phone)) {
		$('.messageTips').html("请输入正确的手机号码~");
		return false;
	}else  {
		$('.messageTips').html("");
		return true;
	};
}

function hasPhone(){
	if(isPhone()){
		$.ajax({
			url: ApiUrl + "/index.php?act=member_login&op=check_mobile&client_type=wap&mobile=" + phone,
			type: "get",
			dataType: "jsonp",
			jsonp: "callback",
			success: function (data) {
				if (data.code != 200) {
					var message = data.message;
					$(".messageTips").text(message);
					$('#getYzm').css('background','#ccc');
					yqmFlag = 0;
				} else {
					$('#getYzm').css('background','#ff9c00');
					yqmFlag = 1;
				}
			}
		})
	}else{
		$('#getYzm').css('background','#ccc');
		yqmFlag = 0 ;
	}
}

function isPassword(){
	password=$('#password').val();
	if(password===''){
		$('.messageTips').html("请输入密码~");
		return false;
	}else{
		$('.messageTips').html("");
		return true;
	};
}

function isRepassword(){
	repassword=$('#repassword').val();
	if(repassword===''){
		$('.messageTips').html("请输入确认密码~");
		return false;
	}else if(password!==repassword){
		$('.messageTips').html("两次密码输入不一致,请确认重新输入");
		return false;
	}else{
		$('.messageTips').html("");
		return true;
	}
}

function isYZM(){
	vertifyCode=$('#vertifyCode').val();
	var reg = /^\d{6}$/;
	if(vertifyCode===''){
		$('.messageTips').html("请输入验证码~");
		return false;
	}else if (!reg.test(vertifyCode)) {
		$('.messageTips').html("验证码应为6位数字~");
		return false;
	}else{
		$('.messageTips').html("");
		return true;
	};
}


//获取url参数
function requestforShare(paras)
{
	var url = location.href;
	url=decodeURI(url);
	var paraString = url.substring(url.indexOf("?")+1,url.length).split(",,,");
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

$(function(){
	FastClick.attach(document.body);

	$.ajax({
		url: ApiUrl + "/index.php?act=member_login&op=get_user_pic&client_type=wap&member_id=" + userId,
		type: "get",
		dataType: "jsonp",
		jsonp: "callback",
		success: function (data) {
			if (data.code == 200) {
				$('#userImg').html('<img src="'+data.data+'" />')
			}
		}
	})


	$('#phone').blur(function(){hasPhone()});
	$('#password').blur(function(){isPassword()});
	$('#repassword').blur(function(){isRepassword()});
	$('#vertifyCode').blur(function(){isYZM()});


	$('#getYzm').click(function(){
		if (yqmFlag == 1 ){
			yqmFlag = 0;
			$(".getYZM").css("background-color", "#ccc");
			$('#phone').attr("readonly","readonly").css("color", "#ccc");

			var num = 60;
			var getFunc = setInterval(function () {
				if (num == 0) {
					clearInterval(getFunc);
					yqmFlag = 1;
					$(".getYZM").text("获取验证码");
					$(".getYZM").css("background-color", "#ff9c00");
					$('#phone').removeAttr("readonly").css("color", "#333");
				} else {
					num = num - 1;
					$(".getYZM").text(num + "秒");
				}
			}, 1000);

			$.ajax({
				url: ApiUrl + "/index.php?act=member_login&client_type=wap&op=send_verify_code&mobile=" + phone,
				type: "get",
				dataType: "jsonp",
				jsonp: "callback",
				success: function (data) {
					if (data.code != 200) {
						var message = data.message;
						$(".messageTips").text(message);
					}
				}
			});
		}
	});



	$('#registerBtn').click(function(){
		if(isPhone()&&isPassword()&&isRepassword()&&isYZM()){
			$.ajax({
				url:ApiUrl+"/index.php?act=member_login&client_type=wap&op=register&mobile="+phone+"&password="+password+"&code="+thisCode+"&vertify_code="+vertifyCode+"&register_from=2",
				type:"post",
				dataType:"jsonp",
				success: function(result){
					if(result.code!=200){
						var message=result.message;
						$(".messageTips").text(message);
					}else{
						addcookie('key',result.data.token,876000);
						addcookie('user_id',result.data.user_id,876000);
						key=getcookie("key");
						user_id=getcookie("user_id");
						window.location.href=WapSiteUrl+"/tmpl/register_ok.html";
					}
				}
			});
		}else{
			if(!isPhone()){
				hasPhone();
			}else if(!isYZM()){
				isYZM();
			}else if(!isPassword()){
				isPassword();
			}else if(!isRepassword()){
				isRepassword();
			}
		}
	});
	$('.off-btn').on('click',function(){
		$(this).parent().height('0');
	});
});










