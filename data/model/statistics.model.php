<?php
/**
 * 店铺统计
 * 
 */
defined('emall') or exit('Access Invalid!');
class statisticsModel{
	/**
	 * 更新统计表
	 *
	 * @param	array $param	条件数组
	 */
	public function updatestat($param){
		if (empty($param)){
			return false;
		}
		$result = Db::update($param['table'],array($param['field']=>array('sign'=>'increase','value'=>$param['value'])),$param['where']);
		return $result;
	}

    /**
     * 店铺流量统计入库
     */
    public function flowstat_record($store_id,$goods_id=0){
        $store_id = intval($store_id);
        if ($store_id <= 0){
            return;
        }
        //确定统计分表名称
        $last_num = $store_id % 10; //获取店铺ID的末位数字
        $tablenum = ($t = intval(C('flowstat_tablenum'))) > 1 ? $t : 1; //处理流量统计记录表数量
        $flow_tablename = ($t = ($last_num % $tablenum)) > 0 ? "flowstat_$t" : 'flowstat';
        //判断是否存在当日数据信息
        $stattime = strtotime(date('Y-m-d',time()));
        $model = Model('stat');
        //查询店铺流量统计数据是否存在
        $store_exist = $model->getoneByFlowstat($flow_tablename,array('stattime'=>$stattime,'store_id'=>$store_id,'type'=>'sum'));
        $goods_id = intval($goods_id);
        if ($goods_id > 0){//统计商品页面流量
            $goods_exist = $model->getoneByFlowstat($flow_tablename,array('stattime'=>$stattime,'goods_id'=>$goods_id,'type'=>'goods'));
        }
        //向数据库写入访问量数据
        $insert_arr = array();
        if($store_exist){
            $model->table($flow_tablename)->where(array('stattime'=>$stattime,'store_id'=>$store_id,'type'=>'sum'))->setInc('clicknum',1);
        } else {
            $insert_arr[] = array('stattime'=>$stattime,'clicknum'=>1,'store_id'=>$store_id,'type'=>'sum','goods_id'=>0);
        }
        if ($goods_id > 0){//已经存在数据则更新
            if ($goods_exist){
                $model->table($flow_tablename)->where(array('stattime'=>$stattime,'goods_id'=>$goods_id,'type'=>'goods'))->setInc('clicknum',1);
            } else {
                $insert_arr[] = array('stattime'=>$stattime,'clicknum'=>1,'store_id'=>$store_id,'type'=>'goods','goods_id'=>$goods_id);
            }
        }
        if ($insert_arr){
            $model->table($flow_tablename)->insertAll($insert_arr);
        }
        return;
    }
}