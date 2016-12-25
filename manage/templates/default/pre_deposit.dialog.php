<?php defined('emall') or exit('Access Invalid!');?>
<style type="text/css">
h3 { margin-top:0;
}
</style>
<form method="post" action="<?php echo urlAdmin('pre_deposit','charge_save') ?>">
    <dl>
        <dd style="border-top: dotted 1px #E7E7E7; padding:10px 30px; ">店铺名称：<?php echo $_GET['stroe_name']; ?></dd>
          <dt style="padding:10px 30px;">
            预充值金额：<input type="text" name="money" style="width:100px;" >
              <input type="hidden" name="member_id" value="<?php echo $_GET['member_id']; ?>">
          </dt>
        <dt style="padding:10px 100px;"><input type="submit" value="确认"></dt>
    </dl>
</form>