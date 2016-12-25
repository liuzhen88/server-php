<?php
/**
 * 代理商模型
 * 
 * @author lijunhua
 * @since 2015-07-22
 */
defined('emall') or exit('Access Invalid!');

class agentModel extends Model {

    public function __construct(){
        parent::__construct('agent');
    }

    /**
     * 取得列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $order
     * @param string $field
     */
    public function getAgentList($condition = array(), $pagesize = '', $limit = '', $order = 'create_time desc,agent_id desc') {
        return $this->where($condition)->order($order)->page($pagesize)->limit($limit)->select();
    }
    
    /**
     * 取得单条信息
     * @param unknown $condition
     */
    public function getAgentInfo($condition = array()) {
        return $this->where($condition)->find();
    }

    /**
     * 根据区域获取代理商信息
     * @param unknown $condition
     */
    public function getAgentInfoByArea($condition = '1=2') {
        $area=Model('agent_area')->getAgentAreaInfo($condition);
        if(!$area) return false;
        return $this->getAgentInfo(array('agent_id'=>$area['agent_id']));
    }

    /**
     * 取得完整信息
     * @param unknown $condition
     */
    public function getAgentFullInfo($condition = array()) {
        $agent =  $this->where($condition)->find();
        $agent['agent_extend'] = Model('agent_extend')->where(array('agent_id' => $condition['agent_id']))->find();
        $agent['agent_area'] = Model('agent_area')->where(array('agent_id' => $condition['agent_id']))->select();
        return $agent;
    }    
    
    
    /**
     * 取得扩展信息
     * @param unknown $condition
     */
    public function getAgentExtendInfo($condition = array()) {
        return Model('agent_extend')->where(array('agent_id' => $condition['agent_id']))->find();
    }
    
    /**
     * 删除
     * @param unknown $condition
     */
    public function delAgent($condition = array()) {
        return $this->where($condition)->delete();
    }

    /**
     * 添加代理商
     *
     * @param	array $param 会员信息
     * @return	array 数组格式的返回结果
     */
    public function addAgent($param) {
            if(empty($param)) {
                    return false;
            }
            try {
                $this->beginTransaction();
                $info	= array();
                $info['member_id']		= $param['member_id'];
                $info['agent_company_name']	= $param['agent_company_name'];
                $info['agent_mode']		= isset($param['agent_mode']) ? (int)$param['agent_mode'] : 1;
                $info['agent_grade']		= $param['agent_grade'];
                $info['agent_status']		= $param['agent_status'];
                $info['check_out']		= $param['check_out']==null?0:$param['check_out'];
                $info['create_time']		= TIMESTAMP;
                $info['update_time']		= TIMESTAMP;
                
                //添加代理商信息
                $insert_id	= $this->table('agent')->insert($info);
                if (!$insert_id) {
                    $this->rollback();
                    return false;
                }
                
                //添加扩展信息
                $param['agent_id']  = (int)$insert_id;
                $insert = $this->addAgentExtend($param);
                if (!$insert) {
                    $this->rollback();
                    return false;
                }
                //添加默认代理区域
                $insert = $this->addBatchAgentArea($param); 
                if (!$insert) {
                     $this->rollback();
                     return false;
                }
                //添加管理员账号
                $insert = $this->addAgentManager($param);
                if (!$insert) {
                     $this->rollback();
                     return false;
                }
                
                // 绑定代理商商家关系
                if ($param['agent_status'] == 1) {
                    $this->_bindAgentStoreArrayHash((int)$insert_id);
                    $info['agent_id'] = (int)$insert_id;
                     // 无主商户绑定(无需写入事务)
                    $this->_bind_noowner_store($info);
                   
                     // 异地代理商邀请商户逻辑处理
                    $this->_bind_abvoad_store($info);

                }
                $this->commit();
               
                return $insert_id;
            } catch (Exception $e) {
                $this->rollback();
                return false;
            }
    }
    
    /**
     * 添加代理商扩展信息
     * 
     * @param array $param
     */
    public function addAgentExtend($param) {
        $info	= array();
        $info['agent_id']	= $param['agent_id'];
        $info['contactor']	= $param['contactor'];
        $info['tel']		= $param['tel'];
        $info['email']		= $param['email'];
        $info['content']	= $param['content'];
        $info['remark']		= $param['remark'];
        return $this->table('agent_extend')->insert($info);
    }
    
