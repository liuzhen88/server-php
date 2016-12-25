<?php
/**
 * 用户中心系统消息
 *
 */
defined('emall') or exit('Access Invalid!');

class member_messageControl extends mobileMemberControl {

	public function __construct(){
		parent::__construct();
	}

	/**
	 * 获取新消息数量
	 */
	public function get_new_message_countOp() {
		$message_model = Model('message');
		$countnum = $message_model->countNewMessage($this->member_info['member_id']);
		output_data($countnum);
	}

	/**
	 * 获取所有消息数量
	 */
	public function get_all_message_countOp() {
		$message_model = Model('message');
		$condition_arr = array();
		$condition_arr['message_type'] = '1';//系统消息
		$condition_arr['to_member_id'] = $this->member_info['member_id'];
		$condition_arr['no_del_member_id'] = $this->member_info['member_id'];
		$countnum = $message_model->countMessage($condition_arr);
		output_data($countnum);
	}


    /**
     * 获取消息列表
     */
	public function get_message_listOp() {
		$model_message	= Model('message');
		$page	= new Page();
		$page->setEachNum(15);
		$message_array	= $model_message->listMessage(array('from_member_id'=>'0','message_type'=>'1','to_member_id'=>$this->member_info['member_id'],'no_del_member_id'=>$this->member_info['member_id']),$page);
		if (!empty($message_array) && is_array($message_array)){
			foreach ($message_array as $k=>$v){
				$v['message_open'] = '0';
				if (!empty($v['read_member_id'])){
					$tmp_readid_arr = explode(',',$v['read_member_id']);
					if (in_array($this->member_info['member_id'],$tmp_readid_arr)){
						$v['message_open'] = '1';
					}
				}
				$v['from_member_name'] = Language::get('home_message_system_message');
				if($v['message_title']=='') {
					$v['message_title'] = '系统消息';
				}
				$message_array[$k]	= $v;
			}
		}
		output_data($message_array);
	}

	/**
	 * 获取消息详情页
	 */
	public function get_message_infoOp(){
		$model_message	= Model('message');
		$message_id = isset($_REQUEST['message_id'])? intval($_REQUEST['message_id']):0;
		if($message_id == 0) {
			output_error('消息ID无效');
		}else{
			$message_array = $model_message->listMessage(array('message_id' => $message_id,'to_member_id' => $this->member_info['member_id']));
			if (!empty($message_array) && is_array($message_array)) {
				foreach ($message_array as $k => $v) {
					$v['message_open'] = '0';
					if (!empty($v['read_member_id'])) {
						$tmp_readid_arr = explode(',', $v['read_member_id']);
						if (in_array($this->member_info['member_id'], $tmp_readid_arr)) {
							$v['message_open'] = '1';
						}
					}
					$v['from_member_name'] = Language::get('home_message_system_message');
					if($v['message_title']=='') {
						$v['message_title'] = '系统消息';
					}
					$message_array[$k] = $v;
				}
			}
			output_data($message_array);
		}
	}

	/**
	 * 删除消息
	 */
	public function del_messageOp(){
		$model_message	= Model('message');
		$message_id = isset($_REQUEST['message_id'])? intval($_REQUEST['message_id']):0;
		if($message_id == 0) {
			output_error('消息ID无效');
		}else{
			$message_array = $model_message->dropBatchMessage(array('message_id' => $message_id,'to_member_id' => $this->member_info['member_id']),$this->member_info['member_id']);
			output_data('删除成功');
		}
	}

	/**
	 * 消息已查看状态更新
	 */
	public function update_message_readOp(){
		$model_message	= Model('message');
		$message_id = isset($_REQUEST['message_id'])? intval($_REQUEST['message_id']):0;
		if($message_id == 0) {
			output_error('消息ID无效');
		}else{
			$message_array = $model_message->updateCommonMessage(array('message_open'=>1,'read_member_id'=>$this->member_info['member_id']),array('message_id' => $message_id,'to_member_id' => $this->member_info['member_id']));
			output_data('已查看标记成功');
		}
	}

	/**
	 * 极光推送消息限制私有接口
	 */
	private function jg_message_limit($type){
		$model_member_common	= Model('member_common');
		$member_info_array = $model_member_common->getMemberCommonInfo(array('member_id' =>$this->member_info['member_id']));
		$jg_limit_array = explode(',',$member_info_array['jg_message_limit']);
		$data = array();
		if (!in_array($type,$jg_limit_array)) {
			if (empty($jg_limit_array[0])) {
				$data['jg_message_limit'] = $type;
			} else {
				$data['jg_message_limit'] = $member_info_array['jg_message_limit'] . ','.$type;
			}
			$model_member_common->editMemberCommon(array('member_id' => $this->member_info['member_id']), $data);
		}
	}

	/**
	 * 极光推送消息限制消费返佣
	 */
	public function jg_message_limit_rebateOp(){
		$this->jg_message_limit('REBATE_RECORD');
		output_data('标注成功');
	}

	/**
	 * 极光推送消息限制分销返佣
	 */
	public function jg_message_limit_distributionOp(){
		$this->jg_message_limit('DISTRIBUTION_RECORD');
		output_data('标注成功');
	}

	/**
	 * 初始化邀请码数据
	 */
	public function inviter_initOp(){
		$result = Model('member')->inviter_init();
		var_dump($result);
	}

}
