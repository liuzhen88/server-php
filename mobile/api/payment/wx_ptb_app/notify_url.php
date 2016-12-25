<?php
/**
 * 微信支付通知地址  dirname(__FILE__).'/../../../index.php'
 *

 */
$postStr = file_get_contents("php://input");

$_GET['act']	= 'payment';
$_GET['op']		= 'notify';
$_GET['payment_code'] = 'wx_ptb_app';

require_once(dirname(__FILE__).'/../../../index.php');

