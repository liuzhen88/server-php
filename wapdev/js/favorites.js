

var lat=localStorage.getItem("latitude");
var lng=localStorage.getItem("longitude");
var key=getcookie('key');
// var lat=31;
// var lng=24;
// var key="a2f274b7c77b440e5b0a8eb5b0c811a3";
$(function() {
    if(key==''){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        $.ajax({
            url:ApiUrl+"/index.php?act=member_favorites&op=favorites_list&curpage=1&location="+lat+","+lng+"&client_type=wap&key="+key+"&fav_type=1&is_online=1"+"&token_member_id="+getcookie("user_id"),
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success: function(data){
                if(data.code==200){
                    var goodsFavListDoTmpl = doT.template($("#goodsFavListtmpl").html());
                    $("#goodsFavList").html(goodsFavListDoTmpl(data));
                }
            }   
        });
        $.ajax({
            url:ApiUrl+"/index.php?act=member_favorites&op=favorites_list&curpage=1&location="+lat+","+lng+"&client_type=wap&key="+key+"&fav_type=2&is_online=1"+"&token_member_id="+getcookie("user_id"),
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success: function(data){
                if(data.code==200){
                    var storeFavListtDoTmpl = doT.template($("#storeFavListtmpl").html());
                    $("#storeFavList").html(storeFavListtDoTmpl(data));
                }
            }   
        });
    }
})
function changMenu(thisID,obj_name){
    $(thisID).addClass('on').siblings().attr('class','');
    $("#"+obj_name).siblings('ul').hide();
    $("#"+obj_name).show();
}
