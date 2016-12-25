<?php
/**
 * 支付回调
 *
 */

//use Tpl;

defined('emall') or exit('Access Invalid!');

class paymentControl extends mobileHomeControl{

    private $payment_code;

	public function __construct() {
		parent::__construct();

        $this->payment_code = isset($_GET['payment_code']) ? $_GET['payment_code'] : 'alipay';
	}

	public function returnopenidOp(){
        $payment_api = $this->_get_payment_api();
        if($this->payment_code != 'wxpay'){
            output_error('支付参数异常');
            die;
        }

        $payment_api->getopenid();

    }

    /**
     * 支付回调
     */
    public function returnOp() {
        unset($_GET['act']);
        unset($_GET['op']);
        unset($_GET['payment_code']);
        $payment_api = $this->_get_payment_api();

        $payment_config = $this->_get_payment_config();
        $callback_info = $payment_api->getReturnInfo($payment_config);
        /* $callback_info = array(
            'out_trade_no' => $_REQUEST['out_trade_no'],
            'trade_no' => $_REQUEST['trade_no'],
        ); */
        if($callback_info) {
            //验证成功
            //$result = $this->_update_order($callback_info['out_trade_no'], $callback_info['trade_no']);
            $out_trade_no = $callback_info['out_trade_no'];
            $trade_no = $callback_info['trade_no'];
            $model_order = Model('order');
            $logic_payment = Logic('payment');
            $result = array();
            $order_type =substr($callback_info['out_trade_no'],0,8);
            if ($order_type == 'pd_order')
            {
                $result = $logic_payment->getPdOrderInfo($out_trade_no);
                if ($result['data']['pdr_payment_state'] == 1) {
                   $result = array('state'=>true);
                }    
                else 
                {
                    $order_pay_info = $result['data'];
                    $result = $logic_payment->updatePdOrder($out_trade_no,$trade_no,$payment_info,$order_pay_info);
                }
                
            }
            else 
            {
                $result = $logic_payment->getRealOrderInfo($out_trade_no);
                if ($result['state'] == false)
                {
                    $result = $logic_payment->getVrOrderInfo($out_trade_no);
                    if ($result['data']['order_state'] != ORDER_STATE_NEW) {
                        $result = array('state'=>true);
                    }
                    else 
                    {
                        $result = $logic_payment->updateVrOrder($out_trade_no, $this->payment_code, $result['data'], $trade_no);
                    }
                    
                }
                else 
                {
                    if (intval($result['data']['api_pay_state'])) {
                        $result = array('state'=>true);
                    }
                    else 
                    {
                        $order_list = $result['data']['order_list'];
                        $result = $logic_payment->updateRealOrder($out_trade_no, $this->payment_code, $order_list, $trade_no);
                    }
                    
                }
                
            }
            
            if($result['state']) {
                Tpl::output('result', 'success');
                Tpl::output('message', '支付成功');
            } else {
                Tpl::output('result', 'fail');
                Tpl::output('message', '支付失败');
			}
        } else {
			//验证失败
            Tpl::output('result', 'fail');
            Tpl::output('message', '支付失败');
		}

        Tpl::showpage('payment_message');
    }

    /**
     * 支付提醒
     */
    public function notifyOp() {
        // 恢复框架编码的post值
        $_POST['notify_data'] = html_entity_decode($_POST['notify_data']);

        $payment_api = $this->_get_payment_api();

        $payment_config = $this->_get_payment_config();

        $callback_info = $payment_api->getNotifyInfo($payment_config);

        if($callback_info) {
            //验证成功
            $result = $this->_update_order($callback_info['out_trade_no'], $callback_info['trade_no']);
            if($result['state']) {
                if($this->payment_code == 'wxpay'){
                    echo $callback_info['returnXml'];
                    die;
                }else{
                    echo 'success';die;
                }
            }
		}

        //验证失败
        if($this->payment_code == 'wxpay'){
            echo '<xml><return_code><!--[CDATA[FAIL]]--></return_code></xml>';
            die;
        }else{
            echo "fail";die;
        }
    }
    
