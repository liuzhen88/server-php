<?php
/**
 * 商品栏目管理
 */
defined('emall') or exit('Access Invalid!');
class goodsControl extends SystemControl{
    const EXPORT_SIZE = 5000;
    public function __construct() {
        parent::__construct ();
        Language::read('goods');
    }

    /**
     * 商品设置
     */
    public function goods_setOp() {
        $model_setting = Model('setting');
        if (chksubmit()){
                $update_array = array();
                $update_array['goods_verify'] = $_POST['goods_verify'];
                $result = $model_setting->updateSetting($update_array);
                if ($result === true){
                        $this->log(L('nc_edit,nc_goods_set'),1);
                        showMessage(L('nc_common_save_succ'));
                }else {
                        $this->log(L('nc_edit,nc_goods_set'),0);
                        showMessage(L('nc_common_save_fail'));
                }
        }
        $list_setting = $model_setting->getListSetting();
        Tpl::output('list_setting',$list_setting);
        Tpl::showpage('goods.setting');
    }
	
    /**
     * 商品推荐
     */
    public function goods_recommendOp(){
        $lang	= Language::getLangContent();
        $model_recommend = Model('recommend');
        $model_goods = Model('goods');
        /**
         * 保存
         */
        if ($_POST['form_submit'] == 'ok'){
            /**
             * 验证
             */
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input"=>$_POST["recommend_id"], "require"=>"true", "message"=>$lang['goods_recommend_choose_type']),
                array("input"=>$_POST["goods_id"], "require"=>"true", "message"=>$lang['goods_recommend_goods_null']),
            );
            $error = $obj_validate->validate();
            if ($error != ''){
                showMessage($error);
            }else {
                $tmp = explode(',',$_POST['goods_id']);
                if (is_array($tmp)){
                    foreach ($tmp as $k => $v){
                        $count	= $model_recommend->getCount(array('recommend_id'=>$_POST["recommend_id"],'goods_id'=>$v));
                        if($count<=0){
                            $insert_array = array();
                            $insert_array['recommend_id'] = $_POST["recommend_id"];
                            $insert_array['goods_id'] = $v;
                            $model_recommend->addRecommendGoods($insert_array);
                            unset($insert_array);
                        }
                    }
                }
                showMessage('推荐商品成功！','index.php?act=goods&op=goods');
            }
        }
        if (empty($_GET['goods_id'])){
            showMessage($lang['goods_recommend_choose_goods']);
        }
        /**
         * 推荐列表
         */
        $recommend_list = $model_recommend->getRecommendList($condition);
        if (empty($recommend_list)){
            showMessage($lang['goods_recommend_type_null'],'index.php?act=recommend&op=recommend_add');
        }
        Tpl::output('recommend_list',$recommend_list);
        Tpl::output('goods_id',$_GET['goods_id']);
        Tpl::showpage('goods.recommend');
    }
    
    /**
     * 分销商品推荐
     * 
     * @author lijunhua
     * @since  2015-09-21
     */
    public function goods_distribution_recommendOp(){
        if (empty($_GET['goods_id'])) {
            showMessage('请选择推荐商品');
        }
        
        /**
         * 保存
         */
        if ($_POST['form_submit'] == 'ok') {
            $goods_str           = implode(',', array_unique(explode(',', $_GET['goods_id'])));

            if (isset($_POST['distribution_recommend']) && !empty($_POST['distribution_recommend'])) {
                $up_data['is_recommend'] = (int)$_POST['distribution_recommend'] - 1;
            } else {
                $up_data['is_recommend'] = 1;
            }
            
            $where = array('goods_commonid' => array('in', $goods_str));
            
            $model_distribution  = Model('goods_distribution');
            $model_distribution->editGoodCommonDistribution($up_data, $where);
            $model_distribution->editGoodDistribution($up_data, $where);
   
            showMessage('分销商品推荐成功！','index.php?act=goods&op=goods');
        }
        Tpl::output('goods_id', $_GET['goods_id']);
        Tpl::showpage('goods.recommend_distribution');
    }
	
    /**
     * 商品管理
     */
    public function goodsOp() {
        $model_goods = Model ( 'goods' );
        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = intval($_REQUEST['choose_gcid']))>0?$t:0;
        $gccache_arr = Model('goods_class')->getGoodsclassCache($choose_gcid,3);
	    Tpl::output('gc_json',json_encode($gccache_arr['showclass']));
		Tpl::output('gc_choose_json',json_encode($gccache_arr['choose_gcid']));

        /**
         * 查询条件
         */
        $where = array();
        if ($_GET['search_goods_name'] != '') {
            $where['goods_name'] = array('like', '%' . trim($_GET['search_goods_name']) . '%');
        }
        if (intval($_GET['search_commonid']) > 0) {
            $where['goods_commonid'] = intval($_GET['search_commonid']);
        }
        if ($_GET['search_store_name'] != '') {
            $where['store_name'] = array('like', '%' . trim($_GET['search_store_name']) . '%');
        }
        if (intval($_GET['b_id']) > 0) {
            $where['brand_id'] = intval($_GET['b_id']);
        }
        if ($choose_gcid > 0){
		    $where['gc_id_'.($gccache_arr['showclass'][$choose_gcid]['depth'])] = $choose_gcid;
		}
        if (in_array($_GET['search_state'], array('0','1','10'))) {
            $where['goods_state'] = $_GET['search_state'];
        }
        if (in_array($_GET['search_verify'], array('0','1','10'))) {
            $where['goods_verify'] = $_GET['search_verify'];
        }
	if ($_GET['good_type'] != '') {
            $where['good_type'] = $_GET['good_type'];
        }
        
	if ($_GET['is_distribution'] != '') {
            $where['is_distribution'] = (int)$_GET['is_distribution'] - 1;
        }
        
	if ($_GET['distribution_recommend'] != '') {
            $where['distribution_recommend'] = (int)$_GET['distribution_recommend'] - 1;
        }
        
        
        switch ($_GET['type']) {
            // 禁售
            case 'lockup':
                $goods_list = $model_goods->getGoodsCommonLockUpList($where);
                break;
            /** 推荐商品 */
            case 'recommend':
                if (!empty($_POST['id'])){
                    @header("Location: index.php?act=goods&op=goods_recommend&goods_id=".implode(',',$_POST['id']));
                    exit;
                }else {
                    showMessage('未选择推荐商品！');
                }
                break;
            /** 分销推荐商品 */
            case 'distribution_recommend':
                if (!empty($_POST['id'])){
                    @header("Location: index.php?act=goods&op=goods_distribution_recommend&goods_id=".implode(',',$_POST['id']));
                    exit;
                }else {
                    showMessage('未选择推荐商品！');
                }
                break;
            // 等待审核
            case 'waitverify':
                $goods_list = $model_goods->getGoodsCommonWaitVerifyList($where, '*', 10, 'goods_verify desc, goods_commonid desc');
                break;
            // 全部商品
            default:
                $goods_list = $model_goods->getGoodsCommonByDistributionList($where);
                break;
        }

        Tpl::output('goods_list', $goods_list);
        Tpl::output('page', $model_goods->showpage(2));

        $storage_array = $model_goods->calculateStorage($goods_list);
        Tpl::output('storage_array', $storage_array);

        // 品牌
        $brand_list = Model('brand')->getBrandPassedList(array());

        Tpl::output('search', $_GET);
        Tpl::output('brand_list', $brand_list);
        Tpl::output('good_type', $this->_get_good_type_array());

        Tpl::output('state', array('1' => '出售中', '0' => '仓库中', '10' => '违规下架'));

        Tpl::output('verify', array('1' => '通过', '0' => '未通过', '10' => '等待审核'));

        Tpl::output('ownShopIds', array_fill_keys(Model('store')->getOwnShopIds(), true));

        switch ($_GET['type']) {
            // 禁售
            case 'lockup':
                Tpl::showpage('goods.close');
                break;
            // 等待审核
            case 'waitverify':
                Tpl::showpage('goods.verify');
                break;
            // 全部商品
            default:
                Tpl::showpage('goods.index');
                break;
        }
    }

    private function _get_good_type_array() {
        return array(
            '1' => '本土店铺',
            '2' => '线上店铺',
            '3' => '配送模版商品',
        );
    }
    /**
     * 跑腿邦商品审核
     */
    public function adt_goodsOp(){
        $province=Model('area')->getTopLevelAreas();
        Tpl::output('province', $province);

        if(isset($_GET['province']) && intval($_GET['province'])!=0){
            $where['store.province_id']=intval($_GET['province']);
        }
        if(isset($_GET['city']) && intval($_GET['city'])!=0){
            $where['store.city']=intval($_GET['city']);
        }
        if(isset($_GET['area']) && intval($_GET['area'])!=0){
            $where['store.area']=intval($_GET['area']);
        }
        if(isset($_GET['verify_type']) && intval($_GET['verify_type'])==1){
            $where['goods_league.league_goods_verify']=10;
        }
        if(isset($_GET['verify_type']) && intval($_GET['verify_type'])==2){
            $where['goods_league.league_goods_verify']=1;
        }
        if(isset($_GET['search_goods_name']) && trim($_GET['search_goods_name'])!=''){
            $where['goods_league.goods_name|goods_league.league_store_name']=array('like','%'.trim($_GET['search_goods_name']).'%');
        }

        $on='store.store_id=goods_league.league_store_id';
        $where['goods_league.league_price_verify']=10;
        $goods_list=Model()->table('goods_league,store')->field('*,goods_league.id leage_id')->on($on)->where($where)->page(10)->select();
        $page= Model()->showpage(2);
        $class=Model('goods_class')->getGoodsClassListAll('all');
        $class=array_under_reset($class,'gc_id');
        Tpl::output('goods_list', $goods_list);
        Tpl::output('class', $class);
        Tpl::output('page',$page);
        Tpl::showpage('goods.adt_verify');
    }

    /**
     * 跑腿邦，商品审核通过(要审核的商品league_goods_verify可能为1，可能为10。league_price_verify 一定为10)
     */
    public function adt_goods_verify_passOp(){
        if (chksubmit()) {
            $commonids = $_REQUEST['id'];
            $commonid_array = explode(',', $commonids);
            foreach ($commonid_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(L('nc_common_op_fail'), 'reload');
                }
            }
            //注意，$update先后顺序不能修改！！！
            $update = array();
            $update['league_goods_price'] = array('exp','((league_goods_verify=10)*league_goods_price+(league_goods_verify=1)*league_goods_change_price)');
            $update['league_goods_verify'] = 1;
            $update['league_price_verify'] = 1;

            $where = array();
            $where['id'] = array('in', $commonid_array);
            $where['league_price_verify']=10;   //以防错误数据。

            Model('goods_league')->where($where)->update($update);
            showDialog(L('nc_common_op_succ'), 'reload', 'succ');
        }
        Tpl::output('id', $_GET['id']);
        Tpl::showpage('goods.adt_goods_verify_pass', 'null_layout');
    }

    /**
     * 跑腿邦，商品审核拒绝(要审核的商品league_goods_verify可能为1，可能为10。league_price_verify 一定为10)
     */
    public function adt_goods_verify_rejectOp(){
        if (chksubmit()) {
            $commonids = $_REQUEST['id'];
            $commonid_array = explode(',', $commonids);
            foreach ($commonid_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(L('nc_common_op_fail'), 'reload');
                }
            }

            $where = array();
            $where['id'] = array('in', $commonid_array);
            $where['league_price_verify']=10;   //以防错误数据。
            $where['league_goods_verify']=10;   //审核中的商品才能删

            Model('goods_league')->where($where)->delete();

            $where['league_goods_verify']=1;    //销售中的商品，拒绝改价申请
            $update = array();
            $update['league_price_verify'] = 0;
            Model('goods_league')->where($where)->update($update);

            showDialog(L('nc_common_op_succ'), 'reload', 'succ');
        }
        Tpl::output('id', $_GET['id']);
        Tpl::showpage('goods.adt_goods_verify_reject', 'null_layout');
    }

    /**
     * 违规下架
     */
    public function goods_lockupOp() {
        if (chksubmit()) {
            $commonids = $_POST['commonids'];
            $commonid_array = explode(',', $commonids);
            foreach ($commonid_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(L('nc_common_op_fail'), 'reload');
                }
            }
            $update = array();
            $update['goods_stateremark'] = trim($_POST['close_reason']);

            $where = array();
            $where['goods_commonid'] = array('in', $commonid_array);

            Model('goods')->editProducesLockUp($update, $where);
            showDialog(L('nc_common_op_succ'), 'reload', 'succ');
        }
        Tpl::output('commonids', $_GET['id']);
        Tpl::showpage('goods.close_remark', 'null_layout');
    }
    

    /**
     * 删除商品
     */
    public function goods_delOp() {
        $common_id = intval($_GET['goods_id']);
        if ($common_id <= 0) {
            showDialog(L('nc_common_op_fail'), 'reload');
        }
        Model('goods')->delGoodsAll(array('goods_commonid' => $common_id));
        showDialog(L('nc_common_op_succ'), 'reload', 'succ');
    }

    /**
     * 审核商品
     */
    public function goods_verifyOp(){
        if (chksubmit()) {
            $commonids = $_POST['commonids'];
            $commonid_array = explode(',', $commonids);
            foreach ($commonid_array as $value) {
                if (!is_numeric($value)) {
                    showDialog(L('nc_common_op_fail'), 'reload');
                }
            }
            $update2 = array();
            $update2['goods_verify'] = intval($_POST['verify_state']);

            $update1 = array();
            $update1['goods_verifyremark'] = trim($_POST['verify_reason']);
            $update1 = array_merge($update1, $update2);
            $where = array();
            $where['goods_commonid'] = array('in', $commonid_array);

            $model_goods = Model('goods');
            if (intval($_POST['verify_state']) == 0) {
                $model_goods->editProducesVerifyFail($where, $update1, $update2);
            } else {
                $model_goods->editProduces($where, $update1, $update2);
            }
            showDialog(L('nc_common_op_succ'), 'reload', 'succ');
        }
        Tpl::output('commonids', $_GET['id']);
        Tpl::showpage('goods.verify_remark', 'null_layout');
    }

    /**
     * ajax获取商品列表
     */
    public function get_goods_list_ajaxOp() {
        $commonid = $_GET['commonid'];
        if ($commonid <= 0) {
            echo 'false';exit();
        }
        $model_goods = Model('goods');
        $goodscommon_list = $model_goods->getGoodeCommonInfoByID($commonid, 'spec_name');
        if (empty($goodscommon_list)) {
            echo 'false';exit();
        }
        $goods_list = $model_goods->getGoodsList(array('goods_commonid' => $commonid), 'goods_id,goods_spec,store_id,goods_price,goods_serial,goods_storage,goods_image');
        if (empty($goods_list)) {
            echo 'false';exit();
        }

        $spec_name = array_values((array)unserialize($goodscommon_list['spec_name']));
        foreach ($goods_list as $key => $val) {
            $goods_spec = array_values((array)unserialize($val['goods_spec']));
            $spec_array = array();
            foreach ($goods_spec as $k => $v) {
                $spec_array[] = '<div class="goods_spec">' . $spec_name[$k] . L('nc_colon') . '<em title="' . $v . '">' . $v .'</em>' . '</div>';
            }
            $goods_list[$key]['goods_image'] = thumb($val, '60');
            $goods_list[$key]['goods_spec'] = implode('', $spec_array);
            $goods_list[$key]['url'] = urlShop('goods', 'index', array('goods_id' => $val['goods_id']));
        }

        /**
         * 转码
         */
        if (strtoupper(CHARSET) == 'GBK') {
            Language::getUTF8($goods_list);
        }
        echo json_encode($goods_list);
    }

}
