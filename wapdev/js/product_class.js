/*var gc_name=new Array();
 var gc_name_list=new Array();
 var getlist=new Array();
 var gc_id;
 var gc_id=new Array();
 var right_list=new Array();
 $.ajax({
 url:ApiUrl+"/index.php?act=goods_class&callback=jsonp1",
 type:"get",
 dataType:"jsonp",
 jsonp:"callback",
 success:function(data){
 if(data.code==200){

 $(data.datas.class_list).each(function(index,user_this){
 gc_name[index]=user_this.gc_name;
 gc_id[index]=user_this.gc_id;
 var subdiv="<li>"+gc_name[index]+"<span id='id'>"+gc_id[index]+"</span></li>";
 $(".sec_ul").append(subdiv);
 });
 $(".sec_ul li").eq(0).addClass("first_class_in");
 $.ajax({
 url:ApiUrl+"/index.php?act=goods_class&gc_id=209",
 type:"get",
 dataType:"jsonp",
 jsonp:"callback",
 success:function(data){
 //请求第一个分类
 if(data.code==200){
 $(data.datas.class_list).each(function(index,list){
 gc_name_list[index]=list.gc_name;
 right_list[index]=list.gc_id;
 var sublist="<li><a><img src='' /></a><br/>"+gc_name_list[index]+"<span style='display:none;'>"+right_list[index]+"</span></li>";
 $(".con_ul").append(sublist);
 });
 //点击右侧分类进入li的分类信息
 $(".con_ul li").click(function(){

 var list_id=$(this).find("span").html();
 window.location.href="list.html?list_id="+list_id;

 });
 }
 }
 });
 $(".sec_ul li").click(function(){
 $(".con_ul li").remove();
 var get_id=$(this).find("span").html();
 //var bb=$.inArray($(this).gc_name,gc_name);
 $(".sec_ul li").removeClass("first_class_in");
 $(this).addClass("first_class_in");
 get_list(get_id);
 });
 }
 }
 });
 //封装获取分类信息的ajax
 function get_list(gc_id){
 $.ajax({
 url:ApiUrl+"/index.php?act=goods_class&gc_id="+gc_id,
 type:"get",
 dataType:"jsonp",
 jsonp:"callback",
 success:function(data){

 if(data.code==200){
 $(data.datas.class_list).each(function(index,list){
 getlist[index]=list.gc_name;
 var listinfo="<li><a href=''><img src='' /></a><br/>"+getlist[index]+"</li>";
 $(".con_ul").append(listinfo);
 });
 }
 }
 });
 }
 //封装获取右侧分类的具体信息
 function get_right_list(gc_id){
 $.ajax({
 url:"",
 type:"get",
 dataType:"jsonp",
 jsonp:"callback",
 success:function(data){

 }
 });
 }*/

//获取分类的一级菜单
var gc_id = new Array();//存放商品的id
var gc_name = new Array();//存放商品分类名
var gc_id2 = new Array(), gc_name2 = new Array(), gc_images = new Array();//存放二级分类的商品id和商品分类名字
var list_gc_id = new Array(), list_gc_name = new Array(), list_gc_image = new Array();
var height = $(window).height();
//$('#class_height').css('height', height - 113);
$('#class_height').css('height', height);
$.ajax({
    url: ApiUrl + "/index.php?act=goods_class&client_type=wap",
    type: "get",
    dataType: "jsonp",
    jsonp: "callback",
    success: function (data) {
        if (data.code == 200) {
            $(data.data.class_list).each(function (index, list) {
                gc_id[index] = list.gc_id;
                gc_name[index] = list.gc_name;
                var subli = "<li>" + gc_name[index] + "<span style='display:none;'>" + gc_id[index] + "</span></li>";
                $(".sec_ul").append(subli);
            });
            $(".sec_ul li").eq(0).addClass("first_class_in");
            var first_id = gc_id[0];
            $.ajax({
                url: ApiUrl + "/index.php?act=goods_class&client_type=wap&gc_id=" + first_id,
                type: "get",
                dataType: "jsonp",
                jsonp: "callback",
                success: function (data) {
                    if (data.code == 200) {
                        $(data.data.class_list).each(function (index, list_this) {
                            gc_id2[index] = list_this.gc_id;
                            gc_name2[index] = list_this.gc_name;
                            gc_images[index] = list_this.image;
                            var sublist = "<li><a><img src='" + gc_images[index] + "' /></a><br/>" + gc_name2[index] + "<span style='display:none;'>" + gc_id2[index] + "</span></li>";
                            $(".con_ul").append(sublist);
                        });
                        //点击二级分类的li进入三级分类
                        $(".con_ul li").click(function () {

                            var three_id = $(this).find("span").html();
                            window.location.href = "list.html?gc_id=" + three_id;
                        });
                    }
                }

            });
            //点击一级分类CSS切换
            $(".sec_ul li").click(function () {
                $(".con_ul li").remove();
                $(".sec_ul li").removeClass("first_class_in");
                $(this).addClass("first_class_in");
                var goodc_id = $(this).find("span").html();//获取当前商品的id
                get_twolist(goodc_id);

            });
        }
    }
});

//封装点击一级菜单显示二级菜单
function get_twolist(gc_id) {
    $.ajax({
        url: ApiUrl + "/index.php?act=goods_class&client_type=wap&gc_id=" + gc_id,
        type: "get",
        dataType: "jsonp",
        jsonp: "callback",
        success: function (data) {
            if (data.code == 200) {
                $(data.data.class_list).each(function (index, shop_list) {
                    list_gc_id[index] = shop_list.gc_id;
                    list_gc_name[index] = shop_list.gc_name;
                    list_gc_image[index] = shop_list.image;
                    var sublistinfo = "<li><a><img src='" + list_gc_image[index] + "' /></a><br/>" + list_gc_name[index] + "<span style='display:none;'>" + list_gc_id[index] + "</span></li>";
                    $(".con_ul").append(sublistinfo);
                });
                //点击二级分类的li进入三级分类
                $(".con_ul li").click(function () {

                    var three_id = $(this).find("span").html();
                    window.location.href = "list.html?gc_id=" + three_id;
                });
            }
        }
    });

}


if (type == 'iOS') {
    $('#footer').hide();
} else if (type == 'android') {
    $('#footer').hide();
} else {
    $('#footer').show();
}
