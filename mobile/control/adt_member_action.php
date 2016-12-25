<?php
defined('emall') or exit('Access Invalid!');
/**
 * @author Administrator
 *
 */
class adt_member_actionControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @name 用户评论
     * @param post ,order_sn,accuser_phone,complain_phone
     * @author xuping
     */
    public function adt_member_complainOP(){
        $order_sn           =$_REQUEST['order_sn'];
        $accuser_phone      =$_REQUEST['accuser_phone'];
        $complain_contents  =$_REQUEST['complain_content'];
        if(empty($order_sn) || empty($accuser_phone) || empty($complain_contents)){
            output_error('参数不完整');
        }

        if (!preg_match('/^1\d{10}$/', $_REQUEST['accuser_phone'])){
            output_error('请正确填写手机号码', array(), ERROR_CODE_ARG);
        }

        $array=array('order_sn'=>$order_sn);
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($array);
        if(empty($order_info) || $order_info['order_type']!=3 || $order_info['buyer_id']!=$this->member_info['member_id'] || $order_info['order_state']!=40) {
            output_error('不允许投诉');
        }

        $condition['order_id']          =$order_info['order_id'];
        $condition['order_sn']          =$order_sn;
        $condition['accuser_id']        =$this->member_info['member_id'];
        $condition['accuser_name']      =$this->member_info['member_name'];
        $condition['accuser_phone']     =$accuser_phone;
        $condition['complain_content']  =$complain_contents;
        $condition['complain_datetime']     =time();
        $condition['complain_state']    =10;
        $condition['league_store_id']   =$order_info['league_store_id'];

        $result=Model('complain')->adt_saveComplain($condition);

        if($result){
            $model_order->editOrder(array('complain_state' => 1), array('order_id' => $order_info['order_id']));
            output_data_msg(array(),'投诉成功');
        }else{
            output_error('投诉失败');
        }
    }


}