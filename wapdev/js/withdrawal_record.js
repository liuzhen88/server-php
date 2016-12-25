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
            url: ApiUrl + "/index.php?act=member_predeposit&op=get_cash_apply_list&key=" + key + "&client_type=wap&curpage="+curpage,
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if (data.code == 200) {
                    var orderDetails = doT.template($("#orderDetails").html());
                    $("#goodsMain").append(orderDetails(data.data));

                    if(curpage==1 && data.data.length<=0){
                        $(".no-data-class").show();
                    }else{
                        $(".no-data-class").hide();
                    }

                    if(data.data.length<=0){
                        $(".add-data-class").text("没有数据了~");
                    }

                    $(".points-details").on("click",function(){
                        var pdc_payment_state=$(this).find(".pdc_payment_state").text();
                        var pdc_amount=$(this).find(".pdc_amount").text();
                        var pdc_add_time=$(this).find(".pdc_add_time").text();
                        var pdc_deal_time=$(this).find(".pdc_deal_time").text();
                        var pdc_payment_time=$(this).find(".pdc_payment_time").text();
                        var pdc_message=$(this).find(".pdc_message").text();
                        var icon=$(this).find(".icon").text();
                        var cardtype=$(this).find(".cardtype").text();
                        var pdc_bank_name=$(this).find(".pdc_bank_name").text();
                        var pdc_bank_no=$(this).find(".pdc_bank_no").text();

                        window.location.href=WapSiteUrl + '/withdrawal_details.html?'+
                            'pdc_payment_state='+pdc_payment_state+
                            '&pdc_amount='+pdc_amount+
                            '&pdc_add_time='+pdc_add_time+
                            '&pdc_deal_time='+pdc_deal_time+
                            '&pdc_payment_time='+pdc_payment_time+
                            '&pdc_message='+pdc_message+
                            '&icon='+icon+
                            '&cardtype='+cardtype+
                            '&pdc_bank_name='+pdc_bank_name+
                            '&pdc_bank_no='+pdc_bank_no;
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

function splitBankNum(bankNo){
    return bankNo.substr(bankNo.length-4,4);
}
