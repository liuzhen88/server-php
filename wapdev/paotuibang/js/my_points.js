$(document).ready(function(){
    var key=getcookie("key");
    if(key==""){
        window.location.href = WapSiteUrl + '/aidatui/login.html';
    }else {
        $.ajax({
            url: ApiUrl + "/index.php?act=member_predeposit&op=index&key=" + key + "&no_list=0&curpage=1&client_type=wap",
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if (data.code == 200) {
                    var orderDetails = doT.template($("#orderDetails").html());
                    $(".points-list").html(orderDetails(data.data.datas));
                    $('.pointsNum').text(data.data.datas.predeposit);
                    if(parseFloat(data.data.datas.predeposit)==0){
                        $('.pointsBtn').on('click',function(){
                            $('#message').css('bottom','60px');
                            setTimeout(function(){
                                $('#message').css('bottom','-56px');
                            },2000);
                        });
                    }else{
                        $('.pointsBtn').on('click',function(){
                            $('.zhezhao').show();
                            $('#phone-tip').show();
                        });
                    }
                    $('#phone-call').on('click',function(){
                        $('.zhezhao').hide();
                        $('#phone-tip').hide();
                    });
                    $('#phone-cancel').on('click',function(){
                        $('.zhezhao').hide();
                        $('#phone-tip').hide();
                    });
                }
            }
        });
    }

});
function get_time(this_time){
    Date.prototype.format = function(format) {
        var date = {
            "M+": this.getMonth() + 1,
            "d+": this.getDate(),
            "h+": this.getHours(),
            "m+": this.getMinutes(),
            "s+": this.getSeconds(),
            "q+": Math.floor((this.getMonth() + 3) / 3),
            "S+": this.getMilliseconds()
        };
        if (/(y+)/i.test(format)) {
            format = format.replace(RegExp.$1, (this.getFullYear() + '').substr(4 - RegExp.$1.length));
        }
        for (var k in date) {
            if (new RegExp("(" + k + ")").test(format)) {
                format = format.replace(RegExp.$1, RegExp.$1.length == 1
                    ? date[k] : ("00" + date[k]).substr(("" + date[k]).length));
            }
        }
        return format;
    };
    var cc=new Date(parseInt(this_time)*1000);
    var aa=cc.format('yyyy-MM-dd h:m:s');
    return aa;
}
