<?php

/**
 * 我的商城
 * 
 * @modify lijunhua
 * @since 2015-08-06
 */
use Tpl;

defined('emall') or exit('Access Invalid!');

class member_indexControl extends mobileMemberControl {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 我的商城-个人中心
     */
    public function indexOp() {
        $member_info = array();
        $m = Model('message');
        $member_id                = $this->member_info['member_id'];
        $member_info['member_id'] = $member_id;
        $member_info['wait_read_message'] = $m->countNewMessage($member_id); //获取未读消息
        $member_info['user_name'] = $this->member_info['member_name'];
        $member_info['nick_name'] = $this->member_info['member_truename'];
        $member_info['avator']    = getMemberAvatar($this->member_info['member_avatar']);
        $member_info['avator_cut']    = $member_info['avator'].'-toppic';
        $member_info['is_avator'] = empty($this->member_info['member_avatar']) ? 0 : 1;
        $member_info['point']     = $this->member_info['member_points'];        // 我的积分
        $member_info['predepoit'] = $this->member_info['available_predeposit']; // 预存款
        
        $member_info['total_favorites']       = $this->_count_favorites($member_id);       // 我的收藏(新版本可能统计数不要,为了效率,到时要删除)
        $member_info['total_first_inviter']   = $this->_count_firest_inviter($member_id);  // 直接邀请
        $member_info['total_second_inviter']  = $this->_count_second_inviter($member_id);  // 间接邀请
        $member_info['total_appointment']     = $this->_count_appointment($member_id);     // 我的预约
        
        $member_info['total_sns_friend']      = $this->_count_sns_friend($member_id);      // 我的关注(我关注别人的总数)
        $member_info['total_sns_fans']        = $this->_count_sns_fans($member_id);        // 我的粉丝(别人关注我的总数)
        $member_info['total_circle_theme']    = $this->_count_circle_theme($member_id);    // 我的发现(我的发现的圈子)
    
        $member_info['is_bind_bank_card']     = $this->member_info['bank_card_bind'];   // 是否绑定银行卡
        $member_info['invitation']            = $this->member_info['invitation']; // 邀请码
        
        // 上级邀请人
        $model_member = Model('member');
        $invitor_info = $model_member->getMemberInfo(array('member_id' => $this->member_info['firest_inviter']));
        $member_info['first_invitation']          = empty($invitor_info['invitation']) ? '00000000' : $invitor_info['invitation'];
        $member_info['first_invitation_nickname'] = empty($invitor_info['member_truename']) ? '爱个购' : $invitor_info['member_truename'];

        $member_info['is_distribution']     = $this->member_info['is_distribution'];  // 是否是分销商 是否是分销商 0：不是分销商1：一级分销商；2：二级分销商  
        $member_info['distribution_points'] = price_format($this->member_info['distribution_points']);    // 我的分销总金额

        $result=Model()->table('sns_friend')->where(array('friend_tomid'=>$member_id))->select();
        $friend_count=count($result);  //粉丝数量

        $theme_count=Model()->table('circle_theme')->where(array('member_id'=>$member_id))->select();
        $theme_count=count($theme_count);   //发现数量

        $order_info=Model()->table('order')->where(array('buyer_id'=>$member_id,'delete_state'=>0))->select();
        $oder_count=count($order_info);     //订单数量

        //银行卡数量
        $card_count = Model('member_bank_card')->where(array('member_id'=>$member_id))->count();
        $member_info['bank_count'] = $card_count;

        $member_info['friend_count'] = $friend_count;
        $member_info['theme_count'] = $theme_count;
        $member_info['oder_count'] = $oder_count;
        $member_common = $model_member->get_member_common(array('member_id'=>$member_info['member_id']));
        $member_info['sex']         =  (int)$this->member_info['member_sex'];
        $member_info['paypwd_status']= ($this->member_info['member_paypwd']?1:0);
        $member_info['real_name']   =  ($member_common['member_realname']?1:0);
        output_data(array('member_info' => $member_info)); 
        
    }
    
    
    /**
     * 修改昵称
     * 
     * @author lijunhua
     * @since  2015-08-11
     * 
     * @param string $param['nick_name']  昵称
     */
     public function update_nicknameOp()
     {
         if (!isset($_REQUEST['nick_name']) || empty($_REQUEST['nick_name'])) {
             output_error('昵称不能为空', array(), ERROR_CODE_ARG);
         }
         
         if (cn_strlen($_REQUEST['nick_name']) < 2 ) {
              output_error('昵称过短,只允许2-16个字符长度');
         }
         
         if (cn_strlen($_REQUEST['nick_name']) > 15 ) {
              output_error('昵称过长,只允许2-15个字符长度');
         }         
         
         $data = array('member_truename' => $_REQUEST['nick_name']);
         $result = Model('member')->editMember(array('member_id' => $this->member_info['member_id']), $data);
         
         if (!$result) {
             output_error('操作失败');
         } 
         
         output_data(array());
     }
     
