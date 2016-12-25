$(function(){
    var num=0;
    var display=1;
    var py = 0;
    app.productDetail = {
        currentState:0,//0:closed ,1:open
        motionLock:0,//is moving
        init:function(){
            FastClick.attach(document.body);

            this.debug();
            this.closeProductDetails();
            this.indexLayClick();

        },
        debug:function(){
            $(".product-detail-debug").click(function(){
                //get html
                var div = $(this).parent().find(".product-detail-box");
                var html = div.html();
                //show
//                alert(html);
                alert(div.attr("style"));
                alert(div.css("background-color"));
                  div.css("background","red");

            });
        },
        addTouchListen:function(){
            document.addEventListener('touchstart', function(e) {
                //e.preventDefault();
                var touch = e.touches[0];
                py = touch.pageY;
//                alert(touch.pageX + " - " + touch.pageY);
              console.log(touch.pageX + " - " + touch.pageY);
            }, false);
        },
        delTouchListen:function(){
          document.removeEventListener('touchstart',function(e){
              py = 0;
              console.log('end touch listen');
          },false);
        },
        openProductDetails:function(store_id){
            var self=this;
            $(".list-per-goods .product-image").click(function(){

              if(1 == app.productDetail.currentState){
                return;
              }
              if(0!=app.productDetail.motionLock){
                return;
              }
              app.productDetail.motionLock = 1;


              $(".product-detail-box" ).scroll(function() {
                $('.product-detail-debug').html(this.scrollTop);
                if(this.scrollTop<-5){
                  $(".product-detail-confirm").trigger("click");
                }
              });

                $(".index-lay").css("display","block");
                $(".product-detail-box").css("display","block");
                $(".product-detail-box").addClass("showProduct");
                $(".product-detail-box").removeClass("hideProduct");

                setTimeout(function(){
                  $(".product-detail-confirm").css("display","block");
                  app.productDetail.currentState=1;
                  app.productDetail.motionLock=0;
                },500);

                var good_id=$(this).parents(".list-per-goods").attr("data-goodsid");//商品ID

                $(".content").css("display","none");

                self.getAjax(good_id,store_id);
            });
        },
        closeProductDetails:function(){
            $(".product-detail-confirm").click(function(){

              if(0 == app.productDetail.currentState){
                return;
              }
              if(0!=app.productDetail.motionLock){
                return;
              }
              app.productDetail.motionLock = 1;

                $(".product-detail-box").scrollTop(0);
                $(".index-lay").css("display","none");
                $(".product-detail-confirm").css("display","none");
                $(".product-detail-box").css("bottom","-740px");
                $(".product-detail-box").addClass("hideProduct");
                $(".product-detail-box").removeClass("showProduct");

                $(".content").css("display","block");

                setTimeout(function(){
                  $(".product-detail-box").css("bottom","0");
                  $(".product-detail-box").css("display","none");
                  app.productDetail.currentState = 0;
                  app.productDetail.motionLock=0;
                },500);
            });

        },
        indexLayClick:function(){
            $(".index-lay").click(function(){
                if(1 == app.productDetail.currentState){

                    if(0!=app.productDetail.motionLock){
                      return;
                    }
                    app.productDetail.motionLock = 1;

                    $(".product-detail-box").scrollTop(0);
                    $(".index-lay").css("display","none");
                    $(".product-detail-confirm").css("display","none");
                    $(".product-detail-box").css("bottom","-740px");
                    $(".product-detail-box").addClass("hideProduct");
                    $(".product-detail-box").removeClass("showProduct");

                    $(".content").css("display","block");

                    setTimeout(function(){
                      $(".product-detail-box").css("bottom","0");
                      $(".product-detail-box").css("display","none");
                      app.productDetail.currentState = 0;
                      app.productDetail.motionLock=0;
                    },500);
                }

                if($(".pub-cart-box").css("display")=="block"){

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
                      $(".pub-cart-box").css("bottom","0");
                    },500);

                    $(".index-lay").css("display","none");
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

              var productDetailTmpl = doT.template($("#product-detail-tmpl").html());
              $(".product-detail-box").html(productDetailTmpl(data));
              var key=getcookie("key");
              num = n;

              // 加
              $(".pd-price-right").click(function(){
                  num++;
                  $(this).parent().find(".pd-price-center").html(num);

                  app.index.changeCartObj(good_id,num);
                  app.index.setCartInfo();

              });
              $(".pd-price-left").click(function(){
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
