$(function () {
    FastClick.attach(document.body);

    getUser();//获取账号名

    var v_code=request("v_code");
    var phone=request("phone");
    var member_identity=request("member_identity");

    var ajaxStr="";
    if(member_identity!="" && (phone=="" && v_code=="")){
        //身份证
        ajaxStr="&reset_type=1&member_identity="+member_identity;
    }else if(member_identity=="" && (phone!="" && v_code!="")){
        //短信
        ajaxStr="&reset_type=2&mobile="+phone+"&vertify_code="+v_code;
    }

    $("#new-trans-pwd,#re-trans-pwd").keydown(function(){
        $(".messageTips").text("");
    });

    $(".btn-commit").click(function(){
        var new_trans_pwd=$("#new-trans-pwd").val();
        var re_trans_pwd=$("#re-trans-pwd").val();

        if(new_trans_pwd==""){
            $(".messageTips").text("密码不能为空");
        }else if(new_trans_pwd!=re_trans_pwd){
            $(".messageTips").text("两次新密码输入不一致");
        }else{
            //设置新交易密码
            $.ajax({
                url:ApiUrl+"/index.php?act=member_security&op=reset_paypwd_step2&client_type=wap&key="+getcookie("key")+"&member_paypwd="+new_trans_pwd+ajaxStr,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(data){
                    if(data.code==200){
                        $(".messageTips").text("设置成功");
                        alert("设置成功!");
                        history.go(-3);
                    }else if(data.code==80001){
                        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                    }else{
                        $(".messageTips").text(data.message);
                    }
                }
            })
        }
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

