<?php
/**
 * 商品图片统一调用函数
 *
 */

defined('emall') or exit('Access Invalid!');

/**
 * 取得商品缩略图的完整URL路径，接收商品信息数组，返回所需的商品缩略图的完整URL
 * @param array $goods 商品信息数组
 * @param string $type 缩略图类型  值为60,240,360,1280
 * @return string
 */
function thumb($goods = array(), $type = '',$watermark=false){
    $type_array = explode(',_', ltrim(GOODS_IMAGES_EXT, '_'));
    if (!in_array($type, $type_array)) {
        $type = '240';
    }
    if (empty($goods)){
        return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
    }
    if (array_key_exists('apic_cover', $goods)) {
        $goods['goods_image'] = $goods['apic_cover'];
    }
    if (empty($goods['goods_image'])) {
        return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
    }
    $search_array = explode(',', GOODS_IMAGES_EXT);
    $file = str_ireplace($search_array,'',$goods['goods_image']);
    $fname = basename($file);
    //取店铺ID
    if (preg_match('/^(\d+_)/',$fname)){
        $store_id = substr($fname,0,strpos($fname,'_'));
    }else{
        $store_id = $goods['store_id'];
    }
//    $file = $type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file);
    $qnycthumbType = qnycthumbType($file, $type,$watermark);
    $file = ($type == '') ? $file : $qnycthumbType;
    if (substr($file, 0 ,11) == 'aigegouold_')
    {
        return UPLOAD_SITE_URL.DS.str_replace('aigegouold_', '', $file);
    }
    $thumb_host = UPLOAD_SITE_URL.'/'.ATTACH_GOODS;
    return $thumb_host.'/'.$store_id.'/'.$file;

}
/**
 * 取得商品缩略图的完整URL路径，接收图片名称与店铺ID
 * @param string $file 图片名称
 * @param string $type 缩略图尺寸类型，值为60,240,360,1280
 * @param mixed $store_id 店铺ID 如果传入，则返回图片完整URL,如果为假，返回系统默认图
 * @return string
 */
function cthumb($file, $type = '', $store_id = false,$watermark=false) {
    $type_array = explode(',_', ltrim(GOODS_IMAGES_EXT, '_'));
//    if (!in_array($type, $type_array)) {
//        $type = '240';
//    }
    if (empty($file)) {
        return UPLOAD_SITE_URL . '/' . defaultGoodsImage ( $type );
    }
   // $search_array = explode(',', GOODS_IMAGES_EXT);
   // $file = str_ireplace($search_array,'',$file);
    $fname = basename($file);
    // 取店铺ID
    if ($store_id === false || !is_numeric($store_id)) {
        $store_id = substr ( $fname, 0, strpos ( $fname, '_' ) );
    }
    $thumb_host = UPLOAD_SITE_URL . '/' . ATTACH_GOODS;
    // 本地存储时，增加判断文件是否存在，用默认图代替
	if (substr($file, 0 ,11) == 'aigegouold_')
    {
        $old_file = str_replace('aigegouold_', '', $file);
        return UPLOAD_SITE_URL.DS.qnycthumbType($old_file, $type, $watermark);
    }
    
   // return $thumb_host . '/' . $store_id . '/' . ($type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file));
    $qnycthumbType = qnycthumbType($file, $type,$watermark);
    $fileName = ($type == '') ? $file : $qnycthumbType;
    return $thumb_host . '/' . $store_id . '/' . $fileName ;
}
/**
 * 七牛云缩略图样式
 * @param unknown $file
 * @param unknown $type
 * @param url $watermark 水印地址，空不设水印
 *
 */
function qnycthumbType($file,$type,$watermark=false)
{
    //$type=intval($type);
    $type_arr = explode(',', $type);
    $width = isset($type_arr[0]) ? intval($type_arr[0]) : 0;
    $heigth = isset($type_arr[1]) ? intval($type_arr[1]) : $width;
    //加水印
    if($watermark) {
        $water_tail = qny_watermark_tail();
        if($width==0) return $file.'?'.$water_tail;
        return $file."?imageView2/1/w/{$width}/h/{$heigth}|{$water_tail}";
    }
    if($width==0) return $file;
    return $file."?imageView2/1/w/{$width}/h/{$heigth}";
}

/**
 * 清晰的七牛云缩略图
 * @param pic_url
 * @param size  100 or 100,200
 */
function qnyResizeHD($pic_url,$size){
    $type_arr = explode(',', $size);
    $width = isset($type_arr[0]) ? intval($type_arr[0]) : 0;
    $heigth = isset($type_arr[1]) ? intval($type_arr[1]) : $width;
    if($width==0) return $pic_url;
    return $pic_url."?imageView2/1/w/{$width}/h/{$heigth}/format/png";

}

