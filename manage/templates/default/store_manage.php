<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['store_manage'];?></h3>
      <ul class="tab-base">
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['manage'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" action="index.php" id="formSearch">
    <input type="hidden" name="act" value="agent" />
    <input type="hidden" name="op" value="store_manage" />
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
          <th><label for="agent_company_name"><?php echo $lang['agent_member_name'];?></label></th>
          <td><input type="text" value="<?php echo $output['member_name'];?>" name="member_name" id="member_name" class="txt"></td>
          <th><label for="query_start_time"><?php echo $lang['create_time'];?></label></th>
          <td><input class="txt date" type="text" value="<?php echo $output['query_start_time'];?>" id="query_start_time" name="query_start_time">
            <label for="query_start_time">~</label>
            <input class="txt date" type="text" value="<?php echo $output['query_end_time'];?>" id="query_end_time" name="query_end_time"/></td>
            <td>
<!--              <a href="javascript:document.formSearch.submit();" class="btn-search " title="--><?php //echo $lang['nc_query']; ?><!--">&nbsp;</a>-->
              <a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
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
          <th><?php echo $lang['agent_member_name'];?></th>
          <th><?php echo $lang['store_count'];?></th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['agent_list']) && is_array($output['agent_list'])){ ?>
        <?php foreach($output['agent_list'] as $k => $data){ ?>
        <tr class="hover edit">
          <td class="w36"><input type="checkbox" name='check_agent_id[]' value="<?php echo $data['agent_id'];?>" class="checkitem"></td>
          <td><?php echo $data['agent_id'];?></td>
          <td><?php echo $data['agent_company_name'];?></td>
          <td><?php echo $data['member_name'];?></td>
          <td><?php echo $data['store_count'];?></td>
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
          <tr style="text-align: center;width: 100%"> <td colspan="17" style="text-align: center;width: 100%;height:20px;padding-top: 30px;font-size:16px;"> <div> 商户总数：<?php echo $output['total'];?></div></td></tr>
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
<script>
  $(function(){
    $('#query_start_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
      $('#formSearch').submit();
    });
  });
</script>