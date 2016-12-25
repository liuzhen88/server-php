<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <form method="post" name="form1" id="form1" action="<?php echo urlAdmin('goods', 'adt_goods_verify_reject');?>">
    <input type="hidden" name="form_submit" value="ok" />
    <input type="hidden" value="<?php echo $output["id"];?>" name="id">
    <table class="table tb-type2 nobdb">
      <tbody>
        <tr class="noborder">
          <td colspan="2" class="required"><label for="close_reason">拒绝该申请？</label></td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="2"><a href="javascript:void(0);" class="btn" nctype="btn_submit"><span>确认</span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script>
$(function(){
    $('a[nctype="btn_submit"]').click(function(){
        ajaxpost('form1', '', '', 'onerror');
    });
});
</script>