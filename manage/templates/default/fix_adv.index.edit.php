<?php defined('emall') or exit('Access Invalid!');?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>爱个购商城</title>
    <script src="<?php echo RESOURCE_SITE_URL."/js/jquery.js";?>"></script> 
    <style type="text/css" adt="123"></style>
    <script src="<?php echo RESOURCE_SITE_URL."/js/jquery.validation.min.js";?>"></script> 
    <script src="<?php echo RESOURCE_SITE_URL."/js/admincp.js";?>"></script> 
    <script src="<?php echo RESOURCE_SITE_URL."/js/jquery.cookie.js";?>"></script> 
    <script src="<?php echo RESOURCE_SITE_URL."/js/common.js";?>"></script> 
    <script src="<?php echo RESOURCE_SITE_URL."/js/area_array.js";?>"></script> 
    <link href="./templates/default/css/skin_0.css" rel="stylesheet" type="text/css" id="cssfile2">
    <link href="<?php echo RESOURCE_SITE_URL."/js/perfect-scrollbar.min.css";?>" rel="stylesheet" type="text/css">
    <link href="./templates/default/css/font/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!--[if IE 7]>
    <link rel="stylesheet"
          href="manage/templates/default/css/font/font-awesome/css/font-awesome-ie7.min.css">
    <![endif]-->
    <script src="<?php echo RESOURCE_SITE_URL."/js/perfect-scrollbar.min.js";?>"></script> 
    <style type="text/css">
        object, embed {
        -webkit-animation-duration: .001s;
        -webkit-animation-name: playerInserted;
        -ms-animation-duration: .001s;
        -ms-animation-name: playerInserted;
        -o-animation-duration: .001s;
        -o-animation-name: playerInserted;
        animation-duration: .001s;
        animation-name: playerInserted;
    }

    @-webkit-keyframes playerInserted {
        from {
            opacity: 0.99;
        }
        to {
            opacity: 1;
        }
    }

    @-ms-keyframes playerInserted {
        from {
            opacity: 0.99;
        }
        to {
            opacity: 1;
        }
    }

    @-o-keyframes playerInserted {
        from {
            opacity: 0.99;
        }
        to {
            opacity: 1;
        }
    }

    @keyframes playerInserted {
        from {
            opacity: 0.99;
        }
        to {
            opacity: 1;
        }
    }
    .required {
        width:80px;
    }
    .rowform {
        width:300px;
    }
    .chk-btn {
        line-height: 22px;;
    }
    .chk-btn input {
        float:left;
        margin-top:5px;
    }
    .chk-btn label {
        line-height:22px;
        float:left;
        margin-right:14px;
    }
    .tb-type1 .select1{
        width:80px;
    }
    .vatop {
        line-height:22px;
    }
    .display-b {
        display: inline-block;
    }
    .chk-all-btn{
        width:80px;
    }
    .remove-city{
        width:50px;
    }
    .city-title{
        width:60px;
    }
     #btn_add{
            display:none;
            cursor: pointer;
            color:#329ED1;
        }
    </style>
</head>
<body>
<div class="page">
<div class="fixed-bar">
<div class="item-title">
  <h3><?php echo $lang['agent'];?></h3>
  <ul class="tab-base">
    <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_edit'];?></span></a></li>
  </ul>
</div>
</div>
<div class="fixed-empty"></div>
<form id="adv_form" enctype="multipart/form-data" method="post" action="index.php?act=fix_adv&op=edit">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="id" value="<?php  echo $output['fix_adv'][0]['id'];?>" />
    <input type="hidden" name="adv_status" value="<?php  echo $output['fix_adv'][0]['adv_status'];?>" />
    <input type="hidden" name="adv_provinceids" id="adv_provinceids" />
    <input type="hidden" name="adv_cityids" id="adv_cityids"  />
    <input type="hidden" name="adv_areaids" id="adv_areaids" />
    <input type="hidden" name="adv_areainfo" id="adv_areainfo" />
    <input type="hidden" name="adv_cityids_search" id="adv_cityids_search"  />
