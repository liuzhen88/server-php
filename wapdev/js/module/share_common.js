var key; 
var user_id; //本人id
var member_id; //用户id
var theme_id; //帖子id
key = getcookie('key');
user_id = getcookie('user_id');
//key="47e01109d7ee54783ba874f97b341a67";
//user_id="19573";
//加关注
function attention(obj){
    if(key==""||user_id==""){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        var attObj=$(obj).find('span').attr('class');
        member_id = $(obj).attr('data-memberid');
        $(obj).find('.loading').show();
        $(obj).find('span').hide();
        if(attObj == 'att0'){
            $.ajax({
               url: ApiUrl + "/index.php?act=circle_info&op=FollowMember&client_type=wap&key="+key+"&friend_id="+member_id,
               type: 'get',
               dataType: 'jsonp',
               success: function(result) {
                    $(obj).find('.loading').hide();
                    $(obj).find('span').show();
                    if (result.code == 200) {
                        if(result.data.status == 1){
                            $(obj).find('span').attr('class','att1').html('已关注');
                        }else if(result.data.status == 2){
                            $(obj).find('span').attr('class','att2').html('相互关注');
                        }
                   }else{
                        alert(result.message);
                   }
               }
            });
        }else if(attObj == 'att1' || attObj == 'att2'){
            var cancelAtt=confirm("您确定要取消关注他吗")
            if (cancelAtt==true)
            {
                $.ajax({
                    url: ApiUrl + "/index.php?act=circle_info&op=FollowMember&client_type=wap&key="+key+"&friend_id="+member_id,
                    type: 'get',
                    dataType: 'jsonp',
                    success: function(result) {
                        $(obj).find('.loading').hide();
                        $(obj).find('span').show();
                        if (result.code == 200) {
                            $(obj).find('span').attr('class','att0').html('关注');
                        }else{
                            alert(result.message);
                        }
                    }
                });
            }else{
                $(obj).find('.loading').hide();
                $(obj).find('span').show();
            }

        }
    }
    
}
//点赞
function zan(obj){
    if(key==""||user_id==""){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        theme_id = $(obj).attr('data-themeid');
        var zanObj=$(obj).attr('class');
        var zanNum=parseInt($(obj).find('.zanNum').text());
        if(zanObj == 'sb_zan0'){
          if(zanNum==0){
            $.ajax({
                url:ApiUrl+'/index.php?act=circle_info&op=like&client_type=wap&key='+key+'&theme_id='+theme_id,
                type: 'get',
                dataType:'jsonp',
                success:function(result){
                    if(result.code == 200){
                        var fanslistTmpl = doT.template($("#fanslistTmpl2").html());
                        $(obj).parent().after(fanslistTmpl(result));
                        $(obj).attr('class','sb_zan1');
                        $(obj).find('.zanNum').text(result.data.count);
                    }
                }
            });
          }else{
            $.ajax({
                url:ApiUrl+'/index.php?act=circle_info&op=like&client_type=wap&key='+key+'&theme_id='+theme_id,
                type: 'get',
                dataType:'jsonp',
                success:function(result){
                    if(result.code == 200){
                        var fanslistTmpl = doT.template($("#fanslistTmpl").html());
                        $(obj).parent().next(".share_zan").find('ul').html(fanslistTmpl(result));
                        $(obj).attr('class','sb_zan1');
                        $(obj).find('.zanNum').text(result.data.count);
                    }
                }
            });
          }
          
        }else if(zanObj == 'sb_zan1'){
          if(zanNum==1){
            $.ajax({
                url:ApiUrl+'/index.php?act=circle_info&op=like&client_type=wap&key='+key+'&theme_id='+theme_id,
                type: 'get',
                dataType:'jsonp',
                success:function(result){
                    if(result.code == 200){
                        $(obj).parent().next(".share_zan").remove();
                        $(obj).attr('class','sb_zan0');
                        $(obj).find('.zanNum').text(result.data.count);
                    }
                }
            });
          }else{
            $.ajax({
                url:ApiUrl+'/index.php?act=circle_info&op=like&client_type=wap&key='+key+'&theme_id='+theme_id,
                type: 'get',
                dataType:'jsonp',
                success:function(result){
                    if(result.code == 200){
                        var fanslistTmpl = doT.template($("#fanslistTmpl").html());
                        $(obj).parent().next(".share_zan").find('ul').html(fanslistTmpl(result));
                        $(obj).attr('class','sb_zan0');
                        $(obj).find('.zanNum').text(result.data.count);
                    }
                }
            });
          }
        }
    }
}

function moreNum(num){
  if(num>99){
    num="99+";
    return num;
  }else{
    return num;
  }
}
//时间戳转换
function timeSpeak(tm){
    var waytime=new Date(parseInt(tm) * 1000);
    var year = waytime.getFullYear();
    var month = waytime.getMonth()+1;
    var date = waytime.getDate();
    var hm = toTen(waytime.getHours())+':'+toTen(waytime.getMinutes());
    waytime = ''+year+month+date;

    var nowtime=new Date();
    var year2 = nowtime.getFullYear();
    var month2 = nowtime.getMonth()+1;
    var date2 = nowtime.getDate();
    var hm2 = toTen(nowtime.getHours())+':'+toTen(nowtime.getMinutes());

    function toTen(t){
        if(t<10){
            t = '0'+t;
        }
        return t;
    }

    nowtime = ''+year2+month2+date2;
    var difftime = nowtime - waytime;
    var timeSpeak;
    if(difftime==0){
        timeSpeak = '';
    }else if(difftime==1){
        timeSpeak = '昨天';
    }else if(difftime==2){
        timeSpeak = '前天';
    }else{
        timeSpeak = month +'月'+ date +'日';
    }
    timeSpeak = timeSpeak + hm;
    return timeSpeak;
}
//转换性别
function memberSex(sex){
    if(sex==0){
        sex = "female";
        return sex;
    }else if(sex == 1){
        sex= "male";
        return sex;
    }else{
        return " ";
    }

}