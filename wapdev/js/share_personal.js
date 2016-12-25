$(function () {
    FastClick.attach(document.body);
    member_id=request('member_id');
    if(member_id==""){
        member_id=user_id;
    }
    if(key==''){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else {
        //加载个人信息
        $.ajax({
            url: ApiUrl + "/index.php?act=index&op=getmemberInfo&client_type=wap&o_id="+member_id+"&key="+key,
            type: 'get',
            dataType: 'jsonp',
            success: function (result) {
                if (result.code == 200) {
                    var spersInfoDoTmpl = doT.template($("#spersInfoTmpl").html());
                    $("#spersInfo").html(spersInfoDoTmpl(result));
                    var attClass=$('.att').find('span').attr('class');
                    if(attClass == 'att0'){
                        $('.att').find('span').html('关注');
                    }else if(attClass == 'att1'){
                        $('.att').find('span').html('已关注');
                    }else if(attClass == 'att2'){
                        $('.att').find('span').html('相互关注');
                    }
                }
            }
        });

        //加载个人相册
        $.ajax({
            url: ApiUrl + "/index.php?act=index&op=getMemberThemepic&client_type=wap&member_id="+member_id,
            type: 'get',
            dataType: 'jsonp',
            success: function (result) {
                if (result.code == 200) {
                    var spersInfoDoTmpl = doT.template($("#spersPhotoTmpl").html());
                    $("#spersPhoto").html(spersInfoDoTmpl(result));
                }
            }
        });
    }
});
