<?php
/**
 * 账号同步
 */

defined('emall') or exit('Access Invalid!');
class accountControl extends SystemControl{
	private $links = array(
		array('url'=>'act=account&op=ucenter','lang'=>'ucenter_integration'),
		array('url'=>'act=account&op=qq','lang'=>'qqSettings'),
		array('url'=>'act=account&op=sina','lang'=>'sinaSettings')
	);
	public function __construct(){
		parent::__construct();
		Language::read('setting');
	}

	/**
	 * QQ互联
	 */
	public function qqOp(){
		$model_setting = Model('setting');
		if (chksubmit()){
			$obj_validate = new Validate();
			if (trim($_POST['qq_isuse']) == '1'){
				$obj_validate->validateparam = array(
					array("input"=>$_POST["qq_appid"], "require"=>"true","message"=>Language::get('qq_appid_error')),
					array("input"=>$_POST["qq_appkey"], "require"=>"true","message"=>Language::get('qq_appkey_error'))
				);
			}
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error);
			}else {
				$update_array = array();
				$update_array['qq_isuse'] 	= $_POST['qq_isuse'];
				$update_array['qq_appcode'] = $_POST['qq_appcode'];
				$update_array['qq_appid'] 	= $_POST['qq_appid'];
				$update_array['qq_appkey'] 	= $_POST['qq_appkey'];
				$result = $model_setting->updateSetting($update_array);
				if ($result === true){
					$this->log(L('nc_edit,qqSettings'),1);
					showMessage(Language::get('nc_common_save_succ'));
				}else {
					$this->log(L('nc_edit,qqSettings'),0);
					showMessage(Language::get('nc_common_save_fail'));
				}
			}
		}

		$list_setting = $model_setting->getListSetting();
		Tpl::output('list_setting',$list_setting);

		//输出子菜单
		Tpl::output('top_link',$this->sublink($this->links,'qq'));
		Tpl::showpage('setting.qq_setting');
	}

	/**
	 * sina微博设置
	 */
	public function sinaOp(){
		$model_setting = Model('setting');
		if (chksubmit()){
			$obj_validate = new Validate();
			if (trim($_POST['sina_isuse']) == '1'){
				$obj_validate->validateparam = array(
					array("input"=>$_POST["sina_wb_akey"], "require"=>"true","message"=>Language::get('sina_wb_akey_error')),
					array("input"=>$_POST["sina_wb_skey"], "require"=>"true","message"=>Language::get('sina_wb_skey_error'))
				);
			}
			$error = $obj_validate->validate();
			if ($error != ''){
				showMessage($error);
			}else {
				$update_array = array();
				$update_array['sina_isuse'] 	= $_POST['sina_isuse'];
				$update_array['sina_wb_akey'] 	= $_POST['sina_wb_akey'];
				$update_array['sina_wb_skey'] 	= $_POST['sina_wb_skey'];
				$update_array['sina_appcode'] 	= $_POST['sina_appcode'];
				$result = $model_setting->updateSetting($update_array);
				if ($result === true){
					$this->log(L('nc_edit,sinaSettings'),1);
					showMessage(Language::get('nc_common_save_succ'));
				}else {
					$this->log(L('nc_edit,sinaSettings'),0);
					showMessage(Language::get('nc_common_save_fail'));
				}
			}
		}
		$is_exist = function_exists('curl_init');
		if ($is_exist){
			$list_setting = $model_setting->getListSetting();
			Tpl::output('list_setting',$list_setting);
		}
		Tpl::output('is_exist',$is_exist);

		//输出子菜单
		Tpl::output('top_link',$this->sublink($this->links,'sina'));

		Tpl::showpage('setting.sina_setting');
	}

	/**
	 * 清除会员信息
	 *
	 * @param
	 * @return
	 */
	public function member_clearOp() {
		/**
		 * 读取语言包
		 */
		$lang	= Language::getLangContent();

		/**
		 * 实例化模型
		 */
		$model_setting = Model('setting');
		$result = $model_setting->memberClear();
		if($result) {
			showMessage($lang['user_info_del_ok']);
		} else {
			showMessage($lang['user_info_del_fail']);
		}
	}

	/**
	 * Ucenter整合设置
	 *
	 * @param
	 * @return
	 */
	public function ucenterOp() {
		/**
		 * 读取语言包
		 */
		$lang	= Language::getLangContent();

		/**
		 * 实例化模型
		 */
		$model_setting = Model('setting');
		/**
		 * 保存信息
		 */
		if (chksubmit()){
			$update_array = array();
			$update_array['ucenter_status']		= trim($_POST['ucenter_status']);
            $update_array['ucenter_type']		= trim($_POST['ucenter_type']);
			$update_array['ucenter_app_id']		= trim($_POST['ucenter_app_id']);
			$update_array['ucenter_app_key']	= trim($_POST['ucenter_app_key']);
			$update_array['ucenter_ip'] 		= trim($_POST['ucenter_ip']);
			$update_array['ucenter_url'] 		= trim($_POST['ucenter_url']);
			$update_array['ucenter_connect_type'] = trim($_POST['ucenter_connect_type']);
			$update_array['ucenter_mysql_server'] = trim($_POST['ucenter_mysql_server']);
			$update_array['ucenter_mysql_username'] = trim($_POST['ucenter_mysql_username']);
			$update_array['ucenter_mysql_passwd'] = htmlspecialchars_decode(trim($_POST['ucenter_mysql_passwd']));
			$update_array['ucenter_mysql_name'] = trim($_POST['ucenter_mysql_name']);
			$update_array['ucenter_mysql_pre']	= trim($_POST['ucenter_mysql_pre']);

			$result = $model_setting->updateSetting($update_array);
			if ($result === true){
				showMessage(Language::get('nc_common_save_succ'));
			}else {
				showMessage(Language::get('nc_common_save_fail'));
			}
		}
		/**
		 * 读取设置内容 $list_setting
		 */
		$list_setting = $model_setting->getListSetting();
		/**
		 * 模板输出
		 */
		Tpl::output('list_setting',$list_setting);
		Tpl::showpage('setting.ucenter_setting');
	}

}
