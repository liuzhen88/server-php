$(function(){
    var order_sn=request("order_sn");
    var order_id=request("order_id");
    var key=getcookie("key");
    console.log(key);
    $("#submit").click(function(){
        var userInfo=$("#info").val();
        var tel=$("#number").val();
        if(userInfo=="" || tel==""){
            alert("意见或手机不能为空");
        }else{
            $.ajax({
                url:ApiUrl+"/index.php?act=adt_member_action&op=adt_member_complain&order_sn="+order_sn+"&accuser_phone="+tel+"&complain_content="+userInfo+"&key="+key+"&client_type=wap",
                type:"get",
                dataType:"jsonp",
                jsonp:"callback",
                success:function(data){
                    if(data.code==200){
                        alert("投诉成功");
                        window.location.href=WapSiteUrl+"/aidatui/order_info1.html?order_id="+order_id;
                    }else if(data.code=80001){
                        window.location.href = WapSiteUrl + "/tmpl/member/login1.html";
                    }else{
                        alert(data.message);
                    }
                }
            });
        }
    });
});