    /**
     * 添加代理商区域
     * 
     * @param array $param
     */
    public function addAgentArea($param) {
        $info	= array();
        $info['agent_id']	= $param['agent_id'];
        $info['create_time']	= TIMESTAMP;
        $area_arr	        = explode(' ', $param['agent_area_name']);
        if ($param['agent_grade'] == 1) {
            $info['province']   = $area_arr[0];
            $info['city']       = '';
            $info['area']       = '';
        } else if ($param['agent_grade'] == 2) {
            $info['province']   = $area_arr[0];
            $info['city']       = $area_arr[1];
            $info['area']       = '';
        } else if (in_array($param['agent_grade'], array(3 ,4 ,5))) {
            $info['province']   = $area_arr[0];
            $info['city']       = $area_arr[1];
            $info['area']       = $area_arr[2];
        }
        
        // 二级代
        if ($param['agent_grade'] == 5) {
            $info['street']   = $param['street'];
        }
       
        return $this->table('agent_area_hash')->insert($info);
    }
  
    
    /**
     * 添加代理商管理员
     * 
     * @param array $param
     */
    public function addAgentManager($param) {
        $info	= array();
        $info['agent_id']	 = $param['agent_id'];
        $info['member_id']	 = $param['member_id'];
        $info['agent_group_id']	 = 0; 
        $info['is_boss']         = 1; //老板账号
        $info['last_login_time'] = TIMESTAMP;
        $info['status']          = 1; //0禁用 1启用
        $res=$this->table('agent_manager')->insert($info);
        $this->table('agent');
        return $res;
    }
    
    /**
     * 批量新增代理商区域
     * 
     * @param array $param['area_name_arr']
     * @param int   $param['agent_id']
     * @param int   $param['agent_grade']
     */
    public function addBatchAgentArea($param)
    {
        if (isset($param['agent_area_arr']) && !empty($param['agent_area_arr'])) {
            // 检查区域重复
           foreach ((array)$param['agent_area_arr'] as $value) {
               $agent_data = array(
                   'agent_id'        => (int)$param['agent_id'], 
                   'agent_grade'     => (int)$param['agent_grade'],
                   'agent_area_name' => $value
               );
               $is_exist = $this->is_exist_agent_area($agent_data);
               if ($is_exist) {
                   return false;
               }
           }                
           // 批量添加
           foreach ((array)$param['agent_area_arr'] as $value) {
                $agent_data = array(
                    'agent_id'        => (int)$param['agent_id'], 
                    'agent_grade'     => (int)$param['agent_grade'],
                    'agent_area_name' => $value,
                );
                
                if ($param['agent_grade'] == 5) {
                    $agent_data['street'] = $param['street'];
                }
                
               $insert_id = $this->addAgentArea($agent_data);
               if (!$insert_id) {
                   return false;
               }
           }

           return true;
       }
    }
    
    
    /**
     * 更新
     * @param unknown $data
     * @param unknown $condition
     */
    public function editAgent($data = array(),$condition = array()) {
         $rs = $this->where($condition)->update($data);
         
         // 已审核，执行绑定关系
         if (isset($data['agent_status']) && $data['agent_status'] == 1) {
            $this->_bindAgentStoreArrayHash($condition['agent_id']);
             
            // 无主商户绑定(无需写入事务)
            $this->_bind_noowner_store($this->getAgentInfo($condition));
            
            // 异地代理商邀请商户逻辑处理
            $this->_bind_abvoad_store($this->getAgentInfo($condition));

         }
         
         return $rs;
    }
    
    /**
     * 更新
     * @param unknown $data
     * @param unknown $condition
     */
    public function editAgentExtend($data = array(),$condition = array()) {
        return Model('agent_extend')->where($condition)->update($data);
    }
    
    
    /**
     * 区域字符串转数组
     * @param type $param
     * @return type
     */
    public function getAreaStrToArr($param)
    {
        $area_arr	        = explode(' ', $param['agent_area_name']);
        $info = array();
        
        // 新模式
        if ($param['agent_grade'] == 1) {
            $info['province']   = $area_arr[0];
            $info['city']       = '';
            $info['area']       = '';
        } else if ($param['agent_grade'] == 2) {
            $info['province']   = $area_arr[0];
            $info['city']       = $area_arr[1];
            $info['area']       = '';
        } else if ($param['agent_grade'] == 3) {
            $info['province']   = $area_arr[0];
            $info['city']       = $area_arr[1];
            $info['area']       = $area_arr[2];
        } else if ($param['agent_grade'] == 4) {
            $info['province']   = $area_arr[0];
            $info['city']       = $area_arr[1];
            $info['area']       = $area_arr[2];  
        }
        return $info;
    }
    
