<?php defined('emall') or exit('Access Invalid!');?>
<style type="text/css">
.d_inline {
      display:inline;
}
</style>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['store'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=store&op=store"><span><?php echo $lang['manage'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <div class="homepage-focus" nctype="editStoreContent">
    <form id="store_form" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="store_id" value="<?php echo $output['store_array']['store_id'];?>" />
    <input type="hidden" name="id" value="<?php echo $output['joinin_detail']['id'];?>" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label><?php echo $lang['store_user_name'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['store_array']['member_name'];?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label class="validation" for="store_name">店铺名称:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="<?php echo $output['store_array']['store_name'];?>" id="store_name" name="store_name" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
        <?php
          if($output['store_array']['store_type']==1) {
        ?>
        <tr>
          <td colspan="2" class="required"><label class="validation" for="store_commis_rates">店铺返利比率:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="<?php echo $output['store_array']['store_commis_rates'];?>" id="store_commis_rates" name="store_commis_rates" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
        <?php
          }
        ?>
        <tr>
            <td colspan="2" class="required"><label class="validation" for="contacts_name">联系人:</label></td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform"><input type="text" value="<?php echo $output['joinin_detail']['contacts_name'];?>" id="contacts_name" name="contacts_name" class="txt"></td>
            <td class="vatop tips"></td>
        </tr>
      </tbody>

      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
    </form>
</div>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/i18n/zh-CN.js";?>" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript">
var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';
$(function(){
    //按钮先执行验证再提交表单
    $("#submitBtn").click(function(){
         $("#store_form").submit();
    });
});
</script>