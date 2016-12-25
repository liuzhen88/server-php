app.getLocation = {
    latAndLon: function (callback, error) {
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
                },
                function () {
                    if (typeof error == "function") {
                        error();
                    }
                });
        } else {
            if (typeof error == "function") {
                error();
            }
        }
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
                var sematic_description = data.result.sematic_description;
                var data = {
                    latitude: latitude,
                    longitude: longitude,
                    cityname: cityname,
                    street:street+street_number,
                    sematic_description:sematic_description
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
        var data = {
            latitude: '31.337882',
            longitude: '120.616634',
            cityname: '苏州市',
            district:'虎丘区',
            street:'珠江路88号',
            sematic_description:'珠江路88号'
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
