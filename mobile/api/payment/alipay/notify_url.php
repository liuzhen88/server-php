<?php
/* * 功能：支付宝服务器异步通知页面
http://120.25.240.53/agg/mobile/api/payment/alipay/call_back_url.php?out_trade_no=14392084833|v&request_token=requestToken&result=success&trade_no=2015081000001000070076433143&sign=5e1a6f71ca1f8ad1e5f2d2661b649e03&sign_type=MD5
 */

$_GET['act'] = 'payment';
$_GET['op']	= 'alipaynotifyweb';
$_GET['payment_code'] = 'alipay';

require_once(dirname(__FILE__).'/../../../index.php');
require_once(dirname(__FILE__).'/../../../control/payment.php'); 

//$result=new paymentControl();
//$result->order_notify();



?>
