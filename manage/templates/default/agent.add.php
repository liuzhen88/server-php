<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['agent'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=agent&op=agent"><span><?php echo $lang['manage'];?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>

  <form id="agent_form" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="agent_company_name"><?php echo $lang['agent_company_name'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="" name="agent_company_name" id="agent_company_name" class="txt"></td>
          <td class="vatop tips"></td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="agent_member_name"><?php echo $lang['agent_member_name'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><input type="text" value="" name="agent_member_name" id="agent_member_name" class="txt"></td>
          <td class="vatop tips">若会员账号不存在，系统将自动创建一个会员账号，初始密码为123456</td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="agent_grade"><?php echo $lang['agent_grade']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
              <select name="agent_grade" id="agent_grade">
                  <option value="1" <?php echo $_GET['agent_grade'] == '1' ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_grade_1']; ?></option>
                  <option value="2" <?php echo $_GET['agent_grade'] == '2' ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_grade_2']; ?></option>
                  <option value="3" <?php echo $_GET['agent_grade'] == '3' ? 'selected="selected"' : ''; ?>><?php echo $lang['agent_grade_3']; ?></option>
              </select>
              
          </td>
          <td class="vatop tips"></td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation"><?php echo $lang['agent_area']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td width="500">
             <input type="hidden" class="txt w300"  name="agent_area_name" id="agent_area_name"  show_grade="1" />
          </td>
          <td class="vatop tips">添加多个代理区域可在编辑进行</td>
        </tr>
        
       <tr>
          <td class="" colspan="2"><table class="table tb-type2 nomargin">
              <thead>
                <tr class="space">
                  <th colspan="16"><?php echo $lang['detail_info']?></th>
                </tr>
              </thead>
              
              <tbody>
                  <tr class="noborder">
                      <td colspan="2" class="required"><label  for="contactor"><?php echo $lang['contactor']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><input type="text" value="" name="contactor" id="contactor" class="txt"></td>
                      <td class="vatop tips"></td>
                  </tr>
                  <tr class="noborder">
                      <td colspan="2" class="required"><label  for="tel"><?php echo $lang['tel']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><input type="text" value="" name="tel" id="tel" class="txt"></td>
                      <td class="vatop tips"></td>
                  </tr>
                  <tr class="noborder">
                      <td colspan="2" class="required"><label for="email"><?php echo $lang['email']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><input type="text" value="" name="email" id="email" class="txt"></td>
                      <td class="vatop tips"></td>
                  </tr>
                  
                  <tr class="noborder">
                      <td colspan="2" class="required"><label for="content"><?php echo $lang['content']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><textarea name="content" rows="6" class="tarea"></textarea></td>
                      <td class="vatop tips"></td>
                  </tr>
                  
                  <tr class="noborder">
                      <td colspan="2" class="required"><label for="remark"><?php echo $lang['remark']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><input type="text" value="" name="remark" id="remark" class="txt"></td>
                      <td class="vatop tips"></td>
                  </tr>
                  
                  <tr class="noborder">
                    <td colspan="2" class="required"><label class="validation">考核状态:</label></td>
                  </tr>
                  <tr class="noborder">
                        <td class="vatop rowform">
                          <input id="check_out_0" type="radio" value="0" style="margin-bottom:6px;" name="check_out" checked="checked" />
                          <label for="check_out_0"><?php echo $lang['check_out_0'];?></label>
                          <input id="check_out_1" type="radio" value="1" style="margin-bottom:6px;" name="check_out" />
                          <label for="check_out_1"><?php echo $lang['check_out_1'];?></label>
                    </td>
                    <td class="vatop tips"></td>
                  </tr>
                  
                  <tr class="noborder">
                    <td colspan="2" class="required"><label class="validation"><?php echo $lang['agent_status']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                        <td class="vatop rowform">

                        <input id="agent_status_0" type="radio" checked="checked" value="0" style="margin-bottom:6px;" name="agent_status" />
                        <label for="agent_status_0"><?php echo $lang['agent_status_0'];?></label>
                        <input id="agent_status_1" type="radio" value="1" style="margin-bottom:6px;" name="agent_status" />
                        <label for="agent_status_1"><?php echo $lang['agent_status_1'];?></label>
                        <input id="agent_status_1" type="radio" value="2" style="margin-bottom:6px;" name="agent_status" />
                        <label for="agent_status_2"><?php echo $lang['agent_status_2'];?></label>

                    </td>
                    <td class="vatop tips"></td>
                  </tr>
                  
              </tbody>
            </table>
            
      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script src="<?php echo RESOURCE_SITE_URL."/js/jquery-ui/i18n/zh-CN.js";?>" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"  />
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script>
    
var SHOP_SITE_URL = '<?php echo SHOP_SITE_URL;?>';

//按钮先执行验证再提交表单
$(function(){
    $("#submitBtn").click(function(){
        if($("#agent_form").valid()){
            $("#agent_form").submit();
        }
    });
});

$(document).ready(function(){
    
       // 代理商级别控制联动菜单处理
       $("#agent_grade").change(function(){
           var grade = $(this).val();
           var area_obj = $("#agent_area_name");
           area_obj.attr('show_grade', grade);
           area_obj.val('');
           area_obj.nextAll('select').remove();
           area_obj.nc_region();
       });
       $("#agent_area_name").nc_region();
       
       
       $('#agent_form').validate({
        errorPlacement: function(error, element){
	     error.appendTo(element.parent().parent().prev().find('td:first'));
        },

        rules : {
//            agent_company_name : {
//                required : true,
//                remote   : {                
//                url :'index.php?act=agent&op=ajax&branch=check_agent_company_name',
//                type:'get',
//                data:{
//                    agent_company_name : function(){
//                        return $('#agent_company_name').val();
//                    }
//                  }
//                }
//            },
//            agent_area_name : {
//                required : true,
//                remote   : {                
//                    url :'index.php?act=agent&op=ajax&branch=check_agent_area_name',
//                    type:'get',
//                    data:{
//                        agent_area_name : function(){
//                            return $('#agent_area_name').val();
//                        },
//                        agent_grade : function(){
//                            return $('#agent_grade').val();
//                        }
//                     }
//                 },
//            },
            agent_area_name : {
                required : true,
            },
            
        },
        messages : {
            agent_company_name : {
                required : '<?php echo $lang['no_null'];?>',
                remote   : '<?php echo $lang['agent_company_name_is_there'];?>'
            },
            agent_member_name : {
                required : '<?php echo $lang['no_null'];?>',
            },  
            agent_area_name : {
                required :  '<?php echo $lang['no_null'];?>',
            },  
          
        },
       
    });
});
</script>