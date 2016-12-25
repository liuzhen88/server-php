window.onload=function() {
    var time = 3000;
    var myDate = new Date();
    var now = myDate.getTime();
    var t = localStorage.getItem("loginTime");
//        setTimeout(function(){
//            if(t != null && now -t < 1000*60*10){
//                window.location.href=WapSiteUrl+"/aidatui/index.html";
//                return;
//            }else{
//                getLocationPosition();
//            }
//        },time);
    setTimeout(function(){
        //localStorage.setItem("latitude",31.24821);
        //localStorage.setItem("longitude",120.650397);
        //localStorage.setItem("description","白金汉爵大酒店(吴中店)");
        localStorage.setItem("latitude",31.277874);
        localStorage.setItem("longitude",120.53535784528854);
        localStorage.setItem("description","今创启园内,财富广场北");
        window.location.href=WapSiteUrl+"/aidatui/index1.html?hash=123";
    },10000);
    getLocationPosition();
    function getLocationPosition() {
        var timestamp = new Date().getTime() + "";
        timestamp = timestamp.substring(0, 10);
        var ranStr = randomString();
        var nurl = document.URL;

        nurl = nurl.replace(/&/g, ",,,");
        nurl = encodeURI(nurl);

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
            url: "http://www.51aigegou.cn/aigegou/ws/webGetTicketSignCommon?timestamp=" + timestamp + "&url=" + nurl + "&nonceStr=" + ranStr,
            type: 'get',
            dataType: 'jsonp',
            cache: false,
            jsonp: "jsonpcallback",
            success: function (data) {
                wx.config({
                    debug: false,
                    appId: 'wxa0641282049ed265',
                    timestamp: timestamp,
                    nonceStr: ranStr,
                    signature: data.sign.toLowerCase(),
                    jsApiList: ['checkJsApi',
                        'onMenuShareTimeline',
                        'onMenuShareAppMessage',
                        'onMenuShareQQ',
                        'onMenuShareQZone',
                        'getLocation'
                    ]
                });
            },
            error: function () {
                console.log("错了");
            }
        });
        wx.ready(function () {
            console.log("微信sdk成功!");
            wx.getLocation({
                type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                success: function (res) {
                    var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                    var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                    var speed = res.speed; // 速度，以米/每秒计
                    var accuracy = res.accuracy; // 位置精度
                    var storage_latitude = localStorage.getItem("latitude");
                    var storage_longitude = localStorage.getItem("longitude");
                    $.ajax({
                        url: 'http://api.map.baidu.com/geocoder/v2/?ak=btsVVWf0TM1zUBEbzFz6QqWF&callback=renderReverse&location=' + latitude + ',' + longitude + '&output=json&pois=0&coordtype=wgs84ll',
                        type: "get",
                        dataType: "jsonp",
                        jsonp: "callback",
                        success: function (data) {
                            if (data.status == 0) {
                                latitude = data.result.location.lat;
                                longitude = data.result.location.lng;
                                var description = data.result.sematic_description;
                                sessionStorage.setItem("wx_lat", latitude);
                                sessionStorage.setItem("wx_lng", longitude);
                                sessionStorage.setItem("wx_sematic_description", description);
                                if (storage_latitude == "" || storage_longitude == "" || storage_latitude == null || storage_longitude == null||storage_latitude == 'null' || storage_longitude == 'null') {
                                    localStorage.setItem("latitude", latitude);
                                    localStorage.setItem("longitude", longitude);
                                    localStorage.setItem("description", description);
                                    window.location.href = WapSiteUrl + "/aidatui/index1.html";
                                } else {
                                    //如果localStorage里面经纬度不为空判断当前定位和用户设置的位置是否一致，不一致说明用户不在当前设置位置
                                    //计算经度相差度大约0.027偏差视为更换了地址，不在百度地图定位偏差内(3km)
                                    if (Math.abs(storage_longitude - longitude) >= 0.027 || Math.abs(storage_latitude - latitude) >= 0.027) {
                                        console.log("这是定位有了变化");
                                        window.location.href = WapSiteUrl + "/aidatui/index1.html?isChange=1";

                                    } else {
                                        console.log("这是定位没有变化");
                                        window.location.href = WapSiteUrl + "/aidatui/index1.html?isChange=0";
                                    }
                                }
                            } else {
                                alert('地址获取失败！');
                                window.location.href = "position1.html"
                            }
                        }
                    });
                    localStorage.setItem("loginTime", now);
                },
                error:function(){
                    alert('微信定位失败');
                }
            });
        });
        wx.error(function (res) {
            alert("error");
        });

    }
};