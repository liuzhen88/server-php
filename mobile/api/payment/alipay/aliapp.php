<?php
/**
 * 支付接口
 */
defined('emall') or exit('Access Invalid!');


class aliapp {
    public function notify($parm_config)
    {
        $alipay_config = array();
        require_once("alipay_app.config.php");
        require_once("lib/alipay_notify_app.class.php");
        
        $alipay_config['partner'] = $parm_config['alipay_partner'];
       // file_get_contents($alipay_config['ali_public_key_path']);
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //商户订单号
            $out_trade_no = $_REQUEST['out_trade_no'];
            $trade_no = $_REQUEST['trade_no'];
            //支付宝交易号
            $trade_no = $_REQUEST['trade_no'];
            //交易状态
            $trade_status = $_REQUEST['trade_status'];
            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                return array(
                    //商户订单号
                    'out_trade_no' => $out_trade_no,
                    //支付宝交易号
                    'trade_no' => $trade_no,
                );
            }
            else 
            {
                return array();
            }
            //请在这里加上商户的业务逻辑程序代
            $notify_data = $_POST['notify_data'];
            $doc = new DOMDocument();
            $doc->loadXML($notify_data);
            
            if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
                //商户订单号
                $out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
                //支付宝交易号
                $trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
                //交易状态
                $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
            
                if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                    return array(
                        //商户订单号
                        'out_trade_no' => $out_trade_no,
                        //支付宝交易号
                        'trade_no' => $trade_no,
                    );
                }

            }
            else 
            {
                return array();
            }
            //请在这里加上商户的业务逻辑程序代
            
        }
        else 
        {
            return array();
        }
    }
}