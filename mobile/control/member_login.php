<?php
/**
 * 前台登录-手机号码登录
 * 
 * @author lijunhua
 * @since  2015-08-04
 * @version 1.0
 */

use Tpl;

defined('emall') or exit('Access Invalid!');

class member_loginControl extends mobileHomeControl {

    public function __construct(){
            parent::__construct();
    }
    
    /**
     * 登录
     * 
     * @param  string $param['username']           用户名|手机号码
     * @param  string $param['password']           密码
     * @param  string $param['registration_id']    注册ID
     * @param  string $param['client_type']        终端方式 默认WAP
     */
    public function indexOp() {
        if(version_compare($_REQUEST['version_name'],'2.1.3')<0 && strtolower($_REQUEST['client_type']) == 'ios'){
            output_error('尊敬的用户，由于您使用的版本不支持预售，将不能继续使用，请下载并安装最新版本', array(), ERROR_CODE_OPERATE);
        }
        if (empty($_REQUEST['username']) || empty($_REQUEST['password'])) {
            output_error('账号或密码为空', array(), ERROR_CODE_OPERATE);
        }
        if(isset($_REQUEST['user_type'])&&$_REQUEST['user_type']==1) {
            $user_type = 2;
        }
        else {
            $user_type = 0;
        }
        $model_member = Model('member');
        $array = array();
        $array['member_name'] = $_REQUEST['username'];
        //$array['member_type'] = 0;
        $member_info = $model_member->getMemberInfo($array);
        // 用户名未查到，进一步查询手机号码
        if (empty($member_info)) {
            $mobile_condition['member_mobile']  = $_REQUEST['mobile'];

            $member_info = $model_member->getMemberInfo($mobile_condition);
            if (empty($member_info)) {
               output_error('账号或密码错误', array(), ERROR_CODE_OPERATE);
            }
        }

        if ($member_info['member_state'] == 0) {
            output_error('账户被停用', array(), ERROR_CODE_OPERATE);
        }
        
        if ($member_info['member_passwd'] !=  md6($_REQUEST['password'], $member_info['member_salt'])) {
            output_error('登录或密码错误', array(), ERROR_CODE_OPERATE);
        }
        

        $agent = isset($_REQUEST['client_type']) ? $_REQUEST['client_type'] : 'wap';
        $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $agent, $user_type);
        if (!$token) {
            output_error('登录失败', array(), ERROR_CODE_AUTH);
        }

        // 登录成功后，处理下registration_id
        if (isset($_REQUEST['registration_id']) && !empty($_REQUEST['registration_id'])) {
            if($user_type==0) {
                $model_member->set_registration_id($member_info['member_id'], $_REQUEST['registration_id']);
            }
        }
        // 经纬度存储
        if(!empty($_REQUEST['lng'])&&$_REQUEST['lng']!=0&&!empty($_REQUEST['lat'])&&$_REQUEST['lat']!=0) {
            $location_update = array();
            $location_update['lat'] = $_REQUEST['lat'];
            $location_update['lng'] = $_REQUEST['lng'];
            $location_where = array();
            $location_where['member_id'] = $member_info['member_id'];
            Model('member_common')->editMemberCommon($location_where, $location_update);
        }

