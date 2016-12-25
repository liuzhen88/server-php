<?php
/**
 * 预存款充值卡
 *

 * @link       QQ交流群：423352945
 * @since      File available since JD Release v3.0
 */
defined('emall') or exit('Access Invalid!');
class pd_gift_cardControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('pd_gift_card');
	}
	/*
	 *充值卡管理
	 */

	public function indexOp(){
		$model_card = Model('pd_gift_card');
		$param = array();
		//面值
		if(trim($_GET['card_value'])){
			$param['card_value'] = $_GET['card_value'];
		}
		//充值卡卡号
		if(trim($_GET['card_id'])){
			$param['card_id'] = $_GET['card_id'];
		}
		//是否使用
		if(empty($_GET['is_use'])){
		}else{
			$param['is_use'] = trim($_GET['is_use']);
		}
		//使用者用户名搜索
		if(trim($_GET['use_name'])){
			$param['member_name'] = $_GET['use_name'];
		}
		//有效期
		$if_start_time = time();
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['valid_date']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['valid_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $param['valid_date'] = array('time',array($start_unixtime,$end_unixtime));
        }
		//生产批次
		if(trim($_GET['batch'])){
			$param['batch'] = $_GET['batch'];
		}
		
		$card_list = $model_card->table('pd_gift_card')->where($param)->order('add_time desc')->page(10)->select();
		$card_count = $model_card->table('pd_gift_card')->where($param)->count();
		$card_batch = count($model_card->table('pd_gift_card')->where($param)->field('batch')->distinct(true)->select());

		Tpl::output('card_list',$card_list);
		Tpl::output('card_count',$card_count);
        Tpl::output('card_batch',$card_batch);
		Tpl::output('show_page',$model_card->showpage());
		Tpl::showpage('pd_gift_card_index');
	}

	/*
	 *生成充值卡页面
	 */
	public function add_cardOp(){
		Tpl::showpage('pd_gift_card_add');
	}

	/*
	 *生成充值卡操作
	 */
	public function create_gift_cardOp(){
		$card_value = $_POST['value'];
		$card_num = $_POST['number'];
		$valid_date = $_POST['valid_date'];
		$batch = $_POST['batch'];

		if($card_value==0 || empty($card_value)) {
			showDialog('您输入的充值卡面值有误');
		}
		if($card_num==0 || empty($card_num)) {
			showDialog('您输入的充值卡数量有误');
		}
		if(empty($valid_date)) {
			showDialog('请选择充值卡的有效期');
		}
		$model_card = Model('pd_gift_card');

		for($i=0;$i<$card_num;$i++){
			//生成充值卡
			$param = array();
			$param['card_id'] = rand(1000000000,9999999999);
			$param['card_password'] = $this->randomkeys(8);
			$param['card_value'] = $card_value;
			$param['add_time'] = time();
			$param['is_use'] = 0;
			$param['valid_date'] = strtotime($valid_date)+86399;
            $param['batch'] = $batch;
			$result = $model_card->addCard($param);
		}

		if($result){
			showDialog('生成充值卡成功','index.php?act=pd_gift_card&op=index','succ','');
		}else{
			showDialog('生成充值卡失败','index.php?act=pd_gift_card&op=add_card','error','');
		}
	}

	/*
	 *生成充值卡密钥
	 */
	function randomkeys($length) {
		$returnStr='';
		$pattern = 'ab1CD2ef3GH4ijk5LMN6gp7qrs8TUVW9sy0z';
		for($i = 0; $i < $length; $i ++) {
		$returnStr .= $pattern {mt_rand (0, 35)}; //生成php随机数
		}
		return $returnStr;
	}

}