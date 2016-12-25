<?php
defined('emall') or exit('Access Invalid!');
/**
 * 加盟店店铺商品相关信息控制器
 * @author Administrator
 *
 */
class adt_store_goodsControl extends BaseStoreLeagueControl{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 商品首页统计数据
     */
    public function adt_homeOp(){
        $goods_condition['league_store_id']=$this->store_info['store_id'];
        $goods_mine=Model('goods_league')->where($goods_condition)->count();
        $goods_condition['league_goods_verify']=1;
        $goods_sell=Model('goods_league')->where($goods_condition)->count();
        $return['goods_sell']=$goods_sell;
        $goods_condition['league_goods_promotion_type']=1;
        $goods_promotion=Model('goods_league')->where($goods_condition)->count();
        //我的商品，审核中数量
        $goods_condition_verify['league_store_id']=$this->store_info['store_id'];
        $goods_condition_verify['league_price_verify']=10;
        $goods_verify=Model('goods_league')->where($goods_condition_verify)->count();
        $return['goods_verify']=$goods_verify;
        //我的商品，未通过审核数量
        $goods_condition_verify_failed['league_store_id']=$this->store_info['store_id'];
        $goods_condition_verify_failed['league_price_verify']=0;
        $goods_verify_failed=Model('goods_league')->where($goods_condition_verify_failed)->count();
        $return['goods_verify_failed']=$goods_verify_failed;
        //无库存商品数量
        $goods_condition_none['league_store_id']=$this->store_info['store_id'];
        $goods_condition_none['league_goods_storage']=0;
        $goods_none=Model('goods_league')->where($goods_condition_none)->count();
        $return['goods_none']=$goods_none;
        //低库存商品数量
        $goods_condition_poor['league_store_id']=$this->store_info['store_id'];
        $goods_condition_poor['league_goods_storage']=array('elt',array('exp','league_goods_storage_alarm'));
        $goods_poor=Model('goods_league')->where($goods_condition_poor)->count();
        $return['goods_poor']=$goods_poor;
//        $source_condition['goods_state']=1;
//        $source_condition['goods_verify']=1;
//        $source_condition['good_type']=3;
//        $goods_source=Model('goods')->where($source_condition)->count();
        $return['goods_mine']=$goods_mine;
        $return['goods_promotion']=$goods_promotion;
//        $return['goods_source']=$goods_source;
        output_data($return);
    }

    /**
     * 商品库，分类列表
     */
    public function adt_classOp(){
        $class=Model('goods_class')->getGoodsClassListAll(3);
        $return=array();
        foreach($class as $key=>$value){
            $temp=array();
            $source_condition['goods_state']=1;
            $source_condition['goods_verify']=1;
            $source_condition['good_type']=3;
            $source_condition['gc_id_1']=$value['gc_id'];
            $goods_count=Model('goods')->where($source_condition)->count();
            $temp['gc_id']=$value['gc_id'];
            $temp['gc_name']=$value['gc_name'];
            $temp['goods_count']=$goods_count;
            $return[]=$temp;
        }
        output_data($return);
    }

    /**
     * 产品库商品列表
     * @param gc_id 分类id
     * @param goods_name 商品名字，搜索功能
     */
    public function adt_goods_list_sourceOp(){
        $class_id=intval($_REQUEST['gc_id']);
        $goods_name=isset($_REQUEST['goods_name'])?trim($_REQUEST['goods_name']):'';
        $is_new=(isset($_REQUEST['is_new']) && 1==$_REQUEST['is_new'])?1:0;
        $is_hot=(isset($_REQUEST['is_hot']) && 1==$_REQUEST['is_hot'])?1:0;

        $source_condition['goods_state']=1;
        $source_condition['goods_verify']=1;
        $source_condition['good_type']=3;
        $class_id && $source_condition['gc_id_1']=$class_id;
        $goods_name && $source_condition['goods_name']=array('like','%'.$goods_name.'%');

        $order='goods_id desc';
        $is_new && $order='goods_addtime desc';
        $is_hot && $order='goods_salenum desc';

        $fields[]='goods_id';
        $fields[]='goods_name';
        $fields[]='store_id';
        $fields[]='goods_image';
        $fields[]='goods_price';
        $fields[]='goods_size';
        $goods_list=Model('goods')->table('goods')->where($source_condition)->field($fields)->page($this->page)->order($order)->select();

        //已上架的商品
        $goods_condition['league_store_id']=$this->store_info['store_id'];
        $goods_mine=Model('goods_league')->where($goods_condition)->field('goods_id')->select();
//        $goods_mine_ids=array_column($goods_mine,'goods_id');     //PHP5.5.0以上才支持此函数
        $goods_mine_ids=array();
        foreach($goods_mine as $value){
            $goods_mine_ids[]=$value['goods_id'];
        }

        foreach($goods_list as & $value){
            $value['goods_image']=cthumb($value['goods_image'],'',$value['store_id']).'-like1';
            $value['goods_price']=del0($value['goods_price']);
            $value['is_geted']=in_array($value['goods_id'],$goods_mine_ids)?1:0;
            unset($value['store_id']);
        }
        output_data($goods_list);
    }

