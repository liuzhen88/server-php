<?php

/**
 * 会员银行卡模型
 *
 * @author lijunhua
 * @since  2015-08-10
 */
defined('emall') or exit('Access Invalid!');

class member_bank_cardModel extends Model {

    public function __construct() {
        parent::__construct('member_bank_card');
    }

    /**
     * 详细信息（查库）
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMemberBankCardInfo($condition, $field = '*') {
        return $this->field($field)->where($condition)->find();
    }
    /**
     * 银行卡列表（查库）
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMemberBankCardList($condition, $field = '*') {
        return $this->field($field)->where($condition)->select();
    }
    /*
     * 增加 
     * @param array $param
     * @return bool
     */

    public function save($param) {
        return $this->insert($param);
    }

    /*
     * 增加 
     * @param array $param
     * @return bool
     */

    public function saveAll($param) {
        return $this->insertAll($param);
    }

    /*
     * 更新
     * @param array $update
     * @param array $condition
     * @return bool
     */
     public function editMemberBankCard($condition, $data) {
        return $this->where($condition)->update($data);
    }

    /*
     * 删除
     * @param array $param
     * @return bool
     */
    public function delBankCard($param){
        return $this->where($param)->delete();
    }

}
