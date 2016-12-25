window.onload=function(){
    FastClick.attach(document.body);
    var key=getcookie("key");
    var boy=document.getElementById("boy");
    var gril=document.getElementById("gril");
    boy.onclick=function(){
        sendSex(1)
    }
    gril.onclick=function(){
        sendSex(2);
    }
    function sendSex(sex){
        $.ajax({
            url:ApiUrl+"/index.php?act=member_security&op=set_sex&key="+key+"&member_sex="+sex+"&client_type=wap"+"&token_member_id="+getcookie("user_id"),
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(data){

                if(data.code==200){

                    window.location.href=WapSiteUrl+"/personal_center.html";
                }
            }
        });
    }
}