/**
 * 水印后缀
 */
function qny_watermark_tail(){
    return 'watermark/1/image/'.urlsafe_base64_encode(C('qny.watermark_url')).'/dissolve/100/gravity/Center';
}

/**
 * 七牛云，水印图片地址，base64转码
 * @param $str
 */
function urlsafe_base64_encode($str){
    $str=base64_encode($str);
    $str=str_replace('+','-',$str);
    $str=str_replace('/','_',$str);
    return $str;
}

/**
 * 七牛云移动图片
 * @param $from 被移动图片
 * @param $goto 移动终点
 */
function qnymove($from,$goto){
    //将文件从文件$key2 移动到文件$key3。 可以在不同bucket移动
    require_once BASE_DATA_PATH.'/api/qiniuyun/src/Qiniu/functions.php';
    $accessKey = C('qny.accessKey');
    $secretKey = C('qny.secretKey');
    $bucket = C('qny.bucket');
    //初始化Auth状态：
    $auth = new Qiniu\Auth($accessKey,$secretKey);
    //初始化BucketManager
    $bucketMgr = new Qiniu\Storage\BucketManager($auth);
    $err = $bucketMgr->move($bucket, $from, $bucket, $goto);
    if ($err !== null) {
        return false;
    } else {
        return true;
    }
}

/**
 * 检查远程文件是否存在
 * @param unknown $url
 * @return boolean
 */
function check_remote_file_exists($url) {
    $curl = curl_init($url); // 不取回数据
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET'); // 发送请求
    $result = curl_exec($curl);
    $found = false; // 如果请求没有发送失败
    if ($result !== false) {

        /** 再检查http响应码是否为200 */
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($statusCode == 200) {
            $found = true;
        }
    }
    curl_close($curl);

    return $found;
}

/**
 * 商品二维码
 * @param array $goods_info
 * @return string
 */
function goodsQRCode($goods_info) {
    if (empty($goods_info))
    {
        return UPLOAD_SITE_URL.DS.ATTACH_STORE.DS.'default_qrcode.png';
    }
    return UPLOAD_SITE_URL.DS.ATTACH_STORE.DS.$goods_info['store_id'].DS.$goods_info['goods_id'].'.png';
}
/**
 * 取得抢购缩略图的完整URL路径
 * @param string $imgurl 商品名称
 * @param string $type 缩略图类型  值为small,mid,max
 * @return string
 */
function gthumb($image_name = '', $type = ''){
	if (!in_array($type, array('small','mid','max'))) $type = 'small';
	if (empty($image_name)){
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
	}
    list($base_name, $ext) = explode('.', $image_name);
    list($store_id) = explode('_', $base_name);
//    $file_path = ATTACH_GROUPBUY.DS.$store_id.DS.$base_name.'_'.$type.'.'.$ext;
    $file_path = ATTACH_GROUPBUY.DS.$store_id.DS.qnycthumbType($base_name.'.'.$ext, $type);
    /* if(!check_remote_file_exists(UPLOAD_SITE_URL.DS.$file_path)) {
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
	} */
	return UPLOAD_SITE_URL.DS.$file_path;
}

/**
 * 取得买家缩略图的完整URL路径
 * @param string $imgurl 商品名称
 * @param string $type 缩略图类型  值为240,1024
 * @return string
 */
function snsThumb($image_name = '', $type = ''){
	//if (!in_array($type, array('240','1024'))) $type = '240';
	if (empty($image_name)){
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
    }
    $member_id = 0;
    //list($member_ids) = explode('_', $image_name);
    $fname = basename($image_name);
    if (preg_match('/^(\d+_)/',$fname)){
        $member_id = substr($fname,0,strpos($fname,'_'));
    }
    if ($member_id == 0)
    {
        return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
    }
//    $file_path = ATTACH_MALBUM.DS.$member_id.DS.str_ireplace('.', '_'.$type.'.', $image_name);
    $qnycthumbType = qnycthumbType($image_name, $type,$watermark);
    $image_name = ($type == '') ? $image_name : $qnycthumbType;
    $file_path = ATTACH_MALBUM.DS.$member_id.DS.qnycthumbType($image_name, $type);
   
	return UPLOAD_SITE_URL.DS.$file_path;
}

/**
 * 取得积分商品缩略图的完整URL路径
 * @param string $imgurl 商品名称
 * @param string $type 缩略图类型  值为small
 * @return string
 */
