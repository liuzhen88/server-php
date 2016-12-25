<?php
defined('emall') or exit('Access Invalid!');

class seller_code_pay_logModel extends Model{
    public function __construct(){
        parent::__construct('seller_code_pay_log');
    }
    /**
     * 日志列表
     * @param unknown $condition
     * @param string $field
     * @return unknown
     */
    public function getLogList($condition, $field = '*')
    {
        $rs = $this->field($field)->where($condition)->select();
        return $rs;
    }
    /**
     * 日志数量
     * @param unknown $condition
     * @param string $field
     * @return unknown
     */
    public function getLogCount($condition, $field = '*')
    {
        $rs = $this->field($field)->where($condition)->count();
        return $rs;
    }
    /**
     * 
     * @param unknown $condition
     * @param unknown $update
     */
    public function addPayLog($insert) {
        return $this->insert($insert);
    }
}