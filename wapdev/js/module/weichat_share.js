if (typeof AGG == "undefined") {
    var AGG = {};
}


//微信分享方法SDK
AGG.weichatShareCustom = {
    init: function (data) {
        this.weichatInit(data);
    },
    weichatInit: function (shareData) {
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
        var self = this;
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
                self.weichatShare(shareData);

            },
            error: function () {
            }
        });

    },
    weichatShare: function (data) {
        var title = data.title;
        var link = data.link;
        var imgUrl = data.imgUrl;
        var desc = data.desc;
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

                },
                cancel: function (res) {
                    //alert('已取消');

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

                },
                cancel: function (res) {
                    //alert('已取消');

                },
                fail: function (res) {
                    //alert(JSON.stringify(res));
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

                },
                success: function (res) {
                    //alert('已分享');

                },
                cancel: function (res) {
                    //alert('已取消');

                },
                fail: function (res) {
                    //alert(JSON.stringify(res));
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

                },
                success: function (res) {
                    //alert('已分享');

                },
                cancel: function (res) {
                    //alert('已取消');

                },
                fail: function (res) {
                    //alert(JSON.stringify(res));
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
                    //alert(JSON.stringify(res));

                },
                success: function (res) {
                    //alert('已分享');

                },
                cancel: function (res) {
                    //alert('已取消');

                },
                fail: function (res) {
                    //alert(JSON.stringify(res));
                }
            });
            //alert('已注册获取“分享到QZone”状态事件');

        });
    }
};

