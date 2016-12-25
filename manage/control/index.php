<?php
/**
 * 默认展示页面
 * 默认展示页面
 ***/

defined('emall') or exit('Access Invalid!');

class indexControl extends SystemControl{
	public function __construct(){
		parent::__construct();
		Language::read('index');
	}
	public function indexOp(){
		//输出管理员信息
		Tpl::output('admin_info',$this->getAdminInfo());
		//输出菜单
		$this->getNav('',$top_nav,$left_nav,$map_nav);
		Tpl::output('top_nav',$top_nav);
		Tpl::output('left_nav',$left_nav);
		Tpl::output('map_nav',$map_nav);

		Tpl::showpage('index','index_layout');
	}

	/**
	 * 退出
	 */
	public function logoutOp(){
		setNcCookie('sys_key','',-1,'',null);
		@header("Location: index.php");
		exit;
	}
	/**
	 * 修改密码
	 */
	public function modifypwOp(){
		if (chksubmit()){
			if (trim($_POST['new_pw']) !== trim($_POST['new_pw2'])){
				//showMessage('两次输入的密码不一致，请重新输入');
				showMessage(Language::get('index_modifypw_repeat_error'));
			}
			$admininfo = $this->getAdminInfo();
			//查询管理员信息
			$admin_model = Model('admin');
			$admininfo = $admin_model->getOneAdmin($admininfo['id']);
                
			if (!is_array($admininfo) || count($admininfo)<= 0){
				showMessage(Language::get('index_modifypw_admin_error'));
			}
			//旧密码是否正确
			if ($admininfo['admin_password'] != md6(trim($_POST['old_pw']), $admininfo['admin_salt'])){
                            showMessage(Language::get('index_modifypw_oldpw_error'));
			}
                        $salt = random_str(8, FALSE);
			$new_pw = md6(trim($_POST['new_pw']), $salt);
                        $up_condition = array(
                            'admin_id'       => $admininfo['admin_id'],
                            'admin_password' => $new_pw,
                            'admin_salt'     => $salt,
                        );
			$result = $admin_model->updateAdmin($up_condition);
			if ($result){
				showMessage(Language::get('index_modifypw_success'));
			}else{
				showMessage(Language::get('index_modifypw_fail'));
			}
		}else{
			Language::read('admin');
			Tpl::showpage('admin.modifypw');
		}
	}
}
