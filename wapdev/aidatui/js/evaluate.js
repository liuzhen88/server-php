$(function(){
    var key=getcookie("key");
    var order_id=request("order_id");
    var is_get_quickly=request("is_get_quickly");
    if(is_get_quickly == 1){
        $.ajax({
            url: ApiUrl + "/index.php?act=member_order&op=adt_order_state&client_type=wap&key="+key+"&order_id="+order_id,
            type: "get",
            dataType: "jsonp",
            success: function (data) {
                var confirmTime; //确认收货时间
                var beginSendTime; //商户开始配送时间
                var minTime; //最终时间

                function get_unix_time(time1){
                    var newstr = time1.replace(/-/g,'/');
                    var date =  new Date(newstr);
                    var time_str = date.getTime().toString();
                    return time_str.substr(0, 10);
                }

                $(data.data.order).each(function(k,v){
                    if(v.log_orderstate == 20){
                        beginSendTime=get_unix_time(data.data.order[k].log_time);
                    }else if(v.log_orderstate == 40){
                        confirmTime=get_unix_time(data.data.order[k].log_time);
                    }
                });
                minTime=parseInt((confirmTime - beginSendTime)/60);
                $("#time").html(minTime+"分钟");
                $('.shopper-info').show();
            }
        });
    }

    var qualityIndex = 5; //评价质量
    var serverIndex = 5; //配送服务
    var speedIndex = 5; //配送速度
    $(".quality-star").click(function () {
        qualityIndex = clickStar(qualityIndex,this,'quality');
    });

    $(".server-star").click(function(){
        serverIndex = clickStar(serverIndex,this,'server');
    });

    $(".speed-star").click(function(){
        speedIndex = clickStar(speedIndex,this,'speed');
    });
    $("#submit").click(function(){
        var userInfo=$("#user-info").val();
        $.ajax({
            url:ApiUrl+"/index.php?act=member_order&op=adt_add_order_evaluate&key="+key+"&order_id="+order_id+"&leval_content="+userInfo+"&leval_desccredit="+qualityIndex+"&leval_servicecredit="+serverIndex+"&leval_deliverycredit="+speedIndex,
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(data){
             if(data.code==200){
                 alert("提交成功");
                 window.location.href=WapSiteUrl+"/aidatui/order_info1.html?order_id="+order_id;
             }else if(data.code==80001){
                 window.location.href = WapSiteUrl + "/aidatui/login1.html";
             }else {
                 alert(data.message);
             }
            }
        });
    });
});
function clickStar(thisIndex,self,className){
    thisIndex = $('.'+className+'-star').index(self)+1;
    for(var i=0;i<thisIndex;i++){
        $('.'+className+'-star').eq(i).attr("src","img/star.png");
    }
    for(var i=4;i>thisIndex-1;i--){
        $('.'+className+'-star').eq(i).attr("src","img/gray_star.png");
    }
    switch (thisIndex)
    {
        case 1:
            $('#'+className+'-desc').text("很差");
            break;
        case 2:
            $('#'+className+'-desc').text("不满意");
            break;
        case 3:
            $('#'+className+'-desc').text("一般");
            break;
        case 4:
            $('#'+className+'-desc').text("满意");
            break;
        case 5:
            $('#'+className+'-desc').text("很满意");
    }
    return thisIndex;
}