<?php
/**
 * 代理商管理
 * 
 * @author lijunhua
 * @since 20150722
 **/

defined('emall') or exit('Access Invalid!');

class agentControl extends SystemControl{
	public function __construct(){
            parent::__construct();
            Language::read('agent');
            $this->_filter_area_arr();
	}

	/**
	 * 代理商列表
	 */
	public function agentOp(){

		$lang	= Language::getLangContent();
                if (isset($_GET['like_ac_name']) && !empty($_GET['like_ac_name'])) {
                    $condition['agent.agent_company_name'] =  array('like',"%" . $_GET['like_ac_name'] . "%");
                    Tpl::output('like_ac_name',$_GET['like_ac_name']);
                }
                if (isset($_GET['agent_mode']) && !empty($_GET['agent_mode'])) {
                     $condition['agent.agent_mode'] =  (int)$_GET['agent_mode'];
                     Tpl::output('agent_mode',$_GET['agent_mode']);
                }
                
                if (isset($_GET['agent_grade']) && !empty($_GET['agent_grade'])) {
                     $condition['agent.agent_grade'] =  (int)$_GET['agent_grade'];
                     Tpl::output('agent_grade',$_GET['agent_grade']);
                }
              
                //省市区县查询
                if (isset($_GET['agent_area_name']) && !empty($_GET['agent_area_name'])) {
                    $agent_area_arr = explode(' ', $_GET['agent_area_name']);
                     
                    if (isset($agent_area_arr[0]) && !empty($agent_area_arr[0])) {
                        $condition['agent_area_hash.province'] = array('like', '%' . $agent_area_arr[0] . '%');
                    } 
                    if (isset($agent_area_arr[1]) && !empty($agent_area_arr[1])) {
                        $condition['agent_area_hash.city'] = $agent_area_arr[1];
                    }
                    if (isset($agent_area_arr[2]) && !empty($agent_area_arr[2])) {
                        $condition['agent_area_hash.area'] = $agent_area_arr[2];
                    }
                }
                
                $model = Model();
                
                // 获取总数
                $agent_total = $model->table('agent,agent_area_hash')
                                      ->join('left')
                                      ->on('agent.agent_id=agent_area_hash.agent_id')
                                      ->where($condition)->count('DISTINCT(agent.agent_id)');

                // 获取分页记录
                $agent_list = $model->table('agent,agent_area_hash')
                                    ->join('left')
                                    ->field('*')
                                    ->on('agent.agent_id=agent_area_hash.agent_id')
                                    ->page(10, $agent_total)
                                    ->select(array(
                                        'where' => $condition,
                                        'group' => 'agent.agent_id',
                                        'order' => 'agent.agent_id desc',
                                    ));
                //获得积分
                $condi = array();
                $order_condi = array();
                $condi['lg_type'] = 'rebate_get';
                $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time']);
                $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time']);
                $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
                $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']): null;
                if ($start_unixtime || $end_unixtime) {
                    Tpl::output('query_start_time',$_GET['query_start_time']);
                    Tpl::output('query_end_time',$_GET['query_end_time']);
                    $condi['lg_add_time'] = array('time',array($start_unixtime,$end_unixtime));
                    $order_condi['add_time'] = array('time',array($start_unixtime,$end_unixtime));
                }
                for($i=0;$i<count($agent_list);$i++){
                    $condi['lg_member_id'] = $agent_list[$i]['member_id'];
                    $point = Model()->table('pd_log')
                        ->field('sum(lg_av_amount) as point')
                        ->select(array(
                            'where' => $condi
                        ));
                   if(empty($point[0]['point']))
                        $point[0]['point'] = 0;
                   $agent_list[$i]['point'] = $point[0]['point'];
                    //获取订单总数
                    //SELECT id from agg_rebate_records where member_id=157 GROUP BY order_id
                    $order_condi['member_id'] = $agent_list[$i]['member_id'];
                    $order_condi['user_type'] =array('in',array(5,6,7,8,9,10));
                    $order_list = Model('')->table('rebate_records')->where($order_condi)->count();
                    $agent_list[$i]['order_count'] = $order_list;
                }
                //格式化
                $agent_list = $this->_formartList($agent_list);
//        Tpl::setLayout('null_layout');
		Tpl::output('agent_list',$agent_list);
		Tpl::output('page',$model->showpage());
		Tpl::showpage('agent.index');
	}


