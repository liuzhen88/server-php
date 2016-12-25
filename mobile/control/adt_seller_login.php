<?php
defined('emall') or exit('Access Invalid!');
/**
 * 爱大腿商户端店铺员工登陆
 * @author Administrator
 *
 */
class adt_seller_loginControl extends mobileHomeControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @name 商户员工登陆
     * @param post,username用户名,password密码,client终端类型
     * @author xuping
     * @return 200为成功返回,其他的是失败
     */
    public function indexOp() {
        if (empty($_REQUEST['username']) || empty($_REQUEST['password'])) {
            output_error('账号或密码为空', array(), ERROR_CODE_OPERATE);
        } else {
            $model_store = Model('store');
            $model_seller= Model('seller');
            $res=$model_seller->where(array('seller_name'=> $_REQUEST['username']))->find();
            if (!empty($res)) {
                //验证商户信息
                $model_member = Model('member');
                $array['member_id']     = $res['member_id'];
                $member_info = $model_member->getMemberInfo($array);

                // 用户名未查到，进一步查询手机号码
                if (empty($member_info)) {
                    $mobile_condition['member_mobile']  = $_REQUEST['username'];
                    $member_info = $model_member->getMemberInfo($mobile_condition);
                    if (empty($member_info)) {
                        output_error('登录或密码错误', array(), ERROR_CODE_OPERATE);
                    }
                }

                if ($member_info['member_state'] == 0) {
                    output_error('账户被停用', array(), ERROR_CODE_OPERATE);
                }

                if ($member_info['member_passwd'] !=  md6($_REQUEST['password'], $member_info['member_salt'])) {
                    output_error('密码错误', array(), ERROR_CODE_OPERATE);
                }

                // $array = array();
                $client=isset($_REQUEST['client_type']) ? $_REQUEST['client_type']:'';

                $result=$model_store->where(array('store_id'=>$res['store_id'],'store_state'=>1))->find();
                if(empty($result)){
                    output_error('店铺未开通', array(), ERROR_CODE_OPERATE);
                    exit;
                }
                if($result['store_type']!=4){
                    output_error('账号或密码错误', array(), ERROR_CODE_OPERATE);
                }

                $store_name = $result['store_name'];
                $store_id   = $result['store_id'];
                $store_type = $result['store_type'];
                $admin_id   = $result['member_id']; //店铺管理员的id 返回该用户id的会员信息给用户

                //管理员的账号信息
                $member_admin =$model_member->where(array('member_id'=>$admin_id))->field('member_name,member_points,member_id,invitation')->find();

                // 登录成功后，处理下registration_id (设备id改到token表)
//                if (isset($_REQUEST['registration_id']) && !empty($_REQUEST['registration_id'])) {
//                    $model_seller->seller_set_registration_id($array['member_id'], $_REQUEST['registration_id']);
//                }
                $token = $this->_get_token($member_info['member_id'] ,$member_info['member_name'], $client);
                if ($token) {
                    $arr = array(
                        'shop_id'   => $store_id,
                        'invitation'=>$member_admin['invitation'],
                        //'username'  => $member_admin['member_name'],
                        'shop_admin_id'  => $admin_id,                  // 管理用户id
                        'member_id' => $member_info['member_id'],  //当前登录者的id
                        'store_name' => $store_name,
                        'store_type'       => $store_type,
                        //'is_agree'  => $is_agree,
                        'key'       => $token
                    );
                    output_data($arr);
                } else {
                    output_error('生成token失败', array(), ERROR_CODE_OPERATE);
                }
            } else {
                output_error('账号或密码不正确', array(), ERROR_CODE_OPERATE);
            }

        }
    }


    /**
     * 保存key值
     * @param  :member_id member_name client
     * @return : ture
     * @author :xuping
     * @tool   :phpstom
     */
    private function _get_token($member_id, $member_name, $client) {
        $model_mb_user_token = Model('mb_seller_token');
        $registration_id=isset($_REQUEST['registration_id'])?$_REQUEST['registration_id']:'';
        //重新登录后以前的令牌失效
        //暂时停用
        $condition = array();
        //$condition['member_id'] = $member_id;
        $condition['registration_id']=$registration_id;
        $condition['client_type'] = $client;
        $model_mb_user_token->delMbUserToken($condition);

        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0, 999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;
        $mb_user_token_info['registration_id'] = $registration_id;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if ($result) {
            return $token;
        } else {
            return null;
        }
    }

    /**
     * 取密保问题
     * @param string $param['mobile']       手机
     */
    public function get_member_security_questionOp()
    {
        if (empty($_REQUEST['mobile']) || empty($_REQUEST['mobile'])) {
            output_error('请输入手机号');
        }

        $model_member = Model('member');

        $condition['member_mobile'] = intval($_REQUEST['mobile']);
        $member_info = $model_member->getMemberInfo($condition);

        if(!$member_info){
            output_error('无此用户');
        }

        $result = $model_member->getMemberSecurityQuestion(array('member_id'=>$member_info['member_id']),'question_1,question_2,question_3');
        if($result){
            output_data_msg(array('security_question' => $result));
        }
        output_error('未设置密保问题');
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
//                $easemob = new Easemob();
//                $rt =$easemob->changePwdToken($_REQUEST['mobile'],$update_reset['member_passwd'],$member_info['member_passwd']);
                $status = $model_member->editMember(array('member_id' => $member_info['member_id']), $update_reset);
                if (!$status) {
                    output_error('密码重置失败');
                }

                $model_mb_seller_token = Model('mb_seller_token');
                $model_mb_seller_token->delMbUserToken(array('member_name'=>$member_info['member_name']));
                output_data('密码重置成功');
            }
        }
        output_error('回答错误');
    }

    /**
     * 发送邮件验证码
     * @param string $param['mail']       邮箱
     */
    public function send_mail_codeOp()
    {
        if (!isset($_REQUEST['mail']) || $_REQUEST['mail'] == '') {
            output_error('请设置邮箱');
        }

        $req_mail = $_REQUEST['mail'];

        $member_model = Model('member');

        $member_info = $member_model->getMemberInfo(array('member_email'=>$req_mail));

        if(empty($member_info)){
            output_error('邮箱错误');
        }

        if(intval($member_info['member_email_bind'])!=1){
            output_error('邮箱未绑定');
        }

        $verify_code = rand(100,999).rand(100,999);

        $site_title = C('site_name');
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();
        $obj_email = new Email();
        $obj_email->set('email_server',$list_setting['email_host']);
        $obj_email->set('email_port',$list_setting['email_port']);
        $obj_email->set('email_user',$list_setting['email_id']);
        $obj_email->set('email_password',$list_setting['email_pass']);
        $obj_email->set('email_from',$list_setting['email_addr']);
        $obj_email->set('site_name',$site_title);

        $subject = '跑腿邦安全邮箱验证';
//        $act_url = WAP_SITE_URL.'/mailbox.html?email='.$req_mail.'&vertify_code='.$verify_code;
        $message = '<p>Hi,'.$member_info['member_name'].'</p>';
        $message .= '<p>验证码：'.$verify_code.'</p>';
        $message .= '<p>如果您没有操作，请忽略此邮件。</p>';
        $message .= '<p><img src="http://shop.aigegou.com/agg/wap/aidatui/img/logo.png"></p>';
        $result = $obj_email->send($req_mail,$subject,$message);
        if($result){
            // 设置验证码缓存
            $cache_path =  'cache/sms_code/';
            $cache_result = set_file_cache(str_replace('.','_',$_REQUEST['mail']), $verify_code, $cache_path, 3600,1);    //将$data数组生成到setting文件缓存
            if (!$cache_result) {
                output_error('发送失败', array(), ERROR_CODE_DATABASE);
            }
            $result = Model('member')->editMember(array('member_id'=>$member_id),array('member_email'=>$req_mail));
            if($result) {
                output_data_msg('发送成功，请打开邮箱查收');
            }
        }
        output_error('发送失败');
    }

    /**
     * 验证邮箱验证码
     * @param string $param['mail']            邮件
     * @param string $param['vertify_code']    邮件验证码
     */
    public function ck_mail_codeOp()
    {
        if(!isset($_REQUEST['mail']) || $_REQUEST['mail'] == '') {
            output_error('请设置mail');
        }

        if(!isset($_REQUEST['vertify_code']) || $_REQUEST['vertify_code'] == '') {
            output_error('请设置验证码');
        }

        $model_member = Model('member');

        $condition['member_email'] = $_REQUEST['mail'];

        $member_info = $model_member->getMemberInfo($condition);

        if (empty($member_info)) {
            output_error('不存在该邮件');
        }

        if($member_info['member_email']) {
            $this->_valid_field_vertify_code(str_replace('.', '_', $member_info['member_email']));
        }

        if(intval($member_info['member_email_bind'])==1){
            output_data_msg('验证成功');
        }
        output_error('邮箱未绑定');
    }

    /**
     * 通过邮件验证码修改密码
     * @param string $param['mail']            邮件
     * @param string $param['vertify_code']    邮件验证码
     * @param string $param['password']        新密码
     */
    public function mail_reset_passwordOp()
    {
        if(!isset($_REQUEST['mail']) || $_REQUEST['mail'] == '') {
            output_error('请设置mail');
        }

        if(!isset($_REQUEST['vertify_code']) || $_REQUEST['vertify_code'] == '') {
            output_error('请设置验证码');
        }

        if (!isset($_REQUEST['password']) || empty($_REQUEST['password'])) {
            output_error('密码不能为空');
        }

        if (!preg_match('/^\w{6,15}$/', $_REQUEST['password'])) {
            output_error('密码长度必须保持在6-15位之间');
        }

        $model_member = Model('member');

        $condition['member_email'] = $_REQUEST['mail'];

        $member_info = $model_member->getMemberInfo($condition);

        if (empty($member_info)) {
            output_error('不存在该邮件');
        }

        if($member_info['member_email']) {
            $this->_valid_field_vertify_code(str_replace('.', '_', $member_info['member_email']));
        }

        if(intval($member_info['member_email_bind'])==1){
            $update_reset = array('member_passwd' => md6($_REQUEST['password'], $member_info['member_salt']));
            //更改环信密码
//                $easemob = new Easemob();
//                $rt =$easemob->changePwdToken($_REQUEST['mobile'],$update_reset['member_passwd'],$member_info['member_passwd']);
            $status = $model_member->editMember(array('member_id' => $member_info['member_id']), $update_reset);
            if (!$status) {
                output_error('密码重置失败');
            }

            $model_mb_seller_token = Model('mb_seller_token');
            $model_mb_seller_token->delMbUserToken(array('member_name'=>$member_info['member_name']));
            output_data_msg('密码重置成功');
        }
        output_error('邮箱未绑定');
    }

    /**
     * 通过用户名取绑定信息
     *
     * @param string $param['username']
     */
    public function name_get_secdetailOp() {

        if (empty($_REQUEST['username'])) {
            output_error('账号为空', array(), ERROR_CODE_OPERATE);
        } else {
            $model_seller = Model('seller');
            $res = $model_seller->where(array('seller_name' => $_REQUEST['username']))->find();
            if (!empty($res)) {
                //验证商户信息
                $model_member = Model('member');
                $array['member_id'] = $res['member_id'];
                $member_info = $model_member->getMemberInfo($array);

                // 用户名未查到，进一步查询手机号码
                if (empty($member_info)) {
                    $mobile_condition['member_mobile'] = $_REQUEST['username'];
                    $member_info = $model_member->getMemberInfo($mobile_condition);
                    if (empty($member_info)) {
                        output_error('用户名错误', array(), ERROR_CODE_OPERATE);
                    }
                }

                if ($member_info['member_state'] == 0) {
                    output_error('账户被停用', array(), ERROR_CODE_OPERATE);
                }
                $member_mobile = $member_info['member_mobile']?$member_info['member_mobile']:'';

                //绑定信息返回
                $security_statue_arr = array(
                    'question_bind'=>0,
                    'mail_bind'=>0,
                    'email'=>'',
                    'member_name'=>'',
                    'member_mobile'=>$member_mobile
                );

                $security_statue_arr['member_name'] = $member_info['member_name'];
                $security_statue_arr['mail_bind'] = $member_info['member_email_bind']==1?1:0;
                if($member_info['member_email_bind']==1){
                    $security_statue_arr['email'] = $member_info['member_email']?$member_info['member_email']:'';
                }
                $result = $model_member->getMemberSecurityQuestion(array('member_id'=>$member_info['member_id']),'answer_1,answer_2,answer_3');

                if($result['answer_1'] && $result['answer_2'] && $result['answer_3']){
                    $security_statue_arr['question_bind'] = 1;
                }

                output_data_msg($security_statue_arr);

            }
            output_error('无此商户');
        }
    }

    /**
     * 重置密码第1步
     * 
     * @param string $param['mobile']
     * @param string $param['vertify_code']
     */
     public function adt_verify_code_repwdOp() {
      
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
    public function adt_reset_passwordOp(){
        
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
                output_error('账号或密码错误');
            }
        }
       
        
        $update_reset = array('member_passwd' => md6($_REQUEST['password'], $member_info['member_salt']));
        $status = $model_member->editMember(array('member_id' => $member_info['member_id']), $update_reset);
        if (!$status) {
           output_error('密码重置失败');
        } 
        
        $model_mb_seller_token = Model('mb_seller_token');
        $model_mb_seller_token->delMbUserToken($condition);
        output_data('密码重置成功');
    }
     
    
    /**
     * 发送手机验证码
     * 
     * @param string $param['mobile'] 手机号码
     */
    public function adt_send_verify_codeOp() {
        $this->_valid_mobile();

        $verify_code = rand(100,999).rand(100,999);
        $mobile  = $_REQUEST['mobile'];
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
        if ($sms_type == 0) {
            $result = $sms->send($mobile, MOBILE_AUTH_CODE_TEMPLATE, $param_sms);
            if (!$result) {
                output_error('发送失败,请稍后再试', array(), ERROR_CODE_OPERATE);
            }
        }
        else {
            $result = $sms->yp_send($mobile, $verify_code);
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