$(function () {
    FastClick.attach(document.body);
    var windowH = $(window).height();
    $('.login').height(windowH);

    var fromRegister = request('fromRegister');
    $('#loginBtn').click(function () {//会员登录
        var username = $('#username').val();
        var pwd = $('#userpwd').val();

        $.ajax({
            type: 'post',
            url: ApiUrl + "/index.php?act=member_login&client_type=wap&op=index",
            data: {username: username, password: pwd},
            dataType: 'json',
            success: function (result) {
                if (result.code == 200) {
                    if (typeof(result.data.token) == 'undefined') {
                        return false;
                    } else {
                        addcookie('key', result.data.token, 876000);
                        addcookie('mobile', result.data.member_mobile, 876000);
                        if(fromRegister == 1){
                            window.location.href=WapSiteUrl+"/aidatui/index.html";
                        }else{
                            //history.go(-1);
                            window.location.href=WapSiteUrl+"/aidatui/index.html";
                        }
                    }
                } else {
                    $(".messageTips").html(result.message);
                }
            }
        });
    });
});

