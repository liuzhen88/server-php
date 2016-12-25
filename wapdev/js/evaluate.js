$(function () {
    FastClick.attach(document.body);
    var order_id=request('order_id');//订单编号
    var goods_id=request('goods_id');//产品编号
    var store_id=request('store_id');//店铺编号
    var geval_scores;//商品评分
    var geval_content;//评论内容
    var geval_isanonymous=0;//是否匿名
    var base64Img=new Array();//图片字符流
    key=getcookie('key');
    //order_id="14489";
    //goods_id="8296";
    //key="35a3335e3e80be653cf3972d5866c0f5";
    // 选择图片
    document.getElementById('evaFilebtn').onchange = function(event){
        var img = event.target.files[0];
        // 判断是否图片  
        if(!img){  
            return ;  
        }  
  
        // 判断图片格式  
        if(!(img.type.indexOf('image')==0 && img.type && /\.(?:jpg|png|gif|jpeg)$/.test(img.name)) ){
            alert('图片只能是jpg,gif,png,jpeg');
            return ;  
        }  

        var reader = new FileReader();  
        reader.readAsDataURL(img);  
        
        reader.onload = function(e){ // reader onload start
        $('.evalutePto').append('<li class="addImg"><span class="del" onclick="delPic(this);"></span><img src="' + this.result + '"/></li>');
        if($('.addImg').length == 9){
            $('#evaluteFile').hide();
        }

        } // reader onload end  
    }
    $(".evaluate-star").on('click','li',function(){
        $(this).siblings().removeClass('eva-star2');
        geval_scores=$(this).index()+1;
        if(geval_scores==1){
            $("#evaSatisfy").text('不满意');
        }else if(geval_scores==2){
            $("#evaSatisfy").text('一般');
        }else if(geval_scores==3){
            $("#evaSatisfy").text('比较满意');
        }else if(geval_scores==4){
            $("#evaSatisfy").text('满意');
        }else if(geval_scores==5){
            $("#evaSatisfy").text('非常满意');
        }
        for(var i=0;i<geval_scores;i++){
            $(".evaluate-star li").eq(i).addClass('eva-star2');
        }
    })
    $("#evaChk").on('click',function(){
        var chkId=$(this).find('span').attr('id');
        if(chkId=='eva-chk'){
            $(this).find('span').attr('id','eva-chk2');
            geval_isanonymous=1;
        }else if(chkId=='eva-chk2'){
            $(this).find('span').attr('id','eva-chk');
            geval_isanonymous=0;
        }
    })
    $('#evaSendBtn').on('click',function(){
        geval_content=$('#evaluteText').val();
        for(var i= 0;i< $('.addImg').length;i++){
            base64Img[i]=$('.addImg').eq(i).find('img').attr('src');
        }
         if(key==""){
              window.location.href=WapSiteUrl+"/tmpl/member/login.html";
         }else{
             if(goods_id!=""){//商品评价
                 $.ajax({
                     url: ApiUrl + "/index.php?act=member_order&op=good_evaluate&client_type=wap&key="+key+"&order_id="+order_id+"&goods_id="+goods_id+"&geval_scores="+geval_scores+"&geval_content="+geval_content+"&geval_isanonymous="+geval_isanonymous,
                     type: 'post',
                     dataType: 'json',
                     callback:'callback',
                     data:{'image_1':base64Img[0],
                         'image_2':base64Img[1],
                         'image_3':base64Img[2],
                         'image_4':base64Img[3],
                         'image_5':base64Img[4],
                         'image_6':base64Img[5],
                         'image_7':base64Img[6],
                         'image_8':base64Img[7],
                         'image_9':base64Img[8]
                     },
                     success: function (result) {
                         if(result.code==200){
                             window.location.href=WapSiteUrl+"/evaluate_ok.html?goods_id="+goods_id;
                         }else if(result.code==80001){
                             alert(result.message);
                             window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                         }else{
                             alert(result.message);
                         }
                     }
                 })
             }else{//店铺评价
                 $.ajax({
                     url: ApiUrl + "/index.php?act=member_order&op=local_store_evaluate&client_type=wap&key="+key+"&order_id="+order_id+"&store_id="+store_id+"&geval_scores="+geval_scores+"&geval_content="+geval_content+"&geval_isanonymous="+geval_isanonymous,
                     type: 'post',
                     dataType: 'json',
                     callback:'callback',
                     data:{'image_1':base64Img[0],
                         'image_2':base64Img[1],
                         'image_3':base64Img[2],
                         'image_4':base64Img[3],
                         'image_5':base64Img[4],
                         'image_6':base64Img[5],
                         'image_7':base64Img[6],
                         'image_8':base64Img[7],
                         'image_9':base64Img[8]
                     },
                     success: function (result) {
                         if(result.code==200){
                             window.location.href=WapSiteUrl+"/evaluate_ok.html?store_id="+store_id;
                         }else if(result.code==80001){
                             alert(result.message);
                             window.location.href=WapSiteUrl+"/tmpl/member/login.html";
                         }else{
                             alert(result.message);
                         }
                     }
                 })
             }
         }
    })
})
function delPic(obj){
    if($('.addImg').length <10){
        $('#evaluteFile').show();
    }
    $(obj).parent('li').remove();
}