<?php
/**
 * OpenSearch 全文搜索调用类
 * @author [name] <[<solon.ring2011@gmail.com>]>
 * Alibaba OpenSearch搜索
 */

use Tpl;

defined('emall') or exit('Access Invalid!');
class open_searchControl extends mobileHomeControl{
    protected $access_key = "Jy75YdnUYRUE3enc";
    protected $secret = "o1lmG6Dgi7oYzyNLSUqWtzYfeWdqZb";
    //杭州公网API地址：http://opensearch-cn-hangzhou.aliyuncs.com
    //北京公网API地址：http://opensearch-cn-beijing.aliyuncs.com (2015年4月初开放)
    protected $host = "http://opensearch-cn-hangzhou.aliyuncs.com";
    protected $key_type = "aliyun";  //固定值，不必修改
    protected $class_array = array(
                            '1'=>'美食',
                            '17'=>'休闲娱乐',
                            '33'=>'酒店',
                            '43'=>'丽人',
                            '56'=>'结婚',
                            '63'=>'亲子',
                            '70'=>'购物',
                            '85'=>'运动健身',
                            '97'=>'生活服务',
                            '121'=>'家装',
                            '147'=>'汽车',
                            '256'=>'家居家装',
                            '283'=>'母婴',
                            '288'=>'家电办公',
                            '298'=>'图书音像',
                            '300'=>'电脑办公',
                            '303'=>'手机数码',
                            '309'=>'服装'
                            );
	public function __construct() {
        require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchClient.php');
        require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchIndex.php');
        require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchSearch.php');
        require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchSuggest.php');
        parent::__construct();
    }

