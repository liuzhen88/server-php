var beginX;
var lastX;
var this_curpage=1;
var nowIndex=0;

$(function () {
    FastClick.attach(document.body);

    if(key==""){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        clickComment();
    }

    $(".s-Mess-head .smh-box").click(function(){
        $(".s-Mess-head .smh-box").removeClass("smh-box-in");
        $(this).addClass("smh-box-in");
        var nIndexx=$(".s-Mess-head .smh-box").indexOf(this);

        $(".sm-list ul li").remove();
        this_curpage=1;

        if(nIndexx==0){
            clickComment();
        }else if(nIndexx==1){
            clickZan();
        }
    });

});

function clickComment(){
    $.ajax({
        url: ApiUrl + "/index.php?act=circle_info&op=getMyconments&key="+key+"&curpage="+this_curpage+"&client_type=wap",
        type: 'get',
        dataType: 'jsonp',
        success: function (result) {
            if (result.code == 200) {
                var fanslistDoTmpl = doT.template($("#commentlistTmpl").html());
                $(".sm-list ul").html(fanslistDoTmpl(result));
            }

            $(".sm-list ul").width($(window).width()+65);
            $(".sm-list ul li").width($(window).width()+65);
            $(".sm-text-box").width($(window).width()-150);
            $(".sm-text-name").width($(window).width()-270);
            $(".sm-text-box2 span").css("max-width",$(window).width()-170);
            $(".dataNull").width($(window).width());

            $(".sm-list ul li").each(function(){
                var touchIndex=$(".sm-list ul li").indexOf(this);
                var liObj=document.getElementsByClassName("sm-list_li")[touchIndex];

                touchStart(liObj,touchIndex);
                touchEnd(liObj,touchIndex);
            });

        }
    });
}

function clickZan(){
    $.ajax({
        url: ApiUrl + "/index.php?act=circle_info&op=getMylike&key="+key+"&curpage="+this_curpage+"&client_type=wap",
        type: 'get',
        dataType: 'jsonp',
        success: function (result) {
            if (result.code == 200) {
                var fanslistDoTmpl = doT.template($("#zanlistTmpl").html());
                $(".sm-list ul").html(fanslistDoTmpl(result));
            }

            $(".sm-list ul").width($(window).width()+65);
            $(".sm-list ul li").width($(window).width()+65);
            $(".sm-text-box").width($(window).width()-150);
            $(".sm-text-name").width($(window).width()-270);
            $(".dataNull").width($(window).width());

            $(".sm-list ul li").each(function(){
                var touchIndex=$(".sm-list ul li").indexOf(this);
                var liObj=document.getElementsByClassName("sm-list_li")[touchIndex];

                touchStart(liObj,touchIndex);
                touchEnd(liObj,touchIndex);
            });
        }
    });
}

function isComment(text){
    if(text=="0"){
        return "评论你";
    }else{
        return "回复你";
    }
}

function touchStart(objLi,index){
    objLi.addEventListener("touchstart",function(event) {
        event.preventDefault();
        beginX = event.targetTouches[0].screenX;
    },false);
}

function touchEnd(objLi,index){
    objLi.addEventListener("touchend",function(event){
        event.preventDefault();
        lastX=event.changedTouches[0].screenX;

        if(Number(lastX-beginX)<0){
            //左划操作
            //alert("left"+index);

            nowIndex=index;

            $(".sm-list_li").css("margin-left","0px");
            $(".sm-list_li").eq(index).css("margin-left","-65px");

        }else if(Number(lastX-beginX)>0){
            //右划操作
            //alert("right"+index);

            $(".sm-list_li").eq(index).css("margin-left","0px");
        }
    },false);
}

function delComment(obj,thisThemeId,thisReplyId){
    obj.parents(".sm-list_li").css("display","none");

    $.ajax({
        url: ApiUrl + "/index.php?act=circle_info&op=delMyconments&key="+key+"&theme_id="+thisThemeId+"&client_type=wap&reply_id="+thisReplyId,
        type: 'get',
        dataType: 'jsonp',
        success: function (result) {
            if (result.code == 200) {
                alert(result.data);
            }else{
                alert(result.message);
            }
        }
    });

}

function delZan(obj,thisThemeId,thisLikeId){
    obj.parents(".sm-list_li").css("display","none");

    $.ajax({
        url: ApiUrl + "/index.php?act=circle_info&op=delMylike&like_id="+thisLikeId+"&key="+key+"&theme_id="+thisThemeId+"&client_type=wap",
        type: 'get',
        dataType: 'jsonp',
        success: function (result) {
            if (result.code == 200) {
                alert(result.data);
            }else{
                alert(result.message);
            }
        }
    });

}

var myDateNow = new Date();
var myDNow=myDateNow.getFullYear()+""+(myDateNow.getMonth()+1)+""+myDateNow.getDate();

function changeTimeFormat(thisTime){

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

    var thisDate=new Date(parseInt(thisTime)*1000);
    var thisDateFor;

    var thisDForCom=thisDate.format('yyyyMMdd')+"";
    if(myDNow==thisDForCom){
        thisDateFor="今天 "+thisDate.format('h:m');
    }else{
        thisDateFor=thisDate.format('yyyy-MM-dd');
    }

    return thisDateFor;
}