    /**
     * 我的商品列表
     * @param type 0:全部，1：出售中，2：审核中，3：未通过审核，4：无库存，5：促销商品,6:低库存
     */
    public function adt_goods_list_mineOp(){
        $goods_condition['league_store_id']=$this->store_info['store_id'];

        $type=isset($_REQUEST['type'])?intval($_REQUEST['type']):0;
        switch($type){
            case 0:
                break;
            case 1:
                $goods_condition['league_goods_verify']=1;
                break;
            case 2:
                $goods_condition['league_price_verify']=10;
                break;
            case 3:
                $goods_condition['league_price_verify']=0;
                break;
            case 4:
                $goods_condition['league_goods_storage']=0;
                break;
            case 5:
                $goods_condition['league_goods_promotion_type']=1;
                break;
            case 6:
                $goods_condition['league_goods_storage']=array('elt',array('exp','league_goods_storage_alarm'));
                break;
            default:
                output_error('参数错误');
                break;
        }

        $goods_name=isset($_REQUEST['goods_name'])?trim($_REQUEST['goods_name']):'';
        $goods_name && $goods_condition['goods_name']=array('like','%'.$goods_name.'%');

        $fields[]='goods_id';
        $fields[]='goods_name';
        $fields[]='store_id';
        $fields[]='goods_image';
        $fields[]='goods_size';
        $fields[]='league_goods_price';
        $fields[]='league_goods_promotion_price';
        $fields[]='league_goods_promotion_type';
        $fields[]='league_goods_storage';
        $fields[]='league_goods_promotion_storage';
        $fields[]='league_goods_change_price';
        $fields[]='league_goods_verify';
        $fields[]='league_price_verify';
        $fields[]='league_goods_storage_alarm';
        $goods=Model('goods_league')->where($goods_condition)->field($fields)->page($this->page)->select();
        foreach($goods as & $value){
            $value['goods_image']=cthumb($value['goods_image'],'',$value['store_id']).'-like1';
//            $value['sale_goods_price']=(1==$value['league_goods_promotion_type'])?del0($value['league_goods_promotion_price']):del0($value['league_goods_price']);
            $value['league_goods_price']=del0($value['league_goods_price']);
            $value['league_goods_change_price']=del0($value['league_goods_change_price']);
            $value['league_goods_promotion_price']=del0($value['league_goods_promotion_price']);
            unset($value['store_id']);
        }
        $count=Model('goods_league')->gettotalnum();
        $return['goods_list']=$goods;
        $return['total_num']=$count;
        output_data($return);
    }

    /**
     * 添加商品
     */
    public function adt_add_goodsOp(){
        $check_param=array('goods_id','goods_price','goods_storage','commis_rate');
        check_request_parameter($check_param);

        $commis_rate=floatval($_REQUEST['commis_rate']);
        $league_goods_storage_alarm=floatval($_REQUEST['storage_alarm']);
        if($commis_rate<0 || $commis_rate>100){
            output_error('返利比率必须是0到100之间');
        }

        $goods_id=intval($_REQUEST['goods_id']);
        $param['league_store_id']=$this->store_info['store_id'];
        $param['goods_id']=$goods_id;
        $source_condition['goods_state']=1;
        $source_condition['goods_verify']=1;
        $source_condition['good_type']=3;
        $source_condition['goods_id']=$goods_id;
        $goods_info=Model('goods')->where($source_condition)->find();
        if(empty($goods_info)){
            output_error('此商品不存在货已下架');
        }

        $check=Model('goods_league')->where($param)->find();
        if(!empty($check)){
            output_error('此商品已上架');
        }

        $param['goods_commonid']=$goods_info['goods_commonid'];
        $param['league_goods_storage_alarm']=$league_goods_storage_alarm;
        $param['goods_name']=$goods_info['goods_name'];
        $param['store_id']=$goods_info['store_id'];
        $param['goods_size']=$goods_info['goods_size'];
        $param['gc_id']=$goods_info['gc_id'];
        $param['gc_id_1']=$goods_info['gc_id_1'];
        $param['gc_id_2']=$goods_info['gc_id_2'];
        $param['gc_id_3']=$goods_info['gc_id_3'];
        $param['goods_image']=$goods_info['goods_image'];
        $param['league_goods_price']=floatval($_REQUEST['goods_price']);
        $param['league_goods_change_price']=floatval($_REQUEST['goods_price']);
        $param['league_store_name']=$this->store_info['store_name'];
        $param['league_goods_storage']=intval($_REQUEST['goods_storage']);
        $param['league_goods_verify']=10;
        $param['league_price_verify']=10;
        $param['league_goods_addtime']=time();
        $param['opt_member_id']=$this->seller_member_info['member_id'];
        $param['opt_member_name']=$this->seller_member_info['member_name'];
        $param['commis_rate']=$commis_rate;
        $res=Model('goods_league')->insert($param);
        if($res){
            output_data(1);
        }else{
            output_error('上架失败');
        }
    }

