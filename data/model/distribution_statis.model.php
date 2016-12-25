<?php
/**
 * 统计每天信息插入
 */
defined('emall') or exit('Access Invalid!');
class distribution_statisModel extends Model{
    public function __construct() {
        parent::__construct('distribution_statis');
    }

    /**
     * /
     * @param [type] $param [description]
     */
    public function addStatis($param)
    {
        $insert_id = $this->insert($param);
        if (!$insert_id) {
            throw new Exception();
        }
    }
}