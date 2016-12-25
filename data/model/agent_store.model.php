<?php
/**
 * 代理商商家关系表
 * @author chenyifei
 * @modify lijunhua 
 */
defined('emall') or exit('Access Invalid!');
class agent_storeModel extends Model {
    public function __construct(){
        parent::__construct('agent_store');
    }
    /**
     * 获取机构下商户id
     * 
     * @modify lijunhua
     * @since 2015-09-21
     * @param int  $agent_id   代理商id
     * @param int $agent_grade 代理级别
     */
    public function getStoreIds($agent_id,$agent_grade, $extend_where = array())
    {

        $condition = $this->get_where_agent_id($agent_id, $agent_grade);
        
        if (!empty($extend_where)) {
            $condition = array_merge($condition, $extend_where);
        }

        if (empty($condition))
            return array();
        $model = $this->where($condition)->field('store_id')->select();
        $data = array();
        foreach ($model as $row)
        {
            $data[] = $row['store_id'];
        }
        return $data;
    }
    
    /**
     * 取得列表  merge from online duplicate
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     */
/*    public function getAgentStoreList($condition = array(), $pagesize = '', $limit = '', $order = 'id desc') {
        return $this->where($condition)->order($order)->page($pagesize)->limit($limit)->select();
    }*/
    /**
     * 取得单条信息
     * @param unknown $condition
     */
    public function getAgentStoreInfo($condition = array()) {
        return $this->where($condition)->find();
    }
    
    public function get_where_agent_id($agent_id, $agent_grade){
       
        $condition = array();
        switch ($agent_grade)
        {
            case 1:
                $condition['agent_id_3'] = $agent_id;
                break;
            case 2:
                $condition['agent_id_2'] = $agent_id;
                break;
            case 3:
                $condition['agent_id_1'] = $agent_id;
                break;
            case 4:
                $condition['agent_id_2'] = $agent_id;
                break;
            case 5:
                $condition['agent_id_1'] = $agent_id;
                break;
                    
        }
        
        return $condition;
    }
    //统计未禁用和有效商户条件
    public function get_where_agent_id_join($agent_id, $agent_grade){

        $condition = array();
        switch ($agent_grade)
        {
            case 1:
                $condition['agent_store.agent_id_3'] = $agent_id;
                break;
            case 2:
                $condition['agent_store.agent_id_2'] = $agent_id;
                break;
            case 3:
                $condition['agent_store.agent_id_1'] = $agent_id;
                break;
            case 4:
                $condition['agent_store.agent_id_2'] = $agent_id;
                break;
            case 5:
                $condition['agent_store.agent_id_1'] = $agent_id;
                break;

        }

        return $condition;
    }
    /**
    * 查询店铺列表
    *
    * @param array $condition 查询条件
    * @param int $page 分页数
    * @param string $order 排序
    * @param string $field 字段
    * @param string $limit 取多少条
    * @return array
    */
    public function getAgentStoreList($condition, $page = null, $order = '', $field = '*', $limit = '') {
        $result = $this->field($field)->where($condition)->order($order)->limit($limit)->page($page)->select();
        return $result;
}
    
    /**
     * 添加
     * @param array $insert
     * @return boolean
     */
    public function addAgentStore($insert) {
        return $this->insert($insert);
    }
    
    /**
     * 编辑
     * @param array $condition
     * @param array $update
     * @return boolean
     */
    public function editAgentStore($condition, $update) {
        return $this->where($condition)->update($update);
    }
    
    /**
     * 取数量
     * @param unknown $condition
     */
    public function getAgentStoreCount($condition = array()) {
        return $this->where($condition)->count();
    }

    
   /**
    * 获取近7天新增商户
    */
    public function getAgentStoreCountGroupWeek($where = '', $group = '', $field = 'FROM_UNIXTIME(add_time , "%w" ) AS week_day,count(*) AS total_num') {
        return $this->field($field)->where($where)->group($group)->select();
    }
}