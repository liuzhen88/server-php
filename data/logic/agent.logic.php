<?php
/**
 * Created by PhpStorm.
 * User: sborg_000
 * Date: 2015/7/27
 * Time: 9:29
 */
defined('emall') or exit('Access Invalid!');
class agentLogic {
    /**
     * 查询上级机构信息,或者上上级。（agg_agent表的数据）
     * @param $agent_info （agg_agent表的数据）
     * @return mixed
     *
     */
    public function getFather($agent_info){
        if(!in_array($agent_info['agent_grade'],array(2,3,5))){
            return false;
        }
        switch($agent_info['agent_grade']){
            case 2:
                $area_info=Model('agent_area_hash')->where(array('agent_id'=>$agent_info['agent_id']))->find();
                if(empty($area_info))   return false;//系统错误
                //找上级省代机构id
                $condition=array(
                    'province'=>$area_info['province'],
                    'city'=>'',
                    'area'=>'',
                );
                $area_father=Model('agent_area_hash')->where($condition)->find();
                if(empty($area_father)) return false;
                $data=Model('agent')->where(array('agent_id'=>$area_father['agent_id']))->find();
                if(empty($data)) return false;
                return array('type'=>'father','data'=>$data);
                break;
            case 3:
                $area_info=Model('agent_area_hash')->where(array('agent_id'=>$agent_info['agent_id']))->find();
                if(empty($area_info))   return false;//系统错误
                //找上级市代机构id
                $condition=array(
                                'province'=>$area_info['province'],
                                'city'=>$area_info['city'],
                                'area'=>'',
                    );
                $area_father=Model('agent_area_hash')->where($condition)->find();
                if(!empty($area_father)){//返回上级市代信息
                    $data=Model('agent')->where(array('agent_id'=>$area_father['agent_id']))->find();
                    if(empty($data)) return false;
                    return array('type'=>'father','data'=>$data);
                }else{//返回上上级省代信息
                    $condition=array(
                        'province'=>$area_info['province'],
                        'city'=>'',
                        'area'=>'',
                    );
                    $area_grandfather=Model('agent_area_hash')->where($condition)->find();
                    if(empty($area_grandfather)) return false;
                    $data=Model('agent')->where(array('agent_id'=>$area_grandfather['agent_id']))->find();
                    if(empty($data)) return false;
                    return array('type'=>'grandfather','data'=>$data);
                }
                break;
            case 5:
                $area_info=Model('agent_area_hash')->where(array('agent_id'=>$agent_info['agent_id']))->find();
                if(empty($area_info))   return false;//系统错误
                //查找一级区代
                $condition=array(
                    'province'=>$area_info['province'],
                    'city'=>$area_info['city'],
                    'area'=>$area_info['area'],
                );
                $agent_ids=Model('agent_area_hash')->field('group_concat(agent_id) as ids')->where($condition)->find();
                if(empty($agent_ids['ids'])) return false;
                $data=Model('agent')->where('agent_id in ('.$agent_ids['ids'].') and agent_grade=4')->find();
                if(empty($data)) return false;
                return array('type'=>'father','data'=>$data);
                break;
        }

    }
}