<?php
/**
 * 我的预存款
 *
 * @author lijunhua 
 * @since 2015-08-10
 */

use Tpl;

defined('emall') or exit('Access Invalid!');

class member_predepositControl extends mobileMemberControl {

    public function __construct() {
            parent::__construct();
    }

        
    /**
     *  积分明细（我的预存款变更明细）
     * 
     * @param string $param['key']        登录后唯一校验码
     * @param string $param['curpage']    当前页  默认第1页
     * @param string $param['no_list']    不显示积分明细 选传 (默认 0)
     */
    public function indexOp() {
        
        $no_list = isset($_REQUEST['no_list']) && !empty($_REQUEST['no_list']) ? $_REQUEST['no_list'] : 0;
        
        $condition_arr = array();
        $model_pd      = Model('predeposit');
        $condition_arr['lg_member_id'] = $this->member_info['member_id']; 
        $result                    = $this->_get_shop_predeposit();
        
        if ($no_list) {
             output_data($result, mobile_page(0));
        }
  
        $pd_list         = $model_pd->getPdLogList($condition_arr, 100000, '*', 'lg_add_time desc'); //不分页,把每页条数放大些
        $datalist        = array();
        $predeposit_list = array();
        if (!empty($pd_list)) {
            foreach ((array)$pd_list as $key => $value) {
                $month = date('Y-m', $value['lg_add_time']);
                $month = $month == date('Y-m', time()) ? '本月' : $month;
                $value['lg_type_tip']      = $this->_get_order_pay_type($value['lg_type']);

                // 积分明细金额
                if (floatval($value['lg_av_amount']) != 0 ) {
                    $value['lg_av_amount'] = $value['lg_av_amount'] > 0 ? '+' . $value['lg_av_amount'] : $value['lg_av_amount'];
                } else {
                    $value['lg_av_amount'] = $value['lg_freeze_amount'] > 0 ? '+' . $value['lg_freeze_amount'] : $value['lg_freeze_amount'];
                }

                $datalist[$month][]   = $value;
            }
            foreach ($datalist as $k => $v) {
                $predeposit_list[] = array('month' => $k, 'data' => $v);
            }
        }

        $result['predeposit_list'] = $predeposit_list;
        $page_count                = $model_pd->gettotalpage();
        output_data($result, mobile_page($page_count));
    }
    /**
     *  积分明细（我的预存款变更明细）
     *  2.1.6
     * @param string $param['key']        登录后唯一校验码
     * @param string $param['curpage']    当前页  默认第1页
     * @param string $param['no_list']    不显示积分明细 选传 (默认 0)
     */
    public function get_predeposit_change_listOp() {
        $curpage = 1;
        if(isset($_REQUEST['curpage'])&&!empty($_REQUEST['curpage'])) {
            $curpage = $_REQUEST['curpage'];
        }
        $per_page = 20;//每页20条记录
        $start = ($curpage-1)*$per_page;
        $condition_arr = array();
        $model_pd      = Model('predeposit');
        $condition_arr['lg_member_id'] = $this->member_info['member_id'];
        $result                    = $this->_get_shop_predeposit();

        //获取总页数
        $count = $model_pd->getPdLogCount($condition_arr);
        $pd_list = $model_pd->getPdLogList($condition_arr, '', '*', 'lg_add_time desc',$start.',20');
        if (!empty($pd_list)) {
            for($i=0;$i<count($pd_list);$i++) {
                $pd_list[$i]['lg_type_tip']      = $this->_get_order_pay_type($pd_list[$i]['lg_type']);
                // 积分明细金额
                if (floatval($pd_list[$i]['lg_av_amount']) != 0 ) {
                    $pd_list[$i]['lg_av_amount'] = $pd_list[$i]['lg_av_amount'] > 0 ? '+' . $pd_list[$i]['lg_av_amount'] : $pd_list[$i]['lg_av_amount'];
                } else {
                    $pd_list[$i]['lg_av_amount'] = $pd_list[$i]['lg_freeze_amount'] > 0 ? '+' . $pd_list[$i]['lg_freeze_amount'] : $pd_list[$i]['lg_freeze_amount'];
                }
                //流水号生成
                $code_ori = "90000000000";
                $serial_number = substr($code_ori,0,11-strlen($pd_list[$i]['lg_id'])).$pd_list[$i]['lg_id'];
                $pd_list[$i]['serial_number'] = $serial_number;
                $pd_list[$i]['order_sn'] = '';
                $pd_list[$i]['details'] = array();
                //详情
                if(!empty($pd_list[$i]['lg_mark'])) {
                    $lg_mark = json_decode($pd_list[$i]['lg_mark'],true);
                    if(!empty($lg_mark['order_sn'])) {
                        $pd_list[$i]['order_sn'] = $lg_mark['order_sn'];
                    }
                    if(isset($lg_mark['pd_amount'])) {
                        $j = 0;
                        if(!empty($lg_mark['pd_amount'])) {
                            $pd_list[$i]['details'][$j]['pay_type'] = '积分支付';
                            $pd_list[$i]['details'][$j]['money'] = '+'.$lg_mark['pd_amount'];
                            $j++;
                            if($lg_mark['order_amount']-$lg_mark['pd_amount']!=0) {
                                $pd_list[$i]['details'][$j]['pay_type'] = orderPaymentName($lg_mark['payment_code']);
                                $pd_list[$i]['details'][$j]['money'] = '+'.($lg_mark['order_amount']-$lg_mark['pd_amount']);
                                $j++;
                            }
                        }
                        else {
                            $pd_list[$i]['details'][$j]['pay_type'] = orderPaymentName($lg_mark['payment_code']);
                            $pd_list[$i]['details'][$j]['money'] = '+'.$lg_mark['order_amount'];
                            $j++;
                        }
                        if(!empty($lg_mark['rebate_money'])) {
                            $pd_list[$i]['details'][$j]['pay_type'] = '消费返利';
                            $pd_list[$i]['details'][$j]['money'] = '-'.$lg_mark['rebate_money'];
                            $j++;
                        }
                        if(!empty($lg_mark['distribution_money'])) {
                            $pd_list[$i]['details'][$j]['pay_type'] = '分销返利';
                            $pd_list[$i]['details'][$j]['money'] = '-'.$lg_mark['distribution_money'];
                        }
                    }
                }
                unset($pd_list[$i]['lg_mark']);
            }
        }
        $result['predeposit_list'] = $pd_list;
        output_data($result, mobile_page(ceil($count/$per_page)));
    }
    /**
     * 积分提现（这里的积分其实是预存款）
     * 
     * @param string $param['key']        登录后唯一校验码
     * @param string $param['amount']     金额数量
     * @param string $param['paypwd']     交易密码
     */
    public function with_drawOp() {
        $pdc_amount = abs(floatval($_REQUEST['amount']));        
        if ($pdc_amount < 1) {
            output_error('提现金额为大于或者等于1的数字', array(), ERROR_CODE_OPERATE);
        }
        
        if (!isset($_REQUEST['paypwd']) || empty($_REQUEST['paypwd'])) {
            output_error('请输入交易密码');
        } 

        if (empty($this->member_info['member_paypwd'])) {
            output_error('请先设置交易密码');
        }
        
        //查主表 加锁
        $member_detail = Model('member')->getMemberInfo(array('member_id' => $this->member_info['member_id']), 'available_predeposit', true, true);
        $this->member_info['available_predeposit'] = $member_detail['available_predeposit'];

        $model_pd = Model('predeposit');

        if (md6($_REQUEST['paypwd'], $this->member_info['member_salt']) != $this->member_info['member_paypwd']) {
            output_error('交易密码错误');
        }
        
        // 限制商户提现金额
        $this->_limit_shop_predeposit($pdc_amount);
        
        // 验证金额是否足够
        if (floatval($this->member_info['available_predeposit']) < $pdc_amount){
             output_error('积分金额不足');
        }
        
        $member_info = $this->member_info;
          
        if ($member_info['bank_card_bind'] == 0) {
            output_error('您尚未绑定银行卡');
        }
        $model_bank_card =  Model('member_bank_card');
        if(isset($_REQUEST['bank_id'])&&!empty($_REQUEST['bank_id'])) {
            $bankcard_info = $model_bank_card->getMemberBankCardInfo(array('member_id' => $member_info['member_id'],'id'=>$_REQUEST['bank_id']));
        }
        else {
            $bankcard_info = Model()->table('member_bank_card')->where(array('member_id' => $member_info['member_id']))->order('is_default desc')->find();
        }
        if (empty($bankcard_info)) {
            output_error('系统未找到任何有银行卡信息', array(), ERROR_CODE_DATABASE); 
        }
        try {
            $model_pd->beginTransaction();
            $pdc_sn = $model_pd->makeSn();
            $data = array();
            $data['pdc_sn']            = $pdc_sn;
            $data['pdc_member_id']     = $member_info['member_id'];
            $data['pdc_member_name']   = $member_info['member_name'];
            $data['pdc_amount']        = $pdc_amount;
            $data['pdc_bank_name']     = $bankcard_info['pdc_bank_name'];
            $data['pdc_bank_no']       = $bankcard_info['pdc_bank_no'];
            $data['pdc_bank_user']     = $bankcard_info['pdc_bank_user'];
            $data['open_branch']       = $bankcard_info['open_branch'];
            if(!empty($bankcard_info['cardtype'])) {
                $data['cardtype']          = $bankcard_info['cardtype'];
            }
            $data['pdc_add_time']      = TIMESTAMP;
            $data['pdc_payment_state'] = 0;
            $insert = $model_pd->addPdCash($data);
            if (!$insert) {
                output_error('操作失败', array(), ERROR_CODE_DATABASE);
            }
            
            
            //冻结可用预存款
            $data = array();
            $data['member_id']   = $member_info['member_id'];
            $data['member_name'] = $member_info['member_name'];
            $data['amount']      = $pdc_amount;
            $data['order_sn']    = $pdc_sn;
            
            $model_pd->changePd('cash_apply', $data);

            $model_pd->commit();
            //操作成功
            $member_detail = Model('member')->getMemberInfo(array('member_id' => $this->member_info['member_id']), 'available_predeposit', true, true);
            output_data(array('predeposit'=>$member_detail['available_predeposit']));
        } catch (Exception $e) {
            $model_pd->rollback();
            ouput_error('操作失败', array(), ERROR_CODE_DATABASE);
        }
    }
    /**
     * 获取提现信息
     */
    public function get_cash_apply_listOp(){
        if(isset($_REQUEST['curpage'])&&!empty($_REQUEST['curpage'])) {
            $curpage = $_REQUEST['curpage'];
        }
        else {
            $curpage = 1;
        }
        $begin = ($curpage-1)*20;
        $cash_list = Model()->table('pd_cash')->where(array('pdc_member_id'=>$this->member_info['member_id']))->order('pdc_add_time desc')->limit($begin.',20')->select();
        for($i=0;$i<count($cash_list);$i++) {
            if($cash_list[$i]['pdc_payment_state']==3) {
                $cash_list[$i]['pdc_amount'] = '+'.$cash_list[$i]['pdc_amount'];
            }
            else {
                $cash_list[$i]['pdc_amount'] = '-'.$cash_list[$i]['pdc_amount'];
            }
            if(empty($cash_list[$i]['pdc_payment_time'])) {
                $cash_list[$i]['pdc_payment_time'] = '';
            }
            if(empty($cash_list[$i]['pdc_deal_time'])) {
                $cash_list[$i]['pdc_deal_time'] = '';
            }
            $bank_icon = $this->_get_bank_icon($cash_list[$i]['pdc_bank_name']);
            $cash_list[$i]['icon'] = $bank_icon;
            if(empty($cash_list[$i]['cardtype'])) {
                $cash_list[$i]['cardtype'] = '银行卡';
            }
        }
        output_data($cash_list);
    }
    
    
    /*---------------以下是私有方法----------------------*/
    /**
     * order_pay下单支付预存款,
     * order_freeze下单冻结预存款,
     * order_cancel取消订单解冻预存款,
     * order_comb_pay下单支付被冻结的预存款,
     * recharge充值,
     * cash_apply申请提现冻结预存款,
     * cash_pay提现成功,
     * cash_del取消提现申请，解冻预存款,refund退款，
     * rebate_pay本土商家支付返利，
     * rebate_get用户获得返利金额
     */
    private function _get_order_pay_type($key) {
        
        $type_arr =  array(
            'order_pay'       => '购买支出',
            'order_freeze'    => '购买支出',
            'order_cancel'    => '订单退款',
            'order_comb_pay'  => '购买支出',
            'recharge'        => '账户充值',
            'cash_apply'      => '提现',
            'cash_pay'        => '提现',
            'cash_del'        => '提现失败',
            'refund'          => '订单退款',
            'rebate_pay'      => '商品销售',
            'rebate_get'      => '人脉返佣',
            'settle_account'  => '商品销售',
            'distribution_get'=> '分销返佣'
        );
        
        return $type_arr[$key];
    }
    
