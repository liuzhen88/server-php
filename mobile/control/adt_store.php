<?php
defined('emall') or exit('Access Invalid!');
/**
 * 加盟店店铺相关信息控制器
 * @author Administrator
 *
 */
class adt_storeControl extends BaseStoreLeagueControl{

    public function __construct() {
        parent::__construct();
    }

    /**
     * 跑腿邦首页
     * 带接单、代发货、配送中数量从agg_order表，根据league_store_id=对应店铺id，order_type=3，order_state对应订单状态查询得出结果
     * @author solon.ring2011@gmail.com
     * @return JSON
     */
    public function homeOp(){
    	$data 				=	array();
    	$model_order		=	Model('order');
    	$where 				=	$this->_order_state();

    	$data['wait_order']			=	$model_order->getOrderStatePayCount($where);
    	$data['wait_order_send']	=	$model_order->getOrderStateSendCount($where);
  		$data['order_sending']		=	$model_order->getOrderStateSendingCount($where);
  		output_data($data);
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
		$member_security_question['member_id'] = $this->store_info['member_id'];

		$result = $model_member->addMemberSecurityQuestion($member_security_question);
		if($result){
			output_data_msg('添加成功');
		}
		output_error('添加失败');
	}

	/**
	 * 通过密保key修改密保问题
	 * @param string $param['key']       唯一校验码
	 * @param string $param['answer_key']       回答key
	 */
	public function edit_security_question_withkeyOp()
	{
		$this->_valid_security_question_key();

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
		$result = $model_member->editMemberSecurityQuestion(array('member_id' => $this->store_info['member_id']), $member_security_question);
		if ($result) {
			output_data_msg('修改成功');
		}
		output_error('修改失败');

	}

