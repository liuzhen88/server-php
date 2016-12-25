var answer_id;
var del_this; //删除评论对象
var replyNum;//评论条数
var replyNumNow; //追加后的评论条数
var aggShareMore = {
    init:function(){
        this.bindEvent();
        this.weichatInit();
    },
    weichatInit: function () {
        function randomString(len) {
            var len = len || 20;
            var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
            var maxPos = $chars.length;
            var pwd = '';
            for (var i = 0; i < len; i++) {
                pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
            }
            return pwd;
        }

        var timestamp = new Date().getTime() + "";
        timestamp = timestamp.substring(0, 10);
        var ranStr = randomString();
        var nurl = document.URL;

        $.ajax({
            async: false,
            url: 'http://wx.51aigegou.com/aigegou/ws/webGetTicketSignJsonP',
            type: 'get',
            dataType: 'jsonp',
            cache: false,
            jsonp: 'jsonpcallback',
            data: {
                'timestamp': timestamp,
                'nonceStr': ranStr,
                'url': nurl
            },
            success: function (data) {
                var si = data.sign.toLowerCase();
                wx.config({
                    debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                    appId: 'wxa0641282049ed265', // 必填，公众号的唯一标识
                    timestamp: timestamp, // 必填，生成签名的时间戳
                    nonceStr: ranStr, // 必填，生成签名的随机串
                    signature: si,// 必填，签名，见附录1
                    jsApiList: [
                        'checkJsApi',
                        'onMenuShareTimeline',
                        'onMenuShareAppMessage',
                        'onMenuShareQQ',
                        'onMenuShareWeibo',
                        'onMenuShareQZone'
                    ]
                });
            },
            error: function () {
            }
        });

    },
    weichatShare:function(data){
        var title = data.title;
        var link = data.link;
        var imgUrl = data.imgUrl;
        var desc = data.desc;
        var that = this;
        wx.ready(function () {
            wx.onMenuShareTimeline({
                title: title,
                link: link,
                imgUrl: imgUrl,
                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                    //alert('用户点击分享到朋友圈');
                },
                success: function (res) {
                    //alert('已分享');
                    that.weichatShareOk();
                },
                cancel: function (res) {
                    //alert('已取消');
                    that.weichatShareOk();
                },
                fail: function (res) {
                    //alert(JSON.stringify(res));
                }
            });
            //alert('已注册获取“分享到朋友圈”状态事件');
            wx.onMenuShareAppMessage({
                title: title,
                desc: desc,
                link: link,
                imgUrl: imgUrl,
                trigger: function (res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                    //alert('用户点击发送给朋友');
                },
                success: function (res) {
                    //alert('已分享');
                    that.weichatShareOk();
                },
                cancel: function (res) {
                    //alert('已取消');
                    that.weichatShareOk();
                },
                fail: function (res) {
                    alert(JSON.stringify(res));
                }
            });
            //alert('已注册获取“发送给朋友”状态事件');
            wx.onMenuShareQQ({
                title: title,
                desc: desc,
                link: link,
                imgUrl: imgUrl,
                trigger: function (res) {
                    //alert('用户点击分享到QQ');
                },
                complete: function (res) {
                    //alert(JSON.stringify(res));
                    that.weichatShareOk();
                },
                success: function (res) {
                    //alert('已分享');
                    that.weichatShareOk();
                },
                cancel: function (res) {
                    //alert('已取消');
                    that.weichatShareOk();
                },
                fail: function (res) {
                    alert(JSON.stringify(res));
                }
            });
            //alert('已注册获取“分享到 QQ”状态事件');
            wx.onMenuShareWeibo({
                title: title,
                desc: desc,
                link: title,
                imgUrl: imgUrl,
                trigger: function (res) {
                    //alert('用户点击分享到微博');
                },
                complete: function (res) {
                    alert(JSON.stringify(res));
                    that.weichatShareOk();
                },
                success: function (res) {
                    //alert('已分享');
                    that.weichatShareOk();
                },
                cancel: function (res) {
                    //alert('已取消');
                    that.weichatShareOk();
                },
                fail: function (res) {
                    alert(JSON.stringify(res));
                }
            });
            //alert('已注册获取“分享到微博”状态事件');
            wx.onMenuShareQZone({
                title: title,
                desc: desc,
                link: link,
                imgUrl: imgUrl,
                trigger: function (res) {
                    //alert('用户点击分享到QZone');
                },
                complete: function (res) {
                    alert(JSON.stringify(res));
                    that.weichatShareOk();
                },
                success: function (res) {
                    //alert('已分享');
                    that.weichatShareOk();
                },
                cancel: function (res) {
                    //alert('已取消');
                    that.weichatShareOk();
                },
                fail: function (res) {
                    alert(JSON.stringify(res));
                }
            });
            //alert('已注册获取“分享到QZone”状态事件');

        });
    },
    bindEvent:function(){
        var that = this;
        $(".sb_more .icon").on("click", function () {
            $(".dialog-mask").show().css("opacity", "1");
            $(".dialog-share-cnt").hide();
            $(".dialog-sheet-cnt").show().height("140px");
            var themeid = $(this).attr("data-themeid");
            var thats = $(this);
            if( (getcookie('key')=='')||(getcookie('user_id')=='')){
                $(".dialog-sheet-cnt .inform,.dialog-sheet-cnt .delete").on("click",function(){
                    window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                });
                $(".dialog-sheet-cnt .inform").html("举报");
            }else{
                var informLink = "share_report.html?theme_id="+themeid+'&comment='+$(this).attr("data-content");
                $(".dialog-sheet-cnt .inform").html('<a href="">举报</a>');
                $(".dialog-sheet-cnt .inform  a").attr("href",informLink);
                var memberid = $(this).attr("data-memberid");
                if(getcookie('user_id')== memberid ){
                    $(".dialog-sheet-cnt").height("95px");
                    $(".dialog-sheet-cnt .delete").show();
                    $(".dialog-sheet-cnt .inform").hide();
                }else{
                    $(".dialog-sheet-cnt").height("95px");
                    $(".dialog-sheet-cnt .inform").show();
                    $(".dialog-sheet-cnt .delete").hide();
                }
            }
            $(".dialog-sheet-cnt .delete").click(function(){
                $.ajax({
                    url: ApiUrl + "/index.php?act=circle_info&op=delTheme&key="+getcookie('key')+"&theme_id="+themeid+"&client_type=wap",
                    type: 'get',
                    dataType: 'jsonp',
                    success: function(result) {
                        //if(result.code==200){
                        //    alert(result.data);
                        //}else{
                        //    alert(result.message);
                        //}
                        $(".dialog-sheet-cnt .cancel").trigger('click');
                        console.log(thats.parents(".share_c"));
                        thats.parents(".share_c").remove();
                    }
                });

            });

        });
        $(".dialog-sheet-cnt .cancel").on("click", function () {
            $(".dialog-mask").hide().css("opacity", "0");
            $(".dialog-sheet-cnt").height("0");
            $(".dialog-weichat-share").hide();
        });
        $(".sb_share .icon").on("click", function () {
            $(".dialog-mask").show().css("opacity", "1");
            $(".dialog-sheet-cnt").hide();
            $(".dialog-share-cnt").show().height("190px");

            var sharedata = {
                title: $(this).attr("data-title"),
                link: $(this).attr("data-link"),
                imgUrl: $(this).attr("data-imgUrl"),
                desc:$(this).attr("data-desc")
            };
            that.weichatShare(sharedata);

        });
        $(".dialog-share-cnt .cancel").on("click", function () {
            $(".dialog-mask").hide().css("opacity", "0");
            $(".dialog-share-cnt").height("0");
            $(".dialog-weichat-share").hide();
        });

        $(".dialog-mask").click(function(){
            $(".dialog-mask").hide().css("opacity", "0");
            $(".dialog-share-cnt").height("0");
            $(".dialog-sheet-cnt").height("0");
            $(".dialog-weichat-share").hide();
            return false;
        });
        $(".share-icon-wrap li").on("click",function(){
            //$(".dialog-mask").show().css("opacity", "1");
            $(".dialog-weichat-share").show();
        });


    },
    weichatShareOk:function(){
        $(".dialog-mask").hide().css("opacity", "0");
        $(".dialog-share-cnt").height("0");
        $(".dialog-weichat-share").hide();
    }
};
$(function () {
    theme_id=request('theme_id');
    FastClick.attach(document.body);
    var page = 1;
    var more = true ;
    var ajaxtime = true;
    $.ajax({
        url: ApiUrl + "/index.php?act=index&op=getContents&client_type=wap&theme_id="+theme_id+"&key="+key,
        type: 'get',
        dataType: 'jsonp',
        success: function(result) {
            if (result.code == 200) {
                var shareDetailsDoTmpl = doT.template($("#shareDetailstmpl").html());
                $("#shareDetails").html(shareDetailsDoTmpl(result));
                aggShareMore.init();
                $(".user_name a").bind("click", function (e) {
                    e.stopPropagation();
                });
                if(result.data.theme_reply.length==10){
                    page ++;
                    more = true;
                }else{
                    more = false;
                }
            }
            canceldel();
        }
    });
    function replyList(curpage){
        ajaxtime = false;
        $.ajax({
            url: ApiUrl + "/index.php?act=index&op=getThemeReply&client_type=wap&theme_id="+theme_id+"&key="+key+'&curpage='+curpage,
            type: 'get',
            dataType: 'jsonp',
            success: function(result) {
                if (result.code == 200) {
                    var replyMoreTmpl = doT.template($("#replyMoretmpl").html());
                    $("#shareReply").append(replyMoreTmpl(result.data));
                    $(".user_name a").bind("click", function (e) {
                        e.stopPropagation();
                    });
                    if(result.data.length==10){
                        page ++;
                        more = true;
                    }else{
                        more = false;
                    }
                    ajaxtime = true;
                }
                canceldel();
            }
        });
    }
    $(window).scroll(function () {
        var doc_h = $(document).height();
        var win_h = $(window).height();
        var scroll_top = $(window).scrollTop();
        if (scroll_top >= doc_h - win_h) {
            if(more == true&&ajaxtime==true){
                replyList(page);
            }
        }
    });
});

