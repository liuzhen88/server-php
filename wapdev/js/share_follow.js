$(function () {
    FastClick.attach(document.body);
    member_id=request('member_id')||getcookie('user_id');
    var page = 1;
    if(key==""){
         window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        function getData(curpage){
            $.ajax({
                url: ApiUrl + "/index.php?act=circle_info&op=Followee&key="+key+"&client_type=wap&curpage="+curpage,
                type: 'get',
                dataType: 'jsonp',
                success: function (result) {
                    if (result.code == 200) {
                        var followeelistDoTmpl = doT.template($("#followeelistTmpl").html());
                        $(".share-like").append(followeelistDoTmpl(result));
                        $('.dataNull').hide();
                        if(result.data.length == 10){
                            page++;
                            getData(page);
                        }
                    }
                }
            });
        }
        $.ajax({
            url: ApiUrl + "/index.php?act=circle_info&op=Followee&key="+key+"&client_type=wap&curpage=1",
            type: 'get',
            dataType: 'jsonp',
            success: function (result) {
                if (result.code == 200) {
                    var followeelistDoTmpl = doT.template($("#followeelistTmpl").html());
                    $(".share-like").html(followeelistDoTmpl(result));
                    if(result.data.length==0){
                        $(".sheader h2").html("我的关注");
                        $(".share-like").css('border','0').css('background','none');
                    }
                    if(result.data.length == 10){
                        page++;
                        getData(page);
                    }
                }
            }
        });

    }

});
