<?php
/**
 * 前台登录 退出操作(此接口后期删除)
 * 
 * @author lijunhua
 * @singce 2015-08-04
 */

use Tpl;

defined('emall') or exit('Access Invalid!');

class loginControl extends mobileHomeControl {

	public function __construct(){
            output_error('此功能接口将作废', array(), ERROR_CODE_OPERATE);
	    parent::__construct();
	}

        /**
         * 登录
         * 
         * @param  string $param['username']    用户名
         * @param  string $param['password']    密码
         * @param  string $param['client_type'] 终端方式 默认WAP
         */
	public function indexOp() {

            if(empty($_REQUEST['username']) || empty($_REQUEST['password']) ){
                 output_error('账号或密码为空', array(), ERROR_CODE_OPERATE);
            }


            $agent = isset($_REQUEST['client_type']) ? $_REQUEST['client_type'] : 'wap';
            $model_member = Model('member');

            $array = array();
            $array['member_name']	= $_REQUEST['username'];
            $array['member_passwd']	= md5($_REQUEST['password']);
            $member_info = $model_member->getMemberInfo($array);

            if (empty($member_info)) {
                 output_error('登录或密码错误', array(), ERROR_CODE_OPERATE);
            }


            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $agent);
            if(!$token) {
                 output_error('登录失败', array(), ERROR_CODE_AUTH);
            }

            $result = array(
                'token'             => $token ,
                'user_id'           => $member_info['member_id'],
                'username'          => $member_info['member_name'],
                'member_avatar'     => $member_info['member_avatar'],
                'member_avatar_url' => getMemberAvatarForID($member_info['member_id']),
            );
            output_data($result);


    }

    /**
     * 会员注册
     * 
     * @param   string $param['username'] 用户名
     * @param   string $param['password'] 密码
     * @param   string $param['password_confirm'] 确认密码
     * @param   string $param['email'] 邮箱
     * @param  string $param['client_type'] 终端方式 默认WAP
     */
    public function registerOp() {
        
        $this->_valid_field_register();
        $model_member = Model('member');

        $register_info = array();
        $register_info['username']         = $_REQUEST['username'];
        $register_info['password']         = $_REQUEST['password'];
        $register_info['password_confirm'] = $_REQUEST['password_confirm'];
        $register_info['email']            = $_REQUEST['email'];
        $register_info['firest_inviter']   = 0;
	$register_info['second_inviter']   = 0;
        $member_info = $model_member->register($register_info);
        if (isset($member_info['error'])) {
             output_error($member_info['error']);
        }
        
        $client_type = isset($_REQUEST['client_type']) ? $_REQUEST['client_type'] : 'wap';
        
        $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $client_type);
        if ($token) {
            output_data(array('username' => $member_info['member_name'], 'key' => $token));
        } else {
            output_error('注册失败');
        }

    }
    
    /**
     *  密码重置
     * 
     * @modify   lijunhua 2015-08-04
     * 
     * @param    string $param['human_account']  用户名
     * @param    string $param['human_password'] 密码
     * @name     重置密码
     */
    public function resetPasswordOp(){
        $model_reset    =   Model('member');
        //$resetpassword['member_id'] =   (isset($_REQUEST['id']) ? $_REQUEST['id'] : "")
        $resetpassword['member_name']  =   $_REQUEST['human_account'];
        $resetpassword['member_passwd']  =   md5($_REQUEST['human_password']);
        $result=$model_reset->member_reisset($resetpassword);
        if(!$result) {
             output_error('不存在用户');
        }
        
        $update_reset=array('member_passwd' => md5('123456'));
        $status=$model_reset->reset_update($resetpassword,$update_reset);
        if ($status==true){
            output_data(array('info' => '密码重置成功'));
        }else{
            output_error('重置失败');
        }
        output_data(array('info' => '用户存在'));
 

    }

    /*-------------------以下是私有方法-------------------*/
    /**
     * 登录生成token
     */
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
    
    
    private function _valid_field_register(){
        
        $this->_valid_field_login();
        if (!isset($_REQUEST['password_confirm']) || empty($_REQUEST['password_confirm'])) {
            output_error('密码不能为空', array(), ERROR_CODE_ARG);
        }
        if ($_REQUEST['password'] != $_REQUEST['password_confirm']) {
            output_error('确认密码输入不一致', array(), ERROR_CODE_ARG);
        }
        if (!isset($_REQUEST['email']) || empty($_REQUEST['email'])) {
            output_error('密码不能为空', array(), ERROR_CODE_ARG);
        }
        if (!preg_match('/^([.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\\.[a-zA-Z0-9_-])+/', $_REQUEST['email'])) {
             output_error('邮件格式错误', array(), ERROR_CODE_ARG);
        }
        
    }
    
    private function _valid_field_login()
    {
        if (!isset($_REQUEST['username']) || empty($_REQUEST['username'])) {
            output_error('用户名不能为空', array(), ERROR_CODE_ARG);
        }
        if (!isset($_REQUEST['password']) || empty($_REQUEST['password'])) {
            output_error('密码不能为空', array(), ERROR_CODE_ARG);
        }
    }

}
