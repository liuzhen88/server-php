var adt_free_carriage_leave="";
$(function(){
    var flag=0;
    var lengthNum = 0;
    app.cart = {
        init:function(){
            FastClick.attach(document.body);

            var forCart=this;
            forCart.getShoppingChatData();
            $(".cart-num").click(function(){
                if($(".pub-cart-box").css("display")=="none"){
                    forCart.showShoppingCart();
                    forCart.getShoppingChatData();
                }else{
                    forCart.hideShoppingCart();
                }
            });
            $(".cart-clear").click(function(){
                forCart.delShopCart();
            });

            $(".cart-total-right").click(function () {
                forCart.controllerCatJump();
            });
            $(".cart-sure").click(function(){
                var latCartNum=$(".shopping-count").html();
               
                if(latCartNum==0){

                    alert("商品数量不能为空");
                }else{

                    var storeId=app.index.storeId;
                    var address_id=request("address_id");
                    if(storeId==""){
                        storeId=localStorage.getItem("store_id");
                    }
                    window.location.href = "confirm_order.html?store_id=" + storeId + "&address_id=" + address_id;
                }

            });

        },
        showShoppingCart:function(){
            $(".cart-sure").css("display","none");
            $(".cart-total").css("display","none");
            $(".cart-clear").css("display","block");

            $(".index-lay").css("display","block");
            $(".pub-cart-box").css("display","block");
            $(".pub-cart-box").addClass("showCart");
            $(".pub-cart-box").removeClass("hideCart");
            $(".pub-cart").css("bottom","575px");
            $(".pub-cart").addClass("showCart");
            $(".pub-cart").removeClass("hideCart");

        },
        hideShoppingCart:function(){
            $(".cart-sure").css("display","block");
            $(".cart-total").css("display","block");
            $(".cart-clear").css("display","none");

            $(".pub-cart").css("bottom","0");
            $(".pub-cart-box").css("bottom","-580px");
            $(".pub-cart").addClass("hideCart");
            $(".pub-cart").removeClass("showCart");
            $(".pub-cart-box").addClass("hideCart");
            $(".pub-cart-box").removeClass("showCart");
            setTimeout(function(){$(".pub-cart-box").css("display","none");$(".pub-cart-box").css("bottom","0");},500);
            $(".index-lay").css("display","none");
        },
        getShoppingChatData:function(){

            var storeId=app.index.storeId;
            if(storeId==""){
                storeId=localStorage.getItem("store_id");
            }
            var address_id=request("address_id");
            $("#list li").remove();
            var key=getcookie("key");
            if(key==""){
                window.location.href = WapSiteUrl + "/aidatui/login.html";
            }else {
                $.ajax({
                    url: ApiUrl + "/index.php?act=member_cart_league&op=cart_list&key=" + key + "&store_id=" + storeId + "&client_type=wap",
                    type: "get",
                    dataType: "jsonp",
                    jsonp: "callback",
                    success: function (data) {
                        if (data.code == 200) {
                            var adt_free_carriage_leave = data.data.adt_free_carriage_leave;
                            //console.log(adt_free_carriage_leave);
                            for (var i = 0; i < data.data.cart_list.length; i++) {
                                var list = "<li class='cart-per-good'>"
                                    + "<div class='cart-goods-name float-left'>" + data.data.cart_list[i].goods_name + "</div>"
                                    + "<div class='cart-goods-price float-left'>¥<span class='shop-goods-price'>" + data.data.cart_list[i].goods_sum + "</span></div>"
                                    + "<div class='cart-num-right float-right' onclick='addNum(this,adt_free_carriage_leave)'><img src='img/add_icon.png' /><span style='display:none' class='cartGoodsId'>" + data.data.cart_list[i].goods_id + "</span><span style='display:none' class='goodsPrice'>" + data.data.cart_list[i].goods_price + "</span></div>"
                                    + "<div class='cart-num-center float-right'>" + data.data.cart_list[i].goods_num + "</div>"
                                    + "<div class='cart-num-left float-right' onclick='reduce(this,adt_free_carriage_leave)'><img src='img/del_icon.png' /><span style='display:none' class='cartGoodsId'>" + data.data.cart_list[i].goods_id + "</span><span style='display:none' class='goodsPrice'>" + data.data.cart_list[i].goods_price + "</span></div>"
                                    + "<div class='clear-float'></div>"
                                    + " </li>";
                                $("#list").append(list);
                            }
                            var money_goods = data.data.money_goods;
                            $("#total-money").html(money_goods);
                            console.log(money_goods);
                            $("#totalPrice").html(money_goods);


                            var adt_carriage_this = data.data.adt_carriage_this;
                            if(money_goods<adt_free_carriage_leave){
                                $(".cart-send-price").hide();
                            }
                            $(".cart-none").html("满" + adt_free_carriage_leave + "免运送费");

                            $("#cart-pei-money").html("￥" + adt_carriage_this);
                            /*if(flag==0){
                             $(".cart-num-right").click(function(){
                             var num=$(this).parent().find(".cart-num-center").html();
                             num++;
                             $(this).parent().find(".cart-num-center").html(num);
                             console.log($(this).parent().find(".shop-goods-price").html());
                             //$(".cart-goods-price").html()
                             });
                             }
                             flag++;*/

                            $(".shopping-count").css("visibility", "visible");
                            //$(".shopping-count").html(data.data.cart_list.length);
                            var totalNumCart = new Array();
                           lengthNum = 0;
                            for (var i = 0; i < $(".cart-num-center").length; i++) {

                                lengthNum += parseInt($(".cart-num-center")[i].innerHTML);
                            }
                            $(".shopping-count").html(lengthNum);




                        }

                    }
                });
            }



        },
        controllerCatJump:function(){
            var storeId=app.index.storeId;
            var address_id=request("address_id");
            if(storeId==""){
                storeId=localStorage.getItem("store_id");
            }
                if(lengthNum==0){

                        alert("商品数量不能为空");
                }
                else{
                    window.location.href = "confirm_order.html?store_id=" + storeId + "&address_id=" + address_id;
                }


        },
        delShopCart:function(){
            var key=getcookie("key");
            var r=confirm("确定要清空购物车吗?");
            if(r==true){
                $.ajax({
                    url:ApiUrl+"/index.php?act=member_cart_league&op=cart_del&key="+key+"&client_type=wap",
                    type:"get",
                    dataType:"jsonp",
                    jsonp:"callback",
                    success:function(data){
                        if(data.code==200){
                            alert(data.message);
                            $("#list li").remove();
                            $(".desc-box-num").html(0);
                            $(".shopping-count").html(0);
                            window.location.reload();
                        }
                    }
                });
            }
        }

    };

    app.cart.init();

});
function addNum(obj,adt_free_carriage_leave){

    var key=getcookie("key");
    var goods_id=$(obj).find(".cartGoodsId").html();
   //console.log(goods_id);
    var cartNumber=$(obj).parent().find(".cart-num-center").html();//购物车数�?
    var totalPrice=$(obj).find(".goodsPrice").html();//总价�?
    //console.log(totalPrice);
    $(obj).parent().find(".cart-goods-price").find(".shop-goods-price").html(totalPrice*cartNumber);
    cartNumber++;
    $(obj).parent().find(".cart-num-center").html(cartNumber);
    $.ajax({
        url:ApiUrl+"/index.php?act=member_cart_league&op=cart_add&key="+key+"&goods_id="+goods_id+"&quantity="+cartNumber+"&store_id="+app.index.storeId+"&client_type=wap",
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(data){
            if(data.code==200){
                $(obj).parent().find(".cart-goods-price").find(".shop-goods-price").html(totalPrice*cartNumber);
                //$(".desc-box-num").html(cartNumber);
                for(var j=0;j<$(".none").length;j++){
                    if(goods_id==$(".none")[j].innerHTML){
                        $(".desc-box-num").eq(j).html(cartNumber);
                    }

                }


               var total=0;
                for(var i=0;i<$(".shop-goods-price").length;i++){
                    total+=parseFloat($(".shop-goods-price")[i].innerHTML);
                }
                $("#total-money").html(total);
                $("#totalPrice").html(total);
                if(total<adt_free_carriage_leave){
                    $(".cart-send-price").hide();
                }
                var totalNumCart=new Array();
                var lengthNum=0;
                for(var i=0;i<$(".cart-num-center").length;i++){

                    lengthNum+=parseInt($(".cart-num-center")[i].innerHTML);
                }
                $(".shopping-count").html(lengthNum);

            }
        }
    });


}
function reduce(obj,adt_free_carriage_leave){
    var key=getcookie("key");
    var goods_id=$(obj).find(".cartGoodsId").html();
    var cartNumber=$(obj).parent().find(".cart-num-center").html();//购物车数�?
    var totalPrice=$(obj).find(".goodsPrice").html();//总价�?
    var storeId=app.index.storeId;


    if(cartNumber>1){
        cartNumber--;
        $(obj).parent().find(".cart-num-center").html(cartNumber);
        $.ajax({
            url:ApiUrl+"/index.php?act=member_cart_league&op=cart_add&key="+key+"&goods_id="+goods_id+"&quantity="+cartNumber+"&store_id="+app.index.storeId+"&client_type=wap",
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(data){
                if(data.code==200){
                    $(obj).parent().find(".cart-goods-price").find(".shop-goods-price").html(totalPrice*cartNumber);
                    //$(".desc-box-num").html(cartNumber);
                    for(var j=0;j<$(".none").length;j++){
                        if(goods_id==$(".none")[j].innerHTML){
                            $(".desc-box-num").eq(j).html(cartNumber);
                        }

                    }
                    var total=0;
                    for(var i=0;i<$(".shop-goods-price").length;i++){
                        total+=parseFloat($(".shop-goods-price")[i].innerHTML);
                    }
                    $("#total-money").html(total);
                    $("#totalPrice").html(total);
                    if(total<adt_free_carriage_leave){
                        $(".cart-send-price").hide();
                    }
                    var totalNumCart=new Array();
                    var lengthNum=0;
                    for(var i=0;i<$(".cart-num-center").length;i++){

                        lengthNum+=parseInt($(".cart-num-center")[i].innerHTML);
                    }
                    $(".shopping-count").html(lengthNum);
                }
            }
        });
    }else{
        var r=confirm("确定要删除该商品吗?");
        if(r==true){
            $.ajax({
                url:ApiUrl+"/index.php?act=member_cart_league&op=cart_add&goods_id="+goods_id+"&key="+key+"&quantity=0&store_id="+storeId+"&client_type=wap",
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(data){
                    if(data.code==200){
                        $(obj).parent(".cart-per-good").remove();
                        var total=0;
                        for(var i=0;i<$(".shop-goods-price").length;i++){
                            total+=parseFloat($(".shop-goods-price")[i].innerHTML);
                        }
                        $("#total-money").html(total);
                        $("#totalPrice").html(total);
                        var lengthNum = 0;
                        for (var i = 0; i < $(".cart-num-center").length; i++) {

                            lengthNum += parseInt($(".cart-num-center")[i].innerHTML);
                        }
                        $(".shopping-count").html(lengthNum);
                        for(var j=0;j<$(".none").length;j++){
                            if(goods_id==$(".none")[j].innerHTML){
                                $(".desc-box-num").eq(j).html(0);
                                $(".desc-box-num").eq(j).hide();
                                $(".desc-box-btn-del").eq(j).hide();
                            }

                        }
                    }
                }
            });
        }
    }

}