//评论帖子
function replysend(obj){
    if(key==""||user_id==""){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        var replay_contents=$('.reply-txt').val();
        replyNum=parseInt($('.replyNum').text());
        $.ajax({
            url: ApiUrl + "/index.php?act=circle_info&op=commentReply&key="+key+"&client_type=wap&theme_id="+theme_id+"&replay_contents="+replay_contents,
            type: 'get',
            dataType: 'jsonp',
            success: function(result) {
                if (result.code == 200) {
                    if(replyNum == 0){
                        $("#shareReply").show();
                    }
                    var shareReplyDoTmpl = doT.template($("#shareReplytmpl").html());
                    $("#shareReply").prepend(shareReplyDoTmpl(result));
                    $(obj).prev('input').val('');
                    $('.replyNum').text(replyNum+1);

                }else{
                    alert(result.message);
                }
            }
        });
    }
}
//回复评论
function reply(obj){
    if(key==""||user_id==""){
        window.location.href=WapSiteUrl+"/tmpl/member/login.html";
    }else{
        answer_id=$(obj).attr('data-replyid');
        var answer_name=$(obj).attr('data-name');
        var member_id=$(obj).attr('data-userid');
        if(member_id==user_id){
            del_this=$(obj);
            $(".dialog-mask").show().css("opacity", "1");
            $(".dialog-reply-cnt").show().height("102px");
        }else{
            $(".reply-txt").attr('placeholder','回复 '+answer_name).focus();
            $("#replybt1").hide();
            $("#replybt2").css("display","inline-block");
        }
    }
}
function replysend2(obj){
    var replay_contents=$('.reply-txt').val();
    replyNum=parseInt($('.replyNum').text());
    $.ajax({
        url: ApiUrl + "/index.php?act=circle_info&op=commentReply&key="+key+"&client_type=wap&theme_id="+theme_id+"&replay_contents="+replay_contents+"&answer_id="+answer_id,
        type: 'get',
        dataType: 'jsonp',
        success: function(result) {
            if (result.code == 200) {
                var shareReplyDoTmpl = doT.template($("#shareReplytmpl2").html());
                $("#shareReply").prepend(shareReplyDoTmpl(result));
                $(".reply-txt").val('').attr('placeholder','评论');
                $("#replybt2").hide();
                $("#replybt1").show();
                $('.replyNum').text(replyNum+1);
            }else{
                alert(result.message);
            }
        }
    });
}
//删除评论
function delreply(obj){
    replyNum=parseInt($('.replyNum').text());
    $.ajax({
        url: ApiUrl + "/index.php?act=circle_info&op=delreplay&client_type=wap&key="+key+"&theme_id="+theme_id+"&reply_id="+answer_id,
        type: 'get',
        dataType: 'jsonp',
        success: function(result) {
            if (result.code == 200) {
                $(".dialog-mask").css("opacity", "0").hide();
                $(".dialog-reply-cnt").height("0").hide();
                replyNumNow = replyNum - 1;
                $('.replyNum').text(replyNumNow);
                if(replyNumNow == 0){
                    $("#shareReply").hide();
                }
                del_this.parents('dl').remove();
            }else{
                alert(result.message);
            }
        }
    });
}
//取消删除
function canceldel(){
    $(".dialog-reply-cnt .cancel").on("click", function () {
        $(".dialog-mask").hide().css("opacity", "0");
        $(".dialog-reply-cnt").height("0");
    });
    $(".dialog-mask").click(function(){
        $(".dialog-mask").hide().css("opacity", "0");
        $(".dialog-reply-cnt").height("0");
    });
}

