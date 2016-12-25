<?php
defined('emall') or exit('Access Invalid!');
/**
 * 加盟店店铺订单相关信息控制器
 * @author Administrator
 *
 */
class adt_store_orderControl extends BaseStoreLeagueControl{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 跑腿邦,订单列表
     * @param int $param['order_state']                            订单状态（选填）    10:未付款 待确认;
     *                                                                               20:待接单;
     *                                                                               30:待发货;
     *                                                                               35:配送中;
     *                                                                               40:已收货/o2o已完成;
     * @param string $param['curpage']                             当前页  默认第1页
     */
    public function indexOp() {

        $condition = array();

        if(isset($this->seller_info['store_id'])) {
            $condition['league_store_id'] = $this->seller_info['store_id'];
        }else{
            output_error('店铺ID信息不存在');
        }

        //传状态参数时按状态、类型显示数据
        if (isset($_REQUEST['order_state'])) {
            if (!empty($_REQUEST['order_state'])) {
                $condition['order_state'] = (int)$_REQUEST['order_state'];
            }
        }

        $condition['order_type'] = 3;   //3：配送订单

        $model_order = Model('order');
        $order_list_array = $model_order->getNormalOrderList($condition, $this->page, '*', 'order_id desc', '', array('order_goods','member','order_common'));

        $new_order_group_list = array();
        $i = 0;
        foreach ($order_list_array as $key => $value) {
            $new_order_group_list[$i]['order_id'] = $value['order_id'];
            $new_order_group_list[$i]['order_sn'] = $value['order_sn'];
            $new_order_group_list[$i]['pay_sn'] = $value['pay_sn'];
            $new_order_group_list[$i]['hope_time'] = $value['hope_time'];
            $new_order_group_list[$i]['buyer_name'] = $value['extend_order_common']['reciver_name']; // 买家姓名
            $new_order_group_list[$i]['buyer_mobile'] = $value['extend_order_common']['reciver_info']['phone'];
            $new_order_group_list[$i]['buyer_address'] = $value['extend_order_common']['reciver_info']['address'];
            $new_order_group_list[$i]['order_amount'] = $value['order_amount'];
            if($value['order_state']==40){
                $order_evaluate_info = $model_order->getOrderEvaluate(array('leval_orderid'=>$value['order_id']));
                $evaluate = '';
                if($order_evaluate_info){
                    $evaluate = round(($order_evaluate_info['leval_desccredit']+$order_evaluate_info['leval_servicecredit']+$order_evaluate_info['leval_deliverycredit'])/3,1);
                }
                $new_order_group_list[$i]['order_evaluate'] = $evaluate;  //评价
            }else{
                $order_assist_info = $model_order->getAssistOrderLog(array('order_id'=>$value['order_id']));
                $assist = '';
                if($order_assist_info){
                    if($order_assist_info['status']==0){
                        $assist = '订单协调中';
                    }
                }
                $new_order_group_list[$i]['order_assist'] = $assist;  //协调信息
            }
            //客户与商户距离,单位米
            if(isset($value['extend_order_common']['lng']) && isset($value['extend_order_common']['lat']) ) {
                $new_order_group_list[$i]['distance'] = get_distance($this->store_info['lng'] . ',' . $this->store_info['lat'], $value['extend_order_common']['lng'] . ',' . $value['extend_order_common']['lat']);
            }else{
                $new_order_group_list[$i]['distance'] = '';
            }
            $i++;
        }

        $array_data = array('order_group_list' => $new_order_group_list);
        $array_data['page_total'] = $model_order->getTotalPage();
        $array_data['total_num'] = $model_order->getTotalNum();

        output_data($array_data);
    }

    /**
     * 跑腿邦,订单状态数量
     *
     */
    public function order_stat_nuOp() {

        $condition = array();

        if(isset($this->seller_info['store_id'])) {
            $condition['league_store_id'] = $this->seller_info['store_id'];
        }else{
            output_error('店铺ID信息不存在');
        }

        $condition['order_type'] = 3;   //3：配送订单

        $model_order = Model('order');
        $order_list_array = $model_order->getNormalOrderList($condition, '', '*', 'order_id desc');

        $order_stat_nu = array(
            'waiting_rcv'=>0,
            'waiting_send'=>0,
            'sending'=>0,
            'ok'=>0
        );
        foreach ( $order_list_array as $key => $value){

            if($value['order_state']==20){

                $order_stat_nu['waiting_rcv']++;

            }elseif($value['order_state']==30){

                $order_stat_nu['waiting_send']++;

            }elseif($value['order_state']==35){

                $order_stat_nu['sending']++;

            }elseif($value['order_state']==40){

                $order_stat_nu['ok']++;

            }
        }
        output_data_msg($order_stat_nu);
    }

