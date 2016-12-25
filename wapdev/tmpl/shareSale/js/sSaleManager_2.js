// JavaScript Document

var wWidth=$(window).width();
var thisDis=request("is_dis");

$(window).ready(function(e) {
	getMemberInfo();

	getDrawAjax("month",thisDis);
	
	$("#fxLabalHead ul li").click(function(){

		$("#fxLabalHead ul li").removeClass("on");
		$(this).addClass("on");

		var indexThis=$("#fxLabalHead ul li").index($(this));

		if(indexThis==0){
			getDrawAjax("week",thisDis);
		}else if(indexThis==1){
			getDrawAjax("month",thisDis);
		}else if(indexThis==2){
			getDrawAjax("year",thisDis);
		}

	});
});

function getMemberInfo(){
	var thisKey=getcookie("key");

	$.ajax({
		url:ApiUrl+"/index.php?act=member_index&client_type=wap&op=index&key="+thisKey,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success: function(data){
			if(data.code==200){
				var name=data.data.member_info.nick_name;
				var photo=data.data.member_info.avator;
				var dis_points = data.data.member_info.distribution_points;
				var is_dis=data.data.member_info.is_distribution;

				var dis_level="二级分销商";

				if(is_dis==1){
					dis_level="一级分销商";
				}else if(is_dis==2){
					$(".bounceIn span").text(2888-dis_points);
					$(".bounceIn").css("display","block");
				}

				if(photo!=""){
					$("#fxAvator img").attr("src",photo);
				}

				$("#fxName").text(name);

				$("#fxLevel").html("成长值：<span>"+dis_points+"</span>"+dis_level);

			}else{
				alert(data.message);
				window.location.href=WapSiteUrl+"/tmpl/member/login.html";
			}
		}
	});
}

function getDrawAjax(dayTime,is_dis){
	var thisKey=getcookie("key");

	$.ajax({
		url:ApiUrl+"/index.php?act=distribution&op=sale_statis&key="+thisKey+"&client_type=wap&time_type="+dayTime,
		type:"get",
		dataType:"jsonp",
		jsonp:"callback",
		success:function(result){
			if(result.code==200){
				var per_money=result.data.per_money;

				$(".start-time").text(result.data.start_day);
				$(".end-time").text(result.data.end_day);
				$(".fxYongjin span").text(result.data.total_money);

				var timeDay;
				if(dayTime=="week"){
					timeDay=7;
				}else if(dayTime=="month"){
					timeDay=15;
				}else if(dayTime=="year"){
					timeDay=6;
				}

				drawB(timeDay,per_money);
			}
		}
	});
}


function drawB(timeDay,per_money){
	$(function(){
			var flow=[];
			$(per_money).each(function(index,thisMoney){
				flow.push(thisMoney);//设置多少个数据点
			});
			
			var data = [
						{
							name : 'PV',
							value:flow,
							color:'#fd4a45',
							line_width:2
						}
					 ];
			 
			var labels =["","","","","","",""];//设置下标值
			
			var line = new iChart.LineBasic2D({
				render : 'canvasDiv',
				data: data,
				align:'center',
				width : wWidth,
				height : 300,
          		background_color : null,//背景色
				//background:'url(../images/jbBg@2x.png) repeat-x',
				animation:true,
				sub_option:{
					smooth : false,//平滑曲线
					point_size:10
				},
				tip:{
					enable:false,
					shadow:true,
				},
				legend : {
					enable : false
				},
				sub_option : {
					label:false,
					//hollow_inside:false,
					point_size:9
				},
				crosshair:{
					enable:false,
					line_color:'#eae0df'
				},
				coordinate:{
					width:'90%',
					valid_width:'90%',
					height:240,
					axis:{
						color:'#eae0df',
						width:[0,0,0,0]
					},
					grids:{
						vertical:{
							way:'share_alike',
							value:timeDay  //设置垂直线条数
						}
					},
					scale:[{
						 position:'left',
						 //start_scale:0,//纵坐标开始数值
						 //end_scale:80,//纵坐标停止数值
						 //scale_space:40,//纵坐标空格数
						 scale_size:0,
						 scale_color:'#eae0df'
					},{
						 position:'bottom',	
						 labels:labels,
						 scale_color:'#eae0df'
					}]
				},
				border:{
					width:[0,0,0,0],
					color:'#eae0df'
				}
			});
		//开始画图
		line.draw();
	});
}