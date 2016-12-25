<?php
/**
 * 交易管理
 *
 ***/

defined('emall') or exit('Access Invalid!');
class orderControl extends SystemControl{
    /**
     * 每次导出订单数量
     * @var int
     */
	const EXPORT_SIZE = 1000;
	/**
	 * 每页显示订单数量
	 * @var int
	 */
	const ONE_PAGE_SIZE = 30;

	public function __construct(){
		parent::__construct();
		Language::read('trade');
	}

	public function indexOp(){
	    $model_order = Model('order');
        $condition	= array();
        if($_GET['order_sn']) {
        	$condition['order_sn'] = $_GET['order_sn'];
        }
		if($_GET['pay_sn']) {
			$condition['pay_sn'] = $_GET['pay_sn'];
		}
        if($_GET['store_name']) {
            $condition['store_name|league_store_name'] = $_GET['store_name'];
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','35','40'))){
        	$condition['order_state'] = $_GET['order_state'];
        }
        if($_GET['payment_code']) {
            $condition['payment_code'] = $_GET['payment_code'];
        }
        if($_GET['buyer_name']) {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
		 if($_GET['order_type']) {
            $condition['order_type'] = $_GET['order_type'];
        }
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        $order_list	= $model_order->getOrderList($condition,self::ONE_PAGE_SIZE);

        foreach ($order_list as $order_id => $order_info) {
            //显示取消订单
            $order_list[$order_id]['if_cancel'] = $model_order->getOrderOperateState('system_cancel',$order_info);
            //显示收到货款
            $order_list[$order_id]['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);
        }
        //显示支付接口列表(搜索)
        $payment_list = Model('payment')->getPaymentOpenList();
        Tpl::output('payment_list',$payment_list);

        Tpl::output('order_list',$order_list);
        Tpl::output('show_page',$model_order->showpage());
        Tpl::showpage('order.index');
	}

	/**
	 * 平台订单状态操作
	 *
	 */
	public function change_stateOp() {
        $order_id = intval($_GET['order_id']);
        if($order_id <= 0){
            showMessage(L('miss_order_number'),$_POST['ref_url'],'html','error');
        }
        $model_order = Model('order');

        //获取订单详细
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info	= $model_order->getOrderInfo($condition);

        if ($_GET['state_type'] == 'cancel') {
            $result = $this->_order_cancel($order_info);
        } elseif ($_GET['state_type'] == 'receive_pay') {
            $result = $this->_order_receive_pay($order_info,$_POST);
        }
        if (!$result['state']) {
            showMessage($result['msg'],$_POST['ref_url'],'html','error');
        } else {
            showMessage($result['msg'],$_POST['ref_url']);
        }
	}

	/**
	 * 系统取消订单
	 */
	private function _order_cancel($order_info) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    $if_allow = $model_order->getOrderOperateState('system_cancel',$order_info);
	    if (!$if_allow) {
	        return callback(false,'无权操作');
	    }
	    $result =  $logic_order->changeOrderStateCancel($order_info,'system', $this->admin_info['name']);
        if ($result['state']) {
            $this->log(L('order_log_cancel').','.L('order_number').':'.$order_info['order_sn'],1);
        }
        return $result;
	}

	/**
	 * 系统收到货款
	 * @throws Exception
	 */
	private function _order_receive_pay($order_info, $post) {
	    $order_id = $order_info['order_id'];
	    $model_order = Model('order');
	    $logic_order = Logic('order');
	    $if_allow = $model_order->getOrderOperateState('system_receive_pay',$order_info);
	    if (!$if_allow) {
	        return callback(false,'无权操作');
	    }

	    if (!chksubmit()) {
	        Tpl::output('order_info',$order_info);
	        //显示支付接口列表
	        $payment_list = Model('payment')->getPaymentOpenList();
	        //去掉预存款和货到付款
	        foreach ($payment_list as $key => $value){
	            if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
	               unset($payment_list[$key]);
	            }
	        }
	        Tpl::output('payment_list',$payment_list);
	        Tpl::showpage('order.receive_pay');
	        exit();
	    }
	    $order_list	= $model_order->getOrderList(array('pay_sn'=>$order_info['pay_sn'],'order_state'=>ORDER_STATE_NEW));
	    $result = $logic_order->changeOrderReceivePay($order_list,'system',$this->admin_info['name'],$post);
        if ($result['state']) {
            $this->log('将订单改为已收款状态,'.L('order_number').':'.$order_info['order_sn'],1);
        }
	    return $result;
	}

