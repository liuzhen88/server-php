<?php
/**
 * 代理商模型
 * 
 * @author lijunhua
 * @since 2015-07-25
 */
defined('emall') or exit('Access Invalid!');

class agent_areaModel extends Model {

    public function __construct(){
        parent::__construct('agent_area_hash');
    }

    /**
     * 取得列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     */
    public function getAgentAreaList($condition = array(), $pagesize = '', $limit = '', $order = 'create_time desc,id desc') {
        return $this->where($condition)->order($order)->page($pagesize)->limit($limit)->select();
    }

    /**
     * 获取区域组成一个字符串
     */
    public function getAgentAreaEmploded($condition = array()) {
        return $this->where($condition)->field('group_concat(province) as province,group_concat(city) as city,group_concat(area) as area')->find();
    }

    /**
     * 取得单条信息
     * @param unknown $condition
     */
    public function getAgentAreaInfo($condition = array()) {
        return $this->where($condition)->find();
    }

    /**
     * 删除
     * @param unknown $condition
     */
    public function delAgentArea($condition = array()) {
        return $this->where($condition)->delete();
    }

  
    /**
     * 更新
     * @param unknown $data
     * @param unknown $condition
     */
    public function editAgentArea($data = array(),$condition = array()) {
        return $this->where($condition)->update($data);
    }
    
    
}