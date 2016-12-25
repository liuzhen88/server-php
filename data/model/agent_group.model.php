<?php
/**
 * 代理商账号组模型
 * 
 *

 */
defined('emall') or exit('Access Invalid!');
class agent_groupModel extends Model{

    public function __construct(){
        parent::__construct('agent_group');
    }

	/**
	 * 读取列表 
	 * @param array $condition
	 *
	 */
	public function getAgentGroupList($condition, $page='', $order='', $field='*') {
        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
	}

    /**
	 * 读取单条记录
	 * @param array $condition
	 *
	 */
    public function getAgentGroupInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

	/*
	 *  判断是否存在 
	 *  @param array $condition
     *
	 */
	public function isAgentGroupExist($condition) {
        $result = $this->getOne($condition);
        if(empty($result)) {
            return FALSE;
        } else {
            return TRUE;
        }
	}

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function addAgentGroup($param){
        return $this->insert($param);	
    }
	
	/*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
	 */
    public function editAgentGroup($update, $condition){
        return $this->where($condition)->update($update);
    }
	
	/*
	 * 删除
	 * @param array $condition
	 * @return bool
	 */
    public function delAgentGroup($condition){
        return $this->where($condition)->delete();
    }
	
}
