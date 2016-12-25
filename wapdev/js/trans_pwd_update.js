$(function () {
    FastClick.attach(document.body);

    getUser();//获取账号名

    $("#now-trans-pwd,#new-trans-pwd,#re-trans-pwd").keydown(function(){
        $(".messageTips").text("");
    });

    $(".btn-commit").click(function(){
        var now_trans_pwd=$("#now-trans-pwd").val();
        var new_trans_pwd=$("#new-trans-pwd").val();
        var re_trans_pwd=$("#re-trans-pwd").val();

        if(now_trans_pwd=="" || new_trans_pwd==""){
            $(".messageTips").text("密码不能为空");
        }else if(new_trans_pwd!=re_trans_pwd){
            $(".messageTips").text("两次新密码输入不一致");
        }else{
            //修改交易密码
            $.ajax({
                url:ApiUrl+"/index.php?act=member_security&op=modify_paypwd&client_type=wap&key="+getcookie("key")+"&member_paypwd="+now_trans_pwd+"&member_new_paypwd="+new_trans_pwd,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(data){
                    if(data.code==200){
                        $(".messageTips").text("设置成功");
                        alert("设置成功!");
                        //window.location.href=WapSiteUrl+"/personal_center.html";
                        history.back();
                    }else if(data.code==80001){
                        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                    }else{
                        $(".messageTips").text(data.message);
                    }
                }
            })
        }
    });

    $(".forget-pwd").click(function(){
        window.location.href=WapSiteUrl+"/trans_pwd_resetlist.html";
    });

    function getUser(){
        //获取账号名
        $.ajax({
            url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+getcookie("key"),
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success: function(data){
                if(data.code==200){
                    var userName=data.data.member_info.user_name;
                    $(".tip-info span").text(userName.substr(0,3)+"****"+userName.substr(7,4));
                }
            }
        });
    }

});

