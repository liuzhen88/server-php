<?php defined('emall') or exit('Access Invalid!');?>
<style type="text/css">
    .show_map{color:#0D93BF}
    .show_map:hover{cursor: pointer;}
</style>
<div class="page">
  <table class="table tb-type2 order">
    <tbody>
      <tr class="space">
        <th colspan="2"><?php echo $lang['order_detail'];?></th>
      </tr>
      <tr>
        <th><?php echo $lang['order_info'];?></th>
      </tr>
      <tr>
        <td colspan="2"><ul>
            <li>
            <strong><?php echo $lang['order_number'];?>:</strong><?php echo $output['order_info']['order_sn'];?>
            ( 支付单号 <?php echo $lang['nc_colon'];?> <?php echo $output['order_info']['pay_sn'];?> )
            </li>
            <li><strong><?php echo $lang['order_state'];?>:</strong><?php echo orderState($output['order_info']);?></li>
            <li><strong><?php echo $lang['order_total_price'];?>:</strong><span class="red_common"><?php echo $lang['currency'].$output['order_info']['order_amount'];?> </span>
            	<?php if($output['order_info']['refund_amount'] > 0) { ?>
            	(<?php echo $lang['order_refund'];?>:<?php echo $lang['currency'].$output['order_info']['refund_amount'];?>)
            	<?php } ?></li>
            <li><strong><?php echo $lang['order_total_transport'];?>:</strong><?php echo $lang['currency'].$output['order_info']['shipping_fee'];?></li>
            <li><strong>商品总额:</strong><?php echo $lang['currency'].($output['order_info']['order_amount']-$output['order_info']['shipping_fee']);?></li>
            <li><strong><?php echo $lang['payment'];?><?php echo $lang['nc_colon'];?></strong><?php echo orderPaymentName($output['order_info']['payment_code']);?></li>
          </ul></td>
      </tr>
      <tr>
        <td><ul>
            <li><strong><?php echo $lang['buyer_name'];?><?php echo $lang['nc_colon'];?></strong><?php echo $output['order_info']['buyer_name'].'(邀请码：'.$output['buyer_info']['invitation'].')';?></li>
            <li><strong><?php echo $lang['store_name'];?><?php echo $lang['nc_colon'];?></strong><?php echo $output['order_info']['store_name'].'(邀请码：'.$output['seller_member_info']['invitation'].')';?></li>
            <li><strong><?php echo $lang['order_time'];?><?php echo $lang['nc_colon'];?></strong><?php echo date('Y-m-d H:i:s',$output['order_info']['add_time']);?></li>
            <li><strong>店铺联系方式<?php echo $lang['nc_colon'];?></strong><?php echo $output['seller_store_info']['store_phone'];?></li>
            <?php if(intval($output['order_info']['payment_time'])){?>
            <li><strong><?php echo $lang['payment_time'];?><?php echo $lang['nc_colon'];?></strong><?php echo date('Y-m-d H:i:s',$output['order_info']['payment_time']);?></li>
            <?php }?>
            <?php if(intval($output['order_info']['shipping_time'])){?>
            <li><strong><?php echo $lang['ship_time'];?><?php echo $lang['nc_colon'];?></strong><?php echo date('Y-m-d H:i:s',$output['order_info']['shipping_time']);?></li>
            <?php }?>
            <?php if(intval($output['order_info']['finnshed_time'])){?>
            <li><strong><?php echo $lang['complate_time'];?><?php echo $lang['nc_colon'];?></strong><?php echo date('Y-m-d H:i:s',$output['order_info']['finnshed_time']);?></li>
            <?php }?>
          </ul></td>
      </tr>
      <tr>
        <th>收货人信息</th>
      </tr>
      <tr>
        <td>
            <ul>
            <li><strong><?php echo $lang['consignee_name'];?><?php echo $lang['nc_colon'];?></strong><?php echo $output['order_info']['extend_order_common']['reciver_name'];?></li>
            <li><strong>送货时间<?php echo $lang['nc_colon'];?></strong><?php echo $output['order_info']['hope_receive_time'];?></li>
            <li><strong><?php echo $lang['address'];?><?php echo $lang['nc_colon'];?></strong><?php echo @$output['order_info']['extend_order_common']['reciver_info']['address'];?></li>
            <li><strong><?php echo $lang['tel_phone'];?><?php echo $lang['nc_colon'];?></strong><?php echo @$output['order_info']['extend_order_common']['reciver_info']['phone'];?></li>
            <li><strong>坐标<?php echo $lang['nc_colon'];?></strong><?php echo floatval($output['order_info']['extend_order_common']['lng']).','.floatval($output['order_info']['extend_order_common']['lat']); ?>
                <span<span class="thumb size-56x56"><div class="show_map" nctype="nyroModal" href="javascript:void(0)" onclick="showMap(<?php echo floatval($output['order_info']['extend_order_common']['lng'])?>,<?php echo floatval($output['order_info']['extend_order_common']['lat'])?>)">显示地图</div></span></span>
            </li>
            <?php if($output['order_info']['shipping_code'] != ''){?>
            <li><strong><?php echo $lang['ship_code'];?><?php echo $lang['nc_colon'];?></strong><?php echo $output['order_info']['shipping_code'];?></li>
            <?php }?>
                <li><strong><?php echo $lang['buyer_message'];?><?php echo $lang['nc_colon'];?></strong><?php if($output['order_info']['extend_order_common']['order_message'] != ''){echo $output['order_info']['extend_order_common']['order_message'];}else{echo '无';}?></li>
            </ul>
            <div id="map" style="display: none;height: 300px;width: 800px;margin: 5px auto;"></div>
        </td>
          </tr>
    <?php if (!empty($output['daddress_info'])) {?>
      <tr>
        <th>发货信息</th>
      </tr>
      <tr>
        <td><ul>
          <li><strong>发货人<?php echo $lang['nc_colon'];?></strong><?php echo $output['daddress_info']['seller_name']; ?></li>
          <li><strong><?php echo $lang['tel_phone'];?>:</strong><?php echo $output['daddress_info']['telphone'];?></li>
          <li><strong>发货地<?php echo $lang['nc_colon'];?></strong><?php echo $output['daddress_info']['area_info'];?>&nbsp;<?php echo $output['daddress_info']['address'];?>&nbsp;<?php echo $output['daddress_info']['company'];?></li>
          </ul></td>
          </tr>
    <?php } ?>
    <?php if (!empty($output['order_info']['extend_order_common']['invoice_info'])) {?>
      <tr>
      	<th>发票信息</th>
      </tr>
      <tr>
      <td><ul>
    <?php foreach ((array)$output['order_info']['extend_order_common']['invoice_info'] as $key => $value){?>
      <li><strong><?php echo $key.$lang['nc_colon'];?></strong><?php echo $value;?></li>
    <?php } ?>
          </ul></td>
      </tr>
    <?php } ?>
      <tr>
        <th><?php echo $lang['product_info'];?></th>
      </tr>
      <tr>
        <td><table class="table tb-type2 goods ">
            <tbody>
              <tr>
                <th>商品货号</th>
                <th></th>
                <th><?php echo $lang['product_info'];?></th>
                <th class="align-center">商品规格</th>
                <th class="align-center">单价</th>
                  <th class="align-center"><?php echo $lang['product_num'];?></th>
                <th class="align-center">总金额</th>
                <th class="align-center">实际支付额</th>
              </tr>
              <?php foreach($output['order_info']['extend_order_goods'] as $goods){?>
              <tr>
                <td class="w96 "><span ><?php echo $goods['goods_id'];?></span></td>
                <td class="w60 picture"><div class="size-56x56"><span class="thumb size-56x56"><i></i><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id'];?>" target="_blank"><img alt="<?php echo $lang['product_pic'];?>" src="<?php echo thumb($goods, 60);?>" /> </a></span></div></td>
                <td class="w50pre"><p><a href="<?php echo SHOP_SITE_URL;?>/index.php?act=goods&goods_id=<?php echo $goods['goods_id'];?>" target="_blank"><?php echo $goods['goods_name'];?></a></p><p><?php echo orderGoodsType($goods['goods_type']);?></p></td>
                  <td class="w96 align-center"><span ><?php echo $goods['goods_spec']; ?></span></td>
                <td class="w96 align-center"><span class="red_common"><?php echo $lang['currency'].$goods['goods_price'];?></span></td>
                  <td class="w96 align-center"><?php echo $goods['goods_num'];?></td>
                <td class="w96 align-center"><span class="red_common"><?php echo $lang['currency'].($goods['goods_price']*$goods['goods_num']);?></span></td>
                <td class="w96 align-center"><span class="red_common"><?php echo $lang['currency'].$goods['goods_pay_price'];?></span></td>
              </tr>
              <?php }?>
            </tbody>
          </table></td>
      </tr>
    <!-- S 促销信息 -->
      <?php if(!empty($output['order_info']['extend_order_common']['promotion_info']) && !empty($output['order_info']['extend_order_common']['voucher_code'])){ ?>
      <tr>
      	<th>其它信息</th>
      </tr>
      <tr>
          <td>
        <?php if(!empty($output['order_info']['extend_order_common']['promotion_info'])){ ?>
        <?php echo $output['order_info']['extend_order_common']['promotion_info'];?>，
        <?php } ?>
        <?php if(!empty($output['order_info']['extend_order_common']['voucher_code'])){ ?>
        使用了面额为 <?php echo $lang['nc_colon'];?> <?php echo $output['order_info']['extend_order_common']['voucher_price'];?> 元的代金券，
         编码 : <?php echo $output['order_info']['extend_order_common']['voucher_code'];?>
        <?php } ?>
          </td>
      </tr>
      <?php } ?>
    <!-- E 促销信息 -->

    <?php if(is_array($output['refund_list']) and !empty($output['refund_list'])) { ?>
      <tr>
      	<th>退款记录</th>
      </tr>
      <?php foreach($output['refund_list'] as $val) { ?>
      <tr>
        <td>发生时间<?php echo $lang['nc_colon'];?><?php echo date("Y-m-d H:i:s",$val['admin_time']); ?>&emsp;&emsp;退款单号<?php echo $lang['nc_colon'];?><?php echo $val['refund_sn'];?>&emsp;&emsp;退款金额<?php echo $lang['nc_colon'];?><?php echo $lang['currency'];?><?php echo $val['refund_amount']; ?>&emsp;备注<?php echo $lang['nc_colon'];?><?php echo $val['goods_name'];?></td>
      </tr>
    <?php } ?>
    <?php } ?>
    <?php if(is_array($output['return_list']) and !empty($output['return_list'])) { ?>
      <tr>
      	<th>退货记录</th>
      </tr>
      <?php foreach($output['return_list'] as $val) { ?>
      <tr>
        <td>发生时间<?php echo $lang['nc_colon'];?><?php echo date("Y-m-d H:i:s",$val['admin_time']); ?>&emsp;&emsp;退货单号<?php echo $lang['nc_colon'];?><?php echo $val['refund_sn'];?>&emsp;&emsp;退款金额<?php echo $lang['nc_colon'];?><?php echo $lang['currency'];?><?php echo $val['refund_amount']; ?>&emsp;备注<?php echo $lang['nc_colon'];?><?php echo $val['goods_name'];?></td>
      </tr>
    <?php } ?>
    <?php } ?>
    <?php if(is_array($output['order_log']) and !empty($output['order_log'])) { ?>
      <tr>
      	<th>订单记录</th>
      </tr>
      <?php foreach($output['order_log'] as $val) { ?>
      <tr>
        <td>
          <?php echo $val['log_role']; ?> <?php echo $val['log_user']; ?>&emsp;<?php echo $lang['order_show_at'];?>&emsp;<?php echo date("Y-m-d H:i:s",$val['log_time']); ?>&emsp;<?php echo $val['log_msg']; ?>
        </td>
      </tr>
      <?php } ?>
    <?php } ?>
    <?php if(is_array($output['order_assist']) and !empty($output['order_assist'])) { ?>
    <?php $asist_type=array('apply'=>'申请协调','sys'=>'平台处理'); ?>
      <tr>
      	<th>异常记录：</th>
      </tr>
      <?php foreach($output['order_assist'] as $val) { ?>
      <tr>
        <td>
          <?php echo date("Y-m-d H:i:s",$val['add_time']); ?>&emsp;申请协调&emsp;<?php echo $val['assist_content']; ?>
        </td>
      </tr>
          <?php if(1==$val['status']){ ?>
      <tr>
          <td>
              <?php echo date("Y-m-d H:i:s",$val['audit_time']); ?>&emsp;平台处理&emsp;<?php echo $val['audit_content']; ?>&emsp;<?php echo $val['audit_msg']; ?>
          </td>
      </tr>
        <?php } ?>
      <?php } ?>
    <?php } ?>
    <?php if(is_array($output['order_complain']) and !empty($output['order_complain'])) { ?>
    <?php $complain_state=array('10'=>'新投诉','20'=>'待仲裁',99=>'已关闭'); ?>
    <?php $complain_handel_type=array('10'=>'确认投诉','20'=>'核减投诉',30=>'无法判断'); ?>
      <tr>
      	<th>投诉记录：</th>
      </tr>
      <?php foreach($output['order_complain'] as $val) { ?>
      <tr>
        <td>
            <ol>
                <li>
                    投诉时间：&emsp;<?php echo date("Y-m-d H:i:s",$val['complain_datetime']); ?>
                </li>
                <li>
                    投诉原因：&emsp;<?php echo $val['complain_content']; ?>
                </li>
                <li>
                    处理状态：&emsp;<?php if(isset($complain_state[$val['complain_state']])){ echo $complain_state[$val['complain_state']];}  ?>
                </li>
                <li>
                    处理结果：&emsp;<?php if(isset($complain_handel_type[$val['handle_type']])){ echo $complain_handel_type[$val['handle_type']];}  ?>&emsp;<?php echo $val['final_handle_message'] ?>
                </li>
            </ol>
        </td>
      </tr>
      <?php } ?>
    <?php } ?>
    </tbody>
    <tfoot>
      <tr class="tfoot">
        <td><a href="JavaScript:void(0);" class="btn" onclick="history.go(-1)"><span><?php echo $lang['nc_back'];?></span></a></td>
      </tr>
    </tfoot>
  </table>
</div>


<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=uLhko8NKBiREseUxAWE0hVYc"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.poshytip.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script>
    $(document).ready(function(){
    $('a[nctype="nyroModal"]').nyroModal();
    });
</script>

<script type="text/javascript">
    var model={
        showMap:false
    };
    function map(lng,lat)
    {
        // 百度地图API功能。初始化地图
        var map = new BMap.Map("map");    // 创建Map实例
        var aigegou=new BMap.Point(lng,lat);
        map.centerAndZoom(aigegou, 18);  // 初始化地图,设置中心点坐标和地图级别
        //    map.addControl(new BMap.MapTypeControl());   //添加地图类型控件
        map.setCurrentCity("苏州市");          // 设置地图显示的城市 此项是必须设置的
        map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
        //创建点
        var marker=new BMap.Marker(aigegou);
        map.addOverlay(marker);
        marker.enableDragging();    //可拖拽
        //    marker.setAnimation(BMAP_ANIMATION_BOUNCE);   跳动
        //拖拽停止监听
        marker.addEventListener("dragend", function(obj) {
            $('#lng').html(obj.point.lng);
            $('#lat').html(obj.point.lat);

        });
    }
    function showMap(){
        if(!model.showMap)
        {
            $("#map").css({"display":"block"});
            model.showMap=true;
            map(<?php echo floatval($output['order_info']['extend_order_common']['lng'])?>,<?php echo floatval($output['order_info']['extend_order_common']['lat'])?>);
        }

        else{
            $("#map").css({"display":"none"});
            model.showMap=false;
        }

    }

</script>