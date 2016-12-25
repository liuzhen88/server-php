<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['order_manage'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['manage'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <table class="table tb-type2 nobdb">
    <thead>
      <tr class="thead">
        <th><?php echo $lang['order_number'];?></th>
        <th><?php echo $lang['store_name'];?></th>
        <th><?php echo $lang['buyer_name'];?></th>
        <th class="align-center"><?php echo $lang['order_time'];?></th>
        <th class="align-center"><?php echo $lang['order_total_price'];?></th>
        <th class="align-center"><?php echo $lang['payment'];?></th>
        <th class="align-center"><?php echo $lang['order_state'];?></th>
        <th class="align-center"><?php echo $lang['nc_handle'];?></th>
      </tr>
    </thead>
    <tbody>
      <?php if(count($output['order_list'])>0){?>
      <?php foreach($output['order_list'] as $order){?>
      <tr class="hover">
        <td><?php echo $order['order_sn'];?></td>
        <td><?php echo $order['order_type']==3 ? $order['league_store_name']:$order['store_name'];?></td>
        <td><?php echo $order['buyer_name'];?></td>
        <td class="nowrap align-center"><?php echo date('Y-m-d H:i:s',$order['add_time']);?></td>
        <td class="align-center"><?php echo $order['order_amount'];?></td>
        <td class="align-center"><?php echo orderPaymentName($order['payment_code']);?></td>
        <td class="align-center"><?php echo orderState($order);?></td>
        <td class="w144 align-center"><a href="index.php?act=order&op=adt_assist_show_order&order_id=<?php echo $order['order_id'];?>"><?php echo $lang['nc_view'];?></a>

        <!-- 取消订单 -->
    		<?php if($order['if_cancel']) {?>
        	| <a href="javascript:void(0)" onclick="if(confirm('<?php echo $lang['order_confirm_cancel'];?>')){location.href='index.php?act=order&op=change_state&state_type=cancel&order_id=<?php echo $order['order_id']; ?>'}">
        	<?php echo $lang['order_change_cancel'];?></a>
        	<?php }?>

        	<!-- 收款 -->
    		<?php if($order['if_system_receive_pay']) {?>
	        	| <a href="index.php?act=order&op=change_state&state_type=receive_pay&order_id=<?php echo $order['order_id']; ?>">
	        	<?php echo $lang['order_change_received'];?></a>
    		<?php }?>
        	</td>
      </tr>
      <?php }?>
      <?php }else{?>
      <tr class="no_data">
        <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
      </tr>
      <?php }?>
    </tbody>
    <tfoot>
      <tr class="tfoot">
        <td colspan="15" id="dataFuncs"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
      </tr>
    </tfoot>
  </table>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript">
$(function(){
    $('#query_start_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('index');$('#formSearch').submit();
    });
});
</script> 
