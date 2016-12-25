<?php
/**
 * Created by PhpStorm.
 * User: xiyu
 * Date: 2015/11/23
 * Time: 17:16
 */

defined('emall') or exit('Access Invalid!');
class homeLogic {

    /**
     * 添加广告
     * @param client_type
     * @param city_name
     * @param district_name
     */
    public function get_adv(){
        $condition=array(
            'adv_status'=>1,
            'adv_start_date'=>array('elt',time()),
            'adv_end_date'=>array('egt',time()),
        );
        $model=Model('fix_adv');
        $client_type=strtolower($_REQUEST['client_type']);
        if(!isset($model->client_to_channel[$client_type])){
            return array();
        }
        $city_id=$area_id=$province_id=0;
        if(isset($_REQUEST['city_name'])&&!empty($_REQUEST['city_name'])){
            $area_info=Model('area')->getAreaInfo(array('area_name'=>trim($_REQUEST['city_name']),'area_deep'=>2));
            $city_id=isset($area_info['area_id'])?$area_info['area_id']:0;
            $province_id=isset($area_info['area_parent_id'])?$area_info['area_parent_id']:0;
        }
        if(isset($_REQUEST['district_name'])&&!empty($_REQUEST['district_name'])){
            $area_info=Model('area')->getAreaInfo(array('area_name'=>trim($_REQUEST['district_name']),'area_deep'=>3));
            $area_id=isset($area_info['area_id'])?$area_info['area_id']:0;
        }

        $channel=$model->client_to_channel[$client_type];
        $pri_adv_list=$model->getAdvList($condition);
        $output_adv=array();
        foreach($pri_adv_list as $value){
            $visualise_client=explode(',',$value['adv_channel']);
            if(!in_array(0,$visualise_client) && !in_array($channel,$visualise_client)){
                continue;
            }
            if($value['adv_limit_area']==1){
                //限制区域。检查该区域是否可显示这条广告
                $visualise_province=explode(',',$value['adv_provinceids']);
                $visualise_city=explode(',',$value['adv_cityids']);
                $visualise_area=explode(',',$value['adv_areaids']);
                $continue=true;
                $temp=$this->check_area($province_id,$visualise_province);
                if(1==$temp){
                    continue;
                }
                if(2==$temp){
                    $continue=false;
                }
                if($continue) {
                    $temp = $this->check_area($city_id, $visualise_city);
                    if (1 == $temp) {
                        continue;
                    }
                    if (2 == $temp) {
                        $continue = false;
                    }
                    if($continue) {
                        $temp = $this->check_area($area_id, $visualise_area);
                        if (2 != $temp) {
                            continue;
                        }
                    }
                }
            }
            $output_adv[]=array(
                'image'=>UPLOAD_SITE_URL."/".ATTACH_ADV."/".$value['adv_pic_path'],
                'data'=>$value['adv_link'],
                'type'=>'',
                'title'=>'',
                'describe'=>'',
            );
        }
        return $output_adv;
    }


}