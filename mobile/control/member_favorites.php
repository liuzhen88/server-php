<?php

/**
 * 我的收藏|我的关注
 *
 */
use Shopnc\Tpl;

defined('emall') or exit('Access Invalid!');

class member_favoritesControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * 我的收藏(出线上)|我的关注(出本土)
     * 
     * @modify lijunhua
     * @since  2015-08-07
     * @param string $param['key']        登录后唯一校验码
     * @param string $param['fav_type']   收藏类型 1.商品  2.商铺
     * @param string $param['is_online']  ------是否线上：1.本土(默认) 2.线上 新版参数去掉
     * @param string $param['location']   当前地理位置 格式如117.286189,31.886131
     * @param string $param['curpage']    分页 默认1
     */
    public function favorites_listOp() {
        
        $fav = isset($_REQUEST['fav_type']) && !empty($_REQUEST['fav_type']) ? $_REQUEST['fav_type'] : 1;

        $model_favorites = Model('favorites');

        $condition_arr = array(
            'member_id' => $this->member_info['member_id'],
            'fav_type'  => ($fav == 1) ? 'goods' : 'store',
            //'is_online' => isset($_REQUEST['is_online']) && !empty($_REQUEST['is_online']) ? $_REQUEST['is_online'] : 1,
        );
        $is_online = isset($_REQUEST['is_online']) ? intval($_REQUEST['is_online']) : 0;
        $is_online && $condition_arr['is_online'] = $is_online;

        $favorites_list = $model_favorites->getFavoritesList($condition_arr, '*', $this->page);
        $page_count = $model_favorites->gettotalpage();
        $favorites_id = '';
        foreach ($favorites_list as $value) {   
            $favorites_id .= $value['fav_id'] . ',';
        }
        $favorites_id = rtrim($favorites_id, ',');

        // 商品收藏
        if ($fav == 1) {
            $model_goods = Model('goods');
            $field = 'goods_id,goods_name,goods_price,goods_marketprice,goods_image,store_name,good_type';
            $goods_list = $model_goods->getGoodsList(array('goods_id' => array('in', $favorites_id)), $field);
            $model_store = Model('store');
            foreach ($goods_list as $key => $value) {
                $goods_list[$key]['fav_id'] = $value['goods_id'];
                $goods_list[$key]['id']     = $value['goods_id']; // 兼容商品详情的商品id以id标识返回
                $goods_list[$key]['goods_image_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $goods_list[$key]['goods_price'] = del0($value['goods_price']);
                $goods_list[$key]['goods_marketprice'] = del0($value['goods_marketprice']);
                $goods_list[$key]['is_online'] = $value['good_type'];
                if($value['good_type'] == 2)
                    $goods_list[$key]['goods_url'] = C('wap_site_url').'/tmpl/productdetail.html?goods_id='.$value['goods_id'];
                else
                   $goods_list[$key]['goods_url'] = ''; 
            }
            output_data(array('favorites_list' => $goods_list), mobile_page($page_count));
        }

       // 商铺收藏
        else {
            $model_store = Model('store');
            $evaluate = Model('evaluate_goods');
            $field = 'store_id,store_name,grade_id,store_label,CONCAT(area_info," ", store_address) AS area,lng,lat,live_store_tel,store_phone,store_avatar,store_type,store_credit';
            $store_list = $model_store->getStoreList(array('store_id' => array('in', $favorites_id)), null, '',  $field);
            foreach ($store_list as $key => $value) {
                $store_list[$key]['fav_id']          = $value['store_id'];
                $store_list[$key]['store_avatar']    = getStoreLogo($value['store_avatar']);
                $store_list[$key]['distance']        = '';
                if (isset($_REQUEST['location']) && !empty($_REQUEST['location']) 
                    && !empty( $value['lng'])  && !empty($value['lat'])) {
                    $store_list[$key]['distance'] = get_distance($_REQUEST['location'], $value['lng'] . ',' . $value['lat']);
                }
                $store_list[$key]['is_online'] = $value['store_type'];
                $store_list[$key]['store_credit'] = $value['store_credit'];
                $store_list[$key]['store_evaluate_count'] = $evaluate->getStoreCredit(array('geval_storeid'=>$value['store_id']));
            }
            output_data(array('favorites_list' => $store_list), mobile_page($page_count));
        }
    }
    

    /**
     * 添加收藏
     */
    public function favorites_addOp() {
        $goods_id = intval($_REQUEST['goods_id']);
        if ($goods_id <= 0) {
            output_error('参数错误');
        }
        $is_online=(isset($_REQUEST['is_online']))?intval($_REQUEST['is_online']):2;
        if(1!=$is_online) $is_online=2;
        $favorites_model = Model('favorites');

        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array('fav_id' => $goods_id, 'fav_type' => 'goods', 'member_id' => $this->member_info['member_id']));
        if (!empty($favorites_info)) {
            output_error('您已经收藏了该商品');
        }

        //判断商品是否为当前会员所有
        $goods_model = Model('goods');
        $goods_info = $goods_model->getGoodsInfoByID($goods_id);
        $seller_info = Model('seller')->getSellerInfo(array('member_id' => $this->member_info['member_id']));
        if ($goods_info['store_id'] == $seller_info['store_id']) {
            output_error('您不能收藏自己发布的商品');
        }

        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = $this->member_info['member_id'];
        $insert_arr['fav_id'] = $goods_id;
        $insert_arr['fav_type'] = 'goods';
        $insert_arr['is_online'] = $is_online;
        $insert_arr['fav_time'] = TIMESTAMP;
        $result = $favorites_model->addFavorites($insert_arr);

        if ($result) {
            //增加收藏数量
            $goods_model->editGoodsById(array('goods_collect' => array('exp', 'goods_collect + 1')), $goods_id);
            output_data('1');
        } else {
            output_error('收藏失败');
        }
    }

    /**
     * 删除收藏
     */
    public function favorites_delOp() {
        $fav_id = intval($_REQUEST['fav_id']);
        if ($fav_id <= 0) {
            output_error('参数错误');
        }

        $model_favorites = Model('favorites');
        $type = 'goods';
        if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'store') {
            $type = 'store';
        }
        
        $condition = array();
        $condition['fav_id'] = $fav_id;
        $condition['fav_type'] = $type;
        $condition['member_id'] = $this->member_info['member_id'];
        if($type == 'store'){
            //减少收藏数量
            Model()->execute('update agg_store set store_colect=store_collect-1 where store_id=' . $fav_id);
        }elseif($type == 'goods'){
            Model()->execute('update agg_goods set goods_collect=goods_collect-1 where goods_id=' . $fav_id);
        }
        $model_favorites->delFavorites($condition);
        output_data('1');
    }
    
    /**
     * 批量删除收藏
     * 
     * @author lijunhua 
     * @since  2015-09-09
     * 
     * @param string $param['key']        登录后唯一校验码
     * @param string $fav_ids             收藏IDS 格式:12,13,14
     * @param string $type                类型 good|store  默认 goods 
     * @param
     */
    public function favorites_delbatchOp() {
        if (empty($_REQUEST['fav_ids'])) {
            output_error('参数错误');
        }
        
        $fav_ids = implode(',', array_map('intval', explode(',', $_REQUEST['fav_ids'])));

        $model_favorites = Model('favorites');
        $type            = 'goods';
        if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'store') {
            $type = 'store';
        }   
        $condition = array();
        $condition['fav_id_in']    = $fav_ids;
        $condition['fav_type']     = $type;
        $condition['member_id']    = $this->member_info['member_id'];
        if ($type == 'store') {
            //减少收藏数量
            Model()->execute('update agg_store set store_colect=store_collect-1 where store_id IN (' . $fav_ids . ')');
        } elseif ($type == 'goods') {
            Model()->execute('update agg_goods set goods_collect=goods_collect-1 where goods_id IN (' . $fav_ids . ')');
        }
        $rs = $model_favorites->delFavorites($condition);
        output_data('1');
    }
    

    /**
     * 收藏店铺
     */
    public function favorites_sotre_addOp() {
        $check_param = array('store_id');
        check_request_parameter($check_param);
        $store_id = intval($_REQUEST['store_id']);
        if ($store_id <= 0) {
            output_error('参数错误');
        }

        $favorites_model = Model('favorites');

        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array('fav_id' => $store_id, 'fav_type' => 'store', 'member_id' => $this->member_info['member_id']));
        if (!empty($favorites_info)) {
            output_error('您已经收藏了该店铺');
        }

        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = $this->member_info['member_id'];
        $insert_arr['fav_id'] = $store_id;
        $insert_arr['fav_type'] = 'store';
        $insert_arr['is_online'] = '1';     //只有本土使用收藏店铺功能
        $insert_arr['fav_time'] = TIMESTAMP;
        $result = $favorites_model->addFavorites($insert_arr);

        if ($result) {
            //增加收藏数量
            Model()->execute('update agg_store set store_colect=store_collect+1 where store_id=' . $store_id);
            output_data('1');
        } else {
            output_error('收藏失败');
        }
    }

}
