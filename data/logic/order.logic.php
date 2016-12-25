<?php
/**
 * 实物订单行为
 */
defined('emall') or exit('Access Invalid!');
class orderLogic {

    /**
     * 跑腿邦,确认订单
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @return array
     */
    public function adt_changeOrderStateReceive($order_info,$role,$user = '', $msg = '') {
        $model_order = Model('order');
        try {
            $order_id = $order_info['order_id'];
            $model_order->beginTransaction();

            //更新订单状态
            $update_order = array();
            $update_order['finnshed_time'] = TIMESTAMP;
            $update_order['order_state'] = ORDER_STATE_SUCCESS;
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = intval($order_id);
            $data['log_role'] = $role;
            $data['log_msg'] = '订单完成';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_SUCCESS;
            $model_order->addOrderLog($data);

            //设置返利
            $this->setRebate($order_info);

            $model_order->commit();
            return callback(true,'操作成功');
        } catch (Exception $e) {
            $model_order->rollback();
            return callback(false,'操作失败');
        }
    }

    /**
     * 跑腿邦,取消订单
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @param boolean $if_update_account 是否变更账户金额
     * @param boolean $if_queue 是否使用队列
     * @return array
     */
    public function adt_changeOrderStateCancel($order_info, $role, $user = '', $msg = '', $if_update_account = true, $if_quque = true) {
        //支付宝取消订单记录 支付宝需要在回调通知里处理
        if($order_info['payment_code']=='alipay' && !isset($order_info['pay_notify'])){
            $result = Model('pay_refund')->addPayRefund($order_info,0);
            if(!$result){
                //退款订单记录插入失败处理
                return callback(true, '操作失败');

            }
            return callback(true, '操作成功');
        }

        try {
            $model_order = Model('order');
            $model_order->beginTransaction();
            $order_id = $order_info['order_id'];

            //库存销量变更
            $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
            $data = array();
            foreach ($goods_list as $goods) {
                $goods_key = $goods['league_store_id'] . '_' . $goods['goods_id'];
                $data[$goods_key] = array(
                    'quantity' => $goods['goods_num'],
                    'is_promotion' => $goods['is_promotion'],
                );
            }
            if ($if_quque) {
                QueueClient::push('adt_cancelOrderUpdateStorage', $data);
            } else {
                Logic('queue')->adt_cancelOrderUpdateStorage($data);
            }

            //变更预存款,开启微信退款这边必须关闭
/*            if ($if_update_account) {
                $model_pd = Model('predeposit');

                $order_amount = floatval($order_info['order_amount']);
                if ($order_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $order_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('refund',$data_pd);
                }
            }*/

            $pay_refund = Model('pay_refund');

            //微信退款
            if ($order_info['payment_code']=='wxpay') {

                $result = Logic('refund')->order_refund($order_info['pay_sn'],intval(bcmul($order_info['order_amount'],100,2)));


                if($result['code']){
                    //退款成功
//                    $msg .= $result['msg'];
                    $result = $pay_refund->addPayRefund($order_info,1);
                }else{
                    //退款失败
                    $result = $pay_refund->addPayRefund($order_info,0);
                }

                if(!$result){
                    //退款订单记录插入失败处理
                    throw new Exception('退款信息提交失败');
                }
            }


            $order_info_new = $model_order->lock(true)->getOrderInfo(array('order_id'=>$order_info['order_id']));

            if($order_info_new['order_state']==20) {
                //更新订单信息
                $update_order = array('order_state' => ORDER_STATE_CANCEL, 'pd_amount' => 0);
                $update = $model_order->editOrder($update_order, array('order_id' => $order_id));
                if (!$update) {
                    throw new Exception('保存失败');
                }

                //添加订单日志
                $data = array();
                $data['order_id'] = $order_id;
                $data['log_role'] = $role;
                $data['log_msg'] = '取消了订单';
                $data['log_user'] = $user;
                if ($msg) {
                    $data['log_msg'] .= ' ( ' . $msg . ' )';
                }
                $data['log_orderstate'] = ORDER_STATE_CANCEL;
                $model_order->addOrderLog($data);
                $model_order->commit();

                return callback(true, '操作成功');


            }else{
                throw new Exception('已经取消');
            }

        } catch (Exception $e) {
            $model_order->rollback();
            return callback(false,$e->getMessage());
        }
    }

    /**
     * 取消订单 cyfei 修改增加本土和配送订单情况
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @param boolean $if_update_account 是否变更账户金额
     * @param boolean $if_queue 是否使用队列
     * @return array
     */
    public function changeOrderStateCancel($order_info, $role, $user = '', $msg = '', $if_update_account = true, $if_quque = true) {
        try {
            $model_order = Model('order');
            $model_order->beginTransaction();
            $order_id = $order_info['order_id'];
            $order_info = Model()->table('order')->where(array('order_id' => $order_id))->master(true)->find();
            if (empty($order_info)) throw new Exception('订单不存在');
            //库存销量变更
            $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
            $data = array();
            foreach ($goods_list as $goods) {
                if ($order_info['order_type'] == 3)
                {
                    $goods_key = $goods['league_store_id'] . '_' . $goods['goods_id'];
                    $data[$goods_key] = array(
                        'quantity' => $goods['goods_num'],
                        'is_promotion' => $goods['is_promotion'],
                    );
                }
                else 
                    $data[$goods['goods_id']] = $goods['goods_num'];
            }
            if ($order_info['order_type'] == 2)
            {
                if ($if_quque) {
                    QueueClient::push('cancelOrderUpdateStorage', $data);
                } else {
                    Logic('queue')->cancelOrderUpdateStorage($data);
                }
            }
            elseif ($order_info['order_type'] == 1){
                if ($if_quque) {
                    QueueClient::push('localcancelOrderUpdateStorage', $data);
                } else {
                    Logic('queue')->localcancelOrderUpdateStorage($data);
                }
            }
            elseif ($order_info['order_type'] == 3){
                if ($if_quque) {
                    QueueClient::push('adt_cancelOrderUpdateStorage', $data);
                } else {
                    Logic('queue')->adt_cancelOrderUpdateStorage($data);
                }
            }
            else 
            {
                throw new Exception('订单类型错误');
            }
            

            if ($if_update_account) {
                $model_pd = Model('predeposit');
                //解冻充值卡
                $rcb_amount = floatval($order_info['rcb_amount']);
                if ($rcb_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $rcb_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changeRcb('order_cancel',$data_pd);
                }
                
                //解冻预存款
                $pd_amount = floatval($order_info['pd_amount']);
                if ($pd_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $pd_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('order_cancel',$data_pd);
                }                
            }

            //更新订单信息
            $update_order = array('order_state' => ORDER_STATE_CANCEL, 'pd_amount' => 0);
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_msg'] = '取消了订单';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_CANCEL;
            $model_order->addOrderLog($data);
            $model_order->commit();

            return callback(true,'操作成功');

        } catch (Exception $e) {
            $this->rollback();
            return callback(false,'操作失败');
        }
    }
    
    /**
     * 删除订单
     * 
     * @author lijunhua
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @param boolean $if_update_account 是否变更账户金额
     * @param boolean $if_queue 是否使用队列
     * @return array
     */
    public function changeOrderStateDelete($order_info, $role, $user = '', $msg = '', $if_update_account = true, $if_quque = true) {
        try {
            $model_order = Model('order');
            $model_order->beginTransaction();
            $order_id = $order_info['order_id'];

            $data = array();
            
            $update_order = array();
            $update_order['delete_state'] = ORDER_DEL_STATE_DELETE;
            
            // 待支付状态 库存销量变更
            if ($order_info['order_state'] == ORDER_STATE_NEW) {
               $goods_list = $model_order->getOrderGoodsList(array('order_id'=>$order_id));
         
                foreach ($goods_list as $goods) {
                    $data[$goods['goods_id']] = $goods['goods_num'];
                }
                if ($if_quque) {
                    QueueClient::push('cancelOrderUpdateStorage', $data);
                } else {
                    Logic('queue')->cancelOrderUpdateStorage($data);
                }
                
                if ($if_update_account) {
                    $model_pd = Model('predeposit');
                    //解冻充值卡
                    $rcb_amount = floatval($order_info['rcb_amount']);
                    if ($rcb_amount > 0) {
                        $data_pd = array();
                        $data_pd['member_id'] = $order_info['buyer_id'];
                        $data_pd['member_name'] = $order_info['buyer_name'];
                        $data_pd['amount'] = $rcb_amount;
                        $data_pd['order_sn'] = $order_info['order_sn'];
                        $model_pd->changeRcb('order_cancel',$data_pd);
                    }

                    //解冻预存款
                    $pd_amount = floatval($order_info['pd_amount']);
                    if ($pd_amount > 0) {
                        $data_pd = array();
                        $data_pd['member_id'] = $order_info['buyer_id'];
                        $data_pd['member_name'] = $order_info['buyer_name'];
                        $data_pd['amount'] = $pd_amount;
                        $data_pd['order_sn'] = $order_info['order_sn'];
                        $model_pd->changePd('order_cancel',$data_pd);
                    }                
                }
                
                // 更新订单信息 (删除订单，若订单未支付，同时也取消)
                $update_order['order_state'] = ORDER_STATE_CANCEL;
                $update_order['pd_amount'] = 0;      
            } 


            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_msg'] = '删除了订单';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = $order_info['order_state'];
            $model_order->addOrderLog($data);
            $model_order->commit();

            return callback(true,'操作成功');

        } catch (Exception $e) {
            $this->rollback();
            return callback(false,'操作失败');
        }
    }
    