    /**
     * app端支付提醒(对应商城，按照支付编号回调)
     */
    public function alipaynotifyappOp() {
        // 恢复框架编码的post值
        $_POST['notify_data'] = html_entity_decode($_POST['notify_data']);
           
        $payment_config = $this->_get_payment_config();
        require_once(BASE_PATH.DS.'api'.DS.'payment'.DS.'alipay'.DS."aliapp.php");
        $aliapp = new aliapp();
        $callback_info = $aliapp->notify($payment_config);
        /*  $callback_info = array(
         'out_trade_no' => $_REQUEST['out_trade_no'],
         'trade_no' => $_REQUEST['trade_no'],
        );  */
    
        if($callback_info) {
            $trade_no       = $callback_info['trade_no'];
            $out_trade_no   =$callback_info['out_trade_no'];
            $logic_payment = Logic('payment');
            //验证成功
            $order_type =substr($out_trade_no,0,8);
            if ($order_type == 'pd_order')
            {
                $order_type='p';
                 
            }
            else
            {
                $rs = $logic_payment->getRealOrderInfo($out_trade_no);
                if ($rs['state'] == true)
                {
                    $order_type='r';
                }
                else 
                {
                    $order_type='v';
                }
            }
            $result = $this->_update_order_rewrite($order_type, $out_trade_no , $trade_no);
            if($result['state']) {
                echo 'success';die;
            }
        }
        
        //验证失败
        echo "fail";die;
    }

    /**
     * web端支付提醒
     */
    public function alipaynotifywebOp() {
        // 恢复框架编码的post值
        $_POST['notify_data'] = html_entity_decode($_POST['notify_data']);
    
        $payment_api = $this->_get_payment_api();
    
        $payment_config = $this->_get_payment_config();
    
        $callback_info = $payment_api->getNotifyInfo($payment_config);
        /*  $callback_info = array(
            'out_trade_no' => $_REQUEST['out_trade_no'],
            'trade_no' => $_REQUEST['trade_no'],
        );  */
    
        if($callback_info) {
            
            $out_trade_no = $callback_info['out_trade_no'];
            $trade_no = $callback_info['trade_no'];
            $model_order = Model('order');
            $logic_payment = Logic('payment');
            $result = array();
            $order_type =substr($callback_info['out_trade_no'],0,8);
            if ($order_type == 'pd_order')
            {
                $result = $logic_payment->getPdOrderInfo($out_trade_no);
                if ($result['data']['pdr_payment_state'] == 1) {
                    echo 'success';die;
                }
            
                $order_pay_info = $result['data'];
                $result = $logic_payment->updatePdOrder($out_trade_no,$trade_no,$payment_info,$order_pay_info);
            }
            else
            {
                $result = $logic_payment->getRealOrderInfo($out_trade_no);
                if ($result['state'] == false)
                {
                    $result = $logic_payment->getVrOrderInfo($out_trade_no);
                    if ($result['data']['order_state'] != ORDER_STATE_NEW) {
                        echo 'success';die;
                    }
                    $result = $logic_payment->updateVrOrder($out_trade_no, $this->payment_code, $result['data'], $trade_no);
                }
                else
                {
                    if (intval($result['data']['api_pay_state'])) {
                        echo 'success';die;
                    }
                    $order_list = $result['data']['order_list'];
                    $result = $logic_payment->updateRealOrder($out_trade_no, $this->payment_code, $order_list, $trade_no);
                }
            
            }
            if($result['state']) {
                echo 'success';die;
            }
        }
    
        //验证失败
        echo "fail";die;
    }
    
    
    /**
     * 支付提醒
     */
    public function alipaynotifyOp() {
        // 恢复框架编码的post值
        $_POST['notify_data'] = html_entity_decode($_POST['notify_data']);
    
       // $payment_api = $this->_get_payment_api();
    
        $payment_config = $this->_get_payment_config();
        require_once(BASE_PATH.DS.'api'.DS.'payment'.DS.'alipay'.DS."aliapp.php");
        $aliapp = new aliapp();
        $callback_info = $aliapp->notify($payment_config);
       // require_once(BASE_PATH.DS.'api'.DS.'payment'.DS.'aliapp'.DS."lib/alipay_notify.class.php");
       // var_dump($alipay_config);
        
        //$callback_info = $payment_api->getNotifyInfo($payment_config);
       /*  $callback_info = array(
            'out_trade_no' => $_REQUEST['out_trade_no'],
            'trade_no' => $_REQUEST['trade_no'],
        ); */
    
        if($callback_info) {
            $trade_no       = $callback_info['trade_no'];
            $out_trade_no   =$callback_info['out_trade_no'];
            //验证成功
            $order_type =substr($out_trade_no,0,8);
            if ($order_type == 'pd_order')
            {
                $order_type='p';
                 
            }
            else
            {
                $condition['order_sn']=$out_trade_no;
                $res=Model('order')->getOrderInfo($condition, array(), '*', '', '',  true);
                if(!empty($res)){
                    $order_type='r';
                    $out_trade_no=$res['pay_sn'];
                }else{
                    $order_type='v';
                }
            }
            $result = $this->_update_order_rewrite($order_type, $out_trade_no , $trade_no);
            if($result['state']) {
                echo 'success';die;
            }
        }
    
        //验证失败
        echo "fail";die;
    }


