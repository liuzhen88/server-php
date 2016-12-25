<?php
/**
 * 首页数据
 */

defined('emall') or exit('Access Invalid!');
class homeControl extends mobileHomeControl{

	public function __construct() {
        parent::__construct();
    }

    /**
     * 跑腿邦首页，首页广告，v1.1新首页
     */
    public function adt_home_1_1Op(){
        $class=Model('goods_class')->adtGetClassInHome();
        $special=Model('mb_special')->getMbSpecialItemUsableListByID(25);
        $data['adv']=$special[0]['adv_list']['item'];
        $data['class']=$class;
        $data['activity']=$special[1]['home3']['item'];
        $data['special']=$special[2]['home3']['item'];
        output_data($data);
    }


    /*
     * 首页数据(超市)，分类和第一个分类下的商品，以及配送
     */
    public function adt_homeOp(){
        $check_param=array('store_id');
        check_request_parameter($check_param);
        $store_id=intval($_REQUEST['store_id']);
        $condition['league_store_id']=$store_id;
        $goods_list_store=$this->adt_get_goods_list_by_class($store_id);
        $class_names=Model('goods_class')->getGoodsClassListByParentId(0,'all');
        $class_names=array_under_reset($class_names,'gc_id');
        $this_class_name=array();
        $this_class_name_added=array();
        $goods_list_return=array();

        //购物车数据
        $cart_list=array();
        $key=isset($_REQUEST['key'])?$_REQUEST['key']:'';
        if(!empty($key)){
            $mb_user_token_info =  Model('mb_user_token')->getMbUserTokenInfoByToken($key);
            if(!empty($mb_user_token_info)){
                $member_id=$mb_user_token_info['member_id'];
                $condition = array('buyer_id' => $member_id);
                $cart_list	= Model('cart_league')->where($condition)->select();
                $cart_list=array_under_reset($cart_list,'goods_id');
            }
        }

        $goods_model=Model('goods');
        $goods_ids=agg_array_column($goods_list_store,'goods_id');
        $goods_detail_array=$goods_model->adtGetAllGoodsDetail($goods_ids,$store_id);
        //获取分类和分类名字，该店铺所有商品的分类
        foreach($goods_list_store as $value){
            //查商品详情
            $this_goods_info=isset($goods_detail_array[$value['goods_id']])?$goods_detail_array[$value['goods_id']]:array();
            $value['goods_detail']=$this_goods_info;
            //计算购物车中商品数量
            $value['cart_goods_num']=isset($cart_list[$value['goods_id']])?$cart_list[$value['goods_id']]['goods_num']:0;
            $goods_list_return[$value['gc_id_1']][]=$value;
            if(in_array($value['gc_id_1'],$this_class_name_added)){
                continue;
            }
            $this_class_name_added[]=$value['gc_id_1'];
            $temp['gc_id']=$value['gc_id_1'];
            $temp['gc_name']=isset($class_names[$value['gc_id_1']])?$class_names[$value['gc_id_1']]['gc_name']:'';
            $temp['gc_sort']=isset($class_names[$value['gc_id_1']])?$class_names[$value['gc_id_1']]['gc_sort']:0;
            $this_class_name[]=$temp;
        }
        $this->ksort($this_class_name,'gc_sort');
        $return['class_info']=$this_class_name;
        $return['goods_list']=(object) $goods_list_return;  //ios强类型，返回的数据不论是空还是数组，都要以对象的形式返回

        $return['adt_carriage']=ADT_CARRIAGE;
        $return['adt_free_carriage_leave']=ADT_FREE_CARRIAGE_LEAVE;

        output_data($return);
    }

    //快速排序，二维数组
    public function ksort(&$array,$key,$i=0,$j=0,$asc=true){
        if(0==$j){
            $j=count($array);
        }
        if($i>=$j){
            return ;
        }
        $min=$i;
        for($index=$i;$index<$j;$index++){
            if($array[$min][$key]>$array[$index][$key] && $asc){
                $min=$index;
            }
        }
        $temp=$array[$i];
        $array[$i]=$array[$min];
        $array[$min]=$temp;
        $this->ksort($array,$key,$i+1,$j);
    }

    /**
     * 根据分类id获取商品
     */
    public function adt_get_goods_list_by_classOp(){
        $check_param=array('class_id','store_id');
        check_request_parameter($check_param);
        $goods_list=$this->adt_get_goods_list_by_class($_REQUEST['store_id'],$_REQUEST['class_id']);
        output_data($goods_list);
    }

    /**
     * 根据分类id获取商品
     */
    public function adt_get_goods_list_by_class($store_id,$class_id=0){
        $condition['league_goods_verify']=1;
        $class_id && $condition['gc_id_1']=intval($class_id);
        $condition['league_store_id']=intval($store_id);
        $field[]='goods_name';
        $field[]='goods_id';
        $field[]='goods_size';
        $field[]='goods_image';
        $field[]='store_id';
        $field[]='league_goods_price';
        $field[]='league_goods_storage';
        $field[]='gc_id_1';
        $goods_list=Model('goods')->adtGetGoodsList($condition,$field);
        foreach($goods_list as & $value){
            $value['goods_image']=cthumb($value['goods_image'],'',$value['store_id']).'-like1';
            $value['league_goods_price']=del0($value['league_goods_price']);
            unset($value['store_id']);
        }
        return $goods_list;
    }
    /**
     * 根据经纬度获取店铺信息
     */
    public function adt_get_store_by_licationOp(){
        $check_param=array('lat','lng');
        check_request_parameter($check_param);
        $store_info=Model('store')->adt_get_store_by_lication($_REQUEST['lat'],$_REQUEST['lng']);
        if (empty($store_info)){
            output_error('当前地址暂无服务');
        }
        else {
            output_data($store_info);
        }
    }
    /**
     * 本土商城首页接口
     */
    public function localOp(){

    }

