<?php defined('emall') or exit('Access Invalid!');?>

<div class="page">
  <div class="fixed-bar">
    <div class="item-title">
      <h3><?php echo $lang['refund_manage'];?></h3>
      <ul class="tab-base">
        <li><a href="index.php?act=adt_refund&op=refund_manage"><span><?php echo '待处理';?></span></a></li>
        <li><a href="index.php?act=adt_refund&op=refund_all"><span><?php echo '所有记录';?></span></a></li>
        <li><a href="JavaScript:void(0);" class="current"><span><?php echo '审核';?></span></a></li>
      </ul>
    </div>
  </div>
  <div class="fixed-empty"></div>
  <form id="post_form" method="post" action="index.php?act=adt_refund&op=edit&order_id=<?php echo $output['refund']['order_id']; ?>">
    <input type="hidden" name="form_submit" value="ok" />
    <table class="table tb-type2">
      <tbody>
        <tr>
          <td colspan="2" class="required"><?php echo $lang['refund_order_refund'].$lang['nc_colon'];?></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo ncPriceFormat($output['refund']['refund_amount']); ?></td>
          <td class="vatop tips"></td>
        </tr>

        <tr>
          <td colspan="2" class="required"><?php echo '支付方式'.$lang['nc_colon'];?></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo $output['refund']['payment_code']; ?></td>
          <td class="vatop tips"></td>
        </tr>

        <tr>
          <td colspan="2" class="required"><?php echo '申请时间'.$lang['nc_colon'];?></td>
        </tr>
        <tr class="noborder">
          <td class="vatop rowform"><?php echo date('Y-m-d H:i:s',$output['refund']['add_time']); ?></td>
          <td class="vatop tips"></td>
        </tr>

      </tbody>
      <tfoot>
        <tr class="tfoot">
          <td colspan="15" ><a href="JavaScript:void(0);" class="btn" id="submitBtn"><span><?php echo $lang['nc_submit'];?></span></a></td>
        </tr>
      </tfoot>
    </table>
  </form>
</div>
<script type="text/javascript" src="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/custom.min.js" charset="utf-8"></script>
<link href="<?php echo RESOURCE_SITE_URL;?>/js/jquery.nyroModal/styles/nyroModal.css" rel="stylesheet" type="text/css" id="cssfile2" />
<script type="text/javascript">
$(function(){
    $('.nyroModal').nyroModal();
	$("#submitBtn").click(function(){
        var payment = "<?php echo $output['refund']['payment_code']; ?>",alipay_url;
        alipay_url = "<?php echo urlAdmin('refund','alipayRefund',array('refund_id'=>$output['refund']['id'])); ?>";
        if($("#post_form").valid()){
            if(payment=='alipay'){
                $.post(alipay_url,{},function (data) {
                    if(!data.state){
                        alert(data.msg);
                    }else{
                        window.open(alipay_url);
                    }

                },'json');
            }else{
                $("#post_form").submit();
            }
        }
	});
    $('#post_form').validate({
		errorPlacement: function(error, element){
			error.appendTo(element.parent().parent().prev().find('td:first'));
        },
        rules : {
            admin_message : {
                required   : true
            }
        },
        messages : {
            admin_message  : {
                required   : '<?php echo $lang['refund_message_null'];?>'
            }
        }
    });
});
</script>