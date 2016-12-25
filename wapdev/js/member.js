//获取url参数
function request(paras)
{
    var url = location.href;
    url=decodeURI(url);
    var paraString = url.substring(url.indexOf("?")+1,url.length).split("&");
    var paraObj = {};
    for (var i=0; j=paraString[i]; i++){
        paraObj[j.substring(0,j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=")+1,j.length);
    }
    var returnValue = paraObj[paras.toLowerCase()];
    if(typeof(returnValue)=="undefined"){
        return "";
    }else{
        return returnValue;
    }
}

$(document).ready(function(){
    var key=request('key');
	var wx_person=request("wx_person");
    if(key==''){
        key = getcookie('key');//wap版的
    }else{
        addcookie('key',key);//安卓和苹果的
        key=getcookie('key');
    }

    if(key==''){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html?wx_person="+wx_person;
    }else{
        var member_id = getcookie('user_id');
        $(".myshare .follow").attr("href","share_att.html?member_id="+member_id);
        $(".myshare .fans").attr("href","share_fans.html?member_id="+member_id);
        $(".myshare .shares").attr("href","share_personal.html?member_id="+member_id);

        $("#order").click(function(){

            window.location.href=WapSiteUrl+"/tmpl/member/order_list.html?key="+key+"&getpayment=1&order_status=10&order_type=2&curpage=1&client_type=wap";//我的订单
        });
        $("#collect").click(function(){
            window.location.href=WapSiteUrl+"/tmpl/member/favorites.html?key="+key+"&fav_type=1&client_type=wap";//我的收藏
        });
        $("#address").click(function(){
            window.location.href=WapSiteUrl+"/tmpl/member/address_list.html?key="+key+"&client_type=wap";
        });

        $("#follow").click(function(){
            window.location.href=WapSiteUrl+"/favorites.html";
        });

        $(".myinfo").click(function(){
            window.location.href=WapSiteUrl+"/personal_center.html";
        });

        $("#points").click(function(){
            window.location.href=WapSiteUrl+"/my_points_main.html";
        });

        $("#people").click(function(){
            window.location.href=WapSiteUrl+"/my_connection.html";
        });

        $("#more").click(function(){
            window.location.href=WapSiteUrl+"/more.html";
        });



        //获取用户个人信息
        $.ajax({
            url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+key+"&token_member_id="+getcookie("user_id"),
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success: function(data){
                if(data.code==200){
                    var name=data.data.member_info.nick_name;
                    var photo=data.data.member_info.avator;
                    var invitation = data.data.member_info.invitation;
                    var is_dis=data.data.member_info.is_distribution;


                    if(is_dis==0){
                        //不是分销商
                        $(".member-list-tel").before("<li><a href='tmpl/shareSale/shareFunct.html?from_member=1'><span class='share-sale-be'></span>成为分销</a></li>");
                    }else{
                        //是分销商
                        $(".member-list-tel").before("<li><a href='tmpl/shareSale/sSaleManager_2.html?is_dis="+is_dis+"'><span class='share-sale-manager'></span>分销管理</a></li>");
                    }

                    $('.info-image').html('<img id="infoImg" src="'+photo+'" style="display:none">');
                    infoImg.onload = function(){
                        $('#infoImg').show();
                    }
                    $(".info-name-text").html(name);
                    $(".info-name-code span").text(invitation);
                    $(".myinfo").css('background-image','url('+photo+'?imageMogr2/blur/3x5)');
                }else if(data.code==80001){
                    alert(data.message);
                    window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                }else{
                    alert(data.message);
                }
            }
        });
    }
    //注销用户信息
    $(".mem_exit").click(function(){
        if (confirm("你确定退出吗？")) {
            $.ajax({
                url:ApiUrl+"/index.php?act=member_logout&client_type=wap&op=index&key="+key,
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success: function(data){
						delCookie('key');
                        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                }
            });
        }
    });




});