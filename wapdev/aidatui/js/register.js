// JavaScript Document

$(window).ready(function () {
	FastClick.attach(document.body);

    initPage();
    var registerFrom = request("register_from"); //是否为扫码注册
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
					window.location.href = "register_step2_next.html?code=00000000&mobile=" + $("#RTel").val() + "&vertify_code=" + $("#RYCode").val()+"&register_from="+registerFrom;
				}
			}
		});
	});

});

function initPage() {
	var RTel = $("#RTel").val();
	$(".messageTips").text("");
    yqmFlag = 1;

	$(".getYZM").css("background-color", "#ff9c00").click(function () {
        if ($("#RTel").val().length < 11) { //TODO:check the tel number.
            $(".messageTips").text("手机号码错误！");
            return;
        }
        if (yqmFlag == 1) {
            yqmFlag = 0;
            $(".getYZM").css("background-color", "#ccc");

            //set button text to count 60s
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

            //send request
            $.ajax({
                url:ApiUrl+"/index.php?act=member_login&client_type=wap&op=adt_login_send_verify_code&mobile="+$("#RTel").val()+"&user_type=1",
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success: function(data){
                    if(data.code==200){
                      $(".messageTips").text("验证码发送成功，请耐心等待！");
                    }else{
                      $(".messageTips").text(data.message);
                      clearInterval(getFunc);
                      $(".getYZM").text("获取验证码").css("background-color", "#ff9c00");
                      yqmFlag = 1;
                    }
                }
            });
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

