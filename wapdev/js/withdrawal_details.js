$(function(){
    $(document).ready(function(){
        var data={};
        data.pdc_payment_state=request("pdc_payment_state");
        data.pdc_amount=request("pdc_amount");
        data.pdc_add_time=request("pdc_add_time");
        data.pdc_deal_time=request("pdc_deal_time");
        data.pdc_payment_time=request("pdc_payment_time");
        data.pdc_message=request("pdc_message");
        data.icon=request("icon");
        data.cardtype=request("cardtype");
        data.pdc_bank_name=request("pdc_bank_name");
        data.pdc_bank_no=request("pdc_bank_no");

        var txDetails = doT.template($("#tx-details-tmpl").html());
        $(".tx-details").html(txDetails(data));

    });
});

function splitFirst(amount){
    return amount.substr(1,amount.length-1);
}

function get_time_1(this_time){
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
    var aa=cc.format('yyyy-MM-dd');
    return aa;
}

function get_time_2(this_time){
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
    var aa=cc.format('hh:mm:ss');
    return aa;
}
