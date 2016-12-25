$(function () {
    FastClick.attach(document.body);

    $("#id-card").keydown(function(){
        $(".messageTips").text("");
    });

    $(".btn-commit").click(function(){
        var id_card=$("#id-card").val();

        if(id_card==""){
            $(".messageTips").text("身份证号不能为空");
        }else{
            //验证身份证号
            $.ajax({
                url:ApiUrl+"/index.php?act=member_security&op=reset_paypwd_by_identity&client_type=wap&key="+getcookie("key")+"&member_identity="+id_card,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(data){
                    if(data.code==200){
                        $(".messageTips").text("验证通过");
                        window.location.href=WapSiteUrl+"/trans_pwd_reset.html?member_identity="+id_card;
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

