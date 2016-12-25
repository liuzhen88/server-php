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
    if(difftime==1){
        timeSpeak = '昨天';
    }else if(difftime==2){
        timeSpeak = '前天';
    }else{
        timeSpeak = month +'月'+ date +'日';
    }
    timeSpeak = timeSpeak + hm;
    return timeSpeak;
}

$(function(){


    var tagname = request("tag");
    var page = 1;
    var more = true;
    var ajaxtime = true;
    $.ajax({
        url:ApiUrl+"/index.php?act=index&op=gettagdetail&client_type=ios&tag_name="+tagname,
        type:"get",
        dataType:"jsonp",
        success:function(result){
            var relateTmpl = doT.template($("#relate-tmpl").html());
            $("#relateBox").html(relateTmpl(result));
            $("#tagTitle").text(tagname);
            $("title").text(tagname);
            if(result.data.length==10){
                page ++;
                more = true;
            }else{
                more = false;
            }
        }
    });
    function getList(curpage){
        ajaxtime = false;
        $.ajax({
            url:ApiUrl+"/index.php?act=index&op=gettagdetail&client_type=ios&tag_name="+tagname+"&curpage="+curpage,
            type:"get",
            dataType:"jsonp",
            success:function(result){
                var relateTmpl = doT.template($("#relate-tmpl").html());
                $("#relateBox").append(relateTmpl(result));
                if(result.data.length==10){
                    page ++;
                    more = true;
                }else{
                    more = false;
                }
                ajaxtime = true;
            }
        });
    }
    $(window).scroll(function () {
        var doc_h = $(document).height();
        var win_h = $(window).height();
        var scroll_top = $(window).scrollTop();
        if (scroll_top >= doc_h - win_h - 10) {
            if(more == true&&ajaxtime==true){
                getList(page);
            }
        }
    });


});