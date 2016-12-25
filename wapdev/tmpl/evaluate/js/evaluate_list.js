$(function (){
    $(".jl_evalist_nav li").click(function(){
        $(".jl_evalist_nav li").removeClass("on");
        $(this).addClass("on");
    });
});

var this_goodId=request("good_id");
var this_curPage=1;

var hasmore;

$(window).ready(function(e){
	
	//$(".jl_evalist ul li").remove();
	$(".list_showTips").css("display","none");

	var wHeight=$(window).height();
	$(".img-lay").css("line-height",wHeight+"px");
	
    getAjax(this_goodId,this_curPage);
	
	$(window).scroll(function(){
		scrollForData(this_goodId,this_curPage);
	});
});

function scrollForData(goodId,curpage){
	var windowHeight=$(window).height();
	var documentHeight=$(document).height();
	var scroll_top=$(window).scrollTop();
	
	if(scroll_top==documentHeight-windowHeight){			
		if(hasmore!=0){
			this_curPage=curpage+1;
		
			getAjax(goodId,this_curPage);
		}else{
			$(".list_showTips").css("display","block");
		}
	}
}

function getAjax(goodId,curpage){
	$.ajax({
		url:ApiUrl+"/index.php?act=unlimited_invitation&op=get_evaluate_goods_list_v2&client_type=wap&curpage="+curpage+"&geval_goodsid="+goodId,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(data){
			if(data.code==200){
				
				hasmore=$(data.data.evaluate_list).length;
				
				//$("#header h2").text("评价("+data.data.length+")");

				$(data.data.evaluate_list).each(function(index, thisData) {
					var subImgDiv="";
					$(thisData.images).each(function(index, thisImgSrc) {
                        subImgDiv=subImgDiv+"<span class='b-radius'><img src='"+thisImgSrc+"'></span>";
                    });
					
					var eDataAvator=thisData.geval_frommemberavara;
					if(eDataAvator=="" || eDataAvator==undefined || eDataAvator==null){
						eDataAvator="images/default@2x.png";
					}
					
					var geval_frommembername=thisData.geval_frommembername;
					var isNM=thisData.geval_isanonymous;

					if(isNM==1){
						geval_frommembername="匿名";
					}
					
					var subDiv="<li><div class='eListHead'><div class='eAvatorBox'><img src='"+eDataAvator+"'/></div><div class='eNameBox'><div class='eNameBoxSpan1'>"+ geval_frommembername +"</div><div class='eNameBoxSpan2'>"+(thisData.geval_addtime).split(' ')[0]+"</div></div><div class='eStarBox'><img src='images/ic_star_gray@2x.png'/><img src='images/ic_star_gray@2x.png'/><img src='images/ic_star_gray@2x.png'/><img src='images/ic_star_gray@2x.png'/><img src='images/ic_star_gray@2x.png'/></div></div><div class='eva_txt'>"+thisData.geval_content+"</div><div class='eva_img'>"+subImgDiv+"</div><div style='display:none;' class='goodScoreL'>"+thisData.geval_scores+"</div></li>";

					$(".jl_evalist ul").append(subDiv);

					clickForBigImg();
                });
				
				$(".jl_evalist ul li").each(function(index, element){
					var scoreStar=parseInt($(this).find(".goodScoreL").text());
					
					//alert(scoreStar);
					var starIndex=0;
					$(this).find(".eStarBox img").each(function(index, element) {
						if(starIndex<scoreStar){
							$(this).attr("src","images/ic_star_orange@2x.png");
							starIndex++;
						}
					});

				});
			}	
		}
	});
}

//点击评论列表图片放大
function clickForBigImg(){
	$(".eva_img img").click(function(){
		var thisSrc=$(this).attr("src");
		$(".img-lay").css("display","block");

		$(".img-box").attr("src",thisSrc);

		$("body").css("overflow","hidden");
	});

	$(".img-lay").click(function(){
		$(".img-lay").css("display","none");
		$(".img-box").attr("src","");

		$("body").css("overflow","");
	});
}

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