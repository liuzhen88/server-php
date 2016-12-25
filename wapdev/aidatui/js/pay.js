$(function(){
    app.pay = {
        init:function(){
            app.checkLogin();
            FastClick.attach(document.body);
            this.bindEvent();
        },
        pay:function(){
            console.log(ApiUrl+"/index.php?act=member_payment&op=pay&key="+getcookie("key")+"&pay_sn="+request("pay_sn")+"&payment_code=wxpay");
            window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+getcookie("key")+"&pay_sn="+request("pay_sn")+"&payment_code=wxpay";	//微信支付
        },
        bindEvent:function(){
            var self = this;
            $("#payBtn").on("click",function(){
                self.pay();
            });
            $("#allMoney").text(request("all_money"));
            $(".order").text(request("order_sn"));
        }
    };
    app.pay.init();
});