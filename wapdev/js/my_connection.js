$(document).ready(function(){
    var flag=0;
    var key=getcookie("key");
    getConnection(1,1);
    var curpage1=1;
    var curpage2=1;
    var text=$(".bar-left").html();
    var text_content=$(".bar-right").html();
    $(".bar-left").click(function(){
        getConnection(1,1);
        $(".list").remove();
        $(".bar-left").addClass("check");
        $(".bar-right").removeClass("check");
        flag=0;
        curpage1=1;
    });
    $(".bar-right").click(function(){
        getConnection(2,1);
        $(".list").remove();
        $(".bar-left").removeClass("check");
        $(".bar-right").addClass("check");
        flag=1;
        curpage2=1;
    });

    //获取人脉
    function getConnection(type,curpage){
        $.ajax({
            url:ApiUrl+"/index.php?act=member_invitation&op=index&key="+key+"&type="+type+"&curpage="+curpage+"&client_type=wap"+"&token_member_id="+getcookie("user_id"),
            type:"get",
            dataType:"jsonp",
            jsonp:"callback",
            success:function(result){
                if(result.code==200){
                    var avator=new Array(),member_truename=new Array(),member_time=new Array();
                    if(type==1){
                        var total_number=result.data.datas.total_number;

                        $(".bar-left").html(text+total_number);
                    }
                    if(type==2){
                        var total_number=result.data.datas.total_number;

                        $(".bar-right").html(text_content+total_number);

                    }

                    //var orderDetails=doT.template($("#orderDetails").html());
                    //$("#goodsMain").show().html(orderDetails(result.data));
                    $(result.data.datas.invitation_list).each(function(k,v){
                        avator[k]= v.avator;
                        member_truename[k]= v.member_truename;
                        member_time[k]= v.member_time;
                        var time;
                        if(member_time[k]==''){
                            time="";
                        }else{
                            time=get_time(member_time[k]);
                        }
                        var list="<div class='list'>"
                                        +"<div class='person-img'><img src='"+avator[k]+"' width='35px' height='35px'/></div>"
                                        +"   <div class='person-info'>"
                                            +"<p class='peson-info-p'>"+member_truename[k]+"</p>"
                                            +"<p class='peson-info-p bottom-p'>加入时间:"+time+"</p>"
                                         +"</div>"
                                    +"</div>";
                        $("#goodsMain").append(list);
                    });
                    if(result.data.extend_data.hasmore == true){
                        curpage++;
                        getConnection(type,curpage);
                    }
                }
            }
        });
    }

    //获取上级邀请码
    $.ajax({
        url:ApiUrl+"/index.php?act=member_index&op=index&key="+key+"&client_type=wap"+"&token_member_id="+getcookie("user_id"),
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(data){
            if(data.code==200){
                var first_invitation=data.data.member_info.first_invitation;
                var first_invitation_nickname=data.data.member_info.first_invitation_nickname;
                $("#title-contain").html(first_invitation+"("+first_invitation_nickname+")");
            }
        }
    });
});
function get_time(this_time){
    Date.prototype.format = function(format) {
        var date = {
            "M+": this.getMonth() + 1,
            "d+": this.getDate(),
            "h+": this.getHours(),
            "m+": this.getMinutes(),
            "s+": this.getSeconds(),
            "q+": Math.floor((this.getMonth() + 3) / 3),
            "S+": this.getMilliseconds()
        };
        if (/(y+)/i.test(format)) {
            format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
        }
        for (var k in date) {
            if (new RegExp("(" + k + ")").test(format)) {
                format = format.replace(RegExp.$1, RegExp.$1.length == 1
                    ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
            }
        }
        return format;
    }
    var cc=new Date(parseInt(this_time)*1000);
    var aa=cc.format('yyyy-MM-dd h:m:s');
    return aa;
}