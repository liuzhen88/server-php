<?php defined('emall') or exit('Access Invalid!');?>
<link href="<?php echo ADMIN_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome.min.css" rel="stylesheet" />
<!--[if IE 7]>
  <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATES_URL;?>/css/font/font-awesome/css/font-awesome-ie7.min.css">
<![endif]-->
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_goods_evaluate']; ?></h3>
      <ul class="tab-base">
          <li><a href="index.php?act=evaluate&op=evalstore_list" ><span><?php echo $lang['admin_evaluate_list'];?></span></a></li>
          <li><a href="index.php?act=evaluate&op=evalstore_list" ><span><?php echo $lang['admin_evalstore_list'];?></span></a></li>
          <li><a href="index.php?act=evaluate&op=goods" ><span>添加假评价</span></a></li>
          <li><a href="JavaScript:void(0);" class="current"><span>查看假评价</span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>

  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul>
            <li></li>
            <li></li>
          </ul></td>
      </tr>
    </tbody>
  </table>
    <table class="table tb-type2">
      <thead>
        <tr class="thead">
          <th class="w24"></th>
          <th class="w60 align-center">评分</th>
          <th >评价内容</th>
          <th class="w108 align-center"><?php echo $lang['nc_handle'];?> </th>
        </tr>
      </thead>
      <tbody>
      <?php
            if(!empty($output['evulate_list'])):
                foreach($output['evulate_list'] as $e):
      ?>
      <tr>
          <td class="w24"></td>
          <td class=" align-center"><?php echo $e['geval_scores'] ?></td>
          <td><?php echo $e['geval_content'] ?></td>
          <td class=" align-center"><a onclick="edit(<?php echo $e['geval_id'] ?>)">编辑</a>&nbsp;|&nbsp;<a href="index.php?act=evaluate&op=del_evaluate&geval_id=<?php echo $e['geval_id'] ?>">删除</a></td>
      </tr>
      <?php endforeach;endif; ?>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td></td>
          <td colspan="16"> <div class="pagination"> <?php echo $output['page'];?> </div></td>
        </tr>
      </tfoot>
    </table>
</div>

<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/dialog/dialog.js" id="dialog_js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>

<script type="text/javascript">
var SITEURL = "<?php echo SHOP_SITE_URL; ?>";

function edit(geval_id){
    ajax_form('dialog_id', '编辑评价', 'index.php?act=evaluate&op=edit_evaluate&geval_id='+geval_id);
}

</script>
