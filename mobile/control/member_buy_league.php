<?php
/**
 * 购买
 *
 */



defined('emall') or exit('Access Invalid!');

class member_buy_leagueControl extends mobileMemberControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 直接购买第一步，确认订单页面接口,移除购物车逻辑,adt_buy_step1Op无购物车版
     */
    public function adb_buy_step1_nocartOp(){
        $check_param=array('store_id','lat','lng','cart_info');
        check_request_parameter($check_param);
        $store_id = intval(abs($_REQUEST['store_id']));
        $cart_info=explode(',',$_REQUEST['cart_info']);
        $goods_buy_info = $this->_parseItems($cart_info);

        $logic_buy = logic('buy');

        //订单确认数据，购物车商品信息，收货地址(附近的地址，或者空)
        $param['address_id']=isset($_REQUEST['address_id'])?intval($_REQUEST['address_id']):0;
        $param['store_id']=$store_id;
        $param['lat']=floatval($_REQUEST['lat']);
        $param['lng']=floatval($_REQUEST['lng']);
        $return_data=$logic_buy->adt_orderConfirmInfo($this->member_info['member_id'],$param,$goods_buy_info);
        if(!isset($return_data['store_info'])) {
            $field=array('store_id','store_name','ship_time','open_state');
            $store_info=Model('store')->field($field)->where(array('store_id'=>$store_id,'store_type'=>4,'store_state'=>1))->find();
            if(empty($store_info)){
                output_error('参数错误，店铺不存在');
            }
            $return_data['store_info']=$store_info;
        }


        $address_condition['member_id']=$this->member_info['member_id'];
        $address_info= Model('address')->adt_getDefaultAddressInfo($address_condition);
        $return_data['has_address']=empty($address_info)?0:1;

        output_data($return_data);

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
                        $buy_items[]=array('goods_id'=>$match[1][0],'goods_num'=>$match[2][0]);
                    }
                }
            }
        }
        return $buy_items;
    }

    /**
     *
     * 购物车、直接购买第一步:选择收获地址和配置方式
     * 业务逻辑说明：
     * 1.如果你传了address_id,就查指定的地址，
     * 2.如果没传address_id,就按经纬度查附近的地址
     * 3.如果没有查到地址，地址返回空数组
     *
     * 4.如果查到了地址，就返回该地址附近的商户信息和对应的商品价格等信息
     * 5.如果地址附近没有商户，商品都会标记为不可购买，store_info为空数组
     *
     * 6.如果没有收货地址，返回你传过来的store_id的商品信息（首页定位时取得的店铺）
     */
    public function adt_buy_step1Op()
    {
        $check_param=array('store_id','lat','lng');
        check_request_parameter($check_param);
        $store_id = intval(abs($_REQUEST['store_id']));

        $logic_buy = logic('buy');

        //订单确认数据，购物车商品信息，收货地址(附近的地址，或者空)
        $param['address_id']=isset($_REQUEST['address_id'])?intval($_REQUEST['address_id']):0;
        $param['store_id']=$store_id;
        $param['lat']=floatval($_REQUEST['lat']);
        $param['lng']=floatval($_REQUEST['lng']);
        $return_data=$logic_buy->adt_orderConfirmInfo($this->member_info['member_id'],$param);
        if(!isset($return_data['store_info'])) {
            $field=array('store_id','store_name','ship_time','open_state');
            $store_info=Model('store')->field($field)->where(array('store_id'=>$store_id,'store_type'=>4,'store_state'=>1))->find();
            if(empty($store_info)){
                output_error('参数错误，店铺不存在');
            }
            $return_data['store_info']=$store_info;
        }


        $address_condition['member_id']=$this->member_info['member_id'];
        $address_info= Model('address')->adt_getDefaultAddressInfo($address_condition);
        $return_data['has_address']=empty($address_info)?0:1;

        output_data($return_data);

        //这两到底有啥意义?
/*        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['vat_hash'] = $result['vat_hash'];*/
    }

    /**
     * 1.判断有没有默认收货地址
     * 2.根据收货地址查找店铺id，判断哪些商品可以买，哪些商品不可用买
     * 3.生成订单
     * 4.清空购物车
     *
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     * @param string $param ['key']       唯一校验码
     * @param string $param ['address_id']      地址
     * @param string $param ['goods_buy']       商品信息 id1|num1,id2|num2,id3|num3
     * @param string $param ['order_message']   买家留言
     * @param string $param ['date']   预期收货时间，0：今天，1：明天，2：后天
     * @param string $param ['time']   预期收货时间，如8:00-9:00
     * @param string $param ['coupon_id']  优惠券id，可以使用
     */
    public function adt_buy_step2Op()
    {
        $check_param=array('address_id','goods_buy','date','time');
        check_request_parameter($check_param);
        $param = array();
        $param['address_id'] = $_REQUEST['address_id'];
        $param['goods_buy'] = explode(',',$_REQUEST['goods_buy']);
        $param['order_message'] = isset($_REQUEST['order_message'])?$_REQUEST['order_message']:'';
        $param['coupon_id'] = isset($_REQUEST['coupon_id'])?intval($_REQUEST['coupon_id']):0;
        $param['date'] = $_REQUEST['date'];
        $param['time'] = $_REQUEST['time'];
        $param['hope_receive_time'] = ((0==$_REQUEST['date'])?'今天':((1==$_REQUEST['date'])?'明天':'后天')).','.(0==($_REQUEST['time'])?'及时达(1小时内)':$_REQUEST['time']);
        $param['is_get_quickly'] = (0==$_REQUEST['time'])?1:0;
        if(!in_array($_REQUEST['date'],array(0,1,2))){
            output_error('参数不合法:date');
        }


        $logic_buy = logic('buy');
        $result = $logic_buy->adt_buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email']);
        if (!$result['state']) {
            output_error($result['msg']);
        }
        output_data(array(
                'pay_sn' => $result['data']['pay_sn'],
                'order_sn' => $result['data']['order_sn'],
                'order_amount'=>$result['data']['order_amount'],
                'order_id'=>$result['data']['order_id'],
        ));
    }


    /**
     * 选择支付页面，获取可用的最实惠的优惠券(优惠券只根据商品总金额计算)
     * order_amount：订单总金额
     * shipping_fee:运费 0:无运费，1（或运费值）:要运费
     */
    public function adt_get_useable_couponOp(){
        $check_param=array('order_amount','shipping_fee');
        check_request_parameter($check_param);
        $shipping_fee=floatval($_REQUEST['shipping_fee']);
        $order_amount=floatval($_REQUEST['order_amount']);
        $goods_amount=$order_amount- ADT_CARRIAGE*(0!=$shipping_fee);
        if($goods_amount<0){
            output_error('参数错误');
        }
        $member_id=$this->member_info['member_id'];

        $best=Logic('buy_1')->adtGetUseableCoupon($member_id,$order_amount,$shipping_fee);
        output_data($best);

    }


}

