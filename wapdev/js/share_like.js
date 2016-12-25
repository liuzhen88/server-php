$(function () {
    FastClick.attach(document.body);
    theme_id=request('theme_id');
    var page = 1;
    function getData(curpage){
        $.ajax({
            url: ApiUrl + "/index.php?act=index&op=getLikeMember&client_type=wap&theme_id="+theme_id+"&curpage="+curpage,
            type: 'get',
            dataType: 'jsonp',
            success: function (result) {
                if (result.code == 200) {
                    var likelistDoTmpl = doT.template($("#likelistTmpl").html());
                    $(".share-like").append(likelistDoTmpl(result));
                }
                $('.dataNull').hide();
                if(result.data.length == 10){
                    page++;
                    getData(page);
                }
            }
        });
    }
    $.ajax({
        url: ApiUrl + "/index.php?act=index&op=getLikeMember&client_type=wap&curpage=1&theme_id="+theme_id,
        type: 'get',
        dataType: 'jsonp',
        success: function (result) {
            if (result.code == 200) {
                var likelistDoTmpl = doT.template($("#likelistTmpl").html());
                $(".share-like").html(likelistDoTmpl(result));
                if(result.data.length==0){
                    $(".sheader h2").html("点赞");
                    $(".share-like").css('border','0').css('background','none');
                }
                if(result.data.length == 10){
                    page++;
                    getData(page);
                }
            }
        }
    });

});
