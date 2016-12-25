<?php defined('emall') or exit('Access Invalid!');?>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/jquery.ui.js"></script>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/i18n/zh-CN.js" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="<?php echo RESOURCE_SITE_URL;?>/js/jquery-ui/themes/ui-lightness/jquery.ui.css"/>
<style type="text/css">
.content{width:1000px; margin:5px 25px;}
.card{font-size:14px; font-family:"微软雅黑"; padding-left:20px;}
.card ul li{margin:10px;}
.card ul li span{height:30px; line-height:30px;}
.card ul li.submit{padding:0 123px;}
.card ul li.notice input{border:none; background:#FCF8E3; width:400px; height:10px; line-height:10px;color:red;}
.card ul li span.notice_tit{font-size:12px; color:#888888;}
.money{color:red; font-size:13px; }
</style>

<div class="content">
   <div class="item-title">
	   <h3>充值卡管理</h3>
	   <ul class="tab-base">
		 <li><a href="index.php?act=pd_gift_card&op=index"><span>充值卡管理</span></a></li>
		 <li><a href="JavaScript:void(0);" class="current"><span>生成充值卡</span></a></li>
	   </ul>
	</div>

   <table class="table tb-type2" id="prompt">
     <tbody>
      <tr class="space odd">
        <th colspan="12" class="nobg"><div class="title">
            <h5><?php echo $lang['nc_prompts'];?></h5>
            <span class="arrow"></span></div></th>
      </tr>
      <tr>
        <td>
		  <ul>
		    <li>1、生成的充值卡数量、面值和有效期为必填选项</li>
		    <li>2、请控制好每次批量生成的充值卡数量，以免增加服务器的负担</li>
		  </ul>
		</td>
      </tr>
    </tbody>
   </table>

	<form method="post" action="index.php?act=pd_gift_card&op=create_gift_card">
		<div class="card">
		  <ul>
			 <li><span>生成充值卡的面值：</span><input type="text" name="value" id="value" size="10" onkeyup="value=value.replace(/[^\d]/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))" onblur="check_value();"/> 元<span class="notice_tit"> (面值必须为整数)</span></li>
			 <li><span>生成充值卡的数量：</span><input type="text" name="number" id="number" size="10" onkeyup="value=value.replace(/[^\d]/g,'');" onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\d]/g,''))" onblur="check_num();"/> 个<span class="notice_tit"> (生成的数量不宜过多)</span></li>
			 <li><span>&nbsp;&nbsp;&nbsp;充值卡批次标识：</span><input type="text" name="batch" size="10"/><span class="notice_tit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(标记充值卡的批次，不超过10个字符，例如：A201409)</span></li>
			 <li><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;使用截止日期：</span><input class="date" type="text" id="valid_date" name="valid_date" size="10">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="notice_tit">(使用截止日期必须填)</span></li>
			 <li class="submit"><input type="submit" value="批量生成充值卡" /></li>
		  </ul>
		</div>
	</form>
</div>

<script type="text/javascript">
function check_value(){
	var value = document.getElementById('value').value;
	if(value=='' || value==0){
		alert("您输入的充值卡面值有误");
		document.getElementById('value').focus();
		return false;
	}
}
function check_num(){
	var number = document.getElementById('number').value;
	if(number=='' || number==0){
		alert("您输入的充值卡数量有误");
		document.getElementById('number').focus();
		return false;
	}
}
$(function(){
    $('#valid_date').datepicker({dateFormat: 'yy-mm-dd'});});
</script>