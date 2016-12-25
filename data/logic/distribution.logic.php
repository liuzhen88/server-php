<?php
/**
 * Created by PhpStorm.
 * User: lixiyu
 * Date: 2015/9/15
 * Time: 17:15
 *
 * 分销的几种情况:
 * 1.商户分销的（只有从分销中心分销）
 * 2.会员分销的（从商品详情、晒、分销中心。a.分销的源头有商户，b.分销的源头没商户。）
 *
 */
defined('emall') or exit('Access Invalid!');
class distributionLogic {
    /**
     * 获取购物车中分销相关的数据
     */
    public function get_cart_data($goods_id,$store_id=0,$member_id=0){
        $return=array(
            'dis_store_member_id'=>0,
            'dis_store_id'=>0,
            'dis_member_id'=>0,
            'is_distribution'=>0,
        );
        if($store_id==0 && $member_id==0){
            return $return;
        }
        $goods_info=Model('goods')->getGoodsInfoByID($goods_id);
        if(1!=$goods_info['is_distribution']){
            return $return;
        }
        $dis_goods=Model('distribution')->get_goods_info(array('goods_id'=>$goods_id));
        if(empty($dis_goods)){
            return $return;
        }
        if($member_id!=0){
            $member_info=Model('member')->where(array('member_id'=>$member_id,'is_distribution'=>array('neq',0)))->find();
            if(!empty($member_info)){
                $return['dis_member_id']=$member_id;
            }
        }
        if($store_id!=0){
            $store_info=Model('store')->where(array('store_id'=>$store_id,'store_state'=>1,'store_type'=>1))->find();
            if(!empty($store_info)){
                $distribution_condition=array('store_id'=>$store_id,'goods_commonid'=>$goods_info['goods_commonid'],'goods_state'=>1);
                $distribution_info=Model('distribution_goods')->where($distribution_condition)->find();
                if(!empty($distribution_info)){
                    $return['dis_store_id']=$store_id;
                    $return['dis_store_member_id']=$store_info['member_id'];
                }
            }
        }
/*        if($return['dis_member_id']>0&&$return['dis_store_id']>0){
            $return['is_distribution']=3;                       //会员从本土商户分销
        }
        if($return['dis_member_id']>0&&$return['dis_store_id']==0){
            $return['is_distribution']=2;                       //会员直接分销
        }
        if($return['dis_member_id']==0&&$return['dis_store_id']>0){
            $return['is_distribution']=1;                       //本土商户分销
        }
        if($return['dis_member_id']==0&&$return['dis_store_id']==0){
            $return['is_distribution']=0;                       //无分销
        }*/
        //上面等价于这个
        $return['is_distribution']=($return['dis_member_id']>0)*2+($return['dis_store_id']>0);
        return $return;
    }
}