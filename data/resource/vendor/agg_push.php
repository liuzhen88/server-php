<?php
/**
 * explain:
 * setPlatform(M\Platform('android', 'ios'))
 * setPlatform(M\all)
 */

require_once BASE_ROOT_PATH.DS.DIR_RESOURCE.DS.'vendor/autoload.php';

use JPush\Model as M;
use JPush\JPushClient;
use JPush\JPushLog;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;

/**
 * @param String $message 推送信息
 * @param Array $registration_id 推送设备,空表示所有设备
 * @param Array $extend 其他参数 client_type array("android", "ios", "winphone") 客户端类型，
 *                      extras 传递给客户端的参数 'extras'=>array('hello'=>'word',9);
 *                      audience_tag=array('v2.1.0', 'v2.1.1');//标签，或者的关系
 *                      app_type 不传默认为爱个购；PTB_STORE：跑腿邦商户端
 * @return 推送成功，true。推送失败false(1.消息超过限制，2.极光返回失败)
 */
function push($message,$registration_ids=array(),$extend=array()){
    if(strlen($message)>=1000) return false;//消息长度不能超过1000字节
    $error_log_path = C('error_log_path');
    JPushLog::setLogHandlers(array(new StreamHandler($error_log_path.'/jpush.log', Logger::ERROR)));
    $app_key=C('JPush.appKey');
    $master_secret = C('JPush.masterSecret');
    if (isset($extend['app_type']) && $extend['app_type'] == 'PTB_STORE')
    {
        $app_key=C('JPush.ptbStoreAppKey');
        $master_secret = C('JPush.ptbStoreMasterSecret');
    }
    $is_production=C('JPush.isPruduction');
    $client = new JPushClient($app_key, $master_secret);
    //消息声音
    $message_type = isset($extend['extras']['data']['message_type']) ? $extend['extras']['data']['message_type'] : '';
    $sound = '';
    switch ($message_type){
        case 'STORE_NEW_ORDER':
            $sound = 'new_order_coming.m4a';
            break;
        default:
            $sound = '';
            break;
    }
    try {
        $push=$client->push();
        //发送给指定用户或全部
        if(empty($registration_ids)){
            $push->setAudience(M\all);
        }else{
            if (isset($extend['audience_tag']) && !empty($extend['audience_tag']))
            {
                $push->setAudience(M\registration_id($registration_ids), M\tag($extend['audience_tag']));
            }
            else{
                $push->setAudience(M\registration_id($registration_ids));
            }
        }
        //指定设备类型
        if(isset($extend['client_type'])&&is_array($extend['client_type'])){
            $push->setPlatform($extend['client_type']);
        }else{
            $push->setPlatform(M\all);
        }
        $push->setOptions(M\options(null, null, null, $is_production, null));

        //扩展信息，只能安卓ios分开推
        if(isset($extend['extras'])){
            //推给指定客户端
            if(isset($extend['client_type'])&&is_array($extend['client_type'])){
                foreach($extend['client_type'] as $value){
                    $notification=M\notification(array('platform'=>strtolower($value),'alert'=>$message,'extras'=>$extend['extras']));
                }
            }else{
                //推给所有客户端
                $ios = array('platform'=>'ios','alert'=>$message,  'extras'=>$extend['extras']);
                if ($sound != '')
                    $ios['sound'] = $sound;
                $notification=M\notification($ios,array('platform'=>'android','alert'=>$message,'extras'=>$extend['extras']));
            }
        }else{
            $notification=M\notification($message);
        }
        $json=$push->setNotification($notification)->getJSON();
//        file_put_contents('log.xiyu',json_encode($json),FILE_APPEND);
        $result=$push->setNotification($notification)
            ->send();
        return true;

    } catch (APIRequestException $e) {
        //@todo error log
        return false;

    } catch (APIConnectionException $e) {
        //@todo error log
        return false;
    }
}