// JavaScript Document
var yqmFlag = 0;
var telFlag = 0;

$(window).load(function (e) {
	var windowHeight = $(window).height();
	var windowWidth = $(window).width();
	$(".my_registBody").css("background-size", windowWidth + "px " + windowHeight + "px");
});

$(window).ready(function (e) {

	/*$("#RTel,#RYQM").focus(function(){
	 $(this).val("");
	 });*/

	$("#RYQM").blur(function () {
		checkRYQM();
	});

	/*$("#RTel").blur(function(){
	 checkRTel();
	 });*/

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
					var message = data.message;
					$(".messageTips").text(message);
					//$("#RYCode").css("color","#999");
				} else {
					window.location.href = "register_step2.html?code=" + $("#RYQM").val() + "&mobile=" + $("#RTel").val() + "&vertify_code=" + $("#RYCode").val();
				}
			}
		});
	});

});


/*function test(){
 alert(1);
 }
 */
function checkRYQM() {

	var RYQM = $("#RYQM").val();
	$(".messageTips").text("");

	$.ajax({
		url: ApiUrl + "/index.php?act=member_login&op=check_code&client_type=wap&code=" + RYQM,

		type: "get",
		dataType: "jsonp",
		jsonp: "callback",
		success: function (data) {
			if (data.code != 200) {
				var message = data.message;
				//$("#RYQM").val(message);
				//$("#RYQM").css("color","#999");
				$(".messageTips").text(message);

			} else {
				yqmFlag = 1;
			}
		}
	});

}

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
				var message = data.message;
				//$("#RTel").val(message);
				//$("#RTel").css("color","#999");
				$(".messageTips").text(message);
			} else {
				$(".getYZM").css("background-color", "#ff9c00");
				$(".messageTips").text("");
				$(".getYZM").click(function () {
					if (yqmFlag == 1) {
						$(".messageTips").text("验证码发送成功，请耐心等待！");
						yqmFlag = 0;
						$(".getYZM").css("background-color", "#ccc");
						var num = 60;
						var getFunc = setInterval(function () {
							if (num == 0) {
								clearInterval(getFunc);
								$(".getYZM").text("获取验证码");
								$(".getYZM").css("background-color", "#ff9c00");
								yqmFlag = 1;
							} else {
								num = num - 1;
								$(".getYZM").text(num + "秒");
							}
						}, 1000);
						setTimeout(function () {
							yqmFlag = 1;
							$(".getYZM").css("background-color", "#ff9c00");
						},61000);
						$.ajax({
							url:ApiUrl+"/index.php?act=member_login&client_type=IOS&op=send_verify_code&mobile="+$("#RTel").val(),
							type:"get",
							dataType:"jsonp",
							jsonp:"callback",
							success: function(data){
								if(data.code!=200){
									var message=data.message;
									$(".messageTips").text(message);
									//$("#RYCode").css("color","#999");
								}
							}
						});
					}
				});
			}
		}
	});

}