    /**
     * 线上商城首页接口
     * @param eliminate_special 排除的专题id，逗号分隔的字符串
     * @param eliminate_goods 排除的商品goods_commonid，逗号分隔的字符串
     * @param limit_goods 随机商品数量
     * @param limit_special 随机专题数量
     * @param client_type 客户端类型
     */
    public function onlineOp(){
        
        //原始代码
        $head=Model('mb_special')->getMbSpecialItemUsableListByID(4);

        $return['adv']=$head[0]['home3']['item'];   //顶部广告
        $return['hot']=$head[1]['hot']['item'];     //顶部广告下方热点 轮播文字  title 对应文字描述，data对应点击的链接
        $return['adv_2']=$head[2]['home3']['item']; //第二块广告，顶部下方
        $return['adv_3']=$head[3]['home3']['item']; //第三块广告，农特电商
        $return['goods_rush']=$head[4]['goods']['item'];    //抢购商品
        $return['hot_distribution']=$this->get_hot_distribution();  //热门分销
        $return['special_fixed']=$this->get_special_recommend(1);   //热门专题
        output_data($return);
    }

    /**
     * 线上商城首页,获取随机商品和专题接口
     * @param eliminate_special 排除的专题id，逗号分隔的字符串
     * @param eliminate_goods 排除的商品goods_commonid，逗号分隔的字符串
     * @param limit_goods 随机商品数量
     * @param limit_special 随机专题数量
     * @param client_type 客户端类型
     */
    public function online_goods_special_randomOp(){
        $return['goods_random']=$this->get_goods_random(2);
        $return['special_random']=$this->get_special_random(1);
        output_data($return);
    }

    /**
     * 获取随机专题
     * @param special_type 专题类型 1：商城 ，2:本土
     * @param eliminate_special 排除的专题id，逗号分隔的字符串
     * @param limit 返回数据的数量
     */
    public function get_special_random($special_type){
        $model_mb_special = Model('mb_special');
        $condition=array('special_type'=>$special_type,'is_recommend'=>0);
        if(isset($_REQUEST['eliminate_special'])){
            $temp=explode(',',$_REQUEST['eliminate_special']);
            $condition['special_id']=array('not in',$temp);
        }
        $limit=isset($_REQUEST['limit_special'])?intval($_REQUEST['limit_special']):2;
        if(0==$limit){
            return array();
        }
        $res=$model_mb_special->getRandom($condition,'*',$limit);
        foreach($res as $key=>$value){
            $res[$key]['special_image']=getMbSpecialImageUrl($value['special_image']).'-shopRandomTheme';
        }
        return $res;
    }

    /**
     * 获取推荐专题
     * @param special_type 专题类型 1：商城 ，2:本土
     */
    public function get_special_recommend($special_type){
        $model_mb_special = Model('mb_special');
        $condition=array('special_type'=>$special_type,'is_recommend'=>1);
        $list=$model_mb_special->getMbSpecialList($condition,6);
        foreach($list as $key=>$value){
            $list[$key]['special_image']=getMbSpecialImageUrl($value['special_image']);
        }
        return $list;
    }

    /**
     * 商城，首页的随机商品列表
     * @param good_type 1本土，2线上
     */
    public function get_goods_random($good_type){
        $limit=isset($_REQUEST['limit_goods'])?intval($_REQUEST['limit_goods']):3;

        $field=array(
            'goods_name',
            'goods_image',
            'goods_price',
            'goods_marketprice',
            'goods_id',
            'goods_commonid',
            'goods_salenum',
            'goods_commonid',
        );
        $condition['good_type']=$good_type;
        if(isset($_REQUEST['eliminate_goods'])){
            $temp=explode(',',$_REQUEST['eliminate_goods']);
            $condition['goods_commonid']=array('not in',$temp);
        }
        $goods=Model('goods')->getGoodsRandom($condition,$field,$limit);
        if(!empty($goods)) {
            foreach ($goods as $key_2 => $value) {
                $goods[$key_2]['goods_image'] = cthumb($goods[$key_2]['goods_image'], '', $goods[$key_2]['store_id']).'-recommend';
                $goods[$key_2]['goods_price']=del0($goods[$key_2]['goods_price']);
                $goods[$key_2]['goods_marketprice']=del0($goods[$key_2]['goods_marketprice']);
            }
        }
        return $goods;
    }

    /**
     * functionname   : 获取热门分销商品
     * author         : xuping
     */
    private function get_hot_distribution(){
        $model_distribution = Model('distribution');
        $condition=array('is_recommend'=>1);
        $list=$model_distribution->get_disgoods_list($condition);

        return $list;
    }

    /**
     * 跑腿邦v1.1 热搜词
     */
    public function adt_hot_keyOp(){
        $res=Model('hot_search_league')->order('count desc')->limit(8)->select();
        output_data($res);
    }
}
