<?php
/**
 * 短信群发
 * 大亮
 */

defined('emall') or exit('Access Invalid!');
class sms_sendControl extends SystemControl{
    public function __construct(){
        parent::__construct();
        Language::read('sms_send');
    }

    /*界面群发*/
    public function indexOP(){
        $model_goods_class = Model('goods_class');
        $gc_list = $model_goods_class->getGoodsClassListByParentId(0,1);
        Tpl::output('gc_list',$gc_list);
        Tpl::showpage('sms_send.index');
    }
    /*界面群发*/
    public function sms_sendOP(){
        $lang = Language::getLangContent();
        //获取用户
        $condition = array();
        //用户区域条件
        if (isset($_REQUEST['agent_area_name']) && !empty($_REQUEST['agent_area_name'])) {
            $agent_area_arr = explode(' ', $_REQUEST['agent_area_name']);
            if (isset($agent_area_arr[0]) && !empty($agent_area_arr[0])) {
                $province_info = Model('area')->getAreaInfo(array('area_name'=>$agent_area_arr[0],'area_deep'=>1));
                if(!empty($province_info)) {
                    $condition['member.member_provinceid'] = $province_info['area_id'];
                    if (isset($agent_area_arr[1]) && !empty($agent_area_arr[1])) {
                        $city_info = Model('area')->getAreaInfo(array('area_name'=>$agent_area_arr[1],'area_deep'=>2,'area_parent_id'=>$province_info['area_id']));
                        if(!empty($city_info)) {
                            $condition[member.'member_cityid'] = $city_info['area_id'];
                            if (isset($agent_area_arr[2]) && !empty($agent_area_arr[2])) {
                                $district_info = Model('area')->getAreaInfo(array('area_name'=>$agent_area_arr[2],'area_deep'=>3,'area_parent_id'=>$city_info['area_id']));
                                if(!empty($district_info)) {
                                    $condition['member.member_areaid'] = $district_info['area_id'];
                                }
                            }
                        }
                    }
                }
            }
        }
        //用户登录情况
        $last_login = $_REQUEST['last_login'];
        if(!empty($last_login)&&$last_login!=0) {
            $condition['member.member_login_time'] = array('time',array(0,time()-$last_login*86400));
        }
        if(!empty($_REQUEST['member_name'])&&preg_match('/^\d{11}$/',$_REQUEST['member_name'])) {
            $condition['member.member_name'] = $_REQUEST['member_name'];
        }
        //消费分类
        $goods_class = $_REQUEST['goods_class'];
        if(!empty($goods_class)) {
            $class_array = explode(',', $goods_class);
            if(!empty($class_array[0])&&!empty($class_array[1])) {
                $condition['order_goods.gc_id'] = $class_array[1];
            }
            elseif(!empty($class_array[0])) {
                $class_list = Model('goods_class')->getGoodsClassListByParentId($class_array[0]);
                $class_in = agg_array_column($class_list,'gc_id');
                $condition['order_goods.gc_id'] = array('in',$class_in);
            }
        }
        //查询符合条件用户
       $member_list = Model()->table('member,order,order_goods')->join('inner,inner')->on('member.member_id=order.buyer_id,order.order_id=order_goods.order_id')->field('member.member_id,member.member_name')->where($condition)->select();
       $sms_content = $_REQUEST['sms_content'];
        if(!empty($sms_content)) {
            //发送
            if(count($member_list)!=0) {
                $sms = new Sms();
                foreach($member_list as $item) {
                    $result = $sms->notice_send($item['member_name'], $sms_content);
                }
                $url = array(
                    array(
                        'url' => 'index.php?act=sms_send&op=index',
                        'msg' => $lang['send_continue'],
                    ),
                );
                showMessage($lang['send_sucess'], $url);
            }
           else {
               showMessage('无符合条件的用户');
           }
        }
        else {
            showMessage('短信内容为空');
        }
    }
}
