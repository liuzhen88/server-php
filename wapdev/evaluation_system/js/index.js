if (typeof ev == "undefined") {
    var ev = {};
}

$(function () {
    ev.index={
        init:function(){
            var self=this;

            self.resizeBg();
            self.openSelect();
            self.openAddEvLay();
            self.openLookEvMain();

            $(window).resize(function(){
                self.resizeBg();
            });
        },
        resizeBg:function(){
            $(".add-ev-box").css("margin-top",($(window).height()-300)/2);
            $(".tb-limit-width").css("width",$("body").width()*0.8-330);
            $(".tb-limit-width-e").css("width",$("body").width()*0.8-580);
        },
        openSelect:function(){
            $(".index-user-box").click(function(){
                if($(".select-lay-box").css("display")=="none"){
                    $(".index-select").addClass("rotate-revert");
                    $(".select-lay-box").show();
                }else{
                    $(".index-select").removeClass("rotate-revert");
                    $(".select-lay-box").hide();
                }

            });
        },
        openAddEvLay:function(){
            $(".add-ev").click(function(){
                $(".add-ev-box-lay").show();
            });
            $(".btn-lay-cancel").click(function(){
                $(".add-ev-box-lay").hide();
            });
        },
        openLookEvMain:function(){
            $(".look-ev").click(function(){
                $(".index-main").hide();
                $(".look-ev-main").show();
            });
            $(".look-ev-btn-back").click(function(){
                $(".index-main").show();
                $(".look-ev-main").hide();
            });
        }
    }

    ev.index.init();

});