<div class="page">
    <table class="table tb-type1">
        <tbody>
        <tr class="noborder">
            <td class="required"><label class="validation" for="adv_title">广告标题:</label></td>
            <td class="vatop rowform"><input type="text" value="<?php  echo $output['fix_adv'][0]['adv_title'];?>" name="adv_title" id="adv_title" class="txt"></td>
           <td class="vatop tips"></td>
        </tr>
        <tr class="noborder">
            <td class="required"><label class="validation" for="agent_company_name">投放平台:</label></td>
            <td class="vatop rowform chk-btn">
            <input type="checkbox" <?php  if(count(explode('0',$output['fix_adv'][0]['adv_channel']))>1){echo 'checked="checked"';} ?> value="0" class="checkall" id="checkallBottom" name="adv_channel[]" />
            <label for="checkallBottom">全部</label>
            <input type="checkbox" <?php  if((count(explode('0',$output['fix_adv'][0]['adv_channel']))>1||count(explode('1',$output['fix_adv'][0]['adv_channel']))>1)){echo 'checked="checked"';} ?>  value="1" class="checkitem" name="adv_channel[]" />
            <label>IOS</label>
            <input type="checkbox" <?php  if((count(explode('0',$output['fix_adv'][0]['adv_channel']))>1||count(explode('2',$output['fix_adv'][0]['adv_channel']))>1)){echo 'checked="checked"';} ?> value="2" class="checkitem" name="adv_channel[]"/>
            <label>安卓</label>
            <input type="checkbox" <?php  if((count(explode('0',$output['fix_adv'][0]['adv_channel']))>1||count(explode('3',$output['fix_adv'][0]['adv_channel']))>1)){echo 'checked="checked"';} ?> value="3" class="checkitem" name="adv_channel[]" />
            <label>微信</label>
            <input type="checkbox" <?php  if((count(explode('0',$output['fix_adv'][0]['adv_channel']))>1||count(explode('4',$output['fix_adv'][0]['adv_channel']))>1)){echo 'checked="checked"';} ?> value="4" class="checkitem" name="adv_channel[]"/>
            <label>PC端</label>

            </td>
            <td class="vatop tips"></td>
        </tr>
        <tr class="noborder">
            <td class="required vatop"><label class="validation">投放区域:</label></td>
            <td class="rowform vatop" id="limitBox">
                 <select name="adv_limit_area" id="adv_limit_area">
                      <option  value="0" <?php if($output['fix_adv'][0]['adv_limit_area']==0){echo 'selected="selected"';} ?>>不限制</option>
                      <option value="1" <?php if($output['fix_adv'][0]['adv_limit_area']==1){echo 'selected="selected"';} ?>>限制</option>
                 </select>
                 <p id="btn_add">添加城市</p>
            </td>
            <td style="padding-top:0 !important;">
                <table class="table tb-type1" style="margin:0">
                    <tbody id="city_adv"></tbody>
                </table>

            </td>
        </tr>
        <tr class="noborder">
            <td class="required"><label class="validation">广告类型:</label></td>
            <td class="vatop rowform">
                <select  id="adv_type" name="adv_type">
                    <option value="1" >图片</option>
                </select>
            </td>
        </tr>
        <tr class="noborder">
            <td class="required"><label class="validation">上线时间:</label></td>
            <td class="rowform">
                <input  type="text" class="txt date"   name="adv_start_date" id="adv_start_date" class="txt" value="<?php  echo date('Y-m-d',$output['fix_adv'][0]['adv_start_date']) ;?>" />
            </td>
            <td class="vatop tips"></td>
        </tr>
        <tr class="noborder">
            <td class="required"><label class="validation">下线时间:</label></td>
            <td class="rowform">
                <input  type="text" class="txt date"   name="adv_end_date" id="adv_end_date" class="txt" value="<?php  echo date('Y-m-d',$output['fix_adv'][0]['adv_end_date']) ;?>"/>
                <td class="vatop tips"></td>
            </td>
        </tr>
        <tr class="noborder">
            <td class="required"><label class="validation" for="adv_order">排序:</label></td>
            <td class="vatop rowform"><input type="text" value="<?php  echo $output['fix_adv'][0]['adv_order'];?>" name="adv_order" id="adv_order" class="txt"></td>
           <td class="vatop tips"></td>
        </tr>
        <tr class="space odd">
            <th colspan="12">图片设置</th>
        </tr>
        <tr class="noborder">
            <td class="required"><label class="validation" for="adv_link">链接地址:</label></td>
            <td class="vatop rowform"><input type="text"   name="adv_link" id="adv_link" class="txt" value="<?php  echo $output['fix_adv'][0]['adv_link'];?>"></td>
            <td class="vatop tips"></td>
        </tr>
         <tr class="noborder">
            <td class="required"><label class="validation" for="company_name">图片:</label></td>
            <td class="vatop rowform" colspan="2"><img src="<?php echo UPLOAD_SITE_URL."/".ATTACH_ADV."/".$output['fix_adv'][0]['adv_pic_path'];?>" style="max-width: 250px; max-height: 480px;"></td>
        </tr>
        <tr class="noborder">
            <td class="required"><label class="validation" for="adv_pic">修改图片:</label></td>
            <td class="vatop rowform"><input name="adv_image_upload" type="file" id="picture_image_upload" size="30"  /></td>
            <td class="vatop tips">建议分辨率480*250</td>
        </tr>
        <tfoot>
            <tr class="tfoot">
                <td colspan="15">
                <a href="JavaScript:void(0);" class="btn" id="submitBtn"><span>确认</span></a>
                <a href="JavaScript:void(0);" class="btn" id="backBtn"><span>返回</span></a>
                </td>
            </tr>
        </tfoot>
    </table>

