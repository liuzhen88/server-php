<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['link_index_mb_category'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=mb_category&op=mb_category_list" ><span><?php echo $lang['nc_manage'];?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['nc_new'];?></span></a></li>
      </ul>
    </div>
  </div>
    <div class="fixed-empty"></div>
  <form id="link_form" method="post" enctype="multipart/form-data">
    <input type="hidden" value="ok" name="form_submit" />
    <table class="table tb-type2">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label class="validation" for="link_catetory"> <?php echo $lang['link_index_category'];?>:</label></td>
        </tr>
        
        <tr class="noborder">
          <td class="vatop rowform" id="gcategory">
		  <input type="hidden" value="" class="mls_id" name="class_id" />
            <input type="hidden" value="" class="mls_name" name="class_name" />
            <select  name="link_category" id="link_category">
              <option value="0"><?php echo $lang['nc_please_choose'];?>...</option>
              <?php if(!empty($output['goods_class'])){ ?>
              <?php foreach($output['goods_class'] as $k => $v){ ?>
              <?php if ($v['gc_parent_id'] == 0) {?>
              <option value="<?php echo $v['gc_id'];?>"><?php echo $v['gc_name'];?></option>
              <?php } ?>
              <?php } ?>
              <?php } ?>
            </select></td>
          <td class="vatop tips"><?php echo $lang['spec_common_belong_class_tips'];?></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label class="validation" for="link_pic"><?php echo $lang['link_index_pic_sign'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform">
          <span class="type-file-box">
            <input type="file" name="link_pic" id="link_pic" class="type-file-file" size="30" >
          </span>
            </td>
          <td class="vatop tips">展示图片，建议大小72px*72px</td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15"><a id="submitBtn" class="btn" href="JavaScript:void(0);"> <span><?php echo $lang['nc_submit'];?></span> </a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript">
$(function(){
    var textButton="<input type='text' name='textfield' id='textfield1' class='type-file-text' /><input type='button' name='button' id='button1' value='' class='type-file-button' />"
	$(textButton).insertBefore("#link_pic");
	$("#link_pic").change(function(){
	$("#textfield1").val($("#link_pic").val());
});
});
</script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/common_select.js" charset="utf-8"></script> 
<script type="text/javascript">
$(function(){
	//表单验证
    $('#spec_form').validate({
        errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },

        rules : {
            link_category  : {
                required : true,
            },
            link_pic  : {
                required : true,
            }
        },
        messages : {
            link_category  : {
                required : '<?php echo $lang['link_add_category_null'];?>',
            },
            link_pic  : {
                required : '<?php echo $lang['link_add_pic_null'];?>',
            }
        }
    });

    //按钮先执行验证再提交表单
    $("#submitBtn").click(function(){
        if($("#link_form").valid()){
        	$("#link_form").submit();
    	}
    });
});

gcategoryInit('gcategory');
</script> 
