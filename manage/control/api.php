<?php
/*
 *
 * API 调用接口
 * */
defined('emall') or exit('Access Invalid!');
class apiControl{

    /**
     * 支付宝退款异步通知接口
     */
    public function refundAlipayNotifyOp()
    {
        $alipay_config = logic('payment')->getAlipayConfig();
        unset($_POST['act'],$_POST['op']);
        include(BASE_PATH."/framework/pay/alipay/refund/notify_url.php");
    }
    


}