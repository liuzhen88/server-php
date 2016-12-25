<?php defined('emall') or exit('Access Invalid!');?>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript">
    $(document).ready(function(){
        $('a[nctype="nyroModal"]').nyroModal();

        $('#btn_fail').on('click', function() {
            if($('#joinin_message').val() == '') {
                $('#validation_message').text('请输入审核意见');
                $('#validation_message').show();
                return false;
            } else {
                $('#validation_message').hide();
            }
            if(confirm('确认拒绝申请？')) {
                $('#verify_type').val('fail');
                $('#form_store_verify').submit();
            }
        });
        $('#btn_pass').on('click', function() {
            var valid = true;
            $('[nctype="commis_rate"]').each(function(commis_rate) {
                rate = $(this).val();
                if(rate == '') {
                    valid = false;
                    return false;
                }

                var rate = Number($(this).val());
                if(isNaN(rate) || rate < 0 || rate >= 100) {
                    valid = false;
                    return false;
                }
            });
            if(valid) {
                $('#validation_message').hide();
                if(confirm('确认通过申请？')) {
                    $('#verify_type').val('pass');
                    $('#form_store_verify').submit();
                }
            } else {
                $('#validation_message').text('请正确填写分佣比例');
                $('#validation_message').show();
            }
        });
    });
