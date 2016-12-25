if (typeof ev == "undefined") {
    var ev = {};
}

$(function () {
    ev.login={
        init:function(){
            var self=this;

            self.resizeBg();
            $(window).resize(function(){
                self.resizeBg();
            });

            $(".login-btn").click(function(){
                window.location.href=WapSiteUrl+"/evaluation_system/index.html";
            });
        },
        resizeBg:function(){
            $(".main-con").css({
                width:$(window).width(),
                height:$(window).height(),
                background:"url('images/login-page.jpg') center center no-repeat",
                backgroundSize:$(window).width()+"px "+$(window).height()+"px"
            });
            $(".login-bg").css("padding-top",($(window).height()-146)/2);
        }
    }

    ev.login.init();

});

