$(function(){
    var num=1;
    var pb_num=1;
    app.productDetail = {
        init:function(){
            FastClick.attach(document.body);

            this.closeProductDetails();
            this.indexLayClick();
        },
        openProductDetails:function(store_id){
            var self=this;
            $(".list-per-goods .product-image").click(function(){

                $(".index-lay").css("display","block");
                $(".product-detail-box").css("display","block");
                $(".product-detail-box").addClass("showProduct");
                $(".product-detail-box").removeClass("hideProduct");

                setTimeout(function(){$(".product-detail-confirm").css("display","block");},500);

                var good_id=$(this).parents(".list-per-goods").attr("data-goodsid");//商品ID

                self.getAjax(good_id,store_id);
            });
        },
        closeProductDetails:function(){
            $(".product-detail-confirm").click(function(){
                $(".product-detail-box").scrollTop(0);
                $(".index-lay").css("display","none");
                $(".product-detail-confirm").css("display","none");
                $(".product-detail-box").css("bottom","-740px");
                $(".product-detail-box").addClass("hideProduct");
                $(".product-detail-box").removeClass("showProduct");
                setTimeout(function(){$(".product-detail-box").css("bottom","0");$(".product-detail-box").css("display","none");},500);
                window.location.reload();

            });

        },
        indexLayClick:function(){
            $(".index-lay").click(function(){
                if($(".product-detail-box").css("display")=="block"){
                    $(".product-detail-box").scrollTop(0);
                    $(".index-lay").css("display","none");
                    $(".product-detail-confirm").css("display","none");
                    $(".product-detail-box").css("bottom","-740px");
                    $(".product-detail-box").addClass("hideProduct");
                    $(".product-detail-box").removeClass("showProduct");
                    setTimeout(function(){$(".product-detail-box").css("bottom","0");$(".product-detail-box").css("display","none");},500);
                }else if($(".pub-cart-box").css("display")=="block"){
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
                }
            });
        },
        getAjax:function(good_id,store_id){
            var self=this;
            $.ajax({
                url:ApiUrl+"/index.php?act=goods&op=adt_good_detail&client_type=wap&good_id="+good_id+"&store_id="+store_id,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(result){
                    if(result.code==200){
                        var productDetailTmpl = doT.template($("#product-detail-tmpl").html());
                        $(".product-detail-box").html(productDetailTmpl(result.data));
                        var key=getcookie("key");
                        // 加
                        num=$(this).find(".pd-price-center").html();

                        $(".pd-price-right").click(function(){

                            var goods_id=$(this).find(".goods_id").html();

                            num++;
                            pb_num=num+1;
                            $(this).parent().find(".pd-price-center").html(pb_num);

                           $.ajax({
                               url:ApiUrl+"/index.php?act=member_cart_league&op=cart_add&goods_id="+goods_id+"&key="+key+"&quantity="+pb_num+"&store_id="+store_id+"client_type=wap",
                               type:"get",
                               dataType:"jsonp",
                               jsonp:"callback",
                               success:function(data){
                                   if(data.code==200){

                                   }
                               }
                           });
                        });
                        $(".pd-price-left").click(function(){
                            var goods_id=$(this).find(".goods_id").html();
                            if(pb_num>1) {
                                num--;
                                pb_num = num + 1;
                                $(this).parent().find(".pd-price-center").html(pb_num);
                                $.ajax({
                                    url:ApiUrl+"/index.php?act=member_cart_league&op=cart_add&goods_id="+goods_id+"&key="+key+"&quantity="+pb_num+"&store_id="+store_id+"client_type=wap",
                                    type:"get",
                                    dataType:"jsonp",
                                    jsonp:"callback",
                                    success:function(data){
                                        if(data.code==200){

                                        }
                                    }
                                });
                            }else{
                                alert("不能小于1");
                            }


                        });

                    }
                }
            });
        }
    };

    app.productDetail.init();

});