<?php
/**
 * 购买
 *
 */

use Tpl;

defined('emall') or exit('Access Invalid!');

class member_buyControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     *
     * @param string $param ['cart_id'] 购物车ID|购买数量,若多件商品需要够好隔开,
     *                               如 1|8,2|2 表示里商品A有8件，商品B有2件
     * @param int $param ['ifcart']  是否购物车购买 0立即购买  1购物车购买
     */
    public function buy_step1Op()
    {
        $cart_id = explode(',', $_REQUEST['cart_id']);

        $logic_buy = logic('buy');

        //得到购买数据
        $result = $logic_buy->buyStep1($cart_id, $_REQUEST['ifcart'], $this->member_info['member_id'], $this->member_info['store_id']);

        if (!$result['state']) {
            output_error($result['msg']);
        } else {
            $result = $result['data'];
        }
        //整理数据
        $store_cart_list = array();
        $i=0;
        $total=0;
        foreach ($result['store_cart_list'] as $key => $value) {
            $store_cart_list[$i]['goods_list'] = $value;
            $store_cart_list[$i]['store_goods_total'] = del0($result['store_goods_total'][$key]);
            /*计算订单商家店铺的商品总素*/
            foreach($value as $k=>$v){
                $store_cart_list[$i]['store_goods_num_total'] +=$v['goods_num'];
                $total +=$v['goods_total'];

            }
            if (!empty($result['store_premiums_list'][$key])) {
                $result['store_premiums_list'][$i][0]['premiums'] = true;
                $result['store_premiums_list'][$i][0]['goods_total'] = 0.00;
                $store_cart_list[$i]['goods_list'][] = $result['store_premiums_list'][$key][0];
            }
            $store_cart_list[$i]['store_mansong_rule_list'] = $result['store_mansong_rule_list'][$key];
            $store_cart_list[$i]['store_voucher_list'] = $result['store_voucher_list'][$key];
            if (!empty($result['cancel_calc_sid_list'][$key])) {
                $store_cart_list[$i]['freight'] = '0';
                $store_cart_list[$i]['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            } else {
                $store_cart_list[$i]['freight'] = '1';
            }
            $store_cart_list[$i]['store_name'] = $value[0]['store_name'];

            $i++;

        }

        $buy_list = array();

        $buy_list['store_cart_list']      = $store_cart_list;
        $buy_list['freight_hash']         = $result['freight_list'];
        $buy_list['address_info']         = $result['address_info'];
        $buy_list['ifshow_offpay']        = $result['ifshow_offpay'];
        $buy_list['vat_hash']             = $result['vat_hash'];
        $buy_list['inv_info']             = $result['inv_info'];
        $buy_list['available_predeposit'] = del0($result['available_predeposit']);
        $buy_list['available_rc_balance'] = $result['available_rc_balance'];
        $buy_list['total_money']          = $total;

        output_data($buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     * @param string $param ['key']       唯一校验码
     * @param string $param ['cart_id'] 购物车ID|购买数量,若多件商品需要够好隔开,
     *                               如 1|8,2|2 表示里商品A有8件，商品B有2件
     * @param int $param ['ifcart']         是否购物车购买 0立即购买  1购物车购买
     * @param int $param ['address_id']     地址ID
     * @param string $param ['vat_hash']       第1步会给到
     * @param string $param ['offpay_hash']   货到付款会给到
     * @param string $param ['pay_name']      付款方式:在线支付/货到付款(online/offline)
     * @param string $param ['pd_pay']        积分(即预存款)支付(暂未涉及,可为0)
     * @param string $param ['rcb_pay']       充值卡支付(暂未涉及,可为0)
     * @vat_hash      $param['vat_hash']      增值税发票信息(step1可获取)
     * @param string $param ['voucher']       处理代金劵(暂未设置,可为空)
     *
     * 分销商品直接购买参数
     * @param int dis_store_id
     * @param int dis_member_id
     */
    public function buy_step2Op()
    {

        $param = array();
        $param['ifcart'] = $_REQUEST['ifcart'];
        $param['cart_id'] = explode(',', $_REQUEST['cart_id']);
        $param['address_id'] = $_REQUEST['address_id'];
        $param['vat_hash'] = $_REQUEST['vat_hash'];
        $param['offpay_hash'] = $_REQUEST['offpay_hash'];
        $param['offpay_hash_batch'] = $_REQUEST['offpay_hash_batch'];
        $param['pay_name'] = $_REQUEST['pay_name'];
        $param['invoice_id'] = $_REQUEST['invoice_id'];

        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', $_REQUEST['voucher']);
        if (!empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($voucher_t_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher[$store_id] = $value;
            }
        }
        $param['voucher'] = $voucher;

        //手机端暂时不做支付留言，页面内容太多了
        //$param['pay_message'] = json_decode($_POST['pay_message']);
        $param['pd_pay'] = $_REQUEST['pd_pay'];
        $param['rcb_pay'] = $_REQUEST['rcb_pay'];
        $param['password'] = $_REQUEST['password'];
        $param['fcode'] = $_REQUEST['fcode'];
        $param['order_from'] = 2;
        $logic_buy = logic('buy');

        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email']);
        if (!$result['state']) {
            output_error($result['msg']);
        }

        output_data(array('pay_sn' => $result['data']['pay_sn'],'order_sn'=>$result['data']['order_sn']));
    }

    /**
     * 验证交易密码
     *
     * @param string $param ['key']       唯一校验码
     * @param string $param ['password']  交易密码
     */
    public function check_passwordOp()
    {
        if (empty($_REQUEST['password'])) {
            output_error('参数错误');
        }

        $model_member = Model('member');

        $member_info = $model_member->getMemberInfoByID($this->member_info['member_id']);
        
        if (empty($member_info['member_paypwd'])) {
            output_error('暂未设置交易密码', array('is_jump' => 1, 'is_jump_tip' => '请点击跳转到设置交易密码页面') ,ERROR_CODE_OPERATE); 
        }
        
        // 交易密码次数限制
        $rs = $model_member->limit_input_paypwd_count($member_info, $_REQUEST['password']); 
        if ($rs['state'] == false) {
            output_error($rs['msg'], $rs['data']);
        }
        
        output_data('操作成功');
    }
    

    /**
     * 更换收货地址
     * @param string $param ['freight_hash']  购买第1步传的参数
     * @param int $param ['city_id']       城市id
     * @param int $param ['area_id']       区域ID
     * @param string
     */
    public function change_addressOp()
    {
        $logic_buy = Logic('buy');

        $data = $logic_buy->changeAddr($_REQUEST['freight_hash'], $_REQUEST['city_id'], $_REQUEST['area_id'], $this->member_info['member_id']);
        if (!empty($data) && $data['state'] == 'success') {
            foreach ($data['content'] as $key => $value) {
                 $data['content'][$key]=(int)$value;
            }
            output_data($data);
        } else {
            output_error('地址修改失败');
        }
    }

    /**
     * 本土商品下单
     *
     * @author chengyifei
     * @since  2015-08-03
     * @param string $param ['key']       唯一校验码
     * @param  int $param ['goods_id']  商品ID
     * @param  int $param ['store_id']  商户ID
     * @param  float $param ['money']     金额
     * @param  float $param ['pd_pay']     账号积分
     * @param int goods_amount 商品数量，非必填，如有值这支付金额=money * goods_amount
     * @param int o2o_order_type :1：面对面付；2：预售订单 (默认面对面付)
     */
    public function local_buyOp()
    {
        if (isset($this->member_info['is_buy']) && $this->member_info['is_buy'] != 1) {
            output_error('此用户无购买权限');
        }
        
        $money = isset($_REQUEST['money']) ? $_REQUEST['money'] : 0;
        if ($money == 0)
        {
            output_error('支付金额不能为零');
        }
        
        $logic_buy = Logic('buy');
        $param = array(
            'member_id' => $this->member_info['member_id'],
            'goods_id' => isset($_REQUEST['goods_id']) ? intval($_REQUEST['goods_id']) : 0,
            'store_id' => isset($_REQUEST['store_id']) ? intval($_REQUEST['store_id']) : 0,
            'money' => $money,
            'pd_pay' => isset($_REQUEST['pd_pay']) ? intval($_REQUEST['pd_pay']) : 0,
            'paypwd' => isset($_REQUEST['paypwd']) ? trim($_REQUEST['paypwd']) : '',
            'goods_amount' => isset($_REQUEST['goods_amount'])?intval($_REQUEST['goods_amount']):1,
            'o2o_order_type' => isset($_REQUEST['o2o_order_type']) ? intval($_REQUEST['o2o_order_type']) : 1,
        );
        
        $result = $logic_buy->localOrderAdd($param);

        if ($result['state'] == true)
        {
            $result_data = array('order_sn'=>$result['data']['order_sn'], 'invitation' => $result['data']['invitation'], 'order_state' =>$result['data']['order_state'],  'goods_name' => $result['data']['goods_name'], 'pay_sn'=>$result['data']['pay_sn'] , 'need_pay'=>$result['data']['need_pay'], 'order_amount'=>$result['data']['order_amount'], 'pd_amount'=>$result['data']['pd_amount'], 'buy_time'=>time());

            output_data($result_data);
        } else {
            output_error($result['msg']);
        }
    }


}

