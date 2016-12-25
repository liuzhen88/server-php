<?php
/**
 * 购物车模型
 *
 *

 */
defined('emall') or exit('Access Invalid!');
class cartModel extends Model {

    /**
     * 购物车商品总金额
     */
    private $cart_all_price = 0;

    /**
     * 购物车商品总数
     */
    private $cart_goods_num = 0;

    public function __construct() {
       parent::__construct('cart');
    }

    /**
     * 取属性值魔术方法
     *
     * @param string $name
     */
    public function __get($name) {
        return $this->$name;
    }

	/**
	 * 检查购物车内商品是否存在
	 *
	 * @param
	 */
	public function checkCart($condition = array()) {
	    return $this->where($condition)->find();
	}

	/**
	 * 取得 单条购物车信息
	 * @param unknown $condition
	 * @param string $field
	 */
	public function getCartInfo($condition = array(), $field = '*') {
	   return $this->field($field)->where($condition)->find();
	}

	/**
	 * 将商品添加到购物车中
	 *
	 * @param array	$data	商品数据信息
	 * @param string $save_type 保存类型，可选值 db,cookie
	 * @param int $quantity 购物数量
	 */
	public function addCart($data = array(), $save_type = '', $quantity = null) {
        $method = '_addCart'.ucfirst($save_type);
	    $insert = $this->$method($data,$quantity);
	    //更改购物车总商品数和总金额，传递数组参数只是给DB使用
	    $this->getCartNum($save_type,array('buyer_id'=>$data['buyer_id']));
	    return $insert;
	}

	/**
	 * 添加数据库购物车
	 *
	 * @param unknown_type $goods_info
	 * @param unknown_type $quantity
	 * @return unknown
	 */
	private function _addCartDb($goods_info = array(),$quantity) {
	    //验证购物车商品是否已经存在

	    $condition = array();
	    $condition['goods_id'] = $goods_info['goods_id'];
	    $condition['buyer_id'] = $goods_info['buyer_id'];
	    if (isset($goods_info['bl_id'])) {
	        $condition['bl_id'] = $goods_info['bl_id'];
	    } else {
	        $condition['bl_id'] = 0;
	    }
    	$check_cart	= $this->checkCart($condition);
    	if (!empty($check_cart)) return true;
 
		$array    = array();


		$array['goods_spec']	= unserialize($goods_info['goods_spec'])? $this->formart_goods_spec(unserialize($goods_info['goods_spec']),$goods_info['goods_commonid']):$goods_info['goods_spec'];
		$array['buyer_id']	= $goods_info['buyer_id'];
		$array['store_id']	= $goods_info['store_id'];
		$array['goods_id']	= $goods_info['goods_id'];
		$array['goods_name'] = $goods_info['goods_name'];
		$array['goods_price'] = $goods_info['goods_price'];
		$array['goods_num']   = $quantity;
		$array['goods_image'] = $goods_info['goods_image'];
		$array['store_name'] = $goods_info['store_name'];
		$array['is_distribution'] = intval($goods_info['is_distribution']);
		$array['dis_store_member_id'] = intval($goods_info['dis_store_member_id']);
		$array['dis_store_id'] = intval($goods_info['dis_store_id']);
		$array['dis_member_id'] = intval($goods_info['dis_member_id']);
		$array['bl_id'] = isset($goods_info['bl_id']) ? $goods_info['bl_id'] : 0;
		return $this->insert($array);
	}

	 /**
     * 加入购物的时候  保存格式化商品规格
     * @param  [type] $array 商品规格 序列化格式
     * @return [type]        [description]
     * xuping
     */
    private function formart_goods_spec($array,$goods_commonid){
        // foreach ($array as $key => $value) {
        //     $tmp=Model()->table('spec_value')->where(array('sp_value_id'=>$key))->field('sp_id')->find();
        //     $result=Model()->table('spec')->where(array('sp_id'=>$tmp['sp_id']))->field('sp_name')->find();
        //     $arr[$result['sp_name']]=$value;
        //     return serialize($arr);
        // }
        $result=Model('goods')->getGoodeCommonInfo(array('goods_commonid'=>$goods_commonid),'spec_name,spec_value');
        $spec_name=array_values(unserialize($result['spec_name']));
        $res=array_combine($spec_name,$array);
       	return serialize($res);
    }



