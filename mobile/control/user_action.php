<?php
/**
 * 本土用户相关。
 *用户预约功能,检测商品是否已被收藏
 * 李熙宇
 */

defined('emall') or exit('Access Invalid!');
class user_actionControl extends mobileMemberControl{
    public function __construct() {
        parent::__construct();
    }


    //预约功能
    public function bookingOp(){
        $check_parm=array('goods_id','reserve_time','reserve_num','remark');
        check_request_parameter($check_parm);
        if(intval($_REQUEST['reserve_num'])<1) output_error('预约份数不能为0');
        $goods_id=intval($_REQUEST['goods_id']);
        $goods_info = Model('goods')->getGoodsInfoByID($goods_id);
        if(!$goods_info){
            output_error('商品不存在');
        }
        $store_info = Model('store')->find($goods_info['store_id']);
        if(!$store_info){
            output_error('店铺不存在');
        }
        $reserve_time=strtotime($_REQUEST['reserve_time']);
        if(!$reserve_time){
            output_error('预约时间格式错误');
        }
        $data['goods_id']=$goods_id;
        $data['goods_name']=$goods_info['goods_name'];
        $data['store_id']=$goods_info['store_id'];
        $data['store_name']=$goods_info['store_name'];
        $data['good_image']=$goods_info['goods_image'];
        $data['goods_price']=$goods_info['goods_price'];
        $data['goods_marketprice']=$goods_info['goods_marketprice'];
        $data['reserve_num']=intval($_REQUEST['reserve_num']);
        $data['member_id']=$this->member_info['member_id'];
        $data['remark']=$_REQUEST['remark'];
        $data['reserve_time']=$reserve_time;
        $data['create_time']=time();
        $data['lat']=floatval($store_info['lat']);
        $data['lng']=floatval($store_info['lng']);
        $data['store_phone']=$store_info['store_phone'];
        $data['store_address']=$store_info['store_address'];
        $res=Model('good_reserves')->insert($data);
        if(!$res){
            output_error('保存失败');
        }else{
            output_data('预约成功');
        }
    }


    //预约信息详情页
    public function book_detailOp(){
        $check_parm=array('id');
        foreach($check_parm as $value) {
            if (!isset($_REQUEST[$value])) output_error('系统错误');
        }
        $booking_info=Model('good_reserves')->find(intval($_REQUEST['id']));
        if(!$booking_info) output_error('系统错误');
        $booking_info['goods_price']=del0($booking_info['goods_price']);
        $booking_info['goods_marketprice']=del0($booking_info['goods_marketprice']);
        output_data($booking_info);
    }

    //客户端我的预约记录，列表分页参数:curpage
    public function book_mineOp(){
        $check_parm=array('lat','lng');
        check_request_parameter($check_parm);
        if(isset($_POST['curpage'])) $_GET['curpage']=$_POST['curpage'];
        $distance_sql=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000) as distance';
        $booking_list=Model('good_reserves')->field('*,'.$distance_sql)->where(array('member_id'=>$this->member_info['member_id']))->order('reserve_time desc')->page($this->page)->select();
        foreach($booking_list as $key=>$value){
            $booking_list[$key]['good_image']=cthumb($booking_list[$key]['good_image'],'',$booking_list[$key]['store_id']);
        }
        output_data($booking_list);
    }

    //取消预约
    public function book_cancleOp(){
        $check_param=array('id');
        check_request_parameter($check_param);
        $res=Model('good_reserves')->delete(array('where'=>array('id'=>intval($_REQUEST['id']),'member_id'=>$this->member_info['member_id'])));
        if($res) output_data('取消成功');
        else   output_error('取消失败');
    }

    //判断指定商品是否已收藏
    public function is_favoritesOp(){
        $check_param=array('good_id');
        check_request_parameter($check_param);
        $where['fav_type']='goods';
        $where['fav_id']=intval($_REQUEST['good_id']);
        $where['member_id']=$this->member_info['member_id'];
        $res=Model('favorites')->where($where)->find();
        if($res){
            output_data('yes');
        }else{
            output_data('no');
        }
    }

    //判断指定店铺是否已收藏
    public function is_favorites_storeOp(){
        $check_param=array('store_id');
        check_request_parameter($check_param);
        $where['fav_type']='store';
        $where['fav_id']=intval($_REQUEST['store_id']);
        $where['member_id']=$this->member_info['member_id'];
        $res=Model('favorites')->where($where)->find();
        if($res){
            output_data('yes');
        }else{
            output_data('no');
        }
    }

    //反馈功能
    public function feedbackOp(){
        $check_parm=array('title','content');
        check_request_parameter($check_parm);

        $data['member_id']=$this->member_info['member_id'];
        $data['title']=$_REQUEST['title'];
        $data['content']=$_REQUEST['content'];
        $data['feed_time']=time();
        $res=Model('feedback')->insert($data);
        if(!$res){
            output_error('反馈失败');
        }else{
            output_data('反馈成功');
        }
    }


}