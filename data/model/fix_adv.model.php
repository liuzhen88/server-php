<?php
/**
 * 定点广告模型类
 *

 */
defined('emall') or exit('Access Invalid!');

class fix_advModel extends Model{
     public $client_to_channel;
     public function __construct() {
       parent::__construct('fix_adv');
         $this->client_to_channel=array(
             'ios'=>1,
             'android'=>2,
             'wap'=>3,
         );
    }
    /**
     * 列表
     */
    public function getAdvList($condition = array(), $pagesize = '', $limit = '', $order = 'adv_add_date desc,adv_order') {
         return $this->where($condition)->order($order)->page($pagesize)->limit($limit)->select();
    }
    /**
     * 广告 停用，启用
     */
    public function updateAdv($param,$condition){
        if (empty($condition)){
            return false;
        }
        $condition_str = '';	
        if ($condition['adv_id_in'] !=''){
                $condition_str .= " and id in({$condition['adv_id_in']}) ";
        }
        return Db::update('fix_adv',$param,$condition_str);
    }
    /**
     * 广告添加
     */
    public function addAdv($param){
        return $this->table('fix_adv')->insert($param);
    }
    
     /**
     * 广告更新
     *
     */
    public function update_($param){
        return Db::update('fix_adv',$param,"id='{$param['id']}'");
    }
    /**
     * 广告 删除
     */
    public function delAdv($condition){
        if (empty($condition)){
            return false;
        }
        $condition_str = '';	
        if (!empty($condition['adv_id_in'])){
            $condition_str .= " and id in({$condition['adv_id_in']}) ";
        }
        return Db::delete('fix_adv',$condition_str);
    }
}
