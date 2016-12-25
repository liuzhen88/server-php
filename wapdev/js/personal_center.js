$(function () {
    FastClick.attach(document.body);
    var key=getcookie("key");
    $("#login-pwd").click(function(){
        window.location.href=WapSiteUrl+"/tmpl/member/password.html";
    });

    $("#address").click(function(){
        window.location.href=WapSiteUrl+"/tmpl/member/address_list.html?client_type=wap&key="+getcookie("key");
    });


    //获取用户个人信息
    $.ajax({
        url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+getcookie("key")+"&token_member_id="+getcookie("user_id"),
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success: function(data){
            if(data.code==200){
                var user_id=data.data.member_info.member_id;
                var name=data.data.member_info.nick_name;
                var photo=data.data.member_info.avator;
                var invitation = data.data.member_info.invitation;
                var is_dis=data.data.member_info.is_distribution;
                var tel=data.data.member_info.user_name;
                var sex=data.data.member_info.sex;
                var realname=data.data.member_info.real_name;
                var trans_pwd=data.data.member_info.paypwd_status;

                $("#nickname .ic-cont").text(name);
                $("#avator .ic-cont img").attr("src",photo);

                if(tel!="" && tel!=null){
                    $("#link-tel .ic-cont").text("已绑定"+tel);
                }

                if(sex==0){
                    $("#sex .ic-cont").text("保密");
                }else if(sex==1){
                    $("#sex .ic-cont").text("男");
                }else if(sex==2){
                    $("#sex .ic-cont").text("女");
                }

                if(realname==0){
                    $("#real-name .ic-cont").text("未认证");
                    $("#real-name").click(function(){
                        window.location.href=WapSiteUrl+"/set_realname.html";
                    });
                }else if(realname==1){
                    $("#real-name .ic-cont").text("已认证，非本人设置请致电客服");
                }

                if(trans_pwd==0){
                    $("#trans-pwd .ic-cont").text("未设置");
                    $("#trans-pwd").click(function(){
                        window.location.href=WapSiteUrl+"/trans_pwd.html";
                    });
                }else if(trans_pwd==1){
                    $("#trans-pwd .ic-cont").text("修改");
                    $("#trans-pwd").click(function(){
                        window.location.href=WapSiteUrl+"/trans_pwd_update.html";
                    });
                }
                /*
                $("#select-file").change(function(){
                        var title = $('input[name=person-pic]').val();
                    $("#file-form").ajaxSubmit({
                        type:"post",
                        url:"http://devshop.aigegou.com/mobile/index.php?act=member_index&op=upload_pic",
                        data:{
                            "key":key,
                            "client_type":"wap",
                            "member_avatar":title,
                        },
                        success:function(data){
                            alert(data.message);
                        }
                    });
                    return false;
                });
                */
                $("#select-file").change(function(){
                    var url=$("#file-form").attr("action");
                    var new_url=url+"&key="+key+"&flag=is_wap_send";
                    $("#file-form").attr("action",new_url);
                    $("#submit").click();
                });
                //修改昵称
                $("#nick-name").click(function(){
                    var name=$("#nick-name").html();
                    $("#get_name").val(name);
                    $("#screen").show();
                    $("#modify-name").show();
                    var h=$(".confirm").height();
                    $(".quit").css("line-height",h+"px");
                    $(".confirm").css("line-height",h+"px");
                    $(".quit").click(function(){
                        $("#modify-name").hide();
                        $("#screen").hide();
                    });
                    $(".confirm").click(function(){
                        var confirmName=$("#get_name").val();
                        $.ajax({
                            url:ApiUrl+"/index.php?act=member_index&op=update_nickname&key="+key+"&nick_name="+confirmName+"&client_type=wap",
                            type:"get",
                            dataType:"jsonp",
                            jsonp:"callback",
                            success:function(data){
                                if(data.code==200){
                                    alert(data.message);
                                    window.location.reload();
                                }
                            }
                        });
                    });
                });
                //设置性别
                $("#sex").click(function(){
                    window.location.href=WapSiteUrl+"/set_sex.html";
                });
                //个人二维码
                $("#personal-card").click(function(){
                    window.location.href=WapSiteUrl+"/personal_card.html?name="+name+"&invitation="+invitation+"&user_id="+user_id+"&photo="+photo;
                });

            }else if(data.code==80001){
                window.location.href=WapSiteUrl+"/tmpl/member/login.html";
            }else{
                alert(data.message);
            }
        }
    });

});

