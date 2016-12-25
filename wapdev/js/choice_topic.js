$(function () {
    FastClick.attach(document.body);

    $.ajax({
        url:ApiUrl+"/index.php?act=special&op=getList&special_type="+request('specialType')+"&curpage=1&recommend=1",
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(result){
            if (result.code == 200) {
                if(result.datas.length!=0){
                    $(".topic-list").html(doT.template($("#topicTmpl").html())(result.datas));

                    echo.init({
                        offset: 10,
                        throttle: 100,
                        unload: false,
                        callback: function (element, op) {
                        }
                    });
                }else{
                    $(".dataNull").css("display","block");
                }
            }
        }
    });
});