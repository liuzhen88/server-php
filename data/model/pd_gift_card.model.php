<?php
/**
 * 充值卡模型
 *  *

 * @link       QQ交流群：423352945
 * @since      File available since JD Release v3.0
 */
defined('emall') or exit('Access Invalid!');

class pd_gift_cardModel extends Model{

	public function __construct(){
        parent::__construct('pd_gift_card');
    }

	/**
	 * 查询充值卡
	 *
	 */
	public function infoCard($card_id){
		if (empty($card_id)){
			return false;
		}
		$param = array();
		$param['table'] = 'pd_gift_card';
		$param['where'] = 'card_id='.$card_id;
		$param['limit'] = 1;
		$param['field'] = '*';
		$card_list	= Db::select($param);
		$card_info	= $card_list[0];

		return $card_info;
	}

	/**
	 * 新增充值卡
	 *
	 */
	public function addCard($param){
		if (empty($param)){
			return false;
		}
		$result	= Db::insert('pd_gift_card',$param);
		if($result) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * 更新充值卡使用状态
	 *
	 */
	 public function updateCard($param,$card_id) {
		if(empty($param)) {
			return false;
		}
		$update		= false;
		//得到条件语句
		$where	= 'card_id='.$card_id;
		$update	= Db::update('pd_gift_card',$param,$where);
		return $update;
	}
}