    /* public function notify(){
        $notify_data = $_POST['notify_data'];
        //解析notify_data
       
        //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
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
    } */


    /**
     * 通知处理(支付宝异步通知和网银在线自动对账)
     *
     */
   /*  public function order_notify(){
        $result=htmlspecialchars_decode($_POST['notify_data']);
        $xml = simplexml_load_string($result);        
        $arr=(array)$xml;

        $order_type     = substr($arr['out_trade_no'], 0,8);
        $out_trade_no   = $arr['out_trade_no'];
        $trade_no       = $arr['trade_no'];
        //参数判断
        //if(!preg_match('/^\d{18}$/',$out_trade_no)) exit($fail);
        $model_pd       = Model('predeposit');
        $logic_payment  = Logic('payment');

        if ($order_type == 'real_order') {
            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (intval($result['data']['api_pay_state'])) {
                exit($success);
            }
            $order_list = $result['data']['order_list'];

        } elseif ($order_type == 'vr_order'){


            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            if ($result['data']['order_state'] != ORDER_STATE_NEW) {
                exit($success);
            }

        } elseif ($order_type == 'pd_order') {

            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if ($result['data']['pdr_payment_state'] == 1) {
                exit($success);
            }

        } else {
            exit();
        }
        $order_pay_info = $result['data'];

        //取得支付方式
        $result = $logic_payment->getPaymentInfo('alipay');
        if (!$result['state']) {
            exit($fail);
        }
        $payment_info = $result['data'];

        //创建支付接口对象
        $payment_api    = new $payment_info['payment_code']($payment_info,$order_pay_info);

        // //对进入的参数进行远程数据判断
        // $verify = $payment_api->notify_verify();
        // if (!$verify) {
        //     exit($fail);
        // }

        //购买商品
        if ($order_type == 'real_order') {
            $result = $logic_payment->updateRealOrder($out_trade_no, $payment_info['payment_code'], $order_list, $trade_no);
        } elseif($order_type == 'vr_order'){
            $result = $logic_payment->updateVrOrder($out_trade_no, $payment_info['payment_code'], $order_pay_info, $trade_no);
        } elseif ($order_type == 'pd_order') {
            $result = $logic_payment->updatePdOrder($out_trade_no,$trade_no,$payment_info,$order_pay_info);
        }

        exit($result['state'] ? $success : $fail);
    } */



