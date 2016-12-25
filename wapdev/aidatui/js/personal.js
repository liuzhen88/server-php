$(function(){
    app.personal ={
        init:function(){
            app.checkLogin();
            FastClick.attach(document.body);
            this.getPersonalInfo();
            this.bindEvent();
        },
        getPersonalInfo:function(){
            $.ajax({
                url: ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+getcookie("key"),
                type: "get",
                dataType: "jsonp",
                success: function (data) {
                    if(data.code==200){
                        $(".user-name").text(data.data.member_info.nick_name);
                        $(".user-img img").attr("src",data.data.member_info.avator);
                    }else if(data.code==80001){
                        alert(data.message);
                        window.location.href = WapSiteUrl + "/aidatui/login1.html";
                    }else{
                        alert(data.message);
                    }
                }
            });
        },
        logout:function(){
            if (confirm("你确定退出吗？")) {
                $.ajax({
                    url:ApiUrl+"/index.php?act=member_logout&client_type=wap&op=index&key="+getcookie("key"),
                    type:"get",
                    dataType:"jsonp",
                    jsonp:"callback",
                    success: function(data){
                        delCookie('key');
                        window.location.href=WapSiteUrl+"/aidatui/login1.html";
                    }
                });
            }
        },
        bindEvent:function(){
            var self = this;
            $("#logout").click(function(){
                self.logout();
            });
        }
    };

    app.personal.init();

});