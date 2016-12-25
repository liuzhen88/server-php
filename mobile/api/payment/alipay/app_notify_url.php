<?php
/* * 功能：支付宝服务器异步通知页面app端
*/

$_GET['act'] = 'payment';
$_GET['op']	= 'alipaynotifyapp';
$_GET['payment_code'] = 'alipay';

require_once(dirname(__FILE__).'/../../../index.php');
require_once(dirname(__FILE__).'/../../../control/payment.php'); 