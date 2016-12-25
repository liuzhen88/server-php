<?php
/**
 * 商品预约模型
 * 
 * @author lijunhua
 * @since 2015-08-07
 */
defined('emall') or exit('Access Invalid!');

class good_reservesModel extends Model {

    public function __construct(){
        parent::__construct('good_reserves');
    }

    /**
     * 取得列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     */
    public function getGoodReservesList($condition = array(), $pagesize = '', $limit = '', $order = 'id desc') {
        return $this->where($condition)->order($order)->page($pagesize)->limit($limit)->select();
    }

    /**
     * 取得单条信息
     * @param unknown $condition
     */
    public function getGoodReservesInfo($condition = array()) {
        return $this->where($condition)->find();
    }

    

    /**
     * 删除
     * @param unknown $condition
     */
    public function delGoodReserves($condition = array()) {
        return $this->where($condition)->delete();
    }

    /**
     * 新增
     *
     * @param array $insert 数据
     * @param string $table 表名
     */
    public function addGoodReserves($insert) {
        return $this->insert($insert);
    }
    
    
    /**
     * 编辑
     * @param array $condition
     * @param array $data
     */
    public function editGoodReserves($condition, $data) {
        return $this->where($condition)->update($data);
    }
    
    /**
     * 统计数量
     * @param array $condition
     * @param array $data
     */
    public function getGoodReservesCount($condition) {
        return $this->where($condition)->count();
    }
}