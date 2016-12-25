<?php
/**
 * 微信支付通知地址
 *

 */
//$postStr = file_get_contents("php://input");

$_GET['act']	= 'payment';
$_GET['op']		= 'notify';
$_GET['payment_code'] = 'wxpay';

require_once(dirname(__FILE__).'/../../../index.php');
