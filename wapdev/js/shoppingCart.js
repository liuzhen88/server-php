$(function () {
    if (type == 'iOS' || type == 'android') {
        $('.my_goodsCartQJS').css('bottom','0');
        $('#footer').hide();
    }
    //JS浮点数计算方法
    var JSFloat = {
        accAdd: function (arg1, arg2) {
            /**
             ** 加法函数，用来得到精确的加法结果
             ** 说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
             ** 调用：accAdd(arg1,arg2)
             ** 返回值：arg1加上arg2的精确结果
             **/
            var r1, r2, m, c;
            try {
                r1 = arg1.toString().split(".")[1].length;
            }
            catch (e) {
                r1 = 0;
            }
            try {
                r2 = arg2.toString().split(".")[1].length;
            }
            catch (e) {
                r2 = 0;
            }
            c = Math.abs(r1 - r2);
            m = Math.pow(10, Math.max(r1, r2));
            if (c > 0) {
                var cm = Math.pow(10, c);
                if (r1 > r2) {
                    arg1 = Number(arg1.toString().replace(".", ""));
                    arg2 = Number(arg2.toString().replace(".", "")) * cm;
                } else {
                    arg1 = Number(arg1.toString().replace(".", "")) * cm;
                    arg2 = Number(arg2.toString().replace(".", ""));
                }
            } else {
                arg1 = Number(arg1.toString().replace(".", ""));
                arg2 = Number(arg2.toString().replace(".", ""));
            }
            return (arg1 + arg2) / m;

            //给Number类型增加一个add方法，调用起来更加方便。
            //            Number.prototype.add = function (arg) {
            //                return accAdd(arg, this);
            //            };
        },

        /**
         ** 乘法函数，用来得到精确的乘法结果
         ** 说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。
         ** 调用：accMul(arg1,arg2)
         ** 返回值：arg1乘以 arg2的精确结果
         **/
        accMul: function (arg1, arg2) {
            var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
            try {
                m += s1.split(".")[1].length;
            }
            catch (e) {
            }
            try {
                m += s2.split(".")[1].length;
            }
            catch (e) {
            }
            return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
        }

        // 给Number类型增加一个mul方法，调用起来更加方便。
        //    Number.prototype.mul = function (arg) {
        //        return accMul(arg, this);
        //    };
    };

    //计算选中商品个数和合计金额
    function my_calculater() {
        var totalNumber = 0;
        var totalMoney = 0;
        $(".my_perGoods_li").each(function (index, element) {
            if ($(this).find(".my_perGoods_l img").attr("src") == "../images/cart_checkon.png") {
                var perNumber = parseInt($(this).find(".my_cgNumM").text());
                var price = parseFloat($(this).find(".my_cartGoodsMoney span").text());
                totalNumber = totalNumber + parseInt($(this).find(".my_cgNumM").text());
                totalMoney = JSFloat.accAdd(totalMoney,JSFloat.accMul(price,perNumber));
            }
        });
        $(".my_QJS span").text(totalNumber);
        $(".my_QJS_HJ .all-price").text(totalMoney.toFixed(2));
    }


    /*var goods_idd=new Array();
     var ggId=0;*/
    var store_id = new Array(),store_list=new Array(),store_name = new Array(), goods_id = new Array(), goods_name = new Array(), goods_price = new Array(), goods_image_url = new Array(), goods_price = new Array(), cart_id = new Array();
    var key = getcookie('key');
    if (key == "" || key == null) {

        window.location.href = WapSiteUrl + "/tmpl/member/login.html";
    } else {
        $.ajax({
            url: ApiUrl + "/index.php?act=member_cart&op=cart_list&key=" + key + "&client_type=wap",
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if (data.code == 200) {
                    $(data.data.cart_list).each(function (kk, vv) {
                        store_name[kk]=vv.store_name;
                    })
                    function unique(arr) {
                        var result = [], hash = {};
                        for (var i = 0, elem; (elem = arr[i]) != null; i++) {
                            if (!hash[elem]) {
                                result.push(elem);
                                hash[elem] = true;
                            }
                        }
                        return result;
                    }
                    store_list=unique(store_name);
                    for(var i=0;i<store_list.length;i++){
                        $(data.data.cart_list).each(function(kk,vv){
                            if(data.data.cart_list[kk].store_name == store_list[i]){
                                store_id[i]=vv.store_id;
                            }
                        })
                        var shopname ="<section class='my_perShop'>"
                                    +   "<section class='my_sc_head'>"
                                    +       "<img src='../images/cart_check.png' />" + store_list[i]
                                    +   "</section>"
                                    +   "<section class='my_perGoods'>"
                                    +   "<ul id='"+store_id[i]+"'>"
                                    +   "</ul></section>"
                                    +"</section>";
                        $(".goodsCart_box").append(shopname);
                        $(data.data.cart_list).each(function(kk,vv){
                            if(data.data.cart_list[kk].store_name == store_list[i]){
                                goods_image_url[kk] = vv.goods_image_url;
                                goods_name[kk] = vv.goods_name;
                                goods_price[kk] = vv.goods_price;
                                cart_id[kk] = vv.cart_id;
                                goods_id[kk] = vv.goods_id;
                                var shopl ="<li class='my_perGoods_li'>"
                                    +           "<div class='my_perGoods_l'><img src='../images/cart_check.png'/></div><div class='my_perGoods_m'><img src='" + goods_image_url[kk] + "'/></div>"
                                    +           "<div class='my_perGoods_r'>"
                                    +               "<div class='my_cartGoodsName'>" + goods_name[kk] + "</div>"
                                    +               "<div class='my_cartGoodsMoney'>&#165;<span>" + goods_price[kk] + "</span></div>"
                                    +               "<div class='my_cgNumBox'>"
                                    +                   "<div class='my_cgNumL'>-</div>"
                                    +                   "<div class='my_cgNumM'>1</div>"
                                    +                   "<div class='my_cgNumR'>+</div>"
                                    +                   "<div class='my_cgDelete'><span style='display:none;'>" + cart_id[kk] + "</span></div>"
                                    +               "</div>"
                                    +           "</div>"
                                    +           "<div class='clear_float'></div>"
                                    +           "<span class='qumo' style='display:none;'>" + goods_id[kk] + "</span>"
                                    +       "</li>";
                                $("ul#"+store_id[i]).append(shopl);
                            }
                        })
                    }
                    $(".my_cgNumR").click(function () {
                        var card = $(this).siblings(".my_cgDelete").find("span").text();
                        var perGNumObj = $(this).parents(".my_cgNumBox").find(".my_cgNumM");
                        var perGNumPlus = parseInt(perGNumObj.text()) + 1;
                        perGNumObj.text(perGNumPlus);

                        my_calculater();
                        var lz_num = $(this).siblings(".my_cgNumM").text();

                        $.ajax({
                            url: ApiUrl + "/index.php?act=member_cart&op=cart_edit_quantity&cart_id=" + card + "&key=" + key + "&quantity=" + lz_num + "&client_type=wap",
                            type: "get",
                            dataType: "jsonp",
                            jsonp: "callback",
                            success: function (data) {

                            }
                        });

                    });


                    $(".my_cgNumL").click(function () {
                        var card_idd = $(this).siblings(".my_cgDelete").find("span").text();
                        var perGNumObj = $(this).parents(".my_cgNumBox").find(".my_cgNumM");
                        if (parseInt(perGNumObj.text()) > 1) {
                            var perGNumPlus = parseInt(perGNumObj.text()) - 1;
                        }
                        perGNumObj.text(perGNumPlus);

                        my_calculater();
                        var lz_nu = $(this).siblings(".my_cgNumM").html();

                        $.ajax({
                            url: ApiUrl + "/index.php?act=member_cart&op=cart_edit_quantity&cart_id=" + card_idd + "&key=" + key + "&quantity=" + lz_nu + "&client_type=wap",
                            type: "get",
                            dataType: "jsonp",
                            jsonp: "callback",
                            success: function (data) {

                            }
                        });
                    });


                    $(".my_perGoods_l img").click(function () {
                        if ($(this).attr("src") == "../images/cart_check.png") {
                            $(this).attr("src", "../images/cart_checkon.png");
                            $(this).parents(".my_perGoods").siblings(".my_sc_head").find('img').attr("src", "../images/cart_checkon.png");
                        } else {
                            $(this).attr("src", "../images/cart_check.png");
                            $(this).parents(".my_perGoods").siblings(".my_sc_head").find('img').attr("src", "../images/cart_check.png");
                        }

                        my_calculater();
                    });


                    $(".my_sc_head img").click(function () {
                        if ($(this).attr("src") == "../images/cart_check.png") {
                            $(this).attr("src", "../images/cart_checkon.png");
                            $(this).parents(".my_perShop").find(".my_perGoods_l img").attr("src", "../images/cart_checkon.png");
                        } else {
                            $(this).attr("src", "../images/cart_check.png");
                            $(this).parents(".my_perShop").find(".my_perGoods_l img").attr("src", "../images/cart_check.png");
                        }

                        my_calculater();
                    });


                    $(".my_QJS_QX img").click(function () {
                        if ($(this).attr("src") == "../images/cart_check.png") {
                            $(this).attr("src", "../images/cart_checkon.png");
                            $(".my_perGoods_l img").attr("src", "../images/cart_checkon.png");
                            $(".my_sc_head img").attr("src", "../images/cart_checkon.png");
                        } else {
                            $(this).attr("src", "../images/cart_check.png");
                            $(".my_perGoods_l img").attr("src", "../images/cart_check.png");
                            $(".my_sc_head img").attr("src", "../images/cart_check.png");
                        }

                        my_calculater();
                    });

                    $(".my_QJS").click(function () {
                        var lmy_card;
                        var lmy_string = "";
                        var goods_idd = "";
                        $(".my_perGoods_li").each(function () {
                            if ($(this).find(".my_perGoods_l img").attr("src") == "../images/cart_checkon.png") {
                                /*goods_idd[ggId]=$(this).find(".qumo").html();
                                 ggId++;*/
                                goods_idd = goods_idd + $(this).find(".qumo").html() + ",";

                                lmy_card = $(this).find(".my_cgDelete span").html();
                                lmy_string = lmy_string + lmy_card + "|" + $(this).find(".my_cgNumM").html() + ",";
                            }
                        });
						if(lmy_string==''){
							alert("所选商品不能为空!");
						}else{
							var lmy_zhu = lmy_string.substr(0, lmy_string.length - 1);
							$.ajax({
								url: ApiUrl + "/index.php?act=member_buy&op=buy_step1&ifcart=1&cart_id=" + lmy_zhu + "&key=" + key + "&client_type=wap",
								type: "get",
								dataType: "jsonp",
								jsonp: "callback",
								success: function (data) {
									window.location.href = WapSiteUrl + "/tmpl/confirmOrder.html?goods_id=" + goods_idd + "&lmy_string=" + lmy_string;
								}
							});
						}

                    });



                    $(".my_cgDelete").click(function () {
                        var cart = $(this).find("span").html();
                        var this_obj = this;
                        $.ajax({
                            url: ApiUrl + "/index.php?act=member_cart&op=cart_del&cart_id=" + cart + "&key=" + key + "&client_type=wap",
                            type: "get",
                            dataType: "jsonp",
                            jsonp: "callback",
                            success: function (data) {
                                if($(this_obj).parents("li").siblings().length==0){
                                    $(this_obj).parents(".my_perShop").remove();
                                }else{
                                    $(this_obj).parents("li").remove();
                                }

                                my_calculater();
                            }
                        });
                    });
                }else{
                    window.location.href = WapSiteUrl + "/tmpl/member/login.html?fromRegister=1";
                }
            }
        });

    }

});



