<?php
/**
 * OpenSearch下拉自动联想
 * @authors solon.ring2011@gmail.com
 * @date    2015-10-19 16:32:14
 * @version 1.0.0
 */
require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchClient.php');
require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchSuggest.php');

class open_search_auto_down_localModel {
    protected $access_key;
    protected $secret;
    protected $host;
    protected $hits;
    function __construct(){
        $this->access_key	= C('open_search.access_key');
        $this->secret		= C('open_search.secret');
        $this->host			= C('open_search.host');
        $this->hits			= 10;
    }
    /**
     * 
     * @param  [type] $name 默认app_name与suggest_name相同
     * @return [type]       [description]
     */
    public function index($name='agg'){
   		//$city_name = isset($_REQUEST['city_name']) ? trim($_REQUEST['city_name']) : "合肥市";
		$query = isset($_REQUEST['query']) ? trim($_REQUEST['query']) : "";
		$callback = isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : "";


		$client = new CloudsearchClient($this->access_key, $this->secret, array("host" => $this->host), "aliyun");
		$suggest = new CloudsearchSuggest($client);
		$app_name		= $name;
        $suggest_name	= $name;
		$suggest->setIndexName($app_name);
		$suggest->setSuggestName($suggest_name);
		$suggest->setHits($this->hits);
		$suggest->setQuery($query);
		$items = array();
		try {
		    $result = json_decode($suggest->search(), true);
		  if (!isset($result['errors'])) {
		    if (isset($result['suggestions']) && !empty($result['suggestions'])) {
		      $items = $result['suggestions'];
		    } else {
		      $items = array();
		    }
		  } else {
		      foreach ($result['errors'] as $error) {
		      throw new Exception($error['message'] . " request_id: " . $result['request_id'],$error['code']);
		    }
		  }
		} catch (Exception $e) {
		    // Logging the error code and error message.
		}
		//exit('{"result":["abc200121","abcr","abqp","abc"],"status":"OK","request_id":"1444632092048420500631206","AliyunPermission":"AVAILABLE"}');
		if(count($items)>0)
		{
		    foreach ($items as $key => $value) {
		    $item[$key]['label'] = $value['suggestion'];
		    }
		}
		else
		{
		    $item = array();
		}
		if (empty($callback)) {
		    return $item;
		} else {
		    return htmlspecialchars($callback) . "(".$item.");";
		}
   }
}