$(function(){
    var timestamp=new Date().getTime()+"";
    timestamp=timestamp.substring(0,10);
    var ranStr=randomString();
    var nurl=document.URL;

    var shareUrl=WapSiteUrl+"/aidatui/index.html";//这是分享出去的url，根据实际情况修改
    var goodsImageUrl=WapSiteUrl+"/aidatui/img/ptb_logo.png";//这是分享的图片，可以根据实际情况修改
    var shareTitle="跑腿邦";//这是分享的标题，可以根据实际情况修改
    var shareDesc="跑腿邦，您身边的移动商店~";//这是分享出去的描述，可以根据实际情况修改

    nurl=nurl.replace(/&/g,",,,");
    nurl=encodeURI(nurl);

    function randomString(len) {
        len = len || 20;
        var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
        var maxPos = $chars.length;
        var pwd = '';
        for (i = 0; i < len; i++) {
            pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
        }
        return pwd;
    }

    $.ajax({
        url:"http://www.51aigegou.cn/aigegou/ws/webGetTicketSignCommon?timestamp="+timestamp+"&url="+nurl+"&nonceStr="+ranStr,
        type:'get',
        dataType:'jsonp',
        cache : false,
        jsonp:"jsonpcallback",
        success:function(data){
            wx.config({
                debug: false,
                appId: 'wxa0641282049ed265',
                timestamp:timestamp,
                nonceStr: ranStr,
                signature: data.sign.toLowerCase(),
                jsApiList: [    'checkJsApi',
                    'onMenuShareTimeline',
                    'onMenuShareAppMessage',
                    'onMenuShareQQ',
                    'onMenuShareQZone',
                    'getLocation'
                ]
            });
        },
        error:function(){
            console.log("错了");
        }
    });
    wx.ready(function(){
        wx.getLocation({
            type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
            success: function (res) {
                var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                var speed = res.speed; // 速度，以米/每秒计
                var accuracy = res.accuracy; // 位置精度
                $.ajax({
                    url: 'http://api.map.baidu.com/geocoder/v2/?ak=btsVVWf0TM1zUBEbzFz6QqWF&callback=renderReverse&location=' + latitude + ',' + longitude + '&output=json&pois=0&coordtype=wgs84ll',
                    type: "get",
                    dataType: "jsonp",
                    jsonp: "callback",
                    success: function (data) {
                        if (data.status == 0) {
                            // alert(data.result.sematic_description);
                            sessionStorage.setItem("wx_lat", data.result.location.lat);
                            sessionStorage.setItem("wx_lng", data.result.location.lng);
                            sessionStorage.setItem("wx_sematic_description", data.result.sematic_description);

                        }
                    }
                });
            }



        });
        wx.onMenuShareAppMessage({
            title: shareTitle,
            desc: shareDesc,
            link: shareUrl,
            imgUrl: goodsImageUrl,
            trigger: function (res) {
            },
            success: function (res) {
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
        wx.onMenuShareTimeline({
            title: shareDesc,
            link: shareUrl,
            imgUrl: goodsImageUrl,
            trigger: function (res) {
            },
            success: function (res) {
            },
            cancel: function (res) {
            },
            fail: function (res) {
                alert(JSON.stringify(res));
            }
        });
        wx.onMenuShareQQ({
            title: shareTitle,
            desc: shareDesc,
            link: shareUrl,
            imgUrl: goodsImageUrl,
            success: function () {

            },
            cancel: function () {

            }
        });
        wx.onMenuShareQZone({
            title: shareTitle,
            desc: shareDesc,
            link: shareUrl,
            imgUrl: goodsImageUrl,
            success: function () {

            },
            cancel: function () {

            }
        });
    });
    wx.error(function(res){
        //alert("error");
    });
});