    /**
     * 阿里OpenSearch全文搜索
     * @param  [string] $keywords       [关键字]
     * @param  [string] $city_name      [城市名称]
     * @param  [float] $lnt             [纬度 必填]
     * @param  [float] $lat             [经度 必填]
     * @param  [int] $store_state       [1:默认 0,1 非必填]
     * @param  [int] $is_agree          [1:默认 0,1 非必填]
     * @param  [int] $order             [ 0:默认，1：距离，2:新店优先，*3：好评（暂无），4：人均消费降序，5：人均消费升序 非必填]
     * @param  [string] $district_name  [区域名称 非必填]
     * @param  [int] $distance          [距离多少米 非必填]
     * @param  [int] $class_id          [顶级分类ID 非必填]
     * @param  [int] $class_sec_id      [二级分类ID 非必填]
     * @param  [int] $curpage           [当前页码 默认:1 非必填]
     * @param  [int] $page              [每页显示数量 默认:20 非必填]
     * @return [JSON]                   [JSON]
     * 
     */
	public function indexOp() {
        $opts = array('host'=>$this->host);
        // 实例化一个client 使用自己的accesskey和Secret替换相关变量
        $client = new CloudsearchClient($this->access_key,$this->secret,$opts,$this->key_type);
        $app_name = "agg";
        // 实例化一个应用类index_obj
        $index_obj = new CloudsearchIndex($app_name,$client);
        $result = $index_obj->createByTemplateName("builtin_novel");

        // 实例化一个搜索类 search_obj
        $search_obj = new CloudsearchSearch($client);
        // 指定一个应用用于搜索
        $search_obj->addIndex($app_name);
        
        //设置返回字段
        $search_obj->addFetchFields("store_name");
        $search_obj->addFetchFields("store_id");
        $search_obj->addFetchFields("store_avatar");
        $search_obj->addFetchFields("district_name");
        $search_obj->addFetchFields("city_name");
        $search_obj->addFetchFields("gc_parent_id");
        $search_obj->addFetchFields("per_consumption");
        $search_obj->addFetchFields("bid");

        $city_name = isset($_REQUEST['city_name'])?htmlspecialchars($_REQUEST['city_name']):'合肥市';//$city_id = isset($_REQUEST['city_id'])?intval($_REQUEST['city_id']):310;
        $store_state = isset($_REQUEST['store_state'])?intval($_REQUEST['store_state']):1;
        $is_agree = isset($_REQUEST['is_agree'])?intval($_REQUEST['is_agree']):1;
        $lng = isset($_REQUEST['lng'])?$_REQUEST['lng']:'117.31992';
        $lat = isset($_REQUEST['lat'])?$_REQUEST['lat']:'31.85168';
        $order = isset($_REQUEST['order'])?intval($_REQUEST['order']):0;//$area_id = isset($_REQUEST['area_id'])?intval($_REQUEST['area_id']):0;
        $district_name = isset($_REQUEST['district_name'])?htmlspecialchars($_REQUEST['district_name']):'';
        $distance = isset($_REQUEST['distance'])?$_REQUEST['distance']:0;
        $class_id = isset($_REQUEST['class_id'])?intval($_REQUEST['class_id']):0;
        $sec_class_id = isset($_REQUEST['sec_class_id'])?intval($_REQUEST['sec_class_id']):0;
        $keywords = isset($_REQUEST['keywords'])?$_REQUEST['keywords']:'';
                
        $keywords = $keywords.' '.$city_name.' '.$district_name; 
        $search_obj->setQueryString("default:'".$keywords."'");

        //设置搜索过滤
        $search_obj->addFilter('store_state='.$store_state);
        $search_obj->addFilter('is_agree='.$is_agree);
        $class_id && $search_obj->addFilter('gc_parent_id='.$class_id);
        $sec_class_id &&  $search_obj->addFilter('class_2='.$sec_class_id);

        $distance && $search_obj->addFilter('distance(lng,lat,"'.$lng.'", "'.$lat.'")<'.($distance/1000));
        //$search_obj->setPair("longtitude_in_query:".$lng.", latitude_in_query:".$lat."");
        //对搜索结果进行去重
        //dist_key:store_id,dist_count:1,dist_times:1,reserved:false&&kvpairs=duniqfield:store_id
        $search_obj->addDistinct('store_id','1','1','false');
        $search_obj->setPair('duniqfield:store_id');
        //$search_obj->addAggregate('store_id','count(store_id)',0,0,'count(store_id)');
        switch ($order) {
          case '1':
            //按创建时间倒序获取搜索结果
            $search_obj->addSort('distance(lng,lat,"'.$lng.'","'.$lat.'")','+');
            break;
          case '2':
            $search_obj->addSort('store_time','-');
            $search_obj->addSort('distance(lng,lat,"'.$lng.'","'.$lat.'")','+');
            break;
          case '3':
            $search_obj->addSort('distance(lng,lat,"'.$lng.'","'.$lat.'")','+');
            break;
          case '4':
            $search_obj->addSort('per_consumption','-');
            $search_obj->addSort('distance(lng,lat,"'.$lng.'","'.$lat.'")','+');
            break;
          case '5':
            $search_obj->addSort('per_consumption','+');
            $search_obj->addSort('distance(lng,lat,"'.$lng.'","'.$lat.'")','+');
            break;
          default:
            $search_obj->addSort('distance(lng,lat,"'.$lng.'","'.$lat.'")','+');
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
        //$rt['result']['items'] = $result['result']['items'];
        foreach ($result['result']['items'] as $key => $value) {
            $rt['result']['items'][$key]['fields']['city_name'] =  $value['fields']['city_name'];
            $rt['result']['items'][$key]['fields']['goods_class'] =  $this->class_array[$value['fields']['gc_parent_id']];
            $rt['result']['items'][$key]['fields']['store_id'] = $value['fields']['store_id'];
            $rt['result']['items'][$key]['fields']['bid'] = $value['fields']['bid'];
            $rt['result']['items'][$key]['fields']['store_name'] = $value['fields']['store_name'];
            $rt['result']['items'][$key]['fields']['store_avatar'] = $value['fields']['store_avatar'];
            $rt['result']['items'][$key]['fields']['district_name'] = $value['fields']['district_name'];
            $rt['result']['items'][$key]['fields']['per_consumption'] = $value['fields']['per_consumption'];
            $rt['result']['items'][$key]['fields']['variableValue'] = $value['variableValue']['extinfo'][0];
        }
        if(count($rt['result']['items']) === 0)
        {
            $rt['status'] = 'ERROR';
            $rt['msg'] = '暂无数据';
        }
        echo json_encode($rt);
	}

   
}