	/**
	 * 添加到cookie购物车,最多保存5个商品
	 *
	 * @param unknown_type $goods_info
	 * @param unknown_type $quantity
	 * @return unknown
	 */
	private function _addCartCookie($goods_info = array(), $quantity = null) {
    	//去除斜杠
    	$cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('cart')) : cookie('cart');
    	$cart_str = base64_decode(decrypt($cart_str));
    	$cart_array = @unserialize($cart_str);
    	$cart_array = !is_array($cart_array) ? array() : $cart_array;
    	if (count($cart_array) >= 5) return false;

    	if (in_array($goods_info['goods_id'],array_keys($cart_array))) return true;
		$cart_array[$goods_info['goods_id']] = array(
		  'store_id' => $goods_info['store_id'],
		  'goods_id' => $goods_info['goods_id'],
		  'goods_name' => $goods_info['goods_name'],
		  'goods_price' => $goods_info['goods_price'],
		  'goods_image' => $goods_info['goods_image'],
		  'goods_num' => $quantity
		);
		setNcCookie('cart',encrypt(base64_encode(serialize($cart_array))),24*3600);
		return true;
	}

	/**
	 * 更新购物车
	 *
	 * @param	array	$param 商品信息
	 */
	public function editCart($data,$condition) {
		$result	= $this->where($condition)->update($data);
		if ($result) {
		    $this->getCartNum('db',array('buyer_id'=>$condition['buyer_id']));
		}
		return $result;
	}

	/**
	 * 购物车列表
	 *
	 * @param string $type 存储类型 db,cookie
	 * @param unknown_type $condition
	 * @param int $limit
	 */
	public function listCart($type, $condition = array(), $limit = '') {
        if ($type == 'db') {
    		$cart_list = $this->where($condition)->limit($limit)->order('store_id')->select();
        } elseif ($type == 'cookie') {
        	//去除斜杠
        	$cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('cart')) : cookie('cart');
        	$cart_str = base64_decode(decrypt($cart_str));
        	$cart_list = @unserialize($cart_str);
        }
        $cart_list = is_array($cart_list) ? $cart_list : array();
        //顺便设置购物车商品数和总金额
		$this->cart_goods_num =  count($cart_list);
	    $cart_all_price = 0;
		if(is_array($cart_list)) {
			foreach ($cart_list as $val) {
				$cart_all_price	+= $val['goods_price'] * $val['goods_num'];
			}
		}
        $this->cart_all_price = ncPriceFormat($cart_all_price);
		return !is_array($cart_list) ? array() : $cart_list;
	}

	public function new_listCart($type, $condition = array(), $limit = '') {
		if ($type == 'db') {
			$field='store_name,store_id';
			$cart_list = $this->where($condition)->limit($limit)->order('store_id')->field($field)->group('store_id')->select();
		}

		$where['buyer_id']=$condition['buyer_id'];
		foreach($cart_list as $key=>$value){
			$where['store_id']=$value['store_id'];
			$fields='cart_id,goods_id,goods_name,goods_price,goods_num,goods_image,goods_spec';
			$data=$this->where($where)->field($fields)->select();
			foreach($data as $k=>$v){
				$data[$k]['goods_image_url'] = cthumb($v['goods_image'],'60', $v['store_id']); unset($data[$k]['goods_image']);
				$data[$k]['total_price'] = ncPriceFormat($v['goods_price'] * $v['goods_num']);
				$data[$k]['goods_price'] = del0($v['goods_price']);
				$data[$k]['goods_spec']=(unserialize($v['goods_spec']))? (array)unserialize($v['goods_spec']):array();
//            	//$sum += $cart_list[$key]['goods_sum'];
			}
			$cart_list[$key]['cart_list'] =$data;
		}

		$cart_list = is_array($cart_list) ? $cart_list : array();

		return !is_array($cart_list) ? array() : $cart_list;
	}


	/**
	 * 删除购物车商品
	 *
	 * @param string $type 存储类型 db,cookie
	 * @param unknown_type $condition
	 */
	public function delCart($type, $condition = array()) {
	    if ($type == 'db') {
    		$result =  $this->where($condition)->delete();
	    } elseif ($type == 'cookie') {
        	$cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('cart')) : cookie('cart');
        	$cart_str = base64_decode(decrypt($cart_str));
        	$cart_array = @unserialize($cart_str);
            if (key_exists($condition['goods_id'],(array)$cart_array)) {
                unset($cart_array[$condition['goods_id']]);
            }
            setNcCookie('cart',encrypt(base64_encode(serialize($cart_array))),24*3600);
            $result = true;
	    }
	    //重新计算购物车商品数和总金额
		if ($result) {
		    $this->getCartNum($type,array('buyer_id'=>$condition['buyer_id']));
		}
		return $result;
	}

	/**
	 * 清空购物车
	 *
	 * @param string $type 存储类型 db,cookie
	 * @param unknown_type $condition
	 */
	public function clearCart($type, $condition = array()) {
	    if ($type == 'cookie') {
            setNcCookie('cart','',-3600);
	    } else if ($type == 'db') {
	        //数据库暂无浅清空操作
	    }
	}

	/**
	 * 计算购物车总商品数和总金额
	 * @param string $type 购物车信息保存类型 db,cookie
	 * @param array $condition 只有登录后操作购物车表时才会用到该参数
	 */
	public function getCartNum($type, $condition = array()) {
	    if ($type == 'db') {
    	    $cart_all_price = 0;
    		$cart_goods	= $this->listCart('db',$condition);
    		$this->cart_goods_num = count($cart_goods);
    		if(!empty($cart_goods) && is_array($cart_goods)) {
    			foreach ($cart_goods as $val) {
    				$cart_all_price	+= $val['goods_price'] * $val['goods_num'];
    			}
    		}
		  $this->cart_all_price = ncPriceFormat($cart_all_price);
	    } elseif ($type == 'cookie') {
        	$cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('cart')) : cookie('cart');
        	$cart_str = base64_decode(decrypt($cart_str));
        	$cart_array = @unserialize($cart_str);
        	$cart_array = !is_array($cart_array) ? array() : $cart_array;
    		$this->cart_goods_num = count($cart_array);
    		$cart_all_price = 0;
    		foreach ($cart_array as $v){
    			$cart_all_price += floatval($v['goods_price'])*intval($v['goods_num']);
    		}
    		$this->cart_all_price = $cart_all_price;
	    }
	    @setNcCookie('cart_goods_num',$this->cart_goods_num,2*3600);
	    return $this->cart_goods_num;
	}

    /**
     * 登录之后,把登录前购物车内的商品加到购物车表
     *
     */
    public function mergecart($member_info = array(), $store_id = null){
        if (!$member_info['member_id']) return;
        // $save_type = C('cache.type') != 'file' ? 'cache' : 'cookie';
        $save_type = 'cookie';
        $cart_new_list = $this->listCart($save_type);
        if (empty($cart_new_list)) return;

        //取出当前DB购物车已有信息
        $cart_cur_list = $this->listCart('db',array('buyer_id'=>$member_info['member_id']));

        //数据库购物车已经有的商品，不再添加
        if (!empty($cart_cur_list) && is_array($cart_cur_list) && is_array($cart_new_list)) {
            foreach ($cart_new_list as $k=>$v){
                if (!is_numeric($k) || in_array($k,array_keys($cart_cur_list))){
                    unset($cart_new_list[$k]);
                }
            }
        }

        //查询在购物车中,不是店铺自己的商品，未禁售，上架，有库存的商品,并加入DB购物车
        $model_goods = Model('goods');
        $condition = array();
        if (!empty($_SESSION['store_id'])) {
            $condition['store_id'] = array('neq',$store_id);
        }
        $condition['goods_id'] = array('in',array_keys($cart_new_list));
        $goods_list = $model_goods->getGoodsOnlineList($condition);
        if (!empty($goods_list)){
            foreach ($goods_list as $goods_info){
                $goods_info['buyer_id']	= $member_info['member_id'];
                $this->addCart($goods_info,'db',$cart_new_list[$goods_info['goods_id']]['goods_num']);
            }
        }
        //最后清空登录前购物车内容
        $this->clearCart($save_type);
    }

	/**
	 * 购物车列表数据
	 */
	public function adt_cartList($member_id,$store_id,$cart_list=array()){
		if(empty($cart_list)) {
			$model_cart = Model('cart_league');
			$condition = array('buyer_id' => $member_id);
			$cart_list = $model_cart->where($condition)->select();
		}
		$count=$sum=0;
		$all_buyalbe=1;      //是否所有的商品都可以购买
		foreach ($cart_list as $key => $value) {
			$this_goods_info_league=Model('goods_league')->where(array('goods_id'=>$value['goods_id'],'league_store_id'=>$store_id,'league_goods_verify'=>1))->find();
			if(empty($this_goods_info_league)){
				$this_goods_info=Model('goods')->getGoodsInfoByID($value['goods_id']);
				$cart_list[$key]['goods_sum']=0;
				$cart_list[$key]['goods_name']=$this_goods_info['goods_name'];
				$cart_list[$key]['goods_image']=cthumb($this_goods_info['goods_image'], '', $this_goods_info['store_id']);;
				$cart_list[$key]['goods_price']=0;
				$cart_list[$key]['goods_storage']=0;
				$cart_list[$key]['goods_size']='';
				$cart_list[$key]['buyalbe']=0;
				$all_buyalbe=0;
				continue ;
			}
			$this_price=(1==$this_goods_info_league['league_goods_promotion_type'])?$this_goods_info_league['league_goods_promotion_price']:$this_goods_info_league['league_goods_price'];
			$this_storage=(1==$this_goods_info_league['league_goods_promotion_type'])?$this_goods_info_league['league_goods_promotion_storage']:$this_goods_info_league['league_goods_storage'];
			$cart_list[$key]['goods_sum']=del0($this_price * $value['goods_num']);
			$cart_list[$key]['goods_name']=$this_goods_info_league['goods_name'];
			$cart_list[$key]['goods_image']=cthumb($this_goods_info_league['goods_image'], '', $this_goods_info_league['store_id']);
			$cart_list[$key]['goods_price']=$this_price;
			$cart_list[$key]['goods_storage']=$this_storage;
			$cart_list[$key]['goods_size']=$this_goods_info_league['goods_size'];
			if(($this_storage<$value['goods_num'])) {
				$cart_list[$key]['buyalbe'] = 0;
				$all_buyalbe=0;
			}else{
				$cart_list[$key]['buyalbe'] = 1;
			}
			$sum += $this_price * $value['goods_num'];
			$count++;
		}
		$return=array(
			'cart_list' => $cart_list,
			'all_buyalbe' => $all_buyalbe,
			'money_goods' => del0(ncPriceFormat($sum)),
			'count_goods'=>$count,
			'adt_carriage_this'=>($sum<ADT_FREE_CARRIAGE_LEAVE)?ADT_CARRIAGE:0,	//本单运费
			'adt_carriage_pre'=>ADT_CARRIAGE,						//原始运费
			'adt_free_carriage_leave'=>ADT_FREE_CARRIAGE_LEAVE
		);
		return $return;
	}
}
