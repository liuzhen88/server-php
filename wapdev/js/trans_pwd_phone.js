$(function () {
    FastClick.attach(document.body);

    var code_flag=1;//1可发送验证码 0不可发送

    $("#phone,#v-code").keydown(function(){
        $(".messageTips").text("");
    });

    $("#get-code").click(function(){
        var phone=$("#phone").val();
        var exp = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/;

        if(phone==""){
            $(".messageTips").text("手机号码不能为空");
        }else if(!exp.test(phone)){
            $(".messageTips").text("请填写正确的手机号码");
        }else if(code_flag==1){
            code_flag=0;
            getCode(phone);//发送验证码
            num=30;
            clearInterval(start);
            start=setInterval(numCal,1000);
        }
    });

    $(".btn-commit").click(function(){
        var phone=$("#phone").val();
        var v_code=$("#v-code").val();

        if(phone==""){
            $(".messageTips").text("手机号不能为空");
        }else if(v_code==""){
            $(".messageTips").text("验证码不能为空");
        }else{
            //验证验证码的正确性
            $.ajax({
                url:ApiUrl+"/index.php?act=member_login&client_type=wap&op=verify_code_repwd&mobile="+phone+"&vertify_code="+v_code,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success: function(data){
                    if(data.code!=200){
                        $(".messageTips").text(data.message);
                    }else{
                        window.location.href=WapSiteUrl+"/trans_pwd_reset.html?v_code="+v_code+"&phone="+phone;
                    }
                }
            });
        }
    });

    function getCode(phone){
        //alert("发送验证码");
        $.ajax({
            url:ApiUrl+"/index.php?act=member_login&op=send_verify_code&client_type=wap&mobile="+phone,
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success: function(data){
                if(data.code==200) {
                    $(".messageTips").text("验证码发送成功");
                }else{
                    $(".messageTips").text(data.message);
                }
            }
        });
    }

    var num;//验证码时间
    var start;//验证倒计时
    function numCal(){
        if(num<=0){
            clearInterval(start);
            code_flag=1;
            $("#get-code").text("获取验证码");
        }else{
            $("#get-code").text(num+"s后重试");
            num--;
        }
    }

});

