$(function () {
    FastClick.attach(document.body);

    $("#by-id-card").click(function(){
        window.location.href=WapSiteUrl+"/trans_pwd_card.html";
    });

    $("#by-phone").click(function(){
        window.location.href=WapSiteUrl+"/trans_pwd_phone.html";
    });
});

