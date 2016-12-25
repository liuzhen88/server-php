<?php
/**
 * 退款
 * 微信退款，确保libcurl版本支持双向认证，版本得高于7.20.1
*/
defined('emall') or exit('Access Invalid!');
class refundLogic {

    /** 网关url地址 */
    var $gateUrl;

    /** 密钥 */
    var $key;

    /** 请求的参数 */
    var $parameters;

    function __construct() {
        $this->RequestHandler();
    }

    function RequestHandler() {
        $this->gateUrl = "";
        $this->key = "";
        $this->parameters = array();
    }

    // test function
    function orderquery(){
        $this->gateUrl="https://api.mch.weixin.qq.com/pay/downloadbill";

        //设置key
        $condition = array();
        $model_mb_payment = Model('mb_payment');
        $condition['payment_code'] = 'wxpay';
        $mb_payment_list = $model_mb_payment->getMbPaymentInfo($condition);
        $this->setKey($mb_payment_list['payment_config']['wxpay_key']);

        //查询单笔订单
        $this->setParameter("appid", $mb_payment_list['payment_config']['wxpay_appid']);
        $this->setParameter("mch_id", $mb_payment_list['payment_config']['wxpay_mch_id']);
        $this->setParameter("nonce_str", rand(1000000000,9999999999));
        $this->setParameter("bill_date", '20151231');
        $this->setParameter("bill_type", 'ALL');
        $data = $this->curl_post_ssl($this->gateUrl);
        print_r($data);
    }

    /**
     * 退款接口
     */
    function order_refund($out_trade_no,$total_fee){
        $this->gateUrl="https://api.mch.weixin.qq.com/secapi/pay/refund";

        //设置key
        $condition = array();
        $model_mb_payment = Model('mb_payment');
        $condition['payment_code'] = 'wxpay';
        $mb_payment_list = $model_mb_payment->getMbPaymentInfo($condition);
        $this->setKey($mb_payment_list['payment_config']['wxpay_key']);

        //退款参数
        $this->setParameter("appid", $mb_payment_list['payment_config']['wxpay_appid']);
        $this->setParameter("mch_id", $mb_payment_list['payment_config']['wxpay_mch_id']);
        $this->setParameter("nonce_str", rand(1000000000,9999999999));
        $this->setParameter("out_trade_no", $out_trade_no);
        $this->setParameter("out_refund_no", $out_trade_no);
        $this->setParameter("total_fee", $total_fee);
        $this->setParameter("refund_fee", $total_fee);
        $this->setParameter("op_user_id", $mb_payment_list['payment_config']['wxpay_mch_id']);
        $data = $this->curl_post_ssl($this->gateUrl,true);
        //调试用
        //print_r($data);
        $data = $this->xmlToArray($data);
        $result=array();
        $result['code']=false;
        $result['msg']='退款失败';
        if($data){
            if($data['return_code']=='SUCCESS'){
                if($data['result_code']=='SUCCESS'){
                    $result['code']=true;
                    $result['msg']='退款成功';
                }elseif($data['err_code']=='REFUND_FEE_MISMATCH'){
                    $result['msg']='退款金额要与实际一致';
                }
            }
        }
        return $result;
    }

    /**
     * 退款查询
     */
    function order_refund_query($out_trade_no){
        $this->gateUrl="https://api.mch.weixin.qq.com/pay/refundquery";

        //设置key
        $condition = array();
        $model_mb_payment = Model('mb_payment');
        $condition['payment_code'] = 'wxpay';
        $mb_payment_list = $model_mb_payment->getMbPaymentInfo($condition);
        $this->setKey($mb_payment_list['payment_config']['wxpay_key']);

        //退款参数
        $this->setParameter("appid", $mb_payment_list['payment_config']['wxpay_appid']);
        $this->setParameter("mch_id", $mb_payment_list['payment_config']['wxpay_mch_id']);
        $this->setParameter("nonce_str", rand(1000000000,9999999999));
        $this->setParameter("out_trade_no", $out_trade_no);
        $data = $this->curl_post_ssl($this->gateUrl);
        $data = $this->xmlToArray($data);
        //调试用
        //var_dump($data);
        $result=array();
        $result['code']=false;
        $result['msg']='无退款状态';
        if($data){
            if($data['return_code']=='SUCCESS'){
                if($data['result_code']=='SUCCESS'){
                    $result['code']=true;
                    $result['msg']='退款成功';
                }else{
                    $result['msg']=$data['err_code'];
                }
            }
        }
        return $result;
    }

