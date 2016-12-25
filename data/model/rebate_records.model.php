<?php
/**
 * 返利模型
 * 
 * @author lijunhua
 * @since 2015-09-23
 */
defined('emall') or exit('Access Invalid!');

class rebate_recordsModel extends Model {

    public function __construct(){
        parent::__construct('rebate_records');
    }

    /**
     * 取得列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     * @param string $field
     */
    public function getRebateRecordsList($condition = array(), $pagesize = '', $limit = '', $order = 'id desc', $field = '*') {
        return $this->where($condition)->field($field)->order($order)->page($pagesize)->limit($limit)->select();
    }
    
    /**
     * 按照商家获取返利列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     * @param string $field
     */
    public function getRebateRecordsGroupStore($condition = array(), $pagesize = '', $limit = '', $order = 'total_rabate DESC, id desc', $field = 'sum(rebate) as total_rabate, store_id') {
        return $this->where($condition)->field($field)->group('store_id')->order($order)->page($pagesize)->limit($limit)->select();
    }

    /**
     * 取得单条信息
     * @param unknown $condition
     */
    public function getRebateRecordsInfo($condition = array()) {
        return $this->where($condition)->find();
    }

    /**
     * 删除
     * @param unknown $condition
     */
    public function delRebateRecords($condition = array()) {
        return $this->where($condition)->delete();
    }

    /**
     * 添加
     * 
     * @param array $param
     */
    public function addRebateRecords($param) {
        return $this->insert($param);
    }
    
    
    /**
     * 更新
     * @param unknown $data
     * @param unknown $condition
     */
    public function editRebateRecords($data = array(),$condition = array()) {
        return $this->where($condition)->update($data);
    }
    /**
     * 代理商返利统计-订单
     */
    public function getRebateRecordsStatis($field = 'DATEDIFF(NOW(),FROM_UNIXTIME(add_time)) num,SUM(rebate) rebate',$where = '', $group = '',$order='') {
        return $this->field($field)->where($where)->group($group)->order($order)->select();
    }
    
}