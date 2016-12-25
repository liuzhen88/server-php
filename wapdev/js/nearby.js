if (typeof AGG == "undefined") {
    var AGG = {};
}

$(window).ready(function (e) {

    FastClick.attach(document.body);

    AGG.cityname = localStorage.getItem("cityname");
    AGG.setcity = localStorage.getItem("setcity");

    var gloab_curpage = 1,
        city_name = AGG.setcity || AGG.cityname,
        lat = localStorage.getItem("latitude"),
        lng = localStorage.getItem("longitude"),
        order = 0,
        thisPageId = request("class_id"),
        storeName = request("store_name"),
        nearbyTitle = request("title"),
        bottomBar = request("bottom_bar");

    AGG.nearby = {
        init: function () {
            this.bindEvent();
            this.initSort();
            this.getDataItem(0, 1, thisPageId, "", storeName);
            if (nearbyTitle) {
                $(".nearby-header-all span").text(nearbyTitle);
            } else if (storeName) {
                $(".nearby-header-all span").text(storeName);
            }
            if (bottomBar == 'no') {
                $("#footer").hide();
            }
        },
        initSort: function () {
            var self = this;
            $.ajax({
                url: ApiUrl + "/index.php?act=unlimited_invitation&op=get_class_all&parent_id=0&client_type=wap",
                type: "get",
                dataType: "jsonp",
                jsonp: "callback",
                success: function (data) {
                    if (data.code == 200) {
                        var $layL = $(".allClassLayL ul");
                        var $layR = $(".allClassLayR ul");
                        var liDoTmpl = doT.template($("#li-tmpl").html());
                        $layL.html('<li id="allCategory" data-id="">全部</li>');
                        $layL.append(liDoTmpl(data.data));
                        $layL.on("click", "li", function () {
                            var $self = $(this);
                            $layL.find("li").removeClass('select');
                            $(this).addClass('select');
                            var index = $(this).index();
                            if (index > 0) {
                                $layR.html(liDoTmpl(data.data[index - 1].subList));
                            } else {
                                $layR.html('');
                                $("#perStoreBox").html('').addClass("loading");
                                $('.nearby_lay').hide();
                                $('.nearby-header-all').removeClass('nearby-header-select');
                                self.getDataItem(0, 1, '', '', '');
                            }
                            //通过分类获取
                            $layR.on("click", "li", function () {
                                self.sortByCategory($(this));
                                if ($(this).index() == 0) {
                                    $(".nearby-header-all span").text($self.text()).attr('data-id', $self.attr('data-id'));
                                } else {
                                    $(".nearby-header-all span").text($(this).text()).attr('data-id', $(this).attr('data-id'));
                                }
                            });
                            $("#allCategory").on("click", function () {
                                var sortCategory = $('.nearby-header-all span').attr('data-id') || '',
                                    sortDistance = $('.nearby-header-dis span').attr('data-des') || '',
                                    sortOrder = $('.nearby-header-sort span').attr('data-sort') || 0;
                                $(".nearby-header-all span").text('全部');
                                self.getDataItem(sortOrder, gloab_curpage, sortCategory,sortDistance);
                            });
                        });
                        $layL.find("li").eq(1).trigger('click');
                    }
                }
            });
        },
        getDataItem: function (orders, curpages, classIdStrs, distanceStrs, storeNames) {
            if (storeNames) {
                storeNames = "&store_name=" + storeNames;
            } else {
                storeNames = "";
            }
            this.ajaxData(orders, curpages, classIdStrs, distanceStrs, storeNames);
            this.getDataByScroll(orders, classIdStrs, distanceStrs, storeNames);
        },
        ajaxData: function (order, curpage, classIdStr, distanceStr, storeName, loading) {
            $.ajax({
                url: ApiUrl + "/index.php?act=unlimited_invitation&op=goods_list_v2&city_name=" + city_name + "&lat=" + lat + "&lng=" + lng + "&client_type=wap&order=" + order + "&curpage=" + curpage + "&page=5&class_id=" + classIdStr + "&distance=" +distanceStr + storeName,
                type: "get",
                dataType: "jsonp",
                jsonp: "callback",
                success: function (data) {
                    if (data.code == 200) {
                        $(".network-error").hide();
                        var nearbyDoTmpl = doT.template($("#nearby-tmpl").html());
                        $("#perStoreBox .nearby-loading").remove();
                        //alert('ok');
                        $(nearbyDoTmpl(data)).appendTo($("#perStoreBox"));
                        $("#perStoreBox").removeClass('loading');
                        if (loading && (data.data.length >= 6)) {
                            $('<li class="loading nearby-loading"></li>').appendTo($("#perStoreBox"));
                        }
                        gloab_curpage++;
                    } else {
                        $(".network-error").show();
                    }
                }
            });
        },
        sortByCategory: function ($obj) {
            $("#perStoreBox").html('').addClass("loading");
            $('.nearby_lay').hide();
            $('.nearby-header-all').removeClass('nearby-header-select');
            gloab_curpage = 1;
            var id = $obj.attr('data-id'),
            sortCategory = $('.nearby-header-all span').attr('data-id') || '',
                sortDistance = $('.nearby-header-dis span').attr('data-des') || '',
                sortOrder = $('.nearby-header-sort span').attr('data-sort') || 0;
            this.getDataItem(sortOrder, gloab_curpage, id, sortDistance);
        },
        sortByDistance: function ($obj) {
            $("#perStoreBox").html('').addClass("loading");
            gloab_curpage = 1;
            var distance = $obj.attr('data-des'),
                sortCategory = $('.nearby-header-all span').attr('data-id') || '',
                sortDistance = $('.nearby-header-dis span').attr('data-des') || '',
                sortOrder = $('.nearby-header-sort span').attr('data-sort') || 0;
            this.getDataItem(sortOrder, gloab_curpage, sortCategory, distance);
            $('.nearby_lay').hide();
            $('.nearby-header-dis').removeClass('nearby-header-select');
        },
        sortByGenius: function ($obj) {
            gloab_curpage = 1;
            var sortGenius = $obj.index(),
                sortCategory = $('.nearby-header-all span').attr('data-id') || '',
                sortDistance = $('.nearby-header-dis span').attr('data-des') || '',
                sortOrder = $('.nearby-header-sort span').attr('data-sort') || 0;
            $("#perStoreBox").html('').addClass("loading");
            this.getDataItem(sortGenius, gloab_curpage, sortCategory, sortDistance);
            $('.nearby_lay').hide();
            $('.nearby-header-sort').removeClass('nearby-header-select');
        },
        getDataByScroll: function (orders, classIdStrs, distanceStrs, storeNames) {
            var self = this;
            $(window).scroll(function () {
                var doc_h = $(document).height();
                var win_h = $(window).height();
                var scroll_top = $(window).scrollTop();
                if (scroll_top >= doc_h - win_h) {
                    self.ajaxData(orders, gloab_curpage, classIdStrs, distanceStrs, storeNames, true);
                }
            });
        },
        bindEvent: function () {
            var self = this;
            $(".nearby_header").on("click", "li", function () {
                if ($(this).hasClass('nearby-header-select')) {
                    $(".nearby_header li").removeClass('nearby-header-select');
                    $(".nearby_lay").hide();
                } else {
                    $(".nearby_header li").removeClass('nearby-header-select');
                    $(this).addClass('nearby-header-select');
                    $(".nearby_lay").hide();
                    $(".nearby_lay").eq($(this).index()).show();
                }
            });

            $(".nearLay ul").on("click", "li", function () {
                self.sortByDistance($(this));
                $(".nearby-header-dis span").text($(this).text()).attr('data-des', $(this).attr('data-des'));
            });

            $(".sortLay ul").on("click", "li", function () {
                self.sortByGenius($(this));
                $(".nearby-header-sort span").text($(this).text()).attr('data-sort', $(this).attr('data-sort'));
            });

        }
    };

    AGG.nearby.init();

});
function evaluateNum(num) {
    var evaN;
    evaN = num * 20;
    return evaN;

}