$(function(){

    FastClick.attach(document.body);

    AGG.getLocation.refresh(function(){
        var city = localStorage.getItem("cityname");
        $(".now-city .city-name").text(city).attr("data-city",city);
    });

    var nowSetCity = (function(){
        if(localStorage.getItem("setcity")&&(localStorage.getItem("setcity")!='undefined')){
            return localStorage.getItem("setcity");
        }else{
            return localStorage.getItem("cityname");
        }
    })();
    $("#nowSetCity").text(nowSetCity);

    var selectDoTmpl = doT.template($("#select-tmpl").html());
    var cityboxDoTtmpl = doT.template($("#citybox-tmpl").html());
    $("#cityBox").html(cityboxDoTtmpl(cityListData));
    $(".select-box").html(selectDoTmpl(cityListData));

    $(".now-city .city-name").on("click",function(){
        var city = $(this).attr("data-city");
        //localStorage.setItem("cityname",city);
        window.location.href = WapSiteUrl + "/index_o2o.html?cityname=" + city;
    });
    $(".hot-city-cnt .city-box,.all-city-box li").on("click",function(){
        var city = $(this).attr("data-city");
        //localStorage.setItem("cityname",city);
        window.location.href = WapSiteUrl + "/index_o2o.html?cityname=" + city;
    });

});


