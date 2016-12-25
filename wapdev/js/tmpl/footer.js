$(function (){
    var headTitle = document.title;
    var tmpl = '<div class="footer">'
        		+'	<div class="jl-fnav">'
				+'		<a href="'+WapSiteUrl+'/index.html" class="quarter">'
				+'			<span class="i-home"></span>'
				+'			<p>商城</p>'
				+'		</a>'
				+'		<a href="'+WapSiteUrl+'/tmpl/productClass.html" class="quarter">'
				+'			<span class="i-type"></span>'
				+'			<p>分类</p>'
				+'		</a>'
				+'		<a href="javascript:;" class="quarter" id="shopCartBtn">'
				+'			<span class="i-cart"></span>'
				+'			<p>购物车</p>'
				+'		</a>'
				+'	</div>'
    			+'</div>';
	var render = template.compile(tmpl);
	var html = render();
	$("#footer").html(html);
    var key = getcookie('key');
	$('#logoutbtn').click(function(){
		var username = getcookie('username');
		var key = getcookie('key');
		var client = 'wap';
		$.ajax({
			type:'get',
			url:ApiUrl+'/index.php?act=logout',
			data:{username:username,key:key,client:client},
			success:function(result){
				if(result){
					delCookie('username');
					delCookie('key');
					location.href = WapSiteUrl+'/tmpl/member/login.html';
				}
			}
		});
	});
	$(".main").css("padding-bottom","56px")
	//当前页面
	if(headTitle == "产品分类"){
		$(".i-type").parent().addClass("current");
	}else if(headTitle == "购物车列表"){
		$(".i-cart").parent().addClass("current");
	}else if(headTitle == "商城首页"){
		$(".i-home").parent().addClass("current");
	}


	$("#shopCartBtn").on("click",function(e){
		var loginStatusKey = getcookie('key');
		if(loginStatusKey==''){
			window.location.href=WapSiteUrl+"/tmpl/member/login.html";
		}else{
			window.location.href=WapSiteUrl+'/tmpl/shoppingCart.html';
		}
		return false;
	});

	
});

$(function(){
	var type=getcookie("type");

	/*if(type=="ios"){
	 $("#header").hide();
	 $("#footer").hide();
	 $(".main").css("padding","0");
	 }else if(type=="android"){
	 $("#header").hide();
	 $("#footer").hide();
	 $(".main").css("padding","0");
	 }*/
});




//获取url参数
function request(paras)
	    { 
	        var url = location.href; 
	        url=decodeURI(url);
	        var paraString = url.substring(url.indexOf("?")+1,url.length).split("&"); 
	        var paraObj = {}; 
	        for (var i=0; j=paraString[i]; i++){ 
	        paraObj[j.substring(0,j.indexOf("=")).toLowerCase()] = j.substring(j.indexOf("=")+1,j.length); 
	        } 
	        var returnValue = paraObj[paras.toLowerCase()]; 
	        if(typeof(returnValue)=="undefined"){ 
	        return ""; 
	        }else{ 
	        return returnValue; 
	        } 
	    }