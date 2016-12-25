$(function(){
    app.index = {
        init:function(){
            FastClick.attach(document.body);
            //this.cartRemove();
            this.getLocation();
        },
        storeId:"",
        storeName:"",
        initData:function(storeId){
            var self = this;
            var key=getcookie("key");
            $.ajax({
                url: ApiUrl + "/index.php?act=home&op=adt_home&client_type=wap&store_id="+storeId+"&key="+key,
                type: "get",
                dataType: "jsonp",
                success: function (data) {
                    if(data.code==200){
                        var listCategoryTmpl = doT.template($("#list-category-tmpl").html());
                        $("#categoryWrap").html(listCategoryTmpl(data.data.class_info));
                        if(data.data.class_info.length == 0){
                            alert(self.storeName + ' 商户已入驻，暂时还未上传商品，敬请期待~')
                        }
                        $(".cart-total-postage span").text("￥"+data.data.adt_carriage);
                        $(".cart-none").text("满"+data.data.adt_free_carriage_leave+"免运送费");
                        $listCategory = $(".list-category ul li");
                        $listCategory.on("click",function(){
                            $listCategory.removeClass("select");
                            $(this).addClass("select");
                            var id = $(this).attr("data-id");
                            var listProductTmpl = doT.template($("#list-product-tmpl").html());
                            $(".product-list-wrap").hide();
                            var list = $(".product-list-wrap");
                            var isAppend = true;
                            for(var i=0;i<list.length;i++){
                                if(list.eq(i).attr("data-iditem")==id){
                                    isAppend = false;
                                    list.eq(i).show();
                                }
                            }
                            if(isAppend){
                                var listItem = '<ul class="product-list-wrap" data-idItem="'+ id +'">' + listProductTmpl(data.data.goods_list[id]) +'</ul>';
                                $("#productWrap").append(listItem);
                            }else{

                            }

                            self.bindEvent(data.data.adt_carriage,data.data.adt_free_carriage_leave);
                        });
                        $listCategory.eq(0).trigger("click");
                    }
                }
            });
        },
        cartAdd:function(goodsID,num,callback){
            var self = this;
            $.ajax({
                url:ApiUrl +  "/index.php?act=member_cart_league&op=cart_add&client_type=wap&goods_id="+goodsID+"&quantity="+num+"&key="+getcookie("key")+"&store_id="+self.storeId,
                type: "get",
                dataType: "jsonp",
                success: function (data) {
                    if(data.code==200){
                        if (typeof callback == "function") {
                            callback();
                        }
                    }else if(data.code==80001){
                        alert(data.message);
                        window.location.href = WapSiteUrl + "/aidatui/login.html";
                    }else{
                        alert(data.message);
                    }
                }
            });
        },
        cartRemove:function(){
            $.ajax({
                url:ApiUrl +  "/index.php?act=member_cart_league&op=cart_del&client_type=wap&key="+getcookie("key"),
                type: "get",
                dataType: "jsonp",
                success: function (data) {
                }
            });
        },
        getLocation:function(){
            var self = this;
            if(request("set_location")&&request("lat")&&request("lng")){
                $(".my-address-text").text(request("set_location"));
                addcookie("latitude",request("lat"));
                addcookie("longitude",request("lng"));
                self.getStoreId();
            }else{
                app.getLocation.latAndLon(
                    function (data) {
                        app.getLocation.cityname(data.latitude, data.longitude, function (datas) {
                            $(".my-address-text").text(datas.sematic_description);
                            addcookie("latitude",datas.latitude);
                            addcookie("longitude",datas.longitude);
                            self.getStoreId();
                        });
                    },
                    function () {
                        alert('GPS定位失败，跑腿邦找不到您的位置啦');
                    }
                );
            }

        },
        getStoreId:function(){
            var self = this;
            $.ajax({
                url: ApiUrl + "/index.php?act=home&op=adt_get_store_by_lication&client_type=wap&lat="+ getcookie("latitude") +"&lng=" + getcookie("longitude"),
                type: "get",
                dataType: "jsonp",
                success: function (data) {
                    if(data.code==200){
                        self.storeId = data.data.store_id;
                        localStorage.setItem("store_id",self.storeId);
                        self.storeName = data.data.store_name;
                        self.initData(data.data.store_id);
                        //$(".cart-sure").attr("href","confirm_order.html?store_id="+data.data.store_id);
                        $('.pub-cart').show();
                    }else if(data.code == 80002){
                        $('.noStore').show();
                    }
                }
            });
        },
        bindEvent:function(carriage,freeCarriage){
            var self = this;
            $(".desc-box-btn-del").off("click").on("click",function(){
                app.checkLogin();
                var $self = $(this);
                var num =$self.next().text();
                var nums = parseInt(num)-1;
                var id = $self.parent().attr("data-goodsid");
                self.cartAdd(id,nums,function(){
                    if(num>1){
                        $self.next().text(num-1);
                    }else if(num==1){
                        $self.hide();
                        $self.next().text("0").hide();
                    }
                    var price = parseFloat($self.parent().attr("data-price"));
                    var totalPrice = parseFloat($("#totalPrice").text());
                    var totalPriceNum = app.accSub(totalPrice,price);
                    var cartNum = $(".shopping-count").text();
                    if(cartNum=="1"){
                        $(".shopping-count").css("visibility","hidden").text("0");
                        $(".cart-sure").hide();
                        $(".cart-none").show();
                    }else{
                        $(".shopping-count").css("visibility","visible").text((parseInt(cartNum)-1));
                        $(".cart-sure").show();
                        $(".cart-none").hide();
                    }
                    $(".shopping-count").addClass("zoom-cart");
                    setTimeout(function(){
                        $(".shopping-count").removeClass("zoom-cart");
                    },150);
                    if(totalPriceNum>=freeCarriage){
                        $(".cart-total-postage").html("免配送费");
                    }else{
                        $(".cart-total-postage").html("另需配送费 <span>￥"+carriage+"</span>");
                    }
                    $("#totalPrice").text(totalPriceNum);
                });


            });
            $(".desc-box-btn-add").off("click").on("click",function(){
                app.checkLogin();
                var $self = $(this);
                var num =$self.prev().text();
                var nums = parseInt(num)+1;
                var id = $self.parent().attr("data-goodsid");
                self.cartAdd(id,nums,function(){
                    var price = parseFloat($self.parent().attr("data-price"));
                    var totalPrice = parseFloat($("#totalPrice").text());
                    var totalPriceNum = app.accAdd(totalPrice,price);
                    if(num==0){
                        $self.prev().prev().css("display","inline-block");
                        $self.prev().css('display','inline-block').text('1');
                    }else if(num>0){
                        $self.prev().css('display','inline-block').text(parseInt(num)+1);
                    }
                    var cartNum = $(".shopping-count").text();
                    if(cartNum==""){
                        $(".shopping-count").css("visibility","visible").text("1");
                    }else{
                        $(".shopping-count").css("visibility","visible").text((parseInt(cartNum)+1));
                    }
                    $(".cart-sure").show();
                    $(".cart-none").hide();
                    $(".shopping-count").addClass("zoom-cart");
                    setTimeout(function(){
                        $(".shopping-count").removeClass("zoom-cart");
                    },150);
                    if(totalPriceNum>=freeCarriage){
                        $(".cart-total-postage").html("免配送费");
                    }else{
                        $(".cart-total-postage").html("另需配送费<span>￥"+carriage+"</span>");
                    }
                    $("#totalPrice").text(totalPriceNum);
                });
                //$self.parent().find(".desc-box-btn-del").show();
                //$self.parent().find(".desc-box-btn-del").css("float","left");
            });

            app.productDetail.openProductDetails(this.storeId);
        }
    };

    app.index.init();

});