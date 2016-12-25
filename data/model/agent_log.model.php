<?php

/**
 * 代理商日志模型
 * 
 *

 */
defined('emall') or exit('Access Invalid!');

class agent_logModel extends Model {

    public function __construct() {
        parent::__construct('agent_log');
    }

    /**
     * 读取列表 
     * @param array $condition
     *
     */
    public function getAgentLogList($condition, $page = '', $order = '', $field = '*') {
        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
    }

    /**
     * 读取单条记录
     * @param array $condition
     *
     */
    public function getAgentLogInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

    /*
     * 增加 
     * @param array $param
     * @return bool
     */

    public function addAgentLog($param) {
        return $this->insert($param);
    }

    /*
     * 删除
     * @param array $condition
     * @return bool
     */

    public function delAgentLog($condition) {
        return $this->where($condition)->delete();
    }

}