    /**
     * 验证多区域是否重复

     * @param string $param['agent_area_name'] 格式如：安徽 合肥市 庐阳区
     * @param string $param['agent_grade']     代理级别
     * @return bool
     */
    public function is_exist_agent_area_list($param)
    {
        foreach ((array)$param['agent_area_arr'] as $area_name) {
            $param['agent_area_name'] = $area_name;
            $is_exist = $this->is_exist_agent_area($param);
            if ($is_exist) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 验证区域是否重复

     * @param string $param['agent_area_name'] 格式如：安徽 合肥市 庐阳区
     * @param string $param['agent_grade']     代理级别
     * @return bool|array
     */
    public function is_exist_agent_area($param)
    {
        $info = $this->getAreaStrToArr($param);
        $result = Model('agent_area')->getAgentAreaList($info);
        
        // 二级代重不重复无所谓
        if ($param['agent_grade'] == 5) {
            return false;
        }
         return empty($result) ? false : true;
    }
    
    
    /**
     * 绑定代理商与商家关系(考虑代理商存在多区域)
     * 说明：新模式下添加省市代才处理绑定关系，因为省市不能直接添加商户)
     * 
     * @param  int          $agent_id
     * @param  string       $pre         查询字段前缀 local_ 表示异地
     * @return boolen|array
     */
    public function _bindAgentStoreArrayHash($agent_id, $pre = '')
    {
        $agent_info = $this->getAgentInfo(array('agent_id' => $agent_id));
        if (empty($agent_info)) {
            return false;
        }

        if (!in_array($agent_info['agent_grade'], array(1, 2))) {
            // 非新模式省市级别，直接滤掉
            return false;
        }
        
        if ($agent_info['agent_status'] != 1) {
            // 禁用 未审核, 直接滤掉
            return false;
        }
        $condition = array('agent_id' => $agent_id);
        $agent_area_arr = Model('agent_area')->getAgentAreaList($condition);
        if (empty($agent_area_arr)) {
            // 异常错误, 直接滤掉
            return false;
        }
        if (count($agent_area_arr) == 1) {
             $agent_info['province'] = $agent_area_arr[0]['province'];
             $agent_info['city']     = $agent_area_arr[0]['city'];
        } else {
            foreach ((array)$agent_area_arr as $area) {
                $agent_info['province'][] = $area['province'];
                $agent_info['city'][]     = $area['city'];
            }
        }
       
        return $this->_bindAgentStoreHash($agent_info, $pre);
        
    }
    
    /**
     * 区域更新店铺的代理区域关系
     * 说明：新模式下添加省市代才处理绑定关系，因为省市不能直接添加商户，也就是将区代商户绑定到省市)
     * 
     * @param  int          $param['agent_id']
     * @param  int          $param['member_id]
     * @param  int          $param['agent_status']
     * @param  int          $param['agent_grade']
     * @param  string|array $param['province']
     * @param  string|array $param['city']
     * 
     * @return boolen|array
     */
    private function _bindAgentStoreHash($param, $pre = '') {

        // 获取区代ID
        $condition = array();
        $condition['province'] = $this->_filter_where_in($param['province']);
        
        $condition['area']     = array('neq', ''); 
        if ($param['agent_grade'] == 2) {
            $condition['city'] =  $this->_filter_where_in($param['city']);
        } else {
            $condition['city'] = array('neq', ''); 
        }
        $agent_area = Model('agent_area')->getAgentAreaList($condition);
        if (empty($agent_area)) {
            // 为空，未找到区，直接滤掉
            return false;
        }
        
        $agent_area_ids = array();
        foreach ((array)$agent_area as $key => $value) {
            $agent_area_ids[] = (int)$value['agent_id'];
        }

        $up_condtion = array();
        $agent_area_ids = array_merge($agent_area_ids);
        if (count($agent_area_ids) == 1) {
            $up_condtion[$pre . 'agent_id_1'] = $agent_area_ids[0];
        } else {
            $up_condtion[$pre . 'agent_id_1'] = array('in', implode(',', $agent_area_ids)); 
        }
        
        if ($param['agent_grade'] == 2) {
            //市级代理
            $up_data = array(
                $pre . 'agent_id_2'        => (int)$param['agent_id'], 
                $pre . 'agent_member_id_2' => (int)$param['member_id']
            );
            $up_condtion[$pre . 'agent_id_2']        = 0;
            $up_condtion[$pre . 'agent_member_id_2'] = 0;
        } else {
             //省级代理
            $up_data = array(
               $pre .  'agent_id_3'        => (int)$param['agent_id'], 
               $pre .  'agent_member_id_3' => (int)$param['member_id']
            );
            $up_condtion[$pre . 'agent_id_3']        = 0;
            $up_condtion[$pre . 'agent_member_id_3'] = 0;
        }
        
        // 不为空，有可能是异地代理
        if (!empty($pre)) {
            $up_condtion['is_remote'] = 1;
        }
        
        $result = Model('agent_store')->where($up_condtion)->update($up_data);
        return $result;
    }
    
    
    private function _filter_where_in($value = '') {
        return is_array($value) ?  array('in', implode(',', $value)) : $value;
    }
    
    
    /**
     * 无主商家与区级代理商关系绑定
     * 
     * 说明：1. 新增代理商时，只会新增新模式的代理商，所以旧模式不考虑
     *       2. 只考虑区代，省市代无主就无主
     *       3. 一旦和区代绑定关系后，省市的关系绑定有别的逻辑处理(_bindAgentStoreArrayHash)
     * @param array $agent_info
     */
     public function _bind_noowner_store($agent_info = array()) {
       
        $store_area = $this->_get_store_area($agent_info);
        if ($store_area < 0) {
            return false;
        }
        $store_ids = $store_area['store_ids'];
        $area_info = $store_area['area_info'];
        
        // 无主商户
        $agent_store_condition = array(
            'store_id'          => array('in', implode(',',$store_ids)),
            'agent_id_1'        => 0,
            'agent_member_id_1' => 0,
            'agent_id_2'        => 0,
            'agent_member_id_2' => 0,
            'agent_id_3'        => 0,
            'agent_member_id_3' => 0,
        );
        
        $up_data = $this->_get_store_updata($agent_info, $area_info);
        $rs = Model('agent_store')->editAgentStore($agent_store_condition, $up_data);
        return $rs;
    }
    
    /**
     * 异地代理商邀请商户逻辑处理
     * 
     * 说明：1.区代邀请：
     *           1.1 若新增市代,则也绑定
     *           1.2 若新增省代,则也绑定
     *       2.市代邀请：
     *           1.2 若新增省代,则也绑定
     *        3.省代邀请
     *           1.1 下级不做任何处理
     * 
     * @param array $agent_info
     */
    public function _bind_abvoad_store($agent_info = array()) {

        $agent_grade = (int)$agent_info['agent_grade'];
        if (!in_array($agent_grade, array(1,2))) {
            return false;
        } 

        $agent_area_model = Model('agent_area');
        $area_info = $agent_area_model->getAgentAreaInfo(array('agent_id' => $agent_info['agent_id']));
        
        $where = array();
        switch ($agent_grade) {
            //省代获取市县的代理商
            case 1: 
                $where['province'] = $area_info['province'];
                $agent_field       = 3;
                $agent_ids         = $this->_get_agent_ids_by_area($where);
                if (!empty($agent_ids)) {
                     $agent_store_condition['local_agent_id_1|local_agent_id_2'] = array('in', $agent_ids);
                }
                break;
                
            //市代获取区代的代理商
            case 2:
                $where['province'] = $area_info['province'];
                $where['city']     = $area_info['city'];
                $agent_field       = 2;
                $agent_ids         = $this->_get_agent_ids_by_area($where);
                if (!empty($agent_ids)) {
                     $agent_store_condition['local_agent_id_1'] = array('in', $agent_ids);
                }
                break;
        }
        
        $agent_store_condition['is_remote'] = 1;
        $up_data = array(
            'local_agent_id_' . $agent_field        => $agent_info['agent_id'],
            'local_agent_member_id_' . $agent_field => $agent_info['member_id'],
        );
        
        
        $agent_store_model = Model('agent_store');
        $rs = $agent_store_model->editAgentStore($agent_store_condition, $up_data);
        return $rs;
    }
    
    private function _get_agent_ids_by_area($where='')
    {
        // 查出所有省市代理商ID
        $agent_id_arr = Model('agent_area')->getAgentAreaList($where, '');
        $agent_ids = '';
         if (empty($agent_id_arr)) {
             return false;
         }
         foreach ((array)$agent_id_arr as $data) {
             $agent_ids[] = $data['agent_id'];
         }
         
         return implode(',', $agent_ids);
    }
    
    /**
     * 获取代理商ID和会员ID
     * 
     * @param type $agent_info
     * @param type $area_info
     * @param type $pre       
     * @return type
     */
    private function _get_store_updata($agent_info, $area_info, $pre='')
    {
         $up_data = array();
         $model_area = Model('agent_area');
         switch ($agent_info['agent_grade']) {
             //省
            case 1: 
                $up_data[$pre . 'agent_id_3']         = $agent_info['agent_id'];
                $up_data[$pre . 'agent_member_id_3']  = $agent_info['member_id'];
                break;
            //市
            case 2:  
                //查省代
                $where = array('province' => $area_info['province'], 'city' => '', 'area' => '');
                $area_info = $model_area->getAgentAreaInfo($where);
                $provice_agent_info = $this->getAgentInfo(array('agent_id' => $area_info['agent_id']));
                if (!empty($provice_agent_info)) {
                    $up_data[$pre . 'agent_id_3']          = $provice_agent_info['agent_id'];
                    $up_data[$pre . 'agent_member_id_3']   = $provice_agent_info['member_id'];
                }
                $up_data[$pre . 'agent_id_2']          = $agent_info['agent_id'];
                $up_data[$pre . 'agent_member_id_2']   = $agent_info['member_id'];

                break;
            //区
            case 3: 
                //查省代
                $where = array('province' => $area_info['province'], 'city' => '', 'area' => '');
                $province_area_info = $model_area->getAgentAreaInfo($where);
                $provice_agent_info = $this->getAgentInfo(array('agent_id' => $province_area_info['agent_id']));
                if (!empty($provice_agent_info)) {
                    $up_data[$pre . 'agent_id_3']          = $provice_agent_info['agent_id'];
                    $up_data[$pre . 'agent_member_id_3']   = $provice_agent_info['member_id'];
                }
                //查市代
                $where = array('province' => $area_info['province'], 'city' => $area_info['city'], 'area' => '');
                $city_area_info = $model_area->getAgentAreaInfo($where);
                $city_agent_info = $this->getAgentInfo(array('agent_id' => $city_area_info['agent_id']));
                if (!empty($city_agent_info)) {
                    $up_data[$pre . 'agent_id_2']          = $city_agent_info['agent_id'];
                    $up_data[$pre . 'agent_member_id_2']   = $city_agent_info['member_id'];
                }
                
                $up_data[$pre . 'agent_id_1']          = $agent_info['agent_id'];
                $up_data[$pre . 'agent_member_id_1']   = $agent_info['member_id'];
                break;
        }
        
        return $up_data;  
    }
    
    /**
     * 根据代理商获取对应区域商户一级区域信息
     * 
     * @param array $agent_info
     * @return array
     */
    private function _get_store_area($agent_info = array())
    {
        if (empty($agent_info)) {
            return -1;
        }
       
        if (!in_array($agent_info['agent_grade'], array(1,2,3))) {
            // 只处理新模式
            return -2;
        }
        
        $area_info = Model('agent_area')->getAgentAreaInfo(array('agent_id' => $agent_info['agent_id']));
        switch ($agent_info['agent_grade']) {
            case 1: 
                $area = $area_info['province']; 
                break;
            case 2: 
                $area = $area_info['province'] . ' ' . $area_info['city'];
                break;
            case 3: 
                $area = $area_info['province'] . ' ' . $area_info['city'] . ' '. $area_info['area']; 
                break;
        }
        
        if (empty($area)) {
            return -3;
        }
        
        
        // 查询店铺(状态关闭也处理,防止被开启后,逻辑未处理到)
        $store_condition = array(
            'area_info' => array('like', $area . '%'),
            'is_own_shop' => 0,  //过滤自营店
        );
        $store_id_arr = Model('store')->getStoreList($store_condition, null, null, 'store_id');
        if (empty($store_id_arr)) {
            return -4;
        }
        $store_ids = array();
        foreach ((array)$store_id_arr as $data) {
            $store_ids[] = $data['store_id'];
        }
        
        return array('store_ids' => $store_ids, 'area_info' => $area_info);
    }
    
    
}