   /*  public function app_order_notify(){
        $arr=$_POST;
        $order_type     = substr($arr['out_trade_no'], 0,8);
        $out_trade_no   = $arr['out_trade_no'];
        $trade_no       = $arr['trade_no'];
        //参数判断
        //if(!preg_match('/^\d{18}$/',$out_trade_no)) exit($fail);
        $model_pd       = Model('predeposit');
        $logic_payment  = Logic('payment');

        if ($order_type == 'real_order') {
            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (intval($result['data']['api_pay_state'])) {
                exit($success);
            }
            $order_list = $result['data']['order_list'];

        } elseif ($order_type == 'vr_order'){


            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            if ($result['data']['order_state'] != ORDER_STATE_NEW) {
                exit($success);
            }

        } elseif ($order_type == 'pd_order') {

            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if ($result['data']['pdr_payment_state'] == 1) {
                exit($success);
            }

        } else {
            exit();
        }
        $order_pay_info = $result['data'];

        //取得支付方式
        $result = $logic_payment->getPaymentInfo('alipay');
        if (!$result['state']) {
            exit($fail);
        }
        $payment_info = $result['data'];

        //创建支付接口对象
        $payment_api    = new $payment_info['payment_code']($payment_info,$order_pay_info);

        // //对进入的参数进行远程数据判断
        // $verify = $payment_api->notify_verify();
        // if (!$verify) {
        //     exit($fail);
        // }

        //购买商品
        if ($order_type == 'real_order') {
            $result = $logic_payment->updateRealOrder($out_trade_no, $payment_info['payment_code'], $order_list, $trade_no);
        } elseif($order_type == 'vr_order'){
            $result = $logic_payment->updateVrOrder($out_trade_no, $payment_info['payment_code'], $order_pay_info, $trade_no);
        } elseif ($order_type == 'pd_order') {
            $result = $logic_payment->updatePdOrder($out_trade_no,$trade_no,$payment_info,$order_pay_info);
        }

        exit($result['state'] ? $success : $fail);
    } */




//错误作废
   /*  public function order_return(){
      
        $order_type =substr($_GET['out_trade_no'],0,8);
        if ($order_type == 'real_order') {
            $act = 'member_order';
        } elseif($order_type == 'vr_order') {
            $act = 'member_vr_order';
        } elseif($order_type == 'pd_order') {
            $act = 'predeposit';
        } else {
            exit('');
        }

        $out_trade_no = $_GET['out_trade_no'];
        $trade_no = $_GET['trade_no'];

        $logic_payment = Logic('payment');
        if ($order_type == 'real_order') {

            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if(!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['api_pay_state']) {
                $payment_state = 'success';
            }
            $order_list = $result['data']['order_list'];

        }elseif ($order_type == 'vr_order') {

            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            if(!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['order_state'] != ORDER_STATE_NEW) {
                $payment_state = 'success';
            }

        } elseif ($order_type == 'pd_order') {
            $result = $logic_payment->getPdOrderInfo($out_trade_no);

             
            if(!$result['state']) {
                showMessage($result['msg'], $url, 'html', 'error');
            }
            if ($result['data']['pdr_payment_state'] == 1) {
                $payment_state = 'success';
            }
        }
        $member_id=$result['data']['pdr_member_id'];  //用户id
        $conut    =$result['data']['pdr_amount'];  //充值数量
        $order_pay_info = $result['data'];
        $api_pay_amount = $result['data']['api_pay_amount'];

        if ($payment_state != 'success') {
            //取得支付方式
            
            $result = $logic_payment->getPaymentInfo('alipay');

            if (!$result['state']) {
                showMessage($result['msg'],$url,'html','error');
            }
            $payment_info = $result['data'];


            //更改订单支付状态
            if ($order_type == 'real_order') {
                $result = $logic_payment->updateRealOrder($out_trade_no, $payment_info['payment_code'], $order_list, $trade_no);
            } else if($order_type == 'vr_order') {
                $result = $logic_payment->updateVrOrder($out_trade_no, $payment_info['payment_code'], $order_pay_info, $trade_no);
            } else if ($order_type == 'pd_order') {
                $result = $logic_payment->updatePdOrder($out_trade_no, $trade_no, $payment_info, $order_pay_info);
            }
            if (!$result['state']) {
                showMessage('支付状态更新失败',$url,'html','error');
            }
        }

        //支付成功后跳转
        if ($order_type == 'real_order') {
            $pay_ok_url = SHOP_SITE_URL.'/index.php?act=buy&op=pay_ok&pay_sn='.$out_trade_no.'&pay_amount='.ncPriceFormat($api_pay_amount);
        } elseif ($order_type == 'vr_order') {
            $pay_ok_url = SHOP_SITE_URL.'/index.php?act=buy_virtual&op=pay_ok&order_sn='.$out_trade_no.'&order_id='.$order_pay_info['order_id'].'&order_amount='.ncPriceFormat($api_pay_amount);
        } elseif ($order_type == 'pd_order') {
            //$pay_ok_url = SHOP_SITE_URL.'/index.php?act=predeposit';
            //Model()->table('member')->where(array('member_id'=>$member_id))->setInc('available_predeposit',$count);

            echo '<script type="text/javascript">Alipay.onRechargeResult(\'success\');</script>';
        }
        if ($payment_info['payment_code'] == 'tenpay') {
            showMessage('',$pay_ok_url,'tenpay');
        } else {
            redirect($pay_ok_url);
        }
    } */




    /**
     * 获取支付接口实例
     */
    private function _get_payment_api() {
        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.$this->payment_code.DS.$this->payment_code.'.php';

        if(is_file($inc_file)) {
            require($inc_file);
        }

        $payment_api = new $this->payment_code();

        return $payment_api;
    }