    /**
     * 商户预充值限制
     * 
     * @since 2015-10-09
     * @version 1.1
     * @param float    $pdc_amount  提现金额
     * @param boolean  $is_return   是否有返回值 
     * @param 
     */
    private function _limit_shop_predeposit($pdc_amount, $is_return = false) {
       
        // 确认商家身份
        $store_info = Model('store')->getOneStore(array('member_id' => $this->member_info['member_id']));
        if (!empty($store_info)) {
            $condition = array(
                'member_id' => $this->member_info['member_id'],
                'type'      => 1,
            );
            $predeposit = Model('store_pre_deposit')->getStorePredepositCount($condition);
            $amount_total = empty($predeposit['amount_total']) ? 0.00 : $predeposit['amount_total'];
            
            $num = $this->member_info['available_predeposit'] - $amount_total; //可提现最大金额 

            if ($num < $pdc_amount ) {
                if ($num <= 0) {
                    output_error('有【' . price_format($amount_total) . '】系统赠送金额被冻结,不能提现');
                } else {
                    output_error('有【' . price_format($amount_total) . '】系统赠送金额被冻结,可提现最大金额为【' . price_format($num) . '】');
                }
            }
        }

    } 
    
    /**
     * 获取提现金额信息
     */
    private function _get_shop_predeposit() {
        
        // 预定义
        $result = array();
        $result['predeposit']            = price_format($this->member_info['available_predeposit']); // 账户余额
        $result['predeposit_give']       = price_format(0.00); // 平台赠送金额
        $result['predeposit_width_draw'] = $result['predeposit'];   // 可提现金额
            
        $store_info = Model('store')->getOneStore(array('member_id' => $this->member_info['member_id']));
        if (!empty($store_info)) {
            $condition = array(
                'member_id' => $this->member_info['member_id'],
                'type'      => 1,
            );
            $predeposit = Model('store_pre_deposit')->getStorePredepositCount($condition);
            $amount_total = empty($predeposit['amount_total']) ? 0.00 : $predeposit['amount_total'];
            $num = $this->member_info['available_predeposit'] - $amount_total;
            
            $result['predeposit_give']       = price_format($amount_total);
            $result['predeposit_width_draw'] = price_format($num); 
        }
        
        return $result;
    }
    /**
     *  根据银行卡获取图片logo
     */
    private function _get_bank_icon($bank_name) {
        $bank_list = array(array('bank_name'=>'中国银行','icon'=>'http://pica.datatiny.com/banklogo/icbc.pn'),array('bank_name'=>'农业银行','icon'=>'http://pica.datatiny.com/banklogo/abc.pn'));
        foreach($bank_list as $item) {
            if($item['bank_name']==$bank_name) {
                return $item['icon'];
            }
        }
        return "http://pica.datatiny.com/banklogo/default.pn";
    }
}    

 
