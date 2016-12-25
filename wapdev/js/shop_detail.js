var goodsPageUrl = WapSiteUrl + "/tmpl/productdetail.html";

var thisStoreId = request("store_id");
var pageNum = 6;
var arrowNum = 0;

var curpage1 = 1;
var curpage2 = 1;
var curpage3 = 1;
var curpage4 = 1;

var hasmore1 = true;
var hasmore2 = true;
var hasmore3 = true;
var hasmore4 = true;

var flag = 0;

$(window).ready(function (e) {
    FastClick.attach(document.body);
    var leftheight = $(".address").css("height");
    $(".phone").css("height", leftheight);
    $('.jl-search-rank2 a').click(function () {
        $('.jl-search-rank2 a').removeClass("current");
        $(this).addClass("current");
    });

    $('.jl-search-rank2 .arrow').click(function () {
        arrowNum++;

        if ($(this).find('span').hasClass('on')) {
            $(this).find('span').removeClass('on');
        } else {
            $(this).find('span').addClass('on');
        }
    })

    $.ajax({
        url: ApiUrl + "/index.php?act=store&op=store_detail&store_id=" + thisStoreId,
        type: "get",
        dataType: "jsonp",
        jsonp: "callback",
        success: function (data) {
            if (data.code == 200) {
                $(".my_shopName").text(data.datas.store_info.store_name);
                var storeNum = parseInt(data.datas.store_info.store_credit_average);

                var subDiv = "<img src='../images/shopStar.png'/>";
                var subStr = "";

                for (var i = 0; i < storeNum; i++) {
                    subStr = subStr + subDiv;
                }

                $(".my_shopStar").append(subStr);
                $(".my_shopStar").append("<span>" + storeNum + ".0</span>");
                $("#store_phone").html(data.datas.store_info.store_phone);
                $("#shopImg").attr("src", data.datas.store_info.store_banner);
                $(".address").html("<span class='icon'></span>" + data.datas.store_info.area_info + " " + data.datas.store_info.store_address);

            }
        }

    });

    //商品列表默认展示新品
    byNewClick();

    //按新品展示商品列表
    $(".byNew").click(function () {

        byNewClick();

    });

    function byNewClick() {
        flag = 0;

        curpage1 = 1;

        $(".goods-item").remove();
        showGoodsList(thisStoreId, 0, pageNum, curpage1, 0);

    }

    //按销量展示商品列表
    $(".bySaleNum").click(function () {
        flag = 1;

        curpage2 = 1;

        $(".goods-item").remove();
        showGoodsList(thisStoreId, 1, pageNum, curpage2, 0);

    });

    //按价格展示商品列表
    $(".arrow").click(function () {
        flag = 2;

        $(".goods-item").remove();

        curpage3 = 1;

        if (arrowNum % 2 == 1) {

            showGoodsList(thisStoreId, 3, pageNum, curpage3, 1);

        } else {

            showGoodsList(thisStoreId, 3, pageNum, curpage3, 2);

        }
    });

    //按人气展示商品列表
    $(".byPeopleNum").click(function () {
        flag = 3;

        $(".goods-item").remove();

        curpage4 = 1;
        showGoodsList(thisStoreId, 2, pageNum, curpage4, 0);

    });

    $(window).scroll(function () {
        if (flag == 0) {

            curpage1 = scrollForData(hasmore1, thisStoreId, 0, pageNum, curpage1, 0);

        } else if (flag == 1) {

            curpage2 = scrollForData(hasmore2, thisStoreId, 1, pageNum, curpage2, 0);

        } else if (flag == 2) {

            if (arrowNum % 2 == 1) {
                curpage3 = scrollForData(hasmore3, thisStoreId, 3, pageNum, curpage3, 1);
            } else {
                curpage3 = scrollForData(hasmore3, thisStoreId, 3, pageNum, curpage3, 2);
            }

        } else if (flag == 3) {

            curpage4 = scrollForData(hasmore4, thisStoreId, 2, pageNum, curpage4, 0);
        }
    });

});


function scrollForData(hasmore, storeId, key, page, curpage, order) {

    var windowHeight = $(window).height();
    var documentHeight = $(document).height();
    var scroll_top = $(window).scrollTop();

    /*var sTHeigth=$("#my_GLHeader").position().top;

     if(sTHeigth<=scroll_top){
     $("#my_GLHeader").addClass("my_addC");
     }else{
     $("#my_GLHeader").removeClass("my_addC");
     };*/

    if (scroll_top == documentHeight - windowHeight) {

        if (hasmore) {

            curpage = curpage + 1;

            //alert(curpage);

            showGoodsList(storeId, key, page, curpage, order);
        } else {
            $(".lmy_NoData").css("display", "block");
        }

    }

    return curpage;
}


function showGoodsList(storeId, key, page, curpage, order) {
    //alert(order);

    $.ajax({
        url: ApiUrl + "/index.php?act=store&op=goods_list&key=" + key + "&page=" + page + "&curpage=" + curpage + "&order=" + order + "&store_id=" + storeId,
        type: "get",
        dataType: "jsonp",
        jsonp: "callback",
        success: function (data) {
            if (data.code == 200) {
                if (flag == 0) {
                    hasmore1 = data.hasmore;
                } else if (flag == 1) {
                    hasmore2 = data.hasmore;
                } else if (flag == 2) {
                    hasmore3 = data.hasmore;
                } else if (flag == 3) {
                    hasmore4 = data.hasmore;
                }

                $(data.datas.goods_list).each(function (index, thisData) {
                    var subDiv = "<div class='goods-item box-shw b-radius'><a href='" + goodsPageUrl + "?thisGoodId=" + thisData.goods_id + "'><div class='goods-item-pic'><img src='" + thisData.goods_image_url + "'></div><div class='goods-item-name'>" + thisData.goods_name + "</div><div class='goods-item-price'>¥" + thisData.goods_price + "</div></a></div>";
                    $(".content").append(subDiv);
                });
            }
        }
    });

}
