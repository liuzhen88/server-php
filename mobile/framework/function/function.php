<?php
/**
 * mobile公共方法
 * 公共方法
 * @package    function
/
 */
defined('emall') or exit('Access Invalid!');

function mobile_page($page_count) {
    //输出是否有下一页
    $extend_data = array();
    $current_page = intval($_REQUEST['curpage']);
    if($current_page <= 0) {
        $current_page = 1;
    }
    if($current_page >= $page_count) {
        $extend_data['hasmore'] = false;
    } else {
        $extend_data['hasmore'] = true;
    }
    $extend_data['page_total'] = $page_count;
    return $extend_data;
}

/**
 * 检查必须参数是否有
 */
function check_request_parameter($check_param){
    foreach($check_param as $value) {
        if (!isset($_REQUEST[$value])) output_error('缺少参数'.$value, array(), ERROR_CODE_ARG);
    }
}


/**
 * 设置文件缓存
 *
 * @author lijunhua
 * @since  2015-08-05
 * @param string $key
 * @param mixed $value
 * @param string $path
 * @param int $expire_time
 * @return mixed
 */
function set_file_cache($key='', $value=array(), $path='cache/', $expire_time=1800,$code_count=0) {
    if (empty($key)) return false;
    $result['data'] = $value;
    $result['expire'] = array(
        'create_time' => time(),
        'long_time'   => $expire_time,
        'count' =>$code_count
    );
    return F($key, $result, $path);    //将$data数组生成到setting文件缓存
}

/**
 * 获取文件缓存
 * @author lijunhua
 * @since  2015-08-05
 * @param int $key
 * @param string $path
 * @return mixed
 */
function get_file_cache($key, $path='cache/') {
    //禁止出现"."
    if (strstr('.', $key)) {
        return false;
    }

    $cache = F($key, null, $path);
    if (!$cache) {
        return false;
    }
    // 过期文件
    if ($cache['expire']['create_time']+$cache['expire']['long_time'] < time()) {
        @unlink(BASE_ROOT_PATH . '/data/'. $path . $key . '.php');
        return false;
    }
    return $cache['data'];
}
/**
 * 获取上次短信类型
 * @author daliang
 * @since  2015-12-18
 * @param int $key
 * @param string $path
 * @return mixed
 */
function get_file_last_cache($key, $path='cache/') {
    //禁止出现"."
    if (strstr('.', $key)) {
        return false;
    }

    $cache = F($key, null, $path);
    if (!$cache) {
        return false;
    }
    if (empty($cache['expire']['count'])) {
        @unlink(BASE_ROOT_PATH . '/data/'. $path . $key . '.php');
        return false;
    }
    return $cache['expire']['count'];
}

/**
 * 删除文件缓存
 */
function del_file_cache($key, $path='cache/')  {
    if (file_exists(BASE_ROOT_PATH . '/data/'. $path . $key . '.php')) {
        @unlink(BASE_ROOT_PATH . '/data/'. $path . $key . '.php');
    }
    return true;
}

/***
 *  测距
 * 
 * @author lijunhua
 * @since   2015-08-11
 * @param  string $location1  地点1 lng,lat
 * @param  string $location2  地点2 lng,lat
 * @pararm string $R          地球赤道半径
 * 
 * @return int 米
 */ 
function get_distance($location1 = '', $location2 = '', $R=6378138) 
{  
    $location_arr1 = explode(',', $location1);
    $location_arr2 = explode(',', $location2);


    $lng1 = ($location_arr1[0] * pi()) / 180;  
    $lat1 = ($location_arr1[1] * pi()) / 180; 
    
    $lng2 = ($location_arr2[0] * pi()) / 180;  
    $lat2 = ($location_arr2[1] * pi()) / 180;  
   
     
    $calcLongitude = $lng2 - $lng1;  
    $calcLatitude  = $lat2 - $lat1;  
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);    
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));  
    $calculatedDistance = round($R * $stepTwo);  
   
    return round($calculatedDistance);  
 }  

/**
 * 中文长度算1个字符长度
 * 
 * @author lijunhua
 * @singce 2015-08-19
 * @param string $str
 * @return int
 */
function cn_strlen($str) {
    return mb_strlen(preg_replace('|[^\x{4e00}-\x{9fa5}]|u', '*', $str), "UTF-8");
}


/**
 * 验证身份证号
 * 
 * @param string $vStr
 * @return boolean
 */
function is_identity($vStr) {
    $vCity = array(
        '11', '12', '13', '14', '15', '21', '22',
        '23', '31', '32', '33', '34', '35', '36',
        '37', '41', '42', '43', '44', '45', '46',
        '50', '51', '52', '53', '54', '61', '62',
        '63', '64', '65', '71', '81', '82', '91'
    );

    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) {
        return false;
    }   

    if (!in_array(substr($vStr, 0, 2), $vCity)) {
        return false;
    }
    
    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
    $vLength = strlen($vStr);

    if ($vLength == 18) {
        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
    }

    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) {
        return false;
    }
    
    if ($vLength == 18) {
        $vSum = 0;
        
        for ($i = 17; $i >= 0; $i--) {
            $vSubStr = substr($vStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr, 11));
        }
        
        if ($vSum % 11 != 1) {
            return false;
        }   
    }

    return true;
}

/**
 * 获取分页
 * @return string
 */
function getLimit(){
    if(!isset($_REQUEST['curpage']) || ($page = intval($_REQUEST['curpage']))<1){
        $page = 1;
    }
    if(!isset($_REQUEST['page']) || ($num = intval($_REQUEST['page']))<1){
        $num = 10;
    }
    $limit = ($page-1)*$num.",".$num;
    return $limit;
}
/**
 * 价格格式化
 * 
 * @author lijunhua
 * @since 2015-10-22
 * @param float $price
 */
function price_format($price) {
    return number_format($price, 2, '.', '');
}

