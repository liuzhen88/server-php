<?php

/**
 * 跑腿邦投诉反馈模型
 */
defined('emall') or exit('Access Invalid!');

class LeagueComplainModel extends Model
{
    protected $table_name = 'complain_league';

    /*
     *  获得投诉列表
     *  @param array $condition 查询条件
     *  @param obj $page 	分页对象
     *  @return array
     */
    public function getList($condition = '', $page = '')
    {

        $param = array();
        $param['table'] = $this->table_name;
        $param['where'] = $this->_getCondition($condition);
        $param['order'] = $condition['order'] ? $condition['order'] : ' id desc ';
        return Db::select($param, $page);
    }


    /*
	 * 构造条件
	 */
    private function _getCondition($condition)
    {
        $condition_str = '';
//        投诉人
        if (!empty($condition['accuser_name'])) {
            $condition_str .= " and  accuser_name = '{$condition['accuser_name']}'";
        }
//        投诉状态
        if (!empty($condition['complain_state'])) {
            $condition_str .= " and  complain_state = '{$condition['complain_state']}'";
        }
//        被投诉店铺
        if (!empty($condition['league_store_name'])) {
            $condition_str .= " and  league_store_name = '{$condition['league_store_name']}'";
        }
        if (!empty($condition['accuser_id'])) {
            $condition_str .= " and  accuser_id = '{$condition['accuser_id']}'";
        }
        if (!empty($condition['order_id'])) {
            $condition_str .= " and  order_id = '{$condition['order_id']}'";
        }
        if (!empty($condition['order_goods_id'])) {
            $condition_str .= " and  order_goods_id = '{$condition['order_goods_id']}'";
        }

        if (!empty($condition['progressing'])) {
            $condition_str .= " and  complain_state < 90 ";
        }

        if (!empty($condition['complain_subject_content'])) {
            $condition_str .= " and  complain_subject_content like '%" . $condition['complain_subject_content'] . "%'";
        }
        if (!empty($condition['complain_datetime_start'])) {
            $condition_str .= " and  complain_datetime > '{$condition['complain_datetime_start']}'";
        }
        if (!empty($condition['complain_datetime_end'])) {
            $end = $condition['complain_datetime_end'] + 86400;
            $condition_str .= " and  complain_datetime < '$end'";
        }
        return $condition_str;
    }

    /*
    *   根据id获取投诉详细信息
    */
    /**
     * @param int $complain_id 投诉订单ID
     * @return mixed
     */
    public function getLeagueComplainInfo($complain_id)
    {
        $param = array();
        $param['value'] = intval($complain_id);
        return $this->where(array(
            'id' => $param['value']
        ))->find();
    }


    public function save($update, $condition)
    {
        return $this->where($condition)->update($update);
    }
}