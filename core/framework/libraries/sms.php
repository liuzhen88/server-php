<?php
/**
 * 手机短信类
 *
 * @package  library  
 */
defined('emall') or exit('Access Invalid!');

class Sms {
    /**
     * 发送手机短信 upass
     * @param unknown $mobile 手机号
     * @param unknown $content 短信内容
     */

    public function send($mobile,$templateId, $params) {
        return $this->_ucpass($mobile,$templateId, $params);
    }
    /**
     *  发送短信 云片
     */
    public function  yp_send($mobile,$verify_code) {
        $text="【爱个购】您的验证码为".$verify_code."，请于30分钟内完成验证，如非本人操作，请忽略本短信。";
        return $this->send_sms("83eb6015470d7eca8ebeca4e4df8bc59",$text,$mobile);
    }
    /**
     *  发送短信 亿美
     */
    /*public function send($mobile,$content) {
        return $this->_sendEmay($mobile,$content);
    } */
    /**
     * 发送通知类短信
     */
    public function  notice_send($number,$message) {
        return $this->send_sms("83eb6015470d7eca8ebeca4e4df8bc59",$message,$number);
    }
    private function _ucpass($mobile,$templateId,$params)
    {
        set_time_limit(0);
        define('SCRIPT_ROOT',  BASE_DATA_PATH.'/api/ucpass/');
        require_once SCRIPT_ROOT.'Ucpaas.class.php';
        $options['accountsid']= C('sms.accountsid');
        $options['token']= C('sms.token');
        $ucpass = new Ucpaas($options);
        $appId = C('sms.appId');   
        $result = $ucpass->templateSMS($appId,$mobile,$templateId,$params);
        $return_code = false;
        $result = json_decode($result,true);
        if (isset($result['resp']['respCode']) && $result['resp']['respCode'] == '000000')
            $return_code = true;
        return $return_code;
    }

    /**
     * 智能匹配模版接口发短信
     * apikey 为云片分配的apikey
     * text 为短信内容
     * mobile 为接受短信的手机号
     */
    function send_sms($apikey, $text, $mobile){
        $url="http://yunpian.com/v1/sms/send.json";
        $encoded_text = urlencode("$text");
        $mobile = urlencode("$mobile");
        $post_string="apikey=$apikey&text=$encoded_text&mobile=$mobile";
        return $this->sock_post($url, $post_string);
    }

    /**
     * url 为服务的url地址
     * query 为请求串
     */
    function sock_post($url,$query){
        $data = "";
        $info=parse_url($url);
        $fp=fsockopen($info["host"],80,$errno,$errstr,30);
        if(!$fp){
            return $data;
        }
        $head="POST ".$info['path']." HTTP/1.0\r\n";
        $head.="Host: ".$info['host']."\r\n";
        $head.="Referer: http://".$info['host'].$info['path']."\r\n";
        $head.="Content-type: application/x-www-form-urlencoded\r\n";
        $head.="Content-Length: ".strlen(trim($query))."\r\n";
        $head.="\r\n";
        $head.=trim($query);
        $write=fputs($fp,$head);
        $header = "";
        while ($str = trim(fgets($fp,4096))) {
            $header.=$str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp,4096);
        }
        return $data;
    }
    /**
     * 亿美短信发送接口
     * @param unknown $mobile 手机号
     * @param unknown $content 短信内容
     */
    private function _sendEmay($mobile,$content) {
        set_time_limit(0);
        define('SCRIPT_ROOT',  BASE_DATA_PATH.'/api/emay/');
        require_once SCRIPT_ROOT.'include/Client.php';
        /**
         * 网关地址
         */
        $gwUrl = C('sms.gwUrl');
        /**
         * 序列号,请通过亿美销售人员获取
         */
        $serialNumber = C('sms.serialNumber');
        /**
         * 密码,请通过亿美销售人员获取
         */
        $password = C('sms.password');
        /**
         * 登录后所持有的SESSION KEY，即可通过login方法时创建
         */
        $sessionKey = C('sms.sessionKey');
        /**
         * 连接超时时间，单位为秒
         */
        $connectTimeOut = 2;
        /**
         * 远程信息读取超时时间，单位为秒
         */
        $readTimeOut = 10;
        /**
         $proxyhost		可选，代理服务器地址，默认为 false ,则不使用代理服务器
         $proxyport		可选，代理服务器端口，默认为 false
         $proxyusername	可选，代理服务器用户名，默认为 false
         $proxypassword	可选，代理服务器密码，默认为 false
         */
        $proxyhost = false;
        $proxyport = false;
        $proxyusername = false;
        $proxypassword = false;

        $client = new Client($gwUrl,$serialNumber,$password,$sessionKey,$proxyhost,$proxyport,$proxyusername,$proxypassword,$connectTimeOut,$readTimeOut);
        /**
         * 发送向服务端的编码，如果本页面的编码为GBK，请使用GBK
        */
        $client->setOutgoingEncoding("UTF-8");
        $statusCode = $client->login();
        if ($statusCode!=null && $statusCode=="0") {
        } else {
            //登录失败处理
        //    echo "登录失败,返回:".$statusCode;exit;
        }
        $statusCode = $client->sendSMS(array($mobile),$content);
        if ($statusCode!=null && $statusCode=="0") {
            return true;
        } else {
            return false;
             print_R($statusCode);
             echo "处理状态码:".$statusCode;
        }
    }
}
