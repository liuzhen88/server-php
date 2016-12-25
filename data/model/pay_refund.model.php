<?php
/**
 * 跑腿邦，退款订单管理
 *
 */
defined('emall') or exit('Access Invalid!');
class pay_refundModel extends Model {
    /**
     * 插入退款订单记录
     * @param array $data
     * @param string $refund_state
     * @param string $payment_code
     * @return int 返回 insert_id
     */
    public function addPayRefund($order_info,$refund_state,$payment_code ='wxpay') {
        $data = array();
        $data['pay_sn'] = $order_info['pay_sn'];
        $result = $this->getPayRefund($data);
        if($result){
            return 'already';
        }
        $data['order_id'] = $order_info['order_id'];
        $data['order_sn'] = $order_info['order_sn'];
        $data['payment_code'] = $payment_code;
        $data['order_amount'] = $order_info['order_amount'];
        $data['refund_amount'] = $order_info['order_amount'];
        $data['refund_state'] = $refund_state;
        //$data['refund_num'] = $refund_num;  //退款尝试次数
        $data['add_time'] = TIMESTAMP;
        if($refund_state==1){
            $data['success_time'] = TIMESTAMP;
        }
        return $this->table('pay_refund')->insert($data);
    }

    /**
     * 取得退款订单记录
     * @param unknown $condition
     * @param string $fields
     * @param string $order
     */
    public function getPayRefund($condition = array(), $fields = '*', $order = '') {
        return $this->table('pay_refund')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取退款记录
     *
     * @param
     * @return array
     */
    public function getPayRefundList($condition = array(), $page = '', $fields = '*', $limit = '') {
        $result = $this->table('pay_refund')->field($fields)->where($condition)->page($page)->limit($limit)->order('id desc')->select();
        return $result;
    }

    /**
     * 跑腿邦，修改退款记录
     *
     * @param
     * @return bool
     */
    public function editPayRefundReturn($condition, $data) {
        if (empty($condition)) {
            return false;
        }
        if (is_array($data)) {
            $result = $this->table('pay_refund')->where($condition)->update($data);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 跑腿邦，确认退款处理
     *
     * @param
     * @return bool
     */
    public function editPayOrderRefund($refund) {
        $order_id = intval($refund['order_id']);
        if ($order_id > 0) {
            Language::read('model_lang_index');
            $field = 'order_id,pay_sn,order_sn,order_amount,payment_code,order_state';
            $model_order = Model('order');
            $order_info = $model_order->getOrderInfo(array('order_id' => $order_id), array(), $field);
            $result = Logic('refund')->order_refund($order_info['pay_sn'],intval(bcmul($order_info['order_amount'],100,2)));

            if($result['code']){
                //退款成功
                return true;
            }else{
                //退款失败
                return false;
            }
        }
        return false;
    }

}
