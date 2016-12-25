<?php
/**
 * 购买行为
 *
 *

 */
defined('emall') or exit('Access Invalid!');
class buyLogic {

    /**
     * 会员信息
     * @var array
     */
    private $_member_info = array();

    /**
     * 下单数据
     * @var array
     */
    private $_order_data = array();

    /**
     * 表单数据
     * @var array
     */
    private $_post_data = array();

    /**
     * buy_1.logic 对象
     * @var obj
     */
    private $_logic_buy_1;

    public function __construct() {
        $this->_logic_buy_1 = Logic('buy_1');
    }

    /**
     * 购买第一步
     * @param unknown $cart_id
     * @param unknown $ifcart
     * @param unknown $member_id
     * @param unknown $store_id
     * @return Ambigous <multitype:unknown, multitype:unknown >
     */
    public function buyStep1($cart_id, $ifcart, $member_id, $store_id) {

        //得到购买商品信息
        if ($ifcart) {
            /*回到确认订单页面 更新一次购物车*/
            $this->edit_cart($cart_id);
            $result = $this->getCartList($cart_id, $member_id);
        } else {
            $result = $this->getGoodsList($cart_id, $member_id, $store_id);
        }

        if(!$result['state']) {
            return $result;
        }

        //得到页面所需要数据：收货地址、发票、代金券、预存款、商品列表等信息
        $result = $this->getBuyStep1Data($member_id,$result['data']);
        return $result;
    }


    private function edit_cart($cart_id){
     $buy_items = $this->_parseItems($cart_id);
     $model_cart = Model('cart');
        /*直接更新一次购物车数量， 如是直接从but_items取得数量， 后面逻辑修改太大，*/
        foreach( $buy_items as $up_cart_id=>$up_num){
            $data['goods_num'] = $up_num;
            $model_cart->editCart($data,array('cart_id'=>$up_cart_id));
         }
    }



    /**
     * 第一步：处理购物车
     *
     * @param array $cart_id 购物车
     * @param int $member_id 会员编号
     */
    public function getCartList($cart_id, $member_id) {
        $model_cart = Model('cart');

        //取得POST ID和购买数量
        $buy_items = $this->_parseItems($cart_id);
        if (empty($buy_items)) {
            return callback(false, '所购商品无效');
        }

        if (count($buy_items) > 50) {
            return callback(false, '一次最多只可购买50种商品');
        }

        //购物车列表
        $condition = array('cart_id'=>array('in',array_keys($buy_items)), 'buyer_id'=>$member_id);
        $cart_list	= $model_cart->listCart('db', $condition);
        foreach($cart_list as $k=>$v){
            $cart_list[$k]['goods_spec']=unserialize($v['goods_spec']);
        }
        //购物车列表 [得到最新商品属性及促销信息]
        $cart_list = $this->_logic_buy_1->getGoodsCartList($cart_list);

        //商品列表 [优惠套装子商品与普通商品同级罗列]
        $goods_list = $this->_getGoodsList($cart_list);

        //以店铺下标归类
        $store_cart_list = $this->_getStoreCartList($cart_list);

        return callback(true, '', array('goods_list' => $goods_list, 'store_cart_list' => $store_cart_list));

    }

    /**
     * 第一步：处理立即购买
     *
     * @param array $cart_id 购物车
     * @param int $member_id 会员编号
     * @param int $store_id 店铺编号
     */
    public function getGoodsList($cart_id, $member_id, $store_id) {

        //取得POST ID和购买数量
        $buy_items = $this->_parseItems($cart_id);
        if (empty($buy_items)) {
            return callback(false, '所购商品无效');
        }

        $goods_id = key($buy_items);
        $quantity = current($buy_items);

        //商品信息[得到最新商品属性及促销信息]
        $goods_info = $this->_logic_buy_1->getGoodsOnlineInfo($goods_id,intval($quantity));
        if(empty($goods_info)) {
            return callback(false, '商品已下架或不存在');
        }

        //不能购买自己店铺的商品
        if ($goods_info['store_id'] == $store_id) {
            return callback(false, '不能购买自己店铺的商品');
        }

        //进一步处理数组
        $store_cart_list = array();
        $goods_list = array();
        $goods_list[0] = $store_cart_list[$goods_info['store_id']][0] = $goods_info;

        return callback(true, '', array('goods_list' => $goods_list, 'store_cart_list' => $store_cart_list));
    }

    /**
     * 购买第一步：返回商品、促销、地址、发票等信息，然后交前台抛出
     * @param unknown $member_id
     * @param unknown $data 商品信息
     * @return
     */
    public function getBuyStep1Data($member_id, $data) {
        list($goods_list,$store_cart_list) = $data;
        $goods_list = $data['goods_list'];
        $store_cart_list = $data['store_cart_list'];

        //定义返回数组
        $result = array();

        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        list($store_cart_list,$store_goods_total) = $this->_logic_buy_1->calcCartList($store_cart_list);
        $result['store_cart_list'] = $store_cart_list;
        $result['store_goods_total'] = $store_goods_total;

        //取得店铺优惠 - 满即送(赠品列表，店铺满送规则列表)
        list($store_premiums_list,$store_mansong_rule_list) = $this->_logic_buy_1->getMansongRuleCartListByTotal($store_goods_total);
        $result['store_premiums_list'] = $store_premiums_list;
        $result['store_mansong_rule_list'] = $store_mansong_rule_list;

        //重新计算优惠后(满即送)的店铺实际商品总金额
        $store_goods_total = $this->_logic_buy_1->reCalcGoodsTotal($store_goods_total,$store_mansong_rule_list,'mansong');

        //返回店铺可用的代金券
        $store_voucher_list = $this->_logic_buy_1->getStoreAvailableVoucherList($store_goods_total, $member_id);
        $result['store_voucher_list'] = $store_voucher_list;

        //返回需要计算运费的店铺ID数组 和 不需要计算运费(满免运费活动的)店铺ID及描述
        list($need_calc_sid_list,$cancel_calc_sid_list) = $this->_logic_buy_1->getStoreFreightDescList($store_goods_total);
        $result['need_calc_sid_list'] = $need_calc_sid_list;
        $result['cancel_calc_sid_list'] = $cancel_calc_sid_list;

        //将商品ID、数量、运费模板、运费序列化，加密，输出到模板，选择地区AJAX计算运费时作为参数使用
        $freight_list = $this->_logic_buy_1->getStoreFreightList($goods_list,array_keys($cancel_calc_sid_list));
        $result['freight_list'] = $this->buyEncrypt($freight_list, $member_id);

        //输出用户默认收货地址
        $result['address_info'] = Model('address')->getDefaultAddressInfo(array('member_id'=>$member_id));

        //输出有货到付款时，在线支付和货到付款及每种支付下商品数量和详细列表
        $pay_goods_list = $this->_logic_buy_1->getOfflineGoodsPay($goods_list);
        if (!empty($pay_goods_list['offline'])) {
            $result['pay_goods_list'] = $pay_goods_list;
            $result['ifshow_offpay'] = true;
        } else {
            //如果所购商品只支持线上支付，支付方式不允许修改
            $result['deny_edit_payment'] = true;
        }

        //发票 :只有所有商品都支持增值税发票才提供增值税发票
        foreach ($goods_list as $goods) {
        	if (!intval($goods['goods_vat'])) {
        	    $vat_deny = true;break;
        	}
        }
        //不提供增值税发票时抛出true(模板使用)
        $result['vat_deny'] = $vat_deny;
        $result['vat_hash'] = $this->buyEncrypt($result['vat_deny'] ? 'deny_vat' : 'allow_vat', $member_id);

        //输出默认使用的发票信息
        $inv_info = Model('invoice')->getDefaultInvInfo(array('member_id'=>$member_id));
        if ($inv_info['inv_state'] == '2' && !$vat_deny) {
            $inv_info['content'] = '增值税发票 '.$inv_info['inv_company'].' '.$inv_info['inv_code'].' '.$inv_info['inv_reg_addr'];
        } elseif ($inv_info['inv_state'] == '2' && $vat_deny) {
            $inv_info = array();
            $inv_info['content'] = '不需要发票';
        } elseif (!empty($inv_info)) {
            $inv_info['content'] = '普通发票 '.$inv_info['inv_title'].' '.$inv_info['inv_content'];
        } else {
            $inv_info = array();
            $inv_info['content'] = '不需要发票';
        }
        $result['inv_info'] = $inv_info;

        $buyer_info	= Model('member')->getMemberInfoByID($member_id);
        if (floatval($buyer_info['available_predeposit']) > 0) {
            $result['available_predeposit'] = $buyer_info['available_predeposit'];
        }
        if (floatval($buyer_info['available_rc_balance']) > 0) {
            $result['available_rc_balance'] = $buyer_info['available_rc_balance'];
        }
        $result['member_paypwd'] = $buyer_info['member_paypwd'] ? true : false;

        return callback(true,'',$result);
    }

    /**
     * 购买第二步
     * @param array $post
     * @param int $member_id
     * @param string $member_name
     * @param string $member_email
     * @return array
     */
    public function buyStep2($post, $member_id, $member_name, $member_email) {

        $this->_member_info['member_id'] = $member_id;
        $this->_member_info['member_name'] = $member_name;
        $this->_member_info['member_email'] = $member_email;
        $this->_post_data = $post;

        try {

            $model = Model('order');
            $model->beginTransaction();

            //第1步 表单验证
            $this->_createOrderStep1();

            //第2步 得到购买商品信息
            $this->_createOrderStep2();

            //第3步 得到购买相关金额计算等信息
            $this->_createOrderStep3();
    
            //第4步 生成订单
            $this->_createOrderStep4();

            //第5步 处理预存款
            $this->_createOrderStep5();
            $model->commit();

            //第6步 订单后续处理
            $this->_createOrderStep6();

            return callback(true,'',$this->_order_data);

        }catch (Exception $e){
            $model->rollback();
            return callback(false, $e->getMessage());
        }

    }

    /**
     * 爱大腿生成订单功能
     */
    public function adt_buyStep2($post, $member_id, $member_name, $member_email){

        $this->_member_info['member_id'] = $member_id;
        $this->_member_info['member_name'] = $member_name;
        $this->_member_info['member_email'] = $member_email;
        $this->_post_data = $post;

        try {

            $model = Model('order');
            $model->beginTransaction();
            //第1步,检查收货地址,商品参数，获取附近的店铺，检查营业时间和收货时间是否匹配
            $this->adt_createOrderStep1();

            //第2步，查商品信息，保留可购买的商品，商品真实价格，判断库存，促销价，促销库存。检查优惠券是否可用，计算商品总金额
            $this->adt_createOrderStep2();

            //第3步，生成订单
            $this->adt_createOrderStep3();

            //第4步，更新库存和销量，清空购物车，发送提醒类消息
            $this->adt_createOrderStep4();

            $model->commit();
            return callback(true,'',$this->_order_data);

        }catch (Exception $e){
            $model->rollback();
            return callback(false, $e->getMessage());
        }

    }


