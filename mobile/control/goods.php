<?php
/**
 * 商品
 */

defined('emall') or exit('Access Invalid!');
class goodsControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 下拉提示
     * @param string $query 搜索关键字
     * @return [type] [description]
     */
    public function open_search_auto_down_goodsOp(){

        if(C('open_search.open')){
            $data=Model('open_search_auto_down_local')->index('goods_list');
            output_data($data);
            exit;
        }
        //$where = 'good_type=2 and goods_state=1 and goods_verify=1';
        $where['goods_name|goods_jingle'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        $where['good_type']     =   2;
        $where['goods_state']   =   1;
        $where['goods_verify']  =   1;
        $list_array =   Model('goods')->where($where)->order('sort desc')->limit('10')->select();
        foreach ($list_array as $key => $value) {
            $item[$key]['label'] = $value['goods_name'];
        }
        output_data($item);
    }

    /**
     * 商品列表v2
     */
    public function goods_list_v2Op() {

        if(C('open_search.open')){
            $data=Model('open_search_goods')->get_store_local();
            $store_list=$data['result']['items'];
            foreach($store_list as & $value){
                $value['goods_image']= cthumb($value['goods_image'], 60, $value['store_id']);
                $value['goods_price']=del0($value['goods_price']);
            }
            output_data($store_list);
            exit;
        }

        $model_goods = Model('goods');
        $model_search = Model('search');

        //查询条件
        $condition = array();
        if(!empty($_REQUEST['gc_id']) && intval($_REQUEST['gc_id']) > 0) {
            $condition['gc_id'] = $_REQUEST['gc_id'];
        } elseif (!empty($_REQUEST['keyword'])) {
            $condition['goods_name|goods_jingle'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }

        //排除本土商品
        $condition['good_type']=2;

        //所需字段
        $fieldstr = "goods_id,goods_commonid,store_id,goods_name,goods_price,goods_marketprice,goods_image,goods_salenum,evaluation_good_star,evaluation_count";

        // 添加3个状态字段
        $fieldstr .= ',is_virtual,is_presell,is_fcode,have_gift';

        //排序方式

        $order = $this->_goods_list_order($_REQUEST['key_sort'], $_REQUEST['order']);

        //优先从全文索引库里查找
        list($indexer_ids,$indexer_count) = $model_search->indexerSearch($_REQUEST,$this->page);
        if (is_array($indexer_ids)) {
            //商品主键搜索
            $goods_list = $model_goods->getGoodsOnlineList(array('goods_id'=>array('in',$indexer_ids)), $fieldstr, 0, $order, $this->page, null, false);

            //如果有商品下架等情况，则删除下架商品的搜索索引信息
            if (count($goods_list) != count($indexer_ids)) {
                $model_search->delInvalidGoods($goods_list, $indexer_ids);
            }
            pagecmd('setEachNum',$this->page);
            pagecmd('setTotalNum',$indexer_count);
        } else {
            //商品列表，改为按common_id分组.@todo 上面全文索引可能也需要修改
//            $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, $order, $this->page);
            $count = $model_goods->getGoodsOnlineCount($condition,"distinct (goods_commonid)");
            $goods_list = $model_goods->getGoodsOnlineList($condition, $fieldstr, $this->page, $order, 0, 'goods_commonid', false,$count);
        }
        $page_count = $model_goods->gettotalpage();

        //处理商品列表(抢购、限时折扣、商品图片)
        $goods_list = $this->_goods_list_extend($goods_list);

        output_data(array('goods_list' => $goods_list), mobile_page($page_count));
    }
    /**
     * 商品列表
     */
    public function goods_listOp() {
        $model_goods = Model('goods');
        $model_search = Model('search');

        //查询条件
        $condition = array();
        if(!empty($_REQUEST['gc_id']) && intval($_REQUEST['gc_id']) > 0) {
            $condition['gc_id'] = $_REQUEST['gc_id'];
        } elseif (!empty($_REQUEST['keyword'])) {
            $condition['goods_name|goods_jingle'] = array('like', '%' . $_REQUEST['keyword'] . '%');
        }

        //排除本土商品
        $condition['good_type']=2;

        //所需字段
        $fieldstr = "goods_id,goods_commonid,store_id,goods_name,goods_price,goods_marketprice,goods_image,goods_salenum,evaluation_good_star,evaluation_count";

        // 添加3个状态字段
        $fieldstr .= ',is_virtual,is_presell,is_fcode,have_gift';

        //排序方式

        $order = $this->_goods_list_order($_REQUEST['key_sort'], $_REQUEST['order']);

        //优先从全文索引库里查找
        list($indexer_ids,$indexer_count) = $model_search->indexerSearch($_REQUEST,$this->page);
        if (is_array($indexer_ids)) {
            //商品主键搜索
            $goods_list = $model_goods->getGoodsOnlineList(array('goods_id'=>array('in',$indexer_ids)), $fieldstr, 0, $order, $this->page, null, false);

            //如果有商品下架等情况，则删除下架商品的搜索索引信息
            if (count($goods_list) != count($indexer_ids)) {
                $model_search->delInvalidGoods($goods_list, $indexer_ids);
            }
            pagecmd('setEachNum',$this->page);
            pagecmd('setTotalNum',$indexer_count);
        } else {
            //商品列表，改为按common_id分组.@todo 上面全文索引可能也需要修改
//            $goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, $order, $this->page);
            $count = $model_goods->getGoodsOnlineCount($condition,"distinct (goods_commonid)");
            $goods_list = $model_goods->getGoodsOnlineList($condition, $fieldstr, $this->page, $order, 0, 'goods_commonid', false,$count);
        }
        $page_count = $model_goods->gettotalpage();

        //处理商品列表(抢购、限时折扣、商品图片)
        $goods_list = $this->_goods_list_extend($goods_list);

        output_data(array('goods_list' => $goods_list), mobile_page($page_count));
    }

    /**
     * 商品列表排序方式
     */
    private function _goods_list_order($key, $order) {
        $result = 'is_own_shop desc,goods_id desc';
        if (!empty($key)) {

            $sequence = 'desc';
            if($order == 1) {
                $sequence = 'asc';
            }

            switch ($key) {
                //销量
                case '1' :
                    $result = 'goods_salenum' . ' ' . $sequence;
                    break;
                //浏览量
                case '2' :
                    $result = 'goods_click' . ' ' . $sequence;
                    break;
                //价格
                case '3' :
                    $result = 'goods_price' . ' ' . $sequence;
                    break;
            }
        }
        return $result;
    }

    /**
     * 商品列表排序方式
     */
    private function _goods_list_order_v2($key, $order) {
        $result = 'is_own_shop desc,sort desc';
        if (!empty($key)) {

            $sequence = 'desc';
            if($order == 1) {
                $sequence = 'asc';
            }

            switch ($key) {
                //销量
                case '1' :
                    $result = 'goods_salenum' . ' ' . $sequence;
                    break;
                //价格
                case '2' :
                    $result = 'goods_price' . ' ' . $sequence;
                    break;
                //新品
                case '3' :
                    $result = 'goods_addtime' . ' ' . $sequence;
                    break;
            }
        }
        return $result;
    }


    /**
     * 处理商品列表(抢购、限时折扣、商品图片)
     */
    private function _goods_list_extend($goods_list) {
        //获取商品列表编号数组
        $commonid_array = array();
        $goodsid_array = array();
        foreach($goods_list as $key => $value) {
            $commonid_array[] = $value['goods_commonid'];
            $goodsid_array[] = $value['goods_id'];
        }

        //促销
        $groupbuy_list = Model('groupbuy')->getGroupbuyListByGoodsCommonIDString(implode(',', $commonid_array));
        $xianshi_list = Model('p_xianshi_goods')->getXianshiGoodsListByGoodsString(implode(',', $goodsid_array));
        foreach ($goods_list as $key => $value) {
            //抢购
            if (isset($groupbuy_list[$value['goods_commonid']])) {
                $goods_list[$key]['goods_price'] = $groupbuy_list[$value['goods_commonid']]['groupbuy_price'];
                $goods_list[$key]['group_flag'] = true;
            } else {
                $goods_list[$key]['group_flag'] = false;
            }

            //限时折扣
            if (isset($xianshi_list[$value['goods_id']]) && !$goods_list[$key]['group_flag']) {
                $goods_list[$key]['goods_price'] = $xianshi_list[$value['goods_id']]['xianshi_price'];
                $goods_list[$key]['xianshi_flag'] = true;
            } else {
                $goods_list[$key]['xianshi_flag'] = false;
            }

            //商品图片url
            $goods_list[$key]['goods_image_url'] = cthumb($value['goods_image'], 360, $value['store_id']);
            $goods_list[$key]['goods_price'] = del0($value['goods_price']);
            $goods_list[$key]['goods_marketprice'] = del0($value['goods_marketprice']);

            unset($goods_list[$key]['store_id']);
            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
    }

    /**
     * 商品详细页
     */
    public function goods_detailOp() {
        $goods_id = intval($_REQUEST ['goods_id']);

        // 商品详细信息
        $model_goods = Model('goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id);
        if (empty($goods_detail) || $goods_detail['goods_info']['good_type']!=2) {
            output_error('商品不存在');
        }

        //推荐商品
        $model_store = Model('store');
        $hot_sales = $model_store->getHotSalesList($goods_detail['goods_info']['store_id'], 6);
        $goods_commend_list = array();
        foreach($hot_sales as $value) {
            $goods_commend = array();
            $goods_commend['goods_id'] = $value['goods_id'];
            $goods_commend['goods_name'] = $value['goods_name'];
            $goods_commend['goods_price'] = del0($value['goods_price']);
            $goods_commend['goods_image_url'] = cthumb($value['goods_image'], '');
            $goods_commend['goods_image_url'] = qnyResizeHD($goods_commend['goods_image_url'], 240);
            $goods_commend_list[] = $goods_commend;
        }
        $goods_detail['goods_commend_list'] = $goods_commend_list;
        $store_info = $model_store->getStoreInfoByID($goods_detail['goods_info']['store_id']);
        $goods_detail['store_info']['store_id'] = $store_info['store_id'];
        $goods_detail['store_info']['store_name'] = $store_info['store_name'];
        $goods_detail['store_info']['member_id'] = $store_info['member_id'];
        $goods_detail['store_info']['member_name'] = $store_info['member_name'];
        $goods_detail['store_info']['avatar'] = getMemberAvatarForID($store_info['member_id']);
        $goods_detail['store_info']['image']= getStoreLogo($store_info['store_banner'],'store_logo') ;//横幅
        //压缩图片
        $goods_detail['store_info']['avatar']=qnycthumbType($goods_detail['store_info']['avatar'],60);
        $goods_detail['store_info']['image']=qnycthumbType($goods_detail['store_info']['image'],480);

        //店铺&商品访问流量统计
        Model('statistics')->flowstat_record($goods_detail['goods_info']['store_id'],$goods_detail['goods_info']['goods_id']);
        //商品详细信息处理
        $goods_detail = $this->_goods_detail_extend($goods_detail);

        //分销信息
        if($goods_detail['goods_info']['is_distribution']){
            $goods_detail['goods_info_distribution']=Model('goods_distribution')->getGoodDistributionInfo(array('goods_id'=>$goods_id));
        }

        output_data($goods_detail);
    }

    /**
     * 商品详细信息处理
     */
    private function _goods_detail_extend($goods_detail) {
        //整理商品规格
        unset($goods_detail['spec_list']);
        $goods_detail['spec_list'] = $goods_detail['spec_list_mobile'];
        unset($goods_detail['spec_list_mobile']);

        //整理商品图片
        unset($goods_detail['goods_image']);
        $goods_detail['goods_image'] = implode(',', $goods_detail['goods_image_mobile']);
        unset($goods_detail['goods_image_mobile']);

        //商品链接
        $goods_detail['goods_info']['goods_url'] = urlShop('goods', 'index', array('goods_id' => $goods_detail['goods_info']['goods_id']));

        //整理数据
        unset($goods_detail['goods_info']['goods_commonid']);
        unset($goods_detail['goods_info']['gc_id']);
        unset($goods_detail['goods_info']['gc_name']);
        unset($goods_detail['goods_info']['store_id']);
        unset($goods_detail['goods_info']['store_name']);
        unset($goods_detail['goods_info']['brand_id']);
        unset($goods_detail['goods_info']['brand_name']);
        unset($goods_detail['goods_info']['type_id']);
        unset($goods_detail['goods_info']['goods_image']);
        unset($goods_detail['goods_info']['goods_body']);
//        unset($goods_detail['goods_info']['goods_state']);
        unset($goods_detail['goods_info']['goods_stateremark']);
        unset($goods_detail['goods_info']['goods_verify']);
        unset($goods_detail['goods_info']['goods_verifyremark']);
        unset($goods_detail['goods_info']['goods_lock']);
        unset($goods_detail['goods_info']['goods_addtime']);
        unset($goods_detail['goods_info']['goods_edittime']);
        unset($goods_detail['goods_info']['goods_selltime']);
        unset($goods_detail['goods_info']['goods_show']);
        unset($goods_detail['goods_info']['goods_commend']);
        unset($goods_detail['goods_info']['explain']);
        unset($goods_detail['goods_info']['cart']);
        unset($goods_detail['goods_info']['buynow_text']);
        unset($goods_detail['groupbuy_info']);
        unset($goods_detail['xianshi_info']);

        return $goods_detail;
    }

    /**
     * 商品详细页
     */
    public function goods_bodyOp() {
        $goods_id = intval($_REQUEST ['goods_id']);

        $model_goods = Model('goods');

        $goods_info = $model_goods->getGoodsInfoByID($goods_id, 'goods_commonid');
        $goods_common_info = $model_goods->getGoodeCommonInfoByID($goods_info['goods_commonid']);

        Tpl::output('goods_common_info', $goods_common_info);
        Tpl::showpage('goods_body');
    }

    /**
     * 商城，随机三条商品
     */
    public function goods_randomOp(){
        $limit=isset($_REQUEST['limit'])?intval($_REQUEST['limit']):3;
        $good_type=isset($_REQUEST['good_type'])?intval($_REQUEST['good_type']):2;

        $field=array(
            'goods_name',
            'goods_image',
            'goods_price',
            'goods_marketprice',
            'goods_id',
            'goods_salenum',
            'goods_commonid',
        );
        $condition['good_type']=$good_type;
        if(isset($_REQUEST['eliminate_goods'])){
            $temp=explode(',',$_REQUEST['eliminate_goods_common']);
            $condition['goods_commonid']=array('not in',$temp);
        }
        $goods=Model('goods')->getGoodsRandom($condition,$field,$limit);
        if(!empty($goods)) {
            foreach ($goods as $key_2 => $value) {
                $goods[$key_2]['goods_image'] = cthumb($goods[$key_2]['goods_image'], '', $goods[$key_2]['store_id']).'-recommend?v=1';
                $goods[$key_2]['goods_price']=del0($goods[$key_2]['goods_price']);
                $goods[$key_2]['goods_marketprice']=del0($goods[$key_2]['goods_marketprice']);
            }
        }
        output_data($goods);
    }


    //爱大腿商品详情页
    public function adt_good_detailOp(){
        $check_param=array('good_id','store_id');
        check_request_parameter($check_param);
        $good_id=intval($_REQUEST['good_id']);
        $store_id=intval($_REQUEST['store_id']);
        $goods_info=Model('goods')->adtGetGoodsDetail($good_id,$store_id);
        if(empty($goods_info)){
            output_error('商品不存在');
        }
        output_data($goods_info);
    }

    //爱大腿产品库商品详情
    public function adt_good_detail_sourceOp(){
        $check_param=array('good_id');
        $good_id=intval($_REQUEST['good_id']);
        $data=Model('goods')->adtGoodsDetailSource($good_id);
        output_data($data);
    }

    /**
     *
     */
    public function getEvaluateListOp()
    {

        $check_param = array('goodsID');
        check_request_parameter($check_param);
        $type = isset($_REQUEST['type']) ? intval($_REQUEST['type']) : 0;
        $goods_id = intval($_REQUEST['goodsID']);

        $goods_model = Model('goods');

        $goods_info = $goods_model->getGoodsInfoByID($goods_id);
        if (empty($goods_info)) {
            output_error('商品不存在');
            exit;
        }
        $goods_list = $goods_model->getGoodsList(array('goods_commonid' => $goods_info['goods_commonid']), 'goods_id');
        $goods_ids = array();
        foreach ($goods_list as $value) {
            $goods_ids[] = $value['goods_id'];
        }
        $condition = array();
        $condition['geval_goodsid'] = array('in', implode(',', $goods_ids));
        $condition['geval_state'] = 0;
        switch ($type) {
            case '1':
            case 'good':
                $condition['geval_scores'] = array('in', '5,4');
                break;
            case '2':
            case 'normal':
                $condition['geval_scores'] = array('in', '3,2');
                break;
            case '3':
            case 'bad':
                $condition['geval_scores'] = array('in', '1');
                break;
            case '4':
            case 'image':
                $condition['geval_image'] = array('NEQ', '');
                break;
        }
        $field = 'geval_scores,geval_content,geval_isanonymous,geval_addtime,geval_frommemberid,geval_frommembername,geval_image';
        $limit = getLimit();
        $evaluate = Model('evaluate_goods')->where($condition)->order('geval_addtime desc')->field($field)->limit($limit)->select();
        if (!empty($evaluate)) {
            $member_ids = array();
            foreach ($evaluate as $key => $value) {
                $evaluate[$key]['geval_addtime'] = date('Y-m-d H:i:s', $evaluate[$key]['geval_addtime']);
                $member_ids[] = $value['geval_frommemberid'];
                //评价图片
                $geval_images = array();
                if (!empty($value['geval_image'])) {
                    foreach (explode(',', $value['geval_image']) as $value) {
                        $geval_images[] = snsThumb($value);
                    }
                }
                $evaluate[$key]['images'] = $geval_images;
            }
            //处理会员头像
            $member_info = Model('member')->getMemberList(array('member_id' => array('in', implode(',', $member_ids))));
            $member_avata = array();
            if (!empty($member_info)) {
                foreach ($member_info as $key => $value) {
                    $member_avata[$value['member_id']] = $value['member_avatar'];
                }
            }
            foreach ($evaluate as $key => $value) {
                if (isset($member_avata[$value['geval_frommemberid']])) {
                    $evaluate[$key]['frommemberavara'] = getMemberAvatar($member_avata[$value['geval_frommemberid']]);
                } else {
                    $evaluate[$key]['frommemberavara'] = getMemberAvatar('');
                }
                $evaluate[$key]['frommemberavara'] = qnyResizeHD($evaluate[$key]['frommemberavara'], 240);

                foreach ($value as $index => $item) {
                    if(in_array($index,array('geval_image','geval_frommemberid'))){
                        unset($evaluate[$key][$index]);
                    }
                    elseif(substr($index,0,6)=='geval_'){
                        $evaluate[$key][substr($index,6)] = $item;
                        unset($evaluate[$key][$index]);
                    }
                }
            }
        }
        $average = Model('evaluate_goods')->getEvaluateGoodsInfoByGoodsCommonID($goods_info['goods_commonid']);
        $simple_goods_info = array(
            'evaluation_good_star' => $average['star_average'],
            'evaluation_count' => $average['all'],
            'good_count' => $average['good'],
            'normal_count' => $average['normal'],
            'bad_count' => $average['bad'],
            'image_count' => (int)$average['image'],
        );
        $data = array(
            'info' => $simple_goods_info,
            'evaluate_list' => $evaluate

        );
        output_data($data);
    }

    /**
     * 跑腿邦v1.1 商品列表
     */
    public function adt_goods_listOp(){
        $check_param = array('store_id');
        check_request_parameter($check_param);
        $store_id=intval($_REQUEST['store_id']);
        $class_id=isset($_REQUEST['class_id'])?intval($_REQUEST['class_id']):0;
        $goods_name=isset($_REQUEST['goods_name'])?trim($_REQUEST['goods_name']):'';
        QueueClient::push('adt_hot_search_counting',$goods_name);

        $this->page=20;
        if(C('open_search.open') && !empty($goods_name)){
            $open_search_result=Model('open_search')->adt_goods_list($store_id,$goods_name,$class_id);
            $goods_list=$open_search_result['result']['items'];
            $return['page_total']=$open_search_result['result']['pagetotal'];
        }else{
            $goods_list = Model('goods')->page($this->page)->adtGetGoodsListComplete($store_id, $class_id, $goods_name);
            $return['page_total'] = pagecmd('getTotalPage');
        }

        if(empty($goods_list)){
            //随机查商品
            $goods_list=Model('goods')->adtGetGoodsListComplete($store_id,$class_id,$goods_name,true);
            $return['page_total']=0;
            $return['has_result']=0;
        }else{
            $return['has_result']=1;
        }
        $return['goods_list']=$goods_list;
        output_data($return);
    }

}