    /**
     * 跑腿邦,首页状态
     *
     */
    public function home_statOp() {

        $condition = array();

        if(isset($this->seller_info['store_id'])) {
            $condition['league_store_id'] = $this->seller_info['store_id'];
        }else{
            output_error('店铺ID信息不存在');
        }

        $complain_model = Model('complain');
        $ComplainCount = $complain_model->adt_getComplainCount($condition);
        $send_percent = $complain_model->query('SELECT send_percent FROM agg_adt_store_home_stat WHERE store_id='.$condition['league_store_id']);
        if(!$send_percent){
            $send_percent_nu = 100;  //默认送达率为100%
        }else{
            $send_percent_nu = $send_percent[0]['send_percent'];
        }
        $home_stat = array(
            'complain_count'=>intval($ComplainCount),
            'send_percent'=>$send_percent_nu
        );

        output_data_msg($home_stat);
    }

    /**
     * 跑腿邦,订单详情
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     */
    public function order_detailOp() {

        $condition = $this->_chk_store_stat();

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition, array('order_goods','order_common'));

        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        $order_evaluate_info = $model_order->getOrderEvaluate(array('leval_orderid'=>$condition['order_id']));
        $evaluate = '';
        if($order_evaluate_info){
            $evaluate = round(($order_evaluate_info['leval_desccredit']+$order_evaluate_info['leval_servicecredit']+$order_evaluate_info['leval_deliverycredit'])/3,1);
        }

        // 订单信息
        $order = array();
        $order['order_id']       = $order_info['order_id']; // 订单ID
        $order['order_sn']       = $order_info['order_sn']; // 订单号
        $order['pay_sn']         = $order_info['pay_sn']; // 付款单号
        $order['add_time']       = date('Y-m-d H:i', $order_info['add_time']); // 消费时间
        $order['payment_time']   = $order_info['payment_time']!=0 ? date('Y-m-d H:i', $order_info['payment_time']):''; // 支付时间
        $order['hope_time']      = $order_info['hope_time']; // 希望收货时间
        $order['finnshed_time']  = $order_info['finnshed_time']!=0 ? date('Y-m-d H:i', $order_info['finnshed_time']) :''; // 订单完成时间
        $order['shipping_fee']   = price_format($order_info['shipping_fee']);  // 运费
        $order['order_amount']   = price_format($order_info['order_amount']);  // 消费总额
        $order['goods_amount']   = price_format($order_info['goods_amount']);  // 商品总额
        $order['order_state']    = $order_info['order_state'];
        $order['order_evaluate'] = $evaluate;  //评价

        $order_assist_info = $model_order->getAssistOrderLog(array('order_id'=>$order_info['order_id']));
        $assist = '';
        if($order_assist_info){
            if($order_assist_info['status']==0){
                $assist = '订单协调中';
            }
        }
        $order['order_assist'] = $assist;  //协调信息

        //客户与商户距离,单位米
        if(isset($order_info['extend_order_common']['lng']) && isset($order_info['extend_order_common']['lat']) ) {
            $order['distance'] = get_distance($this->store_info['lng'] . ',' . $this->store_info['lat'], $order_info['extend_order_common']['lng'] . ',' . $order_info['extend_order_common']['lat']);
        }else{
            $order['distance'] = '';
        }

        // 买家信息
        $order['buyer_name']     = $order_info['extend_order_common']['reciver_name']; // 买家姓名
        $order['buyer_mobile']   = $order_info['extend_order_common']['reciver_info']['phone'];
        $order['buyer_address']  = $order_info['extend_order_common']['reciver_info']['address'];
        $order['order_message']  = $order_info['extend_order_common']['order_message'];

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
     * 跑腿邦,接单
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     */
    public function order_receiveOp() {

        $condition = $this->_chk_store_stat();

        $model_order = Model('order');
        try {
            //开始事务
            $model_order->beginTransaction();

            $order_info = $model_order->lock(true)->getOrderInfo($condition);

            if (empty($order_info)) {
                output_error('订单信息不存在');
            }

            if($order_info['order_state']==20) {

                //更新订单信息并记录订单日志  order_state => 30:待发货;
                $state = $model_order->editOrder(array('order_state' => 30), array('order_id' => $order_info['order_id']));

                if($state){
                    //添加订单日志
                    $data = array();
                    $data['order_id'] = intval($order_info['order_id']);
                    $data['log_role'] = 'seller';
                    $data['log_user'] = $this->seller_info['seller_name'];
                    $data['log_msg'] = '已接单';
                    $data['log_orderstate'] = ORDER_STATE_SEND;
                    $model_order->addOrderLog($data);
                }else{
                    throw new Exception('设置失败');
                }

            }elseif($order_info['order_state']==30){
                output_error('已接单');
            }elseif($order_info['order_state']==0){
                $err_data = array();
                $err_data['order_state'] = 0;
                output_error('用户已取消',$err_data);
            }else{
                output_error('该状态无法接单');
            }

            //提交事务
            $model_order->commit();

        }catch (Exception $e){
            //回滚事务
            $model_order->rollback();
            output_error('接单失败');
        }

        output_data_msg(array(),'接单成功，请尽快配送');

    }