	/**
	 * 通过验证码修改密保问题
	 * @param string $param['key']       唯一校验码
	 * @param string $param['vertify_code']       验证码
	 */
	public function edit_security_question_withvcodeOp()
	{

		$this->_valid_mobilemail_code();

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

		$result = $model_member->editMemberSecurityQuestion(array('member_id' => $this->store_info['member_id']), $member_security_question);
		if ($result) {
			output_data_msg('修改成功');
		}
		output_error('修改失败');
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

		$req_mail = $_REQUEST['mail'];

		$member_id = $this->store_info['member_id'];

		$member_model = Model('member');

		$member_info = $member_model->getMemberInfo(array('member_email'=>$req_mail));

		if(intval($member_info['member_email_bind'])==1){
			if($member_info['member_id']==$member_id){
				output_error('该邮箱已绑定');
			}
			output_error('该邮箱已经被绑定');
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
		$act_url = WAP_SITE_URL.'/mailbox.html?email='.$req_mail.'&vertify_code='.$verify_code;
		$message = '<p>Hi,'.$this->store_info['member_name'].'</p>';
		$message .= '<p>正在尝试绑定邮箱,'.$req_mail.'到你的帐号</p>';
		$message .= '<p>如果这是您的操作，请<a href="'.$act_url.'">点击这里</a>完成邮箱绑定</p>';
		$message .= '<p>如果您没有操作，请忽略此邮件。</p>';
		$message .= '<p>验证码：'.$verify_code.'</p><p><img src="http://shop.aigegou.com/agg/wap/aidatui/img/logo.png"></p>';
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
	 * 绑定邮件
	 * @param string $param['key']                唯一校验码
	 * @param string $param['vertify_code']       验证码
	 */
	public function bind_mailOp()
	{
		$member_id = $this->store_info['member_id'];

		$model_member = Model('member');

		$member_info = $model_member->getMemberInfo(array('member_id'=>$member_id));

		if($member_info['member_email']) {
			$this->_valid_field_vertify_code(str_replace('.', '_', $member_info['member_email']));
		}
		$result = Model('member')->editMember(array('member_id'=>$member_id),array('member_email_bind'=>1));

		if($result) {
			output_data_msg('绑定成功');
		}
		output_error('绑定失败');
	}

	/**
	 * 通过验证码取消绑定邮件
	 * @param string $param['key']                唯一校验码
	 * @param string $param['vertify_code']       验证码
	 */
	public function unbind_mailOp()
	{
		$this->_valid_mobilemail_code();

		$member_id = $this->store_info['member_id'];

		$model_member = Model('member');

		$member_info = $model_member->getMemberInfo(array('member_id'=>$member_id));

		if(intval($member_info['member_email_bind'])==1) {
			$result = Model('member')->editMember(array('member_id' => $member_id), array('member_email_bind' => 0));
		}else{
			output_data_msg('已经解绑');
		}

		if($result) {
			output_data_msg('解绑成功');
		}
		output_error('解绑失败');
	}

	/**
	 * 通过密保问题解绑邮件
	 * @param string $param['key']                唯一校验码
	 * @param string $param['answer_key']         回答key
	 */
	public function answer_key_unbind_mailOp()
	{
		$this->_valid_security_question_key();

		$member_id = $this->store_info['member_id'];

		$model_member = Model('member');

		$member_info = $model_member->getMemberInfo(array('member_id'=>$member_id));

		if(intval($member_info['member_email_bind'])==1) {
			$result = Model('member')->editMember(array('member_id' => $member_id), array('member_email_bind' => 0));
		}else{
			output_data_msg('已经解绑');
		}

		if($result) {
			output_data_msg('解绑成功');
		}
		output_error('解绑失败');
	}

	/**
	 * 登录后取密保问题
	 * @param string $param['key']       唯一校验码
	 */
	public function get_member_security_questionOp()
	{
		$model_member = Model('member');

		$result = $model_member->getMemberSecurityQuestion(array('member_id'=>$this->store_info['member_id']),'question_1,question_2,question_3');
		if($result){
			output_data_msg(array('security_question' => $result));
		}
		output_error('未设置密保问题');
	}

	/**
	 * 帐号安全状态界面
	 * @param string $param['key']       唯一校验码
	 */
	public function security_statOp()
	{
		$member_id = $this->store_info['member_id'];
		$security_statue_arr = array(
			'question_bind'=>0,
			'mail_bind'=>0,
			'email'=>'',
			'member_name'=>''
		);

		$model_member = Model('member');

		$member_info = $model_member->getMemberInfo(array('member_id'=>$member_id));

		if(!$member_info){
			output_error('状态获取失败');
		}

		$security_statue_arr['member_name'] = $member_info['member_name'];
		$security_statue_arr['mail_bind'] = $member_info['member_email_bind']==1?1:0;
		if($member_info['member_email_bind']==1){
			$security_statue_arr['email'] = $member_info['member_email']?$member_info['member_email']:'';
		}
		$result = $model_member->getMemberSecurityQuestion(array('member_id'=>$member_id),'answer_1,answer_2,answer_3');

		if($result['answer_1'] && $result['answer_2'] && $result['answer_3']){
			$security_statue_arr['question_bind'] = 1;
		}

		output_data_msg($security_statue_arr);
	}

    /**
     * 跑腿邦商户端api店铺状态 
     * @param number open_state 1：营业中；2：休息中'
     * agg_store表open_state字段
     */
    public function set_store_statusOp(){
    	$model_store 	=	Model('store');
    	$open_state		=	$_REQUEST['open_state'];
    	if( ! in_array($open_state,array(1,2))) {
    		output_error('参数缺省');
    	}
    	$where 			=	array('store_id'=>$this->seller_info['store_id']);
    	$update 		=	array('open_state'=>$open_state);
    	$rt 	 		=	$model_store->editStore($update, $where);
    	if($rt) {
    		output_data(array());
    	} else {
    		output_error('数据库异常错误',array(),ERROR_CODE_DATABASE);
    	}
    }

	/**
	 * 修改营业时间
	 * @param string ship_time1 开始小时数
	 * @param string ship_time2 结束小时数
	 * agg_store表ship_time字段   '爱大腿商家配送日期设置如8:30-24:30,横线分隔开始和结束时间'
	 */
	public function set_store_ship_timeOp(){
		$model_store 	=	Model('store');
		$ship_time1		=	$_REQUEST['ship_time1'];
		$ship_time2		=	$_REQUEST['ship_time2'];
		if( ! isset($ship_time1) || ! isset($ship_time2)) {
			output_error('参数缺省');
		}
		$where 			=	array('store_id'=>$this->seller_info['store_id']);
		$update 		=	array('ship_time'=>$ship_time1.'-'.$ship_time2);
		$rt 	 		=	$model_store->editStore($update, $where);
		if($rt) {
			output_data(array());
		} else {
			output_error('数据库异常错误',array(),ERROR_CODE_DATABASE);
		}
	}
    /**
     * 我的页面/个人中心
     * @return [type] [description]
     */
    public function my_selfOp(){
    	//$model_store 	=	Model('store');
    	$model_member 	=	Model('member');
    	$data 			=	array();
    	$store_id 		=	$this->seller_info['store_id'];
    	$rt_info 		=	$this->store_info;//$model_store->getStoreInfo(array('store_id'=>$store_id));

    	$data['member_id'] 		=	$rt_info['member_id'];
    	$rt_member 				=	$model_member->getMemberInfo(array('member_id'=>$data['member_id'] ));
    	$data['invitation'] 	=	$rt_member['invitation'];
    	$data['predeposit'] 	=	$rt_member['available_predeposit'];
        $data['qrcode']         =   "https://sp0.baidu.com/5aU_bSa9KgQFm2e88IuM_a/micxp1.duapp.com/qr.php?value=https%3A%2F%2Fopen.weixin.qq.com%2Fconnect%2Foauth2%2Fauthorize%3Fappid%3Dwxa0641282049ed265%26redirect_uri%3Dhttp%253A%252F%252F51aigegou.cn%252Faigegou%252Fagg%252Fagg.html%253Ffatherid%3D".$data['invitation']."%26response_type%3Dcode%26scope%3Dsnsapi_base%26state%3D123%26connect_redirect%3D1%23wechat_redirect";
    	//$rt_member_common 		=	$model_member->getMemberCommonInfo(array('member_id'=>$data['member_id'] ));
    	//$data['member_name']	=	$rt_member_common['member_realname'];

    	$data['store_name'] 	=	$rt_info['store_name'];
    	$data['ship_time']		=	$rt_info['ship_time'];
    	$data['open_state'] 	=	$rt_info['open_state'];
    	$data['open_state_msg'] =	($rt_info['open_state']==1?'营业中':'休息中');
    	$data['store_address']  =	$rt_info['area_info'].$rt_info['store_address'];
    	$data['store_logo']		=	getStoreLogo($rt_info['store_avatar'],'league_store_avatar'); //店铺头像
    	$data['store_phone']	=	(string)$rt_info['store_phone'];
		$data['service_phone']	=	SERVER_PHOHE;
    	output_data($data);
    }

    /**
     * 店铺信息
     * @return [type] [description]
     */
    public function store_infoOp() {
    	$model_member_bank 	= 	Model('member_bank_card');
    	$data 				= 	array();
    	$member_id 			=	$this->store_info['member_id'];
    	$where 				=	array('member_id'=>$member_id);
    	$rt_bank 			= 	$model_member_bank->getMemberBankCardInfo($where);
    	$data['bank_name']	=	(string)$rt_bank['pdc_bank_name'];
    	$data['user_name']	=	(string)$rt_bank['pdc_bank_user'];
    	$data['bank_no']	=	(string)$rt_bank['pdc_bank_no'];
    	$data['mobile']		=	$this->store_info['store_phone'];//(string)$rt_bank['pdc_mobile'];
    	$data['store_address']		=	$this->store_info['area_info'].$this->store_info['store_address'];
    	output_data($data);
    }

    /**
     * 店铺修改手机号
     * @param number $new_mobile 新手机
     * @param number $old_mobile 原手机号
     * @return [type] [description]
     */
    public function change_mobileOp() {
    	$model_store 	=	Model('store');
    	$data 			=	array();
    	$store_id 		=	$this->seller_info['store_id'];
    	$new_mobile 	=	$_REQUEST['new_mobile'];
    	$where 			=	array('store_id'=>$store_id);
    	$update 		=	array('store_phone'=>$new_mobile);
    	$rt 			=	$model_store->editStore($update, $where);
    	if($rt) {
    		output_data($data);
    	} else {
    		output_error('数据库异常错误',array(),ERROR_CODE_DATABASE);
    	}
    }

    /**
     * 修改密码
     * @param string $old_password 原密码
     * @param string $password 新密码
     * @return [type] [description]
     */
    public function adt_change_passwordOp() {
    	$member_id 		=	$this->seller_info['member_id'];
    	$old_password 	=	$_REQUEST['old_password'];
    	$password 		=	$_REQUEST['password'];
        if (!isset($password) || empty($password)) {
            output_error('密码不能为空');
        }
        
        if (!preg_match('/^\w{6,15}$/', $password)) {
            output_error('密码长度必须保持在6-15位之间');
        }    
        
        $model_member = Model('member');


        //$condition['member_name'] = $_REQUEST['mobile'];
        $condition['member_id'] = $member_id;
        $member_info = $model_member->getMemberInfo($condition);
        if(count($member_info) === 0){
        	output_error('此用户不存在');
        }

        if($member_info['member_passwd'] != md6($old_password, $member_info['member_salt'])){
        	output_error('原密码不正确');
        }

        $update_reset = array('member_passwd' => md6($password, $member_info['member_salt']));
        $status = $model_member->editMember(array('member_id' => $member_id), $update_reset);
        if (!$status) {
           output_error('密码重置失败');
        } 
        
        $model_mb_seller_token = Model('mb_seller_token');
        $model_mb_seller_token->delMbUserToken(array('memeber_name'=>$member_info['member_name']));
        output_data('密码重置成功');
    }


    private function _order_state(){
    	return array(
    						'league_store_id'=>$this->seller_info['store_id'],
    						'order_type'=>3
    					);
    }

	/**
	 * @name 爱腿邦 商户体现
	 * @param post ,amount用户名,key,交易密码
	 * @author xuping
	 * @return 200为成功返回,其他的是失败
	 */
	public function adt_with_drawOp() {
		$pdc_amount = abs(floatval($_REQUEST['amount']));
		if ($pdc_amount < 1) {
			output_error('提现金额为大于或者等于1的数字', array(), ERROR_CODE_OPERATE);
		}

		$model_pd = Model('predeposit');
		$member_info = $this->seller_member_info;

		if ($member_info['bank_card_bind'] == 0) {
			output_error('您尚未绑定银行卡');
		}

		$model_bank_card =  Model('member_bank_card');
		$bankcard_info = $model_bank_card->getMemberBankCardInfo(array('member_id' => $member_info['member_id']));

		if (empty($bankcard_info)) {
			output_error('系统未找到任何有银行卡信息', array(), ERROR_CODE_DATABASE);
		}

		try {
			$model_pd->beginTransaction();
			$member_detail = Model('member')->getMemberInfo(array('member_id'=>$this->seller_member_info['member_id']),
				'available_predeposit', true, true);
			$this->seller_member_info['available_predeposit'] = $member_detail['available_predeposit'];
			// 验证金额是否足够
			if (floatval($this->seller_member_info['available_predeposit']) < $pdc_amount){
				output_error('积分金额不足');
			}

			$pdc_sn = $model_pd->makeSn();
			$data = array();
			$data['pdc_sn']            = $pdc_sn;
			$data['pdc_member_id']     = $member_info['member_id'];
			$data['pdc_member_name']   = $member_info['member_name'];
			$data['pdc_amount']        = $pdc_amount;
			$data['pdc_bank_name']     = $bankcard_info['pdc_bank_name'];
			$data['pdc_bank_no']       = $bankcard_info['pdc_bank_no'];
			$data['pdc_bank_user']     = $bankcard_info['pdc_bank_user'];
			$data['pdc_add_time']      = TIMESTAMP;
			$data['pdc_payment_state'] = 0;
			$insert = $model_pd->addPdCash($data);
			if (!$insert) {
				output_error('操作失败', array(), ERROR_CODE_DATABASE);
			}


			//冻结可用预存款
			$data = array();
			$data['member_id']   = $member_info['member_id'];
			$data['member_name'] = $member_info['member_name'];
			$data['amount']      = $pdc_amount;
			$data['order_sn']    = $pdc_sn;

			$model_pd->changePd('cash_apply', $data);
			$model_pd->commit();
			//操作成功
			output_data(array());
		} catch (Exception $e) {
			$model_pd->rollback();
			ouput_error('操作失败', array(), ERROR_CODE_DATABASE);
		}
	}


	/**
	 * @name  爱大腿 获取积分接口
	 * @param post ,key
	 * @return 200为成功返回,其他的是失败
	 */
	public function my_available_predepositOp(){
		$model_member 	=	Model('member');
		$model_member_bank=Model('member_bank_card');
		$data 			=	array();
		$res			=	array();
		$rt_info 		=	$this->store_info;
		$data['member_id'] 		=	$rt_info['member_id'];
		$rt_member 				=	$model_member->getMemberInfo(array('member_id'=>$data['member_id'] ));
		$filed='pdc_bank_name,pdc_bank_no,open_branch,pdc_bank_user';
		$result=$model_member_bank->getMemberBankCardInfo(array('member_id'=>$data['member_id']),
			$filed);
// 		$res		=empty($result)? array() : $result;
		$data['pdc_bank_name']=empty($result['pdc_bank_name']) ? '':$result['pdc_bank_name'];
		$data['pdc_bank_no']  =empty($result['pdc_bank_no']) ? '' :$result['pdc_bank_no'];
		$data['pdc_bank_user']=empty($result['pdc_bank_user']) ? '':$result['pdc_bank_user'];
		$data['open_branch']  =empty($result['open_branch']) ? '':$result['open_branch'];
		$data['predeposit']   =$rt_member['available_predeposit'];
		output_data($data);
	}


	/**
	1.订单付款的记录
	2.商户给用户返利扣除积分的记录
	3.商户作为邀请人，下级人脉消费增加的积分记录
	4.提现记录扣除的积分记录
	 *where(array('lg_member_id'=>$member_id ,'lg_type'=>array('in','recharge,settle_account')))
	 *
	 * @name  爱大腿的商户 获取积分明细
	 * @param post ,username用户名,password密码,client终端类型
	 * @author xuping
	 * @return 200为成功返回,其他的是失败
	 */
	public function adt_available_predepositOP(){
		$page=isset($_REQUEST['curpage']) ? $_REQUEST['curpage'] : 1;
		$size=10;
		$res=intval(($page-1)*$size);

		$member_id= $this->store_info['member_id'];
		$result=Model()->table('pd_log')->field('lg_type,lg_av_amount,
		lg_add_time,lg_desc,lg_freeze_amount')->order('lg_add_time desc')->limit("$res,$size")->where(array('lg_member_id'=>$member_id, 'lg_type'=>array('in',
			'cash_pay,rebate_get,rebate_pay,settle_account')))->select();
		if(!empty($result)){
			foreach($result as $val=>$key){
				if((string)$key['lg_freeze_amount']!='0.00'){
					$result[$val]['lg_av_amount']=$key['lg_freeze_amount'];
				}
			}
			output_data($result);
		}else{
			output_data(array());
		}
	}