    /**
     * 收货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param string $msg 操作备注
     * @return array
     */
    public function changeOrderStateReceive($order_info, $role, $user = '', $msg = '') {
        try {
            $order_id = $order_info['order_id'];
            $model_order = Model('order');
            //change chenyifei
            $model_order->beginTransaction();

            //更新订单状态
            $update_order = array();
            $update_order['finnshed_time'] = TIMESTAMP;
            $update_order['order_state'] = ORDER_STATE_SUCCESS;
            $update = $model_order->editOrder($update_order,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }

            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = 'buyer';
            $data['log_msg'] = '签收了货物';
            $data['log_user'] = $user;
            if ($msg) {
                $data['log_msg'] .= ' ( '.$msg.' )';
            }
            $data['log_orderstate'] = ORDER_STATE_SUCCESS;
            $model_order->addOrderLog($data);

            //添加会员积分
            if (C('points_isuse') == 1){
                Model('points')->savePointsLog('order',array('pl_memberid'=>$order_info['buyer_id'],'pl_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
            }
            //添加会员经验值
            Model('exppoints')->saveExppointsLog('order',array('exp_memberid'=>$order_info['buyer_id'],'exp_membername'=>$order_info['buyer_name'],'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
            //change chenyifei
            //$this->setRebate($order_info);
            $model_order->commit();
            return callback(true,'操作成功');
        } catch (Exception $e) {
            //change chenyifei           
            $model_order->rollback();
            return callback(false,'操作失败');
        }
    }

	 /**
     * 更改订单价格
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param float $price 订单价格
     * @return array
     */
    public function changeOrdergoodsPrice($order_info, $role, $user = '', $price) {
        try {

            $order_id = $order_info['order_id'];
            $model_order = Model('order');

            $data = array();
            $data['goods_amount'] = abs(floatval($price));
            $data['order_amount'] = array('exp','shipping_fee+'.$data['goods_amount']);
            $update = $model_order->editOrder($data,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }
            //记录订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_user'] = $user;
            $data['log_msg'] = '修改了商品价格'.'( '.$price.' )';;
            $data['log_orderstate'] = $order_info['payment_code'] == 'offline' ? ORDER_STATE_PAY : ORDER_STATE_NEW;
            $model_order->addOrderLog($data);
            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }

    /**
     * 更改运费
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @param float $price 运费
     * @return array
     */
    public function changeOrderShipPrice($order_info, $role, $user = '', $price) {
        try {

            $order_id = $order_info['order_id'];
            $model_order = Model('order');

            $data = array();
            $data['shipping_fee'] = abs(floatval($price));
            $data['order_amount'] = array('exp','goods_amount+'.$data['shipping_fee']);
            $update = $model_order->editOrder($data,array('order_id'=>$order_id));
            if (!$update) {
                throw new Exception('保存失败');
            }
            //记录订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_user'] = $user;
            $data['log_msg'] = '修改了运费'.'( '.$price.' )';;
            $data['log_orderstate'] = $order_info['payment_code'] == 'offline' ? ORDER_STATE_PAY : ORDER_STATE_NEW;
            $model_order->addOrderLog($data);
            return callback(true,'操作成功');
        } catch (Exception $e) {
            return callback(false,'操作失败');
        }
    }

    /**
     * 回收站操作（放入回收站、还原、永久删除）
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $state_type 操作类型
     * @return array
     */
    public function changeOrderStateRecycle($order_info, $role, $state_type) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
        //更新订单删除状态
        $state = str_replace(array('delete','drop','restore'), array(ORDER_DEL_STATE_DELETE,ORDER_DEL_STATE_DROP,ORDER_DEL_STATE_DEFAULT), $state_type);
        $update = $model_order->editOrder(array('delete_state'=>$state),array('order_id'=>$order_id));
        if (!$update) {
            return callback(false,'操作失败');
        } else {
            return callback(true,'操作成功');
        }
    }

    /**
     * 发货
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @return array
     */
    public function changeOrderSend($order_info, $role, $user = '', $post = array()) {
        $order_id = $order_info['order_id'];
        $model_order = Model('order');
		try {
            $model_order->beginTransaction();
            $data = array();
            $data['reciver_name'] = $post['reciver_name'];
            $data['reciver_info'] = $post['reciver_info'];
            $data['deliver_explain'] = $post['deliver_explain'];
            $data['daddress_id'] = intval($post['daddress_id']);
            $data['shipping_express_id'] = intval($post['shipping_express_id']);
            $data['shipping_time'] = TIMESTAMP;

            $condition = array();
            $condition['order_id'] = $order_id;
            $condition['store_id'] = $_SESSION['store_id'];
            $update = $model_order->editOrderCommon($data,$condition);
            if (!$update) {
                throw new Exception('操作失败');
            }

            $data = array();
            $data['shipping_code']  = $post['shipping_code'];
            $data['order_state'] = ORDER_STATE_SEND;
            $data['delay_time'] = TIMESTAMP;
            $update = $model_order->editOrder($data,$condition);
            if (!$update) {
                throw new Exception('操作失败');
            }
            //物流订单订阅
            if(!empty($post['shipping_express_id'])&&!empty($data['shipping_code'])){
                $where['id'] = intval($post['shipping_express_id']);
                $deliver_info = Model()->table('express')->where($where)->find();
                $this->deliverCommit($deliver_info['e_code'],$data['shipping_code']);
                //写入物流信息到数据库
            }
            $model_order->commit();
		} catch (Exception $e) {
		    $model_order->rollback();
		    return callback(false,$e->getMessage());
		}
		//更新表发货信息
		if ($post['shipping_express_id'] && $order_info['extend_order_common']['reciver_info']['dlyp']) {
		    $data = array();
		    $data['shipping_code'] = $post['shipping_code'];
		    $data['order_sn'] = $order_info['order_sn'];
		    $express_info = Model('express')->getExpressInfo(intval($post['shipping_express_id']));
		    $data['express_code'] = $express_info['e_code'];
		    $data['express_name'] = $express_info['e_name'];
		    Model('delivery_order')->editDeliveryOrder($data,array('order_id' => $order_info['order_id']));
		}
		//添加订单日志
		$data = array();
		$data['order_id'] = intval($_GET['order_id']);
		$data['log_role'] = 'seller';
		$data['log_user'] = $_SESSION['member_name'];
		$data['log_msg'] = '发出了货物 ( 编辑了发货信息 )';
		$data['log_orderstate'] = ORDER_STATE_SEND;
		$model_order->addOrderLog($data);

		// 发送买家消息
        $param = array();
        $param['code'] = 'order_deliver_success';
        $param['member_id'] = $order_info['buyer_id'];
        $param['param'] = array(
            'order_sn' => $order_info['order_sn'],
            'order_url' => urlShop('member_order', 'show_order', array('order_id' => $order_id)),
            'message_title' => '商品已发货',
        );
        QueueClient::push('sendMemberMsg', $param);

        return callback(true,'操作成功');
    }

    /**
     * 收到货款
     * @param array $order_info
     * @param string $role 操作角色 buyer、seller、admin、system 分别代表买家、商家、管理员、系统
     * @param string $user 操作人
     * @return array
     */
    public function changeOrderReceivePay($order_list, $role, $user = '', $post = array()) {
        $model_order = Model('order');

        try {
            $model_order->beginTransaction();
            
            $buy_1 = Logic('buy_1');
            $order_list_online = array();   //线上订单
           

            $model_pd = Model('predeposit');
            $pay_sn = '';
            foreach($order_list as $key => $order_info) {
                $pay_sn = $order_info['pay_sn'];
                break;
            }
            //订单状态在事物中查主表，避免垃圾数据出错，查询可能有冗余，后期优化
            $order_list =  $model_order->getOrderList(array('pay_sn'=>$pay_sn,'order_state'=>ORDER_STATE_NEW),  '',  '*', 'order_id desc', '',  array(), true);
            foreach($order_list as $key => $order_info) {
                $order_id = $order_info['order_id'];
                if ($order_info['order_state'] != ORDER_STATE_NEW) continue;
                //下单，支付被冻结的充值卡
                $rcb_amount = floatval($order_info['rcb_amount']);
                if ($rcb_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $rcb_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changeRcb('order_comb_pay',$data_pd);
                }

                //下单，支付被冻结的预存款
                $pd_amount = floatval($order_info['pd_amount']);
                if ($pd_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $pd_amount;
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('order_comb_pay',$data_pd);
                }
                $order_list_online[] = $order_info;
                $pay_sn = $order_info['pay_sn'];
            }

            //更新订单状态
            if (!empty($order_list_online))
            {
                $update_order = array();
                $update_order['order_state'] = ORDER_STATE_PAY;
                $update_order['payment_time'] = ($post['payment_time'] ? strtotime($post['payment_time']) : TIMESTAMP);
                $update_order['payment_code'] = $post['payment_code'];
                //本土订单新增消费码
                if ($order_list_online[0]['order_type'] == 1)
                {
                    if ($order_list_online[0]['o2o_order_type'] == 1)
                    {
                        $update_order['order_state'] = ORDER_STATE_SUCCESS;
                        $update_order['payment_time'] = ($post['payment_time'] ? strtotime($post['payment_time']) : TIMESTAMP);
                        $update_order['finnshed_time'] = ($post['payment_time'] ? strtotime($post['payment_time']) : TIMESTAMP);

                    }
                    else 
                    {
                        $update_order['o2o_order_type'] = 2;
                        $update_order['consume_code'] = $buy_1->makeConsumeCode($order_list_online[0]['order_id']);
                    }
                    
                }
                //跑腿邦确认码
                elseif ($order_list_online[0]['order_type'] == 3)
                {
                    $update_order['consume_code'] = mt_rand(1000,9999);
                    $update_order['hope_time'] = array('exp','(payment_time-add_time)*is_get_quickly+hope_time');   //及时达的订单需要更新hope_time为支付成功后1小时，指定其他收获时间的订单不需要hope_time   //is_get_quickly 0：指定时间，1：及时达  //$update_order['payment_time'] + $update_order['hope_time'] - $update_order['add_time'];
                }
                
                $update = $model_order->editOrder($update_order,array('pay_sn'=>$pay_sn,'order_state'=>ORDER_STATE_NEW));
                if (!$update) {
                    throw new Exception('操作失败');
                }
                if ($order_list_online[0]['order_type'] == 1 && $order_list_online[0]['o2o_order_type'] == 1){
                    foreach ($order_list_online as $key_local=>$row_local){
                        $this->setRebate($row_local);
                    }
                }
            }

            if (!empty($pay_sn))
            {
                $data = array();
                $data['api_pay_state'] = 1;
                $data['api_trade_no'] = $post['trade_no'];
                $update = $model_order->editOrderPay($data,array('pay_sn'=>$pay_sn));
                if (!$update) {
                    throw new Exception('更新支付单状态失败');
                }
            }
           
            $model_order->commit();
        } catch (Exception $e) {
            $model_order->rollback();
            return callback(false,$e->getMessage());
        }

        foreach($order_list as $order_info) {
            
            $order_id = $order_info['order_id'];
            //变更销量
            if(!empty($order_info['goods_type']) && $order_info['goods_type']==1) {
                //订单有商品
                $order_goods = Model()->table('order,order_goods')->join('inner')->on('order.order_id=order_goods.order_id')->field('order_goods.*')->where(array('order_id'=>$order_info['order_id']))->find();
                if(!empty($order_goods['goods_id'])) {
                    QueueClient::push('localcreateOrderUpdateStorage', array($order_goods['goods_id']=>$order_goods['goods_num']));
                }
            }
            // 支付成功发送买家消息
            $param = array();
            $param['code'] = 'order_payment_success';
            $param['member_id'] = $order_info['buyer_id'];
            $param['param'] = array(
                'order_sn' => $order_info['order_sn'],
                'order_url' => urlShop('member_order', 'show_order', array('order_id' => $order_info['order_id'])),
                'message_title' => '付款成功',
            );
            QueueClient::push('sendMemberMsg', $param);
            //本土店铺订单支付成功发送消息给店铺
            if ($order_info['order_type'] == 1){
                //面对面付情况
                if ($order_info['o2o_order_type'] == 1){
                    QueueClient::push('storeJpush', array(
                        'message' => "订单：{$order_info['order_sn']}，已完成",
                        'store_id' => $order_info['store_id'],
                        'extend' => array(
                            'extras' => array(
                                'data' => array(
                                    'message_type' => 'O2O_STORE_ORDER_SUCCESS',
                                    'message_data' => array(
                                        'order_id' => $order_id,
                                        'order_sn'=>$order_info['order_sn']
                                    )
                                )
                            )
                        )
                    ));
                }
                elseif($order_info['o2o_order_type'] == 2){
                    //预售情况
                    QueueClient::push('storeJpush', array(
                        'message' => "您有一个新订单",
                        'store_id' => $order_info['store_id'],
                        'extend' => array(
                            'extras' => array(
                                'data' => array(
                                    'message_type' => 'O2O_STORE_ORDER_PAYMENT_SUCCESS',
                                    'message_data' => array(
                                        'order_id' => $order_id,
                                        'order_sn'=>$order_info['order_sn']
                                    )
                                )
                            )
                        )
                    ));
                }
            }
            
            
            // 支付成功发送店铺消息
            $param = array();
            $param['code'] = 'new_order';
            //跑腿邦订单区分
            $param['store_id'] = ($order_info['order_type'] == 3) ? $order_info['league_store_id']  : $order_info['store_id'];
            $param['param'] = array(
                'order_sn' => $order_info['order_sn']
            );
            QueueClient::push('sendStoreMsg', $param);
            //商户端极光消息推送
            if ($order_info['order_type'] == 3)
            {
                QueueClient::push('adtStoreJpush', array(
                    'message' => '您有一个订单,请准时配送',
                    'store_id' => $order_info['league_store_id'],
                    'extend' => array(
                        'app_type' => 'PTB_STORE',
                        'extras' => array(
                            'data' => array(
                                'message_type' => 'STORE_NEW_ORDER',
                                'message_data' => array(
                                    'order_id' => $order_id
                                )
                            )
                        )
                    )
                ));
            }
            
            //添加订单日志
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = $role;
            $data['log_user'] = $user;
            $data['log_msg'] = '收到了货款 ( 支付平台交易号 : '.$post['trade_no'].' )';
            $data['log_orderstate'] = ORDER_STATE_PAY;
            $model_order->addOrderLog($data);
                       
        }

        return callback(true,'操作成功');
    }
    
    /**
     * 订单完成消费返利 
     * 备注：目前此方法在订单完成时执行实时返利仅用于线上订单
     * @author chenyifei
     * @param $order_info 订单信息
     */
    public function setRebate($order_info)
    {
        // 跑腿邦订单情况
        if ($order_info['order_type'] == 3){
            $order_info['store_id'] = $order_info['league_store_id'];
            $order_info['store_name'] = $order_info['league_store_name'];
        }
        $order_check = Model()->table('order')->field('order_state, is_rebate')->where(array('order_id' => $order_info['order_id']))->lock(true)->find();
        if (! ($order_check['order_state'] == ORDER_STATE_SUCCESS && $order_check['is_rebate'] == 0 ) )
            return ;
        //商家表
        $seller = Model()->table('store')->field('member_id')->where(array('store_id'=>$order_info['store_id']))->master(true)->limit(1)->select();
        //商家余额 加锁
        $sell_available_predeposit = Model()->table('member')->field('available_predeposit, member_name,member_id')->where(array('member_id'=>$seller[0]['member_id']))->limit(1)->lock(true)->select();
        //总分利金额
        $rebate_money_all = 0;
        //商户扣除总金额
        $remove_store_money = 0;
        $rebate_arr = array();
        $add_time = time();
        //分销商品
        $distribution_goods = array();
        
        if ($order_info['goods_type']==1)
        {
            $order_goods = Model()->table('order_goods')->where(array('order_id'=>$order_info['order_id']))->master(true)->select();
            //退货商品
            $refund_goods_data = Model('refund_return')->getRefundGoods(array('order_id'=>$order_info['order_id'], 'refund_state'=>3), true);
            $refund_goods = array();
            foreach ($refund_goods_data as $key=>$row)
            {
                $refund_goods[$row['goods_id']] = $row;
            }
            foreach ($order_goods as $key=>$row)
            {
                //退货退款商品不参与分销和返利
                if (!array_key_exists($row['goods_id'], $refund_goods))
                {
                    //统计分销商品
                    if (in_array($row['is_distribution'], array(1, 2, 3)))
                    {
                        $distribution_goods[] = $row;
                    }
                    //分利金额
                    $rebate_money = round($row['goods_pay_price'] * $row['commis_rate'] / 100 , 2);
                    if ($rebate_money > 0)
                    {
                        $rebate_money_all += $rebate_money;
                        $remove_store_money += $rebate_money;
                        $data = array(
                            'goods_id' => $row['goods_id'],
                            'goods_name' => $row['goods_name'],
                            'order_id' => $order_info['order_id'],
                            'rebate' => $rebate_money,
                            'store_id' => $order_info['store_id'],
                            'store_name' => $order_info['store_name'],
                            'rebate_type' => $order_info['order_type'],
                            'order_sn' => $order_info['order_sn'],
                            'add_time' => $add_time,
                            'settle_money' => bcsub($row['goods_pay_price'], $rebate_money, 2),
                            'amount' => $row['goods_pay_price'],
                        );
                        $rebate_arr[] = $data;
                    }
                    /* else 
                    {
                        //返利金额过小，直接将消费金额给商户
                        $change_type = 'settle_account';
                        $desc =  '订单：'.$order_info['order_sn']. '; 商品:'. $row['goods_name'].'结算';
                        $pd_log_data = array(
                            'member_id' => $sell_available_predeposit[0]['member_id'],
                            'member_name' => $sell_available_predeposit[0]['member_name'],
                            'amount' => $row['goods_pay_price'],
                            'order_sn'=>$desc,
                        );
                        $model_pd = Model('predeposit');
                        $model_pd->changePd($change_type, $pd_log_data);
                    } */
                }
                elseif ( $row['goods_pay_price'] > $refund_goods[$row['goods_id']]['refund_amount'] )
                {
                    $remove_store_money = $refund_goods[$row['goods_id']]['refund_amount'];
                    //有退款但是没退完全情况
                    /* $change_type = 'settle_account';
                    $desc =  '订单：'.$order_info['order_sn']. '; 商品:'. $row['goods_name'].'结算';
                    $pd_log_data = array(
                        'member_id' => $sell_available_predeposit[0]['member_id'],
                        'member_name' => $sell_available_predeposit[0]['member_name'],
                        'amount' => bcsub($row['goods_pay_price'], $refund_goods[$row['goods_id']]['refund_amount'], 2),
                        'order_sn'=>$desc,
                    );
                    $model_pd = Model('predeposit');
                    $model_pd->changePd($change_type, $pd_log_data); */
                }
            
            
            }
        }
        else 
        {
            //本土订单线上支付无商品情况返利
            $rebate_money_all = round($order_info['goods_amount'] * $order_info['commis_rate'] / 100 , 2);
            $remove_store_money +=  $rebate_money_all;
            if ($rebate_money_all > 0)
            {
                $rebate_arr[] = array(
                    'goods_id' => 0,
                    'goods_name' => '商户返利',
                    'order_id' => $order_info['order_id'],
                    'rebate' => $rebate_money_all,
                    'store_id' => $order_info['store_id'],
                    'store_name' => $order_info['store_name'],
                    'rebate_type' => $order_info['order_type'],
                    'order_sn' => $order_info['order_sn'],
                    'add_time' => $add_time,
                    'amount' => $order_info['goods_amount'],
                );
            }
           /*  else 
            {
                //金额过小，无返利情况直接将消费给商家
                $change_type = 'settle_account';
                $desc =  '订单：'.$order_info['order_sn'].'结算';
                $pd_log_data = array(
                    'member_id' => $sell_available_predeposit[0]['member_id'],
                    'member_name' => $sell_available_predeposit[0]['member_name'],
                    'amount' => $order_info['order_amount'],
                    'order_sn'=>$desc,
                );
                $model_pd = Model('predeposit');
                $model_pd->changePd($change_type, $pd_log_data);
            } */
        }
        if (!empty($rebate_arr))
        {
            //用户参与返利金额
            $member =  Model()->table('member')->field('member_id, member_name, firest_inviter, second_inviter')->where(array('member_id'=>$order_info['buyer_id']))->master(true)->limit(1)->select();
            //分销商参与返利额
            $agents = Model()->table('agent_store')->where(array('store_id'=>$order_info['store_id']))->master(true)->limit(1)->select();
        
            $this->execRebate($member, $agents, $rebate_arr, $sell_available_predeposit, 'on_line');
        }
        $distribution_remove_money = $this->_distributionGoods($distribution_goods, $order_info);
        $remove_store_money = bcadd($remove_store_money, $distribution_remove_money, 2);
        //商户结算
        if ( bccomp($order_info['order_amount'] , $remove_store_money, 2 ) == 1  )
        {
            $change_type = 'settle_account';
            $desc =  '订单：'.$order_info['order_sn'].'结算';
            $pd_log_data = array(
                'member_id' => $sell_available_predeposit[0]['member_id'],
                'member_name' => $sell_available_predeposit[0]['member_name'],
                'amount' => bcsub($order_info['order_amount'], $remove_store_money, 2),
                'order_sn'=>$desc,
                'lg_mark' => array(
                        'order_sn' => $order_info['order_sn'],
                        'pd_amount' => $order_info['pd_amount'],
                        'payment_code' => $order_info['payment_code'],
                        'order_amount' => $order_info['order_amount'],
                        'rebate_money' => $rebate_money_all,
                        'distribution_money' => $distribution_remove_money,
                    ),
            );
            $model_pd = Model('predeposit');
            $model_pd->changePd($change_type, $pd_log_data);
        }
        
        //更新订单返利状态
        Model()->table('order')->where(array('order_id' => $order_info['order_id']))->update(array('is_rebate' => 1));
        //订单有商品情况
        /* 
        if ($order_info['goods_type']==1)
        {
            $order_goods = Model()->table('order_goods')->where(array('order_id'=>$order_info['order_id']))->select();
            //退货商品
            $refund_goods = Model('refund_return')->getRefundGoodsId(array('order_id'=>$order_info['order_id'], 'refund_state'=>3));
            foreach ($order_goods as $key=>$row)
            {
                if (!in_array($row['goods_id'], $refund_goods))
                {
                    //分利金额
                    $rebate_money = round($row['goods_pay_price'] * $row['commis_rate'] / 100 , 2);
                    if ($rebate_money > 0)
                    {
                        $rebate_money_all += $rebate_money;
                        $data = array(
                            'goods_id' => $row['goods_id'],
                            'goods_name' => $row['goods_name'],
                            'order_id' => $order_info['order_id'],
                            'rebate' => $rebate_money,
                            'store_id' => $order_info['store_id'],
                            'store_name' => $order_info['store_name'],
                            'rebate_type' => $order_info['order_type'],
                            'order_sn' => $order_info['order_sn'],
                            'add_time' => $add_time,
                            'amount' => $row['goods_pay_price'],
                        );
                        $rebate_arr[] = $data;
                    }
                }
                
                
            }
        }
        //本土订单无商品情况
        else 
        {
            $rebate_money_all = round($order_info['goods_amount'] * $order_info['commis_rate'] / 100 , 2);
            if ($rebate_money_all > 0)
            {
                $rebate_arr[] = array(
                    'goods_id' => 0,
                    'goods_name' => '商户返利',
                    'order_id' => $order_info['order_id'],
                    'rebate' => $rebate_money_all,
                    'store_id' => $order_info['store_id'],
                    'store_name' => $order_info['store_name'],
                    'rebate_type' => $order_info['order_type'],
                    'order_sn' => $order_info['order_sn'],
                    'add_time' => $add_time,
                    'amount' => $order_info['goods_amount'],
                );
            }
            
        }

        if ($sell_available_predeposit[0]['available_predeposit'] < $rebate_money_all)
        {
            //throw new Exception('商户可用金额小于返利金额');
            return ;
        }
        else 
        {
            if (!empty($rebate_arr))
            {
                //用户参与返利金额
                $member =  Model()->table('member')->field('member_id, member_name, firest_inviter, second_inviter')->where(array('member_id'=>$order_info['buyer_id']))->limit(1)->select();
                //分销商参与返利额
                $agents = Model()->table('agent_store')->where(array('store_id'=>$order_info['store_id']))->limit(1)->select();
                
                $this->execRebate($member, $agents, $rebate_arr, $sell_available_predeposit);
            }
            
        } 
        */
       
        
    }
    /**
     * 分销商品统计计算
     * @param unknown $distribution_goods
     */
    private function _distributionGoods($distribution_goods, $order_info)
    {
        //商户分销扣除金额
        $store_remove_money = 0;
        $distribution_records = array();
        $add_time = TIMESTAMP;
        $store_distribution_money = 0;
        $store_distribution_type = 0;
        foreach ($distribution_goods as $key=>$row)
        {
            $distribution_records = array(
                'store_id' => $row['store_id'],
                'goods_id' => $row['goods_id'],
                'goods_name' => $row['goods_name'],
                'order_id' => $row['order_id'],
                'order_sn' => $order_info['order_sn'],
                'add_time' => $add_time,
                'goods_price' => $row['goods_price'],
                'first_price' => $row['first_price'],
                'second_price' => $row['second_price'],
                'goods_num' => $row['goods_num'],
            );
             switch ($row['is_distribution'])
            {
                //本土商户分销
                case 1:
                    $store_distribution_money = $distribution_money = round( ($row['goods_price'] - $row['first_price']) * $row['goods_num'], 2 );
                    $store_distribution_type = 1;
                    $store_remove_money = bcadd($store_remove_money, $distribution_money, 2);
                    $dis_members = Model('member')->getMemberInfoByID($row['dis_store_member_id']);
                    $distribution_records['dis_member_id'] = $row['dis_store_member_id'];
                    $distribution_records['dis_member_name'] = $dis_members['member_name'];
                    $distribution_records['dis_store_id'] = $row['dis_store_id'];
                    $distribution_records['distribution_money'] = $distribution_money;
                    $distribution_records['distribution_type'] = 1;
                    Model('distribution_records')->addRecords($distribution_records, $dis_members);
                    break;
                //会员直接分销    
                case 2:
                    $distribution_money = 0;
                    $distribution_type = 0;
                    $dis_members = Model('member')->getMemberInfo(array('member_id'=>$row['dis_member_id']), '*',  true);
                    // 异常
                    if ($dis_members['is_distribution'] == 0)
                    {
                        throw new Exception($dis_members['member_name'].'不是分销商');
                    }
                    elseif ($dis_members['is_distribution'] == 1)
                    {
                        //一级分销商   
                        $store_distribution_money = $distribution_money = round( ($row['goods_price'] - $row['first_price']) * $row['goods_num'], 2 );
                        $store_distribution_type = 2;
                        $store_remove_money = bcadd($store_remove_money, $distribution_money, 2);
                        $distribution_type = 2;
                    }
                    else 
                    {
                        //二级分销商
                        $store_distribution_money = $distribution_money = round( ($row['goods_price'] - $row['second_price']) * $row['goods_num'], 2 );
                        $store_distribution_type = 3;
                        $store_remove_money = bcadd($store_remove_money, $distribution_money, 2);
                        $distribution_type = 3;
                    }
                    $distribution_records['dis_member_id'] = $row['dis_member_id'];
                    $distribution_records['dis_member_name'] = $dis_members['member_name'];
                    $distribution_records['dis_store_id'] = $row['dis_store_id'];
                    $distribution_records['distribution_money'] = $distribution_money;
                    $distribution_records['distribution_type'] = $distribution_type;
                    Model('distribution_records')->addRecords($distribution_records, $dis_members);
                    break;
                //会员从本土商户分销
                case 3:
                    //一级分销价
                    $store_distribution_money = $distribution_money_first = round( ($row['goods_price'] - $row['first_price']) * $row['goods_num'], 2 );
                    $store_distribution_type = 4;
                    //二级分销价
                    $distribution_money_second = round( ($row['goods_price'] - $row['second_price']) * $row['goods_num'], 2 );
                    $store_remove_money = bcadd($store_remove_money, $distribution_money_first, 2);
                    //店铺
                    $dis_store_members = Model('member')->getMemberInfoByID($row['dis_store_member_id']);
                    $store_distribution_records = $distribution_records;
                    $store_distribution_records['dis_member_id'] = $row['dis_store_member_id'];
                    $store_distribution_records['dis_member_name'] = $dis_store_members['member_name'];
                    $store_distribution_records['dis_store_id'] = $row['dis_store_id'];
                    $store_distribution_records['distribution_money'] = bcsub($distribution_money_first, $distribution_money_second, 2);
                    $store_distribution_records['distribution_type'] = 4;
                    Model('distribution_records')->addRecords($store_distribution_records, $dis_store_members);
                    //普通会员
                    $dis_members = Model('member')->getMemberInfo(array('member_id'=>$row['dis_member_id']), '*',  true);
                    $member_distribution_records = $distribution_records;
                    $member_distribution_records['dis_member_id'] = $row['dis_member_id'];
                    $member_distribution_records['dis_member_name'] = $dis_members['member_name'];
                    $member_distribution_records['dis_store_id'] = $row['dis_store_id'];
                    $member_distribution_records['distribution_money'] = $distribution_money_second;
                    $member_distribution_records['distribution_type'] = 5;
                    Model('distribution_records')->addRecords($member_distribution_records, $dis_members);
                    break;
               default:
                   throw new Exception('分销类型错误');
                   break;
            } 
            //平台佣金扣除
            $platform = round($row['goods_price'] * $row['goods_num'] * $row['distribution_rate'] / 100, 2);
            $store_remove_money = bcadd($store_remove_money, $platform, 2);
            //线上商户分销返佣记录
            $distribution_store_records = array(
                'store_id' => $row['store_id'],
                'goods_id' => $row['goods_id'],
                'goods_name' => $row['goods_name'],
                'order_id' => $row['order_id'],
                'distribution_money' => $store_distribution_money,
                'order_sn' => $order_info['order_sn'],
                'add_time' => $add_time,
                'distribution_type' => $store_distribution_type ,
                'goods_price' => $row['goods_price'] ,
                'first_price' => $row['first_price'] ,
                'second_price' => $row['second_price'] ,
                'commission_money' => $platform ,
                'distribution_rate' => $row['distribution_rate'] ,
                'goods_num' => $row['goods_num'],
            );
            Model('distribution_store_records')->addRecords($distribution_store_records);
        }
        return $store_remove_money;
    }
    /**chenyifei
     * 分利计算
     * $order_type 订单来源 ：off_line 本土订单； on_line 线上订单
     */
    public function execRebate($member, $agents, $rebate_arr,$store_member, $order_type='off_line')
    {
        if (empty($member) || empty($rebate_arr) || empty($store_member))
        {
            throw new Exception('系统错误');
        }
        //获取所以分利用户用户名
        $member_ids = array(
            $member[0]['firest_inviter'],
            $member[0]['second_inviter'],
        );
        if (!empty($agents))
        {
            $member_ids = array(
                $member[0]['firest_inviter'],
                $member[0]['second_inviter'],
                $agents[0]['agent_member_id_1'],
                $agents[0]['agent_member_id_2'],
                $agents[0]['agent_member_id_3'],
            );
        }
        $member_names = Model('member')->getMemberNames($member_ids);
        foreach ($rebate_arr as $key=>$row)
        {
            
            //分利激光推送数据
            $jpush_public_data = array(
                'goods_id'=>$row['goods_id'],
                'goods_name' => $row['goods_name'],
                'buyer_name' => $member[0]['member_name'],
                'store_name' => $row['store_name'],
            );
            
            $last_rebate = $all_rebate = $row['rebate'];
            $county_money = 0; //一级二级邀请人没有时给区的金额，二级代理没有时也给区的金额
            $platform_money = 0; //省市代理没有时给平台的金额
            //更新商家金额表
            /*  $result  = Model()->table('member')->where(array('member_id'=>$seller[0]['member_id']))->update(array('available_predeposit'=>array('exp','available_predeposit - '.$all_rebate)));
             if (!$result)
                 throw new Exception('保存失败'); */
             //商家返利记录
             
             //$desc = (empty($row['goods_name'])) ? '订单：'.$row['order_sn']. ' 返利' : '订单：'.$row['order_sn'].' 商品:'. $row['goods_name'] . ' 返利' ;
             $rebate_pay = 'rebate_pay';
             $pd_log_data = array();
             /* if ($order_type == 'off_line')
             {
                 $desc =  '订单：'.$row['order_sn']. ' 返利支出'  ;
                 $pd_log_data = array(
                     'member_id' => $store_member[0]['member_id'],
                     'member_name' => $store_member[0]['member_name'],
                     'amount' => bcsub(0, $all_rebate, 2),
                     'order_sn'=>$desc,
                 );
             }
             else 
             {
                 $pd_log_data = array();
             } */
             if (isset($row['settle_money']))
                 unset($row['settle_money']);
             $rebate_data = $row;
             $rebate_data['rebate'] = bcsub(0, $row['rebate'], 2);
             $rebate_data['member_id'] = $store_member[0]['member_id'];
             $rebate_data['member_name'] = $store_member[0]['member_name'];
             $rebate_data['user_type'] = 4;
             $this->execRebateData($rebate_pay, $pd_log_data, $rebate_data, $order_type);
             //更新用户金额表
             $result_money = round($all_rebate * REBATE_BUY_USER / 100, 2);
             $last_rebate = bcsub($last_rebate, $result_money, 2);
              
             //用户返利记录
             $rebate_data = $row;
             $rebate_data['rebate'] = $result_money;
             $rebate_data['member_id'] = $member[0]['member_id'];
             $rebate_data['member_name'] = $member[0]['member_name'];
             $rebate_data['user_type'] = 1;
              
              
            // $desc = (empty($row['goods_name'])) ? '商家:' .$row['store_name'] . '订单：'.$row['order_sn']. ' 返利' : '商家:' .$row['store_name'] . '订单：'.$row['order_sn'].' 商品:'. $row['goods_name'] . ' 返利' ;
             $desc = '商户:' .$row['store_name'] . '订单：'.$row['order_sn']. ' 返利'  ;
             $pd_log_data = array(
                 'member_id' => $member[0]['member_id'],
                 'member_name' => $member[0]['member_name'],
                 'amount' => $result_money,
                 'order_sn'=>$desc,
                 'lg_mark' => array(
                     'order_sn' => $row['order_sn'],
                 ),
             );
             if ($result_money > 0)
             {
                 $this->execRebateData('rebate_get', $pd_log_data, $rebate_data, $order_type);
                 //极光推送
                 $jpush_data_result = $jpush_public_data;
                 $jpush_data_result['amount'] = $result_money;
                 $jpush_data_result['user_type'] = $rebate_data['user_type'];
                 $this->_rebateMessageJpush($rebate_data['member_id'], $jpush_data_result);
                 
             }
             
             //一级邀请人返利
             $result_money = round($all_rebate * REBATE_FIREST_INVITER / 100, 2);
             if (bccomp($result_money, $last_rebate, 2) == 1)
             {
                 $result_money = $last_rebate;
                 $last_rebate = 0;
             }
             else 
             {
                 $last_rebate = bcsub($last_rebate, $result_money, 2);
             }
             if ($member[0]['firest_inviter'] > 0 )
             {
                 $name_str = isset($member_names[$member[0]['firest_inviter']]) ? $member_names[$member[0]['firest_inviter']] : '';
                 //一级邀请人返利记录
                 $rebate_data = $row;
                 $rebate_data['rebate'] = $result_money;
                 $rebate_data['member_id'] = $member[0]['firest_inviter'];
                 $rebate_data['member_name'] = $name_str;
                 $rebate_data['user_type'] = 2;
                  
                 //$desc = (empty($row['goods_name'])) ? "邀请人{$member[0]['member_name']}在{$row['store_name']}消费返利"  :  "邀请人{$member[0]['member_name']}在{$row['store_name']}购买{$row['goods_name']}返利" ;
                 $desc =  "邀请人{$member[0]['member_name']}在{$row['store_name']}消费返利"   ;
                 $pd_log_data = array(
                     'member_id' => $member[0]['firest_inviter'],
                     'member_name' => $name_str,
                     'amount' => $result_money,
                     'order_sn'=>$desc,
                     'lg_mark' => array(
                         'order_sn' => $row['order_sn'],
                     ),
                 );
                 if ($result_money > 0)
                 {
                     $this->execRebateData('rebate_get', $pd_log_data, $rebate_data, $order_type);
                      
                     //极光推送
                     $jpush_data_result = $jpush_public_data;
                     $jpush_data_result['amount'] = $result_money;
                     $jpush_data_result['user_type'] = $rebate_data['user_type'];
                     $this->_rebateMessageJpush($rebate_data['member_id'], $jpush_data_result);
                     
                 }
                 
             }
             else
             {
                 $county_money = bcadd($county_money, $result_money, 2);
             }
             //二级邀请人返利
             $result_money = round($all_rebate * REBATE_SECOND_INVITER / 100, 2);
             if (bccomp($result_money, $last_rebate, 2) == 1)
             {
                 $result_money = $last_rebate;
                 $last_rebate = 0;
             }
             else
             {
                 $last_rebate = bcsub($last_rebate, $result_money, 2);
             }
             if ($member[0]['second_inviter'] > 0 )
             {
        
                 $name_str = isset($member_names[$member[0]['second_inviter']]) ? $member_names[$member[0]['second_inviter']] : '';
                 //二级邀请人返利记录
                 $rebate_data = $row;
                 $rebate_data['rebate'] = $result_money;
                 $rebate_data['member_id'] = $member[0]['second_inviter'];
                 $rebate_data['member_name'] = $name_str;
                 $rebate_data['user_type'] = 3;
        
                // $desc = (empty($row['goods_name'])) ? "邀请人{$member[0]['member_name']}在{$row['store_name']}消费返利"  :  "邀请人{$member[0]['member_name']}在{$row['store_name']}购买{$row['goods_name']}返利" ;
                 $desc = "邀请人{$member[0]['member_name']}在{$row['store_name']}消费返利"   ;
                 $pd_log_data = array(
                     'member_id' => $member[0]['second_inviter'],
                     'member_name' => $name_str,
                     'amount' => $result_money,
                     'order_sn'=>$desc,
                     'lg_mark' => array(
                         'order_sn' => $row['order_sn'],
                     ),
                 );
                 if ($result_money > 0)
                 {
                     $this->execRebateData('rebate_get', $pd_log_data, $rebate_data, $order_type);
                     //极光推送
                     $jpush_data_result = $jpush_public_data;
                     $jpush_data_result['amount'] = $result_money;
                     $jpush_data_result['user_type'] = $rebate_data['user_type'];
                     $this->_rebateMessageJpush($rebate_data['member_id'], $jpush_data_result);
                     
                 }
                 
             }
             else
             {
                 $county_money = bcadd($county_money, $result_money, 2);
             }
             //平台
             /* $platform_money = round($all_rebate * REBATE_PLATFORM / 100, 2);
              $last_rebate = bcsub($last_rebate, $result_money, 2); */
             //无代理商情况
             if (empty($agents))
             {
                 //平台返利
                 $rebate_data = $row;
                 $rebate_data['rebate'] = bcadd($last_rebate, $county_money, 2);
                 $rebate_data['member_id'] = 0;
                 $rebate_data['member_name'] = '';
                 $rebate_data['user_type'] = 8;
                 if ($rebate_data['rebate'] > 0)
                 {
                     $result = Model()->table('rebate_records')->insert($rebate_data);
                     if (!$result)
                         throw new Exception('保存失败');
                 }
                 
             }
             //省市区代理
             elseif ($agents[0]['agent_mode'] == 1)
             {
                 //区代理商
                 $result_money = round($all_rebate * REBATE_COUNTY_AGENCY / 100, 2);
                 if (bccomp($result_money, $last_rebate, 2) == 1)
                 {
                     $result_money = $last_rebate;
                     $last_rebate = 0;
                 }
                 else
                 {
                     $last_rebate = bcsub($last_rebate, $result_money, 2);
                 }
                 $result_money = bcadd($result_money, $county_money, 2);
        
                 $name_str = isset($member_names[$agents[0]['agent_member_id_1']]) ? $member_names[$agents[0]['agent_member_id_1']] : '';
                 //区代理商返利记录
                 $rebate_data = $row;
                 $rebate_data['rebate'] = $result_money;
                 $rebate_data['member_id'] = $agents[0]['agent_member_id_1'];
                 $rebate_data['member_name'] = $name_str;
                 $rebate_data['user_type'] = 5;
                 $rebate_data['agent_id'] = $agents[0]['agent_id_1'];
        
                 //$desc = (empty($row['goods_name'])) ? "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"  :  "商户:{$row['store_name']} 订单:{$row['order_sn']} 商品{$row['goods_name']}返利" ;
                 $desc =  "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"  ;
                 $pd_log_data = array(
                     'member_id' => $agents[0]['agent_member_id_1'],
                     'member_name' => $name_str,
                     'amount' => $result_money,
                     'order_sn'=>$desc,
                     'lg_mark' => array(
                         'order_sn' => $row['order_sn'],
                     ),
                 );
                 if ($result_money > 0)
                 {
                     $this->execRebateData('rebate_get', $pd_log_data, $rebate_data, $order_type);
                     //极光推送
                     $jpush_data_result = $jpush_public_data;
                     $jpush_data_result['amount'] = $result_money;
                     $jpush_data_result['user_type'] = $rebate_data['user_type'];
                     $this->_rebateMessageJpush($rebate_data['member_id'], $jpush_data_result);
                     
                 }
                 
                 //市级代理商返利
                 $result_money = round($all_rebate * REBATE_CITY_AGENCY / 100, 2);
                 if (bccomp($result_money, $last_rebate, 2) == 1)
                 {
                     $result_money = $last_rebate;
                     $last_rebate = 0;
                 }
                 else
                 {
                     $last_rebate = bcsub($last_rebate, $result_money, 2);
                 }
                 if ($agents[0]['agent_member_id_2'] > 0)
                 {
                      
                     $name_str = isset($member_names[$agents[0]['agent_member_id_2']]) ? $member_names[$agents[0]['agent_member_id_2']] : '';
                     //市级理商返利记录
                     $rebate_data = $row;
                     $rebate_data['rebate'] = $result_money;
                     $rebate_data['member_id'] = $agents[0]['agent_member_id_2'];
                     $rebate_data['member_name'] = $name_str;
                     $rebate_data['user_type'] = 6;
                     $rebate_data['agent_id'] = $agents[0]['agent_id_2'];
        
                    // $desc = (empty($row['goods_name'])) ? "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"  :  "商户:{$row['store_name']} 订单:{$row['order_sn']} 商品{$row['goods_name']}返利" ;
                     $desc =  "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"  ;
                     $pd_log_data = array(
                         'member_id' => $agents[0]['agent_member_id_2'],
                         'member_name' => $name_str,
                         'amount' => $result_money,
                         'order_sn'=>$desc,
                         'lg_mark' => array(
                             'order_sn' => $row['order_sn'],
                         ),
                     );
                     if ($result_money > 0)
                     {
                         $this->execRebateData('rebate_get', $pd_log_data, $rebate_data, $order_type);
                         //极光推送
                         $jpush_data_result = $jpush_public_data;
                         $jpush_data_result['amount'] = $result_money;
                         $jpush_data_result['user_type'] = $rebate_data['user_type'];
                         $this->_rebateMessageJpush($rebate_data['member_id'], $jpush_data_result);
                         
                     }
                     
                 }
                 else
                 {
                     $platform_money = bcadd($platform_money, $result_money, 2);
                 }
                 //省级代理
                 $result_money = round($all_rebate * REBATE_PROVINCE_AGENCY / 100, 2);
                 if (bccomp($result_money, $last_rebate, 2) == 1)
                 {
                     $result_money = $last_rebate;
                     $last_rebate = 0;
                 }
                 else
                 {
                     $last_rebate = bcsub($last_rebate, $result_money, 2);
                 }
                 if ($agents[0]['agent_member_id_3'] > 0)
                 {
                     $name_str = isset($member_names[$agents[0]['agent_member_id_3']]) ? $member_names[$agents[0]['agent_member_id_3']] : '';
                     //省级商返利记录
                     $rebate_data = $row;
                     $rebate_data['rebate'] = $result_money;
                     $rebate_data['member_id'] = $agents[0]['agent_member_id_3'];
                     $rebate_data['member_name'] = $name_str;
                     $rebate_data['user_type'] = 7;
                     $rebate_data['agent_id'] = $agents[0]['agent_id_3'];
        
                    // $desc = (empty($row['goods_name'])) ? "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"  :  "商户:{$row['store_name']} 订单:{$row['order_sn']} 商品{$row['goods_name']}返利" ;
                     $desc =  "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"   ;
                     $pd_log_data = array(
                         'member_id' => $agents[0]['agent_member_id_3'],
                         'member_name' => $name_str,
                         'amount' => $result_money,
                         'order_sn'=>$desc,
                         'lg_mark' => array(
                             'order_sn' => $row['order_sn'],
                         ),
                     );
                     if ($result_money > 0)
                     {
                         $this->execRebateData('rebate_get', $pd_log_data, $rebate_data, $order_type);
                         //极光推送
                         $jpush_data_result = $jpush_public_data;
                         $jpush_data_result['amount'] = $result_money;
                         $jpush_data_result['user_type'] = $rebate_data['user_type'];
                         $this->_rebateMessageJpush($rebate_data['member_id'], $jpush_data_result);
                        
                     }
                     
                 }
                 else
                 {
                     $platform_money = bcadd($platform_money, $result_money, 2);
                 }
                 //平台返利
                 $rebate_data = $row;
                 $rebate_data['rebate'] = bcadd($last_rebate, $platform_money, 2);
                 $rebate_data['member_id'] = 0;
                 $rebate_data['user_type'] = 8;
                 if ($rebate_data['rebate'] > 0)
                 {
                     $result = Model()->table('rebate_records')->insert($rebate_data);
                     if (!$result)
                         throw new Exception('保存失败');
                 }
                 
             }
             //旧代理模式
             elseif ($agents[0]['agent_mode'] == 2)
             {
                 
                 //二级代理
                 $result_money = round($all_rebate * REBATE_TWO_AGENCY / 100, 2);
                 if (bccomp($result_money, $last_rebate, 2) == 1)
                 {
                     $result_money = $last_rebate;
                     $last_rebate = 0;
                 }
                 else
                 {
                     $last_rebate = bcsub($last_rebate, $result_money, 2);
                 }
                 if ($agents[0]['agent_member_id_1'] > 0)
                 {
                     $name_str = isset($member_names[$agents[0]['agent_member_id_1']]) ? $member_names[$agents[0]['agent_member_id_1']] : '';
                     $rebate_data = $row;
                     $rebate_data['rebate'] = $result_money;
                     $rebate_data['member_id'] = $agents[0]['agent_member_id_1'];
                     $rebate_data['member_name'] = $name_str;
                     $rebate_data['user_type'] = 10;
                     $rebate_data['agent_id'] = $agents[0]['agent_id_1'];
        
                    // $desc = (empty($row['goods_name'])) ? "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"  :  "商户:{$row['store_name']} 订单:{$row['order_sn']} 商品{$row['goods_name']}返利" ;
                     $desc =  "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"   ;
                     $pd_log_data = array(
                         'member_id' => $agents[0]['agent_member_id_1'],
                         'member_name' => $name_str,
                         'amount' => $result_money,
                         'order_sn'=>$desc,
                         'lg_mark' => array(
                             'order_sn' => $row['order_sn'],
                         ),
                     );
                     if ($result_money > 0)
                     {
                         $this->execRebateData('rebate_get', $pd_log_data, $rebate_data, $order_type);
                         //极光推送
                         $jpush_data_result = $jpush_public_data;
                         $jpush_data_result['amount'] = $result_money;
                         $jpush_data_result['user_type'] = $rebate_data['user_type'];
                         $this->_rebateMessageJpush($rebate_data['member_id'], $jpush_data_result);
                         
                     }
                     
                 }
                 else
                 {
                     $county_money = bcadd($county_money, $result_money, 2);
                 }
                 //区域代理
                 $result_money = round($all_rebate * REBATE_OLD_COUNTY_AGENCY / 100, 2);
                 if (bccomp($result_money, $last_rebate, 2) == 1)
                 {
                     $result_money = $last_rebate;
                     $last_rebate = 0;
                 }
                 else
                 {
                     $last_rebate = bcsub($last_rebate, $result_money, 2);
                 }
                 $result_money = bcadd($result_money, $county_money, 2);
        
                 $name_str = isset($member_names[$agents[0]['agent_member_id_2']]) ? $member_names[$agents[0]['agent_member_id_2']] : '';
                 //区代理商返利记录
                 $rebate_data = $row;
                 $rebate_data['rebate'] = $result_money;
                 $rebate_data['member_id'] = $agents[0]['agent_member_id_2'];
                 $rebate_data['member_name'] = $name_str;
                 $rebate_data['user_type'] = 9;
                 $rebate_data['agent_id'] = $agents[0]['agent_id_2'];
        
                // $desc = (empty($row['goods_name'])) ? "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"  :  "商户:{$row['store_name']} 订单:{$row['order_sn']} 商品{$row['goods_name']}返利" ;
                 $desc =  "商户:{$row['store_name']} 订单:{$row['order_sn']} 返利"   ;
                 $pd_log_data = array(
                     'member_id' => $agents[0]['agent_member_id_2'],
                     'member_name' => $name_str,
                     'amount' => $result_money,
                     'order_sn'=>$desc,
                     'lg_mark' => array(
                         'order_sn' => $row['order_sn'],
                     ),
                 );
                 if ($result_money > 0)
                 {
                     $this->execRebateData('rebate_get', $pd_log_data, $rebate_data, $order_type);
                     //极光推送
                     $jpush_data_result = $jpush_public_data;
                     $jpush_data_result['amount'] = $result_money;
                     $jpush_data_result['user_type'] = $rebate_data['user_type'];
                     $this->_rebateMessageJpush($rebate_data['member_id'], $jpush_data_result);
                 }
                 
                 
                 //平台返利
                // $result_money = round($all_rebate * REBATE_PLATFORM / 100, 2);
                // $last_rebate = bcsub($last_rebate, $result_money, 2);
                 $rebate_data = $row;
                 $rebate_data['rebate'] = $last_rebate;
                 $rebate_data['member_id'] = 0;
                 $rebate_data['user_type'] = 8;
                 if ($rebate_data['rebate'] > 0)
                 {
                     $result = Model()->table('rebate_records')->insert($rebate_data);
                     if (!$result)
                         throw new Exception('保存失败');
                 }
                 
                 
             }
        }
    }
    /**
     * chenyifei
     * 分利消息推送
     */
    private function _rebateMessageJpush($member_id, $jpush_data_result)
    {
        QueueClient::push('jpush', array(
                'message'=>'消费返佣',
                'member_ids'=>$member_id,
                'extend'=>array(
                //'audience_tag' => array('v2.1.0'),
                'extras'=>array(
                        'data' => array(
                            'message_type' => 'REBATE_RECORD',
                            'message_data'=>$jpush_data_result,
                        ),
                    ),
                )
            )
        );
    }
    /**chenyifei
     *  用户，代理商等分利更新操作
     */
    private function execRebateData($change_type,$pd_log_data, $rebate_data , $order_type='off_line')
    {
        if ($rebate_data['user_type'] !=4 && $rebate_data['rebate'] <= 0) return;
        //线上商户直接返积分，不通过此处，仅本土直接付款要扣返佣
        //2016-1-19更新，所有商户均不在此改变积分，在其他地方结算积分
        if ($rebate_data['user_type'] !=4 )
        {
            if (!empty($pd_log_data)){
                $model_pd = Model('predeposit');
                $model_pd->changePd($change_type, $pd_log_data);
            }  
        }
        
        //返利记录
        if ($rebate_data['rebate'] != 0)
        {
            $result = Model()->table('rebate_records')->insert($rebate_data);
            if (!$result)
                throw new Exception('保存失败');
        }
        
    }
    
    

    /**
     * 本土预售退款
     * @param array $order_info
     * @return array
     */
    public function changeLocalPrice($order_info) {
        try {
            $order_id       =   $order_info['order_id'];
            $order_money    =   $order_info['order_money'];
            $member_id      =   $order_info['member_id'];
            $member_name    =   $order_info['member_name'];
            $order_sn       =   $order_info['order_sn'];
            $model_order    =   Model('order');
            $model_member   =   Model('member');
            $model_order->beginTransaction();
            //order_state=0 且 refund_state=2，
            $data           = array();
            //$member_data    =   array();
            //$order_money    = abs(floatval($order_money));
            //$member_data['available_predeposit'] = array('exp','available_predeposit-'.$order_money);
            //$member_update  =  true;//$model_member->editMember(array('member_id'=>$member_id),$member_data);

            $data['order_state']    =   0;
            $data['refund_state']   =   2;
            $update         =  $model_order->editOrder($data,array('order_id'=>$order_id),1);
            if (!$update) {
                throw new Exception('保存失败');
            }
            //记录订单日志
            $data = array();
            $data['member_id'] = $member_id;
            $data['member_name'] = $member_name;
            $data['amount'] = $order_money;
            $data['order_sn'] = $order_sn;
            Model('predeposit')->changePd('refund',$data);
            $model_order->commit();
            QueueClient::push('storeJpush', array(
                'message' => "订单：{$order_info['order_sn']}，已退单",
                'store_id' => $order_info['store_id'],
                'extend' => array(
                    'extras' => array(
                        'data' => array(
                            'message_type' => 'O2O_STORE_ORDER_REFUND',
                            'message_data' => array(
                                'order_id' => $order_info['order_id'],
                                'order_sn'=>$order_info['order_sn']
                            )
                        )
                    )
                )
            ));
            return true;
        } catch (Exception $e) {
            $model_order->rollback();
            return false;
        }
    }

    /**
     * 商家后台商家操作退款
     */
     public function userActionLocalPrice($order_info=array()){
        try {
            $order_id       =   $order_info['order_id'];
            $order_money    =   $order_info['order_money'];
            $member_id      =   $order_info['member_id'];
            $member_name    =   $order_info['member_name'];
            $order_sn       =   $order_info['order_sn'];
            $model_order    =   Model('order');
            $model_order->beginTransaction();
            //order_state=0 且 refund_state=2，
            $data           = array();
            $data['order_state']    =   0;
            $data['refund_state']   =   2;
            $update         =  $model_order->editOrder($data,array('order_id'=>$order_id),1);
            if (!$update) {
                throw new Exception('保存失败');
            }
            //记录订单日志
            $data = array();
            $data['member_id'] = $member_id;
            $data['member_name'] = $member_name;
            $data['amount'] = $order_money;
            $data['order_sn'] = $order_sn;
            Model('predeposit')->changePd('refund',$data);
            $model_order->commit();
            QueueClient::push('storeJpush', array(
                'message' => "订单：{$order_info['order_sn']}，已退单",
                'store_id' => $order_info['store_id'],
                'extend' => array(
                    'extras' => array(
                        'data' => array(
                            'message_type' => 'O2O_STORE_ORDER_REFUND',
                            'message_data' => array(
                                'order_id' => $order_info['order_id'],
                                'order_sn'=>$order_info['order_sn']
                            )
                        )
                    )
                )
            ));
            return true;
        } catch (Exception $e) {
            $model_order->rollback();
            return false;
        }
     }
    private function  deliverCommit($company,$code){
        $post_data = array();
        $post_data["schema"] = 'json' ;
        //callbackurl请参考callback.php实现，key经常会变，请与快递100联系获取最新key
        //$post_data["param"] = '{"company":"'.$company.'", "number":"'.$code.'","from":"", "to":"", "key":"ShttOvRo792", "parameters":{"callbackurl":"http://shop.aigegou.com/agg/mobile/index.php?act=unlimited_invitation&op=get_delivery_result"}}';
        $post_data["param"] = '{"company":"'.$company.'", "number":"'.$code.'","from":"", "to":"", "key":"ShttOvRo792", "parameters":{"callbackurl":"http://shop.aigegou.com/agg/mobile/index.php?act=unlimited_invitation&op=get_delivery_result"}}';

        $url='http://www.kuaidi100.com/poll';

        $o="";
        foreach ($post_data as $k=>$v)
        {
            $o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
        }

        $post_data=substr($o,0,-1);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result = curl_exec($ch);		//返回提交结果，格式与指定的格式一致（result=true代表成功）
    }

    /**
     * 跑腿邦取消订单功能
     * @param $order_id 订单id
     */
    public function adt_return_order_coupon($order_id){
        $coupon_used=Model('coupon_use_league')->field('member_coupon_id')->where(array('order_id'=>$order_id))->select();
        if(empty($coupon_used)) return;
        $coupon_used=agg_array_column($coupon_used,'member_coupon_id');
        Model('member_coupon_league')->where(array('id'=>array('in',$coupon_used)))->update(array('is_used'=>0));
    }

}