    /**
     *检查收货地址,商品参数
     */
    private function adt_createOrderStep1() {
        $post = $this->_post_data;

        //取得商品ID和购买数量
        $input_buy_items = $this->_parseItems($post['goods_buy']);
        if (empty($input_buy_items)) {
            throw new Exception('所购商品无效');
        }

        //验证收货地址
        $input_address_id = intval($post['address_id']);
        if ($input_address_id <= 0) {
            throw new Exception('请选择收货地址');
        } else {
            $input_address_info = Model('address_league')->where(array('address_id'=>$input_address_id))->master(true)->find();
            if ($input_address_info['member_id'] != $this->_member_info['member_id']) {
                throw new Exception('请选择收货地址');
            }
        }

        //收货地址城市编号
        $input_city_id = intval($input_address_info['city_id']);

        //离该收货地址最近的店铺
        $store_info=Model('store')->adt_get_store_by_lication(floatval($input_address_info['lat']),floatval($input_address_info['lng']));
        if(empty($store_info)){
            throw new Exception('系统异常');
        }
        if(1!=$store_info['open_state']){
            throw new Exception('店铺休息中，不能下单');
        }

        //计算期望收货时间，最后时限，时间戳。
        $add_start_time=date('Y-m-d H:i:s');
        $add_time=date('Y-m-d H:i:s',strtotime('+1 hours'));
        if($post['time']!=0) {
            $input_time = explode('-', $post['time']);
            if(isset($input_time[1])) {
                $input_end_time = trim($input_time[1]);
                $input_start_time = trim($input_time[0]);
                $add_time = date('Y-m-d ') . $input_end_time . ':00';
                $add_start_time = date('Y-m-d ') . $input_start_time . ':00';
            }
        }
        $add_day=intval($post['date']);
        if($add_day!=0) {
            $hope_time_str = $add_time . ' +' . $add_day . ' day';
            $hope_start_time_str = $add_start_time . ' +' . $add_day . ' day';
        }else{
            $hope_time_str = $add_time;
            $hope_start_time_str = $add_start_time;
        }
        $hope_time=strtotime($hope_time_str);
        $hope_start_time=strtotime($hope_start_time_str);
        if(false===$hope_time){
            $hope_time=strtotime('+1 hours');
        }
        if(false===$hope_start_time){
            $hope_start_time=time();
        }

        $ship_time=explode('-',$store_info['ship_time']);
        if(isset($ship_time[0])&&isset($ship_time[1])){
            $ship_start_time=strtotime(date('Y-m-d ',$hope_start_time).$ship_time[0]);
            $ship_end_time=strtotime(date('Y-m-d ',$hope_time).$ship_time[1]);
            if($ship_start_time && $ship_end_time){             //店铺营业时间有效，判断收货时间是否在营业时间内
                if($hope_start_time<$ship_start_time || $hope_time>$ship_end_time){
//                    throw new Exception('收货时间不在店铺配送时间内');
                }
            }
        }



        //保存数据
        $this->_order_data['input_buy_items'] = $input_buy_items;
        $this->_order_data['hope_receive_time'] = $post['hope_receive_time'];
        $this->_order_data['hope_time'] =$hope_time;
        $this->_order_data['input_address_info'] = $input_address_info;
        $this->_order_data['store_info'] = $store_info;
        $this->_order_data['store_id'] = $store_info['store_id'];
        $this->_order_data['order_from'] =1;
        $this->_order_data['input_city_id'] = $input_city_id;
    }

    /**
     * 查商品信息，保留可购买的商品，商品真实价格，判断库存，促销价，促销库存。商品不可购买或者库存不足不允许购买
     */
    private function adt_createOrderStep2()
    {
        $post = $this->_post_data;
        $input_buy_items = $this->_order_data['input_buy_items'];
        $store_id=$this->_order_data['store_id'];
        $goods_store_info=array();
        $order_goods=array();
        $sum=$count=0;
        foreach($input_buy_items as $key=>$value){
            //几个设备同时点生成订单的时候，会导致库存为负数，必须加锁
            $this_goods_info_league=Model('goods_league')->where(array('goods_id'=>$key,'league_store_id'=>$store_id,'league_goods_verify'=>1))->lock(true)->find();
            if(empty($this_goods_info_league)){
                throw new Exception('商品不可购买') ;
            }
            $this_price=(1==$this_goods_info_league['league_goods_promotion_type'])?$this_goods_info_league['league_goods_promotion_price']:$this_goods_info_league['league_goods_price'];
            $this_storage=(1==$this_goods_info_league['league_goods_promotion_type'])?$this_goods_info_league['league_goods_promotion_storage']:$this_goods_info_league['league_goods_storage'];
            if($this_storage<$value){
                throw new Exception('库存不足');
            }
            $this_goods_info=array();
            $this_goods_info['goods_id']=$this_goods_info_league['goods_id'];
            $this_goods_info['store_id']=$this_goods_info_league['store_id'];
            $this_goods_info['gc_id']=$this_goods_info_league['gc_id'];
            $this_goods_info['goods_name']=$this_goods_info_league['goods_name'];
            $this_goods_info['goods_image']=$this_goods_info_league['goods_image'];
            $this_goods_info['goods_size']=$this_goods_info_league['goods_size'];
            $this_goods_info['goods_price']=$this_price;
            $this_goods_info['goods_num']=$value;
            $this_goods_info['goods_price_total']=del0($this_price * $value);
            $this_goods_info['is_promotion']=$this_goods_info_league['league_goods_promotion_type'];
            $this_goods_info['commis_rate']=$this_goods_info_league['commis_rate'];
            $order_goods[]=$this_goods_info;
            $sum += $this_price * $value;
            $count++;

            //发布商品的店铺的信息
            if(empty($goods_store_info)){
                $goods_store_info=Model('store')->getStoreInfoByID($this_goods_info_league['store_id']);
            }
        }

        //1.检查优惠券是否可用，2.获取优惠金额,3.使用优惠券
        $coupon_id=$post['coupon_id'];
        $coupon_info=Model('member_coupon_league')->lock(true)->find($coupon_id);
        $member_id = $this->_member_info['member_id'];
        if(empty($coupon_info)){
            throw new Exception('优惠券不存在');
        }
        if($coupon_info['member_id']!=$member_id || $coupon_info['is_used']!=0){
            throw new Exception('优惠券不可用');
        }
        if($coupon_info['is_validity']==1 && $coupon_info['validity_start_time']<=time() &&  $coupon_info['validity_end_time']>=time()){
            throw new Exception('优惠券不可用');
        }
        if($coupon_info['coupon_type']==2 && $coupon_info['coupon_limit_amount']<$sum){
            throw new Exception('优惠券不可用');
        }
        if($coupon_info['coupon_type']==1){
            $carrage=0;
        }else{
            //运费
            $carrage=($sum<ADT_FREE_CARRIAGE_LEAVE)?ADT_CARRIAGE:0;
        }
        if($coupon_info['coupon_type']==2){
            $sum-=$coupon_info['coupon_amount'];
            if($sum<0) $sum=0;
        }

        $this->_order_data['coupon_id']=$coupon_id;
        $this->_order_data['goods_amount']=$sum;
        $this->_order_data['order_amount']=$sum+$carrage;
        $this->_order_data['shipping_fee']=$carrage;
        $this->_order_data['goods_list']=$order_goods;
        $this->_order_data['goods_store_info']=$goods_store_info;
    }

    /**
     * 爱大腿，生成订单
     */
    public function adt_createOrderStep3(){

        extract($this->_order_data);

        $member_id = $this->_member_info['member_id'];
        $member_name = $this->_member_info['member_name'];
        $member_email = $this->_member_info['member_email'];

        $model_order = Model('order');

        //存储生成的订单数据
        $order_list = array();
        //存储通知信息
        $notice_list = array();
        //商品id，和销售数量，更新销量
        $goods_buy_quantity = array();


        $pay_sn = $this->_logic_buy_1->makePaySn($member_id);
        $order_pay = array();
        $order_pay['pay_sn'] = $pay_sn;
        $order_pay['buyer_id'] = $member_id;
        $order_pay_id = $model_order->addOrderPay($order_pay);
        if (!$order_pay_id) {
            throw new Exception('订单保存失败[未生成支付单]');
        }

        //转化收货人信息
        list($reciver_info,$reciver_name) = $this->_logic_buy_1->getReciverAddr($input_address_info);

        $order = array();
        $order_common = array();
        $order_goods = array();

        $order_sn=$this->_logic_buy_1->makeOrderSn($order_pay_id);
        $order['order_sn'] = $order_sn;
        $order['pay_sn'] = $pay_sn;
        $order['store_id'] = $goods_store_info['store_id'];
        $order['store_name'] = $goods_store_info['store_name'];
        $order['buyer_id'] = $member_id;
        $order['buyer_name'] = $member_name;
        $order['buyer_email'] = $member_email;
        $order['add_time'] = TIMESTAMP;
        $order['payment_code'] = '';
        $order['order_state'] =  ORDER_STATE_NEW;
        $order['order_amount'] = $order_amount;
        $order['goods_amount'] = $goods_amount;
        $order['shipping_fee'] = $shipping_fee;
        $order['order_from'] = $order_from;
        $order['order_type'] = 3;
        $order['league_store_id'] = $store_id;
        $order['league_store_name'] = $store_info['store_name'];
        $order['hope_receive_time'] = $hope_receive_time;
        $order['hope_time'] = $hope_time;
        $order['is_get_quickly'] = $this->_post_data['is_get_quickly'];
        $order_id = $model_order->addOrder($order);
        if (!$order_id) {
            throw new Exception('订单保存失败[未生成订单数据]');
        }
        $order['order_id'] = $order_id;
        $order_list[$order_id] = $order;

        $order_common['order_id'] = $order_id;
        $order_common['store_id'] = 0;
        $order_common['order_message'] = $this->_post_data['order_message'];
        $order_common['reciver_info']= $reciver_info;
        $order_common['reciver_name'] = $reciver_name;
        $order_common['reciver_city_id'] = $input_city_id;
        $order_common['lat'] = $input_address_info['lat'];
        $order_common['lng'] = $input_address_info['lng'];
        $order_id = $model_order->addOrderCommon($order_common);
        if (!$order_id) {
            throw new Exception('订单保存失败[未生成订单扩展数据]');
        }

        //生成order_goods订单商品数据
        $i = 0;
        foreach ($goods_list as $goods_info) {
            $order_goods[$i]['goods_spec'] = $goods_info['goods_size'];
            $order_goods[$i]['order_id'] = $order_id;
            $order_goods[$i]['goods_id'] = $goods_info['goods_id'];
            $order_goods[$i]['goods_name'] = $goods_info['goods_name'];
            $order_goods[$i]['goods_price'] = $goods_info['goods_price'];
            $order_goods[$i]['goods_num'] = $goods_info['goods_num'];
            $order_goods[$i]['goods_image'] = $goods_info['goods_image'];
            $order_goods[$i]['goods_pay_price'] = $goods_info['goods_price_total'];
            $order_goods[$i]['store_id'] = $goods_info['store_id'];
            $order_goods[$i]['buyer_id'] = $member_id;
            $order_goods[$i]['goods_type'] = 1;
            $order_goods[$i]['promotions_id'] =  0;
            $order_goods[$i]['commis_rate'] = $goods_info['commis_rate'];
            $order_goods[$i]['gc_id'] = $goods_info['gc_id'];
            $order_goods[$i]['league_store_id'] = $store_id;
            $order_goods[$i]['league_store_name'] = $store_info['store_name'];
            $order_goods[$i]['is_promotion'] =$goods_info['is_promotion'];
            $goods_buy_quantity[$store_id.'_'.$goods_info['goods_id']]=array(
                'quantity'=>$goods_info['goods_num'],
                'is_promotion'=>$goods_info['is_promotion'],
            );
            $i++;
        }

        $insert = $model_order->addOrderGoods($order_goods);
        if (!$insert) {
            throw new Exception('订单保存失败[未生成商品数据]');
        }


        $order_log = array(
            'order_id' => $order_id,
            'log_msg' => '待支付',
            'log_time' => TIMESTAMP,
            'log_role' => '买家',
            'log_user' => $member_name,
            'log_orderstate' => ORDER_STATE_NEW,
        );
        $insert = Model('order_log')->insert($order_log);
        if (!$insert) {
            throw new Exception('订单日志写入失败');
        }

        //使用优惠券
        if($coupon_id) {
            $member_coupon = array(
                'id' => $coupon_id,
                'is_used' => 1,
                'use_time' => time(),
            );
            $res=Model('member_coupon_league')->update($member_coupon);
            if(!$res){
                throw new Exception('系统异常，礼券使用失败');
            }
            $coupon_use = array(
                'member_id' => $member_id,
                'order_id' => $order_id,
                'member_coupon_id' => $coupon_id,
                'add_time' => time(),
            );
            $res=Model('coupon_use_league')->insert($coupon_use);
            if(!$res){
                throw new Exception('系统异常，礼券使用失败');
            }
        }


        //存储商家发货提醒数据
        $notice_list['new_order'][$store_id] = array('order_sn' => $order['order_sn']);

        //保存数据
        $this->_order_data['order_id'] = $order_id;
        $this->_order_data['order_sn'] = $order_sn;
        $this->_order_data['pay_sn'] = $pay_sn;
        $this->_order_data['order_list'] = $order_list;
        $this->_order_data['notice_list'] = $notice_list;
        $this->_order_data['goods_buy_quantity'] = $goods_buy_quantity;
    }

    /**
     * 爱大腿，生成订单第4步，清空购物车信息，更新销量库存等数据，发送通知消息
     */

    private function adt_createOrderStep4() {
        $goods_buy_quantity = $this->_order_data['goods_buy_quantity'];

        $notice_list = $this->_order_data['notice_list'];

        //变更源商品库存和销量
//        QueueClient::push('adt_createOrderUpdateStorage', $goods_buy_quantity);
        $this->adt_createOrderUpdateStorage($goods_buy_quantity);

        //删除购物车中的商品
        Model('cart_league')->where(array('buyer_id'=>$this->_member_info['member_id']))->delete();

        //发送提醒类信息
        if (!empty($notice_list)) {
            foreach ($notice_list as $code => $value) {
                QueueClient::push('sendStoreMsg', array('code' => $code, 'store_id' => key($value), 'param' => current($value)));
            }
        }

    }