	/**
	 * @param post , registrationid
	 * @author xuping
	 */
	public function set_registrationidOP(){
		$registrationid=$_REQUEST['registration_id'];
		if (isset($registrationid) && !empty($registrationid)) {
			$model_member= Model('member');
			$result=$model_member->set_registration_id($this->seller_member_info['member_id'],$registrationid);
		}
		if($result){
			output_data_msg('ok');
		}else{
			output_error('失败');
		}
	}

	/**
	 * 跑腿邦意见反馈功能
	 * @param 图片，image_1,image_2,image_3,image_4..... base64方式上传
	 * @author lixiyu
	 */
	public function adt_feedbackOp(){
		$check_parm=array('content');
		check_request_parameter($check_parm);

		$data['member_id']=$this->store_info['member_id'];
		$data['store_id']=$this->store_info['store_id'];
		$data['title']=$_REQUEST['title'];
		$data['content']=$_REQUEST['content'];
		$data['feed_time']=time();

		//图片上传
		$i=1;
		$image=array();
		$upload = new UploadFile();
		while(true){
			$key='image_'.$i;
			if(!isset($_REQUEST[$key])){
				break;
			}
			$i++;
			$uploaddir = ATTACH_PATH.DS.'adt_feed_back'.DS;
			$upload->set('default_dir',$uploaddir);
			$upload->set('allow_type',array('jpg','jpeg','gif','png'));
			$upload->set('max_size',C('image_max_filesize'));
			$result = $upload->upBase64Image($_REQUEST[$key]);
			if ($result){
				$pic_name = $upload->file_name;
				$upload->file_name = '';
				$image[]=$pic_name;
			}
		}
		$data['image']=implode(',',$image);

		$res=Model('feedback_league')->insert($data);
		if(!$res){
			output_error('反馈失败');
		}else{
			output_data('反馈成功');
		}
	}

