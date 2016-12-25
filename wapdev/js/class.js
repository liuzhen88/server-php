$(function(){

    FastClick.attach(document.body);

    $.ajax({
        url:ApiUrl + "/index.php?act=unlimited_invitation&op=get_class_all&pure=true&client_type=wap",
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(data){
            if(data.code=="200"){
                var itemDoTmpl = doT.template($("#item-tmpl").html());
                $("#content").html(itemDoTmpl(data)).removeClass("loading");
            }
        }
    });

});