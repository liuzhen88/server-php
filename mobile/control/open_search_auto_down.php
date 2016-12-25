<?php
/**
 * OpenSearch下拉自动联想
 * @authors solon.ring2011@gmail.com
 * @date    2015-10-19 16:32:14
 * @version 1.0.0
 */

class open_search_auto_downControl extends mobileHomeControl {
    protected $access_key = "Jy75YdnUYRUE3enc";
    protected $secret = "o1lmG6Dgi7oYzyNLSUqWtzYfeWdqZb";
    protected $app_name = "agg";
    protected $suggest_name = "agg";
    protected $host = "http://opensearch-cn-hangzhou.aliyuncs.com";
    protected $hits = 10;
    function __construct(){
        require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchClient.php');
        require_once(BASE_DATA_PATH.DS.'api/opensearch/CloudsearchSuggest.php');
        parent::__construct();
    }

   public function indexOp(){
   		$city_name = isset($_REQUEST['city_name']) ? trim($_REQUEST['city_name']) : "合肥市";
		$query = isset($_REQUEST['query']) ? trim($_REQUEST['query']) : "";
		$callback = isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : "";


		$client = new CloudsearchClient($this->access_key, $this->secret, array("host" => $this->host), "aliyun");
		$suggest = new CloudsearchSuggest($client);

		$suggest->setIndexName($this->app_name);
		$suggest->setSuggestName($this->suggest_name);
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
		    echo json_encode($item);
		} else {
		    echo htmlspecialchars($callback) . "(".json_encode($item).");";
		}
   }
}