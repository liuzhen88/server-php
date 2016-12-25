<?php defined('emall') or exit('Access Invalid!');?>

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
            <li><a href="index.php?act=store&op=adt_store_joinin_detail&id=<?php echo $_GET['store_id'] ?>"><span>跑腿邦店铺详情</span></a></li>
            <li><a href="JavaScript:void(0);" class="current" ><span>店铺粉丝</span></a></li>
        </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form method="get" name="formSearch" id="formSearch">
  <input type="hidden" value="store" name="act">
  <input type="hidden" value="adt_fans" name="op">
  <input type="hidden" value="<?php echo $_GET['store_id'] ?>" name="store_id">
  <table class="tb-type1 noborder search">
  <tbody>
    <tr><th><label>粉丝来源</label></th>
      <td><select name="register_from">
          <option value="0"><?php echo $lang['nc_please_choose'];?>...</option>
          <option value="1" <?php if(isset($_GET['register_from']) && $_GET['register_from']==1) echo 'selected'; ?> >邀请码注册</option>
          <option value="2" <?php if(isset($_GET['register_from']) && $_GET['register_from']==2) echo 'selected'; ?> >二维码注册</option>
        </select></td>
        <th><label>消费时间区间</label></th>
        <td>
            <input class="txt date" type="text" value="<?php echo $_GET['query_start_time'];?>" id="query_start_time" name="query_start_time" style="width:110">
            <label for="query_start_time">~</label>
            <input class="txt date" type="text" value="<?php echo $_GET['query_end_time'];?>" id="query_end_time" name="query_end_time"  style="width:110" />
        </td>
        <th><label>会员帐号</label></th>
        <td><input type="text" value="<?php echo $_GET['member_name'];  ?>" name="member_name" ></td>
        <td><a href="javascript:void(0);" id="ncsubmit" class="btn-search " title="<?php echo $lang['nc_query'];?>">&nbsp;</a>
        <?php if($_GET['query_start_time'] != '' or $_GET['query_end_time'] != '' or !empty($_GET['register_from']) or $_GET['member_name']!=''){?>
        <a href="index.php?act=store&op=adt_fans&store_id=<?php echo $_GET['store_id'] ?>" class="btns " title="<?php echo $lang['nc_cancel_search'];?>"><span><?php echo $lang['nc_cancel_search'];?></span></a>
        <?php }?></td>
    </tr></tbody>
  </table>
  </form>
   <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul>
            <li><?php echo $lang['store_help1'];?></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
  <form method="post" id="store_form">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="align-center">用户邀请码</th>
          <th class="align-center">用户昵称</th>
          <th class="align-center">帐号</th>
          <th class="align-center">注册时间</th>
          <th class="align-center">消费单数</th>
          <th class="align-center">消费总额</th>
          <th class="align-center">匹配了最多的商户</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($output['fans_list']) && is_array($output['fans_list'])){ ?>
        <?php foreach($output['fans_list'] as $k => $v){ ?>
        <tr >
          <td class="align-center"><?php echo $v['invitation'];?></td>
          <td class="align-center"><?php echo $v['member_truename'];?></td>
          <td class="align-center"><?php echo $v['member_name'];?></td>
          <td class="align-center"><?php echo date('Y-m-d H:i:s',intval($v['member_time']));?></td>
          <td class="align-center"><?php echo $v['order_count'];?></td>
          <td class="align-center"><?php echo $v['order_amount_total'];?></td>
          <td class="align-center"><?php echo $output['member_fav_store'][$v['buyer_id']]['league_store_name'];?></td>
        </tr>
        <?php } ?>
        <?php }else { ?>
        <tr class="no_data">
          <td colspan="15"><?php echo $lang['nc_no_record'];?></td>
        </tr>
        <?php } ?>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td></td>
          <td colspan="16">
            <div class="pagination"><?php echo $output['page'];?></div></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  /><script>
$(function(){
    $('#query_start_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#query_end_time').datepicker({dateFormat: 'yy-mm-dd'});
    $('#ncsubmit').click(function(){
    	$('input[name="op"]').val('adt_fans');$('#formSearch').submit();
    });
});

</script>
