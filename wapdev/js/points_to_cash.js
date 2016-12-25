$(document).ready(function(){
    var key=getcookie("key");

    var changeBankId=request("changeBankId");
    var changeBankName=request("changeBankName");
    var changeBankCardType=request("changeBankCardType");
    var changeBankNo=request("changeBankNo");
    var bankCardId;

    $.ajax({
        url:ApiUrl+"/index.php?act=member_bankcard&op=detail&key="+key+"&client_type=wap",
        type:"get",
        dataType:"jsonp",
        jsonp:"callback",
        success:function(data){
            if(data.code==200){
                if(changeBankName!="" && changeBankId!="" && changeBankNo!=""){
                    $("#bankName").html(changeBankName);
                    $("#last-card-number").html(changeBankNo);
                    $("#bank-card-type").text(changeBankCardType);
                    bankCardId=changeBankId;
                }else{
                    $("#bankName").html(data.data.pdc_bank_name);

                    var pdc_bank_no=data.data.pdc_bank_no;
                    var last_number=pdc_bank_no.substring(pdc_bank_no.length-4,pdc_bank_no.length);
                    $("#last-card-number").html(last_number);

                    $("#bank-card-type").text(data.data.cardtype);
                    bankCardId=data.data.id;
                }
            }
        }
    });
    $.ajax({
        url: ApiUrl+"/index.php?act=member_index&op=index&key="+key+"&client_type=wap",
        type: "get",
        dataType: "jsonp",
        jsonp: "callback",
        success: function (data) {
            if(data.code==200){
                var predepoit=data.data.member_info.predepoit;
                $(".red").html(predepoit);
            }
        }
    });
    $(".submit").click(function(){
        var cashMoney=$("#cashMoney").val();
        var cashPassword=$("#cashPassword").val();
        if(cashMoney==''){
            alert("请输入提现金额");
        }else{
            if(cashPassword==''){
                alert("请输入提现密码");
            }else{
                $.ajax({
                    url:ApiUrl+"/index.php?act=member_predeposit&op=with_draw&key="+key+"&amount="+cashMoney+"&paypwd="+cashPassword+"&client_type=wap&bank_id="+bankCardId,
                    type:"get",
                    dataType:"jsonp",
                    jsonp:"callback",
                    success:function(data){
                        if(data.code==200){
                            alert("提现成功,等待平台打款");
                            //window.location.href=WapSiteUrl+"/my_points_main.html";
                        }else if(data.code==80001){
                            alert(data.message);
                            window. location.href = WapSiteUrl + '/tmpl/member/login.html';
                        }else{
                            alert(data.message);
                        }
                    }
                });
            }
        }
    });
    $(".change-bank").click(function(){
        window.location.href=WapSiteUrl+"/bank_card.html?from_change_card=1";
    });
});