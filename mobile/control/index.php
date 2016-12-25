
<?php
/**
 * cms首页
 */

use Tpl;
defined('emall') or exit('Access Invalid!');
class indexControl extends mobileHomeControl{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 首页
     */
    public function indexOp() {
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialIndex();
        $this->_output_special($data, $_REQUEST['type']);
    }

    /**
     * 专题
     */
    public function specialOp() {
        $model_mb_special = Model('mb_special');
        $data = $model_mb_special->getMbSpecialItemUsableListByID($_REQUEST['special_id']);
        if(4==$_REQUEST['special_id']){
            foreach($data[0]['home3']['item'] as $key=>$value){
                $data[0]['home3']['item'][$key]['image']=$value['image'].'-shoptopads';
            }
        }
        $this->_output_special($data, $_REQUEST['type'], $_REQUEST['special_id']);
    }

    /**
     * 输出专题
     */
    private function _output_special($data, $type = 'json', $special_id = 0) {
        $model_special = Model('mb_special');
        if($_REQUEST['type'] == 'html') {
            $html_path = $model_special->getMbSpecialHtmlPath($special_id);
            if(!is_file($html_path)) {
                ob_start();
                Tpl::output('list', $data);
                Tpl::showpage('mb_special');
                file_put_contents($html_path, ob_get_clean());
            }
            header('Location: ' . $model_special->getMbSpecialHtmlUrl($special_id));
            die;
        } else {
            $this->get_adv($data);
            output_data($data);
        }
    }

    /**
     * 添加广告
     * @param $data
     */
    public function get_adv(&$data){
        if(!isset($_REQUEST['special_id'])||!in_array($_REQUEST['special_id'],array(2))){
            return;
        }

        $condition=array(
            'adv_status'=>1,
            'adv_start_date'=>array('elt',time()),
            'adv_end_date'=>array('egt',time()),
        );
        $model=Model('fix_adv');
        $client_type=strtolower($_REQUEST['client_type']);
        if(!isset($model->client_to_channel[$client_type])){
            output_error('客户端类型错误');
        }
        $city_id=$area_id=$province_id=0;
        if(isset($_REQUEST['city_name'])&&!empty($_REQUEST['city_name'])){
            $area_info=Model('area')->getAreaInfo(array('area_name'=>trim($_REQUEST['city_name']),'area_deep'=>2));
            $city_id=isset($area_info['area_id'])?$area_info['area_id']:0;
            $province_id=isset($area_info['area_parent_id'])?$area_info['area_parent_id']:0;
        }
        if(isset($_REQUEST['district_name'])&&!empty($_REQUEST['district_name'])){
            $area_info=Model('area')->getAreaInfo(array('area_name'=>trim($_REQUEST['district_name']),'area_deep'=>3));
            $area_id=isset($area_info['area_id'])?$area_info['area_id']:0;
        }

        $channel=$model->client_to_channel[$client_type];
        $pri_adv_list=$model->getAdvList($condition);
        $output_adv=array();
        foreach($pri_adv_list as $value){
            $visualise_client=explode(',',$value['adv_channel']);
            if(!in_array(0,$visualise_client) && !in_array($channel,$visualise_client)){
                continue;
            }
            if($value['adv_limit_area']==1){
                //限制区域。检查该区域是否可显示这条广告
                $visualise_province=explode(',',$value['adv_provinceids']);
                $visualise_city=explode(',',$value['adv_cityids']);
                $visualise_area=explode(',',$value['adv_areaids']);
                $continue=true;
                $temp=$this->check_area($province_id,$visualise_province);
                if(1==$temp){
                    continue;
                }
                if(2==$temp){
                    $continue=false;
                }
                if($continue) {
                    $temp = $this->check_area($city_id, $visualise_city);
                    if (1 == $temp) {
                        continue;
                    }
                    if (2 == $temp) {
                        $continue = false;
                    }
                    if($continue) {
                        $temp = $this->check_area($area_id, $visualise_area);
                        if (2 != $temp) {
                            continue;
                        }
                    }
                }
            }
            $output_adv[]=array(
                'image'=>UPLOAD_SITE_URL."/".ATTACH_ADV."/".$value['adv_pic_path'],
                'data'=>$value['adv_link'],
                'type'=>'',
                'title'=>'',
                'describe'=>'',
            );
        }

        if(!empty($output_adv)){
            $data[0]['adv_list']['item']=array_merge($data[0]['adv_list']['item'],$output_adv);
        }
    }