</script>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['store'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=store&op=store"><span><?php echo $lang['manage'];?></span></a></li>
        <li><a href="index.php?act=store&op=store_joinin" ><span><?php echo $lang['pending'];?></span></a></li>
        <li><a href="index.php?act=store&op=reopen_list" ><span>续签申请</span></a></li>
        <li><a href="index.php?act=store&op=store_bind_class_applay_list" ><span>经营类目申请</span></a></li>
        <li><a href="index.php?act=store&op=store_joinin_o2o" ><span>本土开店申请</span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $output['joinin_detail_title'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>

    <form id="form_store_verify" action="index.php?act=store&op=store_joinin_o2o_verify" method="post">

  <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
      <tr>
        <th colspan="20">联系人信息</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th>联系人姓名：</th>
        <td><?php echo $output['joinin_detail']['contacts_name'];?></td>
        <th>联系人电话：</th>
        <td><?php echo $output['joinin_detail']['contacts_phone'];?></td>
        <th>电子邮箱：</th>
        <td><?php echo $output['joinin_detail']['contacts_email'];?></td>
      </tr>
      <tr>
          <th>店铺所在地址：</th>
          <td colspan="5"><?php echo $output['joinin_detail']['address'];?>&nbsp;<?php echo $output['joinin_detail']['address_detail'];?><span style="float: right"><a onclick="reLoaction()">重新定位</a></span></td>
      </tr>
      <tr>
          <th>经纬度：</th>
          <td colspan="5"><span  class="latlng_describe"><?php echo floatval($output['joinin_detail']['lng']).','.floatval($output['joinin_detail']['lat']);?>&nbsp;</span> <span style="float: right"><a onclick="saveLocation()">保存经纬度</a></span></td>

          <input name="lng" id="lng" type="hidden" class="w130" value="<?php echo floatval($output['joinin_detail']['lng'])?>" >
          <input name="lat" id="lat" type="hidden" class="w130" value="<?php echo floatval($output['joinin_detail']['lat'])?>">
      </tr>
      <tr>
          <th>门店电话：</th>
          <td colspan="5"><?php echo $output['joinin_detail']['company_phone'];?></td>
      </tr>
      <tr>
          <th>位置：</th>
          <td colspan="5"><div id="allmap"></div></td>
      </tr>
    </tbody>
  </table>
  <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
    <thead>
      <tr>
        <th colspan="20">营业执照信息（副本）</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th class="w150">营业执照号：</th>
        <td><?php echo $output['joinin_detail']['business_licence_number'];?></td></tr><tr>
      </tr>

      <tr>
        <th>营业执照<br />
电子版：</th>
        <td colspan="20"><a nctype="nyroModal"  href="<?php echo $output['joinin_detail']['business_licence_number_electronic'];?>"> <img src="<?php echo $output['joinin_detail']['business_licence_number_electronic'];?>" alt="营业执照电子版" /> </a></td>
      </tr>
    </tbody>
  </table>


    <input id="verify_type" name="verify_type" type="hidden" />
    <input name="id" type="hidden" value="<?php echo $output['joinin_detail']['id'];?>" />
    <table border="0" cellpadding="0" cellspacing="0" class="store-joinin">
      <thead>
        <tr>
          <th colspan="20">店铺经营信息</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th class="w150">卖家账号：</th>
          <td>
              <?php if(in_array(intval($output['joinin_detail']['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) { ?>
              <input type="text" name="name">(请填写手机号)
              <?php }else{
                    echo $output['joinin_detail']['member_name'];
              } ?>
          </td>
        </tr>
        <tr>
            <th class="w150">登录密码：</th>
            <td>
                <?php if(in_array(intval($output['joinin_detail']['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) { ?>
<!--                <input type="text" name="password" >-->系统自动生成默认密码：123456
                <?php }else{ ?>
                    ******
                <?php } ?>
            </td>
        </tr>
        <tr>
          <th class="w150">店铺名称：</th>
          <td><?php echo $output['joinin_detail']['store_name'];?></td>
        </tr>
        <tr>
          <th class="w150">店铺返佣比例：</th>
          <td><?php if(in_array(intval($output['joinin_detail']['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) { ?>
                  <input type="text" nctype="commis_rate" name="store_commis_rate" >%
              <?php }else{
                  echo $output['joinin_detail']['store_commis_rate'].'%';
              } ?></td>
        </tr>
<!--        <tr>-->
<!--          <th class="w150">所属代理商：</th>-->
<!--          <td>--><?php //if(in_array(intval($output['joinin_detail']['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) { ?>
<!--                  <input type="text"  name="agent_name" >(请输入代理商名称)-->
<!--              --><?php //}else{
//                  echo $output['joinin_detail']['agent_name'];
//              } ?><!--</td>-->
<!--        </tr>-->
        <tr>
          <th>经营类目：</th>
          <td colspan="2"><table border="0" cellpadding="0" cellspacing="0" id="table_category" class="type">
              <thead>
                <tr>
                  <th>分类1</th>
                  <th>分类2</th>
                  <th>分类3</th>
                  <th>返利比例</th>
                </tr>
              </thead>
              <tbody>
                <?php $store_class_names = unserialize($output['joinin_detail']['store_class_names']);?>
                <?php $store_class_commis_rates = explode(',',$output['joinin_detail']['store_class_commis_rates']);?>
                <?php if(!empty($store_class_names) && is_array($store_class_names)) {?>
                <?php for($i=0, $length = count($store_class_names); $i < $length; $i++) {?>
                <?php list($class1, $class2, $class3) = explode(',', $store_class_names[$i]);?>
                <tr>
                  <td><?php echo $class1;?></td>
                  <td><?php echo $class2;?></td>
                  <td><?php echo $class3;?></td>
                  <td>
                <?php if(intval($output['joinin_detail']['joinin_state']) === 10) {?>
                  <input type="text" nctype="commis_rate" value="" name="commis_rate[]" class="w100" /> %
                <?php } else { ?>
                <?php echo $store_class_commis_rates[$i];?> %
                <?php } ?>
                </td>
                </tr>
                <?php } ?>
                <?php } ?>
                </tbody>
        </table></td>
    </tr>

   <?php if(in_array(intval($output['joinin_detail']['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) { ?>
    <tr>
        <th>审核意见：</th>
        <td colspan="2"><textarea id="joinin_message" name="joinin_message"></textarea></td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
   <?php if(in_array(intval($output['joinin_detail']['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) { ?>
    <div id="validation_message" style="color:red;display:none;"></div>
    <div><a id="btn_fail" class="btn" href="JavaScript:void(0);"><span>拒绝</span></a> <a id="btn_pass" class="btn" href="JavaScript:void(0);"><span>通过</span></a></div>
    <?php } ?>
  </form>
</div>



<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<style type="text/css">
   #allmap {width: 100%;height: 300px;overflow: hidden;margin:0;font-family:"微软雅黑";}
   .store-joinin tbody td div img { max-width: none; max-height: none; padding: 0; border: none;}
</style>
<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=uLhko8NKBiREseUxAWE0hVYc"></script><?php
$locat_city_a=explode(' ',$output['joinin_detail']['address']);
$locat_city=isset($locat_city_a[1])?$locat_city_a[1]:'苏州';
$locat_area=isset($locat_city_a[2])?$locat_city_a[2]:'';
$locat_lat=floatval($output['joinin_detail']['lat']);
$locat_lng=floatval($output['joinin_detail']['lng']);
if(0==$locat_lat) $locat_lat=120.559147;
if(0==$locat_lng) $locat_lng=31.292095;
//?><!----><?php //echo $locat_lat.','.$locat_lng ?><!----><?php //echo $locat_city ?>
<script type="text/javascript">

    // 百度地图API功能
    var point=new BMap.Point(<?php echo $locat_lng.','.$locat_lat ?>);//目标点
    var map = new BMap.Map("allmap");    // 创建Map实例
    map.centerAndZoom(point, 18);  // 初始化地图,设置中心点坐标和地图级别
//    map.addControl(new BMap.MapTypeControl());   //添加地图类型控件
    map.setCurrentCity("<?php echo $locat_city ?>");          // 设置地图显示的城市 此项是必须设置的
    map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放

    //创建标记
    var marker=new BMap.Marker(point);
    map.addOverlay(marker);
    marker.enableDragging();    //可拖拽
    //拖拽监听
    marker.addEventListener("dragend", function(obj) {
        $('#lng').val(obj.point.lng);
        $('#lat').val(obj.point.lat);
        $('.latlng_describe').text(obj.point.lat+","+obj.point.lng);
    });


    var opts = {
        position : point,    // 指定文本标注所在的地理位置
        offset   : new BMap.Size(30, -30)    //设置文本偏移量
    }
    var label = new BMap.Label("拖动调整位置", opts);  // 创建文本标注对象
    label.setStyle({
        color : "red",
        fontSize : "12px",
        height : "20px",
        lineHeight : "20px",
        fontFamily:"微软雅黑"
    });
    map.addOverlay(label);



    //重新定位功能
    //百度地图，获取经纬度
    function reLoaction() {
        var city=  '<?php echo $locat_city ?>';
        var address = '<?php echo $locat_area ?>';
        $.ajax({
            url:"http://api.map.baidu.com/geocoder/v2/?address="+encodeURIComponent(address)+"&city="+encodeURIComponent(city)+"&output=json&ak=uLhko8NKBiREseUxAWE0hVYc&callback=showLocation",
            dataType:"jsonp", //返回的数据类型,text 或者 json数据，建议为json
            type:"get", //传参方式，get 或post
            error: function(msg){  //若Ajax处理失败后回调函数，msg是返回的错误信息
//                $('.notice').html( "处理失败");
            },
            success: function(data) { //若Ajax处理成功后的回调函数，text是返回的页面信息
                if(data.status!=0){
//                    $('.notice').html( "经纬度获取失败，请输入正确公司地址");
                    return;
                }
                $('#lng').val(data.result.location.lng);
                $('#lat').val(data.result.location.lat);
                $('.latlng_describe').text("经纬度："+data.result.location.lat+","+data.result.location.lng);
                map.clearOverlays();
                var current_point=new BMap.Point(data.result.location.lng,data.result.location.lat);
                map.centerAndZoom(current_point, 18);
                marker=new BMap.Marker(current_point);
                map.addOverlay(marker); //拖拽停止监听
                marker.addEventListener("dragend", function(obj) {
                    $('#lng').val(obj.point.lng);
                    $('#lat').val(obj.point.lat);
                    $('.latlng_describe').text("经纬度："+obj.point.lat+","+obj.point.lng);
                });
                marker.enableDragging();
            }
        });
    }

    //保存经纬度
    function saveLocation (){
        var lng=$('#lng').val();
        var lat=$('#lat').val();
        $.ajax({
            url:"index.php?act=store&op=save_location&id="+<?php echo $output['joinin_detail']['id']; ?>+'&lng='+lng+'&lat='+lat,
            dateType:"text",
            type:'get',
            success:function(data){
                alert(data);
            }
        })
    }
</script>