    /**
     * 跑腿邦,开始配送
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     */
    public function order_sendOp() {

        $condition = $this->_chk_store_stat();

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition);

        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        if($order_info['order_state']==30) {
            //更新订单信息并记录订单日志  order_state => 35:配送中;
            $state = $model_order->editOrder(array('order_state' => 35), array('order_id' => $order_info['order_id']));
        }elseif($order_info['order_state']==35){
            output_data_msg('已开始配送');
        }else{
            output_error('接单后才能配送');
        }

        if($state){
            //添加订单日志
            $data = array();
            $data['order_id'] = intval($order_info['order_id']);
            $data['log_role'] = 'seller';
            $data['log_user'] = $this->seller_info['seller_name'];
            $data['log_msg'] = '商家已发货';
            $data['log_orderstate'] = ORDER_STATE_SENDING;
            $model_order->addOrderLog($data);

            // 商户配送发送消息给买家
            $param = array();
            $param['code'] = 'league_order_send';
            $param['member_id'] = $order_info['buyer_id'];
            $param['param'] = array(
                'order_sn' => $order_info['order_sn'],
                'order_url' => urlShop('member_order', 'show_order', array('order_id' => $order_info['order_id'])),
                'message_title' => '跑腿邦发货提醒',
                'consume_code' => $order_info['consume_code'],
                );
            QueueClient::push('sendMemberMsg', $param);
            output_data_msg('提交成功');
        }

