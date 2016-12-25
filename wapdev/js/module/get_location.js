if (typeof AGG == "undefined") {
    var AGG = {};
}

AGG.getLocation = {
    latAndLon: function (callback, error) {
        var that = this;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                    var latitude = position.coords.latitude;
                    var longitude = position.coords.longitude;
                    localStorage.setItem("latitude", latitude);
                    localStorage.setItem("longitude", longitude);
                    var data = {
                        latitude: latitude,
                        longitude: longitude
                    };
                    if (typeof callback == "function") {
                        callback(data);
                    }
                    //that.cityname(latitude,longitude,domTempe);
                },
                function () {
                    //that.setDefaultCity();
                    if (typeof error == "function") {
                        error();
                    }
                });
        } else {
            //that.setDefaultCity();
            if (typeof error == "function") {
                error();
            }
        }
    },
    weichatLatAndLon: function (callback, error) {
        var that = this;
        var timestamp = new Date().getTime() + "";
        timestamp = timestamp.substring(0, 10);
        var ranStr = randomString();
        var nurl = document.URL;

        function randomString(len) {
            len = len || 20;
            var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
            /****默认去掉了容易混淆的字符oOLl,9gq,Vv,Uu,I1****/
            var maxPos = $chars.length;
            var pwd = '';
            for (i = 0; i < len; i++) {
                pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
            }
            return pwd;
        }

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
                    jsApiList: ['checkJsApi',
                        'getLocation'
                    ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
                });
            },
            error: function () {
            }
        });
        wx.ready(function () {

            wx.getLocation({
                success: function (res) {
                    var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                    var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                    var speed = res.speed; // 速度，以米/每秒计
                    var accuracy = res.accuracy; // 位置精度
                    localStorage.setItem("latitude", latitude);
                    localStorage.setItem("longitude", longitude);
                    var data = {
                        latitude: latitude,
                        longitude: longitude
                    };
                    //that.cityname(latitude,longitude,domTempe);
                    if (typeof callback == "function") {
                        callback(data);
                    }
                },
                cancel: function () {
                    //这个地方是用户拒绝获取地理位置
                    //that.setDefaultCity();
                    if (typeof error == "function") {
                        error();
                    }
                }
            });

        });
        wx.error(function (res) {
            //that.setDefaultCity();
            if (typeof error == "function") {
                error();
            }
        });
    },
    cityname: function (latitude, longitude, callback) {
        $.ajax({
            url: 'http://api.map.baidu.com/geocoder/v2/?ak=btsVVWf0TM1zUBEbzFz6QqWF&callback=renderReverse&location=' + latitude + ',' + longitude + '&output=json&pois=1&coordtype=wgs84ll',
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                var province = data.result.addressComponent.province;
                var cityname = (data.result.addressComponent.city);
                var district = data.result.addressComponent.district;
                var street = data.result.addressComponent.street;
                var street_number = data.result.addressComponent.street_number;
                var formatted_address = data.result.formatted_address;
                //alert(formatted_address);
                localStorage.setItem("province", province);
                localStorage.setItem("cityname", cityname);
                localStorage.setItem("district", district);
                localStorage.setItem("street", street);
                localStorage.setItem("street_number", street_number);
                localStorage.setItem("formatted_address", formatted_address);
                //domTempe(cityname,latitude,longitude);
                var data = {
                    latitude: latitude,
                    longitude: longitude,
                    cityname: cityname
                };
                if (typeof callback == "function") {
                    callback(data);
                }

            }
        });
    },
    setDefaultCity: function (callback) {
        alert("获取地理位置失败！");
        //默认经纬度
        var latitude = "31.337882";
        var longitude = "120.616634";
        var cityname = "苏州市";
        localStorage.setItem("latitude", latitude);
        localStorage.setItem("longitude", longitude);
        localStorage.setItem("cityname", cityname);
        localStorage.setItem("province", "江苏省");
        localStorage.setItem("district", "虎丘区");
        localStorage.setItem("street", "珠江路");
        localStorage.setItem("street_number", "88号");
        localStorage.setItem("formatted_address", "江苏省苏州市虎丘区珠江路88号");
        var data = {
            latitude: latitude,
            longitude: longitude,
            cityname: cityname,
            district:'虎丘区'
        };
        if (typeof callback == "function") {
            callback(data);
        }
    },
    refresh: function (callback) {
        var that = this;
        //重新获取经纬度和城市街道并设置到localStorage
        that.latAndLon(
            function (data) {
            that.cityname(data.latitude, data.longitude, function (datas) {
                if (typeof callback == "function") {
                    callback();
                }
            });
        },
        function(){
            that.setDefaultCity(function(){
                if (typeof callback == "function") {
                    callback();
                }
            });
        });
    }
};
