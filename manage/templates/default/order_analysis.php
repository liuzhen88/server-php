<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['order_analysis'];?></h3>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" action="index.php" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="order" />
    <input type="hidden" name="op" value="order_analysis" />
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="query_start_time"><?php echo $lang['between_time'];?></label></th>
          <td><input class="txt date" type="text" value="<?php echo $_GET['query_start_time'];?>" id="query_start_time" name="query_start_time">
            <label for="query_start_time">~</label>
            <input class="txt date" type="text" value="<?php echo $_GET['query_end_time'];?>" id="query_end_time" name="query_end_time"/></td>
         <th><?php echo $lang['nc_area_config'];?></th>
         <td><input type="hidden" name="agent_area_name" id="agent_area_name" /></td>
            <th><?php echo $lang['rebate_type'];?></th>
            <td>
                <input type="checkbox" name="rebate_type[]" value="1" />本土
                <input type="checkbox" name="rebate_type[]" value="2" />商城
                <input type="checkbox" name="rebate_type[]" value="3" />跑腿帮
            </td>
          <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
            
            </td>
        </tr>
      </tbody>
    </table>
  </form>
    <table class="table tb-type2" id="prompt">
        <tbody>
        <tr class="space odd">
            <th colspan="12"><div class="title"><h5><?php echo $lang['nc_prompts'];?></h5><span class="arrow"></span></div></th>
        </tr>
        <tr>
            <td>
                <ul>
                    <li><?php echo $lang['analysis_help'];?></li>
                </ul></td>
        </tr>
        </tbody>
    </table>
  <table class="table tb-type2 nobdb">
    <thead>
      <tr class="thead">
        <th><?php echo $lang['store_amount'];?></th>
        <th><?php echo $lang['member_amount'];?></th>
        <th><?php echo $lang['order_amount'];?></th>
        <th><?php echo $lang['order_price_from'];?></th>
        <th><?php echo $lang['rebate_amount'];?></th>
      </tr>
    </thead>
    <tbody>
      <tr class="hover">
          <td><?php  echo $output['result_list']['store_sum'];?></td>
          <td><?php  echo $output['result_list']['member_sum'];?></td>
          <td><?php echo $output['result_list']['order_count'];?></td>
          <td><?php echo $output['result_list']['order_sum'];?></td>
          <td><?php echo $output['result_list']['rebate_sum'];?></td>
      </tr>
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
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript">
 var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';
$(function(){
    $("#agent_area_name").nc_region();
    $('#query_start_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('order_analysis');$('#formSearch').submit();
    });
});
</script>

