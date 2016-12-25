$(function(){
    var curpage=1;
    $(document).ready(function(){
        var key=getcookie("key");
        if(key==""){
            window. location.href = WapSiteUrl + '/tmpl/member/login.html';
        }else {
            getDataByScroll();
        }

        $(window).on("scroll",function(){
            var doc_h = $(document).height();
            var win_h = $(window).height();
            var scroll_top = $(window).scrollTop();
            if (scroll_top >= doc_h - win_h) {
                $(".add-data-class").show();
                curpage++;
                getDataByScroll();
            }
        });

    });

    function getDataByScroll(){
        $.ajax({
            url: ApiUrl + "/index.php?act=member_predeposit&op=get_predeposit_change_list&key=" + key + "&client_type=wap&curpage="+curpage,
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if (data.code == 200) {
                    var orderDetails = doT.template($("#orderDetails").html());
                    $("#goodsMain").append(orderDetails(data.data.datas.predeposit_list));

                    if(curpage==1 && data.data.datas.predeposit_list.length<=0){
                        $(".no-data-class").show();
                    }else{
                        $(".no-data-class").hide();
                    }

                    if(data.data.datas.predeposit_list.length<=0){
                        $(".add-data-class").text("没有数据了~");
                    }

                    $(".points-details").on("click",function(){

                        var order_sn=$(this).find(".data_order_sn").text();
                        var serial_number=$(this).find(".data_serial_number").text();

                        var lg_add_time=$(this).find(".points-date").text();
                        var lg_type_tip=$(this).find(".store-details").text();

                        var lg_av_amount="";
                        var lg_av_amount_type="";
                        if($(this).find(".points-add").length>0){
                            lg_av_amount_type="入账";
                            lg_av_amount=$(this).find(".points-add").text();
                        }else{
                            lg_av_amount_type="支出";
                            lg_av_amount=$(this).find(".points-not-add").text();
                        }

                        window.location.href=WapSiteUrl + '/my_points_details.html?order_sn='+order_sn+'&serial_number='+serial_number+'&lg_add_time='+lg_add_time+'&lg_type_tip='+lg_type_tip+'&lg_av_amount='+lg_av_amount+'&lg_av_amount_type='+lg_av_amount_type;
                    });

                }else if(data.code == 80001){
                    alert(data.message);
                    window. location.href = WapSiteUrl + '/tmpl/member/login.html';
                }else{
                    alert(data.message);
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
    }
    var cc=new Date(parseInt(this_time)*1000);
    var aa=cc.format('yyyy-MM-dd hh:mm:ss');
    return aa;
}