    /**
     * 下单变更库存销量
     * @param unknown $goods_buy_quantity
     */
    public function adt_createOrderUpdateStorage($goods_buy_quantity) {
        $model_goods = Model('goods_league');
        $model_goods_source = Model('goods');
        foreach ($goods_buy_quantity as $key=> $quantity_info) {
            $data = array();
            $source_data = array();
            $source_data['goods_salenum'] = array('exp','goods_salenum+'.$quantity_info['quantity']);
            $data['league_goods_storage'] = array('exp','league_goods_storage-'.$quantity_info['quantity']);
            if($quantity_info['is_promotion']){
                $data['league_goods_promotion_storage'] = array('exp','league_goods_promotion_storage-'.$quantity_info['quantity']);
            }
            list($store_id,$goods_id)=explode('_',$key);
            $where=array();
            $where['goods_id']=$goods_id;
            $result_source = $model_goods_source->where($where)->update($source_data);
            $where['league_store_id']=$store_id;
            $result = $model_goods->where($where)->update($data);
            if (!$result || !$result_source) {
                throw new Exception('变更商品库存与销量失败');
            }
        }
    }

    /**
     * 删除购物车商品
     * @param unknown $ifcart
     * @param unknown $cart_ids
     */
    public function delCart($ifcart, $member_id, $cart_ids) {
        if (!$ifcart || !is_array($cart_ids)) return;
        $cart_id_str = implode(',',$cart_ids);
        if (preg_match('/^[\d,]+$/',$cart_id_str)) {
            QueueClient::push('delCart', array('buyer_id'=>$member_id,'cart_ids'=>$cart_ids));
        }
    }
    
    /**
     * 选择不同地区时，异步处理并返回每个店铺总运费以及本地区是否能使用货到付款
     * 如果店铺统一设置了满免运费规则，则运费模板无效
     * 如果店铺未设置满免规则，且使用运费模板，按运费模板计算，如果其中有商品使用相同的运费模板，则两种商品数量相加后再应用该运费模板计算（即作为一种商品算运费）
     * 如果未找到运费模板，按免运费处理
     * 如果没有使用运费模板，商品运费按快递价格计算，运费不随购买数量增加
     */
    public function changeAddr($freight_hash, $city_id, $area_id, $member_id) {
        //$city_id计算运费模板,$area_id计算货到付款
        $city_id = intval($city_id);
        $area_id = intval($area_id);
        if ($city_id <= 0 || $area_id <= 0) return null;

        //将hash解密，得到运费信息(店铺ID，运费,运费模板ID,购买数量),hash内容有效期为1小时
        $freight_list = $this->buyDecrypt($freight_hash, $member_id);
    
        //算运费
        $store_freight_list = $this->_logic_buy_1->calcStoreFreight($freight_list, $city_id);
        $data = array();
        $data['state'] = empty($store_freight_list) ? 'fail' : 'success';
        $data['content'] = $store_freight_list;
        //是否能使用货到付款(只有包含平台店铺的商品才会判断)
        //$if_include_platform_store = array_key_exists(DEFAULT_PLATFORM_STORE_ID,$freight_list['iscalced']) || array_key_exists(DEFAULT_PLATFORM_STORE_ID,$freight_list['nocalced']);
    
        $offline_store_id_array = Model('store')->getOwnShopIds();
        $order_platform_store_ids = array();
    
        if (is_array($freight_list['iscalced']))
        foreach (array_keys($freight_list['iscalced']) as $k)
        if (in_array($k, $offline_store_id_array))
            $order_platform_store_ids[$k] = null;
    
        if (is_array($freight_list['nocalced']))
        foreach (array_keys($freight_list['nocalced']) as $k)
        if (in_array($k, $offline_store_id_array))
            $order_platform_store_ids[$k] = null;
    
        if ($order_platform_store_ids) {
            $allow_offpay_batch = Model('offpay_area')->checkSupportOffpayBatch($area_id, array_keys($order_platform_store_ids));
    
            //JS验证使用
            $data['allow_offpay'] = array_filter($allow_offpay_batch) ? '1' : '0';
            $data['allow_offpay_batch'] = $allow_offpay_batch;
        } else {
            //JS验证使用
            $data['allow_offpay'] = '0';
            $data['allow_offpay_batch'] = array();
        }

        //PHP验证使用
        $data['offpay_hash'] = $this->buyEncrypt($data['allow_offpay'] ? 'allow_offpay' : 'deny_offpay', $member_id);
        $data['offpay_hash_batch'] = $this->buyEncrypt($data['allow_offpay_batch'], $member_id);

        return $data;
    }
    
    /**
     * 验证F码
     * @param int $goods_commonid
     * @param string $fcode
     * @return array
     */
    public function checkFcode($goods_commonid, $fcode) {
        $fcode_info = Model('goods_fcode')->getGoodsFCode(array('goods_commonid' => $goods_commonid,'fc_code' => $fcode,'fc_state' => 0));
        if ($fcode_info) {
            return callback(true,'',$fcode_info);
        } else {
            return callback(false,'F码错误');
        }
    }

    /**
     * 订单生成前的表单验证与处理
     *
     */
    private function _createOrderStep1() {
        $post = $this->_post_data;

        //取得商品ID和购买数量
        $input_buy_items = $this->_parseItems($post['cart_id']);
        if (empty($input_buy_items)) {
            throw new Exception('所购商品无效');
        }

        //验证收货地址
        $input_address_id = intval($post['address_id']);
        if ($input_address_id <= 0) {
            throw new Exception('请选择收货地址');
        } else {
            $input_address_info = Model('address')->getAddressInfo(array('address_id'=>$input_address_id));
            if ($input_address_info['member_id'] != $this->_member_info['member_id']) {
                throw new Exception('请选择收货地址');
            }
        }

        //收货地址城市编号
        $input_city_id = intval($input_address_info['city_id']);

        //是否开增值税发票
        $input_if_vat = $this->buyDecrypt($post['vat_hash'], $this->_member_info['member_id']);
        if (!in_array($input_if_vat,array('allow_vat','deny_vat'))) {
            throw new Exception('订单保存出现异常[值税发票出现错误]，请重试');
        }
        $input_if_vat = ($input_if_vat == 'allow_vat') ? true : false;

        //是否支持货到付款
        $input_if_offpay = $this->buyDecrypt($post['offpay_hash'], $this->_member_info['member_id']);
        if (!in_array($input_if_offpay,array('allow_offpay','deny_offpay'))) {
            throw new Exception('订单保存出现异常[货到付款验证错误]，请重试');
        }
        $input_if_offpay = ($input_if_offpay == 'allow_offpay') ? true : false;

        // 是否支持货到付款 具体到各个店铺
        $input_if_offpay_batch = $this->buyDecrypt($post['offpay_hash_batch'], $this->_member_info['member_id']);
        if (!is_array($input_if_offpay_batch)) {
            throw new Exception('订单保存出现异常[部分店铺付款方式出现异常]，请重试');
        }

        //付款方式:在线支付/货到付款(online/offline)
        if (!in_array($post['pay_name'],array('online','offline'))) {
            throw new Exception('付款方式错误，请重新选择');
        }
        $input_pay_name = $post['pay_name'];

        //验证发票信息
        if (!empty($post['invoice_id'])) {
            $input_invoice_id = intval($post['invoice_id']);
            if ($input_invoice_id > 0) {
                $input_invoice_info = Model('invoice')->getinvInfo(array('inv_id'=>$input_invoice_id));
                if ($input_invoice_info['member_id'] != $this->_member_info['member_id']) {
                    throw new Exception('请正确填写发票信息');
                }
            }
        }

        //验证代金券
        $input_voucher_list = array();
        if (!empty($post['voucher']) && is_array($post['voucher'])) {
            foreach ($post['voucher'] as $store_id => $voucher) {
                if (preg_match_all('/^(\d+)\|(\d+)\|([\d.]+)$/',$voucher,$matchs)) {
                    if (floatval($matchs[3][0]) > 0) {
                        $input_voucher_list[$store_id]['voucher_t_id'] = $matchs[1][0];
                        $input_voucher_list[$store_id]['voucher_price'] = $matchs[3][0];
                    }
                }
            }
        }

        //保存数据
        $this->_order_data['input_buy_items'] = $input_buy_items;
        $this->_order_data['input_city_id'] = $input_city_id;
        $this->_order_data['input_pay_name'] = $input_pay_name;
        $this->_order_data['input_if_offpay'] = $input_if_offpay;
        $this->_order_data['input_if_offpay_batch'] = $input_if_offpay_batch;
        $this->_order_data['input_pay_message'] = $post['pay_message'];
        $this->_order_data['input_address_info'] = $input_address_info;
        $this->_order_data['input_invoice_info'] = $input_invoice_info;
        $this->_order_data['input_voucher_list'] = $input_voucher_list;
        $this->_order_data['order_from'] = $post['order_from'] == 2 ? 2 : 1;

    }

    /**
     * 得到购买商品信息
     *
     */
    private function _createOrderStep2() {
        $post = $this->_post_data;
        $input_buy_items = $this->_order_data['input_buy_items'];

        if ($post['ifcart']) {
            //购物车列表
            $model_cart = Model('cart');
            $condition = array('cart_id'=>array('in',array_keys($input_buy_items)),'buyer_id'=>$this->_member_info['member_id']);
            $cart_list	= $model_cart->listCart('db',$condition);

            //购物车列表 [得到最新商品属性及促销信息]
            $cart_list = $this->_logic_buy_1->getGoodsCartList($cart_list);

            //商品列表 [优惠套装子商品与普通商品同级罗列]
            $goods_list = $this->_getGoodsList($cart_list);

            //以店铺下标归类
            $store_cart_list = $this->_getStoreCartList($cart_list);
        } else {
            //来源于直接购买
            $goods_id = key($input_buy_items);
            $quantity = current($input_buy_items);

            //商品信息[得到最新商品属性及促销信息]
            $goods_info = $this->_logic_buy_1->getGoodsOnlineInfo($goods_id,intval($quantity));
            if(empty($goods_info)) {
                throw new Exception('商品已下架或不存在');
            }

            //分销商品直接购买功能(如果店铺不可分销，则不算该店铺分销的)。不算分销也要加这几个字段
//            if(isset($_REQUEST['dis_member_id'])||isset($_REQUEST['dis_store_id'])) {
            $member_id=isset($_REQUEST['dis_member_id'])?intval($_REQUEST['dis_member_id']):0;
            $store_id=isset($_REQUEST['dis_store_id'])?intval($_REQUEST['dis_store_id']):0;
            $distribution_info = Logic('distribution')->get_cart_data($goods_info['goods_id'],$store_id,$member_id);
            $goods_info=array_merge($goods_info,$distribution_info);
//            }

            //进一步处理数组
            $store_cart_list = array();
            $goods_list = array();
            $goods_list[0] = $store_cart_list[$goods_info['store_id']][0] = $goods_info;
        }

        //F码验证
        $fc_id = $this->_checkFcode($goods_list, $post['fcode']);
        if(!$fc_id) {
            throw new Exception('F码商品验证错误');
        }
        //保存数据
        $this->_order_data['goods_list'] = $goods_list;
        $this->_order_data['store_cart_list'] = $store_cart_list;
        if ($fc_id > 0) {
            $this->_order_data['fc_id'] = $fc_id;
        }

    }

