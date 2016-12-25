<?php
/**
 * 分销商返佣记录表
 */
defined('emall') or exit('Access Invalid!');
class distribution_store_recordsModel extends Model{
    public function __construct() {
        parent::__construct('distribution_store_records');
    }
    public function addRecords($param)
    {
        $insert_id = $this->table('distribution_store_records')->insert($param);
        if (!$insert_id) {
            throw new Exception();
        }
        return $insert_id;
    }

    public function getRecords($array=array(),$page=10){
        $res=$this->table('distribution_store_records')->where($array)->page($page)->select();
        return $res;
    }
}