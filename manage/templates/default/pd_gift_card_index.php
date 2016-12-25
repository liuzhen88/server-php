<?php defined('emall') or exit('Access Invalid!');?>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"/>
<style type="text/css">
.content{width:95%; min-width:1000px; margin:5px 20px;}
#list_form{margin-left:25px;}
.card_count{font-size:14px; padding-top:8px; float:right; color:red;}
</style>

<div class="content">
 <div class="item-title">
   <h3>充值卡管理</h3>
   <ul class="tab-base">
     <li><a href="JavaScript:void(0);" class="current"><span>充值卡管理</span></a></li>
     <li><a href="index.php?act=pd_gift_card&op=add_card"><span>生成充值卡</span></a></li>
   </ul>
 </div>

 <form method="get" name="formSearch">
    <input type="hidden" name="act" value="pd_gift_card">
    <input type="hidden" name="op" value="index">
    <table class="tb-type1 noborder search">
      <tbody>
        <tr>
		  <td>&nbsp;</td>
		  <th>卡号</th>
		  <td class="w100"><input type="text" name="card_id" size="15" value="<?php echo $_GET['card_id']; ?>" /></td>
		  <th>面值</th>
		  <td class="w60"><input type="text" name="card_value" size="5" value="<?php echo $_GET['card_value']; ?>"/></td>
		  <th>是否使用</th>
		  <td class="w60">
			<select name="is_use">
			   <option value="0">请选择</option>
			   <option>未使用</option>
			   <option value="1">已使用</option>
			</select>
		  </td>
		  <th>使用者</th>
		  <td class="w120"><input type="text" name="use_name" size="15"  value="<?php echo $_GET['use_name']; ?>"/></td>
		  <th>有效期至</th>
		  <td class="w80"><input class="date" type="text" id="valid_date" name="valid_date" size="8"  value="<?php echo $_GET['valid_date']; ?>"></td>
		  <th>生产批次</th>
		  <td class="w120"><input type="text" name="batch" size="15" value="<?php echo $_GET['batch']; ?>"/></td>
		  <td class="w70 tc"><label class="submit-border"><input type="submit" class="submit" value="搜索"></label></td>
        </tr>
      </tbody>
    </table>
  </form>

  <table class="table tb-type2" id="prompt">
    <tbody>
      <tr class="space odd">
        <th colspan="12" class="nobg"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td><ul><li>网站会员可以在“我的商城->设置->预存款充值->充值卡充值”中输入卡号和密钥即可完成预存款充值。</li></ul></td>
      </tr>
    </tbody>
  </table>

  <form id="list_form" method="post">
    <input type="hidden" id="object_id" name="object_id"  />
    <table class="table tb-type2">
      <thead>
          <tr class="thead">
          	  <th style="width:10%;">卡号</th>
			  <th style="width:10%;">密钥</th>
			  <th style="width:10%;">面值</th>
			  <th style="width:13%;">生成时间</th>
			  <th style="width:10%;">是否使用</th>
			  <th style="width:12%;">使用者</th>
			  <th style="width:12%;">使用时间</th>
			  <th style="width:13%;">有效期</th>
			  <th style="width:10%;">生产批次</th>
          </tr>
      </thead>
      <tbody id="treet1">
	  <?php if(!empty($output['card_list']) && is_array($output['card_list'])){ ?>
       <?php foreach($output['card_list'] as $k=>$card){?>
        <tr class="bd-line">
		  <td><?php echo $card['card_id']; ?></td>
		  <td><?php echo $card['card_password']; ?></td>
		  <td><?php echo $card['card_value']; ?> 元</td>
		  <td><?php echo date('Y-m-d',$card['add_time']); ?></td>
		  <td><?php if($card['is_use']==0){echo "未使用";}else{echo "已使用";} ?></td>
		  <td><?php echo $card['member_name']; ?></td>
		  <td><?php if(!empty($card['use_time'])){echo date('Y-m-d',$card['use_time']);}else{} ?></td>
		  <td><?php echo date('Y-m-d H:i:s',$card['valid_date']); ?></td>
		  <td><?php echo $card['batch'];; ?></td>
		</tr>
	   <?php } ?>
      <?php }else{ ?>
	    <tr class="no_data">
          <td colspan="16"><?php echo $lang['nc_no_record'];?></td>
        </tr>
      <?php } ?>
      </tbody>
     
      <tfoot>
        <tr class="tfoot">
          <td colspan="16">
            <div class="pagination"><?php echo $output['show_page'];?></div>
			<div class="card_count"><?php echo '共计'.$output['card_count'].'张充值卡'; ?><?php echo '，'.$output['card_batch'].'个批次'; ?></div>
          </td>
        </tr>
      </tfoot>

    </table>
  </form>
</div>
<script language="javascript">
$(function(){
	$('#valid_date').datepicker({dateFormat: 'yy-mm-dd'});
});
</script>