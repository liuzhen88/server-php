<?php

/**
 * 我的订单
 *
 */
use Tpl;

defined('emall') or exit('Access Invalid!');

class member_orderControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 跑腿邦,订单状态
     *
     * @param  string $param['order_id']          订单ID
     */
    public function adt_order_stateOp() {

        if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {
            $condition['order_id']     = $_REQUEST['order_id'];
        } else {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }

        $condition['order_type'] = 3;   //3：配送订单

        $condition['buyer_id']     = $this->member_info['member_id'];

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition);

        if (empty($order_info)) {
            output_error('配送订单信息不存在');
        }

        $order_state = $model_order->getOrderLogList(array('order_id'=>$condition['order_id']));

        if (empty($order_state)) {
            if($order_info['order_state']==10){
                output_data(array('order' => '待支付'));
            }else{
                output_data(array('order' => '订单状态不存在'));
            }
        }

        $order = array();
        foreach($order_state as $key=>$var) {
            // 订单状态
            $order[$key]['order_id'] = $var['order_id']; // 订单ID
            $order[$key]['log_time'] = date('Y-m-d H:i', $var['log_time']); // 处理时间
            $order[$key]['log_orderstate'] = $var['log_orderstate'];
            if($var['log_orderstate']==20){
                $order[$key]['log_msg'] = '订单已提交';
            }elseif($var['log_orderstate']==0){
                $order[$key]['log_msg'] = '订单已取消';
            }else{
                $order[$key]['log_msg'] = $var['log_msg'];
            }
            $order[$key]['log_user'] = $var['log_user']; // 日志用户
            $order[$key]['log_role'] = $var['log_role']; // 日志作用
        }

        $pay_refund = Model('pay_refund');
        $data = array();
        $data['pay_sn'] = $order_info['pay_sn'];
        $result = $pay_refund->getPayRefund($data);
        if($result){
            if($result['refund_state']==1){
                $key++;
                // 退款成功,订单状态
                $order[$key]['order_id'] = $result['order_id']; // 订单ID
                $order[$key]['log_time'] = date('Y-m-d H:i', $result['success_time']); // 处理时间
                $order[$key]['log_orderstate'] = "0";
                $order[$key]['log_msg'] = '退款成功';
                $order[$key]['log_user'] = 'sys'; // 日志用户
                $order[$key]['log_role'] = '系统'; // 日志作用
            }
        }

        output_data(array('order' => $order));
    }

    /**
     * 跑腿邦,订单列表
     *
     * @param int $param['order_state']                            订单状态（选填）    10:未付款 待确认;
     *                                                                               20:待接单;
     *                                                                               30:待发货;
     *                                                                               35:配送中;
     *                                                                               40:已收货/o2o已完成;
     * @param string $param['curpage']                             当前页  默认第1页
     */
    public function adt_order_listOp() {

        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];

        //传状态参数时按状态、类型显示数据
        if (isset($_REQUEST['order_state'])) {
            if (!empty($_REQUEST['order_state'])) {
                $condition['order_state'] = (int)$_REQUEST['order_state'];
            }
        }

        $condition['order_type'] = 3;   //3：配送订单

        $model_order = Model('order');
        $order_list_array = $model_order->getNormalOrderList($condition, $this->page, '*', 'order_id desc', '', array('order_goods'));

        $new_order_group_list = array();
        $i = 0;
        foreach ($order_list_array as $key => $value) {
            $new_order_group_list[$i]['order_id'] = $value['order_id'];
            $new_order_group_list[$i]['order_sn'] = $value['order_sn'];
            $new_order_group_list[$i]['pay_sn'] = $value['pay_sn'];
            $new_order_group_list[$i]['order_state'] = $value['order_state'];
            $new_order_group_list[$i]['evaluation_state'] = $value['evaluation_state'];
            $new_order_group_list[$i]['order_amount'] = $value['order_amount'];
            $new_order_group_list[$i]['payment_time']   = $value['payment_time']; // 支付时间
            $new_order_group_list[$i]['hope_time'] = $value['hope_time'];
            $new_order_group_list[$i]['finnshed_time'] = $value['finnshed_time']; // 完成时间
            $new_order_group_list[$i]['is_get_quickly'] = $value['is_get_quickly']; //是否及时达,0：指定时间，1：及时达
            $new_order_goods_list = array();
            foreach ($value['extend_order_goods'] as $key2 => $value2) {
                $new_order_goods_list[$key2]['goods_name'] = $value2['goods_name'];
                $new_order_goods_list[$key2]['goods_image'] = cthumb($value2['goods_image'],300,$value['store_id']);
                $new_order_goods_list[$key2]['goods_num'] = $value2['goods_num'];
            }
            $new_order_group_list[$i]['extend_order_goods'] = $new_order_goods_list;
            $i++;
        }

        $array_data = array('order_group_list' => $new_order_group_list);
        $array_data['page_total'] = $model_order->getTotalPage();
        $array_data['total_num'] = $model_order->getTotalNum();

        output_data($array_data);
    }

    /**
     * 跑腿邦,订单详情
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     */
    public function adt_order_detailOp() {

        if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {
            $condition['order_id']     = $_REQUEST['order_id'];
        } else if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
            $condition['pay_sn']     = $_REQUEST['pay_sn'];
        } else if (isset($_REQUEST['order_sn']) && !empty($_REQUEST['order_sn'])){
            $condition['order_sn']   = $_REQUEST['order_sn'];
        } else {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        $condition['order_type'] = 3;   //3：配送订单

        $condition['buyer_id']     = $this->member_info['member_id'];

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition, array('order_goods','order_common','store'));

        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        // 订单信息
        $order = array();
        $order['order_id']       = $order_info['order_id']; // 订单ID
        $order['order_sn']       = $order_info['order_sn']; // 订单号
        $order['pay_sn']         = $order_info['pay_sn']; // 付款单号
        $order['add_time']       = date('Y-m-d H:i', $order_info['add_time']); // 消费时间
        $order['payment_time']   = $order_info['payment_time']!=0 ? date('Y-m-d H:i', $order_info['payment_time']):''; // 支付时间
        $order['hope_time']      = $order_info['hope_time']!=0 ? date('Y-m-d H:i', $order_info['hope_time']):''; // 希望收货时间
        $order['finnshed_time']  = $order_info['finnshed_time']!=0 ? date('Y-m-d H:i', $order_info['finnshed_time']) :''; // 订单完成时间
        $order['shipping_fee']   = price_format($order_info['shipping_fee']);  // 运费
        $order['order_amount']   = price_format($order_info['order_amount']);  // 消费总额
        $order['goods_amount']   = price_format($order_info['goods_amount']);  // 商品总额
        $order['order_state']    = $order_info['order_state'];
        $order['evaluation_state']    = $order_info['evaluation_state'];  //评价状态
        $order['complain_state'] = $order_info['complain_state'];  //投诉状态
        $order['consume_code']   = $order_info['consume_code'];
        $order['is_get_quickly'] = $order_info['is_get_quickly'];  //是否及时达,0：指定时间，1：及时达

        // 买家信息
        $order['buyer_name']     = $order_info['extend_order_common']['reciver_name']; // 买家姓名
        $order['buyer_mobile']   = $order_info['extend_order_common']['reciver_info']['phone'];
        $order['buyer_address']  = trim($order_info['extend_order_common']['reciver_info']['address']);  //收货地址
        $order['order_message']  = $order_info['extend_order_common']['order_message'];

        // 卖家电话
        $order['seller_mobile']  = $order_info['extend_store']['store_phone'];

        $new_order_goods_list = array();
        foreach ($order_info['extend_order_goods'] as $key => $value) {
            $new_order_goods_list[$key]['goods_name'] = $value['goods_name'];
            $new_order_goods_list[$key]['goods_price'] = $value['goods_price'];
            $new_order_goods_list[$key]['goods_pay_price'] = $value['goods_pay_price'];
            $new_order_goods_list[$key]['goods_num'] = $value['goods_num'];
            $new_order_goods_list[$key]['league_store_name'] = $value['league_store_name'];
        }

        output_data(array('order' => $order,'extend_order_goods' => $new_order_goods_list));
    }

    /**
     * 跑腿邦,取消订单
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     */
    public function adt_order_delOp() {

        if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {
            $condition['order_id']     = $_REQUEST['order_id'];
        } else if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
            $condition['pay_sn']     = $_REQUEST['pay_sn'];
        } else if (isset($_REQUEST['order_sn']) && !empty($_REQUEST['order_sn'])){
            $condition['order_sn']   = $_REQUEST['order_sn'];
        } else {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        $condition['order_type'] = 3;   //3：配送订单

        $condition['buyer_id']     = $this->member_info['member_id'];

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition);

        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        if($order_info['order_state']==10){
            $result = Logic('order')->changeOrderStateCancel($order_info,'buyer',$this->member_info['member_name'],'',true,true);
            Logic('order')->adt_return_order_coupon($order_info['order_id']);
        }elseif($order_info['order_state']==20){
            $result = Logic('order')->adt_changeOrderStateCancel($order_info,'buyer',$this->member_info['member_name'],'',true,true);
            Logic('order')->adt_return_order_coupon($order_info['order_id']);
        }elseif($order_info['order_state']==0){
            output_data_msg('已取消');
        }else{
            output_error('该状态无法取消');
        }

        if($result['state']){
            output_data_msg($result['msg']);
        }else{
            output_error($result['msg']);
        }
    }

    /**
     * 跑腿邦,微信退款状态查询
     */
    public function wx_order_refund_queryOp(){

        if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {
            $condition['order_id']     = $_REQUEST['order_id'];
        } else if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
            $condition['pay_sn']     = $_REQUEST['pay_sn'];
        } else if (isset($_REQUEST['order_sn']) && !empty($_REQUEST['order_sn'])){
            $condition['order_sn']   = $_REQUEST['order_sn'];
        } else {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        $condition['order_type'] = 3;   //3：配送订单

        $condition['buyer_id']     = $this->member_info['member_id'];

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition);

        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        $result = Logic('refund')->order_refund_query($order_info['pay_sn']);

        if($result['state']){
            output_data_msg($result['msg']);
        }else{
            output_error($result['msg']);
        }
    }

    //退款测试函数
