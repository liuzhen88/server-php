<?php
/**
 * 买家退款
 * 
 * @authour lijunhua
 * @since   2015-08-12
 */
use Tpl;
defined('emall') or exit('Access Invalid!');

class member_refundControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 预售本土退款
     * @param [int] $[order_id] [<description>]
     * @return [type] [description]
     */
    public function changeMemberMoneyOp(){
        $model      =   Model('order');
        $order_info =   array();
        $order_id       =   (int)$_REQUEST['order_id'];
        if ($order_id<1) {
            output_error('参数错误');
        }
        $rt     =   $model->getOrderInfo(array('order_id'=>$order_id));
        if($rt['order_state'] != 20){
            output_error('未消费且付款成功的订单才能退款');
        }

        $order_info['order_id']       =   $order_id;
        $order_info['order_money']    =   $rt['order_amount'];
        $order_info['member_id']      =   $this->member_info['member_id'];
        $order_info['member_name']    =   $this->member_info['member_name'];
        $order_info['order_sn']       =   $rt['order_sn'];
        //新增用来极光消息推送使用 cyfei
        $order_info['store_id']       =   $rt['store_id'];
        $lrt = Logic('order')->changeLocalPrice($order_info);
        if($lrt)
            output_data('操作成功');
        else
            output_error('操作失败');
    }

    /**
     * 退款原因
     */
    public function reasonlistOp() {
        $model_refund = Model('refund_return');
        $condition    = array();
        $reason_list  = $model_refund->getReasonList($condition);//退款退货原因
      
        output_data(array_values($reason_list));
    }
       
    /**
     * 我要退款
     * 
     * @parma int    $param['refund_id']         退款ID （选填  不传为新增 传为编辑）
     * @param string $param['refund_amount']     退款金额
     * @param int    $param['order_id']          订单ID
     * @param int    $param['rec_id']            订单商品表编号(非商品ID)
     * @param int    $param['goods_num']         退货数量
     * @param int    $param['reason_id']         退货退款原因
     * @param string $param['refund_type']       1为退款,2为退货
     * @param string $param['buyer_message']     描述说明
     * @param string $_FILE['refund_pic1']       上传凭证图片1（选填）
     * @param string $_FILE['refund_pic1']       上传凭证图片1（选填）
     * @param string $_FILE['refund_pic1']       上传凭证图片1（选填）
     */
     public function add_refundOp() {
         
        $this->_valid_refund();
        $order_id = (int)$_REQUEST['order_id'];
        if(isset($_REQUEST['refund_type']) && $_REQUEST['refund_type'] == 1)
            $rec_id = 0;
        else
            $rec_id = (int)$_REQUEST['rec_id'];//订单商品表编号
     
       
        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];

        $condition['order_id'] = $order_id;
         /** @var refund_returnModel $model_refund */
         $model_refund = Model('refund_return');
        $order = $model_refund->getRightOrderList($condition, $rec_id);
        
        // 已经返利了,不能退款
        if ($order['is_rebate']) {
            output_error('已返利订单,不能退款退货');
        }
        
        // 已取消
        if ($order['order_state'] == 0) {
            output_error('当前订单只允许退款');
        }
        
        // 待付款 || 待发货
        if (in_array($order['order_state'], array(10,20)) && $_REQUEST['refund_type'] == 2) {
            output_error('当前订单只允许退款');
        }

        // 待收货
        if ($order['order_state'] >= 30 && $_REQUEST['refund_type'] == 1) {
            output_error('当前订单只允许退货');
        }


