$(function(){
    $(document).ready(function(){

        var nowChooseBankId;
        var nowIndex;
        var key=getcookie("key");
        var from_change_card=request("from_change_card");
        if(key==""){
            window. location.href = WapSiteUrl + '/tmpl/member/login.html';
        }else {
            $.ajax({
                url:ApiUrl+"/index.php?act=member_bankcard&op=get_all_bank_card&key="+key+"&client_type=wap",
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(data){
                    if(data.code==200){

                        var DoTmpl = doT.template($("#card-list-tmpl").html());
                        $(".card-list ul").html(DoTmpl(data.data));

                        $(".card-list ul li").width($(window).width()-30);

                        //点击操作银行卡
                        $(".bank-jian").click(function(){
                            nowIndex=$(".bank-jian").index(this);
                            nowChooseBankId=data.data[nowIndex].id;

                            $(".bank-jian").removeClass("on");
                            $(this).addClass("on");
                            $(".bank-list-lay").show();

                            if($(this).parents(".per-bank-box").find(".bank-def-flag").attr("bank-default-flag")==1){
                                $(".bank-lay-set-default").hide();
                            }else{
                                $(".bank-lay-set-default").show();
                            }

                        });

                        if(from_change_card==1){
                            //从更换银行卡过来，点击选择银行卡
                            $(".card-list ul li").on("click",function(){
                                var thisIndex=$(".card-list ul li").index(this);
                                var changeBankId=data.data[thisIndex].id;
                                var changeBankName=data.data[thisIndex].pdc_bank_name;
                                var changeBankCardType=data.data[thisIndex].cardtype;
                                if(changeBankCardType==""){
                                    changeBankCardType="银行卡";
                                }
                                var changeBankNo=data.data[thisIndex].pdc_bank_no;
                                changeBankNo=changeBankNo.substr(changeBankNo.length-4,4);

                                window.location.href = WapSiteUrl + '/points_to_cash.html?changeBankId='+changeBankId+'&changeBankName='+changeBankName+'&changeBankCardType='+changeBankCardType+'&changeBankNo='+changeBankNo;
                            });
                        }


                    }else if(data.code==80001){
                        alert(data.message);
                        window.location.href = WapSiteUrl + '/tmpl/member/login.html';
                    }
                }
            });

            $(".add-bank-card").on("click",function(){
                window.location.href = WapSiteUrl + '/bind_bank_card.html';
            });

            //点击管理
            $(".btn_gl").on("click",function(){
                $(".btn_gl").hide();
                $(".btn_success").show();

                $(".bank-jian").show();
                $(".card-list ul li").css("margin-left","52px");

            });

            //点击完成
            $(".btn_success").on("click",function(){
                $(".btn_gl").show();
                $(".btn_success").hide();

                $(".bank-jian").hide();
                $(".card-list ul li").css("margin-left","15px");
            });

            //点击取消蒙层
            $(".bank-lay-cancel").on("click",function(){
                $(".bank-list-lay").hide();
                $(".bank-jian").removeClass("on");
            });

            //删除选中银行卡
            $(".bank-lay-delete").on("click",function(){
                $.ajax({
                    url:ApiUrl+"/index.php?act=member_bankcard&op=del_bank_card&key="+key+"&client_type=wap&bank_id="+nowChooseBankId,
                    type:"get",
                    dataType:"jsonp",
                    jsonp:"callback",
                    success:function(data){
                        if(data.code==200){
                            //$(".per-bank-box").eq(nowIndex).hide();
                            //if(nowIndex==0){
                            //    $(".per-bank-box").eq(nowIndex+1).find(".bank-def-flag").removeClass("bank-def-style2").addClass("bank-def-style1");
                            //}
                            //$(".bank-list-lay").hide();----不刷新页面操作
                            window.scrollTo(0, 0);//回顶端
                            window.location.reload();//刷新页面操作
                        }else if(data.code==80001){
                            alert(data.message);
                            window.location.href = WapSiteUrl + '/tmpl/member/login.html';
                        }else{
                            alert(data.message);
                        }
                    }
                });
            });

            //将选中银行卡设为默认
            $(".bank-lay-set-default").on("click",function(){
                $.ajax({
                    url:ApiUrl+"/index.php?act=member_bankcard&op=set_bank_card_default&key="+key+"&client_type=wap&bank_id="+nowChooseBankId,
                    type:"get",
                    dataType:"jsonp",
                    jsonp:"callback",
                    success:function(data){
                        if(data.code==200){
                            window.scrollTo(0, 0);//回顶端
                            window.location.reload();//刷新页面操作
                        }else if(data.code==80001){
                            alert(data.message);
                            window.location.href = WapSiteUrl + '/tmpl/member/login.html';
                        }else{
                            alert(data.message);
                        }
                    }
                });
            });

        }
    });

});

function subBankNoStr(bankNo){
    return bankNo.substr(bankNo.length-4,4);
}
