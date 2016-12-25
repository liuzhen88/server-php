$(document).ready(function(){
   var key=getcookie("key");
    if(key==""){
        window. location.href = WapSiteUrl + '/tmpl/member/login.html';
    }else {
        $.ajax({
            url: ApiUrl + "/index.php?act=member_index&op=index&key=" + key + "&client_type=wap"+"&token_member_id="+getcookie("user_id"),
            type: "get",
            dataType: "jsonp",
            jsonp: "callback",
            success: function (data) {
                if (data.code == 200) {

                    $("#points-ye .ic-cont").text(data.data.member_info.predepoit);
                    $("#points-yhk .ic-cont").text(data.data.member_info.bank_count);

                    $(".btn_tx").click(function(){
                        var is_bind_bank_card = data.data.member_info.is_bind_bank_card;//是否绑定银行卡
                        if (is_bind_bank_card == 0) {//未绑定银行卡
                            window.location.href=WapSiteUrl+"/bind_bank_card.html";
                        }else{//已绑定银行卡
                            window.location.href=WapSiteUrl+"/points_to_cash.html";
                        }
                    });

                    $("#points-ye").click(function () {
                        window.location.href = WapSiteUrl + "/my_points.html";
                    });
                    $("#points-yhk").click(function () {
                        window.location.href = WapSiteUrl + "/bank_card.html";
                    });
                    $("#points-txjl").click(function () {
                        window.location.href = WapSiteUrl + "/withdrawal_record.html";
                    });

                } else if (data.code == 80001) {
                    alert(data.message);
                    window.location.href = WapSiteUrl + "/tmpl/member/login.html";
                } else {
                    alert(data.message);
                }
            }
        });
    }

});