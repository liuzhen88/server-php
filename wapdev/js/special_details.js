$(function () {
    FastClick.attach(document.body);

    /*if(getcookie("key")=="" || getcookie("key")==undefined){
     window.location.href=WapSiteUrl+"/tmpl/member/login.html";
     }*/

    getSpecial();

});

function getSpecial(){
    $.ajax({
        url:ApiUrl+"/index.php?act=special&op=get_detail&special_id="+request("special_id"),
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(result){
            if (result.code == 200) {
                var doTmpl = doT.template($("#specialDetialTmpl").html());
                $(".content-box").append(doTmpl(result));

                $(".special-list ul li").height(($(window).width()*0.95)*2/5);
                $(".special-img").height(($(window).width()*0.95)*2/5);

                /*$(".special-goodbox").each(function(){
                 var thisImg=$(this).find(".special-image img").height();
                 $(this).height(thisImg);
                 });*/

                $(".special-goodbox").each(function(){
                    $(this).height($(window).width());
                });

                if(result.datas.special.special_explain=="" || result.datas.special.special_explain==undefined){
                    $(".special-explain").css("display","none");
                }

                $(result.datas.goods).each(function(index,thisData){
                    if(thisData.evaluation_count!=0){
                        $(".evaluate-btn").eq(index).attr("href","tmpl/evaluate/evaluate_list.html?good_id="+thisData.goods_id);
                    }
                });

                /*$(result.datas.goods).each(function(index,thisData){
                 //获取收藏商品
                 $.ajax({
                 url:ApiUrl+"/index.php?act=user_action&op=is_favorites&key="+ getcookie("key")
                 +"&client_type=wap&good_id="+thisData.goods_id,
                 type:"get",
                 dataType:"jsonp",
                 jsonp:"callback",
                 success:function(data) {
                 if (data.code == 200 && data.data=="yes") {
                 $('.collect_star').eq(index).find("img").attr("src", "images/icon-collected@2x.png");
                 }
                 }
                 });
                 });*/

                //点击收藏按钮
                /*$(".collect_star").click(function(){
                 var indexThis=$(".collect_star").index($(this));
                 var gIdThis=result.datas.goods[indexThis].goods_id;
                 var isCollect=$(this).find("img").attr("src");
                 if(isCollect=="images/icon-collect@2x.png"){
                 //alert("no");
                 $.ajax({
                 url:ApiUrl+"/index.php?act=member_favorites&op=favorites_add&goods_id="+gIdThis+"&key="+getcookie("key")+"&client_type=wap&is_online=1",
                 type:"get",
                 dataType:"jsonp",
                 jsonp:"callback",
                 success:function(data){
                 if(data.code==200){
                 $(".collect_star").eq(indexThis).find("img").attr("src", "images/icon-collected@2x.png");
                 $(".collect_star").eq(indexThis).find(".collect_star_text").text(parseInt($(".collect_star").eq(indexThis).find(".collect_star_text").text())+1);
                 alert("收藏成功！");
                 return;
                 }else if(data.code==80001){
                 alert(data.message);
                 window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                 }else{
                 alert(data.message);
                 }
                 }
                 });
                 }else if(isCollect=="images/icon-collected@2x.png"){
                 //alert("yes");
                 $.ajax({
                 url:ApiUrl+"/index.php?act=member_favorites&op=favorites_del&fav_id="+gIdThis+"&key="+getcookie("key")+"&client_type=wap&type=goods",
                 type:"get",
                 dataType:"jsonp",
                 jsonp:"callback",
                 success:function(data){
                 if(data.code==200){
                 $(".collect_star").eq(indexThis).find("img").attr("src", "images/icon-collect@2x.png");
                 $(".collect_star").eq(indexThis).find(".collect_star_text").text(parseInt($(".collect_star").eq(indexThis).find(".collect_star_text").text())-1);
                 alert("成功取消收藏！");
                 return;
                 }else if(data.code==80001){
                 alert(data.message);
                 window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                 }else{
                 alert(data.message);
                 }
                 }
                 });
                 }else{
                 alert("star参数错误！");
                 }
                 });*/

                $(".special-goBuy").click(function(){
                    var thisGoodId=$(this).find("span").text();
                    window.location.href=WapSiteUrl+"/tmpl/productdetail.html?goods_id="+thisGoodId;
                });

            }
        }
    });
}