    /**
     * 得到购买相关金额计算等信息
     *
     */
    private function _createOrderStep3() {
        $goods_list = $this->_order_data['goods_list'];
        $store_cart_list = $this->_order_data['store_cart_list'];
        $input_voucher_list = $this->_order_data['input_voucher_list'];
        $input_city_id = $this->_order_data['input_city_id'];

        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        list($store_cart_list,$store_goods_total) = $this->_logic_buy_1->calcCartList($store_cart_list);

        //取得店铺优惠 - 满即送(赠品列表，店铺满送规则列表)
        list($store_premiums_list,$store_mansong_rule_list) = $this->_logic_buy_1->getMansongRuleCartListByTotal($store_goods_total);

        //重新计算店铺扣除满即送后商品实际支付金额
        $store_final_goods_total = $this->_logic_buy_1->reCalcGoodsTotal($store_goods_total,$store_mansong_rule_list,'mansong');

        //得到有效的代金券
        $input_voucher_list = $this->_logic_buy_1->reParseVoucherList($input_voucher_list,$store_goods_total,$this->_member_info['member_id']);

        //重新计算店铺扣除优惠券送商品实际支付金额
        $store_final_goods_total = $this->_logic_buy_1->reCalcGoodsTotal($store_final_goods_total,$input_voucher_list,'voucher');

        //计算每个店铺(所有店铺级优惠活动)总共优惠多少
        $store_promotion_total = $this->_logic_buy_1->getStorePromotionTotal($store_goods_total, $store_final_goods_total);

        //计算每个店铺运费
        list($need_calc_sid_list,$cancel_calc_sid_list) = $this->_logic_buy_1->getStoreFreightDescList($store_final_goods_total);
        $freight_list = $this->_logic_buy_1->getStoreFreightList($goods_list,array_keys($cancel_calc_sid_list));
        $store_freight_total = $this->_logic_buy_1->  calcStoreFreight($freight_list,$input_city_id);

        //计算店铺最终订单实际支付金额(加上运费)
        $store_final_order_total = $this->_logic_buy_1->reCalcGoodsTotal($store_final_goods_total,$store_freight_total,'freight');

        //计算店铺分类佣金[改由任务计划]
//         $store_gc_id_commis_rate_list = Model('store_bind_class')->getStoreGcidCommisRateList($goods_list);

        //将赠品追加到购买列表(如果库存0，则不送赠品)
        $append_premiums_to_cart_list = $this->_logic_buy_1->appendPremiumsToCartList($store_cart_list,$store_premiums_list,$store_mansong_rule_list,$this->_member_info['member_id']);
        if($append_premiums_to_cart_list === false) {
            throw new Exception('抱歉，您购买的商品库存不足，请重购买');
        } else {
            list($store_cart_list,$goods_buy_quantity,$store_mansong_rule_list) = $append_premiums_to_cart_list;
        }

        //保存数据
        $this->_order_data['store_goods_total'] = $store_goods_total;
        $this->_order_data['store_final_order_total'] = $store_final_order_total;
        $this->_order_data['store_freight_total'] = $store_freight_total;
        $this->_order_data['store_promotion_total'] = $store_promotion_total;
//         $this->_order_data['store_gc_id_commis_rate_list'] = $store_gc_id_commis_rate_list;
        $this->_order_data['store_mansong_rule_list'] = $store_mansong_rule_list;
        $this->_order_data['store_cart_list'] = $store_cart_list;
        $this->_order_data['goods_buy_quantity'] = $goods_buy_quantity;
        $this->_order_data['input_voucher_list'] = $input_voucher_list;

    }

    /**
     * 生成订单
     * @param array $input
     * @throws Exception
     * @return array array(支付单sn,订单列表)
     */
    private function _createOrderStep4() {

        extract($this->_order_data);



        $member_id = $this->_member_info['member_id'];
        $member_name = $this->_member_info['member_name'];
        $member_email = $this->_member_info['member_email'];

        $model_order = Model('order');

        //存储生成的订单数据
        $order_list = array();
        //存储通知信息
        $notice_list = array();

        //每个店铺订单是货到付款还是线上支付,店铺ID=>付款方式[在线支付/货到付款]
        $store_pay_type_list    = $this->_logic_buy_1->getStorePayTypeList(array_keys($store_cart_list), $input_if_offpay, $input_pay_name);

        foreach ($store_pay_type_list as $k => & $v) {
            if (empty($input_if_offpay_batch[$k]))
                $v = 'online';
        }

        $pay_sn = $this->_logic_buy_1->makePaySn($member_id);
        $order_pay = array();
        $order_pay['pay_sn'] = $pay_sn;
        $order_pay['buyer_id'] = $member_id;
        $order_pay_id = $model_order->addOrderPay($order_pay);
        if (!$order_pay_id) {
            throw new Exception('订单保存失败[未生成支付单]');
        }
    
        //收货人信息
        list($reciver_info,$reciver_name) = $this->_logic_buy_1->getReciverAddr($input_address_info);

        foreach ($store_cart_list as $store_id => $goods_list) {
    
            //取得本店优惠额度(后面用来计算每件商品实际支付金额，结算需要)
            $promotion_total = !empty($store_promotion_total[$store_id]) ? $store_promotion_total[$store_id] : 0;
            //本店总的优惠比例,保留3位小数
            $should_goods_total = $store_final_order_total[$store_id]-$store_freight_total[$store_id]+$promotion_total;
            $promotion_rate = abs(number_format($promotion_total/$should_goods_total,5));
            if ($promotion_rate <= 1) {
                $promotion_rate = floatval(substr($promotion_rate,0,5));
            } else {
                $promotion_rate = 0;
            }
    
            //每种商品的优惠金额累加保存入 $promotion_sum
            $promotion_sum = 0;
    
            $order = array();
            $order_common = array();
            $order_goods = array();
    
            $order['order_sn'] = $this->_logic_buy_1->makeOrderSn($order_pay_id);
            $order['pay_sn'] = $pay_sn;
            $order['store_id'] = $store_id;
            $order['store_name'] = $goods_list[0]['store_name'];
            $order['buyer_id'] = $member_id;
            $order['buyer_name'] = $member_name;
            $order['buyer_email'] = $member_email;
            $order['add_time'] = TIMESTAMP;
            $order['payment_code'] = $store_pay_type_list[$store_id];
            $order['order_state'] = $store_pay_type_list[$store_id] == 'online' ? ORDER_STATE_NEW : ORDER_STATE_PAY;
            $order['order_amount'] = $store_final_order_total[$store_id];
            $order['shipping_fee'] = $store_freight_total[$store_id];
            $order['goods_amount'] = $order['order_amount'] - $order['shipping_fee'];
            $order['order_from'] = $order_from;
            $order_id = $model_order->addOrder($order);
            if (!$order_id) {
                throw new Exception('订单保存失败[未生成订单数据]');
            }
            $order['order_id'] = $order_id;
            $order_list[$order_id] = $order;
    
            $order_common['order_id'] = $order_id;
            $order_common['store_id'] = $store_id;
            $order_common['order_message'] = $input_pay_message[$store_id];
    
            //代金券
            if (isset($input_voucher_list[$store_id])){
                $order_common['voucher_price'] = $input_voucher_list[$store_id]['voucher_price'];
                $order_common['voucher_code'] = $input_voucher_list[$store_id]['voucher_code'];
            }

            $order_common['reciver_info']= $reciver_info;
            $order_common['reciver_name'] = $reciver_name;
            $order_common['reciver_city_id'] = $input_city_id;

            //发票信息
            $order_common['invoice_info'] = $this->_logic_buy_1->createInvoiceData($input_invoice_info);
    
            //保存促销信息
            if(is_array($store_mansong_rule_list[$store_id])) {
                $order_common['promotion_info'] = addslashes($store_mansong_rule_list[$store_id]['desc']);
            }
    
            $order_id = $model_order->addOrderCommon($order_common);
            if (!$order_id) {
                throw new Exception('订单保存失败[未生成订单扩展数据]');
            }
    
            //生成order_goods订单商品数据
            $i = 0;
            foreach ($goods_list as $goods_info) {
                if (!$goods_info['state'] || !$goods_info['storage_state']) {
                    throw new Exception('部分商品已经下架或库存不足，请重新选择');
                }
                // change chenyifei
                $good_rate = $this->getRate($goods_info['store_id'], $goods_info['gc_id']);
                $order_goods[$i]['goods_spec'] = unserialize($goods_info['goods_spec']) ? $this->formart_goods_spec(unserialize($goods_info['goods_spec'])):$goods_info['goods_spec'];
  
                //分销相关功能 change lixiyu （购物车有这些字段，但直接够买是不一定有这些字段）
                if(isset($goods_info['is_distribution']) && 0!=$goods_info['is_distribution']) {
                    $dis_goods=Model('distribution')->get_goods_info(array('goods_id'=>$goods_info['goods_id']));
                    if(!empty($dis_goods)){
                        $order_goods[$i]['dis_store_member_id'] = $goods_info['dis_store_member_id'];
                        $order_goods[$i]['dis_store_id'] = $goods_info['dis_store_id'];
                        $order_goods[$i]['dis_member_id'] = $goods_info['dis_member_id'];
                        $order_goods[$i]['is_distribution'] = $goods_info['is_distribution'];
                        $order_goods[$i]['first_price'] = $dis_goods['first_price'];
                        $order_goods[$i]['second_price'] = $dis_goods['second_price'];
                        $order_goods[$i]['distribution_rate']=$this->get_distribution_rate($goods_info['store_id'], $goods_info['gc_id']);
                    }
                }else{
                    $order_goods[$i]['dis_store_member_id'] =0;
                    $order_goods[$i]['dis_store_id'] = 0;
                    $order_goods[$i]['dis_member_id'] =0;
                    $order_goods[$i]['is_distribution'] =0;
                    $order_goods[$i]['first_price'] =0;
                    $order_goods[$i]['second_price'] = 0;
                    $order_goods[$i]['distribution_rate']=0;
                }

                if (!intval($goods_info['bl_id'])) {
                    //如果不是优惠套装
                    $order_goods[$i]['order_id'] = $order_id;
                    $order_goods[$i]['goods_id'] = $goods_info['goods_id'];
                    $order_goods[$i]['store_id'] = $store_id;
                    $order_goods[$i]['goods_name'] = $goods_info['goods_name'];
                    $order_goods[$i]['goods_price'] = $goods_info['goods_price'];
                    $order_goods[$i]['goods_num'] = $goods_info['goods_num'];
                    $order_goods[$i]['goods_image'] = $goods_info['goods_image'];
                    $order_goods[$i]['buyer_id'] = $member_id;
                    if ($goods_info['ifgroupbuy']) {
                        $ifgroupbuy = true;
                        $order_goods[$i]['goods_type'] = 2;
                    }elseif ($goods_info['ifxianshi']) {
                        $order_goods[$i]['goods_type'] = 3;
                    }elseif ($goods_info['ifzengpin']) {
                        $order_goods[$i]['goods_type'] = 5;
                    }else {
                        $order_goods[$i]['goods_type'] = 1;
                    }
                    $order_goods[$i]['promotions_id'] = $goods_info['promotions_id'] ? $goods_info['promotions_id'] : 0;
                    // change chenyifei
                   // $order_goods[$i]['commis_rate'] = 200;
                    $order_goods[$i]['commis_rate'] = $good_rate;
                    $order_goods[$i]['gc_id'] = $goods_info['gc_id'];
                    //计算商品金额
                    $goods_total = $goods_info['goods_price'] * $goods_info['goods_num'];
                    //计算本件商品优惠金额
                    $promotion_value = floor($goods_total*($promotion_rate));
                    $order_goods[$i]['goods_pay_price'] = $goods_total - $promotion_value;
                    $promotion_sum += $promotion_value;
                    $i++;

                    //存储库存报警数据
                    if ($goods_info['goods_storage_alarm'] >= ($goods_info['goods_storage'] - $goods_info['goods_num'])) {
                        $param = array();
                        $param['common_id'] = $goods_info['goods_commonid'];
                        $param['sku_id'] = $goods_info['goods_id'];
                        $notice_list['goods_storage_alarm'][$goods_info['store_id']] = $param;
                    }

                } elseif (!empty($goods_info['bl_goods_list']) && is_array($goods_info['bl_goods_list'])) {

                    //优惠套装
                    foreach ($goods_info['bl_goods_list'] as $bl_goods_info) {
                        $order_goods[$i]['order_id'] = $order_id;
                        $order_goods[$i]['goods_id'] = $bl_goods_info['goods_id'];
                        $order_goods[$i]['store_id'] = $store_id;
                        $order_goods[$i]['goods_name'] = $bl_goods_info['goods_name'];
                        $order_goods[$i]['goods_price'] = $bl_goods_info['bl_goods_price'];
                        $order_goods[$i]['goods_num'] = $goods_info['goods_num'];
                        $order_goods[$i]['goods_image'] = $bl_goods_info['goods_image'];
                        $order_goods[$i]['buyer_id'] = $member_id;
                        $order_goods[$i]['goods_type'] = 4;
                        $order_goods[$i]['promotions_id'] = $bl_goods_info['bl_id'];
                        // change chenyifei
                        //$order_goods[$i]['commis_rate'] = 200;
                        $order_goods[$i]['commis_rate'] = $good_rate;
                        $order_goods[$i]['gc_id'] = $bl_goods_info['gc_id'];
    
                        //计算商品实际支付金额(goods_price减去分摊优惠金额后的值)
                        $goods_total = $bl_goods_info['bl_goods_price'] * $goods_info['goods_num'];
                        //计算本件商品优惠金额
                        $promotion_value = floor($goods_total*($promotion_rate));
                        $order_goods[$i]['goods_pay_price'] = $goods_total - $promotion_value;
                        $promotion_sum += $promotion_value;
                        $i++;
    
                        //存储库存报警数据
                        if ($bl_goods_info['goods_storage_alarm'] >= ($bl_goods_info['goods_storage'] - $goods_info['goods_num'])) {
                            $param = array();
                            $param['common_id'] = $bl_goods_info['goods_commonid'];
                            $param['sku_id'] = $bl_goods_info['goods_id'];
                            $notice_list['goods_storage_alarm'][$bl_goods_info['store_id']] = $param;
                        }
                    }
                }
            }

            //将因舍出小数部分出现的差值补到最后一个商品的实际成交价中(商品goods_price=0时不给补，可能是赠品)
            if ($promotion_total > $promotion_sum) {
                $i--;
                for($i;$i>=0;$i--) {
                    if (floatval($order_goods[$i]['goods_price']) > 0) {
                        $order_goods[$i]['goods_pay_price'] -= $promotion_total - $promotion_sum;
                        break;
                    }
                }
            }
            $insert = $model_order->addOrderGoods($order_goods);
            if (!$insert) {
                throw new Exception('订单保存失败[未生成商品数据]');
            }
    
            //存储商家发货提醒数据
            if ($store_pay_type_list[$store_id] == 'offline') {
                $notice_list['new_order'][$order['store_id']] = array('order_sn' => $order['order_sn']);
            }
        }

        //保存数据
        $this->_order_data['order_sn']= $order['order_sn'];
        $this->_order_data['pay_sn'] = $pay_sn;
        $this->_order_data['order_list'] = $order_list;
        $this->_order_data['notice_list'] = $notice_list;
        $this->_order_data['ifgroupbuy'] = $ifgroupbuy;
    }

