$(function () {
    FastClick.attach(document.body);

    var goods_id = request("goods_id");//商品id string类型
    var store_id = request("store_id");//店铺id string类型
    var money = request("money");//单价
    var invitation = request("invitation");//邀请码
    var o2o_order_type=request("o2o_order_type");//本土订单类别
    var maxNum;

    if (goods_id != null && goods_id != "") {
        $(".num-box,.proConBox").css("display", "block");

        goods_id = "&goods_id=" + goods_id;
    } else {
        goods_id = "";
    }

    if (store_id != null && store_id != "") {
        $(".num-box,.proConBox").css("display", "none");
        store_id = "&store_id=" + store_id;
    } else {
        store_id = "";
    }

    var jifenFlag = 0;//是否使用积分
    var predepoit = 0;//积分
    var discount_sum= 0;//优惠总额
    var goodsCount = 1;//商品数量

    //获取初始数据
    initData();

    //是否使用积分 操作
    $(".jifen-box .float-right").click(function () {
        if ($(this).parents(".jifen-box").find(".on").length > 0) {
            $(this).removeClass("on");
            jifenFlag = 0;
        } else {
            $(this).addClass("on");
            jifenFlag = 1;
        }
        getDiscount(2);//2为点击积分操作

        if(predepoit-0<=0){
            alert("您没有可用积分,无法使用积分支付");
            $(this).removeClass("on");
            //不使用积分支付
            jifenFlag = 0;
        }
    });

    //减少订单数量 操作
    $(".num-left").click(function () {
        if (parseInt($(".num-center").text()) <= 1) {
            alert("数量最小值为1");
        } else {
            $(".num-center").text(parseInt($(".num-center").text()) - 1);
            goodsCount=parseInt($(".num-center").text());
            getDiscount(1);//1为点击数量操作
        }
    });

    //增加订单数量 操作
    $(".num-right").click(function () {
        $(".num-center").text(parseInt($(".num-center").text()) + 1);

        if(parseInt($(".num-center").text()) > maxNum){
            alert("数量值超过最大库存量");
            $(".num-center").text(parseInt($(".num-center").text()) - 1);
        }else{
            goodsCount=parseInt($(".num-center").text());
            getDiscount(1);//1为点击数量操作
        }
    });

    //提交订单 操作
    $(".btn-commit").click(function () {
        commitOrder();
    });

    //init 函数
    function initData(){
        $.ajax({
            url: ApiUrl + "/index.php?act=member_order&op=get_discount&client_type=wap&key=" + getcookie("key") +goods_id+store_id+"&total="+money*goodsCount+"&goods_count="+goodsCount+"&token_member_id="+getcookie("user_id"),
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (result) {
                if (result.code == 200) {
                    if (goods_id != null && goods_id != "") {
                        maxNum=result.data.extend_data.goods_info.goods_storage;
                        $(".h-r-imgbox img").attr("src",result.data.extend_data.goods_info.goods_image);
                        $(".h-r-productName").text(result.data.extend_data.goods_info.goods_name);
                        $(".h-r-storeName").text(result.data.extend_data.goods_info.store_name);
                        $(".h-r-sale").text("已售："+result.data.extend_data.goods_info.goods_salenum);

                        //h-r-priceNow h-r-priceOld
                        if(result.data.extend_data.goods_info.goods_promotion_type==0){
                            var nowData=result.data.extend_data.goods_info;
                            if(nowData.goods_price==nowData.goods_marketprice){
                                $(".h-r-priceNow").text("¥"+result.data.extend_data.goods_info.goods_price);
                                $(".h-r-priceOld").text("");
                            }else{
                                $(".h-r-priceNow").text("¥"+result.data.extend_data.goods_info.goods_price);
                                $(".h-r-priceOld").text("¥"+result.data.extend_data.goods_info.goods_marketprice);
                            }
                        }else{
                            var nowData=result.data.extend_data.goods_info;
                            if(nowData.goods_promotion_price==nowData.goods_price){
                                $(".h-r-priceNow").text("¥"+result.data.extend_data.goods_info.goods_promotion_price);
                                $(".h-r-priceOld").text("");
                            }else{
                                $(".h-r-priceNow").text("¥"+result.data.extend_data.goods_info.goods_promotion_price);
                                $(".h-r-priceOld").text("¥"+result.data.extend_data.goods_info.goods_price);
                            }
                        }

                        $(".goods_storage_ajax").text(result.data.extend_data.goods_info.goods_storage);
                    }


                    var totalMoney=money*goodsCount;

                    //积分
                    predepoit=result.data.extend_data.predeposit;
                    $(".text-box span").text(predepoit);
                    //折扣总价
                    discount_sum=result.data.extend_data.discount_sum;

                    //订单金额
                    var money_step1=money*goodsCount;
                    //支付金额
                    var money_step2=money_step1-discount_sum;
                    //实际需支付
                    var money_step3=money_step2;

                    //订单金额
                    $(".pay-money-r").text("¥"+parseFloat(money_step1).toFixed(2));
                    //支付金额
                    $(".pay-money-r2").text("¥"+parseFloat(money_step2).toFixed(2));
                    //实际需支付
                    $(".need-pay-right").text("¥"+parseFloat(money_step3).toFixed(2));

                    var commitorderDoTmpl = doT.template($("#commit-order-tmpl").html());
                    $(".commit-order-box").html(commitorderDoTmpl(result.data));

                } else if (result.code == 80001) {
                    window.location.href = WapSiteUrl + "/tmpl/member/login.html";
                } else {
                    alert(result.message);
                }
            }
        });
    }

    //获取优惠 函数
    function getDiscount(type){//type:1为点击数量操作,2为点击积分操作

        if(type==1){//点击数量操作
            $.ajax({
                url: ApiUrl + "/index.php?act=member_order&op=get_discount&client_type=wap&key=" + getcookie("key") +goods_id+store_id+"&total="+money*goodsCount+"&goods_count="+goodsCount+"&token_member_id="+getcookie("user_id"),
                type: "get",
                dataType: "jsonp",
                jsonp: "callback",
                success: function (result) {
                    if (result.code == 200) {

                        //积分
                        predepoit=result.data.extend_data.predeposit;
                        //折扣总价
                        discount_sum=result.data.extend_data.discount_sum;

                        changeMoney();

                        var commitorderDoTmpl = doT.template($("#commit-order-tmpl").html());
                        $(".commit-order-box").html(commitorderDoTmpl(result.data));

                    } else if (result.code == 80001) {
                        window.location.href = WapSiteUrl + "/tmpl/member/login.html";
                    } else {
                        alert(result.message);
                    }
                }
            });
        }else if(type==2){//点击积分操作

            changeMoney();
        }
    }

    //重新计算价格 函数
    function changeMoney() {

        //订单金额
        var money_step1=money*goodsCount;
        //支付金额
        var money_step2=money_step1-discount_sum;
        //实际需支付
        var money_step3=money_step2;

        if (jifenFlag == 1) {
            if (money_step2 >= predepoit) {
                $(".text-box span").text(0);

                $(".order-jifen .float-right").text("-¥"+parseFloat(predepoit).toFixed(2));

                money_step3 = money_step2 - predepoit;

            } else {
                $(".text-box span").text(parseFloat((predepoit - money_step2).toFixed(2)));

                $(".order-jifen .float-right").text("-¥"+money_step2.toFixed(2));

                money_step3 = 0;
            }
            $(".need-pay-box .need-pay-right").text("¥"+(money_step3).toFixed(2));

        } else {
            $(".need-pay-box .need-pay-right").text("¥"+(money_step2).toFixed(2));

            $(".text-box span").text(predepoit);

            $(".order-jifen .float-right").text("-¥0.00");
        }

        //订单金额
        $(".pay-money-r").text("¥"+parseFloat(money_step1).toFixed(2));
        //支付金额
        $(".pay-money-r2").text("¥"+parseFloat(money_step2).toFixed(2));
        //实际需支付
        $(".need-pay-right").text("¥"+parseFloat(money_step3).toFixed(2));
    }

    //提交订单 函数
    function commitOrder() {
        var payMoney = $(".pay-money-r").text().split("¥")[1];

        var goods_amount = "";
        if (goods_id != "" && store_id == "") {
            goods_amount = "&goods_amount=" + $(".num-center").text();
        }

        if (jifenFlag == 1) {
            //判断是否设置了交易密码
            $.ajax({
                url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+getcookie("key")+"&token_member_id="+getcookie("user_id"),
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success: function(data){
                    if(data.code==200){
                        var paypwd_status=data.data.member_info.paypwd_status;
                        if(paypwd_status==0){
                            alert("请设置交易密码");
                            window.location.href=WapSiteUrl+"/trans_pwd.html";
                        }else{
                            //使用积分支付
                            window.location.href=WapSiteUrl +"/trans_pwd_input.html?money=" + money + store_id + goods_id + goods_amount+"&o2o_order_type="+o2o_order_type;
                        }
                    }
                }
            });

        } else {
            //不使用积分
            $.ajax({
                url: ApiUrl + "/index.php?act=member_buy&op=local_buy&client_type=wap&key=" + getcookie("key") + "&money=" + money + "&pd_pay=0" + store_id + goods_id + goods_amount+"&o2o_order_type="+o2o_order_type+"&token_member_id="+getcookie("user_id"),
                type: "get",
                dataType: "jsonp",
                callback: "callback",
                success: function (result) {
                    if (result.code == 200 && result.data.order_state == 10) {
                        //未付款
                        var goods_num = parseInt($(".num-center").text());
                        var showMoney = $(".need-pay-box .need-pay-right").text().split("¥")[1];
                        window.location.href = WapSiteUrl + "/localPay.html?pay_sn="+result.data.pay_sn+"&money=" + showMoney + "&invitation=" + invitation + goods_id + store_id + goods_amount+"&o2o_order_type="+o2o_order_type;
                    }
                }
            });
        }
    }

});

