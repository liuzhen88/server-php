// JavaScript Document

var wWidth=$(window).width();

$(window).ready(function(e) {
	
	//绘制
    drawB(68.34,26.83);
	
	$("#fxLabalHead ul li").click(function(){
		$("#fxLabalHead ul li").removeClass("on");
		$(this).addClass("on");
		
		drawB(68.34,26.83);
	});
});



function drawB(fs,zy){
	$(function(){   
      var data = [
                  {name : '粉丝贡献',value : fs ,color:'#FF6623'},
                  {name : '自营',value : zy ,color:'#F2AB53'}
              ];
      
      var chart = new iChart.Pie2D({
          render : 'canvasDiv',
          padding:'2 10',
          width : wWidth,
          height : 300,
          data:data,
          shadow:false,
          shadow_color:'#15353a',
          shadow_blur:8,
          background_color : '#f7f3f4',//背景色
          gradient:false,//开启渐变
          color_factor:0.28,
          gradient_mode:'RadialGradientOutIn',
          showpercent:true,//显示百分比
          decimalsnum:2,
          legend:{
              enable:true,
              padding:[240,0,0,0],
              color:'#333',
              border:{
                  width:[0,0,0,0],
                  color:'#343b3e'
              },
			  fontsize:12,
              background_color : null,
          },
          sub_option:{
              border:{
                  enable:false
              },
              label : {
                  background_color:null,
                  sign:false,//设置禁用label的小图标
                  line_height:10,
                  padding:4,
                  border:{
                      enable:true,
					  width:[0,0,1,0],
                      radius : 0,//圆角设置
                      color:'#999'
                  },
                  fontsize:14,
                  fontweight:100,
                  color : '#444444'
              },
              listeners:{
                  parseText:function(d, t){
                      return d.get('value');
                  }
              }
          },
		  border:{
			  width:[0,0,0,0],
			  color:'#1e2223'
		  }
      });
      chart.bound(0);
  	});
}