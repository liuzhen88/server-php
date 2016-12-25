<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['agent'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['manage'];?></span></a></li>
        <li><a href="index.php?act=agent&op=agent_add" ><span><?php echo $lang['nc_new'];?></span></a></li>
        <li><a href="index.php?act=agent&op=store_manage" ><span><?php echo $lang['store_manage'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12" class="nobg"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul>
            <li><?php echo $lang['agent_help1'];?></li>
            <li><?php echo $lang['agent_help2'];?></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <form method="get" name="formSearch" id="formSearch">
    <input type="hidden" value="agent" name="act">
    <input type="hidden" value="agent" name="op">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="agent_company_name"><?php echo $lang['agent_company_name'];?></label></th>
          <td><input type="text" value="<?php echo $output['like_ac_name'];?>" name="like_ac_name" id="like_ac_name" class="txt"></td>
          <th><label><?php echo $lang['agent_mode']; ?></label></th>
          <td><select name="agent_mode">
                <option value=''><?php echo $lang['nc_please_choose']; ?>...</option>
                <option value="1" <?php echo $output['agent_mode'] == '1' ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_mode_1']; ?></option>
                <option value="2" <?php echo $output['agent_mode'] == '2' ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_mode_2']; ?></option>
            </select></td>
          <th><label><?php echo $lang['agent_grade']; ?></label></th>
          <td><select name="agent_grade">
                <option value=''><?php echo $lang['nc_please_choose']; ?>...</option>
                <?php for ($i=1;$i<=5;$i++) : ?>
                     <option value="<?php echo $i;?>" <?php echo $output['agent_grade'] == $i ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_grade_' . $i]; ?></option>
                <?php endfor;?>
            </select></td>
           <th><label><?php echo $lang['agent_area']; ?></label></th>
           <td><input type="hidden" class="txt w300"  name="agent_area_name" id="agent_area_name" /></td>

          <th><label for="query_start_time">时间</label></th>
          <td><input class="txt date" type="text" value="<?php echo $output['query_start_time'];?>" id="query_start_time" name="query_start_time">
            <label for="query_start_time">~</label>
            <input class="txt date" type="text" value="<?php echo $output['query_end_time'];?>" id="query_end_time" name="query_end_time"/></td>
          
            <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query']; ?>">&nbsp;</a>
            </td>
        </tr>
        
      </tbody>
    </table>
  </form>
  <form method='post'>
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2 nobdb">
      <thead>
        <tr class="thead">
          <th><input type="checkbox" class="checkall" id="checkall_1"></th>
          <th><?php echo $lang['agent_id'];?></th>
          <th><?php echo $lang['agent_company_name'];?></th>
          <th><?php echo $lang['agent_mode'];?></th>
          <th><?php echo $lang['agent_grade'];?></th>
          <th><?php echo $lang['agent_area'];?></th>
          <th><?php echo $lang['create_time'];?></th>
          <th><?php echo $lang['update_time'];?></th>
          <th><?php echo $lang['check_out'];?></th>
          <th><?php echo $lang['agent_status'];?></th>
          <th><?php echo $lang['agent_predeposit'];?></th>
          <th><?php echo $lang['agent_point'];?></th>
          <th><?php echo $lang['agent_order'];?></th>
          <th><?php echo $lang['nc_handle'];?></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['agent_list']) && is_array($output['agent_list'])){ ?>
        <?php foreach($output['agent_list'] as $k => $data){ ?>
        <tr class="hover edit">
          <td class="w36"><input type="checkbox" name='check_agent_id[]' value="<?php echo $data['agent_id'];?>" class="checkitem"></td>
          <td><?php echo $data['agent_id'];?></td>
          <td><?php echo $data['agent_company_name'];?></td>
          <td class="nowrap"><?php echo $lang['agent_mode_' . (int)$data['agent_mode']]; ?></td>
          <td class="nowrap"><?php echo $lang['agent_grade_' . (int)$data['agent_grade']]; ?></td>
          <td class="nowrap">
              <?php echo empty($data['area_list']) ? '' : implode("<br>", $data['area_list'])?>
          </td>
          <td class="nowrap"><?php echo date('Y-m-d H:i:s',$data['create_time']);?></td>
          <td class="nowrap"><?php echo date('Y-m-d H:i:s',$data['update_time']);?></td>
          <td class="nowrap"><?php echo $lang['check_out_' . (int)$data['check_out']]; ?></td>
          <td class="nowrap"><?php echo $lang['agent_status_' . (int)$data['agent_status']]; ?></td>
          <td class="nowrap"><?php echo $lang['agent_predeposit_' . (int)$data['agent_predeposit']]; ?></td>
          <td class="nowrap"><?php echo $data['point']; ?></td>
          <td class="nowrap"><?php echo $data['order_count']; ?></td>
          <td class="w84"><span>
                  <a href="index.php?act=agent&op=agent_edit&agent_id=<?php echo $data['agent_id'];?>"><?php echo $lang['nc_edit'];?></a> 
              </span></td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="10"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <?php if(!empty($output['agent_list']) && is_array($output['agent_list'])){ ?>
        <tr id="batchAction" >
          <td></td>
          <td colspan="16" id="dataFuncs">
            <div class="pagination"> <?php echo $output['page'];?> </div></td>
            </td>
        </tr>
        <?php } ?>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.edit.js" charset="utf-8"></script>
<script>
$('#query_start_time').datepicker({dateFormat: 'yy-mm-dd'});
$('#query_end_time').datepicker({dateFormat: 'yy-mm-dd'});
var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';
$(function(){
    $("#agent_area_name").nc_region();
  $('#ncsubmit').click(function(){
    $('#formSearch').submit();
  });
});
</script>