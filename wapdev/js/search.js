$(function () {
    FastClick.attach(document.body);

    $('.jl-search-rank .arrow').click(function () {
        if ($(this).find('span').hasClass('on')) {
            $(this).find('span').removeClass('on');
        } else {
            $(this).find('span').addClass('on');
        }
    });

    if (AGG.client.isApp()) {
        $('.header-wrap').css('padding-top', '20px');
        $('.jl-search-rank').css('top', '76px');
        $('.jl-sch-box').css('padding-top', '68px');
        $(".header-back").css("top", "30px");
    }

    var key;
    var order = 1;
    var curpage = 1;
    var search_curpage = 1;
    var page = 5;
    var gc_id = request('gc_id');
    var keyword = request('keyword');
    if(keyword != ''){
        $("#search_content").val(keyword);
    }
    function searchAjax(url) {
        $.ajax({
            url: url,
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if (data.code == 200) {
                    var searchItemTmpl = doT.template($('#search-item-tmpl').html());
                    $("#searchBox").append(searchItemTmpl(data.data.datas.goods_list));
                }
            }
        });
    }

    //封装ajax请求,刚开始的默认请求
    function getDefaultData(gc_id,keyword) {
        searchAjax(ApiUrl + "/index.php?act=goods&op=goods_list&key_sort=4&page=10&curpage=1&gc_id=" + gc_id + "&keyword=" + keyword + "&client_type=wap");
    }

    var search_sort = request('sort');
    if (search_sort == 'new') {
        $('.jl-search-rank .bar li a').removeClass('current');
        $('.jl-search-rank .bar li').eq(0).find('a').addClass('current');
        $('#searchBox').html('');
        select_list(0, 5, 1, gc_id, 2);
    } else if (search_sort == 'hot') {
        $('.jl-search-rank .bar li a').removeClass('current');
        $('.jl-search-rank .bar li').eq(2).find('a').addClass('current');
        $('#searchBox').html('');
        select_list(1, 5, 1, gc_id, 2);
    } else {
        getDefaultData(gc_id,keyword);
    }

    //封装查询条件的ajax
    function select_list(key, page, curpage, gc_id, order) {
        searchAjax(ApiUrl + "/index.php?act=goods&op=goods_list&key_sort=" + key + "&page=" + page + "&curpage=" + curpage + "&gc_id=" + gc_id + "&order=" + order + "&client_type=wap")
    }

    $(".bar li").click(function () {
        $('.bar li a').removeClass('current');
        $(this).find('a').addClass('current');
        $("#searchBox").html('');
        key = $(this).index();
        curpage = 1;
        if (key != 3) {
            select_list(key, 5, 1, gc_id, 2);
        } else if (key == 3) {
            if ($("#lz_price").find("span").hasClass("on")) {
                order = 2;
                select_list(3, page, 1, gc_id, order);
            } else {
                order = 1;
                select_list(3, page, 1, gc_id, order);
            }
        }
    });

    //滚动条滑到底部ajax请求数据
    $(window).scroll(function () {
        if ($(window).scrollTop() >= $(document).height() - $(window).height()) {
            curpage++;
            select_list(key, page, curpage, gc_id, order);
        }
    });

    //封装搜索框中发内容的ajax请求
    function select_ajax(page, curpage, keyword, order) {
        searchAjax(ApiUrl + "/index.php?act=goods&op=goods_list&key_sort=4&page=" + page + "&curpage=" + curpage + "&keyword=" + keyword + "&order=" + order + "&client_type=wap");
    }

    //点击搜索框查询
    $("#search").click(function () {
        keyword = $("#search_content").val();
        if (keyword != "") {
            $("#searchBox").html('');
            select_ajax(5, 1, keyword, order);
            $(window).scroll(function () {
                if ($(window).scrollTop() >= $(document).height() - $(window).height()) {
                    search_curpage++;
                    select_ajax(5, search_curpage, keyword, order);
                }
            });
        }
    });

});