    /**
     * 获取支付接口信息
     */
    private function _get_payment_config() {
        $model_mb_payment = Model('mb_payment');

        //读取接口配置信息
        $condition = array();
        $condition['payment_code'] = $this->payment_code;
        $payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        
        return $payment_info['payment_config'];
    }
    /*
        支付宝支付 订单的回调参数
     */

    /*  public function app_update_orderOp(){
        $trade_no       =$_REQUEST['trade_no'];
        $out_trade_no   =$_REQUEST['out_trade_no'];
        $order_type =substr($out_trade_no,0,8);
        if ($order_type == 'pd_order')
        {
            $order_type='p';
           
        }
        else 
        {
            $condition['order_sn']=$out_trade_no;
            $res=Model('order')->getOrderInfo($condition);
            if(!empty($res)){
                $order_type='r';
                $out_trade_no=$res['pay_sn'];
            }else{
                $order_type='v';
            }
        }
        $result = $this->_update_order_rewrite($order_type, $out_trade_no , $trade_no);
        if($result['state']) {
            echo 'success';die;
        }
    } */



    /**
     * 更新订单状态
     */
    private function _update_order($out_trade_no, $trade_no) {      
       // $sn=$out_trade_no;
        
        //if(substr($sn, 0,8)== 'pd_order'){
        if(substr($out_trade_no, 0,8)== 'pd_order'){
            $order_type='p';
        }else{
           $tmp = explode('|', $out_trade_no);
            if (count($tmp) != 2)
                return array('state'=>false);
            $order_type = $tmp[0];
            $out_trade_no = $tmp[1];
        }
        
        return $this->_update_order_rewrite($order_type, $out_trade_no, $trade_no);

    }
    
    private function _update_order_rewrite($order_type, $out_trade_no, $trade_no)
    {
        $model_order = Model('order');
        $logic_payment = Logic('payment');
        $result = array();
        if ($order_type == 'r') {
            $result = $logic_payment->getRealOrderInfo($out_trade_no);
            if (intval($result['data']['api_pay_state'])) {
                return array('state'=>true);
            }
            $order_list = $result['data']['order_list'];
            $result = $logic_payment->updateRealOrder($out_trade_no, $this->payment_code, $order_list, $trade_no);

        } elseif ($order_type == 'v') {
            $result = $logic_payment->getVrOrderInfo($out_trade_no);
            if ($result['data']['order_state'] != ORDER_STATE_NEW) {
                return array('state'=>true);
            }
            $result = $logic_payment->updateVrOrder($out_trade_no, $this->payment_code, $result['data'], $trade_no);
        }elseif ($order_type =='p') {
        
            $result = $logic_payment->getPdOrderInfo($out_trade_no);
            if ($result['data']['pdr_payment_state'] == 1) {
                return array('state'=>true);
            }            
            //xuping  获取支付方式
            $condition = array();
            $condition['payment_code'] = $this->payment_code;
            $payment_info = Model('mb_payment')->getMbPaymentOpenInfo($condition);  
                   
            $order_pay_info = $result['data'];
            $result = $logic_payment->updatePdOrder($out_trade_no,$trade_no,$payment_info,$order_pay_info);
        }
        return $result;
    }
    
 /**
  * 测试用支付功能，不可以上传到线上
  */   
    public function testNotifyOp() {
        // 恢复框架编码的post值
        $_POST['notify_data'] = html_entity_decode($_POST['notify_data']);
        $mobile_site_url = C('mobile_site_url');
        if ($mobile_site_url != 'http://devshop.aigegou.com/mobile'){
            exit;
        }
    
        /* $payment_api = $this->_get_payment_api();
    
        $payment_config = $this->_get_payment_config();
    
        $callback_info = $payment_api->getNotifyInfo($payment_config); */
        $callback_info = array(
            'out_trade_no' => $_REQUEST['out_trade_no'],
            'trade_no' => $_REQUEST['trade_no'],
        );
    
        if($callback_info) {
            //验证成功
            $result = $this->_update_order($callback_info['out_trade_no'], $callback_info['trade_no']);
            if($result['state']) {
                if($this->payment_code == 'wxpay'){
                    echo $callback_info['returnXml'];
                    die;
                }else{
                    echo 'success';die;
                }
            }
        }
    
        //验证失败
        if($this->payment_code == 'wxpay'){
            echo '<xml><return_code><!--[CDATA[FAIL]]--></return_code></xml>';
            die;
        }else{
            echo "fail";die;
        }
    }

}