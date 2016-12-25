<?php defined('emall') or exit('Access Invalid!');?>
<script type="text/javascript">
$(document).ready(function(){
    $("#submit").click(function(){
        if($("#final_handle_message").val()=='') {
            alert("<?php echo $lang['final_handle_message_error'];?>");
        }
        else {
            if(confirm("确认修改?")) {
                $("#save_form").submit();
            }
        }
    });
});
</script>
<style type="text/css">
.li_list li{
   padding: 5px;
}
.li_list .line_section_1 {
    padding-right:5px;
}
    .dif{
        color: #0000FF;
    }
</style>
<div class="page">
<div class="fixed-bar">
  <div class="item-title">
    <h3><?php echo $lang['complain_manage_title'];?></h3>
    <ul class="tab-base">
      <?php
		foreach($output['menu'] as $menu) {
		if($menu['menu_type'] == 'text') {
        ?>
      <li><a href="JavaScript:void(0);" class="current"><span><?php echo $menu['menu_name'];?></span></a></li>
      <?php
		}
		 else {
        ?>
      <li><a href="<?php echo $menu['menu_url'];?>" ><span><?php echo $menu['menu_name'];?></span></a></li>
      <?php
		}
		}
        ?>
    </ul>
  </div>
</div>
<div class="fixed-empty"></div>
<table class="table tb-type2  mtw">
  <thead class="thead">
    <tr class="space">
      <th>备注</th>
    </tr>
  </thead>
  <tbody>
  <tr>
      <td>
          <table border="0" width="100%">
              <tr>
                  <td width="60%">
                      <ul class="li_list">
                          <li><span class="line_section_1">投诉人:</span><span class="line_section_1"><?php echo $output['complain_info']['accuser_name'];?></span></li>
                          <li><span class="line_section_1">联系方式:</span><span class="line_section_1"><?php echo $output['complain_info']['accuser_phone'];?></span></li>
                          <li><span class="line_section_1">投诉主题:</span><span class="line_section_1">无</span></li>
                          <li><span class="line_section_1">被投诉商户:</span><span class="line_section_1 dif"><?php echo $output['complain_info']['league_store_name'];?></span></li>
                          <li><span class="line_section_1">投诉订单:</span><span class="line_section_1 dif"><a href="<?php echo urlAdmin('order','show_order',array('order_id'=>$output['complain_info']['order_id']));?>" target="_blank"><?php echo $output['complain_info']['order_sn'];?></a></span></li>
                          <li><span class="line_section_1">下单时间:</span><span class="line_section_1"><?php echo date('Y-m-d H:i:s',$output['complain_info']['order_add_time']);?></span></li>
                          <li><span class="line_section_1">投诉时间:</span><span class="line_section_1"><?php echo date('Y-m-d H:i:s',$output['complain_info']['complain_datetime']);?></span></li>
                          <li><span class="line_section_1">投诉内容:</span><span class="line_section_1"><?php echo $output['complain_info']['complain_content'];?></span></li>
                      </ul>
                  </td>
                  <td width="28%">
                      <form action="<?php echo urlAdmin('complain','leagueSave');?>" method="post" id="save_form">
                          <ul class="li_list">
                              <li>
                          <div><label>处理方式</label>
                              <select name="handle_type">
                                  <?php foreach ($output['league_num']['deal_state'] as $index => $item):

                                  ?>
                                  <option value="<?php echo $item;?>" <?php if($item==$output['complain_info']['handle_type']){echo "selected";}?>><?php echo $output['league_text']['deal_state'][$index];?></option>
                                  <?php endforeach;?>
                              </select>
                          </div></li><li>
                          <div>
                              <textarea name="final_handle_message" id="final_handle_message" cols="30" rows="10" placeholder="请对本次投诉处理进行备注" class="tarea"><?php echo $output['complain_info']['final_handle_message'];?></textarea>
                          </div></li>
                          </ul>
                          <div> <a id="submit" class="btn" href="javascript:void(0)"><span>提交</span></a></div>
                          <input type="hidden" name="id" value="<?php echo intval($_GET['complain_id']);?>">
                      </form>
                  </td>
              </tr>
          </table>
      </td>
  </tr>
    </tbody>
</table>
    <div id="pagemask"></div>

    <div id="dialog" style="display: none;">
        <div class="title">
            <h3>订单详情</h3>
            <span><a href="JavaScript:void(0);" onclick="closeBg();"><?php echo $lang['nc_close'];?></a></span> </div>
        <div class="content" style="overflow: auto;height: 500px">
            <iframe src="<?php echo urlAdmin('order','show_order',array('order_id'=>$output['complain_info']['order_id']));?>" id="main" width="900" frameborder="0" scrolling="no"></iframe>
        </div>
    </div>
