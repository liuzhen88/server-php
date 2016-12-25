$(function () {
    FastClick.attach(document.body);

    var curpage=1;
    var special=1;
    getSpecialByScroll(special);

    $(window).scroll(function(){
        var scrollTop = $(window).scrollTop();
        var scrollHeight = $(document).height();
        var windowHeight = $(window).height();
        if(scrollTop + windowHeight == scrollHeight){
                curpage+=1;
                getSpecialByScroll(special);
        }
    });

    function getSpecialByScroll(specialType){
        $.ajax({
            url:ApiUrl+"/index.php?act=special&op=getList&special_type="+specialType+"&curpage="+curpage+"&page=6",
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(result){
                if (result.code == 200) {
                    var that;
                    if(specialType==1){
                        that=$(".topic-act");
                    }else{
                        that=$(".topic-class");
                    }
                    if(result.datas.length!=0){
                        if(curpage==1){
                            that.find(".topic-list").html(doT.template($("#topicTmpl").html())(result.datas));
                        }else{
                            that.find(".topic-list").append(doT.template($("#topicTmpl").html())(result.datas));
                        }
                        echo.init({
                            offset: 10,
                            throttle: 100,
                            unload: false,
                            callback: function (element, op) {
                            }
                        });
                    }else{
                        that.find(".dataNull").css("display","block");
                    }
                }
            }
        });
    }

    $('.topic-nav').on('click','li',function() {
        $('.topic-nav').find('span').removeClass('on');
        $(this).find('span').addClass('on');
        var navId=$(this).attr('id');
        curpage = 1;
        if (navId == 'topicAct') {
            $('.topic-class').hide();
            $('.topic-act').show();
            special = 1;
            getSpecialByScroll(special);
        }else if(navId == 'topicClass'){
            $('.topic-act').hide();
            $('.topic-class').show();
            special = 3;
            getSpecialByScroll(special);
        }
    })

});