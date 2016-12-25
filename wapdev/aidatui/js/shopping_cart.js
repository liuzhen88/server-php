var adt_free_carriage_leave="";
$(function(){
    var flag=0;
    var lengthNum = 0;
    app.cart = {
        init:function(){
            FastClick.attach(document.body);

            var forCart=this;
            if(getcookie("key")){
                forCart.getShoppingChatData();
            }
            $(".cart-num").on("touchend",function(e){
                e.preventDefault();
                if($(".pub-cart-box").css("display")=="none"){
                    forCart.getShoppingChatData();
                    forCart.showShoppingCart();
                    loadScroller("pub-cart-ul",4);
                }else{
                    forCart.hideShoppingCart();
                }
            });
            $(".cart-clear").on("touchend",function(e){
                e.preventDefault();
                forCart.delShopCart();
            });

            $(".cart-total-right").on("touchend",function (e) {
                e.preventDefault();
                forCart.controllerCatJump();
            });
            $(".cart-sure").on("touchend",function(e){
                e.preventDefault();
                var latCartNum=$(".shopping-count").html();
                if(app.index.storeState != 1){
                    $('#shopTime').text('小店已打烊');
                    $('.zhezhao').show();
                    $('#shopHours').show();
                }else{
                    if(app.index.hoursNow<app.index.startTime||app.index.hoursNow>app.index.endTime||(app.index.hoursNow==app.index.startTime&&app.index.minuteNow<app.index.startMinute)||(app.index.hoursNow==app.index.endTime&&app.index.minuteNow>=app.index.endMinute)){
                        $('.zhezhao').show();
                        $('#shopHours').show();
                    }else if(latCartNum==0){
                        alert("商品数量不能为空");
                    }else{

                        var storeId=app.index.storeId;
                        var address_id=request("address_id");
                        if(storeId==""){
                            storeId=localStorage.getItem("store_id");
                        }
                        window.location.href = "confirm_order1.html?store_id=" + storeId + "&address_id=" + address_id;
                    }
                }


            });
            //$("#list").on("touchend",".cart-num-right",function(e){
            //    e.preventDefault();
            //    addNum(this);
            //});
            //$("#list").on("touchend",".cart-num-left",function(){
            //    e.preventDefault();
            //    reduce(this);
            //})
        },
        showShoppingCart:function(){
            $(".cart-sure").css("display","none");
            $(".cart-total").css("display","none");
            $(".cart-clear").css("display","block");

            $(".index-lay").css("display","block");
            $(".pub-cart-box").css("display","block");
            $(".pub-cart-box").addClass("showCart");
            $(".pub-cart-box").removeClass("hideCart");
            $(".pub-cart").css("bottom","288px");
            $(".pub-cart").addClass("showCart");
            $(".pub-cart").removeClass("hideCart");

        },
        hideShoppingCart:function(){
            $(".cart-sure").css("display","block");
            $(".cart-total").css("display","block");
            $(".cart-clear").css("display","none");

            $(".pub-cart").css("bottom","0");
            $(".pub-cart-box").css("bottom","-290px");
            $(".pub-cart").addClass("hideCart");
            $(".pub-cart").removeClass("showCart");
            $(".pub-cart-box").addClass("hideCart");
            $(".pub-cart-box").removeClass("showCart");
            setTimeout(function(){$(".index-lay").css("display","none");},100);
            setTimeout(function(){$(".pub-cart-box").css("display","none");$(".pub-cart-box").css("bottom","0");},500);

        },
        getShoppingChatData:function(){

            $("#list li").remove();
            if(app.index.originData==null){
                return;
            }
            var goods_list_json = app.index.originData.data.goods_list;
            for(var key in goods_list_json){
                var category= goods_list_json[key];
                for(var j=0;j<category.length;j++){

                  var item = category[j];
                  var idx = item["goods_id"];
                  var numb = app.index.saveObj[idx]
                  if(numb != null){
                    name = item["goods_name"];
                    price = item["league_goods_price"];
                    sum = app.accMul(parseFloat(price),numb);
                      var goods_storage=item["goods_detail"].good_info.goods_storage;

                    var list = "<li class='cart-per-good'>"
                        + "<div class='cart-goods-name float-left'>" + name + "</div>"
                        + "<div class='cart-goods-price float-left'>¥<span class='shop-goods-price'>" + sum + "</span></div>"
                        + "<div class='cart-num-right float-right' ontouchend='addNum(this);'><img src='img/add_icon.png' /><span style='display:none' class='cartGoodsId'>" + idx + "</span><span style='display:none' class='goodsPrice'>" + price + "</span><span style='display: none' class='storageMax'>"+goods_storage+"</span></div>"
                        + "<div class='cart-num-center float-right'>" + numb + "</div>"
                        + "<div class='cart-num-left float-right' ontouchend='reduce(this);'><img src='img/del_icon.png' /><span style='display:none' class='cartGoodsId'>" + idx + "</span><span style='display:none' class='goodsPrice'>" + price + "</span></div>"
                        + "<div class='clear-float'></div>"
                        + " </li>";
                    $("#list").append(list);
                  }
                }
            }


            $("#total-money").html(app.index.cash)
            $("#totalPrice").html(app.index.cash);

            if(app.index.cash >= app.index.adt_free_carriage_leave){
                $(".cart-total-postage").html("免费配送");
                $(".cart-num-page").html("免费配送");
            }else{
                var cha=app.accSub(app.index.adt_free_carriage_leave , app.index.cash);
                var subdiv="另需配送费<span>￥"+app.index.adt_carriage+"</span>,还差<span>"+cha+"</span>元配送费";
                $(".cart-num-page").html(subdiv);
            }


            if(app.index.cash < app.index.adt_free_carriage_leave){
                $(".cart-send-price").hide();
            }
            $(".cart-none").html("满" + app.index.adt_free_carriage_leave + "免运送费");

            $("#cart-pei-money").html("￥" + app.index.adt_carriage);


            $(".shopping-count").css("visibility", "visible");
            $(".shopping-count").html(app.index.cartNum);


        },
        controllerCatJump:function(){
            var storeId=app.index.storeId;
            var address_id=request("address_id");
            if(storeId==""){
                storeId=localStorage.getItem("store_id");
            }
            if(app.index.storeState != 1){
                $('#shopTime').text('小店已打烊');
                $('.zhezhao').show();
                $('#shopHours').show();
            }else {
                if (app.index.hoursNow < app.index.startTime || app.index.hoursNow > app.index.endTime || (app.index.hoursNow == app.index.startTime && app.index.minuteNow < app.index.startMinute) || (app.index.hoursNow == app.index.endTime && app.index.minuteNow >= app.index.endMinute)) {
                    $('.zhezhao').show();
                    $('#shopHours').show();
                } else if (app.index.cartNum == 0) {

                    alert("商品数量不能为空");
                } else {
                    window.location.href = "confirm_order1.html?store_id=" + storeId + "&address_id=" + address_id;
                }
            }


        },
        delShopCart:function(){
          if(app.index.cartNum > 0){
            var key=getcookie("key");
            var r=confirm("确定要清空购物车吗?");
            if(r==true){
                $("#list li").remove();
                $(".desc-box-num").html(0).hide();
                $(".desc-box-num").prev().hide();
                $(".shopping-count").html(0);
                localStorage.removeItem("saveObj");

                app.index.cleanCart();
                this.hideShoppingCart();
            }
          } else{
            this.hideShoppingCart();
          }

        }

    };

    app.cart.init();

});
function setCartInfo2(){

  app.index.refreshList();

    if(app.index.cash >= app.index.adt_free_carriage_leave){
        $(".cart-num-page").html("免费配送");
    }else{
        var cha=app.index.adt_free_carriage_leave - app.index.cash;
        var subdiv="另需配送费<span>￥"+app.index.adt_carriage +"</span>,还差<span>"+cha+"</span>元配送费";
        $(".cart-num-page").html(subdiv);

        $(".cart-send-price").hide();
    }

    $("#total-money").html(app.index.cash);
    $("#totalPrice").html(app.index.cash);
    $(".shopping-count").html(app.index.cartNum);


}
function addNum(obj){
    var e=event||window.event;
    e.preventDefault();
    var goods_id=$(obj).find(".cartGoodsId").html();
    var storageMax=$(obj).find(".storageMax").html();

    var n = $(obj).parent().find(".cart-num-center").html();
    if(n>storageMax-1){
        alert("库存不足了,先买这么多,过段时间再来买吧~");
    }else{


        n = parseInt(n) + 1;
        $(obj).parent().find(".cart-num-center").html(n);

        var price = $(obj).find(".goodsPrice").html();

        $(obj).parent().find(".cart-goods-price").find(".shop-goods-price").html(app.accMul(price,n));

        app.index.changeCartObj(goods_id,n);

        setCartInfo2();
    }

}
function reduce(obj){
    var e=event||window.event;
    e.preventDefault();
    var goods_id=$(obj).find(".cartGoodsId").html();

    var n = $(obj).parent().find(".cart-num-center").html();
    n = parseInt(n) - 1;
    if(n<=0){
      n = 0;
    }

    $(obj).parent().find(".cart-num-center").html(n);

    var price = $(obj).find(".goodsPrice").html();

    $(obj).parent().find(".cart-goods-price").find(".shop-goods-price").html(app.accMul(price,n));

    app.index.changeCartObj(goods_id,n);


    if(n <= 0){
        $(obj).parent(".cart-per-good").remove();
        if(app.index.cartNum <=0){
            app.cart.hideShoppingCart();
        }
    }

    setCartInfo2();
}