        output_error('提交失败');
    }


    /**
     * 跑腿邦,确认订单
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     */
    public function order_receive_endOp() {

        $condition = $this->_chk_store_stat();

        if (isset($_REQUEST['consume_code']) && !empty($_REQUEST['consume_code'])) {
            $condition['consume_code']     = $_REQUEST['consume_code'];
        }else{
            output_error('请输入确认码');
        }

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition);

        if (empty($order_info)) {
            output_error('订单信息不存在,或确认码错误');
        }

        if($order_info['order_state']==35){
            $result = Logic('order')->adt_changeOrderStateReceive($order_info,'seller',$this->seller_info['seller_name']);
            if($result['state']){
                output_data($result['msg']);
            }else{
                output_error($result['msg']);
            }
        }elseif($order_info['order_state']==40){
            output_data('已完成');
        }else{
            output_error('还未配送无法确认');
        }

    }

    /**
     * 跑腿邦,协调订单
     *
     * @param  string $param['order_id']          订单ID (和支付ID选传一个即可)
     * @param  string $param['order_sn']          订单编号 (和支付单号选传一个即可)
     * @param  string $param['pay_sn']            支付单号 (和订单编号选传一个即可)
     * @param  string $param['msg']               协调内容
     */
    public function order_assistOp() {

        $condition = $this->_chk_store_stat();

        $assist = array();

        if (isset($_REQUEST['msg']) && !empty($_REQUEST['msg'])) {
            $assist['assist_content']     = $_REQUEST['msg'];
        }else{
            output_error('请输入内容');
        }

        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition);

        if (empty($order_info)) {
            output_error('订单信息不存在');
        }

        $assist['order_id'] = $order_info['order_id'];
        $assist['order_sn'] = $order_info['order_sn'];
        $assist['assist_order_state'] = $order_info['order_state'];
        $assist['league_store_id'] = $order_info['league_store_id'];
        $assist['league_store_name'] = $order_info['league_store_name'];
        $assist['add_user_id'] = $this->seller_info['member_id'];
        $assist['add_user_name'] = $this->seller_info['seller_name'];
        $state = $model_order->addAssistOrderLog($assist);

        if($state){
            output_data('提交成功');
        }

        output_error('提交失败');
    }

    /**
     * 跑腿邦,协调订单消息模版
     *
     * @param  string $param['msgtype']               协调类型   1:接单
     *                                                          2:待发货
     *                                                          3:配送中
     */
    public function order_assistmsg_listOp() {

        if (isset($_REQUEST['msgtype']) && !empty($_REQUEST['msgtype'])) {
            $msgtype = (int)$_REQUEST['msgtype'] - 1;
        }else{
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }

        $assist = array(
            array(
                '库存已空，申请协助',
                '分单错误，申请转单'
            ),
            array(
                '库存已空，请求协助',
                '配送员繁忙，请求协助'
            ),
            array(
                '客户没带手机，帮忙完成',
                '客户不在家，延迟配送',
                '客户手机打不通',
                '客户修改地址和配送时间'
            )
        );

        output_data($assist[$msgtype]);

    }

    /**
     * 跑腿邦,私有订单信息检查
     *
     * @return $condition
     */
    private function _chk_store_stat() {

        $condition = array();
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

        if(isset($this->seller_info['store_id'])) {
            $condition['league_store_id'] = $this->seller_info['store_id'];
        }else{
            output_error('无店铺信息');
        }

        return $condition;

    }

    /**
     * 跑腿邦,店铺消息列表
     */
    public function msg_listOp() {
        $where = array();
        if(isset($this->seller_info['store_id'])) {
            $where['store_id'] = $this->seller_info['store_id'];
        }

        $model_storemsg = Model('store_msg');
        $msg_list = $model_storemsg->getStoreMsgList($where, '*', 10);

        // 整理数据
        $new_msg_list = array();
        if (!empty($msg_list)) {
            foreach ($msg_list as $key => $val) {
                $new_msg_list[$key]['sm_id'] = $val['sm_id'];
                $new_msg_list[$key]['smt_code'] = $val['smt_code'];
                $new_msg_list[$key]['sm_content'] = $val['sm_content'];
                $new_msg_list[$key]['sm_addtime'] = $val['sm_addtime'];
                $readuser = explode(',', $val['sm_readids']);
                if(in_array($this->seller_info['seller_id'],$readuser)){
                    $new_msg_list[$key]['read_state'] = 1;
                }else {
                    $new_msg_list[$key]['read_state'] = 0;
                }
            }
        }
        output_data($new_msg_list);
    }

    /**
     * 跑腿邦,店铺消息标记为已读
     */
    public function mark_as_readOp() {
        if (isset($_REQUEST['smids']) && !empty($_REQUEST['smids'])) {
            $smids = $_REQUEST['smids'];
        }else{
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        if (!preg_match('/^[\d,]+$/i', $smids)) {
            output_error('消息ID错误');
        }
        $smids = explode(',', $smids);

        $model_storemsgread = Model('store_msg_read');
        $model_storemsg = Model('store_msg');

        foreach ($smids as $val) {
            $condition = array();
            $condition['seller_id'] = $this->seller_info['seller_id'];
            $condition['sm_id'] = $val;

            $read_info = $model_storemsgread->getStoreMsgReadInfo($condition);

            if (empty($read_info)) {
                // 消息阅读表插入数据
                $model_storemsgread->addStoreMsgRead($condition);
            }

            // 更新店铺消息表
            $storemsg_info = $model_storemsg->getStoreMsgInfo(array('sm_id' => $val));
            $sm_readids = explode(',', $storemsg_info['sm_readids']);
            $sm_readids['seller_id'] = $this->seller_info['seller_id'];
            $sm_readids = array_unique($sm_readids);
            $update = array();
            $update['sm_readids'] = implode(',', $sm_readids) . ',';
            $model_storemsg->editStoreMsg(array('sm_id' => $val), $update);
        }

        output_data_msg('标记成功');
    }

    /**
     * 跑腿邦,删除消息
     */
    public function del_msgOp() {
        if (isset($_REQUEST['smids']) && !empty($_REQUEST['smids'])) {
            $smids = $_REQUEST['smids'];
        }else{
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }
        if (!preg_match('/^[\d,]+$/i', $smids)) {
            output_error('消息ID错误');
        }
        $smids = explode(',', $smids);

        $where = array();
        $where['store_id'] = $this->seller_info['store_id'];
        $where['sm_id'] = array('in', $smids);
        // 删除消息记录
        Model('store_msg')->delStoreMsg($where);
        // 删除阅读记录
        unset($where['store_id']);
        Model('store_msg_read')->delStoreMsgRead($where);
        output_data_msg('删除成功');
    }

}