    /**
     * 重置交易密码第2步(手机号码方式重置)
     * 
     * @param  string $param['mobile']        手机号（手机号有可能是用户名）
     * @param  string $param['password']      密码
     * @param  string $param['vertify_code']  手机验证码
     */
    public function reset_paypwdOp(){
        
        $this->_valid_mobile();
        if (!isset($_REQUEST['vertify_code']) || empty($_REQUEST['vertify_code'])) {
            output_error('手机验证码不能为空', array(), ERROR_CODE_ARG);
        }
        $this->_valid_field_vertify_code($_REQUEST['mobile']);

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

        $update_reset = array('member_paypwd' => md6($_REQUEST['password'], $member_info['member_salt']));
        $status = $model_member->editMember(array('member_id' => $member_info['member_id']), $update_reset);
        if (!$status) {
           output_error('保存失败');
        } 
        
        //token失效
        $condition['member_id']    = $this->member_info['member_id'];
        $condition['client_type'] = $_REQUEST['client_type'];
        $model_mb_user_token->delMbUserToken($condition);     
        
        output_data(array());
    }
    
    /**
     * 上传头像（后期要更换上传方法）
     * 
     * @author lijunhua
     * @since  2015-08-11
     * 
     * @param string $_FILES['member_avatar']  图片
     * @param flag=is_wap_send 不必须传
     */
      public function upload_picOp() {
        $field = 'member_avatar';
        $member_id = $this->member_info['member_id'];
        $upload = new UploadFile();

        $dir = ATTACH_AVATAR . DS;
        
        $time = time();
        $upload->set('default_dir', $dir);
        $upload->set('file_name', 'avatar_' . $member_id . '_' . $time  . '.' . $this->_get_ext($field));

        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));  
        $result = $upload->upfile($field);
     
        if (!$result) {
            output_error('上传失败', array(), ERROR_CODE_SYSTEM);
        }
        $member_avatar = $this->member_info['member_avatar'];
        $data = array('member_avatar' => $upload->file_name);
        $result = Model('member')->editMember(array('member_id' => $member_id), $data);
        if (!$result) {
            output_error('上传失败', array(), ERROR_CODE_SYSTEM);
        }
        dcache($member_id,'member');
        $upload->delFileQny($dir . $member_avatar);
        
        $imgage_url = UPLOAD_SITE_URL . DS . $dir . $upload->file_name;
        if(isset($_REQUEST['flag']) && $_REQUEST['flag'] == 'is_wap_send') {
            header('Location: /wapdev/personal_center.html');
        }
        output_data(array('avatar' => $imgage_url));
    }     

    /**
     * 升级为二级分销商
     */
    public function upgrade_distributionOp(){
         $member_id = $this->member_info['member_id'];
         
         if ($this->member_info['is_distribution']  == 2) {
             output_error('已是二级分销商');
         }
         
         if ($this->member_info['is_distribution'] == 1) {
             output_error('已是一级分销商');
         }
        
         $data = array('is_distribution' => 2);
         $result = Model('member')->editMember(array('member_id' => $member_id), $data);
         if (!$result) {
             output_error('操作失败');
         }
         output_data(array('message' => '操作成功'));
    }

      
    /*-----------------------------以下是私有方法---------------------------*/

   private function _get_ext($field)
   {
       $tmp_ext = explode(".", $_FILES[$field]['name']);
       $tmp_ext = $tmp_ext[count($tmp_ext) - 1];
       return  strtolower($tmp_ext);
   }
    
    /**
     * 我的收藏总数(收藏商铺 + 收藏产品总和)
     */
    private function _count_favorites($member_id) {
        return Model('favorites')->getFavoritesCount(array('member_id' => $member_id, 'is_online' => 1));
    }
    
    /**
     * 我的关注(我关注别人的总数)
     * 
     * @param int $member_id
     * @return array
     */
    private function _count_sns_friend($member_id) {
        return Model('sns_friend')->countFriend(array('friend_frommid' => $member_id));
    }
    
    /**
     * 我的粉丝(别人关注我的总数)
     */
    private function _count_sns_fans($member_id) {
       return Model('sns_friend')->countFriend(array('friend_tomid' => $member_id));
    }
    
    /**
     * 我的发现(我的发现的圈子)
     */
    private function _count_circle_theme($member_id) {
        return Model('circle_theme')->getCircleThemeCount(array('member_id' => $member_id));
    }
    
    /**
     * 一级邀请
     * 
     * @param int $member_id
     * @return array
     */
    private function _count_firest_inviter($member_id) {
        return Model('member')->getMemberCount(array('firest_inviter' => $member_id));
    }
    /**
     * 二级邀请
     * 
     * @param int $member_id
     * @return array
     */
    private function _count_second_inviter($member_id) {
        return Model('member')->getMemberCount(array('second_inviter' => $member_id));
    }    
    
    /**
     * 我的预约
     * 
     * @param int $member_id
     * @return array
     */
    private function _count_appointment($member_id) {
        return Model('good_reserves')->getGoodReservesCount(array('member_id' => $member_id, 'status' => 0));
    }
    
    /**
     * 验证手机验证码
     * 
     * @param string $mobile
     */
    private function _valid_field_vertify_code($mobile)
    {
        $valid_code = get_file_cache($mobile, 'cache/sms_code');
        if(empty($valid_code)) {
              output_error('验证码已过期,请重新发送');
        }
        
        if ($valid_code != $_REQUEST['vertify_code']) {
             output_error('验证码错误');
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


    public function set_user_registrationidOP(){
        $registrationid=$_REQUEST['registration_id'];
        if (isset($registrationid) && !empty($registrationid)) {
            $model_member= Model('member');
            $result=$model_member->set_registration_id($this->member_info['member_id'],$registrationid);
        }
        if($result){
            output_data_msg('ok');
        }else{
            output_error('失败');
        }
    }




}