	/**
	 * 查看订单
	 *
	 */
	public function show_orderOp(){
	    $order_id = intval($_GET['order_id']);
	    if($order_id <= 0 ){
	        showMessage(L('miss_order_number'));
	    }
        $model_order	= Model('order');
        $order_info	= $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));
		if(3==$order_info['order_type']){
			$this->adt_show_order($order_info);
			exit;
		}

        //订单变更日志
		$log_list	= $model_order->getOrderLogList(array('order_id'=>$order_info['order_id']));
		Tpl::output('order_log',$log_list);

		//退款退货信息
        $model_refund = Model('refund_return');
        $condition = array();
        $condition['order_id'] = $order_info['order_id'];
        $condition['seller_state'] = 2;
        $condition['admin_time'] = array('gt',0);
        $return_list = $model_refund->getReturnList($condition);
        Tpl::output('return_list',$return_list);

        //退款信息
        $refund_list = $model_refund->getRefundList($condition);
        Tpl::output('refund_list',$refund_list);

		//卖家发货信息
		if (!empty($order_info['extend_order_common']['daddress_id'])) {
		    $daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
		    Tpl::output('daddress_info',$daddress_info);
		}

		Tpl::output('order_info',$order_info);
        Tpl::showpage('order.view');
	}

	/**
	 * 跑腿邦，协调订单列表
	 */
	public function adt_assist_indexOp(){
		$model_order = Model('order');
		$condition	= array();
		if($_GET['order_sn']) {
			$condition['order_sn'] = $_GET['order_sn'];
		}
		if($_GET['pay_sn']) {
			$condition['pay_sn'] = $_GET['pay_sn'];
		}
		if($_GET['store_name']) {
			$condition['store_name|league_store_name'] = $_GET['store_name'];
		}
		if(in_array($_GET['order_state'],array('0','10','20','30','35','40'))){
			$condition['order_state'] = $_GET['order_state'];
		}
		if($_GET['payment_code']) {
			$condition['payment_code'] = $_GET['payment_code'];
		}
		if($_GET['buyer_name']) {
			$condition['buyer_name'] = $_GET['buyer_name'];
		}
		if($_GET['order_type']) {
			$condition['order_type'] = $_GET['order_type'];
		}
		$if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time']);
		$if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time']);
		$start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
		$end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']): null;
		if ($start_unixtime || $end_unixtime) {
			$condition['add_time'] = array('time',array($start_unixtime,$end_unixtime));
		}

		//需要协调的订单列表
		$assist_condition = array();
		if($_GET['status']=='no') {
			$assist_condition['status'] = 0;
		}elseif($_GET['status']=='ok'){
			$assist_condition['status'] = 1;
		}elseif($_GET['status']=='all'){
			$assist_condition = array();
		}else{
			$assist_condition['status'] = 0;
		}
		$assist_list = $model_order->getAssistOrderLogList($assist_condition,'order_id');
		$new_assist_list = array();
		foreach($assist_list as $key=>$value){
			$new_assist_list[] = $value['order_id'];
		}

		$condition['order_type'] = 3;
		$condition['order_id'] = array('IN',$new_assist_list);
		$order_list	= $model_order->getOrderList($condition,self::ONE_PAGE_SIZE,'*','add_time desc');
		foreach ($order_list as $order_id => $order_info) {
			//显示取消订单
			$order_list[$order_id]['if_cancel'] = $model_order->getOrderOperateState('system_cancel',$order_info);
			//显示收到货款
			$order_list[$order_id]['if_system_receive_pay'] = $model_order->getOrderOperateState('system_receive_pay',$order_info);
		}
		//显示支付接口列表(搜索)
		$payment_list = Model('payment')->getPaymentOpenList();
		Tpl::output('payment_list',$payment_list);

		Tpl::output('order_list',$order_list);
		Tpl::output('show_page',$model_order->showpage());
		Tpl::showpage('adt_order.assist');
	}

	/**
	 * 跑腿邦，协调订单详情
	 *
	 */
	public function adt_assist_show_orderOp(){
		$order_id = intval($_GET['order_id']);
		if($order_id <= 0 ){
			showMessage(L('miss_order_number'));
		}
		$model_order	= Model('order');
		$order_info	= $model_order->getOrderInfo(array('order_id'=>$order_id),array('order_goods','order_common','store'));

		//订单变更日志
		$log_list	= $model_order->getOrderLogList(array('order_id'=>$order_info['order_id']));
		Tpl::output('order_log',$log_list);

		//退款退货信息
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['order_id'] = $order_info['order_id'];
		$condition['seller_state'] = 2;
		$condition['admin_time'] = array('gt',0);
		$return_list = $model_refund->getReturnList($condition);
		Tpl::output('return_list',$return_list);

		//退款信息
		$refund_list = $model_refund->getRefundList($condition);
		Tpl::output('refund_list',$refund_list);

		//买家信息
		$buyer_info=Model('member')->getMemberInfoByID($order_info['buyer_id']);
		Tpl::output('buyer_info',$buyer_info);

		//卖家信息
		$seller_store_info=Model('store')->getStoreInfoByID($order_info['store_id']);
		$seller_member_info=Model('member')->getMemberInfoByID($seller_store_info['member_id']);
		Tpl::output('seller_store_info',$seller_store_info);
		Tpl::output('seller_member_info',$seller_member_info);

		//卖家发货信息
		if (!empty($order_info['extend_order_common']['daddress_id'])) {
			$daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
			Tpl::output('daddress_info',$daddress_info);
		}

		Tpl::output('order_info',$order_info);

		//协调订单内容
		$condition_assist=array();
		$condition_assist['order_id'] = $order_info['order_id'];
		$assist_order = $model_order->getAssistOrderLog($condition_assist);
		$admin_assist_list = array();
		switch($assist_order['assist_content']){
			case '库存已空，申请协助':
				$admin_assist_list[] = '不做处理';
				$admin_assist_list[] = '指派工作人员协调库存';
				break;
			case '分单错误，申请转单':
				$admin_assist_list[] = '没有分错，继续配送';
				$admin_assist_list[] = '分单错误，转单子至';
				break;
			case '库存已空，请求协助':
				$admin_assist_list[] = '不做处理';
				$admin_assist_list[] = '指派工作人员协调库存';
				break;
			case '配送员繁忙，请求协助':
				$admin_assist_list[] = '不做处理，继续配送';
				$admin_assist_list[] = '指派工作人员进行协助';
				$admin_assist_list[] = '延迟配送';
				break;
			case '客户没带手机，帮忙完成':
				$admin_assist_list[] = '不做处理';
				$admin_assist_list[] = '完成订单';
				break;
			case '客户不在家，延迟配送':
				$admin_assist_list[] = '不做处理';
				$admin_assist_list[] = '延迟配送';
				break;
			case '客户手机打不通':
				$admin_assist_list[] = '不做处理，继续配送';
				$admin_assist_list[] = '延迟配送';
				$admin_assist_list[] = '取消订单';
				break;
			case '客户修改地址和配送时间':
				$admin_assist_list[] = '不做处理';
				$admin_assist_list[] = '延迟配送';
				$admin_assist_list[] = '手动修改配送时间';
				break;
			default:
				$admin_assist_list[] = '不做处理';
				$admin_assist_list[] = '延迟配送';
				$admin_assist_list[] = '取消订单';
				$admin_assist_list[] = '完成订单';
				$admin_assist_list[] = '指派工作人员协助';
				$admin_assist_list[] = '其他';
		}
		$assist_order['admin_assist_list'] = $admin_assist_list;

		Tpl::output('assist_order',$assist_order);
		Tpl::showpage('adt_order.assist_view');
	}

	/**
	 * 跑腿邦，管理员编辑协调订单
	 *
	 */
	public function adt_assist_editOp()
	{
		if (isset($_REQUEST['audit_content']) && !empty($_REQUEST['audit_content'])) {
			$assist['audit_content'] = $_REQUEST['audit_content'];
		}else{
			showMessage('请选择处理内容');
		}

		if (isset($_REQUEST['audit_msg']) && !empty($_REQUEST['audit_msg'])) {
			$assist['audit_msg'] = $_REQUEST['audit_msg'];
		}


		$order_id = intval($_GET['order_id']);
		if ($order_id <= 0) {
			showMessage(L('miss_order_number'));
		}

		$model_order = Model('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$order_info = $model_order->getOrderInfo($condition);

		if($assist['audit_content']=='延迟配送'){
			if($order_info['hope_time']!=0) {
				$hope_data = array();
				$hope_data['hope_time'] = array('exp', 'hope_time+' . (60 * 30));
				$model_order->editOrder($hope_data, $condition);

				//添加延迟配送日志
				$data = array();
				$data['order_id'] = intval($order_id);
				$data['log_role'] = 'admin';
				$data['log_msg'] = '延迟配送半小时';
				$data['log_user'] = $this->admin_info['name'];
				$data['log_orderstate'] = $order_info['order_state'];
				$model_order->addOrderLog($data);
			}else{
				showMessage('客户没有设置希望配送时间，无法延迟配送');
			}
		}elseif($assist['audit_content']=='取消订单'){
			if($order_info['order_state']==10){
				$result = Logic('order')->changeOrderStateCancel($order_info,'admin',$this->admin_info['name'],'',true,true);
			}elseif($order_info['order_state']==20 || $order_info['order_state']==30 || $order_info['order_state']==35){
				$result = Logic('order')->adt_changeOrderStateCancel($order_info,'admin',$this->admin_info['name'],'',true,true);
			}else{
				showMessage('该状态无法取消，取消失败');
			}
			if(!$result['state']){
				showMessage('取消失败');
			}
		}elseif($assist['audit_content']=='完成订单'){
			if($order_info['order_state']!=0 && $order_info['order_state']!=40) {
				$result = Logic('order')->adt_changeOrderStateReceive($order_info, 'admin', $this->admin_info['name']);
				if (!$result['state']) {
					showMessage('设置订单，提交失败');
				}
			}else{
				showMessage('该订单状态不能设置完成订单，提交失败');
			}
		}

		$assist['audit_user'] = $this->admin_info['name'];
		$assist['assist_type'] = 'sys';
		$state = $model_order->editAssistOrderLog($assist,$condition);

		if($state){
			showMessage('提交成功','index.php?act=order&op=adt_assist_index');
		}else{
			showMessage('提交失败');
		}

	}

	public function adt_show_order($order_info){

		$model_order	= Model('order');
		//订单变更日志
		$log_list	= $model_order->getOrderLogList(array('order_id'=>$order_info['order_id']));
		Tpl::output('order_log',$log_list);

		//退款退货信息
		$model_refund = Model('refund_return');
		$condition = array();
		$condition['order_id'] = $order_info['order_id'];
		$condition['seller_state'] = 2;
		$condition['admin_time'] = array('gt',0);
		$return_list = $model_refund->getReturnList($condition);
		Tpl::output('return_list',$return_list);

		//退款信息
		$refund_list = $model_refund->getRefundList($condition);
		Tpl::output('refund_list',$refund_list);

		//买家信息
		$buyer_info=Model('member')->getMemberInfoByID($order_info['buyer_id']);
		Tpl::output('buyer_info',$buyer_info);

		//卖家信息
		$seller_store_info=Model('store')->getStoreInfoByID($order_info['store_id']);
		$seller_member_info=Model('member')->getMemberInfoByID($seller_store_info['member_id']);
		Tpl::output('seller_store_info',$seller_store_info);
		Tpl::output('seller_member_info',$seller_member_info);

		//卖家发货信息
		if (!empty($order_info['extend_order_common']['daddress_id'])) {
			$daddress_info = Model('daddress')->getAddressInfo(array('address_id'=>$order_info['extend_order_common']['daddress_id']));
			Tpl::output('daddress_info',$daddress_info);
		}

		//异常记录
		$assist_list	= $model_order->getOrderAssistList(array('order_id'=>$order_info['order_id']));
		Tpl::output('order_assist',$assist_list);

		//投诉信息
		$condition = array();
		$condition['order_id']=$order_info['order_id'];
		$complain=Model('complain_league')->where($condition)->select();
		Tpl::output('order_complain',$complain);


		Tpl::output('order_info',$order_info);
		Tpl::showpage('adt_order.view');
	}

	/**
	 * 导出
	 *
	 */
	public function export_step1Op(){
		$lang	= Language::getLangContent();

	    $model_order = Model('order');
        $condition	= array();
        if($_GET['order_sn']) {
        	$condition['order_sn'] = $_GET['order_sn'];
        }
        if($_GET['store_name']) {
            $condition['store_name'] = $_GET['store_name'];
        }
        if(in_array($_GET['order_state'],array('0','10','20','30','40'))){
        	$condition['order_state'] = $_GET['order_state'];
        }
        if($_GET['payment_code']) {
            $condition['payment_code'] = $_GET['payment_code'];
        }
        if($_GET['buyer_name']) {
            $condition['buyer_name'] = $_GET['buyer_name'];
        }
        if($_GET['order_type']) {
            $condition['order_type'] = $_GET['order_type'];
        }
        $if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time']);
        $if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time']);
        $start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
        $end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }

		$order_extend=array(
			'order_common',
			'order_goods',
		);
		if (!is_numeric($_GET['curpage']) && !is_numeric($_GET['cursection'])){
			$count = $model_order->getOrderCount($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=order&op=index');
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$data = $model_order->getOrderList($condition,'','*','order_id desc',self::EXPORT_SIZE,$order_extend);
				$this->createExcel2($data);
			}
		}elseif(is_numeric($_GET['cursection'])) {	//分段下载
			$limit1 = ($_GET['cursection']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_order->getOrderList($condition,'','*','order_id desc',"{$limit1},{$limit2}",$order_extend);
			$this->createExcel2($data);
		}else{	//分页下载
			$limit1 = ($_GET['curpage']-1) * self::ONE_PAGE_SIZE;
			$limit2 = self::ONE_PAGE_SIZE;
			$data = $model_order->getOrderList($condition,'','*','order_id desc',"{$limit1},{$limit2}",$order_extend);
			$this->createExcel2($data);
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
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_no'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_store'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_buyer'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_xtimd'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_count'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_yfei'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_paytype'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_state'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_storeid'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_buyerid'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_bemail'));
		//data
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>'NC'.$v['order_sn']);
			$tmp[] = array('data'=>$v['store_name']);
			$tmp[] = array('data'=>$v['buyer_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['order_amount']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['shipping_fee']));
			$tmp[] = array('data'=>orderPaymentName($v['payment_code']));
			$tmp[] = array('data'=>orderState($v));
			$tmp[] = array('data'=>$v['store_id']);
			$tmp[] = array('data'=>$v['buyer_id']);
			$tmp[] = array('data'=>$v['buyer_email']);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('exp_od_order'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('exp_od_order'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}

	/**
	 * 生成excel
	 *
	 * @param array $data
	 */
	private function createExcel2($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_no'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_store'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_buyer'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_xtimd'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_count'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_yfei'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_paytype'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_state'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_storeid'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_buyerid'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_od_bemail'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'快递公司');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'快递单号');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'买家地址');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'买家电话');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'买家姓名');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'订单完成时间');
		$excel_data[0][] = array('styleid'=>'s_title','data'=>'商品');
		//data
		$express = rkcache('express',true);
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>'AGG'.$v['order_sn']);
			$tmp[] = array('data'=>$v['store_name']);
			$tmp[] = array('data'=>$v['buyer_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['add_time']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['order_amount']));
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['shipping_fee']));
			$tmp[] = array('data'=>orderPaymentName($v['payment_code']));
			$tmp[] = array('data'=>orderState($v));
			$tmp[] = array('data'=>$v['store_id']);
			$tmp[] = array('data'=>$v['buyer_id']);
			$tmp[] = array('data'=>$v['buyer_email']);
			$tmp[] = array('data'=>$express[$v['extend_order_common']['shipping_express_id']]['e_name']);
			$tmp[] = array('data'=>$v['shipping_code']);
			$tmp[] = array('data'=>$v['extend_order_common']['reciver_info']['address']);
			$tmp[] = array('data'=>$v['extend_order_common']['reciver_info']['mob_phone']);
			$tmp[] = array('data'=>$v['extend_order_common']['reciver_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['finnshed_time']));
			$goods_info=array();
			foreach($v['extend_order_goods'] as $v2){
				$goods_info[]=$v2['goods_name'].'('.$v2['goods_num'].'个)';
			}
			$goods_info=implode(',',$goods_info);
			$tmp[]=array('data'=>$goods_info);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('exp_od_order'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('exp_od_order'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
	/**
	 * 订单统计
	 */
	public function order_analysisOp(){
		$model_order = Model('order');
		$condition	= array();//订单条件
		$store_condition = array();//商户条件
		$member_condition = array();//用户条件
		$rebate_condition = array();//返佣条件
		$condition['order.order_state'] = 40;
		$store_condition['store_state'] = 1;
		//区域条件  用户查询暂时不加区域条件  等2.1.6运行一段时间后 再加
		if (isset($_REQUEST['agent_area_name']) && !empty($_REQUEST['agent_area_name'])) {
			$agent_area_arr = explode(' ', $_REQUEST['agent_area_name']);
			if (isset($agent_area_arr[0]) && !empty($agent_area_arr[0])) {
				$province_info = Model('area')->getAreaInfo(array('area_name'=>$agent_area_arr[0],'area_deep'=>1));
				if(!empty($province_info)) {
					$condition['store.province_id'] = $province_info['area_id'];
					$store_condition['province_id'] = $province_info['area_id'];
					if (isset($agent_area_arr[1]) && !empty($agent_area_arr[1])) {
						$city_info = Model('area')->getAreaInfo(array('area_name'=>$agent_area_arr[1],'area_deep'=>2,'area_parent_id'=>$province_info['area_id']));
						if(!empty($city_info)) {
							$condition['store.city_id'] = $city_info['area_id'];
							$store_condition['city_id'] = $city_info['area_id'];
							if (isset($agent_area_arr[2]) && !empty($agent_area_arr[2])) {
								$district_info = Model('area')->getAreaInfo(array('area_name'=>$agent_area_arr[2],'area_deep'=>3,'area_parent_id'=>$city_info['area_id']));
								if(!empty($district_info)) {
									$condition['store.district_id'] = $district_info['area_id'];
									$store_condition['district_id'] =  $district_info['area_id'];
								}
							}
						}
					}
				}
			}
		}
		//时间
		$if_start_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_time']);
		$if_end_time = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_time']);
		$start_unixtime = $if_start_time ? strtotime($_GET['query_start_time']) : null;
		$end_unixtime = $if_end_time ? strtotime($_GET['query_end_time']): null;
		if ($start_unixtime || $end_unixtime) {
			$condition['order.add_time'] = array('time',array($start_unixtime,$end_unixtime));
			$store_condition['store_time'] = array('time',array($start_unixtime,$end_unixtime));
			$member_condition['member_time']  = array('time',array($start_unixtime,$end_unixtime));
			$rebate_condition['add_time']  = array('time',array($start_unixtime,$end_unixtime));
		}
		//类型
		if(!empty($_GET['rebate_type'])) {
			$condition['order.order_type'] = array('in',$_GET['rebate_type']);
			$old_array = $_GET['rebate_type'];
			foreach($old_array as $key=>$value ) {
				if($value==3) {
					$old_array[$key] = 4;
				}
			}
			$store_condition['store_type'] = array('in',$old_array);
		}
		$result_list = array();
		//订单总数
		$order_count = Model()->table('order,store')->join('inner')->on('order.store_id=store.store_id')->where($condition)->count('DISTINCT(order.order_id)');
		$result_list['order_count'] = $order_count;
		//订单金额
		$order_sum = Model()->table('order,store')->join('inner')->on('order.store_id=store.store_id')->where($condition)->sum('order.order_amount');
		if(!empty($order_sum)) {
			$result_list['order_sum'] = $order_sum;
		}
		else {
			$result_list['order_sum'] = 0;
		}
		//商户总数
		$store_sum = Model()->table('store')->where($store_condition)->count('DISTINCT(store_id)');
		$result_list['store_sum'] = $store_sum;
		//用户数量
		$member_sum = Model()->table('member')->where($member_condition)->count('DISTINCT(member_id)');
		$result_list['member_sum'] = $member_sum-$store_sum;
		//返佣总额
		$order_array = Model()->table('order,store')->join('inner')->on('order.store_id=store.store_id')->where($condition)->field('order.order_id')->limit('0,1000')->select();
		for ($i=1;$i<ceil($order_count/1000);$i++) {
			$begin = $i*1000;
			$tempList = Model()->table('order,store')->join('inner')->on('order.store_id=store.store_id')->where($condition)->field('order.order_id')->limit("$begin,1000")->select();
			$order_array =array_merge($order_array,$tempList);
		}
		$id_array = agg_array_column($order_array,'order_id');
		$rebate_condition['order_id'] = array('in',$id_array);
		$rebate_condition['user_type'] = 4;
		$rebate_sum = Model()->table('rebate_records')->where($rebate_condition)->sum('rebate');
		$result_list['rebate_sum'] = $rebate_sum*-1;
		Tpl::output('result_list',$result_list);
		Tpl::showpage('order_analysis');
	}
}
