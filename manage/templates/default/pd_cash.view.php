<?php defined('emall') or exit('Access Invalid!');?>
<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['nc_member_predepositmanage'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=predeposit&op=predeposit"><span><?php echo $lang['admin_predeposit_rechargelist']?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo $lang['admin_predeposit_cashmanage']; ?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
    <table class="table tb-type2 nobdb">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_sn'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['pdc_sn']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_membername'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['pdc_member_name']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_price'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['pdc_amount']; ?>&nbsp;<?php echo $lang['currency_zh'];?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_shoukuanbank']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['pdc_bank_name']; ?></td>
          <td class="vatop tips"></td>
        </tr>
		<tr>
          <td colspan="2" class="required"><label><?php echo $lang['open_branch']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['open_branch']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_shoukuanaccount'];?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['pdc_bank_no']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <tr>
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_shoukuanname']?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['info']['pdc_bank_user']; ?></td>
          <td class="vatop tips"></td>
        </tr>
        <?php if (intval($output['info']['pdc_payment_time'])) {?>
        <tr>
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_paytime']; ?>:</label></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo @date('Y-m-d',$output['info']['pdc_payment_time']); ?> 
          ( <?php echo $lang['admin_predeposit_adminname'];?>: <?php echo $output['info']['pdc_payment_admin'];?> ) </td>
          <td class="vatop tips"></td>
        </tr>
        <?php } ?>
        <?php if(intval($output['info']['pdc_payment_state'])==0){ ?>
          <tr>
            <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_state']?>:</label></td>
          </tr>
          <tr class="noborder">
            <td class="vatop rowform">
              <select name="cash_state" id="cash_state">
                <option value="2" selected="selected"><?php echo $lang['admin_predeposit_cash_state2']?></option>
                <option value="3"><?php echo $lang['admin_predeposit_cash_state3']?></option>
              </select>
            </td>
            <td class="vatop tips"></td>
          </tr>
        <?php  } ?>
        <?php if(intval($output['info']['pdc_payment_state'])==2){ ?>
          <tr>
            <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_state']?>:</label></td>
          </tr>
          <tr class="noborder">
            <td class="vatop rowform">
              <select name="cash_state" id="cash_state">
                <option value="1" selected="selected"><?php echo $lang['admin_predeposit_cashpaysuccess']?></option>
                <option value="3"><?php echo $lang['admin_predeposit_cash_state3']?></option>
              </select>
            </td>
            <td class="vatop tips"></td>
          </tr>
        <?php  } ?>
        <tr id="message1" style="display: none">
          <td colspan="2" class="required"><label><?php echo $lang['admin_predeposit_cash_message']; ?>:</label></td>
        </tr>
        <tr class="noborder" id="message2" style="display: none">
          <td class="vatop rowform"><input type="text" name="pdc_message" id="pdc_message"/></td>
          <td class="vatop tips"></td>
        </tr>
      </tbody>
      <?php if (intval($output['info']['pdc_payment_state'])!=1&&intval($output['info']['pdc_payment_state'])!=3) {?>
        <tfoot id="submit-holder">
        <tr class="tfoot">
        <td colspan="2">
        <a class="btn"><span><?php echo $lang['admin_predeposit_payed'];?></span></a>
        </td>
        </tr>
        </tfoot>
     <?php } ?>
    </table>
</div>
<script type="text/javascript">
  $("#cash_state").change(function(){
    var state = $("#cash_state").val();
    if(state==3) {
      $("#message1").css('display','block');
      $("#message2").css('display','block');
    }
    else {
      $("#message1").css('display','none');
      $("#message2").css('display','none');
    }
  });
$(".btn").click(function(){
  var state = $("#cash_state").val();
  var pdc_message = $("#pdc_message").val();
  if (confirm('<?php echo $lang['admin_predeposit_cash_confirm_change'];?>')){window.location.href='index.php?act=predeposit&op=pd_cash_pay&id=<?php echo $output['info']['pdc_id']; ?>&cash_state='+state+'&pdc_message='+pdc_message;}else{}
});
</script>