    /**
     * 商户统计
     */
    public function store_manageOp(){

        $lang	= Language::getLangContent();
        if (isset($_GET['member_name']) && !empty($_GET['member_name'])) {
            $condition['member.member_name'] = $_GET['member_name'];
        }
        $model = Model();

        // 获取总数
        $agent_total = $model->table('agent,member')
            ->join('left')
            ->on('agent.member_id=member.member_id')
            ->where($condition)->count('DISTINCT(agent.agent_id)');
        // 获取分页记录
        $agent_list = $model->table('agent,member')
            ->join('left')
            ->field('*')
            ->on('agent.member_id=member.member_id')
            ->page(10, $agent_total)
            ->select(array(
                'where' => $condition,
                'group' => 'agent.agent_id',
                'order' => 'agent.agent_id asc',
            ));
        //格式化
        $agent_list = $this->_formartList($agent_list);
        $list_count = count($agent_list);
        $store_condition['store.store_state']=1;
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']): null;
        if ($start_unixtime || $end_unixtime) {
            $store_condition['store.store_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        for ($i=0;$i< $list_count;$i++) {
            $agent_id=$agent_list[$i]["agent_id"];
            $agent_grade=$agent_list[$i]["agent_grade"];
            unset($store_condition['agent_store.agent_id_1']);
            unset($store_condition['agent_store.agent_id_2']);
            // 获取商户总数
            if ($agent_grade==3) {
                $store_condition['agent_store.agent_id_1']=$agent_id;
                $store_total = $model->table('agent_store,store')
                    ->join('left')
                    ->on('agent_store.store_id=store.store_id')
                    ->where($store_condition)
                    ->group('agent_store.agent_id_1')
                    ->count('DISTINCT(store.store_id)');
                $agent_list[$i]["store_count"]= $store_total>0?$store_total:0;
            }
            else {
                $store_condition['agent_store.agent_id_2']=$agent_id;
                $store_total = $model->table('agent_store,store')
                    ->join('left')
                    ->on('agent_store.store_id=store.store_id')
                    ->where($store_condition)
                    ->group('agent_store.agent_id_2')
                    ->count('DISTINCT(store.store_id)');
                $agent_list[$i]["store_count"]=  $store_total>0?$store_total:0;
            }
        }
        //商户总数
        $condition_total = array();
        $condition_total['store_time'] = array('time',array($start_unixtime,$end_unixtime));
        $condition_total['store_state'] = 1;
        $store_sum = Model('store')->where($condition_total)->count('DISTINCT(store_id)');
        Tpl::output('total',$store_sum);
        Tpl::output('member_name',$_GET['member_name']);
        Tpl::output('query_start_time',$_GET['query_start_time']);
        Tpl::output('query_end_time',$_GET['query_end_time']);
        Tpl::output('agent_list',$agent_list);
        pagecmd('settotalnum',$agent_total);
        pagecmd('seteachnum',10);
        $show_page= pagecmd('show',null);
        Tpl::output('page',$show_page);
        Tpl::showpage('store_manage');
    }
    /**
     * 用户统计
     */
    public function member_countOp(){
        $member_sum = Model()->table('member_copy')->where("LENGTH(member_name)=11 and member_name REGEXP('^[0-9]*$') and city is   null and member_id > 115855")->count();
        $member_list=Model()->table('member_copy')->where("LENGTH(member_name)=11 and member_name REGEXP('^[0-9]*$') and city is   null and member_id > 115855")->limit('0,1000')->getfield(2);
        for ($i=1;$i<ceil($member_sum/1000);$i++) {
            $begin = $i*1000;
            $tempList = Model()->table('member_copy')->where("LENGTH(member_name)=11 and member_name REGEXP('^[0-9]*$') and city is   null and member_id > 115855")->limit("$begin,1000")->getfield(2);
            $member_list =array_merge($member_list,$tempList);
        }
        $ch = curl_init();
        $header = array(
            'apikey:805af2bbd64756bb913432f53219bdaa',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        for ($j=0;$j<count($member_list);$j++) {
            $url = 'http://apis.baidu.com/apistore/mobilenumber/mobilenumber?phone='.$member_list[$j];
            // 执行HTTP请求
            curl_setopt($ch , CURLOPT_URL , $url);
            $res = curl_exec($ch);
            $infoArray = json_decode($res,true);
            if (!empty($infoArray['retData']['city'])) {
                $update_array = array();
                $update_array['city'] = $infoArray['retData']['city'];
                Model()->table('member_copy')->where("member_name = $member_list[$j]")->update($update_array);
            }
            usleep(100000);
        }
        var_dump(123);
    }

	/**
	 * 代理商添加
	 */
	public function agent_addOp(){
		$lang	= Language::getLangContent();
		$model_agent = Model('agent');
		if (chksubmit()){
			//验证
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
                            array("input"=>$_POST["agent_company_name"], "require"=>"true", "message"=>$lang['agent_company_name_no_null']),
                            array("input"=>$_POST["agent_member_name"], "require"=>"true", "message"=>$lang['agent_member_name_no_null']),
			);
			$error = $obj_validate->validate();
                      

                        
			if ($error != ''){
			    showMessage($error);
			} else {
                            
                                $param = $_POST;
                                
                                if (!isset($param['agent_area_name']) || empty($param['agent_area_name'])) {
                                    showMessage($lang['agent_area_name_no_null']);
                                }
                                
                                $param['agent_area_arr'][0] = $param['agent_area_name'];
                                
                                $this->_valid_field($param);
                                
                                // 区域重复验证
                                $is_exist_area = $model_agent->is_exist_agent_area_list($param);
                                if ($is_exist_area) {
                                    showMessage($lang['agent_agent_area_is_there']);
                                }
                                
                                $member_id = $this->_get_member_id($param, 'add'); //新增逻辑

                                if ($member_id) {
                                    $param['member_id'] = (int)$member_id;
                                    
                                    // 代理商账号唯一性校验
                                    $exist_info = $model_agent->getAgentInfo(array('member_id' => (int)$member_id));
                                    if (!empty($exist_info)) {
                                         showMessage($lang['agent_member_name_is_there']);
                                    }
                                    
                                    $result = $model_agent->addAgent($param);
                               	    if ($result){
					$url = array(
                                            array(
                                                'url'=>'index.php?act=agent&op=agent_add',
                                                'msg'=>$lang['continue_add_agent'],
                                            ),
                                            array(
                                            'url'=>'index.php?act=agent&op=agent',
                                            'msg'=>$lang['back_agent_list'],
                                            )
					);
					$this->log(L('nc_add,agent').'['.$result.']',1);
					showMessage($lang['nc_common_save_succ'],$url,'html','succ',1,5000);
                                     }  
                                }
                               
				showMessage($lang['nc_common_save_fail']);
			
			}
		}
		Tpl::showpage('agent.add');
	}

	/**
	 * 编辑
	 */
	public function agent_editOp(){
		$lang	= Language::getLangContent();

		$model_agent = Model('agent');

		if (chksubmit()) {
                        
                        $param = $_POST;
                        $this->_valid_field($param);
                        $update_array = array();
                        $update_array['contactor']  = $param['contactor'];
                        $update_array['tel']        = $param['tel'];
                        $update_array['email']      = $param['email'];
                        $update_array['content']    = $param['content'];
                        $update_array['remark']     = $param['remark'];
    
                        $update_agent_arr = array(
                            'agent_status' => (int)$param['agent_status'],
                            'check_out'    => (int)$param['check_out'],
                            'agent_predeposit'  => (int)$param['agent_predeposit'],
                        );
                        $result        = $model_agent->editAgent($update_agent_arr, array('agent_id'=>intval($param['agent_id'])));
                        $result_extend = $model_agent->editAgentExtend($update_array,array('agent_id'=> (int)$param['agent_id']));
                        
                        //新增代理区域
                        if (isset($param['agent_area_arr']) && !empty($param['agent_area_arr'])) {
                            $is_add_area = $model_agent->addBatchAgentArea($param);
                            if (!$is_add_area) {
                                showMessage('代理区域已存在');
                            }
                        }
                        
                        if ($result && $result_extend){
                            $this->log(L('nc_edit,agent').'['. (int)$param['agent_id'].']', 1);
                            showMessage($lang['nc_common_save_succ'],'index.php?act=agent&op=agent');
                        }else {
                            showMessage($lang['nc_common_save_fail']);
                        }
                }
	
                $param = array('agent_id'=>intval($_GET['agent_id']));
		$agent_array = $model_agent->getAgentFullInfo($param);

		if (empty($agent_array)){
			showMessage($lang['illegal_parameter']);
		}

                $member_array = Model('member')->getMemberInfo(array('member_id' => $agent_array['member_id']));

                Tpl::output('agent_area', $this->_getAgentArea($param));
		Tpl::output('agent_array', $agent_array);
                Tpl::output('member_array', $member_array);
		Tpl::showpage('agent.edit');
	}
        
        
	/**
	 * ajax操作
	 */
	public function ajaxOp(){
	    $model_agent = Model('agent');
	    $update_array = array();
		switch ($_GET['branch']){
			//公司名称是否重复
			case 'check_agent_company_name':
//			        $condition = array();
//				$condition['agent_company_name'] = $_GET['agent_company_name'];
//                                if (isset($_GET['agent_id'])) {
//                                    $condition['agent_id'] = array('agent_id'=>array('neq',intval($_GET['agent_id'])));
//                                }
//                                $class_list = $model_agent->getAgentList($condition);
//				$return = empty($class_list) ? 'true' : 'false';
                                // 公司名称允许相同  lijunhua 2015-09-28
                                $return = 'true';
				break;

//			// 区域重复
//			case 'check_agent_area_name':
//                                $condition['agent_grade']     = $_GET['agent_grade'];
//				$condition['agent_area_name'] = explode(' ', $_GET['agent_area_name']);
//                                
//				$class_list = Model('agent_area')->getAgentAreaInfo($condition);
//				$return = true;//empty($class_list) ? 'true' : 'false';
//				break;     
		}
		exit($return);
	}
       
        
        private function _getAgentArea($param)  
        {
            
            $list = Model('agent_area')->getAgentAreaList(array('agent_id' => $param['agent_id']));
            $result  =  '';
            if (!empty($list)) {
                foreach ((array)$list as  $value) {
                    $result .= "<span class='area_city_span'>" . $value['province'] . " " . $value['city'] . $value['area'] . "</span>"; 
                }
            }
            return $result;
        }
        
        /**
         * 字段格式化处理
         * 
         * @param array $agent_list
         * @return array
         */
        private function _formartList($agent_list) {
            if (empty($agent_list)) {
                return array();
            }

            $area_hash = Model('agent_area');
            $area_list = array();
            foreach ((array)$agent_list as $key => $data){
                $area_list = $area_hash->getAgentAreaList(array('agent_id' => $data['agent_id']));
                if (!empty($area_list)) {
                    foreach ((array)$area_list as $v) {
                        $agent_list[$key]['area_list'][] = $v['province'] . ' ' . $v['city'] . ' ' . $v['area'] . ' '. $v['street'];;
                    }
                }
            }
            return $agent_list;
        }
        
       /**
        * 校验区域
        * 
        * @param array $param
        * @return boolean
        */
       private function _filter_area_arr() {
           if (isset($_POST['agent_area_arr'] )) {
               foreach ((array)$_POST['agent_area_arr'] as $key => $value) {
                  $_POST['agent_area_arr'][$key] = preg_replace('|-请选择-|U', '', $value);
               }
           }
       }  
       
        /**
         * 合法性校验
         * 
         * @param array $param
         */
        private function _valid_field($param)
        {
              if (isset($param['tel']) && !empty($param['tel'])) {
                  if (!preg_match('/1\d{10}|0\d{2,3}[|\-]\d{7,8}$/', $param['tel'])) {
                      showMessage('电话格式错误');
                  }
              }
              
              if (isset($param['email']) && !empty($param['email'])) {
                  if (!preg_match('/^([.a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\\.[a-zA-Z0-9_-])+/', $param['email'])) {
                      showMessage('邮件格式错误');
                  }
              }
        }
        
        
        /**
         * 获取代理商账号ID
         * 
         * @param  array  $param 
         * @param  string $type
         */
        private function _get_member_id($param, $type='')
        {
            $model_member = Model('member');

            // 验证用户名是否重复
            $check_member_name = $model_member->getMemberInfo(array('member_name' => $param['agent_member_name']));
            if (is_array($check_member_name) && count($check_member_name) > 0) {
                $member_id = $check_member_name['member_id'];
                
                
                //新建代理商账号不可以和员工账号重复
                if ($type == 'add') {
                    $exist_manager = Model('agent_manager')->getAgentManagerInfo(array('member_id' => (int)$member_id));
                    if ($exist_manager) {
                        showMessage('此账号已被使用');
                    }
                }

            } else {
                // 创建一个会员用户
                $member_info['member_name']		= $param['agent_member_name'];
                $member_info['member_passwd']		= '123456';
                $member_info['member_email']		= '';
                $member_info['firest_inviter']		= 0;
                $member_info['second_inviter']		= 0;
                $member_info['max_member']	        = $model_member->getinviter();//会员邀请码
                $member_id = $model_member->addMember($member_info);
            }
            return $member_id;
        }
        
        /**
         * 验证区域是否重复

         * @param string $param['agent_area_name'] 格式如：安徽 合肥市 庐阳区
         * @param string $param['agent_grade']     代理级别
         * @return bool
         */
        private function _is_exist_agent_area($param)
        {
            $info = $this->_getAreaStrToArr($param);
            $result = Model('agent_area')->getAgentAreaList($info);
            return empty($result) ? false : true;
        }
        
        /**
         * 区域字符串转数组
         * @param type $param
         * @return type
         */
        private function _getAreaStrToArr($param)
        {
            $area_arr	        = explode(' ', $param['agent_area_name']);
            $info = array();
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
            }
            
            return $info;
        }
        
        
}
