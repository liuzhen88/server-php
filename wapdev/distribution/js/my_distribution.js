$(function(){
    var key=getcookie("key");
    if(key==''){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        $.ajax({
           url:ApiUrl+"/index.php?act=distribution&op=get_mydistribute&key="+key+"&client_type=wap",
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(data){
                if(data.code==200){
                    var goodsTmpl=doT.template($("#goods").html());
                    $("#goods_container").html(goodsTmpl(data.data));
                    $(".details").click(function(){
                       var goods_id=$(this).find("span").text();
                        window.location.href=WapSiteUrl+"/distribution/distribution_details.html?goods_id="+goods_id;
                    });
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
});