;$(function (){
    var headTitle = document.title;
    var foottmpl =['<div class="jl-fnav footer-o2o-js">',
        '<a href="'+WapSiteUrl+'/index_o2o.html" class="quarter"><span class="i-home"></span><p>首页</p></a>',
        '<a href="'+WapSiteUrl+'/nearby.html" class="quarter"><span class="i-type"></span><p>商家</p></a>',
        '<a href="'+WapSiteUrl+'/index.html" class="quarter"><span class="i-cart"></span> <p>商城</p></a>',
        '<a href="javascript:;" class="quarter" id="footerMine"><span class="i-mine"></span> <p>我的</p></a>',
        '</div>'].join('');
    $("#footer").html(foottmpl);


//'<a href="'+WapSiteUrl+'/share.html" class="quarter share-box"><span class="i-share"></span><p>发现</p></a>',


    //当前页面
    if((headTitle == "首页")||(headTitle == "爱个购")){
        $(".i-home").parent().addClass("current");
    }else if(headTitle == "商家"){
        $(".i-type").parent().addClass("current");
    }else if((headTitle == "商城首页")||(headTitle == "产品分类")){
        $(".i-cart").parent().addClass("current");
    }else if((headTitle == "个人中心")||(headTitle == "我的")){
        $(".i-mine").parent().addClass("current");
    }else if(headTitle == "发现"){
        $(".i-share").parent().addClass("current");
    }

    $("#footerMine").on("click",function(e){
        var loginStatusKey = getcookie('key');
        if(loginStatusKey==''){
            window.location.href=WapSiteUrl+"/tmpl/member/login.html";
        }else{
            window.location.href=WapSiteUrl+"/member.html";
        }
        return false;
    });

    var clientType=request("client_type")||getcookie('type');
    if(clientType=="android" || clientType.toLowerCase()=="ios"){
        $("#footer").css("display","none");
    }

});