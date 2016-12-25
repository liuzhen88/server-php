$(function () {
    FastClick.attach(document.body);

    $("#trans-pwd").keydown(function(){
        $(".messageTips").text("");
    });

    var money=request("money");
    var goods_id=request("goods_id");
    var store_id=request("store_id");
    var goods_amount=request("goods_amount");
    var o2o_order_type=request("o2o_order_type");

    if (goods_id != null && goods_id != "") {
        goods_id = "&goods_id=" + goods_id;
    } else {
        goods_id = "";
    }

    if (store_id != null && store_id != "") {
        store_id = "&store_id=" + store_id;
    } else {
        store_id = "";
    }

    if (goods_amount != null && goods_amount != "") {
        goods_amount = "&goods_amount=" + goods_amount;
    } else {
        goods_amount = "";
    }


    $(".btn-commit").click(function(){
        var trans_pwd=$("#trans-pwd").val();

        if(trans_pwd==""){
            $(".messageTips").text("密码不能为空");
        }else{
            //验证交易密码
            $.ajax({
                url:ApiUrl+"/index.php?act=member_buy&op=check_password&client_type=wap&key="+getcookie("key")+"&password="+trans_pwd,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success: function(data){
                    if(data.code==200){
                        //alert("交易密码验证通过");

                        //使用积分支付下单
                        $.ajax({
                            url: ApiUrl + "/index.php?act=member_buy&op=local_buy&client_type=wap&key=" + getcookie("key") + "&money=" + money + "&pd_pay=1&paypwd="+trans_pwd + store_id + goods_id + goods_amount+"&o2o_order_type="+o2o_order_type,
                            type: "get",
                            dataType: "jsonp",
                            callback: "callback",
                            success: function (result) {
                                if(result.code == 200 && result.data.order_state == 10){
                                    //未付款
                                    $(".messageTips").text("积分支付成功，您还需支付"+result.data.need_pay+"元");
                                    alert("积分支付成功，您还需支付"+result.data.need_pay+"元");

                                    var showMoney = result.data.need_pay;
                                    var invitation = result.data.invitation;
                                    window.location.href = WapSiteUrl + "/localPay.html?pay_sn="+result.data.pay_sn+"&money=" + showMoney + "&invitation=" + invitation + goods_id + store_id + goods_amount+"&o2o_order_type="+o2o_order_type;

                                }else if(result.code == 200 && (result.data.order_state == 20 || result.data.order_state == 40)){
                                    //已付款
                                    $(".messageTips").text("积分支付成功");
                                    alert("积分支付成功");
                                    window.location.href=WapSiteUrl+"/preSale/local_buy_success.html?pay_sn="+result.data.pay_sn;

                                }
                            }
                        });

                    }else if(data.code==80001){
                        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                    }else if(data.code==80002){
                        $(".messageTips").text(data.message);
                    }
                }
            });

        }
    });

    $(".sheader span").click(function(){
        window.location.href=WapSiteUrl+"/trans_pwd_update.html";
    })

});