</div>
 <script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/jquery.ui.js";?>"></script> 
  <script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/i18n/zh-CN.js";?>" charset="utf-8"></script> 
   <script src="<?php echo RESOURCE_SITE_URL."/js/common_select.js";?>" charset="utf-8"></script> 
    <script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/jquery.ui.js";?>"></script>
    <script src="<?php echo RESOURCE_SITE_URL."/js/area_array.js";?>"></script>
    
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"  />
<script type="text/javascript">
//    var SHOP_SITE_URL = 'http://120.25.240.53/agg/shop';

//var userdataAllsss = <?php echo $output['fix_adv'][0]['adv_areainfo']?>;

$(function(){


    $('#adv_start_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#adv_end_date').datepicker({dateFormat: 'yy-mm-dd'});
    $("#agent_area_name").nc_region();


    function dataToDom(province,city,region,whole_city){
        var htmlTmpl = [
            '<tr class="city-item">',
            '<td class="vatop remove-city"><a href="####">删除</a></td>',
            '<td class="vatop city-title">投放城市</td>',
            '<td class="vatop">',
            '<input type="hidden" class="txt w300" name="agent_area_name" id="agent_area_name" show_grade="" value=" ">',
            '<select class="valid select-province">',
            '<option>-请选择-</option>',
            (function(){
                var a='';
                for(var i=0;i<nc_a[0].length;i++){
                    a+='<option value="'+ nc_a[0][i][0] +'">'+ nc_a[0][i][1]+'</option>';
                }
                return a;
            })(),
            '</select><select class="valid select-city">',
            '<option>-请选择-</option>',
            (function(){
                var a='';
                for(var i=0;i<nc_a[province].length;i++){
                    a+='<option value="'+ nc_a[province][i][0] +'">'+ nc_a[province][i][1]+'</option>';
                }
                return a;
            })(),
            '</select>',
            '</td>',
            '<td class="chk-all-btn">',
            '<span class="chk-all-check">',
            '<input type="checkbox" value="0" class="checkall" ',
            (function(){
                if(whole_city==true){
                    return 'checked="checked"'
                }
            })(),
            ' >',
            '<label>全市</label>',
            '</span>',
            '</td>',
            '<td class="chk-btn select-region">',
            (function(){
                            var a='';
                            var selectRegion = [];

                            for(var i=0;i<nc_a[city].length;i++){
                                a+='<span class="display-b"><input type="checkbox" class="checkitem" value="'+ nc_a[city][i][0] +'"><label>'+ nc_a[city][i][1]+'</label></span>';
                                for(var k=0;k<region.length;k++){
                                    if( nc_a[city][i][0] === region[k] ){
                                        selectRegion.push(i);
                                    }
                                }
                            }
                            return a;
            })(),
            '</td>',
            '</tr>'
        ].join('');

        $("#city_adv").append(htmlTmpl);
    }

    //$("#city_add1 .select-province").val(73);


function selectEvent(){
    $(".select-province").on("change",function(){
        var province = $(this).find("option:selected").val();
        var provinceName = $(this).find("option:selected").text();
        $(this).attr('data-id',province);
        $(this).attr('data-id-name',provinceName);
        var cityList='';
        for(var i=0;i<nc_a[province].length;i++){
            cityList += '<option value="'+ nc_a[province][i][0] +'">'+ nc_a[province][i][1]+'</option>';
        }
        $(this).parent().find('.select-city').html('<option>-请选择-</option>'+cityList);
        $(this).parents('.city-item').find('.select-region').html('');
    });
    $(".select-city").on("change",function(){
        var city = $(this).find("option:selected").val();
        var cityName = $(this).find("option:selected").text();
        $(this).attr('data-id',city);
        $(this).attr('data-id-name',cityName);
        var regionList='';
        var regionListId='';
        for(var i=0;i<nc_a[city].length;i++){
            regionList += '<span class="display-b"><input type="checkbox" class="checkitem" value="'+ nc_a[city][i][0] +'" checked="checked"><label>'+ nc_a[city][i][1] +'</label></span>';
        }
        $(this).parents('.city-item').find('.select-region').html(regionList);
    });
    $(".chk-all-check .checkall").on("click",function(){

           if($(this).attr("checked")!='checked'){
               $(this).parents('.chk-all-btn').next().find(".checkitem").attr("checked",false);
           }else{
               $(this).parents('.chk-all-btn').next().find(".checkitem").attr("checked",true);
           }

    });
    $(".remove-city").off("click").on("click",function(){
        $(this).parents('.city-item').remove();
    });
    $(".select-region .checkitem").off("change").on("change",function(){
       $(this).parents('.city-item').find('.checkall').removeAttr('checked');
    });

}




if( <?php echo $output['fix_adv'][0]['adv_limit_area']; ?> ==1){
    var userdataAll =<?php echo $output['fix_adv'][0]['adv_areainfo']; ?> ;
    for(var i=0;i<userdataAll.length;i++){
            dataToDom(userdataAll[i].province[0],userdataAll[i].city[0],userdataAll[i].region,userdataAll[i].whole_city);
            $(".city-item").eq(i).find(".select-province").attr('data-id',userdataAll[i].province[0]).attr('data-id-name',userdataAll[i].province[1]);
            $(".city-item").eq(i).find(".select-province").eq(0).find("option[value="+ userdataAll[i].province[0] +"]").attr("selected",true);
            $(".city-item").eq(i).find(".select-city").attr('data-id',userdataAll[i].city[0]).attr('data-id-name',userdataAll[i].city[1]);
            $(".city-item").eq(i).find(".select-city").find("option[value="+ userdataAll[i].city[0] +"]").attr("selected",true);
            var $checkitem = $(".city-item").eq(i).find(".select-region").eq(0).find(".checkitem")
            for(var j=0;j<$checkitem.length;j++){
                for(var k=0;k<userdataAll[i].region.length;k++){
                    if($checkitem.eq(j).val() == userdataAll[i].region[k]){
                        $checkitem.eq(j).attr('checked','checked');
                    }
                }
            }
        }
        selectEvent();
        $('#btn_add').css('display','inline-block');
}else{

}

//新增的城市，取消区域，全选未去掉bug修复
    $('#city_adv').on('change',function(){
        $(".select-region .checkitem").off("change").on("change",function(){
            $(this).parents('.city-item').find('.checkall').removeAttr('checked');
        });
    });


$('#adv_limit_area').on('change',function(){
    var limit = $(this).find("option:selected").val();
    if(limit == 1){
        $('#btn_add').css('display','inline-block');
    }else{
        $('#btn_add').css('display','none');
        $('#city_adv').html('');
    }
});




var htmlTmpl = [
        '<tr class="city-item">',
        '<td class="vatop remove-city"><a href="####">删除</a></td>',
        '<td class="vatop city-title">投放城市</td>',
        '<td class="vatop">',
        '<select class="valid select-province">',
        '<option>-请选择-</option>',
        (function(){
            var a='';
            for(var i=0;i<nc_a[0].length;i++){
                a+='<option value="'+ nc_a[0][i][0] +'">'+ nc_a[0][i][1]+'</option>';
            }
            return a;
        })(),
        '</select><select class="valid select-city">',
        '<option>-请选择-</option>',
        '</select>',
        '</td>',
        '<td class="chk-all-btn">',
        '<span class="chk-all-check">',
        '<input type="checkbox" value="0" class="checkall" checked="checked">',
        '<label>全市</label>',
        '</span>',
        '</td>',
        '<td class="chk-btn select-region">',
        '</td>',
        '</tr>'
    ].join('');

$("#btn_add").on("click",function(){
    $("#city_adv").append(htmlTmpl);
    selectEvent();
});



});

