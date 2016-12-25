$(function(){

    app.index = {

        cash:0,
        cartNum:0,
        originData:null,
        saveObj:{},

        storeId:"",
        storeName:"",
        adt_carriage:0,
        adt_free_carriage_leave:0,

        init:function(){
            FastClick.attach(document.body);
            if(localStorage.getItem("key")){
                addcookie("key",localStorage.getItem("key"));
            }

            //@chenee:load app.index.saveObj
            app.index.saveObj = localStorage.getItem("saveObj");
            if(app.index.saveObj == null){
              app.index.saveObj = {}
            }else{
              app.index.saveObj=JSON.parse(app.index.saveObj);
            }

            this.getLocation();
        },
        setCartInfo:function(){
            if(app.index.cash>=this.adt_free_carriage_leave){
                $(".cart-num-page").html("免配送费");
            }else{
                var cha=app.accSub(this.adt_free_carriage_leave,app.index.cash);
                var subdiv="另需配送费<span>￥"+this.adt_carriage+"</span>,还差<span>"+cha+"</span>元免配送费";
                $(".cart-num-page").html(subdiv);
            }
            $("#totalPrice").text(app.index.cash);

            if(app.index.cartNum <= 0){
              app.index.cartNum = 0;
                $(".shopping-count").css("visibility","hidden").text("0");
                $(".cart-sure").hide();
                $(".cart-none").show();
            }else{
                $(".shopping-count").css("visibility","visible").text((app.index.cartNum));
                $(".cart-sure").show();
                $(".cart-none").hide();
            }
        },
        resetOriginData:function(data){
            var goods_list_json = data.data.goods_list;
            app.index.cash = 0;
            app.index.cartNum = 0;
            for(var key in goods_list_json){
                var category= goods_list_json[key];
                for(var j=0;j<category.length;j++){

                  var item = category[j];
                  item["cart_goods_num"]= 0;//init to 0

                  var idx = item["goods_id"];
                  var numb = app.index.saveObj[idx];
                  if(numb != null){
                    //init numb
                    item["cart_goods_num"]=numb;

                    //count total app.index.cash
                    f = item["league_goods_price"];
                    app.index.cash = app.accAdd(app.index.cash,app.accMul(parseFloat(f),numb));

                    //count total numb
                    app.index.cartNum += numb;
                  }
                }
            }
        },
        initData:function(storeId){
            var self = this;
            var key=getcookie("key");
            $.ajax({
                url: "http://shop.aigegou.com/agg/mobile/index.php?act=home&op=adt_home&client_type=wap&store_id=17729&key="+key,
                type: "get",
                dataType: "jsonp",
                success: function (data) {
                    if(data.code==200){
                      app.index.originData = data;

                        var listCategoryTmpl = doT.template($("#list-category-tmpl").html());
                        $("#categoryWrap").html(listCategoryTmpl(app.index.originData.data.class_info));
                        if(app.index.originData.data.class_info.length == 0){
                            alert(self.storeName + ' 商户已入驻，暂时还未上传商品，敬请期待~')
                        }
                        $(".cart-total-postage span").text("￥"+app.index.originData.data.adt_carriage);
                        $(".cart-none").text("满"+app.index.originData.data.adt_free_carriage_leave+"免运送费");

                        app.index.adt_free_carriage_leave = app.index.originData.data.adt_free_carriage_leave;
                        app.index.adt_carriage = app.index.originData.data.adt_carriage;//系统配运费

                        //@chenee: re-count app.index.cash ,app.index.cartNum
                        self.resetOriginData(app.index.originData);
                        self.setCartInfo();

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
                              ul = list.eq(i);
                                if(ul.attr("data-iditem")==id){
                                    isAppend = false;
                                    ul.show();
                                }
                            }
                            if(isAppend){
                                var category= app.index.originData.data.goods_list[id];
                                var listItem = '<ul class="product-list-wrap" data-idItem="'+ id +'">' + listProductTmpl(category) +'</ul>';
                                $("#productWrap").append(listItem);
                            }else{

                            }

                            self.bindEvent(app.index.adt_carriage,app.index.adt_free_carriage_leave);
                          });

                        $listCategory.eq(0).trigger("click");
                    }
                }
            });
        },
        refreshList:function(){
                var id = $(".list-category ul li.select").attr("data-id");

                var list = $(".product-list-wrap");
                for(var i=0;i<list.length;i++){
                  ul = list.eq(i);
                    if(ul.attr("data-iditem")==id){
                        //refresh num !!
                        var l = ul.find('div.product-desc-box-btn');
                        for(var j=0;j<l.length;j++){
                          var id1 = parseInt(l.eq(j).attr('data-goodsid'));
                          n = app.index.saveObj[id1];
                          if(n==null){
                            l.eq(j).find('div.desc-box-btn-del').hide();
                            l.eq(j).find('div.desc-box-num').html(0).hide();
                          } else{
                            l.eq(j).find('div.desc-box-num').html(n).css("display","inline-block");;
                            l.eq(j).find('div.desc-box-btn-del').css("display","inline-block");;
                          }

                        }
                    }
                }

        },
        getLocation:function(){
            var self = this;
            var lat = request("lat");
            var lng = request("lng");
            var setLocation = request("set_location");
            var addrId = request("address_id");
            if(lat != ''&&lng != ''&&setLocation != ''){
                $(".my-address-text").text(setLocation);
                localStorage.setItem("latitude", lat);
                localStorage.setItem("longitude", lng);
                localStorage.setItem("description", setLocation);
                if(addrId != ''){
                    localStorage.setItem("addrId", addrId);
                }
                self.getStoreId();
            }else{
                lat = localStorage.getItem("latitude");
                lng = localStorage.getItem("longitude");
                setLocation = localStorage.getItem("description");
                if(lat == 'null'||lng == 'null'||setLocation == 'null'||lat == null||lng == null||setLocation == null){
                    var geolocation = new BMap.Geolocation();
                    geolocation.getCurrentPosition(function(r){
                        var statusLocation = this.getStatus();
                        if(statusLocation == 0){
                            $.ajax({
                                url: 'http://api.map.baidu.com/geocoder/v2/?ak=btsVVWf0TM1zUBEbzFz6QqWF&callback=renderReverse&location=' + r.point.lat + ',' + r.point.lng + '&output=json&pois=0',
                                type: "get",
                                dataType: "jsonp",
                                jsonp: "callback",
                                success: function (data) {
                                    if(data.status==0){
                                        $(".my-address-text").text(data.result.sematic_description);
                                        localStorage.setItem("latitude", r.point.lat);
                                        localStorage.setItem("longitude", r.point.lng);
                                        localStorage.setItem("description", data.result.sematic_description);
                                        self.getStoreId();
                                    }else{
                                        alert('地址获取失败！');
                                        window.location.href = "position.html"
                                    }
                                }
                            });
                        }else {
                            alert('地址获取失败！');
                            window.location.href = "position.html";
                        }
                    },{enableHighAccuracy: true});
                }else{
                    $(".my-address-text").text(setLocation);
                    localStorage.setItem("latitude", lat);
                    localStorage.setItem("longitude", lng);
                    localStorage.setItem("description", setLocation);
                    self.getStoreId();
                }
            }


        },
        getStoreId:function(){
            var self = this;
            $.ajax({
                url: ApiUrl + "/index.php?act=home&op=adt_get_store_by_lication&client_type=wap&lat="+ localStorage.getItem("latitude") +"&lng=" + localStorage.getItem("longitude"),
                type: "get",
                dataType: "jsonp",
                success: function (data) {
                    if(data.code==200){
                        self.storeId = data.data.store_id;
                        var oldId = localStorage.getItem("store_id");
                        localStorage.setItem("store_id",self.storeId);

                        //if change store ,drop cart !
                        if(oldId != self.storeId){
                          app.index.saveObj = {};
                          var objTmp=JSON.stringify(app.index.saveObj);
                          localStorage.setItem("app.index.saveObj",objTmp);

                        }
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
        cleanCart:function(){
            app.index.saveObj = {};
            var objTmp=JSON.stringify(app.index.saveObj);
            localStorage.setItem("app.index.saveObj",objTmp);
            this.resetOriginData(app.index.originData);
            this.setCartInfo();
        },
        changeCartObj:function(id,numb){
            if(numb == 0){
              delete app.index.saveObj[id];
            } else{
              app.index.saveObj[id]=numb;
            }

            var objTmp=JSON.stringify(app.index.saveObj);
            localStorage.setItem("saveObj",objTmp);

            this.resetOriginData(app.index.originData);
            this.refreshList();
        },
        doAddorSub:function(diff,that){
          //judge add or sub
          var numbPosition;
          if(diff>0){
            numbPosition = that.prev();
          } else{
            numbPosition = that.next();
          }

          var num =parseInt(numbPosition.text());
          var nums = num + diff;

          var id = that.parent().attr("data-goodsid");

          this.changeCartObj(id,nums);
          this.setCartInfo();

          if(nums > 0){
              numbPosition.text(nums).css("display","inline-block");;
              numbPosition.prev().css("display","inline-block");
          }else {//<=0
              numbPosition.text("0").hide();
              numbPosition.prev().hide();
          }

          $(".shopping-count").addClass("zoom-cart");
          setTimeout(function(){
              $(".shopping-count").removeClass("zoom-cart");
          },150);

         // this.setCartInfo();

        },
        bindEvent:function(carriage,freeCarriage){
            var self = this;
            $(".desc-box-btn-del").off("click").on("click",function(){
//                app.checkLogin();
                var $self = $(this);
                self.doAddorSub(-1,$self);
            });
            $(".desc-box-btn-add").off("click").on("click",function(){
//                app.checkLogin();
                var $self = $(this);
                self.doAddorSub(1,$self);
            });

            app.productDetail.openProductDetails(this.storeId);
        }
    };

    app.index.init();

});