    private function get_distribution_rate($store_id,$gc_id){

        //店铺返利比率。(上面的商品信息可能是购物车来的，没有分类信息)   1.查商品所属分类   2.查对应的返利比率
        $shop_class=Model('store_bind_class')->getStoreBindClassList(array('store_id'=>$store_id));
        $distribution_rate=$c_leave=0;
        if(!empty($shop_class)){
            foreach($shop_class as $s_value){                               //更据分类分销返佣比率，查该商品的分销返佣比率。该店铺的分类可能是某个一级分类，商品的分类可能是二级分类，也可能是三级分类，取最精确的分类的分销返佣比率，作为商品的返佣笔录
                if($s_value['class_3']==$gc_id){
                    $distribution_rate=$s_value['distribution_rate'];
                    break;
                }
                if($s_value['class_2']==$gc_id){
                    $c_leave=2;
                    $distribution_rate=$s_value['distribution_rate'];
                }
                if($s_value['class_1']==$gc_id){
                    if($c_leave>1) continue;
                    $c_leave=1;
                    $distribution_rate=$s_value['distribution_rate'];
                }
            }
        }
        return $distribution_rate;
    }

    /**
     * 充值卡、预存款支付
     *
     */
    private function _createOrderStep5() {
        if (empty($this->_post_data['password'])) return ;
        //买家使用预存款时锁定表
        //$buyer_info	= Model('member')->getMemberInfoByID($this->_member_info['member_id']);
        $buyer_info = Model('member')->where(array('member_id' => $this->_member_info['member_id']))->lock(true)->find();
        if ($buyer_info['member_paypwd'] == '' || $buyer_info['member_paypwd'] != md6($this->_post_data['password'], $buyer_info['member_salt'])) return ;
        //交易密码当天出错三次不可用已存款支付
        $paypwd_result = Model('member')->limit_input_paypwd_count($buyer_info, $this->_post_data['password']);
        if (isset($paypwd_result['state']) && $paypwd_result['state'] == false) return ;
        
        //使用充值卡支付
        if (!empty($this->_post_data['rcb_pay'])) {
            $order_list = $this->_logic_buy_1->rcbPay($this->_order_data['order_list'], $this->_post_data, $buyer_info);
        }
        
        //使用预存款支付
        if (!empty($this->_post_data['pd_pay'])) {
            $this->_logic_buy_1->pdPay($order_list ? $order_list :$this->_order_data['order_list'], $this->_post_data, $buyer_info);
        }
    }
    
    /**
     * 本土 预存款支付
     */
    private function _localOrderStep5($post, $order_info, $buyer_info) {
        return true;
       /*  if (!isset($post['password'])) return ;
        if ($buyer_info['member_paypwd'] == '' || $buyer_info['member_paypwd'] != md6($post['password'], $buyer_info['member_salt'])) return ;
      
        //使用预存款支付
        if (isset($post['pd_pay']) &&  !empty($post['pd_pay'])) {
            $available_pd_amount = floatval($buyer_info['available_predeposit']);
            if ($available_pd_amount <= 0) return;
            $model_order = Model('order');
            $model_pd = Model('predeposit');
            $member_id = $buyer_info['member_id'];
            $member_name = $buyer_info['member_name'];
            $order_amount = floatval($order_info['order_amount']);
            $data_pd = array();
            $data_pd['member_id'] = $member_id;
            $data_pd['member_name'] = $member_name;
            $data_pd['amount'] = $order_amount;
            $data_pd['order_sn'] = $order_info['order_sn'];
            
            if ($available_pd_amount >= $order_amount) {
                //预存款立即支付，订单支付完成
                $model_pd->changePd('order_pay',$data_pd);           
            
                //记录订单日志(已付款)
                $data = array();
                $data['order_id'] = $order_info['order_id'];
                $data['log_role'] = 'buyer';
                $data['log_msg'] = L('order_log_pay');
                $data['log_orderstate'] = ORDER_STATE_SUCCESS;
                $insert = $model_order->addOrderLog($data);
                if (!$insert) {
                    throw new Exception('记录订单预存款支付日志出现错误');
                }
            
            } else {
                //暂冻结预存款,后面还需要 API彻底完成支付
                if ($available_pd_amount > 0) {
                    $data_pd['amount'] = $available_pd_amount;
                    $model_pd->changePd('order_freeze',$data_pd);
                    //预存款支付金额保存到订单
                    $data_order = array();
                    $data_order['pd_amount'] = $available_pd_amount;
                    $result = $model_order->editOrder($data_order,array('order_id'=>$order_info['order_id']));
                    $available_pd_amount = 0;
                    if (!$result) {
                        throw new Exception('订单更新失败');
                    }
                }
            }

 

        } */
        
    }

    /**
     * 订单后续其它处理
     *
     */
    private function _createOrderStep6() {
        $ifcart = $this->_post_data['ifcart'];
        $goods_buy_quantity = $this->_order_data['goods_buy_quantity'];
        $input_voucher_list = $this->_order_data['input_voucher_list'];
        $store_cart_list = $this->_order_data['store_cart_list'];
        $input_buy_items = $this->_order_data['input_buy_items'];
        $order_list = $this->_order_data['order_list'];
        $input_address_info = $this->_order_data['input_address_info'];
        $notice_list = $this->_order_data['notice_list'];
        $fc_id = $this->_order_data['fc_id'];
        $ifgroupbuy = $this->_order_data['ifgroupbuy'];

        //变更库存和销量
        QueueClient::push('createOrderUpdateStorage', $goods_buy_quantity);

        //更新使用的代金券状态
        if (!empty($input_voucher_list) && is_array($input_voucher_list)) {
            QueueClient::push('editVoucherState', $input_voucher_list);
        }

        //更新F码使用状态
        if ($fc_id) {
            QueueClient::push('updateGoodsFCode', $fc_id);
        }

        //更新抢购购买人数和数量
        if ($ifgroupbuy) {
            foreach ($store_cart_list as $goods_list) {
                foreach ($goods_list as $goods_info) {
                    if ($goods_info['ifgroupbuy'] && $goods_info['groupbuy_id']) {
                        $groupbuy_info = array();
                        $groupbuy_info['groupbuy_id'] = $goods_info['groupbuy_id'];
                        $groupbuy_info['quantity'] = $goods_info['goods_num'];
                        QueueClient::push('editGroupbuySaleCount', $groupbuy_info);
                    }
                }
            }
        }

        //删除购物车中的商品
        $this->delCart($ifcart,$this->_member_info['member_id'],array_keys($input_buy_items));
        @setNcCookie('cart_goods_num','',-3600);

        //保存订单自提点信息
        if (C('delivery_isuse') && intval($input_address_info['dlyp_id'])) {
            $data = array();
            $data['mob_phone'] = $input_address_info['mob_phone'];
            $data['tel_phone'] = $input_address_info['tel_phone'];
            $data['reciver_name'] = $input_address_info['true_name'];
            $data['dlyp_id'] = $input_address_info['dlyp_id'];
            foreach ($order_list as $v) {
                $data['order_sn_list'][$v['order_id']]['order_sn'] = $v['order_sn'];
                $data['order_sn_list'][$v['order_id']]['add_time'] = $v['add_time'];
            }
            QueueClient::push('saveDeliveryOrder', $data);
        }

        //发送提醒类信息
        if (!empty($notice_list)) {
            foreach ($notice_list as $code => $value) {
                QueueClient::push('sendStoreMsg', array('code' => $code, 'store_id' => key($value), 'param' => current($value)));
            }
        }

    }

    /**
     * 加密
     * @param array/string $string
     * @param int $member_id
     * @return mixed arrray/string
     */
    public function buyEncrypt($string, $member_id) {
        $buy_key = sha1(md5($member_id.'&'.MD5_KEY));
        if (is_array($string)) {
            $string = serialize($string);
        } else {
            $string = strval($string);
        }
        return encrypt(base64_encode($string), $buy_key);
    }

    /**
     * 解密
     * @param string $string
     * @param int $member_id
     * @param number $ttl
     */
    public function buyDecrypt($string, $member_id, $ttl = 0) {
        $buy_key = sha1(md5($member_id.'&'.MD5_KEY));
        if (empty($string)) return;
        $string = base64_decode(decrypt(strval($string), $buy_key, $ttl));
        return ($tmp = @unserialize($string)) !== false ? $tmp : $string;
    }

