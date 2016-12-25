<?php defined('emall') or exit('Access Invalid!');?>
<style>
  #text{width: 200px;height:200px;}
</style>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['sms_send']?></h3>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="user_form" enctype="multipart/form-data" method="post" action="index.php?act=sms_send&op=sms_send">
    <table class="table tb-type2">
      <tbody>
      <tr>
        <td style="width:85px;"><label><?php echo $lang['member_district']; ?></label></td>
        <td><input type="hidden" name="agent_area_name" id="agent_area_name" /></td>
      </tr>
      <tr>
        <td style="width:85px;"><label><?php echo $lang['last_login']; ?></label></td>
        <td>
          <select name="last_login">
            <option value="0" selected="selected">-请选择-</option>
            <option value="3">3天未登录</option>
            <option value="7">7天未登录</option>
            <option value="30">1个月未登录</option>
          </select>
        </td>
      </tr>
      <input id="goods_class" name="goods_class" type="hidden" value="">
      <tr>
        <td style="width:85px;"><label><?php echo $lang['gc_info']; ?></label></td>
        <td id="gcategory">
          <select id="gcategory_class1" style="width: auto;">
            <option value="0">-请选择-</option>
            <?php if(!empty($output['gc_list']) && is_array($output['gc_list']) ) {?>
              <?php foreach ($output['gc_list'] as $gc) {?>
                <option value="<?php echo $gc['gc_id'];?>"><?php echo $gc['gc_name'];?></option>
              <?php }?>
            <?php }?>
          </select>
        </td>
      <tr>
      <tr>
        <td style="width:85px;"><label><?php echo $lang['member_name']; ?></label></td>
        <td>
          <input name="member_name" type="text"/>
        </td>
      </tr>
      <tr>
        <td style="width:85px;"><label><?php echo $lang['sms_type']; ?></label></td>
        <td>
          <select name="sms_temp" id="sms_temp">
            <option value="0" selected="selected">-请选择-</option>
            <option value="1">模板一</option>
            <option value="2">模板二</option>
            <option value="3">模板三</option>
          </select>
        </td>
      </tr>
      <tr>
        <td style="width:85px;"><label><?php echo $lang['sms_content']; ?></label></td>
        <td>
          <textarea name="sms_content" id="text"></textarea>
        </td>
      </tr>
      <tr>
        <td style="width:85px;"></td>
        <td>
          <label style="color: red">替换文本框内**内容</label>
        </td>
      </tr>
      <tr>
        <td style="width:85px;"></td>
        <td><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
      </tr>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script>
  var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';
  $(function(){
    $("#agent_area_name").nc_region();
  });
  $(document).ready(function(){
    gcategoryInit("gcategory");
    $("#sms_temp").change(function(){
      if($("#sms_temp").val()==0){
        $("#text").val("");
      }
      if($("#sms_temp").val()==1){
        $("#text").val("模板一***");
      }
      if($("#sms_temp").val()==2){
        $("#text").val("模板二***");
      }
      if($("#sms_temp").val()==3){
        $("#text").val("模板三***");
      }
    });
  });
  $("#submitBtn").click(function(){
    //获取分类id
    var category_id = '';
    var validation = true;
    $('#gcategory').find('select').each(function() {
      if(parseInt($(this).val(), 10) > 0) {
        category_id += $(this).val() + ',';
      } else {
        validation = false;
      }
    });
    $('#goods_class').val(category_id);
      $("#user_form").submit();
  });
</script>


