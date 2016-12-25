$(function(){
   var goods_id=request("goods_id");
    var key=getcookie("key");
    if(key==""){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        $.ajax({
            url:ApiUrl+"/index.php?act=distribution&op=distribute_list&key="+key+"&goods_id="+goods_id+"&client_type=wap",
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(data){
                if(data.code==200){
                    var listTemplate=doT.template($("#listTemplate").html());
                    $("#listContainer").html(listTemplate(data.data));
                }else if(data.code==80001){
                    window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                }else{
                    alert(data.message);
                }
            },
            error:function(err){
                console.log(err);
            }
        });
    }

});