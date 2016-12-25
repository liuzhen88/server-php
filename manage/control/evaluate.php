<?php
/**
 * 商品评价
 ***/

defined('emall') or exit('Access Invalid!');
class evaluateControl extends SystemControl{
	public function __construct() {
		parent::__construct();
		Language::read('evaluate');
	}

	public function indexOp() {
		$this->evalgoods_listOp();
	}

	/**
	 * 商品来自买家的评价列表
	 */
	public function evalgoods_listOp() {
		$model_evaluate_goods = Model('evaluate_goods');

		$condition = array();
		//商品名称
		if (!empty($_GET['goods_name'])) {
			$condition['geval_goodsname'] = array('like', '%'.$_GET['goods_name'].'%');
		}
		//店铺名称
		if (!empty($_GET['store_name'])) {
			$condition['geval_storename'] = array('like', '%'.$_GET['store_name'].'%');
		}
        $condition['geval_addtime'] = array('time', array(strtotime($_GET['stime']), strtotime($_GET['etime'])));
		$evalgoods_list	= $model_evaluate_goods->getEvaluateGoodsList($condition, 10);

		Tpl::output('show_page',$model_evaluate_goods->showpage());
		Tpl::output('evalgoods_list',$evalgoods_list);
		Tpl::showpage('evalgoods.index');
	}

	/**
	 * 删除商品评价
	 */
	public function evalgoods_delOp() {
		$geval_id = intval($_POST['geval_id']);
		if ($geval_id <= 0) {
			showMessage(Language::get('param_error'),'','','error');
		}

		$model_evaluate_goods = Model('evaluate_goods');

		$result = $model_evaluate_goods->delEvaluateGoods(array('geval_id'=>$geval_id));

		if ($result) {
            $this->log('删除商品评价，评价编号'.$geval_id);
			showMessage(Language::get('nc_common_del_succ'),'','','error');
		} else {
			showMessage(Language::get('nc_common_del_fail'),'','','error');
		}
	}

	/**
	 * 店铺动态评价列表
	 */
	public function evalstore_listOp() {
        $model_evaluate_store = Model('evaluate_store');

		$condition = array();
		//评价人
		if (!empty($_GET['from_name'])) {
			$condition['seval_membername'] = array('like', '%'.$_GET['from_name'].'%');
		}
		//店铺名称
		if (!empty($_GET['store_name'])) {
			$condition['seval_storename'] = array('like', '%'.$_GET['store_name'].'%');
		}
        $condition['seval_addtime_gt'] = array('time', array(strtotime($_GET['stime']), strtotime($_GET['etime'])));

		$evalstore_list	= $model_evaluate_store->getEvaluateStoreList($condition, 10);
		Tpl::output('show_page',$model_evaluate_store->showpage());
		Tpl::output('evalstore_list',$evalstore_list);
		Tpl::showpage('evalstore.index');
	}

	/**
	 * 删除店铺评价
	 */
	public function evalstore_delOp() {
		$seval_id = intval($_POST['seval_id']);
		if ($seval_id <= 0) {
			showMessage(Language::get('param_error'),'','','error');
		}

		$model_evaluate_store = Model('evaluate_store');

		$result = $model_evaluate_store->delEvaluateStore(array('seval_id'=>$seval_id));

		if ($result) {
            $this->log('删除店铺评价，评价编号'.$geval_id);
			showMessage(Language::get('nc_common_del_succ'),'','','error');
		} else {
			showMessage(Language::get('nc_common_del_fail'),'','','error');
		}
	}