        // 用户区域存储
        if(!empty($_REQUEST['district'])&&!empty($_REQUEST['city'])&&!empty($_REQUEST['province'])){
            //获取省市区id
            $district_where = array();
            $district_where['area_name'] = $_REQUEST['district'];
            $district_where['area_deep'] = 3;
            $district_list = Model('area')->getAreaList($district_where);
            $city_where = array();
            $city_where['area_name'] = $_REQUEST['city'];
            $city_where['area_deep'] = 2;
            $city_list = Model('area')->getAreaList($city_where);
            if(!empty($district_list)&&!empty($city_list)){
                foreach($district_list as $district_item){
                    foreach($city_list as $city_item){
                        if($district_item['area_parent_id']==$city_item['area_id']){
                            $member_update = array();
                            $member_update['member_areaid'] = $district_item['area_id'];//区域id
                            $member_update['member_cityid'] = $city_item['area_id'];//市id
                            $member_update['member_provinceid'] = $city_item['area_parent_id'];//省id
                            $member_update['member_areainfo'] = $_REQUEST['province']." ".$city_item['area_name']." ".$district_item['area_name']." ";
                            $member_where['member_id'] = $member_info['member_id'];
                            //写入区域到数据库
                            Model('member')->editMember($member_where,$member_update);
                            break;
                        }
                    }
                }
            }
        }
        $result = array(
            'token'             => $token,
            'user_id'           => $member_info['member_id'],
            'username'          => $member_info['member_name'],
            'member_avatar'     => $member_info['member_avatar'],
            'invitation'        => $member_info['invitation'],
            'member_avatar_url' => getMemberAvatar($member_info['member_avatar']),
            //2015-10-29 solon.ring2011@gmail.com
            'nickname'          =>  $member_info['member_truename'],
            'member_token'      =>  $member_info['member_passwd'],
            'member_mobile'     => $member_info['member_mobile'],
            'service_telephone' => '4006277745',
        );
        output_data($result);
    }


    /**
     * 根据member_id来获取用户图片 
     * @return [type] [description]
     */
    public function get_user_picOp()
    {
        $model_member = Model('member');
        $member_id = $_REQUEST['member_id'];
        if(!isset($member_id) || empty($member_id)){
            output_error('用户无法查找', array(), ERROR_CODE_ARG);
        }
        $rt = getMemberAvatarForID($member_id);
        output_data($rt); 
    }
    
    /**
     * 重置密码第1步
     * 
     * @param string $param['mobile']
     * @param string $param['vertify_code']
     */
     public function verify_code_repwdOp() {
      
        $this->_valid_mobile();
        if (!isset($_REQUEST['vertify_code']) || empty($_REQUEST['vertify_code'])){
            output_error('验证码不能为空', array(), ERROR_CODE_ARG);
        }

        $mobile = $_REQUEST['mobile'];
        $this->_valid_field_vertify_code($mobile);
        
        // 验证成功
        output_data(array());
    }
    
    /**
     *  重置密码第2步
     * 
     * @param  string $param['mobile']        手机号（手机号有可能是用户名）
     * @param  string $param['password']      密码
     * @param  string $param['vertify_code']  手机验证码
     */
    public function reset_passwordOp(){
        
        $this->_valid_mobile();
        if (!isset($_REQUEST['vertify_code']) || empty($_REQUEST['vertify_code'])) {
            output_error('手机验证码不能为空', array(), ERROR_CODE_ARG);
        }
        $this->_valid_field_vertify_code($_REQUEST['mobile']);
        
        if (!isset($_REQUEST['password']) || empty($_REQUEST['password'])) {
            output_error('密码不能为空');
        }
        
        if (!preg_match('/^\w{6,15}$/', $_REQUEST['password'])) {
            output_error('密码长度必须保持在6-15位之间');
        }    
        
        $model_member = Model('member');


        $condition['member_name'] = $_REQUEST['mobile'];
        $member_info = $model_member->getMemberInfo($condition);
        // 先找用户名，再找手机号
        if (empty($member_info)) {
            $re_condition['member_mobile']   = $_REQUEST['mobile'];
            $member_info = $model_member->getMemberInfo($re_condition);
            if (empty($member_info)) { 
                output_error('不存在用户');
            }
        }
       
        
        $update_reset = array('member_passwd' => md6($_REQUEST['password'], $member_info['member_salt']));
        //更改环信密码 solon.ring2011@gmail.com
        $easemob = new Easemob();
        $rt =$easemob->changePwdToken($_REQUEST['mobile'],$update_reset['member_passwd'],$member_info['member_passwd']);
        //file_put_contents('/alidata/www/default/agg/a.txt',$rt);
        //----
        $status = $model_member->editMember(array('member_id' => $member_info['member_id']), $update_reset);
        if (!$status) {
           output_error('密码重置失败');
        } 
        
        $model_mb_user_token = Model('mb_user_token');
        $model_mb_user_token->delMbUserToken($condition);
        output_data('密码重置成功');
    }

    /**
     * 回答密保问题
     * @param string $param['mobile']       手机
     */
    public function answer_member_security_questionOp()
    {
        $this->_valid_mobile();

        if(!isset($_REQUEST['answer_1']) || $_REQUEST['answer_1'] == '') {
            output_error('请回答问题1');
        }elseif(!isset($_REQUEST['answer_2']) || $_REQUEST['answer_2'] == '') {
            output_error('请回答问题2');
        }elseif(!isset($_REQUEST['answer_3']) || $_REQUEST['answer_3'] == '') {
            output_error('请回答问题3');
        }

        $model_member = Model('member');

        $condition['member_name'] = $_REQUEST['mobile'];
        $member_info = $model_member->getMemberInfo($condition);
        // 先找用户名，再找手机号
        if (empty($member_info)) {
            $re_condition['member_mobile']   = $_REQUEST['mobile'];
            $member_info = $model_member->getMemberInfo($re_condition);
            if (empty($member_info)) {
                output_error('不存在用户');
            }
        }

        $member_security_question = array();
        $member_security_question['answer_1'] = trim($_REQUEST['answer_1']);
        $member_security_question['answer_2'] = trim($_REQUEST['answer_2']);
        $member_security_question['answer_3'] = trim($_REQUEST['answer_3']);
        $member_security_question['member_mobile'] = $member_info['member_mobile'];

        $result = $model_member->getMemberSecurityQuestion(array('member_id'=>$member_info['member_id']),'answer_1,answer_2,answer_3');
        if($result) {
            $result['member_mobile'] = $member_info['member_mobile'];
            $ask = md5(serialize($result));
            if(md5(serialize($member_security_question)) == $ask){
                output_data_msg(array('answer_key'=>$ask),'回答正确');
            }
        }
        output_error('回答错误');
    }

    /**
     * 通过密保key修改密码
     * @param string $param['mobile']       手机
     */
    public function answer_reset_passwordOp()
    {
        $this->_valid_mobile();

        if(!isset($_REQUEST['answer_key']) || $_REQUEST['answer_key'] == '') {
            output_error('请设置answer_key');
        }

        if (!isset($_REQUEST['password']) || empty($_REQUEST['password'])) {
            output_error('密码不能为空');
        }

        if (!preg_match('/^\w{6,15}$/', $_REQUEST['password'])) {
            output_error('密码长度必须保持在6-15位之间');
        }

        $model_member = Model('member');

        $condition['member_name'] = $_REQUEST['mobile'];
        $member_info = $model_member->getMemberInfo($condition);
        // 先找用户名，再找手机号
        if (empty($member_info)) {
            $re_condition['member_mobile']   = $_REQUEST['mobile'];
            $member_info = $model_member->getMemberInfo($re_condition);
            if (empty($member_info)) {
                output_error('不存在用户');
            }
        }

        $result = $model_member->getMemberSecurityQuestion(array('member_id'=>$member_info['member_id']),'answer_1,answer_2,answer_3');
        if($result) {
            $result['member_mobile'] = $_REQUEST['mobile'];
            $ask = md5(serialize($result));
            if($_REQUEST['answer_key'] == $ask){
                $update_reset = array('member_passwd' => md6($_REQUEST['password'], $member_info['member_salt']));
                //更改环信密码
                $easemob = new Easemob();
                $rt =$easemob->changePwdToken($_REQUEST['mobile'],$update_reset['member_passwd'],$member_info['member_passwd']);
                $status = $model_member->editMember(array('member_id' => $member_info['member_id']), $update_reset);
                if (!$status) {
                    output_error('密码重置失败');
                }
                $model_mb_user_token = Model('mb_user_token');
                $model_mb_user_token->delMbUserToken($condition);
                output_data_msg('密码重置成功');
            }
        }
        output_error('回答错误');
    }

    /**
     * 通过url绑定邮件
     * @param string $param['email']
     * @param string $param['vertify_code']     验证码
     */
    public function url_bind_mailOp()
    {
        if(!isset($_REQUEST['email']) || $_REQUEST['email'] == '') {
            output_error('缺少email参数');
        }

        $member_email = $_REQUEST['email'];

        $this->_valid_field_vertify_code(str_replace('.', '_', $member_email));

        $member_model = Model('member');

        $member_info = $member_model->getMemberInfo(array('member_email'=>$member_email));

        if(!$member_info){
            output_error('无此邮箱');
        }

        if(intval($member_info['member_email_bind']) != 1) {

            $result = $member_model->editMember(array('member_email' => $member_email), array('member_email_bind' => 1));

            if ($result) {
                output_data_msg('绑定成功');
            }
            output_error('绑定失败');
        }else{
            output_data_msg('已绑定');
        }
    }

    /**
     * 检查手机号码是否存在
     * 
     * @param string $param['mobile']            手机号码
     * @param string $param['is_return_true']    是否返回200 默认0  为1时号码存在返回200
     */ 
    public function check_mobileOp() {
        $this->_valid_mobile();
        $mobile = $_REQUEST['mobile'];
        $member_info = Model('member')->getMemberInfo(array('member_mobile' => $mobile));
        
        if (isset($_REQUEST['is_return_true']) && $_REQUEST['is_return_true'] == 1) {
            $member_info ? output_data(array()) : output_error('手机号号码不存在');
        } else {
            $member_info ? output_error('该手机号已被注册') : output_data(array());
        }
        
    }  
    /**
     * 检查邀请码是否存在
     * @param string $param['code'] 邀请码
     */ 
    public function check_codeOp() {
        $this->_valid_code();
        $code = $_REQUEST['code'];
        if (trim($code) == '00000000') {
            output_data(array());
        } 
        $member_info = Model('member')->getMemberInfo(array('invitation' => $code));
        if (empty($member_info)) {
            output_error('该邀请码不存在');
        }
        output_data(array());
    }
    
    /**
     * 发送手机验证码
     * 
     * @param string $param['mobile'] 手机号码
     */
    public function send_verify_codeOp() {
        $this->_valid_mobile();

        $verify_code = rand(100,999).rand(100,999);
        $mobile  = $_REQUEST['mobile'];
        $user_type = $_REQUEST['user_type'];
        if(empty($user_type)){
            $user_type = 0;
        }
        $message = "【爱个购】您的验证码为123456，请于30分钟内完成验证，如非本人操作，请忽略本短信。";
        if($user_type==0){
            $message = "【爱个购】您的验证码为".$verify_code."，请于30分钟内完成验证，如非本人操作，请忽略本短信。";
        }
        else {
            $message = "【跑腿邦】您的验证码为".$verify_code."，请于30分钟内完成验证，如非本人操作，请忽略本短信。";
        }
        $param_sms = "{$verify_code},30" ;
        $sms = new Sms();
        $sms_type = 1;//短信服务商 0 upass  1 云片
        $code_count = get_file_last_cache($mobile, 'cache/sms_code');
        if(!empty($code_count)) {//有缓存
            $code_count=$code_count+1;
            $sms_type = $code_count%2;
        }
        else {
            $code_count = 1;
        }
        //爱个购
        if($user_type==0){
            if ($sms_type == 0) {
                $result = $sms->send($mobile, MOBILE_AUTH_CODE_TEMPLATE, $param_sms);
                if (!$result) {
                    output_error('发送失败,请稍后再试', array(), ERROR_CODE_OPERATE);
                }
            }
            else {
                $result = $sms->notice_send($mobile, $message);
                $result_array = json_decode($result,true);
                if ($result_array["code"] != 0) {
                    output_error('发送失败，请稍后再试', array(), ERROR_CODE_OPERATE);
                }
            }
        }
        //跑腿邦
        else {
            $result = $sms->notice_send($mobile, $message);
            $result_array = json_decode($result,true);
            if ($result_array["code"] != 0) {
                output_error('发送失败，请稍后再试', array(), ERROR_CODE_OPERATE);
            }
        }
        // 设置短信缓存
        $cache_path =  'cache/sms_code/';
        $cache_result = set_file_cache($mobile, $verify_code, $cache_path, 1800,$code_count);    //将$data数组生成到setting文件缓存
        if (!$cache_result) {
             output_error('发送失败', array(), ERROR_CODE_DATABASE);
        }
        // 发送成功
        output_data(array());
    }

    /**
     * 跑腿邦，注册页面发送验证码功能
     */
    public function adt_login_send_verify_codeOp(){

        $this->_valid_mobile();
        $mobile = $_REQUEST['mobile'];
        $member_info = Model('member')->getMemberInfo(array('member_mobile' => $mobile));
        if($member_info){
            output_error('用户名已注册', array(), ERROR_CODE_ARG);
        }else{
            $this->send_verify_codeOp();
        }
    }


    /**
     * 注册第一步提交，验证手机号和短信验证码
     * 
     * @param string $param['mobile']
     * @param string $param['vertify_code']
     */
  public function vertify_codeOp() {
      
        $this->_valid_mobile();
        if (!isset($_REQUEST['vertify_code']) || empty($_REQUEST['vertify_code'])){
            output_error('验证码不能为空', array(), ERROR_CODE_ARG);
        }

        $mobile = $_REQUEST['mobile'];
        
        $model_member = Model('member');
        $condition          =   array();
        $condition_name     =   array();
        $condition['member_mobile']     =   $mobile;
        $condition_name['member_name']  =   $mobile;
        $member_info = $model_member->getMemberInfo($condition,'member_id');
        $member_name = $model_member->getMemberInfo($condition_name,'member_id');
        if ($member_info || $member_name) {
            output_error('该手机号已被使用，请更换其它手机号');
        }

        $this->_valid_field_vertify_code($mobile);
        
        // 验证成功
        output_data(array());
    }
    

    /**
     * 注册第二步，提交注册信息
     * 
     * @param   string $param['mobile']           手机号
     * @param   string $param['password']         密码
     * @param   string $param['nick_name']        昵称|真实名称 （选填）
     * @param   string $param['member_sex']       性别 保密0  1男 2女 默认2
     * @param   string $param['code']             邀请码 （选填）
     * @param   string $param['vertify_code']     验证码
     * @param   string $param['client_type']      终端方式 默认WAP
     */
    public function registerOp() {
        // 注销验证码缓存
        $model_member = Model('member');

        $register_info = array();
        $register_info['password']         = $_REQUEST['password'];
        $register_info['password_confirm'] = $_REQUEST['password'];//前端确认码不传
        $register_info['mobile']           = $_REQUEST['mobile'];
        if(!empty($_REQUEST['nick_name'])){
            if(cn_strlen($_REQUEST['nick_name'])<2)
               output_error('昵称位数不能小于两位');
        }
        $register_info['nickname']         = empty($_REQUEST['nick_name']) ? 'agg_' . random_str(8) :  $_REQUEST['nick_name'];  
        $register_info['member_sex']       = empty($_REQUEST['member_sex']) ? 2 : $_REQUEST['member_sex'];  
        
        //设置邀请码入库前做检验判断
        if (isset($_REQUEST['code']) && !empty($_REQUEST['code'])) {
            $code = $_REQUEST['code'];
            if (trim($code) != '00000000') {
                $member_info = Model('member')->getMemberInfo(array('invitation' => $code));
                if (empty($member_info)) {
                    output_error('该邀请码不存在');
                }
            }
        }

        if(isset($_REQUEST['register_from']) && $_REQUEST['register_from'] == 2){
            $register_info['register_from']  =   2;
        }else{
            $register_info['register_from']  =   1;
        }

        if (!isset($_REQUEST['password']) || empty($_REQUEST['password'])) {
            output_error('密码不能为空');
        }
        
        if (!preg_match('/^\w{6,15}$/', $_REQUEST['password'])) {
            output_error('密码长度必须保持在6-15位之间');
        }  
        
        
        // 验证码校验
        $this->_valid_field_vertify_code($register_info['mobile']);
        
        $register_info['code']             = empty($_REQUEST['code']) ? '' :  $_REQUEST['code'];  
    
        $member_info = $model_member->register_mobile($register_info);
        if (isset($member_info['error'])) {
             output_error($member_info['error']);
        }
        
        $client_type = isset($_REQUEST['client_type']) ? $_REQUEST['client_type'] : 'wap';
        
        $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $client_type);
        if(!$token) {
             output_error('注册失败', array(), ERROR_CODE_OPERATE);
        }
        
        $result = array(
            'token'           => $token ,
            'user_id'       => $member_info['member_id'],
            'username'      => $member_info['member_name'],
            'member_avatar' => $member_info['member_avatar'],
        );
        
        // 注销验证码缓存
        del_file_cache($_REQUEST['mobile'], 'cache/sms_code');
        
        output_data($result);
    }

    /*-------------------以下是私有方法-------------------*/
    
    /**
     * 登录生成token
     */
    private function _get_token($member_id, $member_name, $client='android', $user_type=0) {
        $model_mb_user_token = Model('mb_user_token');

        //重新登录后以前的令牌失效
        //暂时停用
        $condition = array();
        $condition['member_id'] = $member_id;
        $condition['client_type'] = $client;
        $condition['token_type'] = $user_type;
        $model_mb_user_token->delMbUserToken($condition);

        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0,999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;
        $mb_user_token_info['token_type'] = $user_type;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if($result) {
            return $token;
        } else {
            return null;
        }

    }
    
    
    private function _valid_code() {
        if (!isset($_REQUEST['code']) || empty($_REQUEST['code'])){
            output_error('邀请码不能为空', array(), ERROR_CODE_ARG);
        }
        if (!preg_match('/^\d{2,10}|00000000$/', $_REQUEST['code'])){
            output_error('请正确填写邀请码', array(), ERROR_CODE_ARG);
        }
    }
    
    private function _valid_mobile() {
        if (!isset($_REQUEST['mobile']) || empty($_REQUEST['mobile'])){
            output_error('手机号码不能为空', array(), ERROR_CODE_ARG);
        }
        if (!preg_match('/^1\d{10}$/', $_REQUEST['mobile'])){
            output_error('请正确填写手机号码', array(), ERROR_CODE_ARG);
        }
    }
    
    protected function _valid_field_vertify_code($mobile)
    {
        $valid_code = get_file_cache($mobile, 'cache/sms_code');
        if(empty($valid_code)) {
              output_error('验证码已过期,请重新发送');
        }
        
        if ($valid_code != $_REQUEST['vertify_code']) {
             output_error('验证码错误');
        }
        
    }
    
}
