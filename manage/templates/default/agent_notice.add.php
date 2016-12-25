<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3>代理商/商户公告</h3>
      <ul class="tab-base">
        <li><a href="index.php?act=agent_notice&op=add_notice" ><span>新增公告</span></a></li>
      </ul>
    </div>
  </div>

  <div class="fixed-empty"></div>
  <form id="add_form" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
        <tr style="noborder">
          <td colspan="2" class="required"><label class="validation" >公告类型:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><select name="notice_type">
              <option value="1" <?php if($output['notice_list']['notice_type']== 1){?> selected="true" <?php  } ?>  >代理商公告</option>
              <option value="2" <?php if($output['notice_list']['notice_type']==2) {?> selected="true" <?php } ?> >商户公告</option>
              </optgroup>
            </select>
          </td>
        </tr>
        <tr class="noborder">
          <td colspan="2"><label class="validation" for="activity_title">公告内容:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
            <textarea name="notice_agent" id="activity_desc" rows="6" class="tarea" ><?php echo $output['notice_list']['notice_content']; ?></textarea>
          </td>
        </tr>
      </tbody>

      <tfoot>
        <tr class="tfoot">
          <td colspan="2"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<link type="text/css" rel="stylesheet" href="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/themes/ui-lightness/jquery.ui.css";?>"/>
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/jquery.ui.js";?>"></script> 
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/i18n/zh-CN.js";?>" charset="utf-8"></script> 
<script>
//按钮先执行验证再提交表单
$(function(){$("#submitBtn").click(function(){
    if($("#add_form").valid()){
     $("#add_form").submit();
	}
	});
});
</script> 