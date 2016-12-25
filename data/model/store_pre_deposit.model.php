<?php
/**
 * 本土商户预存款
 */
defined('emall') or exit('Access Invalid!');
class store_pre_depositModel extends Model {
    public function __construct() {
        parent::__construct('store_pre_deposit');
    }
    
    /**
     * 生成充值编号
     * @return string
     */
    public function makeSn($store_id) {
        return mt_rand(10,99)
        . sprintf('%010d',time() - 946656000)
        . sprintf('%03d', (float) microtime() * 1000)
        . sprintf('%03d', (int) $store_id % 1000);
    }
    
    /**
     * 列表
     * @param array $condition
     * @param string $field
     * @param int $page
     * @return array
     */
    public function getStorePredepositList($condition, $field = '*', $page = 0, $order='id desc') {
        return $this->field($field)->where($condition)->order($order)->page($page)->select();
    }
    
    /**
     * 详细信息
     * @param array $condition
     * @return array
     */
    public function getStorePredepositInfo($condition) {
        return $this->where($condition)->find();
    }
    
    /**
     * 获取充值总额
     */
    public function getStorePredepositCount($condition, $master = false){
        return $this->field('SUM(amount) AS amount_total')->where($condition)->master($master)->find();
    }
    
}