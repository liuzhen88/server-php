var CookieFlag = 1;

$(function () {
    //判断是否记住密码，获取cookie值
    var yjUsername = getcookie('username');
    var yjKey = getcookie('key');
    var yjPassword = getcookie('password');

    if (yjKey != null && yjKey != "" && yjKey != "undefined") {
        $("#username").val(yjUsername);
        $("#userpwd").val(yjPassword);
    }


    //记住密码图片高亮
    $(".my_passwordBoxL").click(function () {
        if ($(this).find("img").attr("src") == "images/checked@2x.png") {
            $(this).find("img").attr("src", "images/unchecked@2x.png");
            CookieFlag = 1;
        } else {
            $(this).find("img").attr("src", "images/checked@2x.png");
            CookieFlag = 0;
        }
    });

    $(".my_btnRegister").click(function () {
        window.location.href = WapSiteUrl + "/tmpl/member/register_step1.html";
    });

    $(".my_passwordBoxR").click(function () {
        window.location.href = WapSiteUrl + "/tmpl/member/password.html";
    });


    var memberHtml = '<a class="btn mr5" href="' + WapSiteUrl + '/tmpl/member/member.html?act=member">个人中心</a><a class="btn mr5" href="' + WapSiteUrl + '//tmpl/member/register.html">注册</a>';
    var act = GetQueryString("act");
    if (act && act == "member") {
        memberHtml = '<a class="btn mr5" id="logoutbtn" href="javascript:void(0);">注销账号</a>';
    }
    var tmpl = '<div class="footer">'
        + '<div class="footer-top">'
        + '<div class="footer-tleft">' + memberHtml + '</div>'
        + '<a href="javascript:void(0);"class="gotop">'
        + '<span class="gotop-icon"></span>'
        + '<p>回顶部</p>'
        + '</a>'
        + '</div>'
        + '<div class="footer-content">'
        + '<p class="link">'
        + '<a href="javascript:void(0);" class="standard">标准版</a>'
        + '<a href="javascript:void(0);">下载Android客户端</a>'
        + '</p>'
        + '<p class="copyright">'
        + '版权所有 2013-2015 © ShopNC'
        + '</p>'
        + '</div>'
        + '</div>';
    var render = tmpl;
    var html = render;
    $("#footer").html(html);
    //回到顶部
    $(".gotop").click(function () {
        $(window).scrollTop(0);
    });
    var key = getcookie('key');

    $('#logoutbtn').click(function () {
        var username = getcookie('username');
        var key = getcookie('key');

        var client = 'wap';
        $.ajax({
            type: 'get',
            url: ApiUrl + '/index.php?act=logout',
            data: {username: username, key: key, client: client},
            success: function (result) {
                if (result) {
                    delCookie('username');
                    delCookie('key');
                    location.href = WapSiteUrl + '/tmpl/member/login.html';
                }
            }
        });
    });

    var referurl = document.referrer;//上级网址
    $("input[name=referurl]").val(referurl);
    $.sValid.init({
        rules: {
            username: "required",
            userpwd: "required"
        },
        messages: {
            username: "用户名必须填写！",
            userpwd: "密码必填!"
        },
        callback: function (eId, eMsg, eRules) {
            if (eId.length > 0) {
                var errorHtml = "";
                $.map(eMsg, function (idx, item) {
                    errorHtml += "<p>" + idx + "</p>";
                });
                $(".error-tips").html(errorHtml).show();
            } else {
                $(".error-tips").html("").hide();
            }
        }
    });


    $('#loginbtn').click(function () {//会员登录
        var username = $('#username').val();
        var pwd = $('#userpwd').val();

        var client = 'wap';

        if ($.sValid()) {
            $.ajax({
                type: 'post',
                url: ApiUrl + "/index.php?act=member_login&client_type=wap&op=index",
                data: {username: username, password: pwd, client: client,lat:localStorage.getItem("latitude"),lng:localStorage.getItem("longitude"),province:localStorage.getItem("province"),city:localStorage.getItem("cityname"),district:localStorage.getItem("district"),user_type:0},
                dataType: 'json',
                success: function (result) {

                    if (result.code == 200) {
                        if (typeof(result.data.token) == 'undefined') {
                            return false;
                        } else {
                            var type = getcookie("type");
                            if (CookieFlag == 1) {
                                addcookie('username', result.data.username, 876000);
                                addcookie('key', result.data.token, 876000);
                                addcookie('password', pwd, 876000);
                                addcookie('user_id', result.data.user_id, 876000);
                                addcookie('mobile', result.data.member_mobile, 876000);
                                var username, key, password, user_id;
                                username = getcookie("username");
                                key = getcookie("key");
                                password = getcookie("password");
                                user_id = getcookie("user_id");
                                console.log(username+'  '+ password+' '+ key);
                                if (AGG.client.type() == "ios") {
                                    pop2(username, password, key);
                                    getUrl();
                                } else if (AGG.client.type() == "android") {
                                    app.pop2(username, password, key);
                                    getUrl();
                                } else {
                                    getUrl();
                                }

                            } else {
                                addcookie('username', result.data.username);
                                addcookie('key', result.data.token);
                                addcookie('password', pwd);
                                addcookie('user_id', result.data.user_id);
                                addcookie('mobile', result.data.member_mobile);
                                var username, key, password, user_id;
                                username = getcookie("username");
                                key = getcookie("key");
                                password = getcookie("password");
                                user_id = getcookie("user_id");
                                console.log(username+'  '+ password+' '+ key);
                                if (AGG.client.type() == "ios") {

                                    pop2(username, password, key);

                                    getUrl();
                                } else if (AGG.client.type() == "android") {

                                    app.pop2(username, password, key);

                                    getUrl();
                                } else {
                                    getUrl();
                                }
                            }


                        }

                    } else {

                        $(".messageTips").html(result.message);

                    }
                }
            });
        }
    });
});


function getUrl() {
    var wx_person = request("wx_person");
    if (request("fromRegister") == 1) {
        location.href = WapSiteUrl + "/index.html";
    } else {
        if (wx_person) {
            window.location.href = WapSiteUrl + "/member.html";
        } else {
            history.go(-1);
        }
    }
}

