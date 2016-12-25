<?php
/**
 * 店铺管理界面
 **
 */
defined('emall') or exit('Access Invalid!');

class storeControl extends SystemControl{
	const EXPORT_SIZE = 1000;
	public function __construct(){
		parent::__construct();
		Language::read('store,store_grade');
	}

    public function adt_add_storeOp(){
        if (chksubmit()){
            //检查参数是否完整
            $request=array(
                'data.member_name',
                'data.true_name',
                'data.province_id',
                'data.city_id',
                'data.area_id',
                'data.province_name',
                'data.city_name',
                'data.area_name',
                'data.address_detail',  //详细地址
                'data.store_name',
                'data.contact_phone',   //联系电话
                'data.lat',               //纬度
                'data.lng',                //经度
                'data.licence_number',
                'data.legal_person',    //法人
                'data.bank_name',       //银行名
                'data.bank_no',         //银行帐号
                'data.bank_user',         //银行开户人姓名
            );
            $request_check=check_request($request);
            if(!$request_check){
                showMessage('参数错误,缺少参数','','javascript');
//                echo json_encode(array('res'=>0,'reson'=>'参数错误,缺少参数'));die;
            }

            $p_member_name=$_REQUEST['data']['member_name'];
            $p_member_truename=$_REQUEST['data']['true_name'];
            $p_provinceid=$_REQUEST['data']['province_id'];
            $p_cityid=$_REQUEST['data']['city_id'];
            $p_areaid=$_REQUEST['data']['area_id'];
            $p_province_name=$_REQUEST['data']['province_name'];
            $p_city_name=$_REQUEST['data']['city_name'];
            $p_area_name=$_REQUEST['data']['area_name'];
            $p_address_detail=$_REQUEST['data']['address_detail'];
            $p_store_name=$_REQUEST['data']['store_name'];
            $p_contact_phone=$_REQUEST['data']['contact_phone'];
            $p_lat=$_REQUEST['data']['lat'];
            $p_lng=$_REQUEST['data']['lng'];
            $p_licence_number=$_REQUEST['data']['licence_number'];
            $p_legal_person=$_REQUEST['data']['legal_person'];
            $p_bank_name=$_REQUEST['data']['bank_name'];
            $p_bank_no=$_REQUEST['data']['bank_no'];
            $p_bank_user=$_REQUEST['data']['bank_user'];
            $ship_time='';
            if(!(0==intval($_REQUEST['b_hour']) && 0==intval($_REQUEST['b_minute']) && 0==intval($_REQUEST['e_hour']) && 0==intval($_REQUEST['e_minute']))){
                $ship_time=($_REQUEST['b_hour']).':'.($_REQUEST['b_minute']).'-'.($_REQUEST['e_hour']).':'.($_REQUEST['e_minute']);
            }


            if(!is_numeric($p_member_name)){
                  showMessage('用户名必须为手机号','','javascript');
//                echo json_encode(array('res'=>0,'reson'=>'用户名必须为手机号'));die;
            }

            Model::beginTransaction();
            try {
                $member_info = array();
                $member_name_check = Model('seller')->adt_check_mobile($p_member_name, $member_info);
                if (0 == $member_name_check) {
                    throw new Exception('此帐号已拥有店铺，不能重复创建');
                }
                if (1 == $member_name_check) {
                    //不需要密码
                    $member_id = $member_info['member_id'];

                }
                if (2 == $member_name_check) {
                    //需要密码
                    if (!check_request(array('data.member_passwd'))) {
                        throw new Exception('参数错误,缺少参数data[member_passwd]');
                    }
                    $p_member_passwd = $_REQUEST['data']['member_passwd'];

                    //1.会员表添加数据，生成邀请码,member_common表添加数据
                    $member_data = array();
                    $member_data['member_name'] = $p_member_name;
                    $salt = random_str(8, FALSE);
                    $member_data['member_passwd'] = md6($p_member_passwd, $salt);
                    $member_data['member_salt'] = $salt;
                    $member_data['member_truename'] = $p_member_truename;
                    $member_data['member_areainfo'] = $p_province_name . ' ' . $p_city_name . ' ' . $p_area_name . ' ' . $p_address_detail;
                    $member_data['member_provinceid'] = $p_provinceid;
                    $member_data['member_cityid'] = $p_cityid;
                    $member_data['member_areaid'] = $p_areaid;
                    $member_data['invitation'] = Model('member')->getinviter();
                    $member_data['member_time'] = time();
                    $member_data['member_type'] =10;
                    $member_data['bank_card_bind'] =1;
                    $member_id = Model('member')->insert($member_data);
                    if (!$member_id) {
                        throw new Exception('会员表保存失败');
                    }
                    $member_common_data=array();
                    $member_common_data['member_id']=$member_id;
                    $member_common_data['member_realname']=$p_member_truename;
                    $member_common_data['member_identity']=$p_licence_number;
                    $res = Model('member_common')->insert($member_common_data);
                    if (!$res) {
                        throw new Exception('系统错误');
                    }
                    $member_bank_data=array();
                    $member_bank_data['member_id']=$member_id;
                    $member_bank_data['pdc_bank_name']=$p_bank_name;
                    $member_bank_data['pdc_bank_no']=$p_bank_no;
                    $member_bank_data['pdc_bank_user']=$p_bank_user;
                    $member_bank_data['pdc_mobile']=$p_contact_phone;
                    $member_bank_data['pdc_add_time']=time();
                    $res = Model('member_bank_card')->insert($member_bank_data);
                    if (!$res) {
                        throw new Exception('系统错误');
                    }
                }

                //2.store表添加数据
                $store_data=array();
                $store_data['store_name']=$p_store_name;
                $store_data['store_phone']=$p_contact_phone;
                $store_data['grade_id']=3;
                $store_data['member_id']=$member_id;
                $store_data['member_name']=$p_member_name;
                $store_data['seller_name']=$p_member_name;
                $store_data['sc_id']=2;       //1.旗舰店，2.独立店
                $store_data['province_id']=$p_provinceid;
                $store_data['city_id']=$p_cityid;
                $store_data['district_id']=$p_areaid;
                $store_data['city_name']=$p_city_name;
                $store_data['district_name']=$p_area_name;
                $store_data['store_address']=$p_address_detail;
                $store_data['area_info']=$p_province_name . ' ' . $p_city_name . ' ' . $p_area_name;
                $store_data['store_state']=1;
                $store_data['store_type']=4;      //都是本土
                $store_data['lat']=$p_lat;
                $store_data['lng']=$p_lng;
                $store_data['store_zy']= '';
                $store_data['store_time']=time();
                $store_data['store_end_time'] = strtotime("+10 year");
                $store_data['adt_licence_number'] = $p_licence_number;
                $store_data['adt_licence_file'] = $this->upload_image('licence_file');
                $store_data['adt_legal_person'] = $p_legal_person;
                $store_data['ship_time']=$ship_time;
                $store_id=Model('store')->addStore($store_data);
                if(!$store_id){
                    throw new Exception('系统错误');
                }

                //3.seller表添加数据
                $seller_array = array();
                $seller_array['seller_name'] = $p_member_name;
                $seller_array['member_id'] = $member_id;
                $seller_array['seller_group_id'] = 0;
                $seller_array['store_id'] = $store_id;
                $seller_array['is_admin'] = 1;
                $seller = Model('seller')->addSeller($seller_array);
                if(!$seller){
                    throw new Exception('系统错误');
                }

                //4.如果存在区代，绑定代理商
                $agent_store_data=array();
                $agent_model = Model('agent');
                $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $p_province_name, 'city' => $p_city_name, 'area' => $p_area_name,'street'=>''));
                if(isset($agent_temp['agent_grade']) && $agent_temp['agent_grade']==4){//旧区代
                    $agent_store_data['agent_id_2'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
                    $agent_store_data['agent_member_id_2'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
                    $agent_store_data['agent_mode'] = 2;
                }else {//新代理模式，或不存在区代
                    $agent_store_data['agent_id_1'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
                    $agent_store_data['agent_member_id_1'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
                    $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $province_str, 'city' => $city_str, 'area' => ''));
                    $agent_store_data['agent_id_2'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
                    $agent_store_data['agent_member_id_2'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
                    $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $province_str, 'city' => '', 'area' => ''));
                    $agent_store_data['agent_id_3'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
                    $agent_store_data['agent_member_id_3'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
                    $agent_store_data['agent_mode'] = 1;
                }
                $agent_store_data['add_time']=time();
                $agent_store_data['store_member_id']=$member_id;
                $agent_store_data['store_id']=$store_id;
                if($agent_store_data['agent_id_1']!=0 ||(2==$agent_store_data['agent_mode']) && 0!=$agent_store_data['agent_id_2']) { //有区代才可以绑定
                    $res = Model('agent_store')->insert($agent_store_data);
                    if (!$res) {
                        throw new Exception('代理商绑定失败');
                    }
                }

                //5.添加相册默认
                $album_model = Model('album');
                $album_arr = array();
                $album_arr['aclass_name'] = '默认相册';
                $album_arr['store_id'] = $store_id;
                $album_arr['aclass_des'] = '';
                $album_arr['aclass_sort'] = '255';
                $album_arr['aclass_cover'] = '';
                $album_arr['upload_time'] = time();
                $album_arr['is_default'] = '1';
                $album_model->addClass($album_arr);

                //6.插入扩展表extend
                Model('store_extend')->insert(array('store_id'=>$store_id));


                Model::commit();
                showMessage('添加店铺成功!',urlAdmin('store','adt_add_store_success'),'redirect');
//                echo json_encode(array('res'=>1,'reson'=>'添加店铺成功'));die;
            }catch (Exception $e){
                Model::rollback();
                showMessage('添加店铺失败!','','javascript');
//                echo json_encode(array('res'=>0,'reson'=>'添加店铺失败!'.$e->getMessage()));die;
            }


        }
        Tpl::showpage('store.new');
    }

    public function adt_add_store_successOp(){

        Tpl::showpage('store.new.success');
    }

    /**
     * 检查该手机号是否可以添加商户
     * 0：不可已，1可以，但是已存在，不需要设置密码，2可以，需要设置密码
     */
    public function adt_check_mobileOp(){
        $mobile=trim($_GET['mobile']);
        $check=Model('seller')->adt_check_mobile($mobile);
        echo $check ;
    }

	/**
	 * 店铺
	 */
	public function storeOp(){
		$lang = Language::getLangContent();

		$model_store = Model('store');

		if(trim($_GET['owner_and_name']) != ''){
			$condition['member_name']	= array('like', '%'.$_GET['owner_and_name'].'%');
			Tpl::output('owner_and_name',$_GET['owner_and_name']);
		}
		if(trim($_GET['store_name']) != ''){
			$condition['store_name']	= array('like', '%'.trim($_GET['store_name']).'%');
			Tpl::output('store_name',$_GET['store_name']);
		}
		if(intval($_GET['grade_id']) > 0){
			$condition['grade_id']		= intval($_GET['grade_id']);
			Tpl::output('grade_id',intval($_GET['grade_id']));
		}
		if(intval($_GET['store_type_o2o']) > 0){
			$condition['store_type']		= intval($_GET['store_type_o2o']);
		}

        switch ($_GET['store_type']) {
            case 'close':
                $condition['store_state'] = 0;
                break;
            case 'open':
                $condition['store_state'] = 1;
                break;
            case 'expired':
                $condition['store_end_time'] = array('between', array(1, TIMESTAMP));
                $condition['store_state'] = 1;
                break;
            case 'expire':
                $condition['store_end_time'] = array('between', array(TIMESTAMP, TIMESTAMP + 864000));
                $condition['store_state'] = 1;
                break;
        }

        // 默认店铺管理不包含自营店铺
        $condition['is_own_shop'] = 0;

		//店铺列表
		$store_list = $model_store->getStoreList($condition, 10,'store_id desc');

		//店铺等级
		$model_grade = Model('store_grade');
		$grade_list = $model_grade->getGradeList($condition);
		if (!empty($grade_list)){
			$search_grade_list = array();
			foreach ($grade_list as $k => $v){
				$search_grade_list[$v['sg_id']] = $v['sg_name'];
			}
		}
        Tpl::output('search_grade_list', $search_grade_list);

        //管理员管理商户功能
        $admin_info=Model('admin')->getOneAdmin($this->admin_info['id']);
        if(!$admin_info){
            showMessage('系统异常');
        }

        $str=$admin_info['admin_id'].$admin_info['admin_name'].date('Ymd',time()).$admin_info['admin_login_time'];
        Tpl::output('key_pre',$str);
        Tpl::output('id',$admin_info['admin_id']);

		Tpl::output('grade_list',$grade_list);
		Tpl::output('store_list',$store_list);
        Tpl::output('store_type', $this->_get_store_type_array());
        Tpl::output('store_type_2', $this->_get_store_type_2_array());
		Tpl::output('page',$model_store->showpage('2'));
		Tpl::showpage('store.index');
	}

    /**
     * 导出所有商户
     */
    public function export_all_store_listOp(){
        $lang = Language::getLangContent();
        $model_store = Model('store');
        if(trim($_GET['owner_and_name']) != ''){
            $condition['member_name']	= array('like', '%'.$_GET['owner_and_name'].'%');
        }
        if(trim($_GET['store_name']) != ''){
            $condition['store_name']	= array('like', '%'.trim($_GET['store_name']).'%');
        }
        if(intval($_GET['grade_id']) > 0){
            $condition['grade_id']		= intval($_GET['grade_id']);
        }
        if(intval($_GET['store_type_o2o']) > 0){
            $condition['store_type']		= intval($_GET['store_type_o2o']);
        }
        switch ($_GET['store_type']) {
            case 'close':
                $condition['store_state'] = 0;
                break;
            case 'open':
                $condition['store_state'] = 1;
                break;
            case 'expired':
                $condition['store_end_time'] = array('between', array(1, TIMESTAMP));
                $condition['store_state'] = 1;
                break;
            case 'expire':
                $condition['store_end_time'] = array('between', array(TIMESTAMP, TIMESTAMP + 864000));
                $condition['store_state'] = 1;
                break;
        }
        // 默认店铺管理不包含自营店铺
        $condition['is_own_shop'] = 0;
        //管理员管理商户功能
        $admin_info=Model('admin')->getOneAdmin($this->admin_info['id']);
        if(!$admin_info){
            showMessage('系统异常');
        }
        if (!is_numeric($_GET['curpage']) && !is_numeric($_GET['cursection'])){
            $count = $model_store->table('store')->where($condition)->count();
            $array = array();
            if ($count > self::EXPORT_SIZE ){	//显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=store&op=store_joinin_o2o');
                Tpl::showpage('export.excel');
            }else{	//如果数量小，直接下载
                $data = $model_store->table('store')->where($condition)->limit(self::EXPORT_SIZE)->order('store_id desc')->select();
                $store_state = array('0'=>'关闭','1'=>'开启');
                foreach ($data as $k=>$v) {
                    $data[$k]['store_state'] = $store_state[$v['store_state']];
                }
                $this->createAllStore($data);
            }
        }elseif(is_numeric($_GET['cursection'])) {	//分段下载
            $limit1 = ($_GET['cursection']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_store->table('store')->where($condition)->order('store_id desc')->limit("$limit1,$limit2")->select();
            $store_state = array('0'=>'关闭','1'=>'开启');
            foreach ($data as $k=>$v) {
                $data[$k]['store_state'] = $store_state[$v['store_state']];
            }
            $this->createAllStore($data);
        }else{	//分页下载
            $limit1 = ($_GET['curpage']-1) * 20;
            $limit2 = 20;
            $data = $model_store->table('store')->where($condition)->order('store_id desc')->limit("$limit1,$limit2")->select();
            $store_state = array('0'=>'关闭','1'=>'开启');
            foreach ($data as $k=>$v) {
                $data[$k]['store_state'] = $store_state[$v['store_state']];
            }
            $this->createAllStore($data);
        }
    }
    private function _get_store_type_array() {
        return array(
            'open' => '开启',
            'close' => '关闭',
            'expire' => '即将到期',
            'expired' => '已到期'
        );
    }
    private function _get_store_type_2_array() {
        return array(
            '1' => '本土店铺',
            '2' => '线上店铺',
            '3' => '配送总店',
            '4' => '配送加盟店'
        );
    }
	/**
	 * 管理员修改店铺信息
	 */
	public function manage_store_editOp(){
		$lang = Language::getLangContent();

		$model_store = Model('store');
		//保存
		if (chksubmit()){
            if(!isset($_POST['store_commis_rates'])||empty($_POST['store_commis_rates'])) {
                showMessage($lang['commis_rates_no']);
            }
            if($_POST['store_commis_rates']<0||$_POST['store_commis_rates']>100||!is_numeric($_POST['store_commis_rates'])) {
                showMessage($lang['commis_rates_error']);
            }
            if(!isset($_POST['store_name'])||empty($_POST['store_name'])) {
                showMessage($lang['please_input_store_name']);
            }
            if(!isset($_POST['contacts_name'])||empty($_POST['contacts_name'])) {
                showMessage($lang['contacts_name_no']);
            }
			$update_array = array();
			$update_array['store_name'] = trim($_POST['store_name']);
			$update_array['store_commis_rates'] = trim($_POST['store_commis_rates']);//店铺返佣比
            //写入日志
            $store_join_model = Model('store_joinin');
            $store_array = $model_store->getStoreInfoByID($_GET['store_id']);
            $joinin_detail = $store_join_model->getStoreJoininO2o(array('member_id'=>$store_array['member_id']));
            $message = L('nc_edit,store').'['.$_GET['store_id'].']';
            if($store_array['store_name']!=$update_array['store_name']) {
                $message = $message.' 商户名称由"'.$store_array['store_name'].'"改为"'.$update_array['store_name'].'"';
            }
            if($store_array['store_commis_rates']!=$update_array['store_commis_rates']) {
                $message = $message.' 店铺返佣由"'.$store_array['store_commis_rates'].'"改为"'.$update_array['store_commis_rates'].'"';
            }
            if($_POST['contacts_name']!=$joinin_detail['contacts_name']) {
                $message = $message.' 店铺联系人由"'.$joinin_detail['contacts_name'].'"改为"'.$_POST['contacts_name'].'"';
            }
            $this->log($message,1);
            $result = $model_store->editStore($update_array, array('store_id' => $_POST['store_id']));
            $result = $store_join_model->updateStoreJoininO2o(array('id'=>$_REQUEST['id']),array('contacts_name'=>$_POST['contacts_name']));
            dcache($_POST['store_id'], 'store_info');
            //更新商品表店名
            Model('goods')->editGoods(array('store_name'=>$_POST['store_name']),array('store_id'=>$_POST['store_id']));
			if ($result){
				$url = array(
				array(
				'url'=>'index.php?act=store&op=store',
				'msg'=>$lang['back_store_list'],
				),
				array(
				'url'=>'index.php?act=store&op=manage_store_edit&store_id='.intval($_POST['store_id']),
				'msg'=>$lang['countinue_update_store'],
				),
				);
				showMessage($lang['nc_common_save_succ'],$url);
			}else {
				showMessage($lang['nc_common_save_fail']);
			}
		}
		//取店铺信息
		$store_array = $model_store->getStoreInfoByID($_GET['store_id']);
        $store_join_model = Model('store_joinin');
        $joinin_detail = $store_join_model->getStoreJoininO2o(array('member_id'=>$store_array['member_id']));
		if (empty($store_array)){
			showMessage($lang['store_no_exist']);
		}
		Tpl::output('store_array',$store_array);
        Tpl::output('joinin_detail',$joinin_detail);
        Tpl::showpage('store.edit');
	}

    /**
     * 店铺编辑
     */
    public function store_editOp(){
        $lang = Language::getLangContent();

        $model_store = Model('store');
        //保存
        if (chksubmit()){
            //取店铺等级的审核
            $model_grade = Model('store_grade');
            $grade_array = $model_grade->getOneGrade(intval($_POST['grade_id']));
            if (empty($grade_array)){
                showMessage($lang['please_input_store_level']);
            }
            //结束时间
            $time	= '';
            if(trim($_POST['end_time']) != ''){
                $time = strtotime($_POST['end_time']);
            }
            $update_array = array();
            $update_array['store_name'] = trim($_POST['store_name']);
            $update_array['sc_id'] = intval($_POST['sc_id']);
            $update_array['grade_id'] = intval($_POST['grade_id']);
            $update_array['store_end_time'] = $time;
            $update_array['store_state'] = intval($_POST['store_state']);
            $update_array['store_baozh'] = trim($_POST['store_baozh']);//保障服务开关
            $update_array['store_baozhopen'] = trim($_POST['store_baozhopen']);//保证金显示开关
            $update_array['store_baozhrmb'] = trim($_POST['store_baozhrmb']);//新加保证金-金额
            $update_array['store_qtian'] = trim($_POST['store_qtian']);//保障服务-七天退换
            $update_array['store_zhping'] = trim($_POST['store_zhping']);//保障服务-正品保证
            $update_array['store_erxiaoshi'] = trim($_POST['store_erxiaoshi']);//保障服务-两小时发货
            $update_array['store_tuihuo'] = trim($_POST['store_tuihuo']);//保障服务-退货承诺
            $update_array['store_shiyong'] = trim($_POST['store_shiyong']);//保障服务-试用
            $update_array['store_xiaoxie'] = trim($_POST['store_xiaoxie']);//保障服务-消协
            $update_array['store_huodaofk'] = trim($_POST['store_huodaofk']);//保障服务-货到付款
            $update_array['store_shiti'] = trim($_POST['store_shiti']);//保障服务-实体店铺
            if ($update_array['store_state'] == 0){
                //根据店铺状态修改该店铺所有商品状态
                $model_goods = Model('goods');
                $model_goods->editProducesOffline(array('store_id' => $_POST['store_id']));
                $update_array['store_close_info'] = trim($_POST['store_close_info']);
                $update_array['store_recommend'] = 0;
            }else {
                //店铺开启后商品不在自动上架，需要手动操作
                $update_array['store_close_info'] = '';
                $update_array['store_recommend'] = intval($_POST['store_recommend']);
            }
            $result = $model_store->editStore($update_array, array('store_id' => $_POST['store_id']));
            if ($result){
                $url = array(
                    array(
                        'url'=>'index.php?act=store&op=store',
                        'msg'=>$lang['back_store_list'],
                    ),
                    array(
                        'url'=>'index.php?act=store&op=store_edit&store_id='.intval($_POST['store_id']),
                        'msg'=>$lang['countinue_update_store'],
                    ),
                );
                $this->log(L('nc_edit,store').'['.$_POST['store_name'].']',1);
                showMessage($lang['nc_common_save_succ'],$url);
            }else {
                $this->log(L('nc_edit,store').'['.$_POST['store_name'].']',1);
                showMessage($lang['nc_common_save_fail']);
            }
        }
        //取店铺信息
        $store_array = $model_store->getStoreInfoByID($_GET['store_id']);
        if (empty($store_array)){
            showMessage($lang['store_no_exist']);
        }
        //整理店铺内容
        $store_array['store_end_time'] = $store_array['store_end_time']?date('Y-m-d',$store_array['store_end_time']):'';
        //店铺分类
        $model_store_class = Model('store_class');
        $parent_list = $model_store_class->getStoreClassList(array(),'',false);
        //店铺等级
        $model_grade = Model('store_grade');
        $grade_list = $model_grade->getGradeList();
        Tpl::output('grade_list',$grade_list);
        Tpl::output('class_list',$parent_list);
        Tpl::output('store_array',$store_array);

        $joinin_detail = Model('store_joinin')->getOne(array('member_id'=>$store_array['member_id']));
        Tpl::output('joinin_detail', $joinin_detail);
        Tpl::showpage('store.online.edit');
    }

    /**
     * 编辑保存注册信息
     */
    public function edit_save_joininOp() {
        if (chksubmit()) {
            $member_id = $_POST['member_id'];
            if ($member_id <= 0) {
                showMessage(L('param_error'));
            }
            $param = array();
            $param['company_name'] = $_POST['company_name'];
            $param['company_province_id'] = intval($_POST['province_id']);
            $param['company_address'] = $_POST['company_address'];
            $param['company_address_detail'] = $_POST['company_address_detail'];
            $param['company_phone'] = $_POST['company_phone'];
            $param['company_employee_count'] = intval($_POST['company_employee_count']);
            $param['company_registered_capital'] = intval($_POST['company_registered_capital']);
            $param['contacts_name'] = $_POST['contacts_name'];
            $param['contacts_phone'] = $_POST['contacts_phone'];
            $param['contacts_email'] = $_POST['contacts_email'];
            $param['business_licence_number'] = $_POST['business_licence_number'];
            $param['business_licence_address'] = $_POST['business_licence_address'];
            $param['business_licence_start'] = $_POST['business_licence_start'];
            $param['business_licence_end'] = $_POST['business_licence_end'];
            $param['business_sphere'] = $_POST['business_sphere'];
            if ($_FILES['business_licence_number_electronic']['name'] != '') {
                $param['business_licence_number_electronic'] = $this->upload_image('business_licence_number_electronic');
            }
            $param['organization_code'] = $_POST['organization_code'];
            if ($_FILES['organization_code_electronic']['name'] != '') {
                $param['organization_code_electronic'] = $this->upload_image('organization_code_electronic');
            }
            if ($_FILES['general_taxpayer']['name'] != '') {
                $param['general_taxpayer'] = $this->upload_image('general_taxpayer');
            }
            $param['bank_account_name'] = $_POST['bank_account_name'];
            $param['bank_account_number'] = $_POST['bank_account_number'];
            $param['bank_name'] = $_POST['bank_name'];
            $param['bank_code'] = $_POST['bank_code'];
            $param['bank_address'] = $_POST['bank_address'];
            if ($_FILES['bank_licence_electronic']['name'] != '') {
                $param['bank_licence_electronic'] = $this->upload_image('bank_licence_electronic');
            }
            $param['settlement_bank_account_name'] = $_POST['settlement_bank_account_name'];
            $param['settlement_bank_account_number'] = $_POST['settlement_bank_account_number'];
            $param['settlement_bank_name'] = $_POST['settlement_bank_name'];
            $param['settlement_bank_code'] = $_POST['settlement_bank_code'];
            $param['settlement_bank_address'] = $_POST['settlement_bank_address'];
            $param['tax_registration_certificate'] = $_POST['tax_registration_certificate'];
            $param['taxpayer_id'] = $_POST['taxpayer_id'];
            if ($_FILES['tax_registration_certificate_electronic']['name'] != '') {
                $param['tax_registration_certificate_electronic'] = $this->upload_image('tax_registration_certificate_electronic');
            }
            $result = Model('store_joinin')->editStoreJoinin(array('member_id' => $member_id), $param);
            if ($result) {
                showMessage(L('nc_common_op_succ'), 'index.php?act=store&op=store');
            } else {
                showMessage(L('nc_common_op_fail'));
            }
        }
    }
    
    private function upload_image($file) {
        $pic_name = '';
        $upload = new UploadFile();
        $uploaddir = ATTACH_PATH.DS.'store_joinin'.DS;
        $upload->set('default_dir',$uploaddir);
        $upload->set('allow_type',array('jpg','jpeg','gif','png'));
        if (!empty($_FILES[$file]['name'])){
            $result = $upload->upfile($file);
            if ($result){
                $pic_name = $upload->file_name;
                $upload->file_name = '';
            }
        }
        return $pic_name;
    }
    
    /**
     * 店铺经营类目管理
     */
    public function store_bind_classOp() {
        $store_id = intval($_GET['store_id']);

        $model_store = Model('store');
        $model_store_bind_class = Model('store_bind_class');

        $store_info = $model_store->getStoreInfoByID($store_id);
        if(empty($store_info)) {
            showMessage(L('param_error'),'','','error');
        }
        switch($store_info['store_type']){
            case 1:
                $class_type=1;
                break;
            case 2:
                $class_type=0;
                break;
            case 3:
                $class_type=3;
                break;
            default:
                $class_type=0;
        }

        $model_goods_class = Model('goods_class');
        $gc_list = $model_goods_class->getGoodsClassListByParentId(0,$class_type);
        Tpl::output('gc_list',$gc_list);

        Tpl::output('store_info', $store_info);
        $store_bind_class_list = $model_store_bind_class->getStoreBindClassList(array('store_id'=>$store_id,'state'=>array('in',array(1,2))), null);
        $goods_class = Model('goods_class')->getGoodsClassIndexedListAll($class_type);
        for($i = 0, $j = count($store_bind_class_list); $i < $j; $i++) {
            $store_bind_class_list[$i]['class_1_name'] = $goods_class[$store_bind_class_list[$i]['class_1']]['gc_name'];
            $store_bind_class_list[$i]['class_2_name'] = $goods_class[$store_bind_class_list[$i]['class_2']]['gc_name'];
            $store_bind_class_list[$i]['class_3_name'] = $goods_class[$store_bind_class_list[$i]['class_3']]['gc_name'];
/*            if (!empty($store_bind_class_list[$i]['class_1_name'])) {
                $store_bind_class_list[$i]['distribution_rate'] = $goods_class[$store_bind_class_list[$i]['class_1']]['distribution_rate'];
            }
            if (!empty($store_bind_class_list[$i]['class_2_name'])) {
                $store_bind_class_list[$i]['distribution_rate'] = $goods_class[$store_bind_class_list[$i]['class_2']]['distribution_rate'];
            }
            if (!empty($store_bind_class_list[$i]['class_3_name'])) {
                $store_bind_class_list[$i]['distribution_rate'] = $goods_class[$store_bind_class_list[$i]['class_3']]['distribution_rate'];
            }*/
        }
        Tpl::output('store_bind_class_list', $store_bind_class_list);

        Tpl::showpage('store_bind_class');
    }

    /**
     * 添加经营类目
     */
    public function store_bind_class_addOp() {
        $store_id = intval($_POST['store_id']);
        $commis_rate = floatval($_POST['commis_rate']);
        if($commis_rate < 0 || $commis_rate > 100) {
            showMessage(L('param_error'), '');
        }
        list($class_1, $class_2, $class_3) = explode(',', $_POST['goods_class']);

        $model_store_bind_class = Model('store_bind_class');

        $param = array();
        $param['store_id'] = $store_id;
        $param['class_1'] = $class_1;
        $param['state'] = 1;
        if(!empty($class_2)) {
            $param['class_2'] = $class_2;
        }
        if(!empty($class_3)) {
            $param['class_3'] = $class_3;
        }

        // 检查类目是否已经存在
        $store_bind_class_info = $model_store_bind_class->getStoreBindClassInfo($param);
        if(!empty($store_bind_class_info)) {
            showMessage('该类目已经存在','','','error');
        }

        $param['commis_rate'] = $commis_rate;
        $result = $model_store_bind_class->addStoreBindClass($param);

        if($result) {
            $this->log('删除店铺经营类目，类目编号:'.$result.',店铺编号:'.$store_id);
            showMessage(L('nc_common_save_succ'), '');
        } else {
            showMessage(L('nc_common_save_fail'), '');
        }
    }

    /**
     * 删除经营类目
     */
    public function store_bind_class_delOp() {
        $bid = intval($_POST['bid']);

        $data = array();
        $data['result'] = true;

        $model_store_bind_class = Model('store_bind_class');
        $model_goods = Model('goods');

        $store_bind_class_info = $model_store_bind_class->getStoreBindClassInfo(array('bid' => $bid));
        if(empty($store_bind_class_info)) {
            $data['result'] = false;
            $data['message'] = '经营类目删除失败';
            echo json_encode($data);die;
        }

        // 商品下架
        $condition = array();
        $condition['store_id'] = $store_bind_class_info['store_id'];
        $gc_id = $store_bind_class_info['class_1'].','.$store_bind_class_info['class_2'].','.$store_bind_class_info['class_3'];
        $update = array();
        $update['goods_stateremark'] = '管理员删除经营类目';
        $condition['gc_id'] = array('in', rtrim($gc_id, ','));
        $model_goods->editProducesLockUp($update, $condition);

        $result = $model_store_bind_class->delStoreBindClass(array('bid'=>$bid));

        if(!$result) {
            $data['result'] = false;
            $data['message'] = '经营类目删除失败';
        }
        $this->log('删除店铺经营类目，类目编号:'.$bid.',店铺编号:'.$store_bind_class_info['store_id']);
        echo json_encode($data);die;
    }

    public function store_bind_class_updateOp() {
        $bid = intval($_GET['id']);
        if($bid <= 0) {
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('param_error')));
            die;
        }
        $new_commis_rate = floatval($_GET['value']);
        if ($new_commis_rate < 0 || $new_commis_rate >= 100) {
            echo json_encode(array('result'=>FALSE,'message'=>Language::get('param_error')));
            die;
        } else {
            $update = array('commis_rate' => $new_commis_rate);
            $condition = array('bid' => $bid);
            $model_store_bind_class = Model('store_bind_class');
            $result = $model_store_bind_class->editStoreBindClass($update, $condition);
            if($result) {
                $this->log('更新店铺经营类目，类目编号:'.$bid);
                echo json_encode(array('result'=>TRUE));
                die;
            } else {
                echo json_encode(array('result'=>FALSE,'message'=>L('nc_common_op_fail')));
                die;
            }
        }
    }


	/**
	 * 店铺 待审核列表
	 */
	public function store_joininOp(){
		//店铺列表
		if(!empty($_GET['owner_and_name'])) {
			$condition['member_name'] = array('like','%'.$_GET['owner_and_name'].'%');
		}
		if(!empty($_GET['store_name'])) {
			$condition['store_name'] = array('like','%'.$_GET['store_name'].'%');
		}
		if(!empty($_GET['grade_id']) && intval($_GET['grade_id']) > 0) {
			$condition['sg_id'] = $_GET['grade_id'];
		}
		if(!empty($_GET['joinin_state']) && intval($_GET['joinin_state']) > 0) {
            $condition['joinin_state'] = $_GET['joinin_state'] ;
        } else {
            $condition['joinin_state'] = array('gt',0);
        }
		$model_store_joinin = Model('store_joinin');
		$store_list = $model_store_joinin->getList($condition, 10, 'joinin_state asc');
		Tpl::output('store_list', $store_list);
        Tpl::output('joinin_state_array', $this->get_store_joinin_state());

		//店铺等级
		$model_grade = Model('store_grade');
		$grade_list = $model_grade->getGradeList();
		Tpl::output('grade_list', $grade_list);

		Tpl::output('page',$model_store_joinin->showpage('2'));
		Tpl::showpage('store_joinin');
	}

	/**
	 * 经营类目申请列表
	 */
	public function store_bind_class_applay_listOp(){
	    $condition = array();

        // 不显示自营店铺绑定的类目
        if ($_GET['state'] != '') {
            $condition['state'] = intval($_GET['state']);
            if (!in_array($condition['state'], array('0', '1', )))
                unset($condition['state']);
        } else {
            $condition['state'] = array('in', array('0', '1', ));
        }

	    if(intval($_GET['store_id'])) {
	        $condition['store_id'] = intval($_GET['store_id']);
	    }

        $model_store_bind_class = Model('store_bind_class');
        $store_bind_class_list = $model_store_bind_class->getStoreBindClassList($condition, 15,'state asc,bid desc');
        $goods_class = Model('goods_class')->getGoodsClassIndexedListAll('all');
        $store_ids = array();
        for($i = 0, $j = count($store_bind_class_list); $i < $j; $i++) {
            $store_bind_class_list[$i]['class_1_name'] = $goods_class[$store_bind_class_list[$i]['class_1']]['gc_name'];
            $store_bind_class_list[$i]['class_2_name'] = $goods_class[$store_bind_class_list[$i]['class_2']]['gc_name'];
            $store_bind_class_list[$i]['class_3_name'] = $goods_class[$store_bind_class_list[$i]['class_3']]['gc_name'];
            $store_ids[] = $store_bind_class_list[$i]['store_id'];
        }
        //取店铺信息
        $model_store = Model('store');
        $store_list = $model_store->getStoreList(array('store_id'=>array('in',$store_ids)),null);
        $bind_store_list = array();
        if (!empty($store_list) && is_array($store_list)) {
            foreach ($store_list as $k => $v) {
                $bind_store_list[$v['store_id']]['store_name'] = $v['store_name'];
                $bind_store_list[$v['store_id']]['seller_name'] = $v['seller_name'];
            }
        }

        Tpl::output('bind_list', $store_bind_class_list);
        Tpl::output('bind_store_list',$bind_store_list);

	    Tpl::output('page',$model_store_bind_class->showpage('2'));
	    Tpl::showpage('store_bind_class_applay.list');
	}

	/**
	 * 审核经营类目申请
	 */
	public function store_bind_class_applay_checkOp() {
	    $model_store_bind_class = Model('store_bind_class');
	    $condition = array();
	    $condition['bid'] = intval($_GET['bid']);
	    $condition['state'] = 0;
	    $update = $model_store_bind_class->editStoreBindClass(array('state'=>1),$condition);
	    if ($update) {
	        $this->log('审核新经营类目申请，店铺ID：'.$_GET['store_id'],1);
	        showMessage('审核成功',getReferer());
	    } else {
	        showMessage('审核失败',getReferer(),'html','error');
	    }
	}

	/**
	 * 删除经营类目申请
	 */
	public function store_bind_class_applay_delOp() {
	    $model_store_bind_class = Model('store_bind_class');
	    $condition = array();
	    $condition['bid'] = intval($_GET['bid']);
	    $del = $model_store_bind_class->delStoreBindClass($condition);
	    if ($del) {
	        $this->log('删除经营类目，店铺ID：'.$_GET['store_id'],1);
	        showMessage('删除成功',getReferer());
	    } else {
	        showMessage('删除失败',getReferer(),'html','error');
	    }
	}

    private function get_store_joinin_state() {
        $joinin_state_array = array(
            STORE_JOIN_STATE_NEW => '新申请',
            STORE_JOIN_STATE_PAY => '已付款',
            STORE_JOIN_STATE_VERIFY_SUCCESS => '待付款',
            STORE_JOIN_STATE_VERIFY_FAIL => '审核失败',
            STORE_JOIN_STATE_PAY_FAIL => '付款审核失败',
            STORE_JOIN_STATE_FINAL => '开店成功',
        );
        return $joinin_state_array;
    }

    /**
     * 店铺续签申请列表
     */
    public function reopen_listOp(){
        $condition = array();
        if(intval($_GET['store_id'])) {
            $condition['re_store_id'] = intval($_GET['store_id']);
        }
        if(!empty($_GET['store_name'])) {
            $condition['re_store_name'] = $_GET['store_name'];
        }
        if ($_GET['re_state'] != '') {
            $condition['re_state'] = intval($_GET['re_state']);
        }
        $model_store_reopen = Model('store_reopen');
        $reopen_list = $model_store_reopen->getStoreReopenList($condition, 15);

        Tpl::output('reopen_list', $reopen_list);

        Tpl::output('page',$model_store_reopen->showpage('2'));
        Tpl::showpage('store_reopen.list');
    }

    /**
     * 审核店铺续签申请
     */
    public function reopen_checkOp() {
        if (intval($_GET['re_id']) <= 0) exit();
        $model_store_reopen = Model('store_reopen');
        $condition = array();
        $condition['re_id'] = intval($_GET['re_id']);
        $condition['re_state'] = 1;
        //取当前申请信息
        $reopen_info = $model_store_reopen->getStoreReopenInfo($condition);

        //取目前店铺有效截止日期
        $store_info = Model('store')->getStoreInfoByID($reopen_info['re_store_id']);
        $data = array();
        $data['re_start_time'] = strtotime(date('Y-m-d 0:0:0',$store_info['store_end_time']))+24*3600;
        $data['re_end_time'] = strtotime(date('Y-m-d 23:59:59', $data['re_start_time'])." +".intval($reopen_info['re_year'])." year");
        $data['re_state'] = 2;
        $update = $model_store_reopen->editStoreReopen($data,$condition);
        if ($update) {
            //更新店铺有效期
            Model('store')->editStore(array('store_end_time'=>$data['re_end_time']),array('store_id'=>$reopen_info['re_store_id']));
            $msg = '审核通过店铺续签申请，店铺ID：'.$reopen_info['re_store_id'].'，续签时间段：'.date('Y-m-d',$data['re_start_time']).' - '.date('Y-m-d',$data['re_end_time']);
            $this->log($msg,1);
            showMessage('续签成功，店铺有效成功延续到了'.date('Y-m-d',$data['re_end_time']).'日',getReferer());
        } else {
            showMessage('审核失败',getReferer(),'html','error');
        }
    }

    /**
     * 删除店铺续签申请
     */
    public function reopen_delOp() {
        $model_store_reopen = Model('store_reopen');
        $condition = array();
        $condition['re_id'] = intval($_GET['re_id']);
        $condition['re_state'] = array('in',array(0,1));

        //取当前申请信息
        $reopen_info = $model_store_reopen->getStoreReopenInfo($condition);
        $cert_file = BASE_UPLOAD_PATH.DS.ATTACH_STORE_JOININ.DS.$reopen_info['re_pay_cert'];
        $del = $model_store_reopen->delStoreReopen($condition);
        if ($del) {
            $QNupload=new uploadFile();
            $QNupload->delFileQny(ATTACH_STORE_JOININ.DS.$reopen_info['re_pay_cert']);
//            if (is_file($cert_file)) {
//                unlink($cert_file);
//            }
            $this->log('删除店铺续签目申请，店铺ID：'.$_GET['re_store_id'],1);
            showMessage('删除成功',getReferer());
        } else {
            showMessage('删除失败',getReferer(),'html','error');
        }
    }

	/**
	 * 审核详细页
	 */
	public function store_joinin_detailOp(){
		$model_store_joinin = Model('store_joinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id'=>$_GET['member_id']));
        $joinin_detail_title = '查看';
        if(in_array(intval($joinin_detail['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) {
            $joinin_detail_title = '审核';
        }
        if (!empty($joinin_detail['sg_info'])) {
            $store_grade_info = Model('store_grade')->getOneGrade($joinin_detail['sg_id']);
            $joinin_detail['sg_price'] = $store_grade_info['sg_price'];
        } else {
            $joinin_detail['sg_info'] = @unserialize($joinin_detail['sg_info']);
            if (is_array($joinin_detail['sg_info'])) {
                $joinin_detail['sg_price'] = $joinin_detail['sg_info']['sg_price'];
            }
        }
        Tpl::output('joinin_detail_title', $joinin_detail_title);
		Tpl::output('joinin_detail', $joinin_detail);
		Tpl::showpage('store_joinin.detail');
	}

	/**
	 * 审核
	 */
	public function store_joinin_verifyOp() {
        $model_store_joinin = Model('store_joinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id'=>$_POST['member_id']));

        switch (intval($joinin_detail['joinin_state'])) {
            case STORE_JOIN_STATE_NEW:
                $this->store_joinin_verify_pass($joinin_detail);
                break;
            case STORE_JOIN_STATE_PAY:
                $this->store_joinin_verify_open($joinin_detail);
                break;
            default:
                showMessage('参数错误','');
                break;
        }
	}

    private function store_joinin_verify_pass($joinin_detail) {
        $param = array();
        $param['joinin_state'] = $_POST['verify_type'] === 'pass' ? STORE_JOIN_STATE_VERIFY_SUCCESS : STORE_JOIN_STATE_VERIFY_FAIL;
        $param['joinin_message'] = $_POST['joinin_message'];
        $param['paying_amount'] = abs(floatval($_POST['paying_amount']));
        $param['store_class_commis_rates'] = implode(',', $_POST['commis_rate']);
        $param['open_time'] = time();
        $model_store_joinin = Model('store_joinin');
        $model_store_joinin->modify($param, array('member_id'=>$_POST['member_id']));
        if ($param['paying_amount'] > 0) {
            showMessage('店铺入驻申请审核完成','index.php?act=store&op=store_joinin');
        } else {
            //如果开店支付费用为零，则审核通过后直接开通，无需再上传付款凭证
            $joinin_detail=array_merge($joinin_detail,$param);//将新的返利比率加入
            $this->store_joinin_verify_open($joinin_detail);
        }
    }

    private function store_joinin_verify_open($joinin_detail) {//Model::beginTransaction();
        $model_store_joinin = Model('store_joinin');
        $model_store	= Model('store');
        $model_seller = Model('seller');
        $param = array();

        //验证卖家用户名是否已经存在
        if($model_seller->isSellerExist(array('seller_name' => $joinin_detail['seller_name']))) {
            showMessage('卖家用户名已存在','');
        }


        $param['joinin_state'] = $_POST['verify_type'] === 'pass' ? STORE_JOIN_STATE_FINAL : STORE_JOIN_STATE_PAY_FAIL;
        $param['joinin_message'] = $_POST['joinin_message'];
        $model_store_joinin->modify($param, array('member_id'=>$_POST['member_id']));
        if($_POST['verify_type'] === 'pass') {
            //开店
 			$shop_array		= array();
            $shop_array['member_id']	= $joinin_detail['member_id'];
            $shop_array['store_phone']=$joinin_detail['contacts_phone'];
            $shop_array['member_name']	= $joinin_detail['member_name'];
            $shop_array['seller_name'] = $joinin_detail['seller_name'];
			$shop_array['grade_id']		= $joinin_detail['sg_id'];
			$shop_array['store_name']	= $joinin_detail['store_name'];
			$shop_array['sc_id']		= $joinin_detail['sc_id'];
            $shop_array['store_company_name'] = $joinin_detail['company_name'];
			$shop_array['province_id']	= $joinin_detail['company_province_id'];
			$shop_array['area_info']	= $joinin_detail['company_address'];
			$shop_array['store_address']= $joinin_detail['company_address_detail'];
			$shop_array['store_type']= $joinin_detail['store_type'];
			$shop_array['lat']= $joinin_detail['lat'];
			$shop_array['lng']= $joinin_detail['lng'];
			$shop_array['store_zip']	= '';
			$shop_array['store_zy']		= '';
			$shop_array['store_state']	= 1;
            $shop_array['store_time']	= time();
            $shop_array['store_end_time'] = strtotime(date('Y-m-d 23:59:59', strtotime('+1 day'))." +".intval($joinin_detail['joinin_year'])." year");
            $store_id = $model_store->addStore($shop_array);

            if($store_id) {            //添加默认组
                $seller_group=array();
                $seller_group['group_name']='默认组';
                $seller_group['store_id']=$store_id;
                if (2==$joinin_detail['store_type']) {//线上
                    $seller_group['limits'] = 'store_goods_add,store_goods_online,store_goods_offline,store_spec,store_album,store_order,store_evaluate,store_setting,store_info,store_brand,store_info,store_refund,store_return,statistics_general,statistics_goods,statistics_sale,statistics_industry,statistics_flow,store_rebate,store_bill,store_msg,lottery_draw,distribution_center,distribution_center,distribution_center,distribution_center';
                    $seller_group['smt_limits'] = 'complain,goods_storage_alarm,goods_verify,goods_violation,new_order,refund,refund_auto_process,return,return_auto_process,return_auto_receipt,store_bill_affirm,store_bill_gathering,store_cost,store_expire';
                }else{
                    $seller_group['limits'] ='store_goods_add,store_goods_online,store_goods_offline,store_spec,store_album,store_order,store_vr_order,store_deliver,store_deliver_set,store_waybill,store_transport,store_setting,store_info,store_brand,store_refund,store_return,statistics_general,statistics_goods,statistics_sale,statistics_industry,statistics_flow,store_bill,store_bill,store_bill,store_msg';
                    $seller_group['smt_limits'] = 'complain,goods_storage_alarm,goods_verify,goods_violation,new_order,refund,refund_auto_process,return,return_auto_process,return_auto_receipt,store_bill_affirm,store_bill_gathering,store_cost,store_expire';
                }
                $group_id=Model('seller_group')->insert($seller_group);
                if(!$group_id){
                    showMessage('添加默认分组错误');
                }

                //写入卖家账号
                $seller_array = array();
                $seller_array['seller_name'] = $joinin_detail['seller_name'];
                $seller_array['member_id'] = $joinin_detail['member_id'];
                $seller_array['seller_group_id'] = $group_id;
                $seller_array['store_id'] = $store_id;
                $seller_array['is_admin'] = 1;
                $state = $model_seller->addSeller($seller_array);
            }else{
                showMessage('系统错误');
            }

            //自动关联代理商。如果商家所在区域没有区代，不关联
            //1.获取商家所在省市区
            $store_area=explode(' ',$joinin_detail['company_address']);
            $province_str=isset($store_area[0]) ? $store_area[0]:'';
            $city_str=isset($store_area[1]) ? $store_area[1]:'';
            $area_str=isset($store_area[2]) ? $store_area[2]:'';
            //2.查找该区域各级代理
            $agent_model = Model('agent');
            $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $province_str, 'city' => $city_str, 'area' => $area_str));
            $agent_store_data['agent_id_1'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
            $agent_store_data['agent_member_id_1'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
            $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $province_str, 'city' => $city_str, 'area' => ''));
            $agent_store_data['agent_id_2'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
            $agent_store_data['agent_member_id_2'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
            $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $province_str, 'city' => '', 'area' => ''));
            $agent_store_data['agent_id_3'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
            $agent_store_data['agent_member_id_3'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
            $agent_store_data['agent_mode'] =1;
            $agent_store_data['add_time']=time();
            $agent_store_data['store_member_id']=$joinin_detail['member_id'];
            $agent_store_data['store_id']=$store_id;
            //3.如果存在区代，绑定代理商
            if($agent_store_data['agent_id_1']!=0){ //有区代才可以绑定
                $res=Model('agent_store')->insert($agent_store_data);
                if(!$res){
                    showMessage('代理商绑定失败');
                }
            }

			if($state) {
				// 添加相册默认
				$album_model = Model('album');
				$album_arr = array();
				$album_arr['aclass_name'] = Language::get('store_save_defaultalbumclass_name');
				$album_arr['store_id'] = $store_id;
				$album_arr['aclass_des'] = '';
				$album_arr['aclass_sort'] = '255';
				$album_arr['aclass_cover'] = '';
				$album_arr['upload_time'] = time();
				$album_arr['is_default'] = '1';
				$album_model->addClass($album_arr);

				$model = Model();
				//插入店铺扩展表
				$model->table('store_extend')->insert(array('store_id'=>$store_id));
				$msg = Language::get('store_save_create_success');

                //插入店铺绑定分类表
                $store_bind_class_array = array();
                $store_bind_class = unserialize($joinin_detail['store_class_ids']);
                $store_bind_commis_rates = explode(',', $joinin_detail['store_class_commis_rates']);
                for($i=0, $length=count($store_bind_class); $i<$length; $i++) {
                    list($class1, $class2, $class3) = explode(',', $store_bind_class[$i]);
                    //计算分销返利比率
                    $distribution_rate=$temp_class_id=0;
                    $temp_class_id=($class3!=0)?$class3:(($class2!=0)?$class2:$class1);
                    if($temp_class_id>0) {
                        $temp_class_info = Model('goods_class')->getGoodsClassInfoById($temp_class_id, 'all');
                        if(!empty($temp_class_info)){
                            $distribution_rate=$temp_class_info['distribution_rate'];
                        }

                    }
                    $store_bind_class_array[] = array(
                        'store_id' => $store_id,
                        'commis_rate' => $store_bind_commis_rates[$i],
                        'distribution_rate' => $distribution_rate,
                        'class_1' => $class1,
                        'class_2' => $class2,
                        'class_3' => $class3,
                        'state' => 1
                    );
                }//var_dump( $joinin_detail['store_class_commis_rates'],$store_bind_class,$store_bind_commis_rates,$store_bind_class_array);die;
                $model_store_bind_class = Model('store_bind_class');
                $model_store_bind_class->addStoreBindClassAll($store_bind_class_array);
                showMessage('店铺开店成功','index.php?act=store&op=store_joinin');
            } else {
                showMessage('店铺开店失败','index.php?act=store&op=store_joinin');
            }
        } else {
            showMessage('店铺开店拒绝','index.php?act=store&op=store_joinin');
        }
    }

    /**
     * 提醒续费
     */
    public function remind_renewalOp() {
        $store_id = intval($_GET['store_id']);
        $store_info = Model('store')->getStoreInfoByID($store_id);
        if (!empty($store_info) && $store_info['store_end_time'] < (TIMESTAMP + 864000) && cookie('remindRenewal'.$store_id) == null) {
            // 发送商家消息
            $param = array();
            $param['code'] = 'store_expire';
            $param['store_id'] = intval($_GET['store_id']);
            $param['param'] = array();
            QueueClient::push('sendStoreMsg', $param);

            setNcCookie('remindRenewal'.$store_id, 1, 86400 * 10);  // 十天
            showMessage('消息发送成功');
        }
            showMessage('消息发送失败');
    }
    
    /**
     * 验证店铺名称是否存在
     */
    public function ckeck_store_nameOp() {
        /**
         * 实例化卖家模型
         */
        $where = array();
        $where['store_name'] = $_GET['store_name'];
        $where['store_id'] = array('neq', $_GET['store_id']);
        $store_info = Model('store')->getStoreInfo($where);
        if(!empty($store_info['store_name'])) {
            echo 'false';
        } else {
            echo 'true';
        }
    }


    /**
     * 本土店铺申请详情
     */
    public function store_joinin_o2o_detailOp(){
        $model_store_joinin = Model('store_o2o_joinin');
        if(isset($_GET['id'])) {
            $joinin_detail = $model_store_joinin->find($_GET['id']);
        }elseif(isset($_GET['member_id'])){
            $joinin_detail = $model_store_joinin->where(array('member_id'=>$_GET['member_id']))->find();
        }
        $joinin_detail_title = '查看';
        if(in_array(intval($joinin_detail['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY))) {
            $joinin_detail_title = '审核';
        }
        Tpl::output('joinin_detail_title', $joinin_detail_title);
        Tpl::output('joinin_detail', $joinin_detail);
        Tpl::showpage('store_joinin_o2o.detail');
    }

    /**
     * 本土店铺申请、审核
     */
    public function store_joinin_o2o_verifyOp() {
        if(empty($_POST['id'])) showMessage('系统错误');
        //拒绝开店流程
        $param = array();
        $param['joinin_state'] = $_POST['verify_type'] === 'pass' ? STORE_JOIN_STATE_FINAL : STORE_JOIN_STATE_VERIFY_FAIL;
        $param['joinin_message'] = $_POST['joinin_message'];
        $model_store_joinin = Model('store_o2o_joinin');
        $joinin_detail = $model_store_joinin->find($_POST['id']);
        if(empty($joinin_detail)) showMessage('系统异常');
        if(STORE_JOIN_STATE_VERIFY_FAIL==$param['joinin_state']){
            //被拒绝，只更新申请表
            $model_store_joinin->update($param, array('where' => array('id' => $_POST['id'])));
            showMessage('操作成功','index.php?act=store&op=store_joinin_o2o');
        }

        //同意开店流程
        if(empty($_POST['name']) || empty($_POST['commis_rate'])) showMessage('请输入完整信息');
        if(!is_numeric($_POST['name'])) showMessage('用户名必须为手机号');
        //判断会员名是否重复
        $add_member=true;
        $member=Model('member')->where(array('member_name'=>$_POST['name']))->find();
        if(!empty($member)){
            $add_member=false;
            $member_id=$member['member_id'];
//            showMessage('用户名'.$_POST['name'].'已被占用');
        }
        $seller=Model('seller')->select(array('where'=>array('seller_name'=>$_POST['name'])));

        if(!empty($seller)) showMessage('卖家账号'.$_POST['name'].'已被占用');
        if(!empty($_POST['agent_name'])){
            $sql='select * from agg_agent where agent_status=1 and agent_grade in (3,4,5) and agent_company_name="'.$_POST['agent_name'].'" limit 1';
            $agent_info=Model()->query($sql);
            if(empty($agent_info)) showMessage('代理商'.$_POST['agent_name'].'不存在');
            $agent_id=$agent_info[0]['agent_id'];
            $param['agent_id']=$agent_id;
            $param['agent_name']=$_POST['agent_name'];
            $agent_info=$agent_info[0];
        }

        Model::beginTransaction();
        try {
            $money_presents=Model('store_pre_deposit_set')->find();
            $now=time();
            $money=0;
            //1.获取商家所在省市区
            $store_area=explode(' ',$joinin_detail['address']);
            $province_str=isset($store_area[0]) ? $store_area[0]:'';
            $city_str=isset($store_area[1]) ? $store_area[1]:'';
            $area_str=isset($store_area[2]) ? $store_area[2]:'';
            if(empty($province_str)||empty($city_str)||empty($area_str)){
                throw new Exception('请选择所在省市区');
            }
            //2.查找该区域各级代理
            $agent_model = Model('agent');
            $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $province_str, 'city' => $city_str, 'area' => $area_str,'street'=>''));
            if(isset($agent_temp['agent_grade']) && $agent_temp['agent_grade']==4){
                $agent_store_data['agent_id_2'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
                $agent_store_data['agent_member_id_2'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
                $agent_store_data['agent_mode'] = 2;
            }else {
                $agent_store_data['agent_id_1'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
                $agent_store_data['agent_member_id_1'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
                $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $province_str, 'city' => $city_str, 'area' => ''));
                $agent_store_data['agent_id_2'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
                $agent_store_data['agent_member_id_2'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
                $agent_temp = $agent_model->getAgentInfoByArea(array('province' => $province_str, 'city' => '', 'area' => ''));
                $agent_store_data['agent_id_3'] = ($agent_temp) ? $agent_temp['agent_id'] : 0;
                $agent_store_data['agent_member_id_3'] = ($agent_temp) ? $agent_temp['member_id'] : 0;
                $agent_store_data['agent_mode'] = 1;
            }
            $agent_store_data['add_time']=time();
            //3.如果存在区代
            if($agent_store_data['agent_id_1']!=0 ||(2==$agent_store_data['agent_mode']) && 0!=$agent_store_data['agent_id_2']){ //有代理商
                if ($agent_store_data['agent_id_1']!=0)
                    $predeposit_info = $agent_model->where(array('agent_id' => $agent_store_data['agent_id_1']))->find();//获取代理商是否开启预充值
                if ($agent_store_data['agent_id_2']!=0)
                    $predeposit_info = $agent_model->where(array('agent_id' => $agent_store_data['agent_id_2']))->find();//获取代理商是否开启预充值
                if(!empty($money_presents) && $now<$money_presents['end_time'] && $now>$money_presents['begin_time'] && 1==$money_presents['type'] && $predeposit_info['agent_predeposit'] == 1){
                    $money=$money_presents['amount'];
                }
            }
            else{//无代理商
                if (!empty($city_str) && $city_str=='苏州市') {//苏州市开启预充值
                    if(!empty($money_presents) && $now<$money_presents['end_time'] && $now>$money_presents['begin_time'] && 1==$money_presents['type']){
                        $money=$money_presents['amount'];
                    }
                }
            }
            if($add_member) {
                //0.往会员表插入数据，生成邀请码,同时插入到member_common表
                $data = array();
                $data['member_name'] = $_POST['name'];
                $salt = random_str(8, FALSE);
                $data['member_passwd'] = md6('123456', $salt);
                $data['member_salt'] = $salt;
                $data['member_email'] = empty($joinin_detail['contacts_email']) ? '' : $joinin_detail['contacts_email'];
                $data['member_truename'] = $joinin_detail['contacts_name'];
//                $data['member_mobile'] = $joinin_detail['contacts_phone'];
                $data['member_areainfo'] = $joinin_detail['address'] . ' ' . $joinin_detail['address_detail'];
                $data['available_predeposit'] = $money;
                if (!empty($joinin_detail['region'])) {
                    $region = json_decode($joinin_detail['region'], true);
                    $data['member_areaid'] = $region['area'];
                    $data['member_cityid'] = $region['city'];
                    $data['member_provinceid'] = $region['province'];
                }
                $data['invitation'] = Model('member')->getinviter();
                $data['member_time']=time();
                $member_id = Model('member')->insert($data);
                if (!$member_id) throw new Exception('会员表保存失败');
                $res = Model('member_common')->insert(array('member_id' => $member_id));
                if (!$res) throw new Exception('系统错误');
            }elseif($money>0){
                //预充值
                $data = array('member_id' => $member_id, 'available_predeposit' => $member['available_predeposit'] + $money);
                Model('member')->update($data);
            }

            //1.更新申请表(agg_store_o2o_joinin)审核状态,和会员id
            $param['member_id']=$member_id;
            $param['member_name']=$_POST['name'];
            $param['store_commis_rate']=floatval($_POST['store_commis_rate']);
            $param['store_class_commis_rates']=implode(',',$_POST['commis_rate']);
            $param['lng']=floatval($_POST['lng']);
            $param['lat']=floatval($_POST['lat']);
            $param['open_time'] = time();
            $res=$model_store_joinin->update($param, array('where' => array('id' => $_POST['id'])));
            if(!$res) throw new Exception('系统错误');

            //2.插入store表
            $data=array();
            $data['store_name']=$joinin_detail['store_name'];
            $data['store_phone']=$joinin_detail['contacts_phone'];
            $data['grade_id']=3;
            $data['member_id']=$member_id;
            $data['member_name']=$_POST['name'];
            $data['seller_name']=$_POST['name'];
            $data['store_commis_rates']=floatval($_POST['store_commis_rate']);
            $data['sc_id']=2;       //1.旗舰店，2.独立店
            if(!empty($joinin_detail['region'])) {
                $region = json_decode($joinin_detail['region'],true);
                $data['province_id']=$region['province'];
                $temp=explode(' ',$joinin_detail['address']);
                $data['city_name']=$temp[1];
                $data['district_name']=$temp[2];
                $data['city_id']=$region['city'];
                $data['district_id']=$region['area'];
            }
            $data['area_info']=$joinin_detail['address'];
            $data['store_address']=$joinin_detail['address_detail'];
            $data['store_state']=1;
            $data['store_type']=1;      //都是本土
            $data['lat']=floatval($_POST['lat']);
            $data['lng']=floatval($_POST['lng']);
            $data['store_zip']	='';
            $data['store_zy']= '';
            $data['store_time']=time();
            $data['store_end_time'] = strtotime("+10 year");
            $store_id=Model('store')->addStore($data);
            if(!$store_id) throw new Exception('系统错误');


            $seller_group=array();
            $seller_group['group_name']='默认组';
            $seller_group['store_id']=$store_id;
            $seller_group['limits'] ='store_goods_add,store_goods_online,store_goods_offline,store_spec,store_album,store_order,store_vr_order,store_deliver,store_deliver_set,store_waybill,store_transport,store_setting,store_info,store_brand,store_refund,store_return,statistics_general,statistics_goods,statistics_sale,statistics_industry,statistics_flow,store_bill,store_bill,store_bill,store_msg';
            $seller_group['smt_limits'] = 'complain,goods_storage_alarm,goods_verify,goods_violation,new_order,refund,refund_auto_process,return,return_auto_process,return_auto_receipt,store_bill_affirm,store_bill_gathering,store_cost,store_expire';
            $group_id=Model('seller_group')->insert($seller_group);
            if(!$group_id){
                throw new Exception('添加默认分组错误');
            }

            //5.插入seller表,写入卖家账号
            $seller_array = array();
            $seller_array['seller_name'] = $_POST['name'];
            $seller_array['member_id'] = $member_id;
            $seller_array['seller_group_id'] = $group_id;
            $seller_array['store_id'] = $store_id;
            $seller_array['is_admin'] = 1;
            $seller = Model('seller')->addSeller($seller_array);
            if(!$seller) throw new Exception('系统错误');


            //预充值功能
            if($money>0){
                $store_pre_deposit['store_id']=$store_id;
                $store_pre_deposit['store_name']=$joinin_detail['store_name'];
                $store_pre_deposit['member_id']=$member_id;
                $store_pre_deposit['amount']=$money;
                $store_pre_deposit['type']=1;
                $store_pre_deposit['create_time']=time();
                $store_pre_deposit['create_name']=$this->admin_info['name'];
                $store_pre_deposit['pre_sn']=Model('store_pre_deposit')->makeSn($store_id);
                $res=Model('store_pre_deposit')->insert($store_pre_deposit);
                if($res===false){
                    throw new Exception('预充值异常');
                }
                $pd_log['lg_member_id']=$member_id;
                $pd_log['lg_member_name']=$_POST['name'];
                $pd_log['lg_admin_name']=$this->admin_info['name'];
                $pd_log['lg_type']='recharge';
                $pd_log['lg_av_amount']=$money;
                $pd_log['lg_add_time']=time();
                $pd_log['lg_desc']='预存款充值';
                $res=Model('pd_log')->insert($pd_log);
                if($res===false){
                    throw new Exception('预充值异常');
                }
            }
            $agent_store_data['store_member_id']=$member_id;
            $agent_store_data['store_id']=$store_id;
            //3.如果存在区代，绑定代理商
            if($agent_store_data['agent_id_1']!=0 ||(2==$agent_store_data['agent_mode']) && 0!=$agent_store_data['agent_id_2']){ //有区代才可以绑定
                $res=Model('agent_store')->insert($agent_store_data);
                if(!$res){
                    throw new Exception('代理商绑定失败');
                }
            }

            // 添加相册默认
            $album_model = Model('album');
            $album_arr = array();
            $album_arr['aclass_name'] = Language::get('store_save_defaultalbumclass_name');
            $album_arr['store_id'] = $store_id;
            $album_arr['aclass_des'] = '';
            $album_arr['aclass_sort'] = '255';
            $album_arr['aclass_cover'] = '';
            $album_arr['upload_time'] = time();
            $album_arr['is_default'] = '1';
            $album_model->addClass($album_arr);
            //3.绑定分类，bind_class   //插入店铺绑定分类表
            $store_bind_class_array = array();
            $store_bind_class = unserialize($joinin_detail['store_class_ids']);
            $store_bind_commis_rates = $_POST['commis_rate'];
            for($i=0, $length=count($store_bind_class); $i<$length; $i++) {
                list($class1, $class2, $class3) = explode(',', $store_bind_class[$i]);
                //计算分销返利比率
                $distribution_rate=$temp_class_id=0;
                $temp_class_id=($class3!=0)?$class3:(($class2!=0)?$class2:$class1);
                if($temp_class_id>0) {
                    $temp_class_info = Model('goods_class')->getGoodsClassInfoById($temp_class_id, 'all');
                    if(!empty($temp_class_info)){
                        $distribution_rate=$temp_class_info['distribution_rate'];
                    }

                }
                $store_bind_class_array[] = array(
                    'store_id' => $store_id,
                    'commis_rate' => $store_bind_commis_rates[$i],
                    'distribution_rate' => $distribution_rate,
                    'class_1' => $class1,
                    'class_2' => $class2,
                    'class_3' => $class3,
                    'state' => 1
                );
            }
            $model_store_bind_class = Model('store_bind_class');
            $model_store_bind_class->addStoreBindClassAll($store_bind_class_array);

            //4.插入扩展表extend
            Model('store_extend')->insert(array('store_id'=>$store_id));

            //final 提交数据
            Model::commit();
            showMessage('开店成功!','index.php?act=store&op=store_joinin_o2o');
        }catch (Exception $e){
            Model::rollback();
            showMessage('开店失败!'.$e->getMessage());
        }
    }

    //本土开店审核，列表页
    public function store_joinin_o2oOp(){
        //店铺列表
        if(!empty($_GET['owner_and_name'])) {
            $condition['contacts_name'] = array('like','%'.$_GET['owner_and_name'].'%');
        }
        if(!empty($_GET['store_name'])) {
            $condition['store_name'] = array('like','%'.$_GET['store_name'].'%');
        }
        if(!empty($_GET['joinin_state']) && intval($_GET['joinin_state']) > 0) {
            $condition['joinin_state'] = $_GET['joinin_state'] ;
        } else {
            $condition['joinin_state'] = array('gt',0);
        }
        //分页用法：
        $model =new Model();
        $store_list = $model->table('store_o2o_joinin')->where($condition)->page(20)->order('joinin_state asc,id desc')->select();
        Tpl::output('page',$model->showpage('2'));

        Tpl::output('store_list', $store_list);
        Tpl::output('joinin_state_array', $this->get_store_joinin_o2o_state());

        Tpl::showpage('store_joinin_o2o');
    }
    //导出店铺申请
    public function export_store_listOp(){
        //店铺列表
        if(!empty($_GET['owner_and_name'])) {
            $condition['contacts_name'] = array('like','%'.$_GET['owner_and_name'].'%');
        }
        if(!empty($_GET['store_name'])) {
            $condition['store_name'] = array('like','%'.$_GET['store_name'].'%');
        }
        if(!empty($_GET['joinin_state']) && intval($_GET['joinin_state']) > 0) {
            $condition['joinin_state'] = $_GET['joinin_state'] ;
        } else {
            $condition['joinin_state'] = array('gt',0);
        }
        //分页用法：
        $model_pd =new Model();
        if (!is_numeric($_GET['curpage']) && !is_numeric($_GET['cursection'])){
            $count = $model_pd->table('store_o2o_joinin')->where($condition)->count();
            $array = array();
            if ($count > self::EXPORT_SIZE ){	//显示下载链接
                $page = ceil($count/self::EXPORT_SIZE);
                for ($i=1;$i<=$page;$i++){
                    $limit1 = ($i-1)*self::EXPORT_SIZE + 1;
                    $limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
                    $array[$i] = $limit1.' ~ '.$limit2 ;
                }
                Tpl::output('list',$array);
                Tpl::output('murl','index.php?act=store&op=store_joinin_o2o');
                Tpl::showpage('export.excel');
            }else{	//如果数量小，直接下载
                $data = $model_pd->table('store_o2o_joinin')->where($condition)->limit(self::EXPORT_SIZE)->order('joinin_state asc,id desc')->select();
                $join_state = array('10'=>'待审核','40'=>'已通过','30'=>'未通过');
                foreach ($data as $k=>$v) {
                    $data[$k]['join_state'] = $join_state[$v['joinin_state']];
                }
                $this->createStoreExcel($data);
            }
        }elseif(is_numeric($_GET['cursection'])) {	//分段下载
            $limit1 = ($_GET['cursection']-1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model_pd->table('store_o2o_joinin')->where($condition)->order('joinin_state asc,id desc')->limit("$limit1,$limit2")->select();
            $join_state = array('10'=>'待审核','40'=>'已通过','30'=>'未通过');
            foreach ($data as $k=>$v) {
                $data[$k]['join_state'] = $join_state[$v['joinin_state']];
            }
            $this->createStoreExcel($data);
        }else{	//分页下载
            $limit1 = ($_GET['curpage']-1) * 20;
            $limit2 = 20;
            $data = $model_pd->table('store_o2o_joinin')->where($condition)->order('joinin_state asc,id desc')->limit("$limit1,$limit2")->select();
            $join_state = array('10'=>'待审核','40'=>'已通过','30'=>'未通过');
            foreach ($data as $k=>$v) {
                $data[$k]['join_state'] = $join_state[$v['joinin_state']];
            }
            $this->createStoreExcel($data);
        }

    }

    /**
     * 生成商户申请excel
     *
     * @param array $data
     */
    private function createStoreExcel($data = array()){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商户名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'店主姓名');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'所在地');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'联系电话');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'申请时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'状态');
        foreach ((array)$data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>$v['store_name']);
            $tmp[] = array('data'=>$v['contacts_name']);
            $tmp[] = array('data'=>$v['address']);
            $tmp[] = array('data'=>$v['contacts_phone']);
            $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['apply_time']));
            $tmp[] = array('data'=>$v['join_state']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('商户申请',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('商户申请',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }
    /**
     * 商户数据导出excel
     *
     * @param array $data
     */
    private function createAllStore($data = array()){
        Language::read('export');
        import('libraries.excel');
        $excel_obj = new Excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
        //header
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'商户名称');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'账号');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'所在地');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'开店时间');
        $excel_data[0][] = array('styleid'=>'s_title','data'=>'状态');
        foreach ((array)$data as $k=>$v){
            $tmp = array();
            $tmp[] = array('data'=>$v['store_name']);
            $tmp[] = array('data'=>$v['member_name']);
            $tmp[] = array('data'=>$v['area_info']);
            $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['store_time']));
            $tmp[] = array('data'=>$v['store_state']);
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data,CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('商户列表',CHARSET));
        $excel_obj->generateXML($excel_obj->charset('商户列表',CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
    }
    //商户禁用、启用
    public function change_store_statusOp(){
        if(!isset($_GET['store_id'])||!isset($_GET['status'])||!in_array($_GET['status'],array(0,1))){
            echo 2;
            exit;
        }
        $rts = Model('store')->editStore(array('store_state'=>intval($_GET['status'])),'store_id='.intval($_GET['store_id']));
        
        //-------solon.ring2011@gmail.com-退款-
        $wheres = array('store_id'=>$_GET['store_id']);
        if($rts && $_GET['status'] == 0)
        {
                $grt = Model('order')->getOrderList(array('store_id'=>$_GET['store_id'],'order_state'=>20,'delete_state'=>0,'refund_state'=>0,'order_type'=>1));
                if(count($grt)>0)
                {
                    foreach ($grt as $key => $value) {
                        $ids['order_id']       =   $value['order_id'];
                        $ids['order_money']    =   $value['order_amount'];
                        $ids['member_id']      =   $value['buyer_id'];
                        $ids['member_name']    =   $value['buyer_name'];
                        $ids['order_sn']       =   $value['order_sn'];
                        Logic('order')->userActionLocalPrice($ids);
                    }
                }   
            //----end------
        }

        Model('store')->editStore(array('store_state'=>intval($_GET['status'])),'store_id='.intval($_GET['store_id']));
        //删除商户店铺收藏记录
        if($_GET['status'] == 0) {
            $condition = array();
            $condition['fav_id'] = $_GET['store_id'];
            $condition['fav_type'] = 'store';
            Model('favorites')->where($condition)->delete();
            //删除商品收藏记录
            $condi = array();
            $condi['goods.store_id'] = $_GET['store_id'];
            $condi['favorites.fav_type'] = 'goods';
            $option['attr']='favorites';
            Model()->table('favorites,goods')->join('inner')->on('favorites.fav_id=goods.goods_id')->where($condi)->join_delete($option);
        }
        echo 1;
    }
    private function get_store_joinin_o2o_state() {
        $joinin_state_array = array(
            STORE_JOIN_STATE_NEW => '新申请',
            STORE_JOIN_STATE_VERIFY_FAIL => '审核失败',
            STORE_JOIN_STATE_FINAL => '开店成功',
        );
        return $joinin_state_array;
    }

    public function save_locationOp(){
        if(!isset($_GET['lat'])||!isset($_GET['lng'])||!isset($_GET['id'])){
            echo '参数错误';exit;
        }
        $data=array(
            'lat'=>floatval($_GET['lat']),
            'lng'=>floatval($_GET['lng']),
        );
        $join_info=Model('store_o2o_joinin')->where(array('id'=>intval($_GET['id'])))->find();
        if(empty($join_info)){
            echo '数据异常';exit;
        }
        Model('store_o2o_joinin')->where(array('id'=>intval($_GET['id'])))->update($data);
        Model('store')->where(array('member_id'=>$join_info['member_id']))->update($data);
        echo '保存成功';

    }

    public function adt_save_locationOp(){
        if(!isset($_GET['lat'])||!isset($_GET['lng'])||!isset($_GET['id'])){
            echo '参数错误';exit;
        }
        $data=array(
            'lat'=>floatval($_GET['lat']),
            'lng'=>floatval($_GET['lng']),
        );
        Model('store')->where(array('store_id'=>intval($_GET['id'])))->update($data);
        echo '保存成功';

    }


    /**
     * 跑腿邦店铺详情
     */
    public function adt_store_joinin_detailOp(){
        $model_store_joinin = Model('store_o2o_joinin');
        if(isset($_GET['id'])) {
            $store_info = Model('store')->find($_GET['id']);
            $member_info=Model('member')->getMemberInfoByID($store_info['member_id']);
        }else{
            showMessage('参数错误');
        }

        Tpl::output('store_info', $store_info);
        Tpl::output('member_info', $member_info);
        Tpl::showpage('adt_store_joinin.detail');
    }

    /**
     * 跑腿邦店铺粉丝页面
     */
    public function adt_fansOp(){
        if(!isset($_GET['store_id'])){
            showMessage('参数错误');
        }

        if(isset($_GET['register_from']) && !empty($_GET['register_from'])){
            $where['member.register_from']=intval($_GET['register_from']);
        }
        if(isset($_GET['member_name']) && !empty($_GET['member_name'])){
            $where['member.member_name']=array('like','%'.$_GET['member_name'].'%');
        }
        if(isset($_GET['query_start_time']) && !empty($_GET['query_start_time'])){
            $where['order.add_time']=array('egt',$_GET['query_start_time']);
        }
        if(isset($_GET['query_end_time']) && !empty($_GET['query_end_time'])){
            $where['order.add_time']=array('elt',$_GET['query_end_time']);
        }

        $store_id=intval($_GET['store_id']);
        $on='order.buyer_id=member.member_id';
        $where['order.order_type']=3;
        $where['order.order_state']=40;
        $where['order.league_store_id']=$store_id;
        $field[]='sum(1) order_count';
        $field[]='sum(order_amount) order_amount_total';
        $field[]='`order`.*';
        $field[]='member.*';
        $fans_list=Model()->table('order,member')->join('left')->on($on)->where($where)->field($field)->group('order.buyer_id')->page(20)->select();
        $list_count=Model()->table('order,member')->join('left')->on($on)->where($where)->count('distinct buyer_id');
        pagecmd('settotalnum',$list_count);
        $show_page=Model()->showpage();
        //查询匹配最多的商户
        $member_ids=agg_array_column($fans_list,'buyer_id');
        $where_stores['order_type']=3;
        $where_stores['order_state']=40;
        $where_stores['buyer_id']=array('in',$member_ids);
        $field_stores=array('buyer_id','league_store_id','league_store_name','sum(1) order_count');
        $store_names=Model()->table('order')->where($where_stores)->field($field_stores)->group('buyer_id,league_store_id')->order('buyer_id,order_count asc')->select();
        $store_names_useable=array_under_reset($store_names,'buyer_id');

        Tpl::output('member_fav_store', $store_names_useable);
        Tpl::output('fans_list', $fans_list);
        Tpl::output('page',$show_page);
        Tpl::showPage('store.adt_fans');
    }

}