var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';
var userdataAll_new = [];
var cityAll = [];
var citySearch = [];
var provinceids = [];
var areaids = [];
//按钮先执行验证再提交表单
$(function(){

    $('#backBtn').on('click',function(){
        window.history.back();
    });

    $("#submitBtn").click(function(){
        if($("#adv_form").valid()){
            var adv_limit_area = $("#adv_limit_area").val();
             if(adv_limit_area==1){
        for (var i = 0; i < $(".city-item").length; i++) {
            var province = $(".city-item .select-province").eq(i).attr('data-id');
            var city = $(".city-item .select-city").eq(i).attr('data-id');
            var cityName = $(".city-item .select-city").eq(i).attr('data-id-name');
            var provinceName = $(".city-item .select-province").eq(i).attr('data-id-name');
            var region = [];
            var j = 0;

            if(isNaN(province)){
                continue;
            }

            for (var k = 0; k < $(".city-item").eq(i).find(".select-region .checkitem").length; k++) {
                if ($(".city-item").eq(i).find(".select-region .checkitem").eq(k).attr("checked") == 'checked') {
                    region[j] = $(".city-item").eq(i).find(".select-region .checkitem").eq(k).val();
                    j++;
                }
            }
            var userdata = {};
            userdata.province=[province,provinceName];
            if(!isNaN(city)) {
                userdata.city = [city, cityName];
                citySearch[citySearch.length]=city;
            }
            if(isNaN(city)){
                provinceids[provinceids.length]=province;
            }
            userdata.region = region;
            userdataAll_new[i] = userdata;
            userdata.whole_city=$(".city-item").eq(i).find(".checkall").attr("checked") == 'checked';
            if(userdata.whole_city){
                cityAll[cityAll.length] = city;
            }else{
                areaids=areaids.concat(region);
            }
        }
        console.log(cityAll);
        console.log(userdataAll_new);

                  if(userdataAll_new.length == 0||citySearch.length == 0){
                    alert("请选择限制的区域");
                     return false;
                }else{
                    $("#adv_provinceids").val(JSON.stringify(provinceids));
                    $("#adv_cityids").val(JSON.stringify(cityAll));
                    $("#adv_areaids").val(JSON.stringify(areaids));
                    $("#adv_areainfo").val(JSON.stringify(userdataAll_new));
                    $("#adv_cityids_search").val(JSON.stringify(citySearch));
                    //alert(JSON.stringify(userdataAll_new));
                    //alert(JSON.stringify(cityAll));
                    $("#adv_form").submit();
                }

             }else{
                  $("#adv_form").submit();
             }

        }
    });
});

$(document).ready(function(){
    $('#adv_form').validate({
       onkeyup: false,
       errorPlacement: function(error, element){
            error.appendTo(element.parent().parent().find('td:last'));
        },
        rules : {
            adv_title : {
                required : true
            },
            adv_start_date  : {
                required : true,
                date	 : true
            },
            adv_end_date  : {
            	required : true,
                date	 : true
            },
            adv_channel   : {
            	required : true,
                date	 : false
            },
            adv_link   : {
            	required : true,
                date	 : false
            },
            adv_order   : {
            	 number   : true
            },
            adv_pic_path   : {
            	required : true,
                date	 : false
            }
        },
        messages : {
            adv_title : {
            required : '广告标题不能为空'
            },
            adv_start_date : {
                required : '请选择上线时间'
            },
            adv_end_date : {
                required : '请选择下线时间'
            },
            adv_channel  : {
            	required   : '请选择投放平台'
            }, 
            adv_link  : {
            	required   : '请选择链接地址'
            },
            adv_order  : {
            	required   : '请输入数字'
            },
            adv_pic_path  : {
            	required   : '请选择图片'
            }
        }
    });
});
</script>
</body>
</form>
</html>