//        $order_amount        = $order['order_amount'];//订单金额
//        $order_refund_amount = $order['refund_amount'];//订单退款金额
        $goods_list          = $order['goods_list'];
        $goods = array();
        if(isset($_REQUEST['refund_type']) && $_REQUEST['refund_type'] == 1)
            $goods = $goods;
        else
            $goods               = $goods_list[0];
        //$goods_pay_price     = $goods['goods_pay_price'];//商品实际成交价
        
         
        $refund_array = array();
        if($rec_id>0)
            $refund_amount = floatval($_REQUEST['refund_amount']); //退款金额
        else
            $refund_amount = $order['order_amount'];
        //if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
        //    $refund_amount = $goods_pay_price;
        //}
        
        $goods_num = intval($_REQUEST['goods_num']); //退货数量
        //if (($goods_num < 0) || ($goods_num > $goods['goods_num'])) {
        //    $goods_num = 1;
       // }
        
        $reason_id                      = intval($_REQUEST['reason_id']); //退货退款原因
        $refund_array['reason_id']      = $reason_id;
        $reason_array                   = array();
        $reason_array['reason_info']    = '其他';
        $refund_array['reason_info']    = '其他';
    
        $reason_detail = Model()->table('refund_reason')->where(array('reason_id' => $reason_id))->find();
        if (!empty($reason_detail)) {
            $reason_array['reason_info'] = $reason_detail['reason_info'];
            $refund_array['reason_info'] = $reason_detail['reason_info'];
        }

        $pic_array = array();
        $pic_array['buyer'] = $this->upload_pic(); //上传凭证
        $info = serialize($pic_array);
        $refund_array['pic_info'] = $info;

         /** @var tradeModel $model_trade */
         $model_trade = Model('trade');
        $order_shipped = $model_trade->getOrderState('order_shipped'); //订单状态30:已发货
        if ($order['order_state'] == $order_shipped) {
            $refund_array['order_lock'] = '2'; //锁定类型:1为不用锁定,2为需要锁定
        }
        
        $refund_array['refund_type'] = $_REQUEST['refund_type']; //类型:1为退款,2为退货
        $refund_array['return_type'] = '2'; //退货类型:1为不用退货,2为需要退货
        
        
        if ($refund_array['refund_type'] != '2') {
            $refund_array['refund_type'] = '1';
            $refund_array['return_type'] = '1';
        }
        
        $refund_array['seller_state']    = '1'; //状态:1为待审核,2为同意,3为不同意
        $refund_array['refund_amount']   = ncPriceFormat($refund_amount);
        $refund_array['goods_num']       = $goods_num;
        $refund_array['buyer_message']   = $_REQUEST['buyer_message'];
        $refund_array['add_time']        = time();
        $refund_info = $model_refund->getRefundReturnInfo(array('order_id' => $order_id));

         if (empty($refund_info)) {
             // 新增
             $refund_id = $model_refund->addRefundReturn($refund_array, $order, $goods);
             $model_refund->editOrderLock($order_id);

        }  else {
             if ($refund_info['refund_state'] == 2) {
                 output_error('商家处理中,禁止修改');
             }

             if ($refund_info['refund_state'] == 3) {
                 output_error('申请已完成,禁止修改');
             }
             $refund_id = $refund_info['refund_id'];
             // 编辑
             $model_refund->editRefundReturn2($refund_id, $refund_array, $order, $goods);

        }
         output_data(array('refund_id' => $refund_id));

     }
      
    
    

    /**
     * 退款记录列表页
     * 
     * @param string $param['key'] 唯一校验码
     */
    public function indexOp(){
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];
        $refund_list = $model_refund->getRefundReturnList($condition, $this->page);
        foreach ($refund_list as $key=>$row)
        {
            $refund = $row;
            $pic_info = mb_unserialize($row['pic_info']);
            $pic_info = isset($pic_info['buyer']) ? $pic_info['buyer'] : array();
            if (!empty($pic_info))
            {
                foreach ($pic_info as $key_1=>$row_1)
                {
                    $pic_info[$key_1] = otherThumb($row_1, ATTACH_PATH.DS.'refund');
                }
            }
            $refund['pic_info'] = array_values($pic_info);
            $refund['goods_image'] = cthumb($row['goods_image'], 480, $row['store_id']);
            $refund_list[$key] = $refund;
        }
        $page_count = $model_refund->gettotalpage();
        output_data(array('refund_list' => $refund_list), mobile_page($page_count));
    }
    
    /**
     * 退款记录查看
     * 
     * @param string $param['key']     唯一校验码
     * @param int $param['refund_id']  记录ID
     */
    public function detailOp(){
        if (!isset($_REQUEST['refund_id']) || empty($_REQUEST['refund_id'])) {
            output_error('参数错误', array(), ERROR_CODE_ARG);
        }
        
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];

        $condition['refund_id'] = (int)$_REQUEST['refund_id'];
        $refund_list = $model_refund->getRefundReturnList($condition); //不区分退货,取款
        
        if (empty($refund_list)) {
             output_error('未找到相关信息');
        }
        $reason_detail = Model()->table('refund_reason')->where(array('reason_id' =>$refund_list[0]['reason_id'] ))->find();
        $refund_list[0]['reason_info'] = $reason_detail['reason_info'];
        $refund_list[0]['isrefund']=1;
        $refund = $refund_list[0];
        //---solon.ring2011@gmail.com
        $exp = Model()->table('express')->where(array('id'=>$refund['express_id']))->find();
        $refund['express_name'] = $exp['e_name'];
        //----
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        $pic_list = $info['buyer'];
        
        
        $condition = array();
        $condition['order_id'] = $refund['order_id'];
        $order = $model_refund->getRightOrderList($condition, $refund['order_goods_id']);
        
        $result = array(
            'refund_info' => $refund,
            'order_info'  => $order,
            'pic_list'    => $pic_list
        );
        output_data($result);
    }
        
        
    /**
     * 退款退货订单详情
     * 
     * @param string $param['key']	     唯一校验码
     * @param string $param['order_id']     订单ID
     * 
     * 
     * 返回说明：    
     *           extend_order_goods.refund 为1 可以申请退款,前端出申请退款控件
     *           extend_order_goods.refund 为0 不能申请退款,前端不出申请退款控件
     *           extend_order_goods.extend_refund.refund_code 为 1 退款中
     *           extend_order_goods.extend_refund.refund_code 为 2 退款成功
     *           extend_order_goods.extend_refund.refund_code 为 3 退款失败
     *           extend_order_goods.extend_refund.refund_time  退款成功|失败时间 会有
     */
    public function order_detailOp(){
        if (!isset($_REQUEST['order_id']) || empty($_REQUEST['order_id'])) {
            output_error('参数错误', array(), ERROR_CODE_ARG);
        }
        
        $order_id                  = (int)$_REQUEST['order_id'];
        $condition['order_id']     = $order_id;
        $condition['buyer_id']     = $this->member_info['member_id'];
        //$condition['order_state']  = array('gt', ORDER_STATE_NEW);
        
        $model_order = Model('order');
        $order_info = $model_order->getOrderInfo($condition, array('order_common','order_goods'));
        if (empty($order_info)) {
            output_error('订单信息不存在');
        }
        if (isset($order_info['extend_order_goods']))
        {
            foreach ($order_info['extend_order_goods'] as $key=>$row)
            {
                $order_info['extend_order_goods'][$key]['goods_image'] = cthumb($row['goods_image'], 480, $row['store_id']);
            }
        }
        
        
        $order_list_array[$order_id] = $order_info;
        $order_list_array = Model('refund_return')->getGoodsRefundList($order_list_array);
        
        output_data($order_list_array[$order_id]); 
        
    }
    
    /**
     * 上传凭证
     *
     */
	 private function upload_pic() {
        $refund_pic = array();
        $refund_pic[1] = 'refund_pic1';
        $refund_pic[2] = 'refund_pic2';
        $refund_pic[3] = 'refund_pic3';
        $pic_array = array();
        $upload = new UploadFile();
        $dir = ATTACH_PATH . DS . 'refund' . DS;
        //$upload->set('default_dir', $dir);
        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
        $upload->set('new_ext','jpg');
        $upload->set('default_dir',$dir.$upload->getSysSetPath());
        $count = 1;
        foreach ($refund_pic as $pic) {
            if (!empty($_POST[$pic])) {
                $tmp_name = sprintf('%010d',time() - 946656000)
                        . sprintf('%03d', microtime() * 1000)
                        . sprintf('%04d', mt_rand(0,9999));
                $upload->set('file_name',$tmp_name.'.jpg');        
                $result = $upload->upBase64Image($_POST[$pic]);
                if (!$result) {
                   continue;
                } 
                $pic_array[$count] = $upload->getSysSetPath().$upload->file_name;
            }

            $count++;
        }
       return $pic_array;
    }

    /**
     * 物流公司列表
     * 
     * @param string $param['key']        唯一校验码
     */
    public function express_listOp ()
    {
        $express_list  = rkcache('express',true);
        output_data($express_list);
    }
    
    /**
     * 退货商品-会员发货
     * 
     * @param string $param['key']        唯一校验码
     * @param int    $param['refund_id']  退货申请ID
     * @param int    $param['express_id'] 物流ID
     * @param string $param['invoice_no'] 物流单号
     */
    public function refund_shipOp() {
        
        $param_arr = array('refund_id', 'express_id', 'invoice_no');
        foreach ($param_arr as $param) {
             if (!isset($_REQUEST[$param]) || empty($_REQUEST[$param])) {
                output_error('参数缺省', array(), ERROR_CODE_ARG);
            }
        }
        
        
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['refund_id'] = (int)$_REQUEST['refund_id'];
        $refund_detail = $model_refund->getRefundReturnInfo($condition);
        if (empty($refund_detail)) {
            output_error('信息不存在或已被处理');
        }
        
        if ($refund_detail['goods_state'] == 2) {
            output_error('已发货，请勿重复提交');
        }
        
        $refund_array = array();
        $refund_array['ship_time'] = time();
        $refund_array['delay_time'] = time();
        $refund_array['express_id'] = $_REQUEST['express_id'];
        $refund_array['invoice_no'] = $_REQUEST['invoice_no'];
        $refund_array['goods_state'] = '2';
        
        $state = $model_refund->editRefundReturn($condition, $refund_array);
        if (!$state) {
            output_error('操作失败');
        }
        output_data('操作成功');
    }

    /**
     * 延迟收货时间
     * 
     * @param string $param['key']        唯一校验码
     * @param int    $param['refund_id']  退货申请ID
     */
    public function refund_delayOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['buyer_id']    = $this->member_info['member_id'];
        $condition['refund_id']   = (int)$_REQUEST['refund_id'];

        $refund_detail = $model_refund->getRefundReturnInfo($condition);
        if (empty($refund_detail)) {
            output_error('信息不存在或已被处理');
        }

        if ($refund_detail['return_type'] != 2 ) { //1 退款 2 退货
            output_error('退货申请类型错误');
        }
        
        if ($refund_detail['seller_state'] != '2') {
           output_error('卖家同意才能延迟收货时间');
        }
        
        if ($refund_detail['goods_state'] != '3') {// 未收到状态
           output_error('没收到退货,才能被延迟收货时间');
        }
        
        $refund_array = array();
        $refund_array['delay_time']  = time();
        $refund_array['goods_state'] = '2'; 
        $state = $model_refund->editRefundReturn($condition, $refund_array);
        if (!$state) {
            output_error('操作失败');
        }
        output_data('操作成功');
    }
    

    private function _valid_refund() {
        if (!isset($_REQUEST['order_id']) || empty($_REQUEST['order_id'])) {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }

        if (!isset($_REQUEST['reason_id']) || empty($_REQUEST['reason_id'])) {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }

        if (!isset($_REQUEST['rec_id']) || empty($_REQUEST['rec_id'])) {
            output_error('参数缺省', array(), ERROR_CODE_ARG);
        }

        if (!isset($_REQUEST['refund_amount']) || floatval($_REQUEST['refund_amount']) <= 0) {
            output_error('退款金额必须大于0', array(), ERROR_CODE_ARG);
        }
    }

}
