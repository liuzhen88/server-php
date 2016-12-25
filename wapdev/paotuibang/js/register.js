// JavaScript Document
var yqmFlag = 0;

$(window).ready(function () {
	FastClick.attach(document.body);

	$("#RTel").keyup(function () {
		if ($("#RTel").val().length >= 11) {
			checkRTel();
		}
	});

	$("#confirmStepOne").click(function () {
		$(".messageTips").text("");
		$.ajax({
			url: ApiUrl + "/index.php?act=member_login&client_type=wap&op=vertify_code&mobile=" + $("#RTel").val() + "&vertify_code=" + $("#RYCode").val(),
			type: "get",
			dataType: "jsonp",
			jsonp: "callback",
			success: function (data) {
				if (data.code != 200) {
					$(".messageTips").text(data.message);
				} else {
					window.location.href = "register_step2.html?code=00000000&mobile=" + $("#RTel").val() + "&vertify_code=" + $("#RYCode").val();
				}
			}
		});
	});

});

function checkRTel() {
	var RTel = $("#RTel").val();
	$(".messageTips").text("");

	$.ajax({
		url: ApiUrl + "/index.php?act=member_login&op=check_mobile&client_type=wap&mobile=" + RTel,
		type: "get",
		dataType: "jsonp",
		jsonp: "callback",
		success: function (data) {
			if (data.code != 200) {
				$(".messageTips").text(data.message);
			} else {
				$(".getYZM").css("background-color", "#ff9c00");
				$(".messageTips").text("");
				yqmFlag = 1;
				$(".getYZM").click(function () {
					if (yqmFlag == 1) {
						$(".messageTips").text("验证码发送成功，请耐心等待！");
						yqmFlag = 0;
						$(".getYZM").css("background-color", "#ccc");
						var num = 60;
						var getFunc = setInterval(function () {
							if (num == 0) {
								clearInterval(getFunc);
								$(".getYZM").text("获取验证码").css("background-color", "#ff9c00");
								yqmFlag = 1;
							} else {
								num = num - 1;
								$(".getYZM").text(num + "秒");
							}
						}, 1000);
						$.ajax({
							url:ApiUrl+"/index.php?act=member_login&client_type=IOS&op=send_verify_code&mobile="+$("#RTel").val(),
							type:"get",
							dataType:"jsonp",
							jsonp:"callback",
							success: function(data){
								if(data.code!=200){
									$(".messageTips").text(data.message);
								}
							}
						});
					}
				});
			}
		}
	});

}