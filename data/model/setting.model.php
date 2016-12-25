<?php
/**
 * 系统设置内容
 */
defined('emall') or exit('Access Invalid!');
class settingModel extends Model{
	public function __construct(){
		parent::__construct('setting');
	}
	/**
	 * 读取系统设置信息
	 *
	 * @param string $name 系统设置信息名称
	 * @return array 数组格式的返回结果
	 */
	public function getRowSetting($name){
		$param	= array();
		$param['table']	= 'setting';
		$param['where']	= "name='".$name."'";
		$result	= Db::select($param);
		if(is_array($result) and is_array($result[0])){
			return $result[0];
		}
		return false;
	}

	/**
	 * 读取系统设置列表
	 *
	 * @param
	 * @return array 数组格式的返回结果
	 */
	public function getListSetting(){
		$param = array();
		$param['table'] = 'setting';
		$result = Db::select($param);
		/**
		 * 整理
		 */
		if (is_array($result)){
			$list_setting = array();
			foreach ($result as $k => $v){
				$list_setting[$v['name']] = $v['value'];
			}
		}
		return $list_setting;
	}

	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @return bool 布尔类型的返回结果
	 */
	public function updateSetting($param){
		if (empty($param)){
			return false;
		}

		if (is_array($param)){
			foreach ($param as $k => $v){
				$tmp = array();
				$specialkeys_arr = array('statistics_code');
				$tmp['value'] = (in_array($k,$specialkeys_arr) ? htmlentities($v,ENT_QUOTES) : $v);
				$where = " name = '". $k ."'";
				$result = Db::update('setting',$tmp,$where);
				if ($result !== true){
					return $result;
				}
			}
			dkcache('setting');
			//H('setting',true);
			@unlink(BASE_DATA_PATH.DS.'cache'.DS.'setting.php');
			return true;
		}else {
			return false;
		}
	}
	public function memberClear() {
		/*
	    Db::query("delete from `".DBPRE."member`");
		Db::query("delete from `".DBPRE."store`");
		Db::query("TRUNCATE TABLE `".DBPRE."goods`");
		Db::query("TRUNCATE TABLE `".DBPRE."groupbuy`");
		Db::query("TRUNCATE TABLE `".DBPRE."activity_detail`");
		Db::query("TRUNCATE TABLE `".DBPRE."address`");
		Db::query("TRUNCATE TABLE `".DBPRE."cart`");
		Db::query("TRUNCATE TABLE `".DBPRE."consult`");
		Db::query("TRUNCATE TABLE `".DBPRE."favorites`");
		Db::query("TRUNCATE TABLE `".DBPRE."message`");
		Db::query("TRUNCATE TABLE `".DBPRE."order`");
		Db::query("TRUNCATE TABLE `".DBPRE."order_common`");
		Db::query("TRUNCATE TABLE `".DBPRE."order_goods`");
		Db::query("TRUNCATE TABLE `".DBPRE."order_log`");
		Db::query("TRUNCATE TABLE `".DBPRE."store_class`");
		Db::query("TRUNCATE TABLE `".DBPRE."store_goods_class`");
		Db::query("TRUNCATE TABLE `".DBPRE."store_navigation`");
		Db::query("TRUNCATE TABLE `".DBPRE."store_watermark`");
		Db::query("TRUNCATE TABLE `".DBPRE."inform`");
		Db::query("TRUNCATE TABLE `".DBPRE."complain`");
		Db::query("TRUNCATE TABLE `".DBPRE."complain_goods`");
		Db::query("TRUNCATE TABLE `".DBPRE."complain_talk`");
		Db::query("TRUNCATE TABLE `".DBPRE."voucher`");
		Db::query("TRUNCATE TABLE `".DBPRE."voucher_template`");
		Db::query("TRUNCATE TABLE `".DBPRE."points_cart`");
		Db::query("TRUNCATE TABLE `".DBPRE."points_log`");
		Db::query("TRUNCATE TABLE `".DBPRE."points_order`");
		Db::query("TRUNCATE TABLE `".DBPRE."points_orderaddress`");
		Db::query("TRUNCATE TABLE `".DBPRE."points_ordergoods`");
		Db::query("TRUNCATE TABLE `".DBPRE."album_class`");
		Db::query("TRUNCATE TABLE `".DBPRE."album_pic`");
		Db::query("TRUNCATE TABLE `".DBPRE."refund_reason`");
		Db::query("TRUNCATE TABLE `".DBPRE."refund_return`");
		Db::query("TRUNCATE TABLE `".DBPRE."p_mansong`");
		Db::query("TRUNCATE TABLE `".DBPRE."p_mansong_quota`");
		Db::query("TRUNCATE TABLE `".DBPRE."p_mansong_rule`");
		Db::query("TRUNCATE TABLE `".DBPRE."p_xianshi`");
		Db::query("TRUNCATE TABLE `".DBPRE."p_xianshi_goods`");
		Db::query("TRUNCATE TABLE `".DBPRE."p_xianshi_quota`");
		Db::query("TRUNCATE TABLE `".DBPRE."transport`");
		Db::query("TRUNCATE TABLE `".DBPRE."transport_extend`");
		Db::query("TRUNCATE TABLE `".DBPRE."sns_comment`");
		Db::query("TRUNCATE TABLE `".DBPRE."sns_friend`");
		Db::query("TRUNCATE TABLE `".DBPRE."sns_goods`");
		Db::query("TRUNCATE TABLE `".DBPRE."sns_sharegoods`");
		Db::query("TRUNCATE TABLE `".DBPRE."sns_sharestore`");
		Db::query("TRUNCATE TABLE `".DBPRE."sns_tracelog`");
		Db::query("TRUNCATE TABLE `".DBPRE."sns_visitor`");
		*/
		return true;
	}	

}
