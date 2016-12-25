<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。


 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

require_once("lib/alipay_notify.class.php");

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if ($verify_result) {//验证成功
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //请在这里加上商户的业务逻辑程序代


    //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

    //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

    //批次号

    $batch_no = $_POST['batch_no'];

    //批量退款数据中转账成功的笔数

    $success_num = $_POST['success_num'];

    //批量退款数据中的详细信息
    $result_details = $_POST['result_details'];

    //判断是否在商户网站中已经做过了这次通知返回的处理
    //如果没有做过处理，那么执行商户的业务程序
    //如果有做过处理，那么不执行商户的业务程序
    $order_list = explode('#', $result_details);
    /** @var pay_refundModel $pay_refund_model */
    $pay_refund_model = Model('pay_refund');
    foreach ($order_list as $index => $order) {
        list($api_trade_no, $refund_amount, $refund_state) = explode('#', $order);

        $order_info = Model('refund_return')->getOrderByTrade($api_trade_no);
        $condition = array(
            'refund_state' => 0,
            'order_id'     => $order_info['order_id'],
        );
        $pay_return = Model('pay_refund')->getPayRefund($condition);


        try {

            if ($refund_state != 'SUCCESS' || empty($pay_return)) {
                throw new Exception('状态返回失败');
            }
            elseif ($pay_return['refund_amount'] != $refund_amount) {
                throw new Exception('金额不对');

            }

            $order_info['pay_notify'] = 1;
            $result = Logic('order')->adt_changeOrderStateCancel($order_info, 'system', '', '', false, true);

            $update = array();
            $update['refund_num'] = array('exp', 'refund_num+1');
            //更改退款状态
            if ($result['state']) {
                $update['refund_state'] = 1;
                $update['success_time'] = time();

                $pay_refund_model->editPayRefundReturn($condition, $update);

            } else {
                $pay_refund_model->editPayRefundReturn($condition, $update);
                throw new Exception('退款修改状态失败');
            }
        } catch (Exception $e) {
            logResult($e->getMessage());
            exit;
        }


    }
    echo "success";        //请不要修改或删除

    //调试用，写文本函数记录程序运行情况是否正常
    logResult("success");
} else {
    //验证失败
    echo "fail";
    logResult("验证失败");
}
logResult("==>" . var_export($_POST, true));

?>