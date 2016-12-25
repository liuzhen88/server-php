<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['admin_rebate_log_title'];?></h3>     
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" name="act" value="rebate">
    <input type="hidden" name="op" value="index">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label><?php echo $lang['admin_membername']; ?></label></th>
          <td><input type="text" name="mname" class="txt" value='<?php echo $_GET['mname'];?>'></td><th><?php echo $lang['admin_rebate_addtime']; ?></th>
          <td><input type="text" id="stime" name="stime" class="txt date" value="<?php echo $_GET['stime'];?>" >
            <label>~</label>
            <input type="text" id="etime" name="etime" class="txt date" value="<?php echo $_GET['etime'];?>" ></td><td><select name="stage">
              <option value="" <?php if (!$_GET['stage']){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_stage']; ?></option>
              <option value="2" <?php if ($_GET['stage'] == '2'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_xianshang']; ?></option>
              <option value="1" <?php if ($_GET['stage'] == '1'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_bentu']; ?></option>              
          </select></td>
          </tr><tr><th><label><?php echo $lang['admin_rebate_order']; ?></label></th><td><input type="text" name="order_sn" class="txt" value='<?php echo $_GET['aname'];?>'></td>

          <th><?php echo $lang['admin_rebate_user']; ?></th>
          <td><select name="user_type"> 
		      <option value="" <?php if (!$_GET['user_type']){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_type']; ?></option>             
              <option value="1" <?php if ($_GET['user_type'] == '1'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_sale']; ?></option>
              <option value="2" <?php if ($_GET['user_type'] == '2'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_oneinviter']; ?></option>
              <option value="3" <?php if ($_GET['user_type'] == '3'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_twoinviter']; ?></option>
			  <option value="4" <?php if ($_GET['user_type'] == '4'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_store']; ?></option>
			  <option value="5" <?php if ($_GET['user_type'] == '5'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_area_agent']; ?></option>
			  <option value="6" <?php if ($_GET['user_type'] == '6'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_city_agent']; ?></option>
		      <option value="7" <?php if ($_GET['user_type'] == '7'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_province_agent']; ?></option>
			  <option value="8" <?php if ($_GET['user_type'] == '8'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_platform']; ?></option>
			  <option value="9" <?php if ($_GET['user_type'] == '9'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_area_old']; ?></option>
			  <option value="10" <?php if ($_GET['user_type'] == '10'){echo 'selected=selected';}?>><?php echo $lang['admin_rebate_user_two_old']; ?></option>
              
          </select></td>
          <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
          
            <?php if($output['search_field_value'] != '' or $output['search_sort'] != ''){?>
            <a href="index.php?act=member&op=member" class="btns "><span><?php echo $lang['nc_cancel_search']?></span></a>
            <?php }?></td>
        </tr>
      </tbody>
    </table>
  </form><div style="text-align:right;"><a class="btns" href="javascript:void(0);" id="ncexport"><span><?php echo $lang['nc_export'];?>Excel</span></a></div>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title"><h5><?php echo $lang['nc_prompts'];?></h5><span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td>
        <ul>
            <li><?php echo $lang['admin_points_log_help1'];?></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <table class="table tb-type2">
    <thead>
      <tr class="thead">
        <th><?php echo $lang['admin_membername']; ?></th>
        <th><?php echo $lang['admin_goods_nname']; ?></th>
        <th class="align-center"><?php echo $lang['admin_order_sn']; ?></th>
        <th class="align-center"><?php echo $lang['admin_rebate_amount']; ?></th>
		 <th class="align-center"><?php echo $lang['admin_rebate_user']; ?></th>
        <th class="align-center"><?php echo $lang['admin_rebate_stage']; ?></th>
       
      </tr>
    </thead>
    <tbody>
      <?php if(!empty($output['list_log']) && is_array($output['list_log'])){ ?>
      <?php foreach($output['list_log'] as $k => $v){?>
      <tr class="hover">
        <td><?php echo $v['member_name'];?></td>
        <td><?php echo $v['goods_name'];?></td>
        <td class="align-center"><?php echo $v['order_sn'];?></td>
        <td class="nowrap align-center"><?php echo $v['rebate'];?></td>
		<td class="align-center"><?php 
				switch ($v['user_type']){
              		case '1':
              			echo $lang['admin_rebate_user_sale'];
              			break;
              		case '2':
              			echo $lang['admin_rebate_user_oneinviter'];
              			break;
					case '3':
              			echo $lang['admin_rebate_user_twoinviter'];
              			break;
					case '4':
              			echo $lang['admin_rebate_user_store'];
              			break;
					case '5':
              			echo $lang['admin_rebate_user_area_agent'];
              			break;
					case '6':
              			echo $lang['admin_rebate_user_city_agent'];
              			break;
					case '7':
              			echo $lang['admin_rebate_user_province_agent'];
              			break;
					case '8':
              			echo $lang['admin_rebate_user_platform'];
              			break;
					case '9':
              			echo $lang['admin_rebate_user_area_old'];
              			break;
					case '10':
              			echo $lang['admin_rebate_user_two_old'];
              			break;
              		                  		
	          }?></td>
        <td class="align-center"><?php 
				switch ($v['rebate_type']){
              		case '1':
              			echo $lang['admin_rebate_user_bentu'];
              			break;
              		case '2':
              			echo $lang['admin_rebate_user_xianshang'];
              			break;
              		                  		
	          }?></td>
       
      </tr>
      <?php } ?>
      <?php }else { ?>
      <tr class="no_data">
        <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
      </tr>
      <?php } ?>
    </tbody>
    <tfoot>
      <tr style="text-align: center;width: 100%"> <td colspan="15" style="text-align: center;width: 100%;height:20px;padding-top: 30px;font-size:16px;"> <div>返利总和<?php echo $output['sum'];?></div></td></tr>
      <tr class="tfoot">
        <td colspan="15"><div class="pagination"> <?php echo $output['show_page'];?> </div></td>
      </tr>
    </tfoot>
  </table>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script> 
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script language="javascript">
$(function(){
	$('#stime').datepicker({dateFormat: 'yy-mm-dd'});
	$('#etime').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncexport').click(function(){
    	$('input[name="op"]').val('export_step1');
    	$('#formSearch').submit();
    });
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('pointslog');$('#formSearch').submit();
    });
});
</script>
