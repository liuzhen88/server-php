<?php
/**
 * Created by PhpStorm.
 * User: xiyu
 * Date: 2015/11/2
 * Time: 7:45
 * 专题接口
 */


use Tpl;
defined('emall') or exit('Access Invalid!');
class specialControl extends mobileHomeControl
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取推荐专题
     * @param special_type 专题类型 1：商城 ，2:本土
     */
    public function get_recommendOp(){
        $model_mb_special = Model('mb_special');
        $special_type=intval($_REQUEST['special_type']);
        $condition=array('special_type'=>$special_type,'is_recommend'=>1);
        $list=$model_mb_special->getMbSpecialList($condition,6);
        foreach($list as $key=>$value){
            $list[$key]['special_image']=getMbSpecialImageUrl($value['special_image']);
        }
        output_data($list);
    }

    /**
     * 获取随机专题
     * @param special_type 专题类型 1：商城 ，2:本土
     * @param eliminate_special 排除的专题id，逗号分隔的字符串
     * @param limit 返回数据的数量
     */
    public function get_randomOp(){
        $check_param=array('special_type');
        check_request_parameter($check_param);
        $model_mb_special = Model('mb_special');
        $special_type=intval($_REQUEST['special_type']);
        $condition=array('special_type'=>$special_type,'is_recommend'=>0);
        if(isset($_REQUEST['eliminate_special'])){
            $temp=explode(',',$_REQUEST['eliminate_special']);
            $condition['special_id']=array('not in',$temp);
        }
        $limit=isset($_REQUEST['limit'])?intval($_REQUEST['limit']):2;
        if(0==$limit){
            output_data(array());
        }
        $res=$model_mb_special->getRandom($condition,'*',$limit);
        foreach($res as $key=>$value){
            $res[$key]['special_image']=getMbSpecialImageUrl($value['special_image']).'-shopRandomTheme';
        }
        output_data($res);
    }

    /**
     * 专题列表
     * @param special_type 专题类型 1：商城 ，2:本土
     */
    public function get_listOp(){
        $check_param=array('special_type');
        check_request_parameter($check_param);
        $model_mb_special = Model('mb_special');
        $special_type=intval($_REQUEST['special_type']);
        $condition=array('special_type'=>$special_type);
        $list=$model_mb_special->getMbSpecialList($condition,$this->page,'sort asc');
        foreach($list as $key=>$value){
            $list[$key]['special_image']=getMbSpecialImageUrl($value['special_image']);
        }
        output_data($list);
    }

    /**
     * 专题详情
     */
    public function get_detailOp(){
        $check_param=array('special_id');
        check_request_parameter($check_param);
        $special_info=Model('mb_special')->getSpecialSimple(array('special_id'=>intval($_REQUEST['special_id'])));
        if(empty($special_info)){
            output_error('专题不存在');
        }
        $special_info['special_image']=getMbSpecialImageUrl($special_info['special_image']);
        $res=Model('mb_special')->getMbSpecialItemUsableListByID(intval($_REQUEST['special_id']));
        $goods=array();
        foreach($res as $value){
            if(isset($value['goods'])){
                $goods=array_merge($goods,$value['goods']['item']);
            }
        }
        output_data(array('special'=>$special_info,'goods'=>$goods));
    }

    /**
     * @param string $special_type  专题类型 1:活动(就是以前的商城专题) 2:本土 3:分类专题
     * @param string $recommend  是否推荐
     */
    public function getListOp(){

        $check_param = array('special_type');
        check_request_parameter($check_param);

        $model_mb_special = Model('mb_special');
        $special_type = intval($_REQUEST['special_type']);

        $recommend = isset($_REQUEST['recommend']) ? intval($_REQUEST['recommend']) : null;

        $condition = array();
        if ($recommend == 1) {
            $_REQUEST['page'] = 30;
            $condition['is_recommend'] = 1;
        }
        $condition['special_type'] = $special_type;
        $list = $model_mb_special->getSpecialList($condition);
        output_data($list);
    }

    /**
     *专题详情
     * @param string $special_id 专题ID
     */
    public function getDetailOp()
    {
        $check_param = array('special_id');
        check_request_parameter($check_param);
        $special_info = Model('mb_special')->getDetail(array('special_id' => intval($_REQUEST['special_id'])));
        if (empty($special_info)) {
            output_error('专题不存在');
        }
        $res = Model('mb_special')->getMbSpecialItemUsableListByID(intval($_REQUEST['special_id']));
        $goods = array();
        $slide = array();
        if(isset($res[1]) && isset($res[1]['goods'])){
            $goods = $res[1]['goods']['item'];
            foreach ($goods as $index => $item) {
                $goods[$index]['goods_price'] = $item['goods_promotion_price'];
                $goods[$index]['goods_describe'] = $item['goods_jingle'];
                unset($goods[$index]['goods_salenum'],
                    $goods[$index]['goods_collect'],
                    $goods[$index]['goods_click'],
                    $goods[$index]['goods_promotion_price'],
                    $goods[$index]['goods_jingle']
                );
            }
        }

        if(isset($res[0]) && isset($res[0]['adv_list'])){
            $slide = $res[0]['adv_list']['item'];
        }
        output_data(array('goods'=>$goods
        ,'slide'=>$slide
        ));
    }
}
