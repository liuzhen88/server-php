<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 */
$_GET['act'] = 'payment';
$_GET['op']	= 'alipaynotify';
$_GET['payment_code']	= 'alipay';
require_once(dirname(__FILE__).'/../../../index.php');

 // file_put_contents('/www/web/log_error/222.log',json_encode($_REQUEST));
require_once(dirname(__FILE__).'/../../../control/payment.php');
//$result=new paymentControl();
//$result->app_order_notify();

?>
