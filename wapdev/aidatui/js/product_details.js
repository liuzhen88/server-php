$(function(){
    var num=0;
    app.productDetail = {
        currentState:0,//0:closed ,1:open
        motionLock:0,//is moving
        init:function(){
            FastClick.attach(document.body);

            this.closeProductDetails();
            this.indexLayClick();
        },
        openProductDetails:function(store_id){
            var self=this;
           $(".list-per-goods .product-image").off().on("click",function(e){
               e.preventDefault;
                $(".index-lay").css("display","block");
                $(".product-detail-box").css("display","block");
                $(".product-detail-confirm").css("display","block");

                var good_id=$(this).parents(".list-per-goods").attr("data-goodsid");//商品ID

                self.getAjax(good_id,store_id);
            });
        },
        closeProductDetails:function(){
            $(".product-detail-confirm").on("touchend",function(e){
                e.preventDefault;
                destroyScroller(3);
                $(".product-detail-box").scrollTop(0);
                $(".index-lay").css("display","none");
                $(".product-detail-confirm").css("display","none");
                $(".product-detail-box").css("display","none");
            });

        },
        indexLayClick:function(){
            $(".index-lay").off().on("touchend",function(e){
                e.preventDefault();
                if($(".product-detail-box").css("display")=="block"){
                    destroyScroller(3);
                    $(".product-detail-box").scrollTop(0);
                    $(".index-lay").css("display","none");
                    $(".product-detail-confirm").css("display","none");
                    $(".product-detail-box").css("display","none");
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
                    setTimeout(function(){
                      $(".pub-cart-box").css("display","none");
                      $(".pub-cart-box").css("bottom","0");$(".index-lay").css("display","none");
                    },500);
                    destroyScroller(4);

                }

            });
        },
        getAjax:function(good_id,store_id){
            var n = app.index.saveObj[good_id];
            if(n == undefined){
             n = 0;
            }

            //find data
            var data = null;
            gl = app.index.originData.data.goods_list;
            for(var key in gl){
              for(var i=0;i<gl[key].length;i++){
                if(gl[key][i].goods_id == good_id){
                  data = gl[key][i].goods_detail;
                  break;
                }
              }
            }
            if(data == null){ //error
              return;
            }

            data.good_info["goods_num"] = n;

            var productDetailTmpl = doT.template($("#product-detail-tmpl").html()),picNum=0;
            $(".product-detail-box .scroller").html(productDetailTmpl(data));
            var box_img=$(".product-detail-box img");
            function FinishLoad(){
                picNum++;
                if(picNum==box_img.length){
                    if (scroller3 == null) {
                        loadScroller("product-detail-wrapper",3);
                    }else{
                        destroyScroller(3);
                        loadScroller("product-detail-wrapper",3);
                    }
                }
            }
            box_img.on("load",function(e){
                FinishLoad();
            });
            box_img.on("error",function(e){
                FinishLoad();
            });
              var key=getcookie("key");
              num = n;
            echo.init({
                offset: 10,
                throttle: 100,
                unload: false,
                callback: function (element, op) {
                    //console.log(element, 'has been', op + 'ed')
                }
            })

              // 加
              $(".pd-price-right").on("touchend",function(e){
                  e.preventDefault;
                  var storage=$(this).find(".storage").html();
                  if(num>storage-1){
                        alert("库存不足了,先买这么多,过段时间再来买吧~");
                  }else{
                      num++;
                      $(this).parent().find(".pd-price-center").html(num);
                      app.index.changeCartObj(good_id,num);
                      app.index.setCartInfo();
                  }


              });
              $(".pd-price-left").on("touchend",function(e){
                  e.preventDefault;
                  if(num > 0) {
                      num--;
                      $(this).parent().find(".pd-price-center").html(num);
                      app.index.changeCartObj(good_id,num);
                      app.index.setCartInfo();
                  }else{
                      //alert("不能小于0");
                  }

              });

        }
    };

    app.productDetail.init();

});
