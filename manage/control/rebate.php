<?php
/**
 * 返利管理
 ***/

defined('emall') or exit('Access Invalid!');
class rebateControl extends SystemControl{
	const EXPORT_SIZE = 500;
	/**
	 * 每页显示数量
	 * @var int
	 */
	const ONE_PAGE_SIZE = 30;
	public function __construct(){
		parent::__construct();
		Language::read('rebate');		
	}

	
	
	/**
	 * 返利列表
	 */
	public function indexOp(){
		$condition_arr = array();
		$condition_arr['member_name'] = trim($_GET['mname']);
	
		$condition_arr['user_type'] =trim($_GET['user_type']);
		if ($_GET['stage']){
			$condition_arr['rebate_type'] = trim($_GET['stage']);
		}		
        $condition_arr['rebate_saddtime'] = strtotime($_GET['stime']);
		$condition_arr['rebate_eaddtime'] = strtotime($_GET['etime']);
        if($condition_arr['rebate_eaddtime'] > 0) {
            $condition_arr['rebate_eaddtime'] += 86400;
        }
		if ($_GET['order_sn']){
				$condition_arr['order_sn'] = trim($_GET['order_sn']);
		}	
		
		//分页
		$page	= new Page();
		$page->setEachNum(self::ONE_PAGE_SIZE);
		$page->setStyle('admin');
		//查询返利列表
		$points_model = Model('points');
		$list_log = $points_model->getRebateLogList($condition_arr,$page,'*','');
		//返利总和
		$points_sum = $points_model->getRebateSum($condition_arr);
		//信息输出
		Tpl::output('sum',$points_sum[0]['sum']);
		Tpl::output('show_page',$page->show());
		Tpl::output('list_log',$list_log);
		Tpl::showpage('rebate_list');
	}

	/**
	 * 返利列表导出
	 */
	public function export_step1Op(){
		$condition_arr = array();
		$condition_arr['member_name'] = trim($_GET['mname']);
	
		$condition_arr['user_type'] =trim($_GET['user_type']);
		if ($_GET['stage']){
			$condition_arr['rebate_type'] = trim($_GET['stage']);
		}		
        $condition_arr['rebate_saddtime'] = strtotime($_GET['stime']);
		$condition_arr['rebate_eaddtime'] = strtotime($_GET['etime']);
        if($condition_arr['rebate_eaddtime'] > 0) {
            $condition_arr['rebate_eaddtime'] += 86400;
        }
		if ($_GET['order_sn']){
				$condition_arr['order_sn'] = trim($_GET['order_sn']);
		}	
		$points_model = Model('points');
		if (!is_numeric($_GET['curpage']) && !is_numeric($_GET['cursection'])){
			$count = $points_model->getRebateLogTotalNum();
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=pointslog&op=pointslog');
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$list_log = $points_model->getRebateLogList($condition_arr,$page,'*','');
				$this->createExcel($list_log);
			}
		}elseif(is_numeric($_GET['cursection'])) {	//分段下载
			$limit1 = ($_GET['cursection']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$condition_arr['limit'] = "{$limit1},{$limit2}";
			$list_log = $points_model->getRebateLogList($condition_arr,$page,'*','');
			$this->createExcel($list_log);
		}else{	//分页下载
			$limit1 = ($_GET['curpage']-1) * self::ONE_PAGE_SIZE;
			$limit2 = self::ONE_PAGE_SIZE;
			$condition_arr['limit'] = "{$limit1},{$limit2}";
			$list_log = $points_model->getRebateLogList($condition_arr,$page,'*','');
			$this->createExcel($list_log);
		}
	}

	/**
	 * 生成excel
	 *
	 * @param array $data
	 */
	private function createExcel($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>Language::get('admin_membername'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>Language::get('admin_goods_nname'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>Language::get('add_time'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>Language::get('admin_rebate_stage'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>Language::get('admin_rebate_amount'));
		
		$state_cn = array(Language::get('admin_rebate_user_bentu'),Language::get('admin_rebate_user_xianshang'));
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>$v['member_name']);
			$tmp[] = array('data'=>$v['goods_name']);			
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
			$tmp[] = array('data'=>str_replace(array('1','2'),$state_cn,$v['rebate_type']));
			$tmp[] = array('data'=>$v['rebate']);	
			

			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(Language::get('admin_rebate_log_title'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(Language::get('admin_rebate_log_title'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
}
