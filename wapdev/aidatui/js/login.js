$(function () {
    FastClick.attach(document.body);
    var fromRegister = request('fromRegister');
    var isFocus = 0;
    $('.login-txt input').focus(function(){
        $('.login-more').hide();
        isFocus = 1;
    }).blur(function(){
        isFocus = 0;
        setTimeout(function(){
            if (isFocus == 0) {
                $('.login-more').show();
            }
        },500);
    });
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
                        localStorage.setItem("token",result.data.token);
                        localStorage.setItem("mobile",result.data.member_mobile);
                        if(fromRegister == 1){
                            window.location.href=WapSiteUrl+"/aidatui/index1.html";
                        }else{
                            //history.go(-1);
                            window.location.href=WapSiteUrl+"/aidatui/index1.html";
                        }
                    }
                } else {
                    $(".messageTips").html(result.message);
                }
            }
        });
    });
});