    /**
     * @return int 1: 该广告不能显示，2:该广告可以显示，3:继续判断
     */
    private function check_area($area_id,$visualise_arr){
        if(0==$area_id){
            return 1;
        }
        if(in_array($area_id,$visualise_arr)){
            return 2;
        }
        return 3;

    }

    /*
      android客户端版本号
      @author:xuping
    */

    public function apk_versionOp() {
        $version = C('mobile_apk_version');
        $url = C('mobile_apk');
        if(empty($version)) {
            $version = '';
        }
        if(empty($url)) {
            $url = '';
        }

        output_data(array('version' => $version, 'url' => $url));
    }


    /*
    返回推荐的帖子内容， 无需用户登录用户登录
    author xuping
    time 2015年9月9日15:46:09
    */
    public function getrecommendThemeOP(){
        if($_REQUEST['key']){
            $model_mb_user_token = Model('mb_user_token');
            $key = $_REQUEST['key'];
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        }

        //返回所有的帖子
        $model=Model();
        $size=10;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) : 1;
        $res=($page-1)*$size;
        $theme_content_count=$model->table('circle_theme')->where(array('is_closed'=>0 ))->select();

        $theme_content=$model->table('circle_theme')->where(array('is_closed'=>0,'is_recommend'=>1 ))->limit(" $res ,$size" )->order('theme_addtime DESC')->select();
        $circle_threply=Model('circle_threply');
        $member=Model('member');
        foreach ($theme_content as $key => $value) {
            $theme_content[$key]['theme_pic']=getMemberAvatar($value['theme_pic']);
            /*处理增加时间 显示发布时间的格式化*/

            $theme_content[$key]['theme_addtime']= $value['theme_addtime'];
            //$theme_content[$key]['testtime']=$value['theme_addtime'];
            $theme_content[$key]['theme_pic']=getMemberAvatar($value['theme_pic']);
            $res=$circle_threply->where(array('theme_id'=>$value['theme_id']))->order('reply_addtime DESC ')->limit(10)->select();
            foreach ($res as $k => $v) {
                $avatar=$member->where(array('member_id'=>$v['member_id']))->field('member_avatar')->find();
                $res[$k]['avatar']=getMemberAvatar($avatar['member_avatar']);

                $res[$k]['reply_addtime']=$v['reply_addtime'];

            }
            $avatar=$member->where(array('member_id'=>$value['member_id']))->find();
            $theme_content[$key]['member_avatar']=getMemberAvatar($avatar['member_avatar']);
            unset($avatar);

            $result=Model('circle_like')->where(array('theme_id'=>$value['theme_id']))->select();
            foreach ($result as $kkk => $vvv) {
                $avatar=Model('member')->where(array('member_id'=>$vvv['member_id']))->field('member_avatar')->find();
                $result[$kkk]['avatar']=getMemberAvatar($avatar['member_avatar']);
                if( $mb_user_token_info['member_id'] && $mb_user_token_info['member_id']== $vvv['member_id'] ){
                    $theme_content[$key]['member_islike']=1;
                }
            }

            $fres=Model()->table('sns_friend')->where(array('friend_frommid'=>$mb_user_token_info['member_id'],'friend_tomid'=>$value['member_id']))->find();
            if($fres['friend_followstate']==1 && $mb_user_token_info['member_id'] ){
                $theme_content[$key]['member_isfriend']=1;   //单项关注
            }elseif($fres['friend_followstate']==2 && $mb_user_token_info['member_id'] ){
                $theme_content[$key]['member_isfriend']=2;   //双向关注
            }else {
                $theme_content[$key]['member_isfriend']=0;   //未
            }

            /*判断当前的帖子是否是自己的帖子*/
            if($mb_user_token_info['member_id'] && $mb_user_token_info['member_id']==$value['member_id']){
                $theme_content[$key]['is_my']=1;
                $theme_content[$key]['member_isfriend']=0;
            }else{
                $theme_content[$key]['is_my']=0;
            }

            //获取所有tag
            $tag_result=Model()->table('circle_tag')->where(array('theme_id'=>$value['theme_id']))->select();
            $result=array_reverse($result);
            $theme_content[$key]['member_islike']  =isset($theme_content[$key]['member_islike']) ?  1 : 0;
            // $theme_content[$key]['member_isfriend']=isset($theme_content[$key]['member_isfriend']) ?  1 : 0;
            $theme_content[$key]['theme_reply']=$res;
            $theme_content[$key]['like']=$result;
            $theme_content[$key]['tag'] =$tag_result;
            $theme_content[$key]['url']=WAP_SITE_URL.'/share_details.html?theme_id='.$value['theme_id'];
            /*修改用户名称  === 用户昵称  public function getMemberInfoByID($member_id, $fields = '*') { */
            $member_name=Model('member')->getMemberInfoByID($value['member_id'],'member_truename');
            $theme_content[$key]['member_name']=$member_name['member_truename'];
        }
        output_data($theme_content);

    }




    /*
     标签跳转页 api
     2015年9月11日19:24:45
     xuping 位置链接过来
    */
    public function getTagdetailaddOP(){
        $check_param=array('lat','lng');
        $distance=isset($_REQUEST['distance']) ?  $_REQUEST['distance'] : '85000';
        $page    =isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1 ;
        check_request_parameter($check_param);
        if(isset($_REQUEST['curpage']))
            $page=$_REQUEST['curpage'];

        $distance_sql=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000) as distance';
        $distance_condition=' round(6378.138*2*asin(sqrt(pow(sin(('.floatval($_REQUEST['lat']).'*pi()/180-lat*pi()/180)/2),2)+cos('.floatval($_REQUEST['lat']).'*pi()/180)*cos(lat*pi()/180)* pow(sin( ('.floatval($_REQUEST['lng']).'*pi()/180-lng*pi()/180)/2),2)))*1000)';
        //查询店铺的条件
        $option['field']='store_id,store_name,concat(area_info," ",store_address) as area,'.$distance_sql;
        $option['where']='store_type=1 ';
        $option['order']='distance asc';
        $option['where'].=' and '.$distance_condition.'<='.intval($distance);
        $stores=Model('store')->limit(30)->select($option);     //附近的商家


        $where['order_type']=1;
        $key=isset($_REQUEST['key']) ? $_REQUEST['key'] : '';
        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if($model_mb_user_token){
            $where['buyer_id']=$mb_user_token_info['member_id'];
            $where['goods_type']=1;
            $array=Model()->table('order')->where($where)->select();
            $arr=array();
            if(!empty($array)){
                $array=$this->two_array_unique($array);
                foreach ($array as  $value) {
                    $tmp[]=intval(trim($value,'"'));
                }

                foreach ($tmp as  $val) {
                    /*测试使用*/
                    if($val){
                        $arr[]=Model()->table('store')->where(array('store_id'=>$val ))->field('store_id,area_info as area,store_name')->find();
                    }
                }
            }
        }else{
            output_error('对不起，用户身份验证失败');
        }
        $data=array('stores'=>$stores,'mystores'=>$arr);   //我去过的商家信息

        if(empty($stores) && empty($arr)){
            output_data(array());
        }else{
            output_data($data);
        }
    }


    /*
     标签跳转页
     2015年9月11日21:22:41
     time：2015年9月11日21:22:49
     返回热门标签
     */

    public function getTagdetailhotOP(){
        $res=Model()->table('circle_tag')->where(array('tag_type'=>'normal'))->order('addtime DESC')->field('tag_content')->distinct(true)->select();
        if($res){
            output_data($res);
        }else{
            output_data(array());
        }
    }


    /*标签跳转页
    2015年9月11日21:27:06 thumb($goods_info, 60);
    返回购买过的链接
    */
    public function getTagdetailOrdersOP(){
        $key=isset($_REQUEST['key']) ? $_REQUEST['key'] : '';
        $page=isset($_REQUEST['page']) ? $_REQUEST['page'] : 12;
        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if(empty($mb_user_token_info['member_id'])){
            output_error('身份信息验证失败');
        }
        $member_id=$mb_user_token_info['member_id'];
        $where['order_type']=1;
        $where['buyer_id']=$member_id;
        $where['order_state']=40;
        $info=array();
        $result=Model()->table('order')->where($where)->page($page)->order('finnshed_time DESC')->field('order_id')->select();
        foreach ($result as $key => $value) {
            $tmp=Model()->table('order_goods')->where(array('order_id'=>$value['order_id']))->field('goods_id,goods_name,goods_price,goods_image ,order_id')->find();
            if(!empty($tmp)){
                $info[]=$tmp;
            }
        }
        if($info){
            foreach ($info as $k => $v) {
                $info[$k]['goods_id']=$v['goods_id'];
                $info[$k]['goods_name']=$v['goods_name'];
                $info[$k]['goods_price']=$v['goods_price'];
                $info[$k]['goods_image']=thumb($v,60);

            }
        }

        if(!empty($info)){
            output_data($info);
        }else{
            output_data(array());
        }

    }





    /*过滤二位数组中的重复的数组
    xuping*/
    private function two_array_unique($array2D){
        $tmp=array();
        $arr=array();
        foreach ($array2D as $k=> $v){
            $tmp[]=json_encode($v['store_id']);
        }
        $arr=array_unique($tmp);

        return $arr;
    }

    /*
    “我的”需要添加：粉丝数量、我的发现数量、我的订单数量

    看不见的浮云 2015/9/12 11:00:26
    “个人中心”需要添加：用户头像、昵称、性别、是否绑定手机
        public function getMemberInfo($condition, $field = '*', $master = false) {
        return $this->table('member')->field($field)->where($condition)->master($master)->find();
    }
    */
    public function getMyinfoOP(){
        $key=$_REQUEST['key'];
        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if(empty($mb_user_token_info)){
            output_error('身份验证失败');
        }
        $member_id=$mb_user_token_info['member_id'];
        $result=Model()->table('sns_friend')->where(array('friend_tomid'=>$member_id))->select();
        $friend_count=count($result);  //粉丝数量

        $theme_count=Model()->table('circle_theme')->where(array('member_id'=>$member_id))->select();
        $theme_count=count($theme_count);   //发现数量

        $order_info=Model()->table('order')->where(array('buyer_id'=>$member_id))->select();
        $oder_count=count($order_info);     //订单数量

        $array=array(
            'friend_count'=>$friend_count,
            'theme_count'=>$theme_count,
            'oder_count'=>$oder_count,
        );

        if(!empty($array)){
            output_data($array);
        }else{
            output_data(array());
        }
    }

    public function searStoreOP(){
        $storename=$_REQUEST['store_name'];
        if(empty($storename)){
            output_error('参数为空');
        }
        $option['where'].=' store_name like "'.'%'.$storename.'%'.'" ';

        $field='store_id , store_name, concat(area_info," ",store_address) as  area';
        $size=10;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) : 1;
        $res=($page-1)*$size;

        $stores=Model('store')->field($field)->where($option['where'])->limit(" $res ,$size" )->select();
        if(!empty($stores)){
            output_data($stores);
        }else{
            output_data(array());
        }
    }
    /*
     2015年9月14日18:17:20
     徐萍
     获取用户信息
     public function getMemberInfo($condition, $field = '*', $master = false) {
     return $this->table('member')->field($field)->where($condition)->master($master)->find();
     }
    */
    public function getmemberInfoOP(){
        $key=$_REQUEST['key'];          //判断是否是登录状态
        $mid=intval($_REQUEST['o_id']); //点击的用户id
        if(empty($mid)){
            output_error('参数为空');
        }
        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);


        $condition['member_id']=$mid;
        $field='member_truename , member_sex ,member_avatar ,member_name as username';
        $result=Model('member')->getMemberInfo($condition ,$field);
        $result['member_avatar']=getMemberAvatar($result['member_avatar']);
        if($mb_user_token_info){             // 用户已经登录  返回好友状态 给用户
            $fag=0;
            $fans=array();
            $fans=Model()->table('sns_friend')->where(array('friend_tomid'=>$mid))->field('friend_frommid ,friend_followstate')->select();
            foreach ($fans as $key => $value) {
                if( $mb_user_token_info['member_id']== $value['friend_frommid']){
                    $fag=$value['friend_followstate'];
                    break;
                }
            }
            $result['friend_followstate']=$fag;
            output_data($result);
        }else{                      //用户未登录状态
            $result['friend_followstate']=0;
            output_data($result);
        }
    }

    public function getMemberThemepicOP(){
        $member_id=$_REQUEST['member_id'];
        if(empty($member_id)){
            output_error('参数为空');
        }
        $size=10;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) : 1;
        $res=($page-1)*$size;
        $result=Model()->table('circle_theme')->where(array('member_id'=>$member_id))->order('theme_addtime DESC')->field('theme_pic , theme_id')->limit(" $res ,$size" )->select();
        foreach ($result as $key => $value) {
            $result[$key]['theme_pic']=getMemberAvatar($value['theme_pic']);
            $result[$key]['theme_id'] =$value['theme_id'];
        }
        if(!empty($result)){
            output_data($result);
        }else{
            output_data(array());
        }
    }

    //获取点赞的人的 用户id 和头像
    /*
    author xuping
    time 2015年7月24日17:35:18
    tool: sublime
    */
    public function getLikeMemberOP(){
        $theme_id=$_REQUEST['theme_id'];
        $size=10;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) :1 ;
        $resf=($page-1)*$size;
        if(!empty($theme_id)){
            $res=Model()->table('circle_like')->where(array('theme_id'=>$theme_id))->limit("$resf , $size")
                ->order('addtime DESC')->select();
            $arr=array();
            $i=0;
            foreach ($res as $key => $value) {
                // $member_name=Model('member')->getMemberInfoByID($value['member_id'],'member_truename');
                //$arr[$i]=Model('member')->field('member_id,member_truename,member_avatar')->where(array('member_id'=>$value['member_id']))->find();
                $arr[$i]=Model('member')->getMemberInfoByID($value['member_id'],'member_id,member_truename,member_avatar');
                $arr[$i]['add_time']=$value['addtime'];
                $arr[$i]['member_name']=$value['member_name'];
                $i++;
            }

            foreach ($arr as $k => $v) {
                $arr[$k]['avatar']=getMemberAvatar($v['member_avatar']);
                $arr[$k]['member_id']=$v['member_id'];
                $arr[$k]['member_name']=$v['member_truename'];
                //$time=time()-$v['add_time'];
                $arr[$k]['add_time']=$v['add_time'];
            }

            if(!empty($arr)){
                output_data($arr);
            }else{
                output_data(array());
            }
        }else{
            output_data(array());
        }
    }

    /*
     说说的标签合集
     2015年9月15日19:06:09
     xuping
            $size=10;
            $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) :1 ;
            $res=($page-1)*$size;
->limit("$res , $size")
         public function getMemberInfo($condition, $field = '*', $master = false) {
        return $this->table('member')->field($field)->where($condition)->master($master)->find();
    }

     */
    public function gettagdetailOP(){
        $tag_name=$_REQUEST['tag_name'];
        if(empty($tag_name)){
            output_error('参数为空');
        }

        $size=10;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) :1 ;
        $resf=($page-1)*$size;

        $result=Model()->table('circle_tag')->field('theme_id')->distinct(true)->where(array('tag_content'=>$tag_name ,'tag_type'=>'normal'))->limit("$resf , $size")->order('addtime  DESC')->select();

        if(!empty($result)){
            $arr=array();
            foreach ($result as $key => $value) {
                $arr[]=Model()->table('circle_theme')->where(array('theme_id'=>$value['theme_id']))->field('theme_id ,member_id ,theme_addtime,theme_pic')->find();
            }

            foreach ($arr as $k => $v) {
                $condition['member_id']=$v['member_id'];
                $temp=Model('member')->where($condition)->field('member_name,member_truename,member_avatar')->find();
                $res[$k]['theme_id']     =$v['theme_id'];
                $res[$k]['member_id']    =$v['member_id'];
                $res[$k]['theme_pic']    =getMemberAvatar($v['theme_pic']);
                // $res[$k]['theme_addtime']=$v['theme_addtime'];
                /*处理增加时间 显示发布时间的格式化*/
                //$time=time()-$v['theme_addtime'];
                $res[$k]['theme_addtime']=$v['theme_addtime'];
                $res[$k]['member_name']  =$temp['member_truename'];
                $res[$k]['member_avatar'] =getMemberAvatar($temp['member_avatar']);
            }
            output_data($res);
        }else{
            output_data(array());
        }

    }

    public function appBindOP(){
        //file_put_contents('qq.log',json_encode($_REQUEST));
        $opend_id=$_REQUEST['openid'];
        $qqinfo=json_decode($_REQUEST['qqinfo'],true);
        //file_put_contents('qq.log12',$qqinfo);
        $client_type=isset($_REQUEST['client_type']) ? $_REQUEST['client_type'] : 'wap';
        if(empty($opend_id) || empty($qqinfo)){
            output_error('参数为空');
        }
        $model_member=Model('member');

        $member_info = $model_member->getMemberInfo(array('member_qqopenid'=>$opend_id),'member_id,member_name,member_avatar');
        if($member_info){  //用户之前已经用qq 号登录过  返回用户信息给用户
            $token=$this->_get_token($member_info['member_id'] , $member_info['member_name'] , $client_type);
            $result = array(
                'token'             => $token ,
                'user_id'           => $member_info['member_id'],
                'username'          => $member_info['member_name'],
                'member_avatar'     => $member_info['member_avatar'],
                'member_avatar_url' => getMemberAvatarForID($member_info['member_id']),
            );
            output_data($result);
        }else{  //用户第一次用qq注册 登录

            $user_passwd = rand(100000, 999999);
            $user_array = array();
            $user_array['member_name']    = $qqinfo['nickName'];
            $user_array['member_passwd']  = $user_passwd;
            $user_array['member_email']   = '';
            $user_array['member_qqopenid']  =$opend_id;//qq openid
            $user_array['member_qqinfo']  = serialize($qqinfo);//qq 信息
            $result = $model_member->addMember($user_array);
            $member_info = $model_member->getMemberInfo(array('member_qqopenid'=>$opend_id),'member_id,member_name,member_avatar');
            $token=$this->_get_token(4512 , $qqinfo['nickName'] , $client_type);
            $result = array(
                'token'             => $token ,
                'user_id'           => $member_info['member_id'],
                'username'          => $member_info['member_name'],
                'member_avatar'     => $member_info['member_avatar'],
                'member_avatar_url' => getMemberAvatarForID($member_info['member_id']),
            );
            output_data($result);
        }
    }



    private function _get_token($member_id, $member_name, $client='andorid') {
        $model_mb_user_token = Model('mb_user_token');

        //重新登录后以前的令牌失效
        //暂时停用
        $condition = array();
        $condition['member_id'] = $member_id;
        //$condition['client_type'] = $_REQUEST['client'];
        $model_mb_user_token->delMbUserToken($condition);

        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0,999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if($result) {
            return $token;
        } else {
            return null;
        }

    }
    /*查询一个帖子的内容帖子的内容 查询话题的内容*/
    public  function getContentsOP(){
        $theme_id=$_REQUEST['theme_id'];
        if($_REQUEST['key']){
            $model_mb_user_token = Model('mb_user_token');
            $key = $_REQUEST['key'];
            $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
            $member_id=$mb_user_token_info['member_id'];
        }
        //返回 某一帖子的内容  是否已经点过赞

        $theme_content=Model()->table('circle_theme')->where(array('theme_id'=>$theme_id , 'is_closed'=>0 ))->find();
        if(!empty($theme_id) && !empty($theme_content) ){
            //查询是否点过赞
            $circle_threply=Model('circle_threply');
            $member=Model('member');
            $like=Model('circle_like');
            //$result=$member->where(array('member_id'=>$theme_content['member_id']))->find();
            $result=$member->getMemberInfoByID($theme_content['member_id'],'member_avatar,member_truename');
            $theme_content['member_avatar']=getMemberAvatar($result['member_avatar']);  //用户图像
            $theme_content['member_name'] =$result['member_truename'];  //修改的情况下 ，在用户表中获取用户名
            //回复内容
            $res=$circle_threply->where(array('theme_id'=>$theme_content['theme_id']))->order('reply_addtime DESC ')->limit(10)->select();
            if(!empty($res)){
                foreach ($res as $k => $v) {
                    $avatar=$member->where(array('member_id'=>$v['member_id']))->field('member_avatar')->find();
                    $res[$k]['avatar']=getMemberAvatar($avatar['member_avatar']);
                }

            }

            $theme_content['theme_reply']=$res;
            /*用户是否 点赞过*/
            $result=Model('circle_like')->where(array('theme_id'=>$theme_content['theme_id']))->limit(10)->select();
            if(!empty($result)){
                foreach ($result as $kkk => $vvv) {
                    $avatar=Model('member')->where(array('member_id'=>$vvv['member_id']))->field('member_avatar')->find();
                    $result[$kkk]['avatar']=getMemberAvatar($avatar['member_avatar']);
                    if($member_id == $vvv['member_id']){
                        $theme_content['member_islike']=1;
                    }
                }
                $result=array_reverse($result);
            }

            $theme_content['member_islike']=empty($theme_content['member_islike']) ? 0 : 1;

            /*用户是否  关注状态 where(array('friend_frommid'=>$mb_user_token_info['member_id'],'friend_tomid'=>$value['member_id']))->find(); */
            if($member_id){
                $sns=Model()->table('sns_friend')->where(array('friend_tomid'=>$theme_content['member_id'] ,'friend_frommid'=>$member_id))->find();
                if($sns['friend_followstate']==1 ){
                    $theme_content['member_isfriend']=1;   //单项关注
                }elseif($sns['friend_followstate']==2 ){
                    $theme_content['member_isfriend']=2;   //双向关注
                }else {
                    $theme_content['member_isfriend']=0;   //未关注
                }
            }else{
                $theme_content['member_isfriend']=0;
            }


            $theme_content['like']=$result;
            $theme_tag=array();
            $theme_tag=Model()->table('circle_tag')->where(array('theme_id'=>$theme_content['theme_id']))->select();
            if(!empty($theme_tag)){
                foreach ($theme_tag as $key => $value) {
                    if($value['tag_type'] == 'product' ){
                        $id=$value['link_id'];
                        break;
                    }
                }
            }

            if($id){
                $result=Model('goods')->where(array('goods_id'=>$id))->field('store_id , store_name')->find();
                $theme_content['store_id']  =$result['store_id'];
                $theme_content['store_name']=$result['store_name'];
            }

            $theme_content['tag']=$theme_tag;
            $theme_content['theme_pic']=getMemberAvatar($theme_content['theme_pic']);
            //$time=time()-$theme_content['theme_addtime'];
            //$theme_content['url']='http://shop.aigegou.com/agg/wap/sharepersonal.html?theme_id='.$theme_content['theme_id'];
            $theme_content['theme_addtime']=$theme_content['theme_addtime'];
            $theme_content['url']=WAP_SITE_URL.'/share_details.html?theme_id='.$theme_content['theme_id'];;
            if(!empty($theme_content)){
                output_data($theme_content);
            }else{
                output_data(array());
            }

        }
    }

    /*私有方法 格式化时间问题 xuping*/
    private function _formatTime($time){
        if($time < 60){                                                    //模板中 以秒为单位显示
            $result=$time.'秒前';
        }elseif($time > 60  && $time < 3600){                              //模板中 以分钟为单位显示
            $result=(int)($time/(60)).'分钟前';
        }elseif($time > 3600 && $time < 3600*24 ){                         //模板中 显示以小时为单位
            $result=(int)($time/(3600)).'小时前';
        }elseif($time > 3600*24 ){
            $result=(int)($time/(3600*24)).'天前';//模板中 显示以天为单位
        }
        return $result;
    }

    /*获取帖子的评论*/
    public function getThemeReplyOP(){
        $theme_id=$_REQUEST['theme_id'];
        if(empty($theme_id)){
            echo json_encode(array('code'=>ERROR_CODE_AUTH ,'message'=>'参数为空' ));
            exit;
        }
        $model=Model();
        $size=10;
        $page=isset($_REQUEST['curpage']) ? intval($_REQUEST['curpage']) :1 ;
        $res=($page-1)*$size;
        $res=$model->table('circle_threply')->where(array('theme_id'=>$theme_id))->limit("$res , $size")->order('reply_addtime DESC')->select();
        $member=Model('member');

        foreach ($res as $key => $value) {
            // $arr[$i]=Model('member')->getMemberInfoByID($value['member_id'],'member_id,member_truename,member_avatar');
            //$result=$member->where(array('member_id'=>$value['member_id']))->field('member_avatar')->find();
            $result=$member->getMemberInfoByID($value['member_id'],'member_avatar');
            $res[$key]['avatar']=getMemberAvatar($result['member_avatar']);
            if(!empty($value['reply_replyid'])){
                $result=$model->table('circle_threply')->where(array('reply_id'=>$value['reply_replyid']))->find();
                $res[$key]['re_replycontent']  =$result['reply_content'];
            }else{
                $res[$key]['re_replycontent']  ='';
            }
            //$time=time()-$value['reply_addtime'];
            $res[$key]['reply_addtime']=$value['reply_addtime'];
        }
        if(!empty($res)){
            output_data($res);
        }else{
            output_data(array());
        }
    }


}