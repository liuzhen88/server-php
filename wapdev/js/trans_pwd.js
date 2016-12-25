$(function () {
    FastClick.attach(document.body);

    isReal();//是否实名认证

    $("#trans-pwd,#re-trans-pwd").keydown(function(){
        $(".messageTips").text("");
    });

    $(".btn-commit").click(function(){
        var trans_pwd=$("#trans-pwd").val();
        var re_trans_pwd=$("#re-trans-pwd").val();

        if(trans_pwd==""){
            $(".messageTips").text("密码不能为空");
        }else if(trans_pwd!=re_trans_pwd){
            $(".messageTips").text("两次密码输入不一致");
        }else{
            //设置交易密码
            $.ajax({
                url:ApiUrl+"/index.php?act=member_security&op=set_paypwd&client_type=wap&key="+getcookie("key")+"&member_paypwd="+trans_pwd+"&token_member_id="+getcookie("user_id"),
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(data){
                    if(data.code==200){
                        $(".messageTips").text("设置成功");
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

    function isReal(){
        //是否实名认证
        $.ajax({
            url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+getcookie("key")+"&token_member_id="+getcookie("user_id"),
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success: function(data){
                if(data.code==200){
                    var realname=data.data.member_info.real_name;
                    if(realname==0){
                        //没有实名认证
                        //alert("设置交易密码需实名认证");
                        window.location.href=WapSiteUrl+"/set_realname.html";
                    }
                }
            }
        });
    }

});

