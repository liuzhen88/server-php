<?php
/**
 * 我的购物车
 *
 */


defined('emall') or exit('Access Invalid!');

class member_cart_leagueControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 购物车列表
     */
    public function cart_listOp() {
        $check_param=array('store_id');
        check_request_parameter($check_param);
        $store_id = intval(abs($_REQUEST['store_id']));

        $cart_list=Model('cart')->adt_cartList($this->member_info['member_id'],$store_id);
        output_data($cart_list);
    }

    /**
     * 购物车添加
     */
    public function cart_addOp() {
        $check_param=array('store_id','goods_id','quantity');
        check_request_parameter($check_param);
        $goods_id = intval(abs($_REQUEST['goods_id']));
        $quantity = intval(abs($_REQUEST['quantity']));
        $store_id = intval(abs($_REQUEST['store_id']));

        if($goods_id <= 0 ) {
            output_error('参数错误');
        }
        $model_cart	= Model('cart_league');

        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['goods_id'] = $goods_id;
        if( $quantity <= 0){//删除购物车商品
            $res=$model_cart->where($condition)->delete();
            output_data('1');
        }


        $model_goods = Model('goods');
        $logic_buy_1 = Logic('buy_1');
        $goods_info_league=Model('goods_league')->where(array('goods_id'=>$goods_id,'league_store_id'=>$store_id,'league_goods_verify'=>1))->find();
        if(empty($goods_info_league)){
            output_error('商品不存在或未审核');
        }
        $goods_info = $model_goods->getGoodsSellingById($goods_id);
        //验证是否可以购买
        if(empty($goods_info)) {
            output_error('商品已下架或不存在');
        }
        if ($goods_info['store_id'] == $this->member_info['store_id']) {
            output_error('不能购买自己发布的商品');
        }
        //检查库存是否充足
        if(!$this->adt_checkout_goods_storage($goods_info_league,$quantity)) {
            output_error('库存不足');
        }

        //检查购物车商品是否存在，如果存在修改购物车数量
        $check_cart	= $model_cart->where($condition)->select();
        if (!empty($check_cart)){
            $this->cart_edit_quantityOp();
            exit;
        }

        $param = array();
        $param['buyer_id']	= $this->member_info['member_id'];
        $param['goods_id']	= $goods_id;
        $param['goods_num']	=$quantity;

        $result = $model_cart->insert($param);
        if($result) {
            output_data('1');
        } else {
            output_error('添加失败');
        }
    }

    /**
     * 批量加入购物车
     * cart_info goods_id|goods_num
     * store_id  店铺id
     */
    public function adt_cart_add_allOp(){

        $check_param=array('store_id','cart_info');
        check_request_parameter($check_param);
        $store_id = intval(abs($_REQUEST['store_id']));
        $cart_info=explode(',',$_REQUEST['cart_info']);
        $cart_param = array();

        $goods_buy_info = $this->_parseItems($cart_info);
        if (empty($goods_buy_info)) {
            output_error('参数错误');
        }
        $goods_id_arr=array_keys($goods_buy_info);

        $model_goods = Model('goods');
        $goods_info_league=Model('goods_league')->where(array('goods_id'=>array('in',$goods_id_arr),'league_store_id'=>$store_id,'league_goods_verify'=>1))->select();
        if(empty($goods_info_league)){
            output_error('商品不存在或未审核');
        }
        $goods_info_league=array_under_reset($goods_info_league,'goods_id');
        foreach($goods_buy_info as $goods_id=>$goods_quantity){
            if($goods_quantity<=0){
                output_error('参数错误');
            }
            $goods_info = $model_goods->getGoodsSellingById($goods_id);                                                 //验证是否可以购买，从缓存中取的，可以写在循环里
            if(empty($goods_info)) {
                output_error('商品已下架或不存在');
            }
            if ($goods_info['store_id'] == $this->member_info['store_id']) {
                output_error('不能购买自己发布的商品');
            }
            if(!isset($goods_info_league[$goods_id])){
                output_error('商品不存在或未审核');
            }
            //检查库存是否充足
            if(!$this->adt_checkout_goods_storage($goods_info_league[$goods_id],$goods_quantity)) {
                output_error('库存不足');
            }
            $cart_param[]=array(
                'buyer_id'=>$this->member_info['member_id'],
                'goods_id'=>$goods_id,
                'goods_num'=>$goods_quantity,
            );
        }

        $condition = array();
        $condition['buyer_id'] = $this->member_info['member_id'];
//        $condition['goods_id'] = array('in',$goods_id_arr);

        //检查购物车商品是否存在，如果存在修改购物车数量,不存在，更新购物车
        $model_cart	= Model('cart_league');
        $model_cart->where($condition)->delete();
        $result = $model_cart->insertAll($cart_param);
        if($result) {
            output_data('1');
        } else {
            output_error('添加失败');
        }
    }

    /**
     * 购物车删除
     */
    public function cart_delOp() {
        $cart_id = intval($_REQUEST['cart_id']);

        $model_cart = Model('cart_league');

        if($cart_id > 0) {
            $condition = array();
            $condition['buyer_id'] = $this->member_info['member_id'];
            $condition['cart_id'] = $cart_id;
            $model_cart->where($condition)->delete();
        }else{
            $condition = array();
            $condition['buyer_id'] = $this->member_info['member_id'];
            $model_cart->where($condition)->delete();
        }
        output_data('1');
    }

    /**
     * 更新购物车购买数量
     */
    public function cart_edit_quantityOp() {
        $check_param=array('store_id','goods_id','quantity');
        check_request_parameter($check_param);
        $goods_id = intval(abs($_REQUEST['goods_id']));
        $quantity = intval(abs($_REQUEST['quantity']));
        $store_id = intval(abs($_REQUEST['store_id']));
        if(empty($goods_id) || empty($quantity)) {
            output_error('参数错误');
        }

        $model_cart = Model('cart_league');

        $cart_info = $model_cart->where(array('goods_id'=>$goods_id, 'buyer_id' => $this->member_info['member_id']))->find();

        //检查是否为本人购物车
        if($cart_info['buyer_id'] != $this->member_info['member_id']) {
            output_error('参数错误');
        }

        //检查库存是否充足
        if(!$this->_check_goods_storage($goods_id, $quantity, $store_id)) {
            output_error('库存不足');
        }

        $data = array();
        $data['goods_num'] = $quantity;
        $update = $model_cart->where(array('cart_id'=>$cart_info['cart_id']))->update($data);
        if ($update) {
            $return = array();
            $return['quantity'] = $quantity;
            output_data($return);
        } else {
            output_error('修改失败');
        }
    }

    /**
     * 检查库存是否充足
     */
    private function _check_goods_storage($goods_id, $quantity, $store_id) {
        $goods_info=Model('goods_league')->where(array('goods_id'=>$goods_id,'league_store_id'=>$store_id,'league_goods_verify'=>1))->find();
        if(!empty($goods_info)){
            $storage=(0==$goods_info['league_goods_promotion_type'])?$goods_info['league_goods_storage']:$goods_info['league_goods_promotion_storage'];
            if($storage>=$quantity) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $league_goods goods_league 表的数据
     * @param $quantity
     * @return bool
     */
    private function adt_checkout_goods_storage($league_goods_info,$quantity){
        if(!empty($league_goods_info)){
            $storage=(0==$league_goods_info['league_goods_promotion_type'])?$league_goods_info['league_goods_storage']:$league_goods_info['league_goods_promotion_storage'];
            if($storage>=$quantity) {
                return true;
            }
        }
        return false;
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

}
