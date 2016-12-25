<?php

/**
 * 会员扩展模型
 *
 * @author lijunhua
 * @since  2015-08-20
 */
defined('emall') or exit('Access Invalid!');

class member_commonModel extends Model {

    public function __construct() {
        parent::__construct('member_common');
    }

    /**
     * 详细信息（查库）
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getMemberCommonInfo($condition, $field = '*') {
        return $this->field($field)->where($condition)->find();
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
     public function editMemberCommon($condition, $data) {
        return $this->where($condition)->update($data);
    }

    /**
     * 根据会员id查找多条数据
     */
    public function selectMemberComonByMemberId($member_ids=array(),$field='*',$addition_condition=array()){
        if(empty($member_ids)) return false;
        $condition=array_merge(array('member_id'=>array('in',$member_ids)),$addition_condition);
        return $this->where($condition)->field($field)->select();
    }


}