/*    public function wx_order_refundOp(){
        $result = Logic('refund')->order_refund('310505408805209058','1');
        if($result['code']){
            //成功处理
            echo $result['msg'];
        }else{
            //失败处理
            echo $result['msg'];
        }
    }*/

    /**
     * 跑腿邦,确认订单
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     */
    public function adt_order_receive_endOp() {

        if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {
            $condition['order_id']     = $_REQUEST['order_id'];
        } else if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
            $condition['pay_sn']     = $_REQUEST['pay_sn'];
        } else if (isset($_REQUEST['order_sn']) && !empty($_REQUEST['order_sn'])){
            $condition['order_sn']   = $_REQUEST['order_sn'];
        } else {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        $condition['order_type'] = 3;   //3：配送订单

        $condition['buyer_id']     = $this->member_info['member_id'];

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition);

        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        if($order_info['order_state']==35){
            $result = Logic('order')->adt_changeOrderStateReceive($order_info,'buyer',$this->member_info['member_name']);
            if($result['state']){
                output_data_msg($result['msg']);
            }else{
                output_error($result['msg']);
            }
        }elseif($order_info['order_state']==40){
            output_data_msg('已完成');
        }else{
            output_error('该状态无法确认收货');
        }

    }

    /**
     * 跑腿邦,添加配送评价
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     * @param  string $param['leval_desccredit']        商品质量评分
     * @param  string $param['leval_servicecredit']     配送服务评分
     * @param  string $param['leval_deliverycredit']    配送速度评分
     * @param  string $param['leval_content']     评论内容
     */
    public function adt_add_order_evaluateOp() {

        if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {
            $condition['order_id']     = $_REQUEST['order_id'];
        } else if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
            $condition['pay_sn']     = $_REQUEST['pay_sn'];
        } else if (isset($_REQUEST['order_sn']) && !empty($_REQUEST['order_sn'])){
            $condition['order_sn']   = $_REQUEST['order_sn'];
        } else {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        $condition['order_type'] = 3;   //3：配送订单

        $condition['buyer_id']     = $this->member_info['member_id'];

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition);

        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        $leval_desccredit = intval($_REQUEST['leval_desccredit']);
        if($leval_desccredit <= 0 || $leval_desccredit > 5) {
            $leval_desccredit = 5;
        }

        $leval_servicecredit = intval($_REQUEST['leval_servicecredit']);
        if($leval_servicecredit <= 0 || $leval_servicecredit > 5) {
            $leval_servicecredit = 5;
        }

        $leval_deliverycredit = intval($_REQUEST['leval_deliverycredit']);
        if($leval_deliverycredit <= 0 || $leval_deliverycredit > 5) {
            $leval_deliverycredit = 5;
        }

        $leval_content = (isset($_REQUEST['leval_content']) && !empty($_REQUEST['leval_content'])) ? trim($_REQUEST['leval_content']) : '';
        if (empty($leval_content)) {
            output_error('评论内容不能为空');
        }

        if($order_info['evaluation_state']==1){
            output_error('已评价,不需要再次评价');
        }

        if($order_info['order_state']==40){
            $data = array();
            $data['leval_orderid'] = $order_info['order_id'];
            $data['leval_orderno'] = $order_info['order_sn'];
            $data['leval_addtime'] = TIMESTAMP;
            $data['leval_storeid'] = $order_info['store_id'];
            $data['leval_storename'] = $order_info['store_name'];
            $data['leval_memberid'] = $this->member_info['member_id'];
            $data['leval_membername'] = $this->member_info['member_name'];
            $data['leval_desccredit'] = $leval_desccredit;
            $data['leval_servicecredit'] = $leval_servicecredit;
            $data['leval_deliverycredit'] = $leval_deliverycredit;
            $data['leval_content'] = $leval_content;
            $result = $model_order->addOrderEvaluate($data);
            if($result){
                $model_order->editOrder(array('evaluation_state' => 1), array('order_id' => $order_info['order_id']));
                output_data_msg('评价成功');
            }else{
                output_error('评价失败');
            }
        }else{
            output_error('该状态不能评价');
        }

    }

    /**
     * 订单列表
     * 
     * @param int $param['getpayment']                             支付状态（选填）  true|false
     * @param int $param['order_state']| $param['order_status']    订单状态（选填）  10:未付款待确认;
     *                                                                               20:已付款;30:已发货;
     *                                                                               40:已收货/o2o已完成 (返回说明：order_state=40)
     *                                                                               41:待评价 (返回说明：order_state=40 且 valuation_state=0)
     *                                                                               42:已评价 (返回说明：order_state=40 且 valuation_state=1) 【这才项目订单的已完成】 
     *                                                                               43:过期未评价 (返回说明：order_state=40 且 valuation_state=2) 【这个项目用不到】
     *                                                                               44: 所有退款 (返回说明：order_state=40 且 refund_state=1|2)
     *                                                                               45: 退部分款 (返回说明：order_state=40 且 refund_state=1)
     *                                                                               46: 退全部款 (返回说明：order_state=40 且 refund_state=2)
     * @param int $param['order_type']                             店铺类型(选填)    1.本土 2.线上
     * @param string $param['curpage']                             当前页  默认第1页
     */
    public function order_listOp() {

        $model_order = Model('order');

        $condition = array();
        //传状态参数时按状态、类型显示数据
        if (isset($_REQUEST['order_status'])) { 
            if (!empty($_REQUEST['order_status'])) {
                $condition = $this->_filter_order_state((int)$_REQUEST['order_status']);
            } 
        } else if (isset($_REQUEST['order_state'])) { 
            if (!empty($_REQUEST['order_state'])) {
                $condition = $this->_filter_order_state((int)$_REQUEST['order_state']);
            }
        }
        
        if (isset($_REQUEST['order_type']) && !empty($_REQUEST['order_type'])) { 
            $condition['order_type'] = (int)$_REQUEST['order_type'];
        }

        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_list_array = $model_order->getNormalOrderList($condition, $this->page, '*', 'order_id desc', '', array('order_goods', 'store', 'member'));

        // 根据订单取商品的退款退货状态
        $order_list_array = Model('refund_return')->getGoodsRefundList($order_list_array);
       
        $order_group_list = array();
        $order_pay_sn_array = array();
        $store_arr = array();
        foreach ($order_list_array as $value) {
            $value = $this->_set_order_detail($value); //二次处理订单详情

            //所有都显示商家信息
            if (isset($store_arr[$value['store_id']])) {
                $value['store_info'] = $store_arr[$value['store_id']];
            } else {
                $value['store_info'] = $this->_get_store_info($value['store_id']);
                $store_arr[$value['store_id']] = $value['store_info'];
            }

            $order_group_list[$value['pay_sn']]['order_list'][] = $value;

            //如果有在线支付且未付款的订单则显示合并付款链接(应前端要求，这里不做判断处理)
            if ($value['order_state'] == ORDER_STATE_NEW) { 
                $order_group_list[$value['pay_sn']]['pay_amount'] += ($value['order_amount']*100 - $value['rcb_amount']*100 - $value['pd_amount']*100)/100;
                $order_group_list[$value['pay_sn']]['shipping_fee'] += $value['shipping_fee'];
            }
            $order_group_list[$value['pay_sn']]['add_time'] = $value['add_time'];
            //$order_group_list[$value['pay_sn']]['shipping_fee'] = $value['shipping_fee'];
            //记录一下pay_sn，后面需要查询支付单表
            $order_pay_sn_array[] = $value['pay_sn'];
        }

        $new_order_group_list = array();
        foreach ($order_group_list as $key => $value) {
            $value['pay_sn'] = strval($key);
            $new_order_group_list[] = $value;
        }

        $page_count = $model_order->gettotalpage();

        $array_data = array('order_group_list' => $new_order_group_list);
        if (isset($_REQUEST['getpayment']) && $_REQUEST['getpayment'] == "true") {
            $model_mb_payment = Model('mb_payment');

            $payment_list = $model_mb_payment->getMbPaymentOpenList();
            $payment_array = array();
            if (!empty($payment_list)) {
                foreach ((array)$payment_list as $value) {
                    $payment_array[] = array('payment_code' => $value['payment_code'], 'payment_name' => $value['payment_name']);
                }
            }
            $array_data['payment_list'] = $payment_array;
        }


        output_data($array_data, mobile_page($page_count));
    }
    
    /**
     * 取消订单
     * 
     * @param int $param['order_id'] 订单ID
     */
    public function order_cancelOp() {
        if (!isset($_REQUEST['order_id']) || empty($_REQUEST['order_id'])) {
            output_error('参数有误', array(), ERROR_CODE_ARG);
        }
        
        $order_id =  (int)$_REQUEST['order_id'];
        $model_order = Model('order');
        $logic_order = Logic('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('buyer_cancel', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }

        $result = $logic_order->changeOrderStateCancel($order_info, 'buyer', $this->member_info['member_name'], '其它原因');
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('操作成功');
        }
    }

    /**
     * 删除订单
     * 
     * @param int $param['order_id'] 订单ID
     */
    public function order_deleteOp() {
        if (!isset($_REQUEST['order_id']) || empty($_REQUEST['order_id'])) {
            output_error('参数有误', array(), ERROR_CODE_ARG);
        }
        
        $order_id =  (int)$_REQUEST['order_id'];
        $model_order = Model('order');
        $logic_order = Logic('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('delete', $order_info); //只允许交易完成、待支付、已取消时候操作
        if (!$if_allow) {
            output_error('无权操作');
        }

        $result = $logic_order->changeOrderStateDelete($order_info, 'buyer', $this->member_info['member_name'], '其它原因');
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('操作成功');
        }
    }
    
    /**
     * 订单确认收货
     * 
     * @param int $param['order_id'] 订单ID
     */
    public function order_receiveOp() {
        $model_order = Model('order');
        $logic_order = Logic('order');
        $order_id = intval($_REQUEST['order_id']);

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition);
        $if_allow = $model_order->getOrderOperateState('receive', $order_info);
        if (!$if_allow) {
            output_error('无权操作');
        }

        $result = $logic_order->changeOrderStateReceive($order_info, 'buyer', $this->member_info['member_name']);
        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            output_data('操作成功');
        }
    }

    /**
     * 订单物流跟踪
     * 
     * @param int $param['order_id'] 订单ID
     */
    public function search_deliverOp() {
        $order_id = intval($_REQUEST['order_id']);
        if ($order_id <= 0) {
            output_error('参数缺省');
        }

        $model_order = Model('order');
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $model_order->getOrderInfo($condition, array('order_common'));
        if (empty($order_info)) {
            output_error('订单不存在');
        }
        
//        switch ($order_info['order_state']) {
//            case ORDER_STATE_CANCEL :    output_error('订单已取消');  break;
//            case ORDER_STATE_NEW :       output_error('订单待支付');  break;
//            case ORDER_STATE_PAY :       output_error('订单待发货');  break;
//        }

        $express = rkcache('express', true);
        $e_code = $express[$order_info['extend_order_common']['shipping_express_id']]['e_code'];
        $e_name = $express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
        // 物流追踪信息
        $deliver_info = $this->_get_express_new($e_code, $order_info['shipping_code'], $order_info['delivery_result']);
        
        $result = array(
            'express_name'  => $e_name, // 配送公司
            'shipping_code' => $order_info['shipping_code'], // 物流单号
            'deliver_info'  => $deliver_info['data'], // 配送信息
            'deliver_state' => $deliver_info['state'], // 配送状态
            'reciver_name'  => $order_info['extend_order_common']['reciver_name'], // 收货人姓名
            'reciver_info'  =>  $order_info['extend_order_common']['reciver_info'] // 收货人其他信息
        );
        output_data($result);
    }

    
    /**
     * 本土订单确认
     * 
     * @author chenyifei
     * @param string $param['order_sn']     订单编号
     * @param string $param['code']         员工确认码
     * @param string $param['invitation']   邀请码
     * 
     */
    public function local_order_sureOp()
    {
        $order_sn = isset($_REQUEST['order_sn']) ? trim($_REQUEST['order_sn']) : ''; //订单编号
        $code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';    //员工确认码
        $invitation = isset($_REQUEST['invitation']) ? trim($_REQUEST['invitation']) : '';  //邀请码
        $client_type = isset($_REQUEST['client_type']) ? trim($_REQUEST['client_type']) : '';  //来源
        $model_order = Model('order');
        if (empty($order_sn) || empty($invitation))
        {
            output_error('参数错误');
        }
        if (empty($code))
        {
            output_error('员工确认码不能为空');
        }
        $order_info = $model_order->getOrderInfo(array('order_sn'=>$order_sn, 'order_type'=>1), array('order_goods'));
        if (empty($order_info))
            output_error('订单不存在');
        if ($order_info['order_state'] != ORDER_STATE_NEW)
        {
            if ($order_info['order_state'] == ORDER_STATE_CANCEL)
            {
                output_error('订单已取消');
            }
            elseif ($order_info['order_state'] == ORDER_STATE_SUCCESS)
            {
                output_error('订单已确认');
            }
            elseif ($order_info['order_state'] == ORDER_STATE_PAY)
            {
                output_error('订单已支付');
            }
            elseif ($order_info['order_state'] == ORDER_STATE_SEND)
            {
                output_error('订单已发货');
            }
            else 
            {
                output_error('订单不可确认');
            }
        }
        //判断邀请码
        $store_member = Model('member')->getMemberInfo(array('invitation'=>$invitation));
        if (empty($store_member))
            output_error('口令有误');
        //商户
        $store_info = Model('store')->where(array('member_id'=>$store_member['member_id']))->find();
        if (empty($store_info))
            output_error('商户不存在');
        //判断员工验证码
        $seller_info = Model('seller')->getSellerInfo(array('store_id'=>$store_info['store_id'], 'pay_code'=>$code));
        if (empty($seller_info))
        {
            $data = array(
                'store_id' => $order_info['store_id'] ,
                'seller_ip' => getIp() ,
                'order_id' => $order_info['order_id'] ,
                'pay_time' => TIMESTAMP ,
                'pay_amount' => $order_info['order_amount'] ,
                'buyer_id' => $order_info['buyer_id'] ,
                'buyer_name' => $order_info['buyer_name'] ,
                'pay_status' => 2,
                'client_type' => $client_type,
            );
            Logic('queue')->addCodePayLog($data);
            output_error('口令有误');
            
        }
        $code_message = $this->_code_pay_log_check($seller_info, $order_info)    ;
        if (!empty($code_message))
            output_error($code_message);
        $buy_log = Logic('buy');
        $result = $buy_log->localOrderSure($order_info, $store_info, $seller_info, $this->member_info);
        if ($result['state'] == true)
        {
            $data = array(
                'seller_id' => $seller_info['seller_id'] ,
                'seller_name' => $seller_info['seller_name'] ,
                'member_id' => $seller_info['member_id'] ,
                'store_id' => $order_info['store_id'] ,
                'seller_ip' => getIp() ,
                'order_id' => $order_info['order_id'] ,
                'pay_time' => TIMESTAMP ,
                'pay_amount' => $order_info['order_amount'] ,
                'buyer_id' => $order_info['buyer_id'] ,
                'buyer_name' => $order_info['buyer_name'] ,
                'pay_status' => 1,
                'client_type' => $client_type,
            );
            Logic('queue')->addCodePayLog($data);
            output_data('订单确认成功');
        }
        else 
        {
            output_error($result['msg']);
        }
    }
    
    /**
     * 消费码确认订单
     */
    public function local_order_consume_code_sureOp(){
        $order_sn = isset($_REQUEST['consume_code']) ? trim($_REQUEST['consume_code']) : ''; //消费码，本土预售订单确认码
        $code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';    //员工确认码
        $invitation = isset($_REQUEST['invitation']) ? trim($_REQUEST['invitation']) : '';  //邀请码
        $client_type = isset($_REQUEST['client_type']) ? trim($_REQUEST['client_type']) : '';  //来源
        $model_order = Model('order');
        if (empty($order_sn) || empty($invitation))
        {
            output_error('参数错误');
        }
        if (empty($code))
        {
            output_error('员工确认码不能为空');
        }
        /* $order_info = $model_order->getOrderInfo(array('consume_code'=>$order_sn, 'order_type'=>1), array('order_goods'));
        if (empty($order_info))
            output_error('订单不存在');
        if ($order_info['order_state'] != ORDER_STATE_PAY)
        {
            output_error('此订单非待确认状态，不可确认');
        } */
        //判断邀请码
        $store_member = Model('member')->getMemberInfo(array('invitation'=>$invitation));
        if (empty($store_member))
            output_error('口令有误');
        //商户
        $store_info = Model('store')->where(array('member_id'=>$store_member['member_id']))->find();
        if (empty($store_info))
            output_error('商户不存在');
        //判断员工验证码
        $seller_info = Model('seller')->getSellerInfo(array('store_id'=>$store_info['store_id'], 'pay_code'=>$code));
        if (empty($seller_info))
        {
           /*  $data = array(
                'store_id' => $order_info['store_id'] ,
                'seller_ip' => getIp() ,
                'order_id' => $order_info['order_id'] ,
                'pay_time' => TIMESTAMP ,
                'pay_amount' => $order_info['order_amount'] ,
                'buyer_id' => $order_info['buyer_id'] ,
                'buyer_name' => $order_info['buyer_name'] ,
                'pay_status' => 2,
                'client_type' => $client_type,
            );
            Logic('queue')->addCodePayLog($data); */
            output_error('口令有误');
        
        }
        /* $code_message = $this->_code_pay_log_check($seller_info, $order_info)    ;
        if (!empty($code_message))
            output_error($code_message); */
        $buy_log = Logic('buy');
        $result = $buy_log->localOrderConsumeCodeSure($order_sn, $store_info['store_id'], $seller_info['seller_name']);
        if ($result['state'] == true)
        {
            /* $data = array(
                'seller_id' => $seller_info['seller_id'] ,
                'seller_name' => $seller_info['seller_name'] ,
                'member_id' => $seller_info['member_id'] ,
                'store_id' => $order_info['store_id'] ,
                'seller_ip' => getIp() ,
                'order_id' => $order_info['order_id'] ,
                'pay_time' => TIMESTAMP ,
                'pay_amount' => $order_info['order_amount'] ,
                'buyer_id' => $order_info['buyer_id'] ,
                'buyer_name' => $order_info['buyer_name'] ,
                'pay_status' => 1,
                'client_type' => $client_type,
            );
            Logic('queue')->addCodePayLog($data); */
            output_data('订单确认成功');
        }
        else
        {
            output_error($result['msg']);
        }
    }
    
  /**
   * 员工确认码确认订单日志
   * @param unknown $seller_info
   */  
    private function _code_pay_log_check($seller_info, $order_info)
    {
        $message = '';
        if ($seller_info['pay_code_is_agree'] != 1)
        {
            //$message = '暂未开启支付口令功能，请选择其它支付方式';
            return ;
        } 
        
        //每笔订单限额 （30元）以下
        if ($order_info['order_amount'] >= $seller_info['pay_code_consume'])
        {
            $message = '超过面对面支付消费限额，请选择其他支付方式';
            return $message;
        }
        $seller_code_pay_log = Model('seller_code_pay_log');
        $now_time = strtotime(date('Y-m-d'));
        //当天可用次数
        $pay_code_use = $seller_code_pay_log->getLogCount(array('member_id'=>$seller_info['member_id'], 'store_id'=>$seller_info['store_id'], 'buyer_id'=>$order_info['buyer_id'], 'pay_time'=>array('egt', $now_time)));
        if ($pay_code_use >= $seller_info['pay_code_use'])
        {
            $message = '超过面对面支付可用支付次数，请选择其他支付方式';
            return $message;
        }
        //客户端试错（5次）以后，该客户（3天）内不能再次使用该功能
        $pay_time = TIMESTAMP - $seller_info['pay_code_error_punish']*60*60*24;
        $pay_code_error_count = $seller_code_pay_log->getLogCount(array( 'store_id'=>$seller_info['store_id'], 'buyer_id'=>$order_info['buyer_id'], 'pay_status'=>2, 'pay_time'=>array('gt', $pay_time)));
        if ($pay_code_error_count >= $seller_info['pay_code_error'])
        {
            $message = '超过输入次数，面对面支付禁用'.$seller_info['pay_code_error_punish'].'天';
            return $message;
        }
        return $message;
    }
    

    /**
     * 订单详情(支付之后返回)
     * 
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     * @param  float  $param['location']          地理位置 格式如117.281456,31.868228
     */
    public function order_detailOp() {

        if (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id'])) {
            $condition['order_id']     = $_REQUEST['order_id'];
        } else if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
            $condition['pay_sn']     = $_REQUEST['pay_sn'];
        } else if (isset($_REQUEST['order_sn']) && !empty($_REQUEST['order_sn'])){
            $condition['order_sn']   = $_REQUEST['order_sn'];
        } else {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        
        if (!preg_match('/^\d{1,3}\.\d+,\d{1,3}.\d+$/', $_REQUEST['location'])) {
            output_error('地理位置格式错误', array(), ERROR_CODE_ARG);
        }

        $condition['buyer_id']     = $this->member_info['member_id'];
        $order['invitation']   = $this->member_info['invitation'];
        // 微信支付、支付宝支付，需要做个校验 (client_type)
        //if (isset($_REQUEST['pay_sn']) && !empty($_REQUEST['pay_sn'])) {
        //    $condition['order_state']  = array('gt', ORDER_STATE_NEW); //ORDER_STATE_NEW已付款的都可以查询 大于0未付款
       // }
        
        $model_order = Model('order');
        $model_goods = Model('goods');
        $model_goods_class = Model('goods_class');
        $order_info = $model_order->getOrderInfo($condition, array('store'));
        $rt = Model('member')->getMemberInfo(array('member_id'=>$order_info['extend_store']['member_id']));
        $store['store_invitation'] = $rt['invitation'];
        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        // 订单信息
        $order['order_id']       = $order_info['order_id']; // 订单ID
        $order['order_sn']       = $order_info['order_sn']; // 订单号
        $order['pay_sn']         = $order_info['pay_sn']; // 付款单号
        $order['payment_code']   = $order_info['payment_code']; // 付款方式
        $order['payment_time']   = date('Y-m-d H:i', $order_info['add_time']); // 消费时间
        $order['order_amount']   = price_format($order_info['order_amount']);  // 消费总额
        $order['goods_amount']   = price_format($order_info['goods_amount']);  // 商品总额
        $order['pd_amount']      = price_format($order_info['pd_amount']);  // 抵用预存款(积分)
        $order['realpay_amount'] = price_format($order_info['order_amount'] - $order_info['pd_amount']); //实际支付
        //$order['order_state']    = $order_info['order_state']; //0(已取消)10(默认):未付款认;20:已付款;30:已发货;40:已收货/o2o已完成;
        //$order['evaluation_state'] = $order_info['evaluation_state']; //0未评价，1已评价，2已过期未评价
        //已完成已评价42, 已完成未评价 41
        if($order_info['order_state']==40 && $order_info['goods_type'] == 0) {
            if(isset($_REQUEST['ver_code']) && $_REQUEST['ver_code']<32) { 
                $order['order_state'] = 42;
            }else{
                if($order_info['evaluation_state'] == 0)
                    $order['order_state'] = 41;
                else
                    $order['order_state'] = 42;
            }
        }
        elseif($order_info['order_state']==40 && $order_info['evaluation_state'] == 0 && $order_info['goods_type'] == 1)
            $order['order_state'] = 41;
        elseif($order_info['order_state']==40 && $order_info['evaluation_state'] == 1 && $order_info['goods_type'] == 1)
            $order['order_state'] = 42;
        else
            $order['order_state']    = $order_info['order_state'];
        //本土 无商品 小于1元返回成功 
        if($order_info['order_amount'] < 1 && $order_info['order_type'] == 1 && $order_info['goods_type'] == 0 && $order_info['order_state']==40) {
            $order['order_state'] = 42;
        }
        $order['goods_type']     = $order_info['goods_type'];   
        $order['consume_code']   = $order_info['consume_code'];

        // 买家信息
        $order['buyer_id']       = $order_info['buyer_id']; // 买家ID
        $order['buyer_name']     = $order_info['buyer_name']; // 买家姓名
        
        // 本土商家要显示的东西
        if ($order_info['order_type'] == 1) {

            if($order_info['order_state']== 0 && $order_info['refund_state'] == 2)
                $order['order_state']    =  51;
            // 商品ID
            $order_goods_detail = $model_order->getOrderGoodsInfo(array('order_id'=>$order_info['order_id']), '*'); //获取订单商品表
            $order['goods_id'] = $order_goods_detail['goods_id'];
            $goods = $model_goods->getGoodsInfo(array('goods_id'=>$order_goods_detail['goods_id']),'goods_id,goods_price,goods_marketprice,validate_time,goods_image,gc_id,evaluation_good_star');
            //本土预售
            if ($order_info['goods_type'] == 1) {
                $order['goods_image']       =   cthumb($goods['goods_image'], 60, $goods['store_id']);
                $order['goods_name']        =   $order_goods_detail['goods_name'];
                $order['goods_num']         =   $order_goods_detail['goods_num'];
                $order['validate_time']     =   ($goods['validate_time']?$goods['validate_time']:0);
                $order['goods_price']       =   ($goods['goods_price']?$goods['goods_price']:$order_goods_detail['goods_pay_price']);  
                $order['goods_marketprice'] =   ($goods['goods_marketprice']?$goods['goods_marketprice']:$order_goods_detail['goods_price']);
                $order['goods_count']       =   $model_order->getOrderGoodsCount(array('goods_id'=>$order['goods_id']));
                //$order['goods_credit']      =   ($goods['evaluation_good_star']?(string)$goods['evaluation_good_star']:"0");

                $eva_where                  =   array('geval_ordergoodsid' =>$order_goods_detail['rec_id'] , 'geval_frommemberid'=>$order_info['buyer_id']);
                $o                          =   Model('evaluate_goods')->getEvaluateGoodsInfo($eva_where);
                $order['goods_credit']      =   ($o['geval_scores']?(float)$o['geval_scores']:"0");
            }

            // 返利金额
            $rebate_condition = array(
                'order_id'    => $order_info['order_id'],
                'member_id'  => $order_info['buyer_id'],
                'user_type'   => 1, 
            );
            
            if($order_info['goods_type'] == 0){
                $commis_rate = round(($order_info['commis_rate']*$order_info['order_amount'])/100,2);
                $order['rebate'] = price_format(round(($commis_rate*REBATE_BUY_USER)/100,2));
            }else{
                //$rebate_records_detail = Model('rebate_records')->getRebateRecordsInfo($rebate_condition);
                //$order['rebate'] = empty($rebate_records_detail) ? '0.00' : price_format($rebate_records_detail['rebate']); //返利金额
                $goods_class_rebate = $model_order->getOrderGoodsInfo(array('order_id'=>$order_info['order_id']));
                $commis_rate = round(($goods_class_rebate['commis_rate']*$goods_class_rebate['goods_pay_price'])/100,2);
                $order['rebate'] = price_format(round(($commis_rate*REBATE_BUY_USER)/100,2));
            }
            
        } 
        
        // 商家信息
        $store['store_id']      = $order_info['store_id'];
        $store['store_name']    = $order_info['extend_store']['store_name'];
        $store['area_info']     = $order_info['extend_store']['area_info'] . $order_info['extend_store']['store_address'];
        $store['mobile']        = $order_info['extend_store']['store_phone'];
        $store['store_credit']  = $order_info['extend_store']['store_credit_old'];//$order_info['extend_store']['store_credit_average'];
        $store['store_avatar']  = getStoreLogo($order_info['extend_store']['store_avatar']);
        $store['district_name'] = $order_info['extend_store']['district_name'];
        $store['evaluate_count']= Model('evaluate_goods')->getStoreCredit(array('geval_storeid' =>$order_info['store_id']));
        //$store['goods_count']   = $order_info['extend_store']['goods_count'];
        $storec  = Model('store_bind_class')->getStoreBindClassInfo(array('store_id'=>$store['store_id']));
        $storet   = $model_goods_class->getGoodsClassName(array('gc_id'=>$storec['class_1']));
        $store['store_type_name']   =   $storet['gc_name'];

        $store_location         = $order_info['extend_store']['lng'] . ',' .  $order_info['extend_store']['lat'];
        $location               = $_REQUEST['location'];
        $store['distance']      = get_distance($location, $store_location) ;
        $store['distance']      = $store['distance'] > 1000 ? round($store['distance']/1000) . 'km' : $store['distance'] . 'm';
        output_data(array('order' => $order, 'store' => $store));
    }

    
    /**
     * 获取商户邀请码
     * 
     * @param int store_id 店铺ID
     */
    public function get_seller_invitationOp() {
        if (!isset($_REQUEST['store_id']) && !empty($_REQUEST['store_id'])) {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        
        $store_info = Model('store')->getOneStore(array('store_id' => (int)$_REQUEST['store_id']), 'member_id');
        if (empty($store_info)) {
             output_error('商家信息不存在');
        }
        
        $member_info = Model('member')->getMemberInfoByID($store_info['member_id'], 'invitation');
        output_data((string)$member_info['invitation']);
    }
    
    /**
     * 具体线上订单商品评价详情
     * 
     * @param int $param['order_id'] 订单ID
     */
    public function order_evaluateOp()
    {
        $order_id = intval($_REQUEST['order_id']);
        if (empty($order_id)){
            output_error('参数为空');
        }
        $orders = Model('order')->getOrderInfo(array('order_id' => $order_id, 'buyer_id' => $this->member_info['member_id'] ), array('order_goods'), 'order_id, order_sn, store_id,store_name,  evaluation_state');
        if (empty($orders) && empty($orders['extend_order_goods']))
        {
            output_error('订单不存在');
        }  
        $has_evaluate_store = 0;
        $evaluate_good_ids = array();
        //if ($orders['evaluation_state'] == 0)
        {
            //商户评分
            
            $evaluate_store = Model('evaluate_store')->getEvaluateStoreInfo(array('seval_orderid' => $order_id, 'seval_storeid' => $orders['store_id']), 'seval_id');
            if (!empty($evaluate_store))
                $has_evaluate_store = 1; 
            $evaluate_goods = Model('evaluate_goods')->getEvaluateGoodsList(array('geval_orderid' => $order_id, 'geval_storeid' =>$orders['store_id'] ));
            foreach ($evaluate_goods as $key=>$row)
            {
                $evaluate_good_ids[] = $row['geval_goodsid'];
            }
        }
        $orders['has_evaluate_store'] = $has_evaluate_store;
        foreach ($orders['extend_order_goods'] as $key=>$row)
        {
            $data = array(
                'goods_id' => $row['goods_id'],
                'goods_name' => $row['goods_name'],
                'goods_price' => $row['goods_price'],
                'goods_pay_price' => $row['goods_pay_price'],
                'goods_num' => $row['goods_num'],
                'goods_image' =>  cthumb($row['goods_image'], 240, $row['store_id']) ,
                'has_evaluated' => in_array($row['goods_id'], $evaluate_good_ids) ? 1 : 0 ,
                
            );
            $orders['extend_order_goods'][$key] = $data;
        }
        output_data($orders);
        
    }

    /**
     * 线上商户评价
     * 
     * @param int $param['order_id']
     */
    public function store_evaluateOp()
    {
        $order_id = intval($_REQUEST['order_id']);
        $seval_desccredit = intval($_REQUEST['seval_desccredit']);
        if($seval_desccredit <= 0 || $seval_desccredit > 5) {
            $seval_desccredit = 5;
        }
        
        $seval_servicecredit = intval($_REQUEST['seval_servicecredit']);
        if($seval_servicecredit <= 0 || $seval_servicecredit > 5) {
            $seval_servicecredit = 5;
        }
        
        $seval_deliverycredit = intval($_REQUEST['seval_deliverycredit']);
        if($seval_deliverycredit <= 0 || $seval_deliverycredit > 5) {
            $seval_deliverycredit = 5;
        }
        
        $seval_logistics = intval($_REQUEST['seval_logistics']);
        if($seval_logistics <= 0 || $seval_logistics > 5) {
            $seval_logistics = 5;
        }
        
        if (empty($order_id)){
            output_error('参数为空');
        }
        $orders = Model('order')->getOrderInfo(array('order_id' => $order_id, 'buyer_id' => $this->member_info['member_id'], 'order_type'=> 2), array('order_goods'));
        if (empty($orders) && empty($orders['extend_order_goods']))
        {
            output_error('订单不存在');
        }
        if ($orders['evaluation_state'] != 0)
        {
            output_error('此订单已经评论');
        }
        $evaluate_store = Model('evaluate_store')->getEvaluateStoreInfo(array('seval_orderid' => $order_id, 'seval_storeid' => $orders['store_id']), 'seval_id');
        if (!empty($evaluate_store))
        {
            output_error('此商户已经评价过，不可重复评价');
        }
        $data = array(
            'seval_orderid' => $orders['order_id'],
            'seval_orderno' => $orders['order_sn'],
            'seval_addtime' => TIMESTAMP,
            'seval_storeid' => $orders['store_id'],
            'seval_storename' => $orders['store_name'],
            'seval_memberid' => $orders['buyer_id'],
            'seval_membername' => $orders['buyer_name'],
            'seval_desccredit' => $seval_desccredit,
            'seval_servicecredit' => $seval_servicecredit,
            'seval_deliverycredit' => $seval_deliverycredit,
            'seval_logistics' => $seval_logistics,
        );
        $result = Model('evaluate_store')->addEvaluateStore($data);
        if (empty($result))
        {
            output_error('商户评价失败');
        }
        else 
        {
            //对应商品评价数量
            $evaluate_goods = Model('evaluate_goods')->where(array('geval_orderid' => $order_id, 'geval_storeid' =>$orders['store_id'] ))->count();
            //订单对应商品数
            $order_goods = Model('order_goods')->where(array('order_id' => $order_id, 'store_id' =>$orders['store_id'] ))->count();
            if ($evaluate_goods == $order_goods)
            {
                //更新订单信息并记录订单日志
                $state = Model('order')->editOrder(array('evaluation_state'=>1), array('order_id' => $order_id));
                Model('order')->editOrderCommon(array('evaluation_time'=>TIMESTAMP), array('order_id' => $order_id));
                if ($state){
                    $data = array();
                    $data['order_id'] = $order_id;
                    $data['log_role'] = 'buyer';
                    $data['log_msg'] = L('order_log_eval');
                    Model('order')->addOrderLog($data);
                }  
            }
            //更新店铺评价值
            $evaluet_store_field = 'AVG(seval_desccredit) as store_desccredit,';
            $evaluet_store_field .= 'AVG(seval_servicecredit) as store_servicecredit,';
            $evaluet_store_field .= 'AVG(seval_deliverycredit) as store_deliverycredit,';
            $evaluet_store_field .= 'AVG(seval_logistics) as store_logisticscredit,';
            $evaluet_store_field .= 'COUNT(seval_id) as count';
            $evaluet_store_info = Model('evaluate_store')->getEvaluateStoreInfo(array('seval_storeid'=>$orders['store_id']), $evaluet_store_field);
            $store_evaluet_data = array(
                'store_credit'=>$evaluet_store_info['store_desccredit'],
                'store_desccredit' => $evaluet_store_info['store_desccredit'],
                'store_servicecredit' => $evaluet_store_info['store_servicecredit'],
                'store_deliverycredit' => $evaluet_store_info['store_deliverycredit'],
                'store_logisticscredit' => $evaluet_store_info['store_logisticscredit'],
            );
            Model('store')->editStore($store_evaluet_data, array('store_id' => $orders['store_id'] )); 
            
            output_data('评价成功');
        }
    }
    
    /**
     * 商品评价
     * 
     * @param int     $param['order_id']           订单ID
     * @param int     $param['goods_id']           商品ID
     * @param string  $param['geval_content']      评论内容
     * @param string  $param['geval_isanonymous']  是否匿名评价 0不匿名 1匿名 (选填)
     * @param FILES   $param['image_1']            图片1
     * @param FILES  $param['image_2']             图片2
     * @param FILES  $param['image_n']             图片n
     */
    public function good_evaluateOp()
    {

        $order_id = intval($_REQUEST['order_id']);
        $goods_id = intval($_REQUEST['goods_id']);
        if (!$order_id && !$goods_id){
            output_error('参数不正确');
        }
        $geval_content = (isset($_REQUEST['geval_content']) && !empty($_REQUEST['geval_content'])) ? trim($_REQUEST['geval_content']) : '';
        if (empty($geval_content)) {
            output_error('评论内容不能为空');
        }
        
        $model_order = Model('order');
        $orders = $model_order->getOrderInfo(array('order_id' => $order_id, 'buyer_id' => $this->member_info['member_id']), array('order_goods'));
        if (empty($orders) && empty($orders['extend_order_goods']))
        {
            output_error('订单不存在');
        }
        if ($orders['evaluation_state'] != 0)
        {
            output_error('此订单已经评论');
        }
        $order_goods = array();
        foreach ($orders['extend_order_goods'] as $key=>$row)
        {
            if ($row['goods_id'] == $goods_id)
            {
                $order_goods = $row;
                break;
            }
        }
        if (empty($order_goods))
        {
            output_error('此商品不存在');
        }
        $evaluate_good_ids = array();
        $evaluate_goods = Model('evaluate_goods')->getEvaluateGoodsList(array('geval_orderid' => $order_id, 'geval_storeid' =>$orders['store_id'] ));
        foreach ($evaluate_goods as $key=>$row)
        {
            $evaluate_good_ids[] = $row['geval_goodsid'];
        }
        if (in_array($goods_id, $evaluate_good_ids))
        {
            output_error('此商品已经评论过，不可重复评论');
        }
        //上传图片处理
        $geval_image = '';
        $geval_image_arr = array();
        foreach ($_REQUEST as $key=>$row)
        {
            if (substr($key, 0, 6) == 'image_')
            {
                $upload = new UploadFile();
                $upload_dir = ATTACH_MALBUM.DS.$this->member_info['member_id'].DS;
                $upload->set('max_size',C('image_max_filesize'));
                $upload->set('default_dir',$upload_dir.$upload->getSysSetPath());
                $upload->set('fprefix',$this->member_info['member_id']);
                $img_result = $upload->upBase64Image($row);
                if (!$img_result){
                    /* if (strtoupper(CHARSET) == 'GBK'){
                        $upload->error = Language::getUTF8($upload->error);
                    }
                    echo output_error($upload->error);die; */
                    //图片上传失败则不保存
                    continue;
                }
                $geval_image_arr[] = $upload->getSysSetPath().$upload->file_name;
            }
        }
        $geval_image = implode(',', $geval_image_arr);
        $geval_scores = intval($_REQUEST['geval_scores']);
        if($geval_scores <= 0 || $geval_scores > 5) {
            $geval_scores = 5;
        }
        
        
        $geval_isanonymous = (isset($_REQUEST['geval_isanonymous']) && intval($_REQUEST['geval_isanonymous']) == 1) ? 1 : 0; 
        
        $evaluate_goods_info = array(
            'geval_orderid' => $orders['order_id'],
            'geval_orderno' => $orders['order_sn'],
            'geval_ordergoodsid' => $order_goods['rec_id'],
            'geval_goodsid' => $order_goods['goods_id'],
            'geval_goodsname' => $order_goods['goods_name'],
            'geval_goodsprice' => $order_goods['goods_price'],
            'geval_goodsimage' => $order_goods['goods_image'],
            'geval_scores' => $geval_scores,
            'geval_content' => $geval_content,
            'geval_isanonymous' => $geval_isanonymous,
            'geval_addtime' => TIMESTAMP,
            'geval_storeid' => $orders['store_id'],
            'geval_storename' => $orders['store_name'],
            'geval_frommemberid' => $this->member_info['member_id'],
            'geval_frommembername' => $this->member_info['member_truename'],
            'geval_state' => 0,
            'geval_image' => $geval_image,
        );
        $evaluate_goods_array[] = $evaluate_goods_info;      

        $goodsid_array[] = $order_goods['goods_id']; 

        $model_evaluate_goods = Model('evaluate_goods');
        $evaluate_good_result = $model_evaluate_goods->addEvaluateGoodsArray($evaluate_goods_array, $goodsid_array);
        if ($evaluate_good_result)
        {
            QueueClient::push('updateGoodsEvaluate', $goods_id);
            //订单中所有商品全部评价完情况
            if (count($orders['extend_order_goods']) == ( count($evaluate_good_ids) + 1 ) )
            {
                $evaluate_flg = true;
                
                //商户评论
                if ($orders['order_type'] == 2)
                {
                    $evaluate_store = Model('evaluate_store')->getEvaluateStoreInfo(array('seval_orderid' => $order_id, 'seval_storeid' => $orders['store_id']), 'seval_id');
                    if (empty($evaluate_store))
                        $evaluate_flg = false;
                }
               //线上订单需所有商品和店铺都评价才完成；本土订单仅商品评价完成
                if ($evaluate_flg)
                {
                    //更新订单信息并记录订单日志
                    $state = $model_order->editOrder(array('evaluation_state'=>1), array('order_id' => $order_id));
                    $model_order->editOrderCommon(array('evaluation_time'=>TIMESTAMP), array('order_id' => $order_id));
                    if ($state){
                        $data = array();
                        $data['order_id'] = $order_id;
                        $data['log_role'] = 'buyer';
                        $data['log_msg'] = L('order_log_eval');
                        $model_order->addOrderLog($data);
                    }
                }
                
            }

            //本土商铺评论平均值
            if ($orders['order_type'] == 1)
            {
                $evaluet_store_field = ' AVG(geval_scores) as store_credit ';
                $evaluet_goods_field = Model('evaluate_goods')->getEvaluateGoodsInfo(array('geval_storeid'=>$orders['store_id'], 'geval_state' => 0), $evaluet_store_field);
                $store_evaluet_data = array(
                    'store_credit'=>$evaluet_goods_field['store_credit'],
                );
                Model('store')->editStore($store_evaluet_data, array('store_id' => $orders['store_id'] ));
            }
            
            
           output_data('评论成功');
        }
        else 
        {
            output_error('评论失败');
        }
        
    }



    /**
     * 店铺评价
     *
     * @param int     $param['order_id']           订单ID
     * @param int     $param['store_id']           店铺ID
     * @param string  $param['geval_content']      评论内容
     * @param string  $param['geval_isanonymous']  是否匿名评价 0不匿名 1匿名 (选填)
     * @param FILES   $param['image_1']            图片1
     * @param FILES  $param['image_2']             图片2
     * @param FILES  $param['image_n']             图片n
     */
    public function local_store_evaluateOp()
    {

        $order_id = intval($_REQUEST['order_id']);
        $store_id = intval($_REQUEST['store_id']);
        if (!$order_id && !$store_id){
            output_error('参数不正确');
        }
        $geval_content = (isset($_REQUEST['geval_content']) && !empty($_REQUEST['geval_content'])) ? trim($_REQUEST['geval_content']) : '';
        if (empty($geval_content)) {
            output_error('评论内容不能为空');
        }

        $model_order = Model('order');
        $orders = $model_order->getOrderInfo(array('order_id' => $order_id, 'buyer_id' => $this->member_info['member_id']));
        if (empty($orders))
        {
            output_error('订单不存在');
        }
        if ($orders['evaluation_state'] != 0)
        {
            output_error('此订单已经评论');
        }

        if ($orders['order_amount'] < 1)
        {
            output_error('对不起，价格小于1元， 不能评价');
        }

        $evaluate_good_ids = array();
        $evaluate_goods = Model('evaluate_goods')->getEvaluateGoodsList(array('geval_orderid' => $order_id, 'geval_storeid' =>$orders['store_id'] ));
        if ($evaluate_goods)
        {
            output_error('已经评论过，不可重复评论');
        }
        //上传图片处理
        $geval_image = '';
        $geval_image_arr = array();
        foreach ($_REQUEST as $key=>$row)
        {
            if (substr($key, 0, 6) == 'image_')
            {
                $upload = new UploadFile();
                $upload_dir = ATTACH_MALBUM.DS.$this->member_info['member_id'].DS;
                $upload->set('max_size',C('image_max_filesize'));
                $upload->set('default_dir',$upload_dir.$upload->getSysSetPath());
                $upload->set('fprefix',$this->member_info['member_id']);
                $img_result = $upload->upBase64Image($row);
                if (!$img_result){
                    /* if (strtoupper(CHARSET) == 'GBK'){
                        $upload->error = Language::getUTF8($upload->error);
                    }
                    echo output_error($upload->error);die; */
                    //图片上传失败则不保存
                    continue;
                }
                $geval_image_arr[] = $upload->getSysSetPath().$upload->file_name;
            }
        }
        $geval_image = implode(',', $geval_image_arr);
        $geval_scores = intval($_REQUEST['geval_scores']);
        if($geval_scores <= 0 || $geval_scores > 5) {
            $geval_scores = 5;
        }
        $geval_isanonymous = (isset($_REQUEST['geval_isanonymous']) && intval($_REQUEST['geval_isanonymous']) == 1) ? 1 : 0;

        $evaluate_goods_info = array(
            'geval_orderid' => $orders['order_id'],
            'geval_orderno' => $orders['order_sn'],
            'geval_ordergoodsid' => '',
            'geval_goodsid' => '',
            'geval_goodsname' => '',
            'geval_goodsprice' => '',
            'geval_goodsimage' =>'',

            'geval_scores' => $geval_scores,
            'geval_content' => $geval_content,
            'geval_isanonymous' => $geval_isanonymous,
            'geval_addtime' => TIMESTAMP,
            'geval_storeid' => $orders['store_id'],
            'geval_storename' => $orders['store_name'],
            'geval_frommemberid' => $this->member_info['member_id'],
            'geval_frommembername' => $this->member_info['member_truename'],
            'geval_state' => 0,
            'geval_image' => $geval_image,
        );
        $evaluate_goods_array[] = $evaluate_goods_info;

        $model_evaluate_goods = Model('evaluate_goods');
        $evaluate_good_result = $model_evaluate_goods->addEvaluateGoodsArray($evaluate_goods_array);
        if ($evaluate_good_result)
        {
                //更新订单信息并记录订单日志
                $state = $model_order->editOrder(array('evaluation_state'=>1), array('order_id' => $order_id));
                $model_order->editOrderCommon(array('evaluation_time'=>TIMESTAMP), array('order_id' => $order_id));
                if ($state){
                    $data = array();
                    $data['order_id'] = $order_id;
                    $data['log_role'] = 'buyer';
                    $data['log_msg'] = L('order_log_eval');
                    $model_order->addOrderLog($data);
            }

            //本土商铺评论平均值
            if ($orders['order_type'] == 1)
            {
                $evaluet_store_field = ' AVG(geval_scores) as store_credit ';
                $evaluet_goods_field = Model('evaluate_goods')->getEvaluateGoodsInfo(array('geval_storeid'=>$orders['store_id'], 'geval_state' => 0), $evaluet_store_field);
                $store_evaluet_data = array(
                    'store_credit'=>$evaluet_goods_field['store_credit'],
                );
                Model('store')->editStore($store_evaluet_data, array('store_id' => $orders['store_id'] ));
            }
            output_data('评论成功');
        }
        else
        {
            output_error('评论失败');
        }

    }



    /**
     * 获取用户指定订单指定商品的评价
     * 
     * @param int order_id 订单id
     * @param int goods_id 商品id
     * @param int member_id 会员id
     */
    public function get_evaluate_by_order_goodsOp(){

        $check_param=array('order_id','goods_id');
        check_request_parameter($check_param);

        $condition = array();
        $condition['geval_orderid'] = intval($_REQUEST['order_id']);
        $condition['geval_goodsid'] = intval($_REQUEST['goods_id']);
        $condition['geval_frommemberid'] = $this->member_info['member_id'];
        $condition['geval_state'] = 0;
        $evaluate=Model('evaluate_goods')->where($condition)->order('geval_addtime desc')->select();
        if(!empty($evaluate)){
            foreach($evaluate as $key=>$value){
                $evaluate[$key]['geval_addtime']=date('Y-m-d H:i:s',$evaluate[$key]['geval_addtime']);
                //评价图片
                $geval_images=array();
                if(!empty($value['geval_image'])){
                    foreach(explode(',',$value['geval_image']) as $value){
                        $geval_images[]=snsThumb($value);
                    }
                }
                $evaluate[$key]['images']=$geval_images;
                $evaluate[$key]['geval_frommemberavara']=qnyResizeHD(getMemberAvatarForID( $this->member_info['member_id']),100);
            }
        }
        output_data($evaluate);
    }    
    
    
    /**
     * 从第三方取快递信息
     *
     */
    private function _get_express($e_code, $shipping_code) {

        $url = 'http://www.kuaidi100.com/query?type=' . $e_code . '&postid=' . $shipping_code . '&id=1&valicode=&temp=' . random(4) . '&sessionid=&tmp=' . random(4);
        import('function.ftp');
        $content = dfsockopen($url);
        $content = json_decode($content, true);

        if ($content['status'] != 200) {
            return array();
        }
        
     
        
        // $content['data'] = array_reverse($content['data']);
        $output = array();
        if (is_array($content['data'])) {
            foreach ($content['data'] as $k => $v) {
                if ($v['time'] == '')
                    continue;
                $output[] = $v['time'] . '&nbsp;&nbsp;' . $v['context'];
            }
        }
           
        if (strtoupper(CHARSET) == 'GBK') {
            $output = Language::getUTF8($output); //网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
        }

        return array('data' => $output, 'state' => $content['state']);
    }
    /**
     * 从数据库取快递信息
     * 订阅接口
     */
    private function _get_express_new($e_code, $shipping_code, $delivery_result) {
          if(!empty($delivery_result)){
               $content = mb_unserialize($delivery_result);
               $output = array();
               if (is_array($content['data'])) {
                   foreach ($content['data'] as $k => $v) {
                       if ($v['time'] == '')
                           continue;
                       $output[] = $v['time'] . '&nbsp;&nbsp;' . $v['context'];
                   }
               }
               if (strtoupper(CHARSET) == 'GBK') {
                   $output = Language::getUTF8($output); //网站GBK使用编码时,转换为UTF-8,防止json输出汉字问题
               }
               return array('data' => $output, 'state' => $content['state']);
           }
           else {
               return array();
           }
    }
    /**
     * 统一过滤下订单状态
     * 
     * @param  int $order_state
     * @return array
     */
    private function _filter_order_state($order_state) {
     
        $evaluation_state = -1;
        $refund_state     = -1;
        
        switch ($order_state) {
            
            case 41 : $evaluation_state = 0; break;
            case 42 : $evaluation_state = 1; break;
            case 43 : $evaluation_state = 2; break;
            
            case 44 : $refund_state = array('gt', 0); break;
            case 45 : $refund_state = 1;              break;
            case 46 : $refund_state = 2;              break;
        }
        // 待评价本土 特殊处理 51是预售退款
        if ( $_REQUEST['order_type'] == 1) {
            if ($order_state == 41) {
                 return array('order_state' => 40, 'evaluation_state' => $evaluation_state, 'goods_type' => 1);//
            } else if ($order_state == 42) {
                 return array('order_state' => 40,'evaluation_state' => $evaluation_state); //,  'goods_type' => 0
            } elseif($order_state == 51) {
                return array('order_state' => 0, 'refund_state' => 2);//
            }
        }
        if ((int)$evaluation_state >= 0) {
            // 交易成功后评价状态
            return array('order_state' => 40, 'evaluation_state' => $evaluation_state);
        } 
        
        if ((int)$refund_state >= 0) {
            // 交易成功后退换状态
            return array('order_state' => 40, 'refund_state' => $refund_state);
        } 
        
        return array('order_state' => $order_state);

    }
    
    /**
     * 店铺ID
     * 
     * @param int $store_id
     * @return array
     */
    private function _get_store_info($store_id) {
         $result = Model('store')->getOneStore(array('store_id' => $store_id), 'store_name,store_avatar,CONCAT(area_info," ", store_address) AS area');
         $result['store_avatar'] = getStoreLogo($result['store_avatar']);      
         
         return $result;
    }
    
    /**
     * 二次设置订单详情
     * 
     * @return type
     */
    private function _set_order_detail($value) {
        
       $model_order = Model('order');
       // 显示取消订单
       $value['if_cancel'] = $model_order->getOrderOperateState('buyer_cancel', $value);
       // 显示收货
       $value['if_receive'] = $model_order->getOrderOperateState('receive', $value);
       // 显示锁定中
       $value['if_lock'] = $model_order->getOrderOperateState('lock', $value);
       // 显示物流跟踪
       $value['if_deliver'] = $model_order->getOrderOperateState('deliver', $value);
       // 显示申请退款
       $value['if_refund'] = $model_order->getOrderOperateState('refund_cancel', $value);
       // 显示评价
       $order['if_evaluation'] = $model_order->getOrderOperateState('evaluation', $order);

       // 商品图
       if (!empty($value['extend_order_goods'])) {
           foreach ((array)$value['extend_order_goods'] as $k => $goods_info) {
               $value['extend_order_goods'][$k]['goods_image_url'] = cthumb($goods_info['goods_image'], 240, $value['store_id']);
           }
       }
       

       // 状态返回结果处理
       $value['order_state_tip'] = '';
       switch ($value['order_state']) {
           case ORDER_STATE_CANCEL :
                if($value['order_type'] == 1 && $value['refund_state'] == 2) {
                   $value['order_state_tip'] = '已退款';
                   $value['order_state_code'] = ORDER_STATE_OFFLINE;
                }else{
                   $value['order_state_tip'] = '已取消';
                   $value['order_state_code'] = ORDER_STATE_CANCEL;
                }
               break;
           case ORDER_STATE_NEW :    
               $value['order_state_tip'] = '待付款';
               $value['order_state_code'] = ORDER_STATE_NEW;
               break;
           case ORDER_STATE_PAY :    
               $value['order_state_tip'] = '待发货';
               $value['order_state_code'] = ORDER_STATE_PAY;
               break;
           case ORDER_STATE_SEND :   
               $value['order_state_tip'] = '待收货';
               $value['order_state_code'] = ORDER_STATE_SEND;
               break;
           case ORDER_STATE_SUCCESS :
                // 正常状态 
            if(isset($_REQUEST['ver_code']) && $_REQUEST['ver_code']<32) { 
               if($value['order_type'] == 1 && $value['goods_type'] == 0){
                   // 特殊状态：本土无商品的订单，显示"已完成"
                   $value['order_state_tip'] = '已完成';
                   $value['order_state_code'] = ORDER_STATE_FINISH;
               }else{
                    $value['order_state_tip']  = $value['evaluation_state'] == 1 ? L('order_state_success') : L('order_state_valuetion');
                    $value['order_state_code'] = $value['evaluation_state'] == 1 ? ORDER_STATE_FINISH : ORDER_STATE_VALUATION;
               }
            }else{
                $value['order_state_tip']  = $value['evaluation_state'] == 1 ? L('order_state_success') : L('order_state_valuetion');
                $value['order_state_code'] = $value['evaluation_state'] == 1 ? ORDER_STATE_FINISH : ORDER_STATE_VALUATION;
            }
            if($value['order_amount'] <= 1 && $value['order_type'] == 1 && $value['goods_type'] == 0 && $value['order_state']==40) {
                $value['order_state_tip'] = '已完成';
                $value['order_state_code'] = ORDER_STATE_FINISH;
            }
              break;
       }
       
       return $value;
    }
    /**
     * 获取优惠信息
     * @author daliang
     * @param string $param['key']           token
     * @param string $param['store_id']      店铺id
     * @param string $param['goods_id']      商品id
     * @param string $param['goods_count']   商品数量
     * @param string $param['total']         订单总额
     */
    public function get_discountOp()
    {
        //判断店铺买单还是活动买单  店铺买单享受全场折扣
        $discount_info = array();
        if(empty($_REQUEST['goods_id'])&&!empty($_REQUEST['store_id'])){
            //计算优惠折扣金额
            //全场折扣
            $store_info = Model('store')->getStoreInfoByID($_REQUEST['store_id']);
            if(floatval($store_info['whole_discount'])!=10.0) {
                $discount = $_REQUEST['total']*(1-$store_info['whole_discount']*0.1);
                $discount_info[0]['name'] = '全场'.$store_info['whole_discount'].'折';
                $discount_info[0]['money'] =round($discount,2);
            }
        }
        //获取商品和店铺信息
        $goods_info = array();
        if(!empty($_REQUEST['goods_id'])) {
            $goods_model = Model('goods');
            $goods_info = $goods_model->getGoodsInfoByID($_REQUEST['goods_id']);
            // 商品图片
            $goods_info['goods_image']=cthumb($goods_info['goods_image'],'',$goods_info['store_id']);
        }
        // ....其他折扣


        //计算折扣总额
        $discount_sum = 0;
        foreach($discount_info as $value) {
            $discount_sum = $discount_sum+$value['money'];
        }
        //计算可用金额
        $available_predeposit = price_format($this->member_info['available_predeposit']); // 账户余额
        $predeposit = $available_predeposit;   // 可提现金额
        $store_info = Model('store')->getOneStore(array('member_id' => $this->member_info['member_id']));
        if (!empty($store_info)) {
            $condition = array(
                'member_id' => $this->member_info['member_id'],
                'type'      => 1,
            );
            $predeposit = Model('store_pre_deposit')->getStorePredepositCount($condition);
            $amount_total = empty($predeposit['amount_total']) ? 0.00 : $predeposit['amount_total'];
            $num = $available_predeposit - $amount_total;
            if($num<0)
                $num=0;
            $predeposit = price_format($num);
        }
        output_data($discount_info,array('discount_sum'=>$discount_sum,'predeposit'=>$predeposit,'goods_info'=>$goods_info));
    }
}

