<?php
/**
 * 搜索
 */
defined('emall') or exit('Access Invalid!');

require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchClient.php');
require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchIndex.php');
require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchSearch.php');
require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchSuggest.php');

class open_search_goodsModel{

    protected $access_key ;
    protected $secret;
    protected $host;
    protected $key_type;  //固定值，不必修改
    public function __construct() {
        $this->access_key   = C('open_search.access_key');
        $this->secret       = C('open_search.secret');
        $this->host         = C('open_search.host');
        $this->key_type     = C('open_search.key_type');
    }

    /**
     * 阿里OpenSearch全文搜索。获取商品列表
     * @param  [string] $keywords       [关键字]
     * @param  [int] $store_state       [1:默认 0,1 非必填]
     * @param  [int] $is_agree          [1:默认 0,1 非必填]
     * @param  [int] $order             [非必填]
     * @param  [int] $curpage           [当前页码 默认:1 非必填]
     * @param  [int] $page              [每页显示数量 默认:20 非必填]
     * @return [array]                   [array]
     *
     */
    public function get_store_local() {
        if(!C('open_search.open')){
            $rt['status'] = 'ERROR';
            $rt['msg'] = '未开启';
            return $rt;
        }
        $opts = array('host'=>$this->host);
        // 实例化一个client 使用自己的accesskey和Secret替换相关变量
        $client = new CloudsearchClient($this->access_key,$this->secret,$opts,$this->key_type);
        $app_name = "goods_list";
        // 实例化一个应用类index_obj
        $index_obj = new CloudsearchIndex($app_name,$client);
        $result = $index_obj->createByTemplateName("builtin_novel");

        // 实例化一个搜索类 search_obj
        $search_obj = new CloudsearchSearch($client);
        // 指定一个应用用于搜索
        $search_obj->addIndex($app_name);

        //设置返回字段
        $search_obj->addFetchFields("goods_name");
        $search_obj->addFetchFields("goods_id");
        $search_obj->addFetchFields("goods_salenum");
        $search_obj->addFetchFields("evaluate_good_star");
        $search_obj->addFetchFields("evaluation_count");
        $search_obj->addFetchFields("goods_price");
        $search_obj->addFetchFields("goods_image");

        $goods_state    =   1;
        $goods_verify   =   1;
        $order = isset($_REQUEST['key_sort'])?intval($_REQUEST['key_sort']):0;
        $keywords = isset($_REQUEST['keyword'])?$_REQUEST['keyword']:'';
        $search_obj->setQueryString("default:'".$keywords."'");

        //设置搜索过滤
        $search_obj->addFilter('goods_state='.$goods_state);
        $search_obj->addFilter('goods_verify='.$goods_verify);
        switch ($order) {
            case '1':
                //销量
                $search_obj->addSort('goods_salenum','-');
                break;
            case '2':
                $search_obj->addSort('goods_price','-');
                break;
            case '3':
                $search_obj->addSort('goods_addtime','-');
                break;
            default:
                $search_obj->addSort('is_own_shop','-');
                break;
        }

        $curpage = (!empty($_REQUEST['curpage'])?$_REQUEST['curpage']:1);
        $hit = (!empty($_REQUEST['page'])?$_REQUEST['page']:20);
        //设置结果偏移量
        $search_obj->setStartHit(($curpage-1)*$hit);

        //每页获取20条记录
        $search_obj->setHits($hit);
        // 指定返回的搜索结果的格式为json
        $search_obj->setFormat("fulljson");

        // 执行搜索，获取搜索结果
        $json = $search_obj->search();
        // 将json类型字符串解码
        $result = json_decode($json,true);
        $rt = array();
        $rt['status'] = $result['status'];
        $rt['request_id'] = $result['request_id'];
        $rt['result']['num'] = $result['result']['viewtotal'];
        $rt['result']['pagetotal'] = ceil($result['result']['viewtotal']/$hit);
//        var_dump($search_obj->getQuery());
//        echo '<pre/>';print_r($result);
//        @var_dump($result['result']['items'][0]);
        foreach ($result['result']['items'] as $key => $value) {
            $rt['result']['items'][$key] =  $value['fields'];
            unset($rt['result']['items'][$key]['index_name']);
        }
        if(count($rt['result']['items']) === 0)
        {
            $rt['status'] = $result['status'];
            $rt['msg'] = '暂无数据';
            $rt['result']['num'] = 0;
            $rt['result']['pagetotal'] = 0;
            $rt['result']['items'] = array();
        }
        return $rt;
    }



}
