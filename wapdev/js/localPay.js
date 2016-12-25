$(function () {

    var wWidth = $(window).width();
    $(".tipLine").width((wWidth - 140) / 2);

    var thisPayNum = 0;

    $(".payBox").click(function () {
        $(".payBox").find(".checkPay").removeClass("checkenPay");
        $(this).find(".checkPay").addClass("checkenPay");
        thisPayNum = $(".payBox").index(this);
    });

    var thisGoodId = request("goods_id");
    var thisStoreId = request("store_id");
    var thisMoney = request("money");
    var thisKey = getcookie("key");
    var thisInvitation = request("invitation");
    var pay_sn = request("pay_sn");
    var order_sn=request("order_sn");
    var flag=request("flag");
    //var invitation=request("invitation");
    var o2o_order_type=request("o2o_order_type");
    if(o2o_order_type==1){
        $(".by-cash-pay").css("display","block");
    }


    if (thisGoodId != null && thisGoodId != "") {
        thisGoodId = "&goods_id=" + thisGoodId;
    } else {
        thisGoodId = "";
    }

    if (thisStoreId != null && thisStoreId != "") {
        thisStoreId = "&store_id=" + thisStoreId;
    } else {
        thisStoreId = "";
    }

    $("#pay_right span").text(thisMoney);

    $(".confirmBtn").click(function () {
        if (thisPayNum == 1) {//面对面付款
            if(flag==1){
                window.location.href = "fast_order.html?price=" + thisMoney + "&order_sn="+order_sn+"&flag=1&invitation=" + thisInvitation + thisGoodId + thisStoreId;
            }else{
                window.location.href = "fast_order.html?price=" + thisMoney + "&invitation=" + thisInvitation + thisGoodId + thisStoreId;
            }
         } else {
            var thisPayCode = "wx_app";
            /*if(thisPayNum==0){
             thisPayCode="ali_app";
             }else */
            if (thisPayNum == 0) {
                thisPayCode = "wx_app";
            }

            /*if(thisPayNum==0){//支付宝付款
             window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+thisKey+"&pay_sn="+paySn+"&payment_code=alipay"+"&token_member_id="+getcookie("user_id");
             }else */
            if (thisPayNum == 0) {//微信付款
                window.location.href = ApiUrl + "/index.php?act=member_payment&op=pay&key=" + thisKey + "&pay_sn=" + pay_sn + "&payment_code=wxpay"+"&token_member_id="+getcookie("user_id");
            }
        }
    });
});
