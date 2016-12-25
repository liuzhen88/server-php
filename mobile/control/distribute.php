<?php
/**
 * Created by PhpStorm.
 * User: xiyu
 * Date: 2015/10/28
 * Time: 1:40
 */


defined('emall') or exit('Access Invalid!');
class distributeControl extends mobileHomeControl
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_goods_list_by_storeOp(){
        $check_parm=array('store_id');
        check_request_parameter($check_parm);
        $client=ThriftClient::getInstance();
        //获取已分销商品id
        $param=array(
            'store_id'=>intval($_REQUEST['store_id']),
            'goods_state'=>1,
            'platform'=>1,
            'curpage'=>isset($_REQUEST['curpage'])?intval($_REQUEST['curpage']):1,
            'limit'=>$this->page,
        );
        $res=$client->send("DistributionGoodsService::queryStoreDistributionGoodsList",$param);
        if(!isset($res['data'])){
            output_error('服务异常',array());
        }else{
            output_data($res['data']);
        }
    }

    /**
     * 获取商户分销商品列表接口
     */
    public function getGoodsListOp()
    {

        $condition = array();
        /** @var distributionModel $distribution_model */
        $distribution_model = Model('distribution');


        $temp = $distribution_model->getWaitDistribution($condition);
//        $result['count'] = $temp['count'];
        $goods_list = (array) $temp['data'];

        if (!empty($goods_list)) {
            foreach ($goods_list as $key => $this_goods_info) {
                $goods_list[$key]['goods_image'] = cthumb($this_goods_info['goods_image'], '', $this_goods_info['store_id']);
                unset($goods_list[$key]['store_id']);
            }
        }
        $result['data'] = (array) $goods_list;
        output_data($result['data']);
    }

}