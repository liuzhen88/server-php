<?php

/**
 * 跑腿邦，退款管理
 *
 * * */
defined('emall') or exit('Access Invalid!');

class adt_refundControl extends SystemControl {

    public function __construct() {
        parent::__construct();
        $model_refund = Model('adt_refund_return');
        $model_refund->getRefundStateArray();
    }

    /**
     * 待处理列表
     *
     */
    public function refund_manageOp() {
        $condition = array();
        $condition['refund_state'] = '0'; //退款状态:0退款失败,1退款成功

        $keyword_type = array('order_sn', 'pay_sn');
        if (trim($_GET['key']) != '' && in_array($_GET['type'], $keyword_type)) {
            $type = $_GET['type'];
            $condition[$type] = array('like', '%' . $_GET['key'] . '%');
        }
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('time', array($add_time_from, $add_time_to));
            }
        }
//        var_dump($condition);
        $model_refund = Model('pay_refund');
        $refund_list = $model_refund->getPayRefundList($condition, 10);

//        var_dump($refund_list);
//        exit;
        Tpl::output('refund_list', $refund_list);
        Tpl::output('show_page', $model_refund->showpage());
        Tpl::showpage('adt_refund_manage.list');
    }

    /**
     * 所有记录
     */
    public function refund_allOp() {
        $condition = array();

        $keyword_type = array('order_sn', 'pay_sn');
        if (trim($_GET['key']) != '' && in_array($_GET['type'], $keyword_type)) {
            $type = $_GET['type'];
            $condition[$type] = array('like', '%' . $_GET['key'] . '%');
        }
        if (trim($_GET['add_time_from']) != '' || trim($_GET['add_time_to']) != '') {
            $add_time_from = strtotime(trim($_GET['add_time_from']));
            $add_time_to = strtotime(trim($_GET['add_time_to']));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('time', array($add_time_from, $add_time_to));
            }
        }
        $model_refund = Model('pay_refund');
        $refund_list = $model_refund->getPayRefundList($condition, 10);

        Tpl::output('refund_list', $refund_list);
        Tpl::output('show_page', $model_refund->showpage());
        Tpl::showpage('adt_refund_all.list');
    }

    /**
     * 退款处理页
     *
     */
    public function editOp() {
        $model_refund = Model('pay_refund');
        $condition = array();
        $condition['order_id'] = intval($_GET['order_id']);
        $refund_list = $model_refund->getPayRefundList($condition);
//        var_dump($refund_list);
        $refund = $refund_list[0];
        if (chksubmit()) {
            if ($refund['refund_state'] != '0') {//检查状态,防止页面刷新不及时造成数据错误
                showMessage(Language::get('nc_common_save_fail'));
            }
            $state = $model_refund->editPayOrderRefund($refund);
            if ($state) {
                $refund_array = array();
                $refund_array['success_time'] = time();
                $refund_array['refund_state'] = '1'; //退款状态:0退款失败,1退款成功
                $model_refund->editPayRefundReturn($condition, $refund_array);

                showMessage(Language::get('nc_common_save_succ'), 'index.php?act=adt_refund&op=refund_manage');
            } else {
                showMessage(Language::get('nc_common_save_fail'));
            }
        }
        Tpl::output('refund', $refund);
        Tpl::showpage('adt_refund.edit');
    }

    /**
     * 退款记录查看页
     *
     */
    public function viewOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['refund_id'] = intval($_GET['refund_id']);
        $refund_list = $model_refund->getRefundList($condition);
        $refund = $refund_list[0];
        Tpl::output('refund', $refund);
        $info['buyer'] = array();
        if (!empty($refund['pic_info'])) {
            $info = unserialize($refund['pic_info']);
        }
        Tpl::output('pic_list', $info['buyer']);
        Tpl::showpage('refund.view');
    }

    /**
     * 退款退货原因
     */
    public function reasonOp() {
        $model_refund = Model('refund_return');
        $condition = array();

        $reason_list = $model_refund->getReasonList($condition, 10);
        Tpl::output('reason_list', $reason_list);
        Tpl::output('show_page', $model_refund->showpage());

        Tpl::showpage('refund_reason.list');
    }

    /**
     * 新增退款退货原因
     *
     */
    public function add_reasonOp() {
        $model_refund = Model('refund_return');
        if (chksubmit()) {
            $reason_array = array();
            $reason_array['reason_info'] = $_POST['reason_info'];
            $reason_array['sort'] = intval($_POST['sort']);
            $reason_array['update_time'] = time();

            $state = $model_refund->addReason($reason_array);
            if ($state) {
                $this->log('新增退款退货原因，编号' . $state);
                showMessage(Language::get('nc_common_save_succ'), 'index.php?act=refund&op=reason');
            } else {
                showMessage(Language::get('nc_common_save_fail'));
            }
        }
        Tpl::showpage('refund_reason.add');
    }

    /**
     * 编辑退款退货原因
     *
     */
    public function edit_reasonOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['reason_id'] = intval($_GET['reason_id']);
        $reason_list = $model_refund->getReasonList($condition);
        $reason = $reason_list[$condition['reason_id']];
        if (chksubmit()) {
            $reason_array = array();
            $reason_array['reason_info'] = $_POST['reason_info'];
            $reason_array['sort'] = intval($_POST['sort']);
            $reason_array['update_time'] = time();
            $state = $model_refund->editReason($condition, $reason_array);
            if ($state) {
                $this->log('编辑退款退货原因，编号' . $condition['reason_id']);
                showMessage(Language::get('nc_common_save_succ'), 'index.php?act=refund&op=reason');
            } else {
                showMessage(Language::get('nc_common_save_fail'));
            }
        }
        Tpl::output('reason', $reason);
        Tpl::showpage('refund_reason.edit');
    }

    /**
     * 删除退款退货原因
     *
     */
    public function del_reasonOp() {
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['reason_id'] = intval($_GET['reason_id']);
        $state = $model_refund->delReason($condition);
        if ($state) {
            $this->log('删除退款退货原因，编号' . $condition['reason_id']);
            showMessage(Language::get('nc_common_del_succ'), 'index.php?act=refund&op=reason');
        } else {
            showMessage(Language::get('nc_common_del_fail'));
        }
    }


    /**
     * 支付宝退款
     */
    public function alipayRefundOp(){
        $alipay_config = logic('payment')->getAlipayConfig();
        //todo 判断该订单是否是支付宝付款
        if(!$alipay_config){
            showMessage('暂时不支持支付宝退款','','json');
        }

        $order_sn = $_GET['order_sn'];
        /** @var refund_returnModel $refund_return */
        $refund_return = Model('refund_return');
        $condition = array();
        $condition['order_sn'] = array('in',$order_sn);
        $refund_order = $refund_return->getOrderBySN($condition,[['order_sn,refund_amount']]);

        if(empty($refund_order)){
            showMessage('没有可以退款的订单','','json');
        }

        $detail_data_arr = $refund_order;
        include(BASE_PATH."/framework/pay/alipay/refund/alipayRefund.php");
    }

}
