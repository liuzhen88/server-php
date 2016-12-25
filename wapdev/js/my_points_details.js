$(function(){
    var order_sn=request("order_sn");
    var serial_number=request("serial_number");
    var lg_add_time=request("lg_add_time");
    var lg_type_tip=request("lg_type_tip");
    var lg_av_amount=request("lg_av_amount");
    var lg_av_amount_type=request("lg_av_amount_type");

    if(order_sn!=""){
        $("#order_sn").show();
        $("#order_sn .on").text(order_sn);
    }
    if(serial_number!=""){
        $("#serial_number").show();
        $("#serial_number .on").text(serial_number);
    }
    if(lg_add_time!=""){
        $("#lg_add_time").show();
        $("#lg_add_time .on").text(lg_add_time);
    }
    if(lg_type_tip!=""){
        $("#lg_type_tip").show();
        $("#lg_type_tip .on").text(lg_type_tip);
    }
    if(lg_av_amount!=""){
        $("#lg_av_amount_box").show();
        $(".lg_av_amount_type").text(lg_av_amount_type);
        $(".lg_av_amount").text(lg_av_amount.substr(1,lg_av_amount.length-1)+"积分");
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
