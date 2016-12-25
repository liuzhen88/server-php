<?php
/* * 
 * 功能：支付宝页面跳转同步通知页面
 */
$_GET['act'] = 'payment';
$_GET['op']	= 'return';
$_GET['payment_code']	= 'alipay';
require_once(dirname(__FILE__).'/../../../index.php');
require_once(dirname(__FILE__).'/../../../control/payment.php');
//$result=new paymentControl();
//$result->order_return();

?>