    /**
     *设置参数值
     */
    Private function setParameter($parameter, $parameterValue) {
        $this->parameters[$parameter] = $parameterValue;
    }

    /**
     *获取所有请求的参数
     *@return array
     */
    Private function getAllParameters() {
        return $this->parameters;
    }

    /**
     *获取密钥
     */
    Private function getKey() {
        return $this->key;
    }

    /**
     *设置密钥
     */
    Private function setKey($key) {
        $this->key = $key;
    }

    Private function curl_post_ssl($url,$iscert=false, $second=30,$aHeader=array()){
        $this->createSign();
        $xml_vars = $this->arrtoxml($this->getAllParameters());
        $ch = curl_init();
        //超时时间
        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //设置代理
        //curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        if($iscert){
            //以下两种方式需选择一种

            //第一种方法，cert 与 key 分别属于两个.pem文件
            //默认格式为PEM，可以注释
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT,$_SERVER['DOCUMENT_ROOT'].'/mobile/api/payment/wxpay/cert/apiclient_cert.pem');
            //默认格式为PEM，可以注释
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY,$_SERVER['DOCUMENT_ROOT'].'/mobile/api/payment/wxpay/cert/apiclient_key.pem');

            //第二种方式，两个文件合成一个.pem文件
            //curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
        }

        if( count($aHeader) >= 1 ){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml_vars);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }

    /**
     *  将array转为xml
     */
    Private function arrtoxml($arr,$dom=0,$item=0){
        if(!$dom){
            $dom = new DOMDocument("1.0");
        }
        if(!$item){
            $item = $dom->createElement("xml");
            $dom->appendChild($item);
        }
        foreach ($arr as $key=>$val){
            $itemx = $dom->createElement(is_string($key)?$key:"item");
            $item->appendChild($itemx);
            if (!is_array($val)){
                $text = $dom->createTextNode($val);
                $itemx->appendChild($text);
            }else {
                arrtoxml($val,$dom,$itemx);
            }
        }
        return $dom->saveXML();
    }

    /**
     *  将xml转为array
     */
    Private function xmlToArray($xml){
        $xml_parser = xml_parser_create();
        if(!xml_parse($xml_parser,$xml,true)){
            xml_parser_free($xml_parser);
            return false;
        }else {
            $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            return $array_data;
        }
    }

    /**
     *创建md5摘要,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
     */
    Private function createSign() {
        $signPars = "";
        ksort($this->parameters);
        foreach($this->parameters as $k => $v) {
            if("" != $v && "sign" != $k) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $this->getKey();
        $sign = strtoupper(md5($signPars));
        $this->setParameter("sign", $sign);
    }

    /**
     * @param array $refund_id 退款订单编号
     */
    public function alipay_order_refund($refund_id)
    {
//        获取支付宝配置
        $alipay_config = Logic('payment')->getAlipayConfig();
        if (!$alipay_config) {
            return callback(false,'暂时不支持支付宝退款');
        }

        $refund_order = $this->getOrderByRefundID($refund_id);

        if (empty($refund_order)) {
            return callback(false,'没有可以退款的订单');
        }

        $detail_data_arr = $refund_order;
        include(BASE_PATH . "/framework/pay/alipay/refund/alipayRefund.php");
        return callback(true,'');

    }

    /**
     * 根据退款ID获取退款数据
     * @param array $refund_id_arr
     * @return mixed
     */
    public function getOrderByRefundID($refund_id_arr)
    {
        $field = 'pay_refund.refund_amount,order_pay.api_trade_no';
        $condition['pay_refund.id'] = array('in',$refund_id_arr);
        $condition['pay_refund.payment_code'] = 'alipay';
        $data = Model()->table('pay_refund,order_pay')->join('inner')->on('pay_refund.pay_sn=order_pay.pay_sn')->field($field)->where($condition)->select();
        return $data;
    }


}
