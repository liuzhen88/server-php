<?php
/**
 * 代理商模型
 * 
 * @author lijunhua
 * @since 2015-07-25
 */
defined('emall') or exit('Access Invalid!');

class agent_managerModel extends Model {

    public function __construct(){
        parent::__construct('agent_manager');
    }

    /**
     * 取得列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     * @param string $field
     */
    public function getAgentManagerList($condition = array(), $pagesize = '', $limit = '', $order = 'id desc', $field='*') {
        return $this->where($condition)->field($field)->order($order)->page($pagesize)->limit($limit)->select();
    }

    /**
     * 取得单条信息
     * @param unknown $condition
     */
    public function getAgentManagerInfo($condition = array()) {
        return $this->where($condition)->find();
    }

    /**
     * 删除
     * @param unknown $condition
     */
    public function delAgentManager($condition = array()) {
        return $this->where($condition)->delete();
    }

    /**
     * 添加
     * 
     * @param array $param
     */
    public function addAgentManager($param) {
        return $this->insert($param);
    }
    
    
    /**
     * 更新
     * @param unknown $data
     * @param unknown $condition
     */
    public function editAgentManager($data = array(),$condition = array()) {
        return $this->where($condition)->update($data);
    }
    
    
}