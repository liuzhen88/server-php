$(function () {
    FastClick.attach(document.body);

    $("#id-card,#re-id-card,#real-name").keydown(function(){
        $(".messageTips").text("");
    });

    $(".btn-commit").click(function(){
        var real_name=$("#real-name").val();
        var id_card=$("#id-card").val();
        var re_id_card=$("#re-id-card").val();

        if(real_name==""){
            $(".messageTips").text("姓名不能为空");
        }else if(id_card!=re_id_card){
            $(".messageTips").text("两次身份证号输入不一致");
        }else{
            //设置实名认证
            $.ajax({
                url:ApiUrl+"/index.php?act=member_security&op=set_realname&client_type=wap&key="+getcookie("key")+"&member_realname="+real_name+"&member_identity="+id_card+"&token_member_id="+getcookie("user_id"),
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

});

