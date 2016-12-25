//获取url参数
function request(paras) {
    var url = location.href;
    url = decodeURI(url);
    var paraString = url.substring(url.indexOf("?") + 1, url.length).split("&");
    var paraObj = {};
    for (var i = 0; j = paraString[i]; i++) {
        paraObj[j.substring(0, j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=") + 1, j.length);
    }
    var returnValue = paraObj[paras.toLowerCase()];
    if (typeof(returnValue) == "undefined") {
        return "";
    } else {
        return returnValue;
    }
}


// JavaScript Document
$(window).load(function (e) {

    $(".payw_box").css("margin-top", ($(window).height() - $(".payw_box").height()) / 2);

});

$(document).ready(function () {
    var ua = navigator.userAgent.toLowerCase();

    var browser = {
        versions : function() {
            var u = navigator.userAgent, app = navigator.appVersion;
            return {
                trident : u.indexOf('Trident') > -1,
                presto : u.indexOf('Presto') > -1,
                webKit : u.indexOf('AppleWebKit') > -1,
                gecko : u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1,
                mobile : !!u.match(/AppleWebKit.*Mobile.*/)
                || !!u.match(/AppleWebKit/),
                ios : !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/),
                android : u.indexOf('Android') > -1 || u.indexOf('Linux') > -1,
                iPhone : u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1,
                iPad : u.indexOf('iPad') > -1,
                webApp : u.indexOf('Safari') == -1
            };
        }(),
        language : (navigator.browserLanguage || navigator.language).toLowerCase()
    };

    var lmy_storename;
    var lmy_img;
    var lmy_goods_num;
    var lmy_price;
    var lmy_goods_total;
    var total = 0;
    var three_address_id;
    var con_freight_hash;
    var last_city_id;
    var last_area_id;
    var offpay_hash;
    var offpay_hash_batch;
    var vat_hash;
    var agg_cart_id;
    var agg_num;
    var agg;
    var keyy = getcookie('key');
    var type = getcookie("type");
    var doc_h = $(window).height();
    var lmy_string = request("lmy_string");//获取从购物车过来的商品id
    var goods_id = request("gc_id");//获取从商品详情直接购买的id
    var cart_goods_idd = request("goods_id");
    var cart_goods_id = cart_goods_idd.substr(0, cart_goods_idd.length - 1).split(",");
    var ken_area_id;
    var ken_city_id;
    var ken_freight_hash;
    var flag = 0;
    var gobal;
    $("#zf").css("top", doc_h / 2 - 100);
    if (keyy == '') {
        window.location.href = WapSiteUrl + "/tmpl/member/login.html";
    } else {
		var dis_store_id=request("dis_store_id");
		var dis_member_id=request("dis_member_id");
        if (lmy_string == '' && goods_id != '') {
            gobal = 0;
            /*获取商品的信息*/
            $.ajax({
                url: ApiUrl + "/index.php?act=member_buy&op=buy_step1&cart_id=" + goods_id + "|1&key=" + keyy + "&client_type=wap",
                type: "get",
                dataType: "jsonp",
                jsonp: "callback",
                success: function (data) {
                    if (data.code == 200) {
                        if (type == 'ios' || type == 'iOS' || type == 'android') {
                            $("#way").html("支付宝支付");
                        }
                        for (var key in data.data.store_cart_list) {
                            var dan = data.data.store_cart_list[key].goods_list;
                            $(dan).each(function (k, v) {
                                lmy_img = v.goods_image_url;
                                lmy_storename = v.store_name;
                                lmy_goods_num = v.goods_num;
                                lmy_price = v.goods_price;
                                lmy_goods_total = v.goods_total;
                            });
                        }
                        var pdiv = "<section class='orderBox_header'><a>" + lmy_storename + "</a><span>待付款</span></section><section class='order_list'><ul><li><div class='o_list_img'><img src='" + lmy_img + "'/></div><div class='o_list_cs'><p class='order_gName'></p><p class='order_money'>¥" + lmy_price + "<span>×" + lmy_goods_num + "</span></p></div></li></ul></section></section>";
                        $(".lmy_sec").append(pdiv);
                        $(".myO_t_red").html('￥'+parseFloat(lmy_goods_total).toFixed(2));
                        $("#obj").html('￥'+parseFloat(lmy_goods_total).toFixed(2));
                        //显示用户信息
                        if (data.data.address_info.true_name != undefined) {
                            var jpdiv = "<section class='orderAddr'><p class='orderName'><span id='tga'>" + data.data.address_info.true_name + "</span>" + data.data.address_info.mob_phone + "</p><p class='orderAddress'>" + data.data.address_info.area_info + "</p><div class='confOrder_jian'><img src='../images/jiantou_addr.png'/></div></section>";
                            $(".kz").append(jpdiv);
                        } else {
                            alert('您目前还没有默认地址，请先添加地址');
                        }

                        //往4号接口发请求
                        ken_area_id = data.data.address_info.area_id;
                        ken_city_id = data.data.address_info.city_id;
                        con_freight_hash = data.data.freight_hash;
                        three_address_id = data.data.address_info.address_id;//a.未改地址时：来自3号接口中的地址信息
                        vat_hash = data.data.vat_hash;

                        if (flag == 0) {
                            if(ken_area_id == 0 || ken_city_id == 0){
                                alert('地址信息错误，请重新编辑地址');
                                $('#main-container').hide();
                                $(".main").show();
                                flag = 1;
                                address_add();
                            }else{
                                $.ajax({
                                    url: ApiUrl + "/index.php?act=member_buy&op=change_address&client_type=wap&key=" + keyy + "&area_id=" + ken_area_id + "&city_id=" + ken_city_id + "&freight_hash=" + con_freight_hash,
                                    type: "get",
                                    dataType: "jsonp",
                                    jsonp: "callback",
                                    success: function (data) {
                                        if (data.code == 200) {
                                            offpay_hash_batch = data.data.offpay_hash_batch;
                                            offpay_hash = data.data.offpay_hash;
                                            $(".orderAddr").click(function () {

                                                //window.location.href="PackageAddr.html";
                                            });
                                            var expenses = 0;

                                            for (var k in data.data.content) {
                                                expenses += data.data.content[k];
                                            }
                                            $("#expenses").html(parseFloat(expenses));
                                            var total_money = parseFloat(expenses) + parseFloat(lmy_goods_total);
                                            $(".myO_t_red").html('￥'+total_money.toFixed(2));
                                            $("#obj").html('￥'+total_money.toFixed(2));
                                            //没有选择地址，是默认的，提交订单
                                            $("#zhifu").click(function () {
                                                if (flag == 0) {
                                                    //生成订单
                                                    $.ajax({
                                                        url: ApiUrl + "/index.php?act=member_buy&op=buy_step2&client_type=wap&key=" + keyy + "&ifcart=0&cart_id=" + goods_id + "|1&address_id=" + three_address_id + "&vat_hash=" + vat_hash + "&offpay_hash=" + offpay_hash + "&offpay_hash_batch=" + offpay_hash_batch + "&pay_name=online&invoice_id=undefined&voucher=&rcb_pay=0&pd_pay=0&dis_store_id="+dis_store_id+"&dis_member_id="+dis_member_id,
                                                        type: "get",
                                                        dataType: "jsonp",
                                                        jsonp: "callback",
                                                        success: function (data) {

                                                            if (data.code == 200) {

                                                                if (type == 'ios' || type == 'iOS' || type == 'android') {
                                                                    window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                    /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                                }else {
                                                                    if(ua.match(/MicroMessenger/i)=="micromessenger") {
                                                                        /*window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=wxpay";*/
                                                                        window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=wxpay";	//微信支付


                                                                    }else{
                                                                        window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                        /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/

                                                                    }

                                                                }
                                                            } else {
                                                                alert(data.message);
                                                            }
                                                            //alert("这是第一个默认的生成订单!");
                                                            //alert(data.data.pay_sn);//获取需要支付的参数
                                                        }
                                                    });
                                                }
                                            });

                                        }
                                    }
                                });
                            }
                        }
                        //切换地址信息
                        $(".orderAddr").click(function () {
                            flag = 1;
                            $(".kz").hide();
                            var address_id = new Array(), true_name = new Array(), area_info = new Array(), address = new Array(), mob_phone = new Array(), is_default = new Array(), tel_phone = new Array(), city_id = new Array(), area_id = new Array();
                            $.ajax({
                                url: ApiUrl + "/index.php?act=member_address&op=address_list&client_type=wap&key=" + keyy,
                                type: "get",
                                dataType: "jsonp",
                                jsonp: "callback",
                                success: function (data) {
                                    if (data.code = 200) {
                                        $(data.data.address_list).each(function (index, jk) {
                                            true_name[index] = jk.true_name;
                                            area_info[index] = jk.area_info;
                                            mob_phone[index] = jk.mob_phone;
                                            tel_phone[index] = jk.tel_phone;
                                            city_id[index] = jk.city_id;
                                            area_id[index] = jk.area_id;
                                            address[index] = jk.address;
                                            address_id[index] = jk.address_id;

                                            var subdiv1 = "<li class='df'><div class='addr_check'></div><div class='addr_box'><p class='addrName'><span id='name'>" + true_name[index] + "</span><span id='mob'>" + mob_phone[index] + "</span></p><p class='addrAddress'>" + area_info[index] + "</p></div><div class='clear_float'></div><span style='display:none' id='tel'>" + tel_phone[index] + "</span><span style='display:none' id='city'>" + city_id[index] + "</span><span style='display:none' id='area'>" + area_id[index] + "</span><span style='display:none' id='address'>" + address[index] + "</span><span style='display:none;' id='add_id'>" + address_id[index] + "</span></li>";
                                            $(".get_location ul").append(subdiv1);
                                            var i=$(".get_location ul li").length;
                                            console.log(i);
                                            if(i>1){
                                                $("#jiantou").show();
                                            }else{
                                                $("#jiantou").hide();
                                            }


                                        });
                                        $(".get_location").css("background", "#fff");
                                        $(".df").click(function () {

                                            $(".df").find(".addr_check").attr("class", "addr_check");
                                            $(this).find(".addr_check").attr("class", "addr_check addr_checkon");

                                            //var choose=$(".df").index(this)+1;
                                            $(".df").hide();
                                            $(this).show();
                                            var city_id = $(this).find("#city").html();
                                            var area_id = $(this).find("#area").html();
                                            var add_id = $(this).find("#add_id").html();
                                            /*这边是调试的*/
                                            $(this).click(function () {
                                                $(".df").toggle();
                                                $(this).toggle();
                                            });
                                            /*这边是调试的*/
                                            //选择收货地址后往4号接口发请求获取运费和需要生成订单的2个参数
                                            if(area_id == 0 || city_id == 0){
                                                alert('地址信息错误，请重新编辑地址');
                                                $('#main-container').hide();
                                                $(".main").show();
                                                flag = 1;
                                                address_add();
                                            }else {
                                                $.ajax({
                                                    url: ApiUrl + "/index.php?act=member_buy&op=change_address&client_type=wap&key=" + keyy + "&area_id=" + area_id + "&city_id=" + city_id + "&freight_hash=" + con_freight_hash,
                                                    type: "get",
                                                    dataType: "jsonp",
                                                    jsonp: "callback",
                                                    success: function (data) {

                                                        if (data.code == 200) {
                                                            var new_offpay_hash = data.data.offpay_hash;
                                                            var new_offpay_hash_batch = data.data.offpay_hash_batch;
                                                            var expenses1 = 0;

                                                            for (var k in data.data.content) {
                                                                expenses1 += data.data.content[k];
                                                            }
                                                            $("#expenses").html(parseFloat(expenses1));
                                                            var total_money = parseFloat(expenses1) + parseFloat(lmy_goods_total);
                                                            $(".myO_t_red").html('￥' + total_money.toFixed(2));
                                                            $("#obj").html('￥' + total_money.toFixed(2));
                                                            $("#zhifu").click(function () {

                                                                //生成订单
                                                                $.ajax({
                                                                    url: ApiUrl + "/index.php?act=member_buy&op=buy_step2&client_type=wap&key=" + keyy + "&ifcart=0&cart_id=" + goods_id + "|1&address_id=" + add_id + "&vat_hash=" + vat_hash + "&offpay_hash=" + new_offpay_hash + "&offpay_hash_batch=" + new_offpay_hash_batch + "&pay_name=online&invoice_id=undefined&voucher=&rcb_pay=0&pd_pay=0&dis_store_id=" + dis_store_id + "&dis_member_id=" + dis_member_id,
                                                                    type: "get",
                                                                    dataType: "jsonp",
                                                                    jsonp: "callback",
                                                                    success: function (data) {
                                                                        if (data.code == 200) {
                                                                            if (type == 'ios' || type == 'iOS' || type == 'android') {
                                                                                window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                                /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                                            } else {
                                                                                if (ua.match(/MicroMessenger/i) == "micromessenger") {
                                                                                    /*window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=wxpay";*/
                                                                                    window.location.href = ApiUrl + "/index.php?act=member_payment&op=pay&key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=wxpay";	//微信支付
                                                                                } else {
                                                                                    window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                                    /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                                                }
                                                                            }
                                                                        } else {
                                                                            /*alert("这是错误信息");*/
                                                                            alert(data.message);
                                                                        }
                                                                    }
                                                                });
                                                            });//支付click结束
                                                        }
                                                    }
                                                });//选择收货地址4号接口结束
                                            }
                                        });//li的点击事件
                                    }
                                }
                            });
                        });
                        //这边是添加之后的
                        $("#add_adress").click(function () {
                            $('#main-container').hide();
                            $(".main").show();
                            flag = 1;
                            address_add();
                        });
                    }else if(data.code==80001){
                        window.location.href = WapSiteUrl + "/tmpl/member/login.html";
                    }
                }
            });


            $("#lmy_close").click(function () {
                $("#zf").hide();
                $("#screen").hide();
            });
            $("#PayWay").click(function () {
                $("#zf").show();
                $("#screen").show();
            });
        } else {
            gobal = 1;
            var flg = 0;
            var arr = new Array();
            agg = lmy_string.substr(0, lmy_string.length - 1);

            for (var i = 0; i < agg.split(",").length; i++) {
                var agg_arr = agg.split(",")[i];//获取到每个组的cart_id和数量
                agg_cart_id = agg_arr.split("|")[0];//这是cart_id
                agg_num = agg_arr.split("|")[1];//这是数量

            }
            /*显示商品信息*/
            var kk_cart_id = new Array(), kk_store_name = new Array(), kk_goods_name = new Array(), kk_goods_price = new Array(), kk_goods_num = new Array(), kk_goods_image_url = new Array(), kk_goods_total = new Array();
            $.ajax({
                url: ApiUrl + "/index.php?act=member_buy&op=buy_step1&client_type=wap&key=" + keyy + "&ifcart=1&cart_id=" + agg,
                type: "get",
                dataType: "jsonp",
                jsonp: "callback",
                success: function (data) {
                    if (data.code == 200) {
                        if (type == 'ios' || type == 'iOS' || type == 'android') {
                            $("#way").html("支付宝支付");
                        }
                        for (var key in data.data.store_cart_list) {
                            var pc = data.data.store_cart_list[key].goods_list;
                            vat_hash = data.data.vat_hash;
                            three_address_id = data.data.address_info.address_id;//a.未改地址时：来自3号接口中的地址信息
                            var lmy_numTotal = 0;

                            $(pc).each(function (index, kk) {
                                kk_store_name[index] = kk.store_name;
                                kk_goods_image_url[index] = kk.goods_image_url;
                                kk_goods_name[index] = kk.goods_name;
                                kk_goods_price[index] = kk.goods_price;
                                kk_goods_num[index] = kk.goods_num;


                                lmy_numTotal = lmy_numTotal + parseInt(kk.goods_num);

                                var oop = "<section class='orderBox_header'><a>" + kk_store_name[index] + "</a><span>待付款</span></section><section class='order_list'><ul><li><div class='o_list_img'><img src='" + kk_goods_image_url[index] + "'/></div><div class='o_list_cs'><p class='order_gName'>" + kk_goods_name[index] + "</p><p class='order_money'>¥" + kk_goods_price[index] + "<span>×" + kk_goods_num[index] + "</span></p></div></li></ul></section></section>";
                                $(".lmy_sec").append(oop);
                            });

                            var cc = parseFloat(data.data.store_cart_list[key].store_goods_total);
                            total += cc;

                            $(".myO_t_red").html('￥'+parseFloat(total).toFixed(2));
                            $("#obj").html('￥'+total.toFixed(2));
                            $(".myO_t_jian").html(lmy_numTotal);
                        }

                        //显示地址信息
                        var con_area_id = data.data.address_info.address_id;
                        var con_city_id = data.data.address_info.city_id;
                        con_freight_hash = data.data.freight_hash;
                        if (data.data.address_info.true_name != undefined ){
                            var jpdiv = "<section class='orderAddr'><p class='orderName'><span>" + data.data.address_info.true_name + "</span>" + data.data.address_info.mob_phone + "</p><p class='orderAddress'>" + data.data.address_info.area_info + " "+data.data.address_info.address+"</p><div class='confOrder_jian'><img src='../images/jiantou_addr.png'/></div></section>";
                            $(".kz").append(jpdiv);
                        } else if ( con_city_id == 0) {
                            alert('地址信息错误，请重新编辑地址');
                            $('#main-container').hide();
                            $(".main").show();
                            flag = 1;
                            address_add();
                        }else {
                            alert('您目前还没有默认地址，请先添加地址');
                            $('#main-container').hide();
                            $(".main").show();
                            flag = 1;
                            address_add();
                        }

                        //往4号接口发请求

                        if (flg == 0) {
                            $.ajax({
                                url: ApiUrl + "/index.php?act=member_buy&op=change_address&client_type=wap&key=" + keyy + "&area_id=" + con_area_id + "&city_id=" + con_city_id + "&freight_hash=" + con_freight_hash,
                                type: "get",
                                dataType: "jsonp",
                                jsonp: "callback",
                                success: function (data) {
                                    if (data.code == 200) {
                                        offpay_hash_batch = data.data.offpay_hash_batch;
                                        offpay_hash = data.data.offpay_hash;
                                        var expenses2 = 0;

                                        for (var k in data.data.content) {
                                            expenses2 += data.data.content[k];
                                        }
                                        $("#expenses").html(parseFloat(expenses2));
                                        var total_money = parseFloat(expenses2) + parseFloat(total);
                                        $(".myO_t_red").html('￥'+total_money.toFixed(2));
                                        $("#obj").html('￥'+total_money.toFixed(2));
                                        $(".orderAddr").click(function () {

                                            //window.location.href="PackageAddr.html";
                                        });
                                        //没有选择地址，是默认的，提交订单
                                        $("#zhifu").click(function () {
                                            if (flg == 0) {
                                                //生成订单
                                                $.ajax({
                                                    url: ApiUrl + "/index.php?act=member_buy&op=buy_step2&client_type=wap&key=" + keyy + "&ifcart=1&cart_id=" + agg + "&address_id=" + three_address_id + "&vat_hash=" + vat_hash + "&offpay_hash=" + offpay_hash + "&offpay_hash_batch=" + offpay_hash_batch + "&pay_name=online&invoice_id=undefined&voucher=&rcb_pay=0&pd_pay=0",
                                                    type: "get",
                                                    dataType: "jsonp",
                                                    jsonp: "callback",
                                                    success: function (data) {
                                                        if (data.code == 200) {
                                                            if (type == 'ios' || type == 'iOS' || type == 'android') {
                                                                window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                            } else {
                                                                if(ua.match(/MicroMessenger/i)=="micromessenger") {
                                                                    /*window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=wxpay";*/
                                                                    window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=wxpay";	//微信支付
                                                                }else{
                                                                    window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                    /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                                }
                                                            }
                                                        } else {
                                                            alert(data.message);
                                                        }
                                                    }
                                                });
                                            }
                                        });
                                    }
                                }
                            });
                        }
                        //切换地址信息
                        $(".orderAddr").click(function () {
                            flg = 1;
                            $(".kz").hide();
                            var address_id = new Array(), true_name = new Array(), area_info = new Array(), address = new Array(), mob_phone = new Array(), is_default = new Array(), tel_phone = new Array(), city_id = new Array(), area_id = new Array();
                            $.ajax({
                                url: ApiUrl + "/index.php?act=member_address&op=address_list&client_type=wap&key=" + keyy,
                                type: "get",
                                dataType: "jsonp",
                                jsonp: "callback",
                                success: function (data) {
                                    if (data.code = 200) {
                                        $(data.data.address_list).each(function (index, jk) {
                                            true_name[index] = jk.true_name;
                                            area_info[index] = jk.area_info;
                                            mob_phone[index] = jk.mob_phone;
                                            tel_phone[index] = jk.tel_phone;
                                            city_id[index] = jk.city_id;
                                            area_id[index] = jk.area_id;
                                            address[index] = jk.address;
                                            address_id[index] = jk.address_id;
                                            var subdiv1 = "<li class='df'><div class='addr_check'></div><div class='addr_box'><p class='addrName'><span id='name'>" + true_name[index] + "</span><span id='mob'>" + mob_phone[index] + "</span></p><p class='addrAddress'>" + area_info[index] + "&nbsp;<span id='address'>" + address[index] + "</span></p></div><div class='clear_float'></div><span style='display:none' id='tel'>" + tel_phone[index] + "</span><span style='display:none' id='city'>" + city_id[index] + "</span><span style='display:none' id='area'>" + area_id[index] + "</span><span style='display:none;' id='add_id'>" + address_id[index] + "</span></li>";
                                            $(".get_location ul").append(subdiv1);
                                        });
                                        $(".get_location").css("background", "#fff");
                                        $(".df").click(function () {

                                            $(".df").find(".addr_check").attr("class", "addr_check");
                                            $(this).find(".addr_check").attr("class", "addr_check addr_checkon");

                                            //var choose=$(".df").index(this)+1;
                                            $(".df").hide();
                                            $(this).show();
                                            var city_id = $(this).find("#city").html();
                                            var area_id = $(this).find("#area").html();
                                            var add_id = $(this).find("#add_id").html();
                                            /*这边是调试的*/
                                            $(this).click(function () {
                                                $(".df").toggle();
                                                $(this).toggle();
                                            });
                                            /*这边是调试的*/
                                            //选择收货地址后往4号接口发请求获取运费和需要生成订单的2个参数
                                            $.ajax({
                                                url: ApiUrl + "/index.php?act=member_buy&op=change_address&client_type=wap&key=" + keyy + "&area_id=" + area_id + "&city_id=" + city_id + "&freight_hash=" + con_freight_hash,
                                                type: "get",
                                                dataType: "jsonp",
                                                jsonp: "callback",
                                                success: function (data) {
                                                    if (data.code == 200) {
                                                        var new_offpay_hash = data.data.offpay_hash;
                                                        var new_offpay_hash_batch = data.data.offpay_hash_batch;
                                                        var expenses3 = 0;
                                                        for (var k in data.data.content) {
                                                            expenses3 += data.data.content[k];
                                                        }
                                                        $("#expenses").html(parseFloat(expenses3));
                                                        var total_money = parseFloat(expenses3) + parseFloat(total);
                                                        $(".myO_t_red").html('￥'+total_money.toFixed(2));
                                                        $("#obj").html('￥'+total_money.toFixed(2));
                                                        $("#zhifu").click(function () {

                                                            //生成订单
                                                            $.ajax({
                                                                url: ApiUrl + "/index.php?act=member_buy&op=buy_step2&client_type=wap&key=" + keyy + "&ifcart=1&cart_id=" + agg + "&address_id=" + add_id + "&vat_hash=" + vat_hash + "&offpay_hash=" + new_offpay_hash + "&offpay_hash_batch=" + new_offpay_hash_batch + "&pay_name=online&invoice_id=undefined&voucher=&rcb_pay=0&pd_pay=0",
                                                                type: "get",
                                                                dataType: "jsonp",
                                                                jsonp: "callback",
                                                                success: function (data) {
                                                                    if (data.code == 200) {
                                                                        if (type == 'ios' || type == 'iOS' || type == 'android') {
                                                                            window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                            /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                                        } else {
                                                                            if(ua.match(/MicroMessenger/i)=="micromessenger") {
                                                                                /*window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=wxpay";*/
                                                                                window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=wxpay";	//微信支付
                                                                            }else{
                                                                                window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                                /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                                            }
                                                                        }
                                                                    } else {
                                                                        alert(data.message);
                                                                    }
                                                                }
                                                            });
                                                        });
                                                    } else {
                                                        alert(data.message);
                                                    }
                                                }
                                            });
                                        });
                                    }
                                }
                            });


                        });//切换地址结束

                    }else if(data.code==80001){
                        window.location.href = WapSiteUrl + "/tmpl/member/login.html";
                    }
                }
            });
            $("#add_adress").click(function () {
                $('#main-container').hide();
                $(".main").show();
                flg = 1;
                address_add();
            });


        }
    }

    /*获取省的接口*/
    function address_add() {
        var address;
        var name;
        var mob;
        var area_info;
        var provincesName; // 省
        var provincesId;
        var cityName;            // 市
        var cityId;
        var areaName;           // 区
        var areaId;
        var get_area=[];
        var get_area_id = [];

        function isPhone() {
            mob = $('.input_mob').val();
            var reg = /^0?1[3|4|5|7|8][0-9]\d{8}$/;
            if(mob===''){
                $('.message').text("请输入收货人手机号码~");
                return false;
            }else if (!reg.test(mob)) {
                $('.message').text("请输入正确的手机号码~");
                return false;
            }else  {
                $('.message').text("");
                return true;
            };
        }

        function isName(){
            name=$(".input_name").val();
            if(name===''){
                $('.message').text("收货人姓名不能为空~");
                return false;
            }else{
                $('.message').text("");
                return true;
            };
        }

        function isAddress(){
            address=$('#details').val();
            if(address===''){
                $('.message').text("请输入详细地址~");
                return false;
            }else{
                $('.message').text("");
                return true;
            };
        }

        function isArea(){
            if(area_info=='undefined undefined undefined'){
                $('.message').text("请选择所在省市~");
                return false;
            }else{
                $('.message').text("");
                return true;
            };
        }

        function getAreaId(sel,area_id){
            $.ajax({
                url: ApiUrl + "/index.php?act=member_address&op=area_list&key=" + key + "&client_type=wap&area_id=" + area_id,
                type: "get",
                dataType: "jsonp",
                jsonp: "callback",
                success: function (data) {
                    if(data.code == 200){
                        if(sel ==1){
                            $(data.data.area_list).each(function (index, list) {
                                get_area[index] = list.area_name;
                                get_area_id[index] = list.area_id;
                                var objdiv = "<option value='" + get_area_id[index] + "'>" + get_area[index] + "</option>";
                                $(".sel1").append(objdiv);
                            });
                            provincesId = $(".sel1").val();
                        }else if(sel ==2){
                            $(data.data.area_list).each(function (index, list) {
                                get_area[index] = list.area_name;
                                get_area_id[index] = list.area_id;
                                var objdiv = "<option value='" + get_area_id[index] + "'>" + get_area[index] + "</option>";
                                $(".sel2").append(objdiv);
                            });
                            cityId = $(".sel2").val();
                            cityName =$(".sel2").find("option:selected").text();
                            getAreaId(3,cityId);
                        }else if(sel == 3){
                            $(data.data.area_list).each(function (index, list) {
                                get_area[index] = list.area_name;
                                get_area_id[index] = list.area_id;
                                var objdiv = "<option value='" + get_area_id[index] + "'>" + get_area[index] + "</option>";
                                $(".sel3").append(objdiv);
                            });
                            areaId = $(".sel3").val();
                            areaName =$(".sel3").find("option:selected").text();
                        }
                    }
                }
            });
        }

        if (keyy == '') {
            window.location.href = WapSiteUrl + "/tmpl/member/login.html";
        } else {
            if (flag == 1 || flg == 1) {
                getAreaId(1,0);
                $(".sel1").change(function () {
                    provincesId = $(".sel1").val();
                    provincesName =$(".sel1").find("option:selected").text();
                    $(".sel2 option").remove();
                    $(".sel3 option").remove();
                    getAreaId(2,provincesId);

                });
                $(".sel2").change(function () {
                    $(".sel3 option").remove();
                    cityId = $(".sel2").val();
                    cityName =$(".sel2").find("option:selected").text();
                    getAreaId(3,cityId);
                });
                $(".sel3").change(function () {
                    areaId = $(".sel3").val();
                    areaName =$(".sel3").find("option:selected").text();
                });
                //保存收货地址
                $(".ok").click(function () {
                    address = $("#details").val();
                    name = $(".input_name").val();
                    mob = $(".input_mob").val();
                    area_info = provincesName + " " + cityName +" "+areaName;
                    if(isPhone()&&isName()&&isAddress()&&isArea()){
                        $.ajax({
                            url: ApiUrl + "/index.php?act=member_address&op=address_add&client_type=wap&key=" + keyy + "&true_name=" + name + "&mob_phone=" + mob + "&tel_phone=" + mob +"&address=" + address + "&area_info=" + area_info+ "&city_id=" + cityId + "&area_id=" + areaId+ "&is_default=" + 1,
                            type: "get",
                            dataType: "jsonp",
                            jsonp: "callback",
                            success: function (data) {
                                var bao_address_id = data.data.address_id;
                                window.location.reload();
                                //添加收货地址后重新发送4号接口获取运费和生成当单的两个参数
                                $.ajax({
                                    url: ApiUrl + "/index.php?act=member_buy&op=change_address&client_type=wap&key=" + keyy + "&area_id=" + areaId + "&city_id=" + cityId + "&freight_hash=" + con_freight_hash,
                                    type: "get",
                                    dataType: "jsonp",
                                    jsonp: "callback",
                                    success: function (data) {

                                        if (data.code == 200) {
                                            var new1_offpay_hash = data.data.offpay_hash;
                                            var new1_offpay_hash_batch = data.data.offpay_hash_batch;
                                            //b.新增地址时：来自6号接口保存成功是返回的信息
                                            var expenses4 = 0;
                                            for (var k in data.data.content) {
                                                expenses4 += data.data.content[k];
                                            }
                                            $("#expenses").html(parseFloat(expenses4));
                                            $("#zhifu").click(function () {
                                                if (gobal == 0) {
                                                    cd = goods_id;

                                                } else if (gobal == 1) {
                                                    cd = lmy_string.substr(0, lmy_string.length - 1);
                                                }
                                                //生成订单
                                                $.ajax({
                                                    url: ApiUrl + "/index.php?act=member_buy&op=buy_step2&client_type=wap&key=" + keyy + "&ifcart=1&cart_id=" + cd + "&address_id=" + bao_address_id + "&vat_hash=" + vat_hash + "&offpay_hash=" + new1_offpay_hash + "&offpay_hash_batch=" + new1_offpay_hash_batch + "&pay_name=online&invoice_id=undefined&voucher=&rcb_pay=0&pd_pay=0",
                                                    type: "get",
                                                    dataType: "jsonp",
                                                    jsonp: "callback",
                                                    success: function (data) {
                                                        if (data.code == 200) {
                                                            if (type == 'ios' || type == 'iOS' || type == 'android') {
                                                                window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                            } else {
                                                                if(ua.match(/MicroMessenger/i)=="micromessenger") {
                                                                    /*window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=wxpay";*/
                                                                    window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=wxpay";	//微信支付
                                                                }else{
                                                                    window.location.href = "pay.html?key=" + keyy + "&pay_sn=" + data.data.pay_sn + "&payment_code=alipay";
                                                                    /*window.location.href=ApiUrl+"/index.php?act=member_payment&op=pay&key="+keyy+"&pay_sn="+data.data.pay_sn+"&payment_code=alipay";//支付宝支付*/
                                                                }
                                                            }
                                                        } else {
                                                            alert(data.message);
                                                        }
                                                    }
                                                });
                                            });
                                        } else {
                                            alert(data.message);
                                        }
                                    }
                                });
                            }
                        });
                    }else{
                        if(!isName()){
                            isName();
                        }else if(!isPhone()){
                            isPhone();
                        }else if(!isArea()){
                            isArea();
                        }else if(!isAddress()){
                            isAddress();
                        }
                    }
                });//保存收货地址结束
            }
        }
    }

});
 