    /**
     * 得到所购买的id和数量
     *
     */
    private function _parseItems($cart_id) {
        //存放所购商品ID和数量组成的键值对
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    if (intval($match[2][0]) > 0) {
                        $buy_items[$match[1][0]] = $match[2][0];
                    }
                }
            }
        }
        return $buy_items;
    }

    /**
     * 从购物车数组中得到商品列表
     * @param unknown $cart_list
     */
    private function _getGoodsList($cart_list) {
        if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
        $goods_list = array();
        $i = 0;
        foreach ($cart_list as $key => $cart) {
            if (!$cart['state'] || !$cart['storage_state']) continue;
            //购买数量
            $quantity = $cart['goods_num'];

            //分销相关功能（购物车中下单的分销功能）
            $goods_list[$i]['dis_store_member_id'] = $cart['dis_store_member_id'];
            $goods_list[$i]['dis_store_id'] = $cart['dis_store_id'];
            $goods_list[$i]['dis_member_id'] = $cart['dis_member_id'];
            $goods_list[$i]['is_distribution'] = $cart['is_distribution'];

            if (!intval($cart['bl_id'])) {
                //如果是普通商品
                $goods_list[$i]['goods_num'] = $quantity;
                $goods_list[$i]['goods_id'] = $cart['goods_id'];
                $goods_list[$i]['store_id'] = $cart['store_id'];
                $goods_list[$i]['gc_id'] = $cart['gc_id'];
                $goods_list[$i]['goods_name'] = $cart['goods_name'];
                $goods_list[$i]['goods_price'] = del0($cart['goods_price']);
                $goods_list[$i]['store_name'] = $cart['store_name'];
                $goods_list[$i]['goods_image'] = $cart['goods_image'];
                $goods_list[$i]['transport_id'] = $cart['transport_id'];
                $goods_list[$i]['goods_freight'] = del0($cart['goods_freight']);
                $goods_list[$i]['goods_vat'] = $cart['goods_vat'];
                $goods_list[$i]['is_fcode'] = $cart['is_fcode'];
                $goods_list[$i]['bl_id'] = 0;
                $i++;
            } else {
                //如果是优惠套装商品
                foreach ($cart['bl_goods_list'] as $bl_goods) {
                    $goods_list[$i]['goods_num'] = $quantity;
                    $goods_list[$i]['goods_id'] = $bl_goods['goods_id'];
                    $goods_list[$i]['store_id'] = $cart['store_id'];
                    $goods_list[$i]['gc_id'] = $bl_goods['gc_id'];
                    $goods_list[$i]['goods_name'] = $bl_goods['goods_name'];
                    $goods_list[$i]['goods_price'] = $bl_goods['goods_price'];
                    $goods_list[$i]['store_name'] = $bl_goods['store_name'];
                    $goods_list[$i]['goods_image'] = $bl_goods['goods_image'];
                    $goods_list[$i]['transport_id'] = $bl_goods['transport_id'];
                    $goods_list[$i]['goods_freight'] = $bl_goods['goods_freight'];
                    $goods_list[$i]['goods_vat'] = $bl_goods['goods_vat'];
                    $goods_list[$i]['bl_id'] = $cart['bl_id'];
                    $i++;
                }
            }
        }
        return $goods_list;
    }

    /**
     * 将下单商品列表转换为以店铺ID为下标的数组
     *
     * @param array $cart_list
     * @return array
     */
    private function _getStoreCartList($cart_list) {
        if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
        $new_array = array();
        foreach ($cart_list as $cart) {
            $new_array[$cart['store_id']][] = $cart;
        }
        return $new_array;
    }

    /**
     * 本次下单是否需要码及F码合法性
     * 无需使用F码，返回 true
     * 需要使用F码，返回($fc_id/false)
     */
    private function _checkFcode($goods_list, $fcode) {
        foreach ($goods_list as $k => $v) {
            if ($v['is_fcode'] == 1) {
                $is_fcode = true; break;
            }
        }
        if (!$is_fcode) return true;
        if (empty($fcode) || count($goods_list) > 1) {
            return false;
        }
        $goods_info = $goods_list[0];
        $fcode_info = $this->checkFcode($goods_info['goods_commonid'],$fcode);
        if ($fcode_info['state']) {
            return intval($fcode_info['data']['fc_id']);
        } else {
            return false;
        }
    }
    /**chenyifei
     * 根据商品分类获取对应的分利比率
     * @param $store_id 商户id $gc_id 商品分类id
     */
    private function getRate($store_id, $gc_id)
    {
        $data = Model()->table('store_bind_class')->where(array('store_id'=>$store_id,'state'=>1, 'class_1|class_2|class_3'=>$gc_id))->select();
        if (empty($data))
            throw new Exception('无此商品分类');
        $rate = 0;
        $flg = false;
        foreach ($data as $row)
        {
            if ($row['class_3'] == $gc_id)
            {
                $rate = $row['commis_rate'];
                $flg = true;
                break;
            }
        }
        if ($flg == false)
        {
            if ($row['class_2'] == $gc_id)
            {
                $rate = $row['commis_rate'];
                $flg = true;
               
            }
        }
        if ($flg == false)
        {
            if ($row['class_1'] == $gc_id)
            {
                $rate = $row['commis_rate'];
                $flg = true;
                
            }
        }
        return $rate;
    }
    
    /**
     * cyfei
     * 本土订单下单
     * @param unknown $param
     */
    function localOrderAdd($param)
    {
        $member_id = isset($param['member_id']) ? intval($param['member_id']) : 0;
        $goods_id = isset($param['goods_id']) ? intval($param['goods_id']) : 0;
        $store_id = isset($param['store_id']) ? intval($param['store_id']) : 0;
        $count     =isset($param['goods_amount']) ? intval($param['goods_amount']) : 1;
        $money = isset($param['money']) ? $param['money'] : 0;
        //本土预付款活动订单修改，有商品数量
        $money = bcmul($money, $count, 2);
        $order_from = isset($param['order_from']) ? intval($param['order_from']) : 2; // 默认手机端
        $pd_pay = isset($param['pd_pay']) ? intval($param['pd_pay']) : 0;  //余额支付
        $paypwd = isset($param['paypwd']) ? trim($param['paypwd']) : ''; //支付密码
        if (empty($member_id)  )
            return callback(false, '参数错误');
        
        
        try {
            $model = Model();
            $model->beginTransaction();
            $order_amount = 0;
            $pd_amount = 0;
            //会员
            $member = $model->table('member')->where(array('member_id' => $member_id))->lock(true)->find();
            if (empty($member))
                throw new Exception('用户不存在');
            if ($pd_pay > 0)
            {
                //支付密码验证
                if ($member['member_paypwd'] == '' || $member['member_paypwd'] != md6($paypwd, $member['member_salt']))
                {
                    return callback(false, '支付密码不存在或错误');
                }
                $paypwd_result = Model('member')->limit_input_paypwd_count($member, $paypwd);
                if (isset($paypwd_result['state']) && $paypwd_result['state'] == false)
                {
                    return callback(false, $paypwd_result['msg']);
                }
            }
           // $store_id = 0;
            $store_name = '';
            $use_rebate = 0;
            $good_arr = array();
            $invitation = '';
            $promotion_info = ''; //优惠备注
            if ($goods_id > 0)
            {
                $good_arr = $model->table('goods')->where(array('goods_id'=>$goods_id,  'good_type' => 1, 'goods_state' => 1))->master(true)->find();
                if (empty($good_arr))
                    throw new Exception('商品不存在');
                //商品限购时判断
                if ($good_arr['limit_count'] > 0)
                {
                    if ($count > $good_arr['limit_count'])
                        throw new Exception('已超过限购数量，不可购买');
                    $has_buy_query = Model()->query("select b.goods_num from ".DBPRE."order a join ". DBPRE ."order_goods b on a.order_id=b.order_id where a.buyer_id='".$member_id."' and b.goods_id='".$goods_id."' and a.order_state >=10");
                    $has_buy_counts =  0;
                    if (!empty($has_buy_query)){
                        foreach ($has_buy_query as $key_q=>$row_q){
                            $has_buy_counts += $row_q['goods_num'];
                        }
                    }
                    $has_buy_counts = $count + $has_buy_counts;
                    if ($has_buy_counts > $good_arr['limit_count'])
                        throw new Exception('已超过限购数量，不可购买');
                }
                //商品库存判断
                if(!empty($good_arr['limit_storage']) && $good_arr['limit_storage']==1) {
                    $storage =!empty($good_arr['goods_storage']) ? intval($good_arr['goods_storage']) : 0;
                    if($count>$storage) {
                        return callback(false, '商品库存不足');
                    }
                    else {
                        //更新库存
                        $data = array();
                        $data['goods_storage'] = array('exp','goods_storage-'.$count);
                        Model('goods')->editGoodsById($data, $goods_id);
                    }
                }
                $store_id = $good_arr['store_id'];
                //商家表
                $seller = $model->table('store')->where(array('store_id'=>$store_id, 'store_type' => 1, 'store_state'=>1))->master(true)->find();
                if (empty($seller))
                    throw new Exception('商户不存在');
                
                $store_name = $seller['store_name'];
                
                $seller_member = $model->table('member')->where(array('member_id' => $seller['member_id']))->master(true)->find();
                $invitation = $seller_member['invitation'];
                //使用分利比率
                $use_rebate = $this->getRate($store_id, $good_arr['gc_id']);
            }
            elseif ($store_id > 0)
            {
                $store_info = Model('store')->getStoreInfoByID($store_id);
                if (empty($store_info))
                {
                    throw new Exception('商户不存在');
                }
                $store_name = $store_info['store_name'];
                $seller_member = $model->table('member')->where(array('member_id' => $store_info['member_id']))->master(true)->find();
                $invitation = $seller_member['invitation'];
                //店铺买单全场打折金额计算
                if ($store_info['whole_discount'] < 10)
                {
                    $money = round($money * $store_info['whole_discount'] / 10, 2) ;
                    $promotion_info = "全场打{$store_info['whole_discount']}折";
                }
                
                //使用分利比率
                $use_rebate = $store_info['store_commis_rates'];
            }
            
        
            //生成订单
            $model_order = Model('order');
            $pay_sn = $this->_logic_buy_1->makePaySn($param['member_id']);
            $order_pay = array();
            $order_pay['pay_sn'] = $pay_sn;
            $order_pay['buyer_id'] = $member_id;
            $order_pay_id = $model_order->addOrderPay($order_pay);
            $order_state = ORDER_STATE_NEW;
            if (!$order_pay_id) {
                throw new Exception('订单保存失败[未生成支付单]');
            }
            $order = array(
                'order_sn' => $this->_logic_buy_1->makeOrderSn($order_pay_id),
                'pay_sn' => $pay_sn,
                'store_id' => $store_id,
                'store_name' => $store_name,
                'buyer_id' => $member_id,
                'buyer_name' => $member['member_name'],
                'buyer_email' => $member['member_email'],
                'add_time' => TIMESTAMP,
                //'payment_code' => 'offline',
               // 'payment_time' => TIMESTAMP,
               // 'finnshed_time' => TIMESTAMP,
                'goods_amount' => $money,
                'order_amount' => $money,
                'order_state' => ORDER_STATE_NEW,
                'order_from' => $order_from,
                'goods_type' => ($goods_id > 0) ? 1 : 0,
                'commis_rate' => $use_rebate,
                'order_type' => 1,
                'o2o_order_type' => $param['o2o_order_type'],
            );
            $order_amount = $money;

            $order_id = $model_order->addOrder($order);
            if (!$order_id) {
                throw new Exception('订单保存失败[未生成订单数据]');
            }
            //订单扩展表
            $order_common = array(
                'order_id' => $order_id,
                'store_id' => $store_id,
                'promotion_info' => $promotion_info,
            );
            $order_id = $model_order->addOrderCommon($order_common);
            if (!$order_id) {
                throw new Exception('订单保存失败[未生成订单扩展数据]');
            }
            //$rebate_arr = array();
            $goods_name = '';
            //订单商品表
            if (!empty($goods_id))
            {
                $goods_name = $good_arr['goods_name'];
                $order_good = array(
                    'order_id' => $order_id,
                    'goods_id' => $goods_id,
                    'goods_name' => $good_arr['goods_name'],
                    'goods_price' => $good_arr['goods_price'],
                    'goods_image' => $good_arr['goods_image'],
                    'goods_pay_price' => $money,
                    'store_id' => $store_id,
                    'buyer_id' => $member_id,
                    'commis_rate' => $use_rebate,
                    'gc_id' => $good_arr['gc_id'],
                    'goods_num'   =>$count,
                );

                $insert = $model->table('order_goods')->insert($order_good);
                if (!$insert) {
                    throw new Exception('订单保存失败[未生成商品数据]');
                }
            }  
            //积分支付情况
            if ($pd_pay > 0)
            {
                $available_predeposit = $member['available_predeposit'];
                //商家预充值金额去除
                $store_pre_deposit = Model('store_pre_deposit')->getStorePredepositCount(array('member_id'=>$member_id),true);
                if (!empty($store_pre_deposit))
                {
                    $available_predeposit = bcsub($available_predeposit, $store_pre_deposit['amount_total'], 2);
                }
                $data_pd = array();
                $data_pd['member_id'] = $member_id;
                $data_pd['member_name'] = $member['member_name'];
                //$data_pd['amount'] = $order_amount;
                $data_pd['order_sn'] = $order['order_sn'];
                $model_pd = Model('predeposit');
                //预存款大于订单金额情况,直接支付完成并分利
                if ($available_predeposit >= $order['order_amount']  )
                {
                    $pd_amount = $data_pd['amount'] = $order['order_amount'];
                    $model_pd->changePd('order_pay',$data_pd);
                    
                    //记录订单日志(已付款)
                    $data = array();
                    $data['order_id'] = $order_id;
                    $data['log_role'] = 'buyer';
                    $data['log_msg'] = L('order_log_pay');
                    $data['log_orderstate'] = ORDER_STATE_PAY;
                    $insert = $model_order->addOrderLog($data);
                    if (!$insert) {
                        throw new Exception('记录订单预存款支付日志出现错误');
                    }
                    if ($order['o2o_order_type'] == 1){
                        $order_state = ORDER_STATE_SUCCESS;
                        $data_order = array();
                        $data_order['order_state'] = ORDER_STATE_SUCCESS;
                        $data_order['payment_time'] = TIMESTAMP;
                        $data_order['payment_code'] = 'predeposit';
                        $data_order['pd_amount'] = $order['order_amount'];
                        $data_order['finnshed_time'] = TIMESTAMP;
                        $result = $model_order->editOrder($data_order,array('order_id'=>$order_id));
                        if (!$result) {
                            throw new Exception('订单更新失败');
                        }
                        //分利执行
                        $order_logit = Logic('order');
                        $order_info = $model_order->getOrderInfo(array('order_id'=>$order_id), array(), '*',  '', '',  true);
                        $order_logit->setRebate($order_info);
                        QueueClient::push('storeJpush', array(
                            'message' => "订单：{$order['order_sn']}，已完成",
                            'store_id' => $order['store_id'],
                            'extend' => array(
                                'extras' => array(
                                    'data' => array(
                                        'message_type' => 'O2O_STORE_ORDER_SUCCESS',
                                        'message_data' => array(
                                            'order_id' => $order_id,
                                            'order_sn'=>$order['order_sn']
                                        )
                                    )
                                )
                            )
                        ));
                    }else{
                        //订单状态 置为已支付
                        $order_state = ORDER_STATE_PAY;
                        $data_order = array();
                        $data_order['order_state'] = ORDER_STATE_PAY;
                        $data_order['payment_time'] = TIMESTAMP;
                        $data_order['payment_code'] = 'predeposit';
                        $data_order['pd_amount'] = $order['order_amount'];
                        $data_order['consume_code'] = $this->_logic_buy_1->makeConsumeCode($order_id);
                        $data_order['o2o_order_type'] = 2;
                        //$data_order['finnshed_time'] = TIMESTAMP;
                        $result = $model_order->editOrder($data_order,array('order_id'=>$order_id));
                        if (!$result) {
                            throw new Exception('订单更新失败');
                        }
                        QueueClient::push('storeJpush', array(
                            'message' => "您有一个新订单",
                            'store_id' => $order['store_id'],
                            'extend' => array(
                                'extras' => array(
                                    'data' => array(
                                        'message_type' => 'O2O_STORE_ORDER_PAYMENT_SUCCESS',
                                        'message_data' => array(
                                            'order_id' => $order_id,
                                            'order_sn'=>$order['order_sn']
                                        )
                                    )
                                )
                            )
                        ));
                    }
                }
                else 
                {
                    if ($available_predeposit > 0)
                    {
                        $pd_amount = $data_pd['amount'] = $available_predeposit;
                        $model_pd->changePd('order_freeze',$data_pd);
                        $data_order = array();
                        $data_order['pd_amount'] = $available_predeposit;
                        $result = $model_order->editOrder($data_order,array('order_id'=>$order_id));
                        //$available_pd_amount = 0;
                        if (!$result) {
                            throw new Exception('订单更新失败');
                        }
                    }
                }
            }

                
            $model->commit();
            $need_pay = bcsub($order_amount, $pd_amount, 2);
            return callback(true,'',array('order_sn'=>$order['order_sn'], 'invitation' => $invitation, 'pay_sn'=>$order['pay_sn'], 'order_state'=>$order_state,  'goods_name' => $goods_name, 'need_pay'=>$need_pay, 'order_amount'=>$order_amount, 'pd_amount'=>$pd_amount));
        }catch (Exception $e){
            $model->rollback();
            return callback(false, $e->getMessage());
        }
        
    }

    /**chenyifei
     * 本土订单商家确认
     * @param unknown $param
     */
    function localOrderSure($order_arr,$store,$opt_member, $member_info)
    { 
        $order_good = isset($order_arr['extend_order_goods']) ? $order_arr['extend_order_goods'][0] : array();
        $model = Model();
        try {
            $model->beginTransaction();         
            $this->_localOrderComplete($order_arr, $store, $opt_member, $member_info);
            $order_log = array(
                'order_id' => $order_arr['order_id'],
                'log_msg' => '确认付款',
                'log_time' => TIMESTAMP,
                'log_role' => '商家',
                'log_user' => $opt_member['seller_name'],
                'log_orderstate' => ORDER_STATE_SUCCESS,
            );
            $insert = $model->table('order_log')->insert($order_log);
            if (!$insert) {
                throw new Exception('订单日志写入失败');
            }
            
            
            $model->commit();
            //变更销量
            if(!empty($order_arr['goods_type']) && $order_arr['goods_type']==1) {
                //订单有商品
                $order_goods = Model()->table('order,order_goods')->join('inner')->on('order.order_id=order_goods.order_id')->field('order_goods.*')->where(array('order_id'=>$order_info['order_id']))->find();
                if(!empty($order_goods['goods_id'])) {
                    QueueClient::push('localcreateOrderUpdateStorage', array($order_goods['goods_id']=>$order_goods['goods_num']));
                }
            }
            QueueClient::push('storeJpush', array(
                'message' => "订单：{$order_arr['order_sn']}，已完成",
                'store_id' => $order_arr['store_id'],
                'extend' => array(
                    'extras' => array(
                        'data' => array(
                            'message_type' => 'O2O_STORE_ORDER_SUCCESS',
                            'message_data' => array(
                                'order_id' => $order_arr['order_id'],
                                'order_sn'=>$order_arr['order_sn']
                            )
                        )
                    )
                )
            ));
            return callback(true);                
        }catch (Exception $e){
            $model->rollback();
            return callback(false, $e->getMessage());
        }
        
        
        
    }
    
    /**
     * 本土订单消费码确认
     */
    function localOrderConsumeCodeSure($consume_code,  $store_id, $seller_name)
    {
        $model = Model();
        try {
            $model->beginTransaction();
            $order_info = $model->table('order')->where(array('store_id'=>$store_id, 'consume_code'=>$consume_code, 'order_type'=>1))->lock(true)->find();
            if (empty($order_info))
                throw new Exception('此消费码不存在');
            if ($order_info['order_state'] != ORDER_STATE_PAY)
                throw new Exception('此消费码非已付款状态，不可使用');
            $update_order = array();
            $update_order['order_state'] = ORDER_STATE_SUCCESS;
            $update_order['finnshed_time'] =  TIMESTAMP;
            $update_order['delay_time'] = TIMESTAMP;
            
            
            
            $update = Model('order')->editOrder($update_order,array('order_id'=>$order_info['order_id'],'order_state'=>ORDER_STATE_PAY));
            if (!$update) {
                throw new Exception('操作失败');
            }
            Logic('order')->setRebate($order_info);
            $order_log = array(
                'order_id' => $order_info['order_id'],
                'log_msg' => '确认订单',
                'log_time' => TIMESTAMP,
                'log_role' => '商家',
                'log_user' => $seller_name,
                'log_orderstate' => ORDER_STATE_SUCCESS,
            );
            $insert = $model->table('order_log')->insert($order_log);
            if (!$insert) {
                throw new Exception('订单日志写入失败');
            }
            $model->commit();
            //变更销量
            if(!empty($order_info['goods_type']) && $order_info['goods_type']==1) {
                //订单有商品
                $order_goods = Model()->table('order,order_goods')->join('inner')->on('order.order_id=order_goods.order_id')->field('order_goods.*')->where(array('order_id'=>$order_info['order_id']))->find();
                if(!empty($order_goods['goods_id'])) {
                    QueueClient::push('localcreateOrderUpdateStorage', array($order_goods['goods_id']=>$order_goods['goods_num']));
                }
            }
            QueueClient::push('storeJpush', array(
                'message' => "订单：{$order_info['order_sn']}，已完成",
                'store_id' => $order_info['store_id'],
                'extend' => array(
                    'extras' => array(
                        'data' => array(
                            'message_type' => 'O2O_STORE_ORDER_SUCCESS',
                            'message_data' => array(
                                'order_id' => $order_info['order_id'],
                                'order_sn'=>$order_info['order_sn']
                            )
                        )
                    )
                )
            ));
            return callback(true);
        }catch (Exception $e){
            $model->rollback();
            return callback(false, $e->getMessage());
        }
    }
    /**
     * chenyifei 本土订单确认完成
     * @param $payment_code 支付方式区分；offline：线下付款；alipay：支付宝付款 ；wxpay：微信付款
     * 实现功能：1、订单状态改为完成；2、实现返佣；3、商户订单金额实时结算到商户账户
     */
    public function _localOrderComplete($order_arr,$store,$opt_member, $member_info, $payment_code = 'offline')
    {
        //判断订单状态
        $order_check = Model()->table('order')->field('order_state')->where(array('order_sn'=>$order_arr['order_sn'], 'order_type'=>1))->lock(true)->find();
        if ($order_check['order_state'] != ORDER_STATE_NEW)
            throw new Exception('此订单非待确认状态');
        $order_good = isset($order_arr['extend_order_goods']) ? $order_arr['extend_order_goods'][0] : array();
        $model = Model();
        //商家余额 加锁
        $sell_available_predeposit = $model->table('member')->field('available_predeposit, member_name,member_id')->where(array('member_id'=>$store['member_id']))->lock(true)->find();
        //使用分利比率
        $use_rebate = $store['store_commis_rates'];
        if (!empty($order_good))
        {
            $use_rebate = $this->getRate($store['store_id'], $order_good['gc_id']);
        }
        //返利金额
        $rebate_money = round($order_arr['goods_amount'] * $use_rebate / 100 , 2);
        if ($payment_code == 'offline')
        {
            if ($rebate_money > $sell_available_predeposit['available_predeposit'])
                throw new Exception('商户可用金额小于返利金额');
        }
        
        $order = array(
            'store_id' => $store['store_id'],
            'store_name' => $store['store_name'],
            'payment_code' => $payment_code,
            'payment_time' => TIMESTAMP,
            'finnshed_time' => TIMESTAMP,
            'order_state' => ORDER_STATE_SUCCESS,
            'commis_rate' => empty($order_good) ? $use_rebate : 0,
            'is_rebate' => 1,
        );
        $model_order = Model('order');
        $update = $model_order->editOrder($order,array('order_id'=>$order_arr['order_id']));
        if (!$update) {
            throw new Exception('保存失败');
        }
        $update = $model->table('order_common')->where(array('order_id'=>$order_arr['order_id']))->update(array('store_id'=>$store['store_id']));
        if (!$update) {
            throw new Exception('保存失败');
        }
        
        $rebate_arr = array();
        //订单商品表
        if (!empty($order_good))
        {
        
            $rebate_arr[0] = array(
                'goods_id' => $order_good['goods_id'],
                'goods_name' => $order_good['goods_name'],
                'order_id' => $order_arr['order_id'],
                'rebate' => $rebate_money,
                'store_id' => $store['store_id'],
                'store_name' => $store['store_name'],
                'rebate_type' => 1,
                'order_sn' => $order_arr['order_sn'],
                'add_time' => TIMESTAMP,
                'amount' => $order_arr['goods_amount'],
            );
        }
        else
        {
            $rebate_arr[0] = array(
                'goods_id' => 0,
                'goods_name' => '商户返利',
                'order_id' => $order_arr['order_id'],
                'rebate' => $rebate_money,
                'store_id' => $store['store_id'],
                'store_name' => $store['store_name'],
                'rebate_type' => 1,
                'order_sn' => $order_arr['order_sn'],
                'add_time' => TIMESTAMP,
                'amount' => $order_arr['goods_amount'],
            );
        }
        $order_type = 'off_line';
        if ($payment_code != 'offline')
        {
            //线上付款，直接跟商户结算
            $rebate_arr[0]['settle_money'] = bcsub($order_arr['goods_amount'], $rebate_money, 2);
            $order_type = 'on_line';
        }
        
        if ($rebate_money > 0)
        {
            //分利
            $order_logic = Logic('order');
            //分销商参与返利额
            $agents = $model->table('agent_store')->where(array('store_id'=>$store['store_id']))->master(true)->limit(1)->select();
        
            $order_logic->execRebate(array($member_info), $agents,$rebate_arr, array($sell_available_predeposit), $order_type);
        }
        //部分金额使用积分支付情况
        $pd_amount = floatval($order_arr['pd_amount']);
        if ($pd_amount > 0) {
            $model_pd = Model('predeposit');
            $data_pd = array();
            $data_pd['member_id'] = $order_arr['buyer_id'];
            $data_pd['member_name'] = $order_arr['buyer_name'];
            $data_pd['amount'] = $pd_amount;
            $data_pd['order_sn'] = $order_arr['order_sn'];
            $model_pd->changePd('order_comb_pay', $data_pd);
            // 将用户积分直接给商户
            /* $change_type = 'settle_account';
            $desc = '订单：' . $order_arr['order_sn'] . '结算(' . $pd_amount . '积分支付 )';
            $pd_log_data = array(
                'member_id' => $sell_available_predeposit['member_id'],
                'member_name' => $sell_available_predeposit['member_name'],
                'amount' => $pd_amount,
                'order_sn' => $desc,
                'lg_mark' => array(
                    'order_sn' => $order_arr['order_sn'],
                    'pd_amount' => $order_arr['pd_amount'],
                    'payment_code' => $order_arr['payment_code'],
                    'order_amount' => $order_arr['order_amount'],
                    'rebate_money' => $rebate_money
                )
            );
            $model_pd->changePd($change_type, $pd_log_data); */
        }
        //商户结算金额
        $seller_amount = bcsub($pd_amount, $rebate_money, 2);
        if ($seller_amount != 0){
            $change_type = 'settle_account';
            $desc =  '订单：'.$order_arr['order_sn'].'结算';
            $pd_log_data = array(
                'member_id' => $sell_available_predeposit['member_id'],
                'member_name' => $sell_available_predeposit['member_name'],
                'amount' => $seller_amount,
                'order_sn'=>$desc,
                'lg_mark' => array(
                    'order_sn' => $order_arr['order_sn'],
                    'pd_amount' => $order_arr['pd_amount'],
                    'payment_code' => $payment_code,
                    'order_amount' => $order_arr['order_amount'],
                    'rebate_money' => $rebate_money,
                ),
            );
            $model_pd = Model('predeposit');
            $model_pd->changePd($change_type, $pd_log_data);
        }
        
            
    }

    /**
     * chenyifei暂时不用，废除
     * 本土购买下单，直接完成
     * 
     * @param $param =array(
     *            $member_id, //买家id
     *            $store_id, //商铺id
     *            $money, //下单金额
     *            $good_id //所购商品，可以没有
     *            $order_from //订单来源 1：web；2：手机端
     *            $opt_id //确定订单会员id， 店员member_id
     *            )
     */
        /*
     * function localOrderCommit($param)
     * {
     * $member_id = isset($param['member_id']) ? intval($param['member_id']) : 0;
     * $store_id = isset($param['store_id']) ? intval($param['store_id']) : 0;
     * $goods_id = isset($param['goods_id']) ? intval($param['goods_id']) : 0;
     * $money = isset($param['money']) ? $param['money'] : 0;
     * $order_from = isset($param['order_from']) ? intval($param['order_from']) : 2; // 默认手机端
     * $opt_id = (isset($param['opt_id'])) ? intval($param['opt_id']) : 0; //店员会员id
     * if (empty($member_id) || empty($store_id) || empty($opt_id) )
     * throw new Exception('参数错误');
     * try {
     * $model = Model();
     * $model->beginTransaction();
     * //会员
     * $member = $model->table('member')->where(array('member_id' => $member_id))->find();
     * if (empty($member))
     * throw new Exception('用户不存在');
     * $opt_member = $model->table('seller')->field('member_id, seller_name')->where(array('member_id' => $opt_id, 'store_id' => $store_id))->find();
     * if (empty($opt_member))
     * throw new Exception('店员不存在');
     * //商家表
     * $seller = $model->table('store')->where(array('store_id'=>$store_id, 'store_type' => 1))->find();
     * if (empty($seller))
     * throw new Exception('商户不存在');
     * //商家余额 加锁
     * $sell_available_predeposit = $model->table('member')->field('available_predeposit, member_name,member_id')->where(array('member_id'=>$seller['member_id']))->lock(true)->find();
     * //使用分利比率
     * $use_rebate = $seller['store_commis_rates'];
     * $good_arr = array();
     *
     * if (!empty($goods_id))
     * {
     * $good_arr = $model->table('goods')->where(array('goods_id'=>$goods_id, 'store_id' => $store_id, 'good_type' => 1))->find();
     * if (empty($good_arr))
     * throw new Exception('商品不存在');
     * $use_rebate = $this->getRate($store_id, $good_arr['gc_id']);
     * }
     * //返利金额
     * $rebate_money = round($money * $use_rebate / 100 , 2);
     * if ($rebate_money > $sell_available_predeposit['available_predeposit'])
     * throw new Exception('商户可用金额小于返利金额');
     * //生成订单
     * $model_order = Model('order');
     * $pay_sn = $this->_logic_buy_1->makePaySn($param['member_id']);
     * $order_pay = array();
     * $order_pay['pay_sn'] = $pay_sn;
     * $order_pay['buyer_id'] = $member_id;
     * $order_pay_id = $model_order->addOrderPay($order_pay);
     * if (!$order_pay_id) {
     * throw new Exception('订单保存失败[未生成支付单]');
     * }
     * $order = array(
     * 'order_sn' => $this->_logic_buy_1->makeOrderSn($order_pay_id),
     * 'pay_sn' => $pay_sn,
     * 'store_id' => $store_id,
     * 'store_name' => $seller['store_name'],
     * 'buyer_id' => $member_id,
     * 'buyer_name' => $member['member_name'],
     * 'buyer_email' => $member['member_email'],
     * 'add_time' => TIMESTAMP,
     * 'payment_code' => 'offline',
     * 'payment_time' => TIMESTAMP,
     * 'finnshed_time' => TIMESTAMP,
     * 'goods_amount' => $money,
     * 'order_amount' => $money,
     * 'order_state' => ORDER_STATE_SUCCESS,
     * 'order_from' => $order_from,
     * 'goods_type' => ($goods_id > 0) ? 1 : 0,
     * 'commis_rate' => empty($goods_id) ? $use_rebate: 0,
     * 'order_type' => $seller['store_type'],
     * );
     * $order_id = $model_order->addOrder($order);
     * if (!$order_id) {
     * throw new Exception('订单保存失败[未生成订单数据]');
     * }
     * //订单扩展表
     * $order_common = array(
     * 'order_id' => $order_id,
     * 'store_id' => $store_id,
     * );
     * $order_id = $model_order->addOrderCommon($order_common);
     * if (!$order_id) {
     * throw new Exception('订单保存失败[未生成订单扩展数据]');
     * }
     * $rebate_arr = array();
     * //订单商品表
     * if (!empty($goods_id))
     * {
     * $order_good = array(
     * 'order_id' => $order_id,
     * 'goods_id' => $goods_id,
     * 'goods_name' => $good_arr['goods_name'],
     * 'goods_price' => $good_arr['goods_price'],
     * 'goods_image' => $good_arr['goods_image'],
     * 'goods_pay_price' => $money,
     * 'store_id' => $store_id,
     * 'buyer_id' => $member_id,
     * 'commis_rate' => $use_rebate,
     * 'gc_id' => $good_arr['gc_id'],
     * );
     *
     * $insert = $model->table('order_goods')->insert($order_good);
     * if (!$insert) {
     * throw new Exception('订单保存失败[未生成商品数据]');
     * }
     *
     * $rebate_arr[] = array(
     * 'goods_id' => $goods_id,
     * 'goods_name' => $good_arr['goods_name'],
     * 'order_id' => $order_id,
     * 'rebate' => $rebate_money,
     * 'store_id' => $store_id,
     * 'store_name' => $seller['store_name'],
     * 'rebate_type' => 1,
     * 'order_sn' => $order['order_sn'],
     * 'add_time' => TIMESTAMP,
     * 'amount' => $money,
     * );
     * }
     * else
     * {
     * $rebate_arr[] = array(
     * 'goods_id' => 0,
     * 'goods_name' => '商户返利',
     * 'order_id' => $order_id,
     * 'rebate' => $rebate_money,
     * 'store_id' => $store_id,
     * 'store_name' => $seller['store_name'],
     * 'rebate_type' => 1,
     * 'order_sn' => $order['order_sn'],
     * 'add_time' => TIMESTAMP,
     * 'amount' => $money,
     * );
     * }
     *
     * if ($rebate_money > 0)
     * {
     * //分利
     * $order_logic = Logic('order');
     * //分销商参与返利额
     * $agents = $model->table('agent_store')->where(array('store_id'=>$store_id))->limit(1)->select();
     *
     * $order_logic->execRebate(array($member), $agents,$rebate_arr, array($sell_available_predeposit));
     * }
     *
     * $order_log = array(
     * 'order_id' => $order_id,
     * 'log_msg' => '确认付款',
     * 'log_time' => TIMESTAMP,
     * 'log_role' => '商家',
     * 'log_user' => $opt_member['seller_name'],
     * 'log_orderstate' => ORDER_STATE_SUCCESS,
     * );
     * $insert = $model->table('order_log')->insert($order_log);
     * if (!$insert) {
     * throw new Exception('订单日志写入失败');
     * }
     * $model->commit();
     * return callback(true,'',$this->_order_data);
     * }catch (Exception $e){
     * $model->rollback();
     * return callback(false, $e->getMessage());
     * }
     *
     *
     * }
     */
    /**
     * 用户面对面付，商户确认极光消息推送给用户
     * 
     * @param unknown $order_arr
     *            订单信息，包括订单商品信息
     */
    public function sellerLocalSureJgPush($order_arr, $store_info)
    {
        $order_good = isset($order_arr['extend_order_goods']) ? $order_arr['extend_order_goods'][0] : array();
        // 返利金额
        $rebate_condition = array(
            'order_id'    => $order_arr['order_id'],
            'member_id'  => $order_arr['buyer_id'],
            'user_type'   => 1,
        );
        //返利信息
        $rebate_records_detail = Model('rebate_records')->getRebateRecordsInfo($rebate_condition);
        //商户信息
        //$store_info = Model('store')->getStoreInfoByID($order_arr['store_id']);
        $jpush_data_result = array(
            'order' => array(
                'order_sn' => $order_arr['order_sn'],
                'pay_sn' => $order_arr['pay_sn'],
                'order_id' => $order_arr['order_id'],
                'order_amount' => $order_arr['order_amount'],
                'payment_time' => date('Y-m-d H:i', time()),
                'goods_type' => $order_arr['goods_type'],
                'pd_amount'    => price_format($order_arr['pd_amount']), // 抵用预存款(积分)
                'realpay_amount' => price_format($order_arr['order_amount'] - $order_arr['pd_amount']), //实际支付
                'rebate' => empty($rebate_records_detail) ? '0.00' : price_format($rebate_records_detail['rebate']), 
                'goods_id' => empty($order_good) ? 0 : $order_good['goods_id'],
                'consume_code' => $order_arr['consume_code'],
                'goods_num' => empty($order_good) ? 0 : $order_good['goods_num'],
            ),
            'store' => array(
                'store_id' => $store_info['store_id'],
                'store_name' => $store_info['store_name'],
                'area_info' => $store_info['area_info'] . $store_info['store_address'],
                'mobile' => $store_info['store_phone'],
            ),
        );
        $message_type = ($order_arr['consume_code'] != 0) ? 'LOCAL_ORDER_SELLER_CONSUME_CODE_SURE'  : 'LOCAL_ORDER_SELLER_SURE';
        QueueClient::push('jpush', array(
            //'message' => '订单已确认',
            'message' => '',
            'member_ids' => $order_arr['buyer_id'],
            'extend' => array(
                // 'audience_tag' => array('v2.1.0'),
                'extras' => array(
                    'data' => array(
                        'message_type' => $message_type,
                        'message_data' => $jpush_data_result
                    )
                )
            )
        ));
    }

    /**
     * 确认订单页面，购物车数据，以及默认收货地址
     * @param $member_id
     *
     */
    public function adt_orderConfirmInfo($member_id,$param,$goods_buy_info=array())
    {
        $store_id=isset($param['store_id'])?intval($param['store_id']):0;
        $address_id=isset($param['address_id'])?intval($param['address_id']):0;
        $lat=$param['lat'];
        $lng=$param['lng'];

        //1.如果指定地址了，查指定的。如果没有指定地址，找附近的地址，如果附近没有，不返回地址
        if($address_id!=0) {
            $address_condition['member_id']=$member_id;
            $address_condition['address_id']=$address_id;
            $address_info= Model('address')->adt_getDefaultAddressInfo($address_condition);
        }else{
            $address_info = Model('address')->adt_getAddressNearby($member_id, $lat, $lng);
        }

        //如果有地址，查找对应的商户。如果没有地址，用首页用的商户
        if(!empty($address_info)){
            $store_info=Model('store')->adt_get_store_by_lication($address_info['lat'],$address_info['lng']);
            if(!empty($store_info)){
                $store_id=$store_info['store_id'];
                $return['store_info']=$store_info;
            }else{
                $store_id=0;
                $return['store_info']=array();
            }
        }
        $cart_info = Model('cart')->adt_cartList($member_id, $store_id,$goods_buy_info);

        $return['cart_info']=$cart_info;
        $return['address_info']=$address_info;
        return $return;
    }


         /**
     * 加入购物的时候  保存格式化商品规格
     * @param  [type] $array 商品规格 序列化格式
     * @return [type]        [description]
     * xuping
     */
    private function formart_goods_spec($array){
        foreach ($array as $key => $value) {
            $tmp=Model()->table('spec_value')->where(array('sp_value_id'=>$key))->field('sp_id')->find();
            $result=Model()->table('spec')->where(array('sp_id'=>$tmp['sp_id']))->field('sp_name')->find();
            $arr[$result['sp_name']]=$value;
            return serialize($arr);
        }

    }
    
}

