<?php defined('emall') or exit('Access Invalid!');?>
<style>
    .btn_add_area{background:#005299;color:white;padding:5px 10px 5px 10px;}
    .btn_remove_area{background:gray;color:white;padding:5px 10px 5px 10px;}
    .area_city_span{padding:5px 10px 5px 10px; border:1px solid gray;margin-right:10px;display:inline-block;margin-bottom:5px; }
</style>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['agent'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=agent&op=agent"><span><?php echo $lang['manage'];?></span></a></li>
        <li><a href="index.php?act=agent&op=agent_add"><span><?php echo $lang['nc_new'];?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_edit'];?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>

  <form id="agent_form" method="post">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" name="agent_id" value="<?php echo $output['agent_array']['agent_id'];?>" />
    <input type="hidden" name="agent_grade" value="<?php echo $output['agent_array']['agent_grade'];?>" />
    <input type="hidden" name="ref_url" value="<?php echo getReferer();?>" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
            <td colspan="2"><label class="validation" for="agent_company_name"><?php echo $lang['agent_company_name'];?>:</label></td>
        </tr>
        <tr class="noborder">
            <td class="vatop rowform"><?php echo $output['agent_array']['agent_company_name']?></td>
          <td class="vatop tips"></td>
        </tr>
        
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="agent_member_name"><?php echo $lang['agent_member_name'];?>:</label></td>
        </tr>
        <tr class="noborder">
              <td class="vatop rowform"><?php echo $output['member_array']['member_name']?></td>
          <td class="vatop tips"></td>
        </tr>

        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="agent_mode"><?php echo $lang['agent_mode'];?>:</label></td>
        </tr>
        <tr class="noborder">
              <td class="vatop rowform"><?php echo $lang['agent_mode_' . $output['agent_array']['agent_mode']]?></td>
          <td class="vatop tips"></td>
        </tr>

        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="agent_grade"><?php echo $lang['agent_grade'];?>:</label></td>
        </tr>
        <tr class="noborder">
              <td class="vatop rowform"><?php echo $lang['agent_grade_' . $output['agent_array']['agent_grade']]?></td>
          <td class="vatop tips"></td>
        </tr>

        
        <tr class="noborder">
            <td colspan="2" class="required">
                <label class="validation"><?php echo $lang['agent_area']; ?>:</label>
                &nbsp;&nbsp; 
                <a href="javascript:void(0)" id="btn_add_area" index="1" class="btn_add_area">+添加区域</a>
                <a href="javascript:void(0)" id="btn_remove_area" class="btn_remove_area">-移除区域</a>
                <span>说明：只能移除新增区域</span>
            </td>
        </tr>
        <tr class="noborder">
          <td width="500">
               <div style="margin:5px;"> <?php echo $output['agent_area']?></div>
               <div style="margin:20px 5px 20px 5px"  id="area_tr"> 
                  
               </div>
          </td>
          <td class="vatop tips"></td>
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
                      <td class="vatop rowform"><input type="text" name="contactor" id="contactor" class="txt" value="<?php echo $output['agent_array']['agent_extend']['contactor']?>"></td>
                      <td class="vatop tips"></td>
                  </tr>
                  <tr class="noborder">
                      <td colspan="2" class="required"><label  for="tel"><?php echo $lang['tel']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><input type="text" name="tel" id="tel" class="txt" value="<?php echo $output['agent_array']['agent_extend']['tel']?>" ></td>
                      <td class="vatop tips"></td>
                  </tr>
                  <tr class="noborder">
                      <td colspan="2" class="required"><label for="email"><?php echo $lang['email']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><input type="text" name="email" id="email" class="txt" value="<?php echo $output['agent_array']['agent_extend']['email']?>"></td>
                      <td class="vatop tips"></td>
                  </tr>
                  
                  <tr class="noborder">
                      <td colspan="2" class="required"><label for="content"><?php echo $lang['content']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><textarea name="content" rows="6" class="tarea"><?php echo $output['agent_array']['agent_extend']['content']?></textarea></td>
                      <td class="vatop tips"></td>
                  </tr>
                  
                  <tr class="noborder">
                      <td colspan="2" class="required"><label for="remark"><?php echo $lang['remark']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform"><input type="text" value="<?php echo $output['agent_array']['agent_extend']['remark']?>" name="remark" id="remark" class="txt"></td>
                      <td class="vatop tips"></td>
                  </tr>
                  
                  <tr class="noborder">
                      <td colspan="2" class="required"><label class="validation">考核状态:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform">
                          <input id="check_out_0" type="radio" value="0" style="margin-bottom:6px;" name="check_out"  <?php echo $output['agent_array']['check_out'] == 0 ? 'checked="checked"' : ''?> />
                          <label for="check_out_0"><font color="red"><?php echo $lang['check_out_0'];?></font></label>
                          <input id="check_out_1" type="radio" value="1" style="margin-bottom:6px;" name="check_out"  <?php echo $output['agent_array']['check_out'] == 1 ? 'checked="checked"' : ''?> />
                          <label for="check_out_1"><font color="green"><?php echo $lang['check_out_1'];?></font></label>
                      </td>
                      <td class="vatop tips"></td>
                  </tr>
                  
                  <tr class="noborder">
                      <td colspan="2" class="required"><label class="validation"><?php echo $lang['agent_status']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform">

                          <input id="agent_status_0" type="radio" value="0" style="margin-bottom:6px;" name="agent_status"  <?php echo $output['agent_array']['agent_status'] == 0 ? 'checked="checked"' : ''?> />
                          <label for="agent_status_0"><?php echo $lang['agent_status_0']; ?></label>
                          <input id="agent_status_1" type="radio" value="1" style="margin-bottom:6px;" name="agent_status"  <?php echo $output['agent_array']['agent_status'] == 1 ? 'checked="checked"' : ''?> />
                          <label for="agent_status_1"><?php echo $lang['agent_status_1']; ?></label>
                          <input id="agent_status_2" type="radio" value="2" style="margin-bottom:6px;" name="agent_status"  <?php echo $output['agent_array']['agent_status'] == 2 ? 'checked="checked"' : ''?> />
                          <label for="agent_status_2"><?php echo $lang['agent_status_2']; ?></label>

                      </td>
                      <td class="vatop tips"></td>
                  </tr>

                  <tr class="noborder">
                      <td colspan="2" class="required"><label class="validation"><?php echo $lang['agent_predeposit']; ?>:</label></td>
                  </tr>
                  <tr class="noborder">
                      <td class="vatop rowform">

                          <input id="agent_predeposit_0" type="radio" value="0" style="margin-bottom:6px;" name="agent_predeposit"  <?php echo $output['agent_array']['agent_predeposit'] == 0 ? 'checked="checked"' : ''?> />
                          <label for="agent_predeposit_0"><?php echo $lang['agent_predeposit_0']; ?></label>
                          <input id="agent_predeposit_1" type="radio" value="1" style="margin-bottom:6px;" name="agent_predeposit"  <?php echo $output['agent_array']['agent_predeposit'] == 1 ? 'checked="checked"' : ''?> />
                          <label for="agent_predeposit_1"><?php echo $lang['agent_predeposit_1']; ?></label>

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

// 按钮先执行验证再提交表单
$(function(){
    $("#submitBtn").click(function(){
        if($("#agent_form").valid()){
            $("#agent_form").submit();
        }
    });
})

$(document).ready(function(){

        // 添加区域
        $('#btn_add_area').click(function(){
             var index = $(this).attr('index');
            index = index < 1 ? 1 : index;
            $("#area_tr").append('<div style="margin-bottom:5px;" id ="area_div_'+ index +'">新增区域' + index + '：<input type="hidden" class="txt w300"  name="agent_area_arr[]" id="area_name_' + index + '" show_grade="<?php echo $output['agent_array']['agent_grade']?>" /></div>');
            $("#area_name_" + index).nc_region();
            $(this).attr('index', ++index);

        });
        
        // 移除区域
        $('#btn_remove_area').click(function(){
            var index = $("#btn_add_area").attr('index');
            index--;
            if (index == 0) {
                return;
            }
            $("#area_div_" + index).remove();
            $("#btn_add_area").attr('index', index);
        });
        
       
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
//            agent_area_name : {
//                required : true,
//            },
            
        },
        messages : {
            agent_company_name : {
                required : '<?php echo $lang['no_null'];?>',
                remote   : '<?php echo $lang['agent_company_name_is_there'];?>'
            },
            agent_member_name : {
                required : '<?php echo $lang['no_null'];?>',
            },  
//            agent_area_name : {
//                required :  '<?php echo $lang['no_null'];?>',
//            },  
          
        },
       
    });
});
</script>