	/**
	 * 验证验证码
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

	/**
	 * 验证手机或邮箱验证码
	 *
	 * @param string  $mobile
	 * @param string  $mail
	 */
	protected function _valid_mobilemail_vertify_code($mobile=true,$mail=true)
	{
		$valid_code1 = get_file_cache($mobile, 'cache/sms_code');
		$valid_code2 = get_file_cache($mail, 'cache/sms_code');
		if(empty($valid_code1) && empty($valid_code2)) {
			output_error('验证码已过期,请重新发送');
		}

		if (($valid_code1 != $_REQUEST['vertify_code']) && ($valid_code2 != $_REQUEST['vertify_code'])) {
			output_error('验证码错误');
		}

	}

	/**
	 * 验证密保key
	 *
	 */
	private function _valid_security_question_key()
	{
		if(!isset($_REQUEST['answer_key']) || $_REQUEST['answer_key'] == '') {
			output_error('请设置answer_key');
		}

		$model_member = Model('member');

		$condition['member_id'] = $this->store_info['member_id'];
		$member_info = $model_member->getMemberInfo($condition);

		$result = $model_member->getMemberSecurityQuestion(array('member_id'=>$member_info['member_id']),'answer_1,answer_2,answer_3');
		if($result) {
			$result['member_mobile'] = $member_info['member_mobile'];
			$ask = md5(serialize($result));
			if ($_REQUEST['answer_key'] != $ask) {
				output_error('答案错误');
			}
		}else{
			output_error('没有密保问题');
		}
	}

	/**
	 * 验证手机或邮箱验证码 私有函数
	 *
	 */
	private function _valid_mobilemail_code()
	{
		$member_id = $this->store_info['member_id'];

		$model_member = Model('member');

		$member_info = $model_member->getMemberInfo(array('member_id'=>$member_id));

		if(!empty($member_info['member_email']) || !empty($member_info['member_mobile'])) {
			$this->_valid_mobilemail_vertify_code($member_info['member_mobile'],str_replace('.', '_', $member_info['member_email']));
		}else{
			output_error('用户信息错误');
		}
	}

}