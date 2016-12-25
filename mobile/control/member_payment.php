<?php
/**
 * 支付
 *
 */

use Tpl;

defined('emall') or exit('Access Invalid!');

class member_paymentControl extends mobileMemberControl {

    private $payment_code = 'wxpay';

	public function __construct() {
		parent::__construct();
		$this->payment_code = isset($_REQUEST['payment_code']) && trim($_REQUEST['payment_code']) != '' ? trim($_REQUEST['payment_code']) :'alipay';
	}
	
	/**
	 * 实物支付前根据pay_sn查询需支付情况
	 */
	public function pre_payOp(){
	    $pay_sn = $_REQUEST['pay_sn'];

	    $model_order = Model('order');
	    $condition = array();
	    $condition['pay_sn'] = $pay_sn;
	    $condition['buyer_id'] = $this->member_info['member_id'];
	    //$condition['order_state'] = ORDER_STATE_NEW;
	    $order_pay_info = $model_order->getOrderPayInfo($condition);
	    if(empty($order_pay_info)){
	        output_error('该支付单不存在');
	    }
	    $condition = array();
	    $condition['pay_sn'] = $pay_sn;
	    $condition['order_state'] = ORDER_STATE_NEW;
	    $order_list = $model_order->getNormalOrderList($condition, '', '*', 'order_id desc', '', array('order_goods', 'store', 'member'));
	    $order_list_arr = array();
	    //计算本次需要在线支付的订单总金额
	    $pay_amount = 0;
	    if (!empty($order_list)) {
	        foreach ($order_list as $order_info) {
	            if ($order_info['order_state']  == ORDER_STATE_NEW)
	            {
	                // 商品图
	                if (!empty($order_info['extend_order_goods'])) {
	                    foreach ((array)$order_info['extend_order_goods'] as $k => $goods_info) {
	                        $order_info['extend_order_goods'][$k]['goods_image_url'] = cthumb($goods_info['goods_image'], 240, $order_info['store_id']);
	                    }
	                }
	                $order_info['store_info'] = $order_info['extend_store'];
	                unset($order_info['extend_store']);
	                $order_info['shipping_fee'] = null;
	                $order_info['order_state_code'] = null;
	                $order_list_arr[] = $order_info;
	                $pay_amount += ncPriceFormat(floatval($order_info['order_amount']) - floatval($order_info['pd_amount']));
	            }
	    
	        }
	    }
	    output_data(array('pay_amount' => $pay_amount, 'order_list' => $order_list_arr));
	    /////////////////////////////////////////////////////
	   
	}
	

    /**
     * 实物订单支付
     */
    public function payOp() {
	    $pay_sn = $_REQUEST['pay_sn'];
	    $pay_from = isset($_REQUEST['pay_from']) ? trim($_REQUEST['pay_from']) : ''; 

        $model_mb_payment = Model('mb_payment');
        $logic_payment = Logic('payment');

        $condition = array();
        $condition['payment_code'] = $this->payment_code;
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        if(!$mb_payment_info) {
            output_error('系统不支持选定的支付方式');
        }

        //重新计算所需支付金额
        $result = $logic_payment->getRealOrderInfo($pay_sn, $this->member_info['member_id']);
        if(!$result['state']) {
            output_error($result['msg']);
        }
        if ($pay_from == 'alipay_app')
        {
            
            if (isset($result['data']['order_list']))
            {
                $order_list = array();
                foreach ($result['data']['order_list'] as $key =>$row)
                {
                    $order_list[] = $row;
                }
                $result['data']['order_list'] = $order_list;
            }
            output_data($result['data']);
        }
        //第三方API支付
        $this->_api_pay($result['data'], $mb_payment_info);
    }

    /*
    微信支付 用户充值
    xuping
    2015年9月21日11:32:22
     */
    public function payChargeOP(){
        $pay_sn = $_REQUEST['pay_sn'];

        $model_mb_payment = Model('mb_payment');
        $logic_payment = Logic('payment');

        $condition = array();
        $condition['payment_code'] = $this->payment_code;
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        if(!$mb_payment_info) {
            output_error('系统不支持选定的支付方式');
        }
        $res=Model()->table('seller')->where(array('member_id'=>$this->member_info['member_id']))->find();
        if(empty($res)){
            output_error('对不起，用户id 不合法');
            exit;
        }
        //判断是否是店铺管理员
        if ($res['is_admin'] != 1){
            $store_info=Model('store')->getStoreInfoByID($res['store_id']);
            $uid = $store_info['member_id'];
        }

        //重新计算所需支付金额
        $result = $logic_payment->getPdOrderInfo($pay_sn, $uid);

        if(!$result['state']) {
            output_error($result['msg']);
        }
        //第三方API支付
        $this->_api_pay($result['data'], $mb_payment_info);
    }


/*  注释 */
      public function userRechargeOP(){
        $api_pay_amount=(float)$_REQUEST['money'];  
        $uid           =intval($_REQUEST['shop_admin_id']);
        if(empty($api_pay_amount) || empty($uid)){
            output_error('参数为空！');
            exit;
        }
        $res=Model()->table('seller')->where(array('member_id'=>$uid))->find();
        if(empty($res)){
            output_error('对不起，用户id 不合法');
            exit;
        }
        //判断是否是店铺管理员
        if ($res['is_admin'] != 1){
            $store_info=Model('store')->getStoreInfoByID($res['store_id']);
            $uid = $store_info['member_id'];
        }
        $model_pdr = Model('predeposit');
        $order_sn  = 'pd_order'. $model_pdr->makeSnBymember($uid);
        
        $subject='充值到商户中心 订单号为：'.$order_sn;
        $model_mb_payment = Model('mb_payment');
        $condition = array();
        $condition['payment_code'] = $this->payment_code;
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        if(!$mb_payment_info) {
            output_error('系统不支持选定的支付方式');
        }
        $result=array();
        $result['order_sn']     =$order_sn;
        $result['pay_sn']       =$order_sn;
        $result['order_amount'] =$api_pay_amount;
        $result['api_pay_amount']=$api_pay_amount;
        $result['subject']      =$subject;
        $result['order_type']   ='pd_order';

        $model_pdr = Model('predeposit');
        $data = array();
        $data['pdr_sn']             = $order_sn;
        $data['pdr_member_id']      = $uid ;  
        $res=Model('member')->where(array('member_id'=>$uid))->find();
        $data['pdr_member_name']    = $res['member_name'];
        $data['pdr_amount']         = $api_pay_amount;
        $data['pdr_add_time']       = TIMESTAMP;
        $insert = Model()->table('pd_recharge')->insert($data);

        if ($insert) {
             $this->_api_pay($result, $mb_payment_info);
        }
      }


/*
 app 调用方式  调用支付宝
 */