	/**
	 * 商品管理
	 */
	public function goodsOp() {
		Language::read('goods');
		$model_goods = Model ( 'goods' );

		/**
		 * 查询条件
		 */
		$where = array();
		if ($_GET['search_goods_name'] != '') {
			$where['goods_name'] = array('like', '%' . trim($_GET['search_goods_name']) . '%');
		}
		if (intval($_GET['search_commonid']) > 0) {
			$where['goods_id'] = intval($_GET['search_goodsid']);
		}
		if ($_GET['search_store_name'] != '') {
			$where['store_name'] = array('like', '%' . trim($_GET['search_store_name']) . '%');
		}
		if ($_GET['good_type'] != '') {
			$where['good_type'] = $_GET['good_type'];
		}
		if ($_GET['search_store_invitation'] != '') {
			$where['store_id'] = array('in', '( select store_id from agg_store where member_id in ( select member_id from agg_member where invitation="' . trim($_GET['search_store_invitation']) . '"))','exp');
		}
		$goods_list = $model_goods->getGoodsList($where);

		Tpl::output('goods_list', $goods_list);
		Tpl::output('page', $model_goods->showpage(2));


		Tpl::output('search', $_GET);
		Tpl::output('good_type', $this->_get_good_type_array());

		Tpl::output('state', array('1' => '出售中', '0' => '仓库中', '10' => '违规下架'));

		Tpl::output('verify', array('1' => '通过', '0' => '未通过', '10' => '等待审核'));

		Tpl::output('ownShopIds', array_fill_keys(Model('store')->getOwnShopIds(), true));


		Tpl::showpage('evaluate.goods');
	}


	private function _get_good_type_array() {
		return array(
			'1' => '本土店铺',
			'2' => '线上店铺',
			'3' => '配送模版商品',
		);
	}

	/**
	 * 添加假评论
	 */
	public function add_evaluateOp(){
		if(chksubmit()){
			$goods_id=intval($_POST['goods_id']);
			$goods_info=Model('goods')->getGoodsInfoByID($goods_id);
			if(empty($goods_info)){
				showMessage('商品不存在');
			}
			$nick_name_array=require(BASE_RESOURCE_PATH.'/nickname.php');
			$rand=array_rand($nick_name_array);
			$nick_name=$nick_name_array[$rand];
			$member_info=Model('member')->getRandomMemberInfo('member_avatar is not null');
			$data['geval_orderid']=0;
			$data['geval_orderno']=0;
			$data['geval_ordergoodsid']=0;
			$data['geval_goodsid']=$goods_id;
			$data['geval_goodsname']=$goods_info['goods_name'];
			$data['geval_goodsprice']=$goods_info['goods_price'];
			$data['geval_goodsimage']=$goods_info['goods_image'];
			$data['geval_scores']=intval($_POST['score']);
			$data['geval_content']=trim($_POST['content']);
			$data['geval_isanonymous']=0;
			$data['geval_addtime']=time();
			$data['geval_storeid']=$goods_info['store_id'];
			$data['geval_storename']=$goods_info['store_name'];
			$data['geval_frommemberid']=0;
			$data['geval_frommembername']=$nick_name;
			$data['geval_state']=0;
			$data['geval_remark']='';
			$data['geval_explain']='';
			$data['geval_image']='';
			$data['is_false']=1;
			$data['false_member_avatar']=$member_info['member_avatar'];
			Model('evaluate_goods')->insert($data);
			showMessage('添加成功');
		}
		Tpl::showpage('evaluate.add','null_layout');
	}

	public function goods_evaluate_detailOp(){
		$goods_id=intval($_GET['goods_id']);
		$condition=array(
			'geval_goodsid'=>$goods_id,
			'is_false'=>1,
		);
		$evulate_list=Model('evaluate_goods')->where($condition)->page(20)->select();
		Tpl::output('page', Model()->showpage(2));
		Tpl::output('evulate_list',$evulate_list);
		Tpl::showpage('evaluate.goodsdetail');
	}

	/**
	 * 修改假评论
	 */
	public function edit_evaluateOp(){
		$geval_id=intval($_REQUEST['geval_id']);
		$evaluate_info=Model('evaluate_goods')->find($geval_id);
		if(!$evaluate_info){
			showMessage('系统异常');
		}
		if(chksubmit()){
			$data['geval_id']=$geval_id;
			$data['geval_scores']=intval($_POST['score']);
			$data['geval_content']=trim($_POST['content']);
			Model('evaluate_goods')->update($data);
			showMessage('修改成功');
		}
		Tpl::output('evaluate_info',$evaluate_info);
		Tpl::showpage('evaluate.edit','null_layout');
	}

	/**
	 * 删除假评价
	 */
	public function del_evaluateOp(){
		$geval_id=intval($_GET['geval_id']);
		$evaluate_info=Model('evaluate_goods')->delete($geval_id);
		showMessage('删除成功');
	}

}
