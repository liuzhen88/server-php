<?php

/**
 * 安全设置
 * 
 * @modify lijunhua
 * @since 2015-08-21
 */
use Tpl;

defined('emall') or exit('Access Invalid!');

class member_securityControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 安全设置状态-首页 （后期考虑标*）
     * 
     * @param string $param['key']        唯一校验码
     */
    public function indexOp() {
        $result['is_use_loginpwd'] =  empty($this->member_info['member_passwd']) ? 0 : 1;
        $result['is_use_paypwd']   =  empty($this->member_info['member_paypwd']) ? 0 : 1;
        
        $common_info = $this->_get_common_info();
        
        $result['is_member_realname'] = 0;
        $result['is_member_identity'] = 0;
        
        if (!empty($common_info['member_realname'])) { 
            $result['is_member_realname']  = 1;
            $result['member_realname']     = $common_info['member_realname'];
        }
        
        if (!empty($common_info['member_identity'])) {
//            $result['is_member_identity']  = 1;
            $result['member_identity']     = preg_replace('/^(\d)\d+(\d)$/', '$1********$2', $common_info['member_identity']);
        }
        
        $result['is_bind_bank_card']     = $this->member_info['bank_card_bind']; // 是否绑定银行卡
        $result['is_bind_member_mobile'] = (int)$this->member_info['member_mobile_bind'];
        $result['member_avatar']         = getMemberAvatar($this->member_info['member_avatar']);
        $result['member_truename']       = $this->member_info['member_truename'];
        $result['member_sex']            = $this->member_info['member_sex']; // 性别 0保密 1男 2女 默认0
        $result['member_mobile']         = (string)$this->member_info['member_mobile'];
        
        output_data($result);
    }

    /**
     * 添加密保问题
     * @param string $param['key']       唯一校验码
     */
    public function set_member_security_questionOp()
    {
        if (!isset($_REQUEST['question_1']) || $_REQUEST['question_1'] == '') {
            output_error('请设置问题1');
        }elseif(!isset($_REQUEST['answer_1']) || $_REQUEST['answer_1'] == '') {
            output_error('请设置答案1');
        }elseif(!isset($_REQUEST['question_2']) || $_REQUEST['question_2'] == '') {
            output_error('请设置问题2');
        }elseif(!isset($_REQUEST['answer_2']) || $_REQUEST['answer_2'] == '') {
            output_error('请设置答案2');
        }elseif(!isset($_REQUEST['question_3']) || $_REQUEST['question_3'] == '') {
            output_error('请设置问题3');
        }elseif(!isset($_REQUEST['answer_3']) || $_REQUEST['answer_3'] == '') {
            output_error('请设置答案3');
        }
        $model_member = Model('member');
        $member_security_question = array();
        $member_security_question['question_1'] = trim($_REQUEST['question_1']);
        $member_security_question['question_2'] = trim($_REQUEST['question_2']);
        $member_security_question['question_3'] = trim($_REQUEST['question_3']);
        $member_security_question['answer_1'] = trim($_REQUEST['answer_1']);
        $member_security_question['answer_2'] = trim($_REQUEST['answer_2']);
        $member_security_question['answer_3'] = trim($_REQUEST['answer_3']);
        $member_security_question['member_id'] = $this->member_info['member_id'];

        $result = $model_member->addMemberSecurityQuestion($member_security_question);
        if($result){
            output_data_msg('添加成功');
        }
        output_error('添加失败');
    }

    /**
     * 修改密保问题
     * @param string $param['key']       唯一校验码
     */
    public function edit_member_security_questionOp()
    {
        if (!isset($_REQUEST['question_1']) || $_REQUEST['question_1'] == '') {
            output_error('请设置问题1');
        }elseif(!isset($_REQUEST['answer_1']) || $_REQUEST['answer_1'] == '') {
            output_error('请设置答案1');
        }elseif(!isset($_REQUEST['question_2']) || $_REQUEST['question_2'] == '') {
            output_error('请设置问题2');
        }elseif(!isset($_REQUEST['answer_2']) || $_REQUEST['answer_2'] == '') {
            output_error('请设置答案2');
        }elseif(!isset($_REQUEST['question_3']) || $_REQUEST['question_3'] == '') {
            output_error('请设置问题3');
        }elseif(!isset($_REQUEST['answer_3']) || $_REQUEST['answer_3'] == '') {
            output_error('请设置答案3');
        }
        $model_member = Model('member');
        $member_security_question = array();
        $member_security_question['question_1'] = trim($_REQUEST['question_1']);
        $member_security_question['question_2'] = trim($_REQUEST['question_2']);
        $member_security_question['question_3'] = trim($_REQUEST['question_3']);
        $member_security_question['answer_1'] = trim($_REQUEST['answer_1']);
        $member_security_question['answer_2'] = trim($_REQUEST['answer_2']);
        $member_security_question['answer_3'] = trim($_REQUEST['answer_3']);

        $result = $model_member->editMemberSecurityQuestion(array('member_id'=>$this->member_info['member_id']),$member_security_question);
        if($result){
            output_data_msg('修改成功');
        }
        output_error('修改失败');
    }

    /**
     * 取密保问题
     * @param string $param['key']       唯一校验码
     */
    public function get_member_security_questionOp()
    {
        $model_member = Model('member');

        $result = $model_member->getMemberSecurityQuestion(array('member_id'=>$this->member_info['member_id']),'question_1,question_2,question_3');
        if($result){
            output_data_msg(array('security_question' => $result));
        }
        output_error('未设置密保问题');
    }

    /**
     * 发送绑定邮件
     * @param string $param['key']       唯一校验码
     */
    public function send_bind_mailOp()
    {
        if (!isset($_REQUEST['mail']) || $_REQUEST['mail'] == '') {
            output_error('请设置邮箱');
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

        $subject = '爱个购安全邮箱验证';
        $message = '<p>Hi,'.$this->member_info['member_name'].'</p><p>请<a href="#">点击这里</a>完成邮箱绑定</p><p>验证码：'.$verify_code.'</p><p><img src="http://shop.aigegou.com/agg/wap/aidatui/img/logo.png"></p>';
        $result = $obj_email->send($_REQUEST['mail'],$subject,$message);
        if($result){
            // 设置验证码缓存
            $cache_path =  'cache/sms_code/';
            $cache_result = set_file_cache(str_replace('.','_',$_REQUEST['mail']), $verify_code, $cache_path, 3600,1);    //将$data数组生成到setting文件缓存
            if (!$cache_result) {
                output_error('发送失败', array(), ERROR_CODE_DATABASE);
            }
            $result = Model('member')->editMember(array('member_id'=>$this->member_info['member_id']),array('member_email'=>$_REQUEST['mail']));
            if($result) {
                output_data_msg('发送成功，请打开邮箱查收');
            }
        }
        output_error('发送失败');
    }

    /**
     * 绑定邮件
     * @param string $param['key']       唯一校验码
     */
    public function bind_mailOp()
    {
        $model_member = Model('member');

        $member_info = $model_member->getMemberInfo(array('member_id'=>$this->member_info['member_id']));

        if($member_info['member_email']) {
            $this->_valid_field_vertify_code(str_replace('.', '_', $member_info['member_email']));
        }
        $result = Model('member')->editMember(array('member_id'=>$this->member_info['member_id']),array('member_email_bind'=>1));

        if($result) {
            output_data_msg('绑定成功');
        }
        output_error('绑定失败');
    }

    /**
     * 帐号安全状态界面
     * @param string $param['key']       唯一校验码
     */
    public function security_statOp()
    {
        $security_statue_arr = array(
            'question_bind'=>0,
            'mail_bind'=>0,
            'member_name'=>''
        );

        $model_member = Model('member');

        $member_info = $model_member->getMemberInfo(array('member_id'=>$this->member_info['member_id']));

        if(!$member_info){
            output_error('状态获取失败');
        }

        $security_statue_arr['member_name'] = $member_info['member_name'];
        $security_statue_arr['mail_bind'] = $member_info['member_email_bind']==1?1:0;

        $result = $model_member->getMemberSecurityQuestion(array('member_id'=>$this->member_info['member_id']),'question_1,question_2,question_3');

        if($result['answer_1'] && $result['answer_2'] && $result['answer_3']){
            $security_statue_arr['question_bind'] = 1;
        }

        output_data_msg($security_statue_arr);
    }

    /**
     * 设置性别
     * 
     * @author lijunhua
     * @since  2015-09-19
     * 
     * @param string $param['key']       唯一校验码
     * @param string $param['member_sex'] 性别 保密0  1男 2女 默认2
     */
     public function set_sexOp()
     {
         if (!isset($_REQUEST['member_sex'])) {
             output_error('参数缺少', array(), ERROR_CODE_ARG);
         }
         
         if (!in_array((int)$_REQUEST['member_sex'], array(0, 1, 2))) {
              output_error('参数非法', array(), ERROR_CODE_ARG);
         }
         
         $member_sex = empty($_REQUEST['member_sex']) ? 2 : $_REQUEST['member_sex'];  
         
         $data = array('member_sex' => $member_sex);
         $result = Model('member')->editMember(array('member_id' => $this->member_info['member_id']), $data);
         
         if (!$result) {
             output_error('操作失败');
         } 
         
         output_data(array());
     }

     
    /**
     * 绑定手机号
     * 
     * @author lijunhua
     * @since  2015-09-19
     * 
     * @param string $param['key']       唯一校验码
     * @param string $param['mobile']    手机号码
     */
    public function bind_mobileOp() {

        if (1 == $this->member_info['member_mobile_bind']) {
            output_error('已绑定过手机号');
        }
        
        $this->_valid_mobile();
 
        $model_member   = Model('member');
        $member_mobile  = trim($_REQUEST['mobile']);
        $condition      = array('member_id' => $this->member_info['member_id']);
        
        $update_data                         = array();
        $update_data['member_mobile']        = $member_mobile;
        $update_data['member_mobile_bind']   = 1;
        
        if (empty($this->member_info['member_name'])) {
            $update_data['member_name']        = $member_mobile;
        }
        
        $result = $model_member->editMember($condition, $update_data);
        if (!$result) {
             output_error('设置失败');
        }
        
        output_data(array());
        
    }
     
    /**
     * 设置实名认证（不用显示审核）
     * 
     * @param string $param['key']              唯一校验码
     * @param string $param['member_realname']  真实姓名
     * @param string $param['member_identity']  身份证
     */
    public function set_realnameOp() {
        $this->_valid_realname();
        $this->_valid_identity();
      
        $update_data  = array();
        $update_data['member_realname'] = $_REQUEST['member_realname'];
        $update_data['member_identity']  = $_REQUEST['member_identity'];
        
        $common_info = $this->_get_common_info();
        
        if (!empty($common_info['member_realname']) && !empty($common_info['member_identity'])) {
            output_error('此账号已实名认证', null, ERROR_CODE_OPERATE);
        }
       
        $model_common = Model('member_common');
        $condition    = array('member_id' => $this->member_info['member_id']);
        $is_update = $model_common->editMemberCommon($condition, $update_data);
        if (!$is_update) {
            output_error('设置失败', null, ERROR_CODE_OPERATE);
        }
        
        output_data(array());
    }
    
   
    /**
     * 设置交易密码(已实名认证过且未设置过交易密码)
     * 
     * @param string $param['key']              唯一校验码
     * @param string $param['member_paypwd']    交易密码
     */
    public function set_paypwdOp() {

        $this->_valid_paypwd();
        
        if (!empty($this->member_info['member_paypwd'])) {
            output_error('初始交易密码已存在');
        }
 
        $model_member   = Model('member');
        $member_paypwd  = trim($_REQUEST['member_paypwd']);
        $condition      = array('member_id' => $this->member_info['member_id']);
        $result = $model_member->editMember($condition, array('member_paypwd' => $this->mc_md5($member_paypwd)));
        if (!$result) {
             output_error('设置失败');
        }
        
        output_data(array());
        
    }
    
    /**
     * 修改交易密码(已实名认证过且已设置过交易密码)
     * 
     * @param string $param['key']                唯一校验码
     * @param string $param['member_paypwd']      当前交易密码
     * @param string $param['member_new_paypwd']  新交易密码
     */
    public function modify_paypwdOp() {

        if (!isset($_REQUEST['member_paypwd']) || !isset($_REQUEST['member_new_paypwd'])) {
            output_error('参数错误');
        }
        
        if (empty($_REQUEST['member_paypwd'])) {
            output_error('请输入当前交易密码');
        }
        
        if (empty($_REQUEST['member_new_paypwd'])) {
            output_error('请输入新交易密码');
        }
        
        $member_paypwd     = trim($_REQUEST['member_paypwd']);
        $member_new_paypwd = trim($_REQUEST['member_new_paypwd']);
        
        if (!preg_match('|[^0-9]|U', $member_new_paypwd)) {
            output_error('交易密码不能纯数字', null, ERROR_CODE_OPERATE);
        }
        
        if (!preg_match('/^\w{6,20}$/', $member_new_paypwd)) {
            output_error('新交易密码长度建议在6-20之间', null, ERROR_CODE_OPERATE);
        }
        
        if ($this->mc_md5($member_paypwd) != $this->member_info['member_paypwd']) {
            output_error('当前交易密码错误');
        }
        
        if ($this->mc_md5($member_new_paypwd) == $this->member_info['member_paypwd']) {
            output_error('新密码不能和当前交易密码一致');
        }
        
        $condition    = array('member_id' => $this->member_info['member_id']);
        $model_member = Model('member');
        $result       = $model_member->editMember($condition, array('member_paypwd' => $this->mc_md5($member_new_paypwd)));
        
        if (!$result) {
             output_error('修改失败');
        }
        
        output_data(array());
        
    }

    /**
     * 重置交易密码(身份证方式重置第1步)
     * 
     * @param string $param['key']                唯一校验码
     * @param string $param['member_identity']    认证身份证号
     */
    public function reset_paypwd_by_identityOp() {
        $this->_valid_identity();
        $common_info = $this->_get_common_info();
        if ($_REQUEST['member_identity'] != $common_info['member_identity']) {
            output_error('身份证号输入错误'); 
        }
        
        output_data(array('member_identity' => $common_info['member_identity']));
    }
    
    /**
     * 重置交易密码(重置第2步,支持2种重置方式)
     * 
     * @param  string $param['key']               唯一校验码
     * @param  string $param['member_paypwd']     新交易密码
     * @param  string $param['reset_type']        重置方式： 1 身份证校验  2.手机号码校验（默认）
     * 
     * @param  string $param['member_identity']   认证身份证号(重置方式1必传)
     * 
     * @param  string $param['mobile']            手机号（重置方式2必传）
     * @param  string $param['vertify_code']      手机验证码（重置方式2必传）
     */
    public function reset_paypwd_step2Op(){
        
        $this->_valid_paypwd();
        if ($_REQUEST['reset_type'] == 1) {
            $this->_reset_paypwd_by_identity(); // 身份证号校验
        } else {
            $this->_reset_paypwd_by_mobile(); // 手机号号码校验
        }
 
        $md5_paypwd = $this->mc_md5($_REQUEST['member_paypwd']);
        if ($md5_paypwd == $this->member_info['member_paypwd']) {
            output_error('重置交易密码不能和原密码相同');
        }
        
        $update_reset = array('member_paypwd' => $md5_paypwd);
        $status = Model('member')->editMember(array('member_id' => $this->member_info['member_id']), $update_reset);
        if (!$status) {
           output_error('保存失败');
        } 
        output_data('密码重置成功');
    }
    
    
 
    
    
    /*----------------------以下是私有方法------------------------*/
    
    /**
     * md5密码加密
     * 
     * @param string $pwd
     * @return array
     */
    private function mc_md5($pwd) {
        return md6($pwd, $this->member_info['member_salt']);
    }
    
    /**
     * 手机方式验证交易密码修改
     */
    private function _reset_paypwd_by_mobile() {
         $this->_valid_mobile();
        if (!isset($_REQUEST['vertify_code']) || empty($_REQUEST['vertify_code'])) {
            output_error('手机验证码不能为空', array(), ERROR_CODE_ARG);
        }
        $this->_valid_field_vertify_code($_REQUEST['mobile']);
        
        if (empty($this->member_info['member_mobile'])) {
             output_error('请先设置会员手机号');
        }
        
        if ($_REQUEST['mobile'] != $this->member_info['member_mobile']) {
             output_error('手机号输入错误');
        }
        
    }
    
    /**
     * 身份证验证交易密码修改
     */
    private function _reset_paypwd_by_identity() {
        
        $this->_valid_identity();
        $common_info = $this->_get_common_info();
        if ($_REQUEST['member_identity'] != $common_info['member_identity']) {
            output_error('身份证号输入错误'); 
        }
        
    }

   
    private function  _valid_paypwd() {
        if (!isset($_REQUEST['member_paypwd'])) {
            output_error('参数错误', null, ERROR_CODE_ARG);
        }
        
        if (empty($_REQUEST['member_paypwd'])) {
            output_error('交易密码不能为空', null, ERROR_CODE_ARG);
        }
        
        if (!preg_match('|[^0-9]|U', $_REQUEST['member_paypwd'])) {
            output_error('交易密码不能纯数字', null, ERROR_CODE_OPERATE);
        }
        
        if (!preg_match('/^\w{6,20}$/', $_REQUEST['member_paypwd'])) {
            output_error('交易密码长度建议在6-20之间', null, ERROR_CODE_OPERATE);
        }
        
        if ($_REQUEST['member_paypwd'] == $this->member_info['member_paypwd']) {
           output_error('交易密码不能和登录密码一致', null, ERROR_CODE_OPERATE);
        }
        
    }
    /**
     * 验证真实名称
     */
    private function  _valid_realname() {
        if (!isset($_REQUEST['member_realname'])) {
            output_error('参数错误', null, ERROR_CODE_ARG);
        }
        
        if (empty($_REQUEST['member_realname'])) {
            output_error('真实姓名不能为空', null, ERROR_CODE_ARG);
        }
        
        if (preg_match( '|\w+|U', $_REQUEST['member_realname']) ) { 
            output_error('请输入纯中文名称', null, ERROR_CODE_OPERATE);
        }  
    }
    
    
    /**
     * 验证身份证
     */
    private function _valid_identity() {
        
        if (!isset($_REQUEST['member_identity'])) {
            output_error('参数错误', null, ERROR_CODE_ARG);
        }
        
        if (empty($_REQUEST['member_identity'])) {
            output_error('身份证号不能为空', null, ERROR_CODE_ARG);
        }
        
        if (!is_identity($_REQUEST['member_identity'])) {
            output_error('身份证号格式错误', null, ERROR_CODE_OPERATE);
        }
    }
    
    /**
     * 验证手机号码
     */
    private function _valid_mobile() {
        if (!isset($_REQUEST['mobile']) || empty($_REQUEST['mobile'])){
            output_error('手机号码不能为空', array(), ERROR_CODE_ARG);
        }
        if (!preg_match('/^1\d{10}$/', $_REQUEST['mobile'])){
            output_error('请正确填写手机号码', array(), ERROR_CODE_ARG);
        }
    }  
    
    /**
     * 获取扩展信息
     * 
     * @return array
     */
    private function _get_common_info() {
       return Model('member_common')->getMemberCommonInfo(array('member_id' => $this->member_info['member_id']));
    }
    
    /**
     * 验证验证码过期
     * 
     * @param string  $mobile
     */
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