      public function appRechargeOP(){
        $api_pay_amount=(float)$_REQUEST['money'];  
        $uid           =intval($_REQUEST['shop_admin_id']);
        if(empty($api_pay_amount) || empty($uid)){
            output_error('参数为空！');
            exit;
        }

        $res=Model()->table('seller')->where(array('member_id'=>$uid))->find();
        if(empty($res)){
            output_error('对不起，用户id 不合法');
            exit;
        }
        //判断是否是店铺管理员
        if ($res['is_admin'] != 1){
            $store_info=Model('store')->getStoreInfoByID($res['store_id']);
            $uid = $store_info['member_id'];
        }
        $model_pdr = Model('predeposit');
        $order_sn  = 'pd_order'. $model_pdr->makeSnBymember($uid);
        $subject='商户线上充值';
        $model_mb_payment = Model('mb_payment');
        $condition = array();
        $condition['payment_code'] = $this->payment_code;
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        if(!$mb_payment_info) {
            output_error('系统不支持选定的支付方式');
        }
      

        //$url={"code":200,"message":"OK","data":{"order_sn":"7000000000898501","invitation":"118672","order_state":10,"goods_name":"\u6fb3\u95e8\u8c46\u635e","pay_sn":"680498329767536319","need_pay":"1.00","order_amount":"1.00","pd_amount":0,"buy_time":1444985767}}
        $data = array();
        $data['pdr_sn']             = $order_sn;
        $data['pdr_member_id']      = $uid ;  
        $res=Model('member')->where(array('member_id'=>$uid))->find();
        $data['pdr_member_name']    = $res['member_name'];
        $data['pdr_payment_code']   = $this->payment_code;
        $data['pdr_amount']         = $api_pay_amount;
        $data['pdr_add_time']       = TIMESTAMP;
        $insert = Model()->table('pd_recharge')->insert($data);
        if ($insert) {
            if($this->payment_code=='alipay'){
                $result=array();
                $result['order_sn']     =$order_sn;
                $result['pay_sn']       =$order_sn;
                $result['order_amount'] =$api_pay_amount;
                $result['need_pay']=$api_pay_amount;
                $result['goods_name']      =$subject;
                $result['buy_time']=time();
                //$result['order_type']   ='pd_order';
                //$result['notify_url']   = MOBILE_SITE_URL .'/api/payment/alipay/app_call_back_url.php';
                output_data($result);
            }elseif($this->payment_code=='wx_app'){
               $result['order_sn']     =$order_sn;
               $result['pay_sn']       =$order_sn;
               output_data($result);
            }
         }
    }


    /**
     * 虚拟订单支付
     */
    public function vr_payOp() {
        $order_sn = $_REQUEST['pay_sn'];
        $model_mb_payment = Model('mb_payment');
        $logic_payment = Logic('payment');
    
        $condition = array();
        $condition['payment_code'] = $this->payment_code;
        $mb_payment_info = $model_mb_payment->getMbPaymentOpenInfo($condition);
        if(!$mb_payment_info) {
            output_error('系统不支持选定的支付方式');
        }
    
        //重新计算所需支付金额
        $result = $logic_payment->getVrOrderInfo($order_sn, $this->member_info['member_id']);
    
        if(!$result['state']) {
            output_error($result['msg']);
        }
    
        //第三方API支付
        $this->_api_pay($result['data'], $mb_payment_info);
    }

	/**
	 * 第三方在线支付接口
	 *
	 */
	private function _api_pay($order_pay_info, $mb_payment_info) {
        $inc_file = BASE_PATH.DS.'api'.DS.'payment'.DS.$this->payment_code.DS.$this->payment_code.'.php';
    	if(!is_file($inc_file)){
            output_error('支付接口不存在');
    	}
    	require($inc_file);
        $param = array();
    	$param = $mb_payment_info['payment_config'];
        $param['order_sn'] = $order_pay_info['pay_sn'];
        $param['order_amount'] = $order_pay_info['api_pay_amount'];
        $param['order_type'] = $order_pay_info['order_type'];
        $param['buy_goods_num'] = $order_pay_info['buy_goods_num'];
        $payment_api = new $this->payment_code();
        $return = $payment_api->submit($param);
        echo $return;
    	exit;
	}

    /**
     * 可用支付参数列表
     */
    public function payment_listOp() {
        $model_mb_payment = Model('mb_payment');

        $payment_list = $model_mb_payment->getMbPaymentOpenList();

        $payment_array = array();
        if(!empty($payment_list)) {
            foreach ($payment_list as $value) {
                $payment_array[] = $value['payment_code'];
            }
        }

        output_data(array('payment_list' => $payment_array));
    }
}
