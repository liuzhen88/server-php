$(function(){
    var allow_next=0;

    $(document).ready(function(){
        var key=getcookie("key");
        if(key==""){
            window. location.href = WapSiteUrl + '/tmpl/member/login.html';
        }else {
            $.ajax({
                url:ApiUrl+"/index.php?act=member_security&op=index&key="+key+"&client_type=wap",
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(data){
                    if(data.code==200){
                        if(data.data.is_member_realname!=0){
                            var member_realname=data.data.member_realname;
                            $("#username").val(member_realname);
                            var member_identity=data.data.member_identity;
                            $("#id-card2").val(member_identity);
                        }else{
                            alert("请先设置实名认证");
                            window.location.href = WapSiteUrl + '/set_realname.html';
                        }
                    }else if(data.code==80001){
                        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
                    }
                }
            });

            //身份证和卡号
            changeInput();

            $(".submit").click(function () {
                if(allow_next==1){
                    allow_next=0;
                    $(".submit").css("background","#ddd");

                    if($("#bank-card").val()==""){
                        alert("银行卡号不能为空");
                    }else if($("#bank-card").val().length<16 || $("#bank-card").val().length>19){
                        alert("银行卡号长度为16-19位");
                    }else{
                        $.ajax({
                            url: ApiUrl+"/index.php?act=member_bankcard&op=add_bank_card&client_type=wap&key="+key+"&bank_no="+$("#bank-card").val()+"&bank_name="+$("#bank-kh").val(),
                            type:"get",
                            dataType:"jsonp",
                            jsonp:"callback",
                            success:function(data){
                                allow_next=1;
                                $(".submit").css("background","#ff4946");

                                if(data.code==200){
                                    $(".add-bank-card-box").hide();
                                    $(".add-bank-card-success").show();
                                    $(".btn_success").show();
                                    $(".sh_back").hide();
                                }else if(data.code == 80001){
                                    alert(data.message);
                                    window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                                }else if(data.code == 80002){
                                    alert(data.message);
                                    $(".bank-kh").show();
                                }
                            }
                        });
                    }
                }
            });
        }
    });

    function changeInput(){
        var idCardFlag=$("#id-card2").val().length;
        //身份证号
        $("#id-card2").on("keyup",function(){
            var stringLen=$("#id-card2").val().length;

            if(idCardFlag<stringLen){
                idCardFlag=stringLen;

                if(stringLen<=18){
                    var nowValue=($("#id-card2").val()).substr(stringLen-1,1);
                    $("#id-card").val($("#id-card").val()+nowValue);


                    if($("#id-card2").val().length<=1 || $("#id-card2").val().length==18){
                        $("#id-card2").val($("#id-card2").val().substr(0,stringLen-1)+nowValue);
                    }else{
                        $("#id-card2").val($("#id-card2").val().substr(0,stringLen-1)+"*");
                    }
                }else{
                    $("#id-card2").val($("#id-card2").val().substr(0,18));
                    alert("身份证不得超过18位");
                }
            }else{
                idCardFlag=stringLen;

                $("#id-card").val($("#id-card").val().substr(0,$("#id-card2").val().length));

            }

            //alert($("#id-card").val());
        });

        var bankCardFlag=$("#bank-card2").val().length;
        //银行卡号
        $("#bank-card2").on("keyup",function(){

            var stringLen=$("#bank-card2").val().length;

            if(bankCardFlag<stringLen){
                bankCardFlag=stringLen;

                if(stringLen<=23){
                    var nowValue=($("#bank-card2").val()).substr(stringLen-1,1);
                    $("#bank-card").val($("#bank-card").val()+nowValue);

                    if($("#bank-card2").val().length==4 || $("#bank-card2").val().length==9 || $("#bank-card2").val().length==14 || $("#bank-card2").val().length==19){
                        $("#bank-card2").val($("#bank-card2").val()+" ");
                    }
                }else{
                    $("#bank-card2").val($("#bank-card2").val().substr(0,23));
                    alert("银行卡号不得超过19位");
                }
            }else{
                bankCardFlag=stringLen;

                if($("#bank-card2").val().length>=20){
                    $("#bank-card").val($("#bank-card").val().substr(0,$("#bank-card2").val().length-4));
                }else if($("#bank-card2").val().length<20 && $("#bank-card2").val().length>=15){
                    $("#bank-card").val($("#bank-card").val().substr(0,$("#bank-card2").val().length-3));
                }else if($("#bank-card2").val().length<15 && $("#bank-card2").val().length>=10){
                    $("#bank-card").val($("#bank-card").val().substr(0,$("#bank-card2").val().length-2));
                }else if($("#bank-card2").val().length<10 && $("#bank-card2").val().length>=5){
                    $("#bank-card").val($("#bank-card").val().substr(0,$("#bank-card2").val().length-1));
                }else{
                    $("#bank-card").val($("#bank-card").val().substr(0,$("#bank-card2").val().length));
                }

            }

            if($("#bank-card").val().length>=16){
                $(".submit").css("background","#ff4946");
                allow_next=1;
            }else{
                $(".submit").css("background","#ddd");
                allow_next=0;
            }

            //alert($("#bank-card").val());
        });
    }

});