function pointprodThumb($image_name = '', $type = ''){
	if (!in_array($type, array('small','mid'))) $type = '';
	if (empty($image_name)){
		return UPLOAD_SITE_URL.'/'.defaultGoodsImage('240');
    }

    if($type) {
//        $file_path = ATTACH_POINTPROD.DS.str_ireplace('.', '_'.$type.'.', $image_name);
        $file_path = ATTACH_POINTPROD.DS.qnycthumbType($image_name, $type);
    } else {
        $file_path = ATTACH_POINTPROD.DS.$image_name;
    }
	return UPLOAD_SITE_URL.DS.$file_path;
}

/**
 * 取得品牌图片
 * @param string $image_name
 * @return string
 */
function brandImage($image_name = '') {
    if ($image_name != '') {
        return UPLOAD_SITE_URL.'/'.ATTACH_BRAND.'/'.$image_name;
    }
    return UPLOAD_SITE_URL.'/'.ATTACH_COMMON.'/default_brand_image.gif';
}

/**
* 取得订单状态文字输出形式
*
* @param array $order_info 订单数组
* @return string $order_state 描述输出
*/
function orderState($order_info) {
    switch ($order_info['order_state']) {
        case ORDER_STATE_CANCEL:
            $order_state = L('order_state_cancel');
            break;
        case ORDER_STATE_NEW:
            $order_state = L('order_state_new');
            break;
        case ORDER_STATE_PAY:
            $order_state = L('order_state_pay');
            break;
        case ORDER_STATE_SEND:
            $order_state = L('order_state_send');
            break;
        case ORDER_STATE_SENDING:
            $order_state = L('order_state_dispatching');
            break;
        case ORDER_STATE_SUCCESS:
            $order_state = L('order_state_success');
            break;
    }
    return $order_state;
}

/**
 * 取得订单支付类型文字输出形式
 * @param array $payment_code
 * @return string
 */
function orderPaymentName($payment_code) {
    return str_replace(
            array('offline','online','alipay','tenpay','chinabank','predeposit'),
            array('货到付款','在线付款','支付宝','财付通','网银在线','站内余额支付'),
            $payment_code);
}

/**
 * 取得订单商品销售类型文字输出形式
 * @param array $goods_type
 * @return string 描述输出
 */
function orderGoodsType($goods_type) {
    return str_replace(
            array('1','2','3','4','5'),
            array('','抢购','限时折扣','优惠套装','赠品'),
            $goods_type);
}

/**
 * 取得结算文字输出形式
 * @param array $bill_state
 * @return string 描述输出
 */
function billState($bill_state) {
    return str_replace(
            array('1','2','3','4'),
            array('已出账','商家已确认','平台已审核','结算完成'),
            $bill_state);
}
/**
 * 其它缩略图url路径
 * @param unknown $file_name
 * @param string $path
 * @param string $type
 * @param string $watermark
 * @return string
 */
function otherThumb( $file_name,$path='', $type='', $watermark = false)
{
    if (empty($file_name)) return '';
    $path = empty($path) ? UPLOAD_SITE_URL : UPLOAD_SITE_URL . DS . $path;
    if ($type != '')
        $file_name = qnycthumbType($file_name, $type,$watermark);
    return  $path . DS . $file_name;
}

/**
 * chenyifei
 * 反序列话操作，避免编码不同出错
 * @param unknown $serial_str
 * @return mixed
 */
function mb_unserialize($serial_str) {
    $serial_str = preg_replace_callback('!s:(\d+):"(.*?)";!s', create_function('$math',"return 's:'.strlen(\$math[2]).':\"'.\$math[2].'\";';"), $serial_str);
    return unserialize($serial_str);
}
/**
 * 商品价格去零处理
 * @param unknown $s
 * @return mixed|unknown
 */
function del0($s)
{
    $s = trim(strval($s));
    if (preg_match('#^-?\d+?\.0+$#', $s)) {
        return preg_replace('#^(-?\d+?)\.0+$#','$1',$s);
    }
    if (preg_match('#^-?\d+?\.[0-9]+?0+$#', $s)) {
        return preg_replace('#^(-?\d+\.[0-9]+?)0+$#','$1',$s);
    }
    return $s;
}

/**chenyifei
 * 七牛云图片读取
 * @param unknown $key 存储文件名称
 * @param $params=array(
 * size : 缩略图尺寸 60_60,240_240,360_360,1280_1280
 * )  
 */
/* function viewFileQny($key,$params=array())
{
    //$key = 'chang.jpg';
    require_once BASE_DATA_PATH.'/api/qiniuyun/src/Qiniu/functions.php';
    $accessKey = C('qny.accessKey');
    $secretKey = C('qny.secretKey');
    $auth = new Qiniu\Auth($accessKey, $secretKey);
    $qnypath = C('upload_qiniuyun_url');
    $signedUrl = $auth->privateDownloadUrl($qnypath.DS.$key);
    return $signedUrl;
} */
?>
