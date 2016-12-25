<?php
/**
 * 本土分销商品表
 * 
 * @author lijunhua
 * @since  2015-09-14
 */
defined('emall') or exit('Access Invalid!');

class goods_distributionModel extends Model {

    public function __construct() {
        parent::__construct('goods_distribution');
    }

    /**
     * 查询列表
     * @param array $condition
     */
    public function getGoodDistributionList($condition, $field='*') {
        return $this->table('goods_distribution')->where($condition)->field($field)->select();
    }
    
    
    /**
     * 查询
     *
     * @param array $condition 查询条件
     * @return array
     */
    public function getGoodDistributionInfo($condition) {
        return $this->table('goods_distribution')->where($condition)->find();
    }
    
    /**
     * 查询公共数据
     *
     * @param array $condition 查询条件
     * @return array
     */
    public function getGoodCommonDistributionInfo($condition) {
        return $this->table('goods_common_distribution')->where($condition)->find();
    }
    
    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addGoodDistribution($param) {
        return $this->table('goods_distribution')->insert($param);
    }
    
    /**
     * 新增公共数据
     *
     * @param array $insert 数据
     * @param string $table 表名
     */
    public function addGoodCommonDistribution($insert) {
        return $this->table('goods_common_distribution')->insert($insert);
    }

    
    /**
     * 更新
     * 
     * @param array $data
     * @param array $condition
     */
    public function editGoodDistribution($data = array(),$condition = array()) {
        return $this->table('goods_distribution')->where($condition)->update($data);
    }
    
    /**
     * 更新公共数据
     * 
     * @param array $data
     * @param array $condition
     */
    public function editGoodCommonDistribution($data = array(),$condition = array()) {
        return $this->table('goods_common_distribution')->where($condition)->update($data);
    }
    
    /**
     * 删除
     *
     * @param int $condition 条件
     * @return bool 布尔类型的返回结果
     */
    public function delGoodDistribution($condition) {
        return $this->table('goods_distribution')->where($condition)->delete();
    }
    
    /**
     * 删除公共数据
     *
     * @param int $condition 条件
     * @return bool 布尔类型的返回结果
     */
    public function delGoodCommonDistribution($condition) {
        return $this->table('goods_common_distribution')->where($condition)->delete();
    }
    
}