   /**
    * 删除商品
    */
    public function adt_delete_goodsOp(){
        $check_param=array('goods_id');
        check_request_parameter($check_param);

        $goods_id=intval($_REQUEST['goods_id']);
        $param['league_store_id']=$this->store_info['store_id'];
        $param['goods_id']=$goods_id;
        $res=Model('goods_league')->where($param)->delete();
        if($res){
            output_data(1);
        }else{
            output_error('删除失败,商品不存在');
        }
    }

    /**
     * 取消改价申请/删除商品
     */
    public function adt_delete_speciallyOp(){
        $check_param=array('goods_id');
        check_request_parameter($check_param);

        $goods_id=intval($_REQUEST['goods_id']);
        $param['league_store_id']=$this->store_info['store_id'];
        $param['goods_id']=$goods_id;
        $goods_info=Model('goods_league')->where($param)->find();
        if(empty($goods_info)){
            output_error('商品不存在');
        }
        if(1==$goods_info['league_goods_verify']) {
            $update['league_price_verify']=1;
            $update['league_goods_change_price']=0;
            $res = Model('goods_league')->where($param)->update($update);
        }else{
            $res = Model('goods_league')->where($param)->delete();
        }
        if($res){
            output_data(1);
        }else{
            output_error('删除失败,商品不存在');
        }
    }

    /**
     * 改价
     */
    public function adt_change_priceOp(){
        $check_param=array('goods_id','price');
        check_request_parameter($check_param);

        $goods_id=intval($_REQUEST['goods_id']);
        $goods_new_price=floatval($_REQUEST['price']);
        $param['league_store_id']=$this->store_info['store_id'];
        $param['goods_id']=$goods_id;
        $goods_info=Model('goods_league')->where($param)->find();
        if(empty($goods_info)){
            output_error('商品不存在');
        }
        if(1==$goods_info['league_goods_verify']){//销售中
            $update['league_goods_change_price']=$goods_new_price;
        }else{//审核中
            $update['league_goods_price']=$goods_new_price;
            $update['league_goods_change_price']=$goods_new_price;
        }

        $update['league_price_verify']=10;
        $res=Model('goods_league')->where($param)->update($update);
        if($res){
            output_data(1);
        }else{
            output_error('改价申请失败,商品不存在');
        }
    }

    /**
     * 促销/修改促销价格、库存
     */
    public function adt_promotionOp(){
        $check_param=array('goods_id','price','num');
        check_request_parameter($check_param);

        $goods_id=intval($_REQUEST['goods_id']);
        $goods_new_price=floatval($_REQUEST['price']);
        $num=floatval($_REQUEST['num']);
        $param['league_store_id']=$this->store_info['store_id'];
        $param['goods_id']=$goods_id;
        $goods=Model('goods_league')->where($param)->find();
        if(empty($goods)){
            output_error('商品不存在');
        }
        if($goods_new_price>$goods['league_goods_price'] || $num>$goods['league_goods_storage']){
            output_error('价格或数量不合法');
        }

        $update['league_goods_promotion_price']=$goods_new_price;
        $update['league_goods_promotion_storage']=$num;
        $update['league_goods_promotion_type']=1;
        $res=Model('goods_league')->where($param)->update($update);
        if($res){
            output_data(1);
        }else{
            output_error('修改促销信息失败,商品不存在');
        }
    }

    /**
     * 关闭促销
     */
    public function adt_close_promotionOp(){
        $check_param=array('goods_id');
        check_request_parameter($check_param);

        $goods_id=intval($_REQUEST['goods_id']);
        $param['league_store_id']=$this->store_info['store_id'];
        $param['goods_id']=$goods_id;
        $update['league_goods_promotion_type']=0;
        $res=Model('goods_league')->where($param)->update($update);
        if($res){
            output_data(1);
        }else{
            output_error('关闭促销失败,商品不存在');
        }
    }

    /**
     * 修改库存
     */
    public function adt_edit_storageOp(){
        $check_param=array('goods_id','num','storage_alarm');
        check_request_parameter($check_param);

        $goods_id=intval($_REQUEST['goods_id']);
        $league_goods_storage_alarm=floatval($_REQUEST['storage_alarm']);
        $num=floatval($_REQUEST['num']);
        $param['league_store_id']=$this->store_info['store_id'];
        $param['goods_id']=$goods_id;

        $update['league_goods_storage_alarm']=$league_goods_storage_alarm;
        $update['league_goods_storage']=$num;
        $res=Model('goods_league')->where($param)->update($update);
        if($res){
            output_data(1);
        }else{
            output_error('修改库存失败,商品不存在');
        }
    }


}