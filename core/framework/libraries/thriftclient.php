<?php

defined('emall') or exit('Access Invalid!');
/**
 * Thrift PHP通信客户端
 * 
 * @author lijunhua
 * @since 2015-09-15
 * @version  thrift0.9.2
 */
//error_reporting(E_ALL);
define('THRIFT_HOST',        C('thrift.host')); //服务器
define('THRIFT_PORT',        C('thrift.port')); //端口
define('THRIFT_LIB',         BASE_DATA_PATH . '/api/thrift/lib');
define('THRIFT_SERVICE',     BASE_DATA_PATH . '/api/thrift/service');

require_once THRIFT_LIB .'/Thrift/ClassLoader/ThriftClassLoader.php';

use Thrift\ClassLoader\ThriftClassLoader;

$GEN_DIR = BASE_DATA_PATH . '/api/thrift/gen-php';
$loader = new ThriftClassLoader();

$loader->registerNamespace('Thrift', THRIFT_LIB);
$loader->registerDefinition('shared', $GEN_DIR);
$loader->registerDefinition('tutorial', $GEN_DIR);
$loader->register();

require_once THRIFT_LIB . '/Thrift/Protocol/TProtocol.php';
require_once THRIFT_LIB . '/Thrift/Transport/TTransport.php';
require_once THRIFT_LIB . '/Thrift/Type/TType.php';
require_once THRIFT_LIB . '/Thrift/Type/TMessageType.php';
require_once THRIFT_LIB . '/Thrift/Factory/TStringFuncFactory.php';
require_once THRIFT_LIB . '/Thrift/StringFunc/TStringFunc.php';
require_once THRIFT_LIB . '/Thrift/StringFunc/Core.php';

require_once THRIFT_LIB . '/Thrift/Protocol/TProtocolDecorator.php';
require_once THRIFT_LIB . '/Thrift/Protocol/TCompactProtocol.php';
require_once THRIFT_LIB . '/Thrift/Protocol/TMultiplexedProtocol.php';
require_once THRIFT_LIB . '/Thrift/Transport/TSocket.php';
require_once THRIFT_LIB . '/Thrift/Transport/TFramedTransport.php';
require_once THRIFT_LIB . '/Thrift/Exception/TException.php';
require_once THRIFT_LIB . '/Thrift/Exception/TTransportException.php';

use Thrift\Protocol\TCompactProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\TFramedTransport;
use Thrift\Exception\TException;

class ThriftClient{
    

    private static $_instance = null;

    private function __construct() {
        
    }

    private function __clone() {
    }

    static public function getInstance() {
        if (is_null ( self::$_instance ) || isset ( self::$_instance )) {
            self::$_instance = new self ();
        }
        return self::$_instance;
    }
    
    /**
     * thrift通信主方法
     * 
     * @param string $class_function  格式：类名::方法名
     * @param array  $param           参数 
     */
    public function send($class_function, $param) {
        $function = explode('::', $class_function);
        if (count($function) != 2) {
            return array('code' => '80000', 'message' => '参数格式错误');
        }

        $class_name = $function[0];
        $method_name = $function[1];

        try {

            $socket = new TSocket(THRIFT_HOST, THRIFT_PORT);
            $transport = new TFramedTransport($socket, 1024, 1024);
            require_once THRIFT_SERVICE . '/' . $class_name . '.php';
            $protocol = new TMultiplexedProtocol(new TCompactProtocol($transport), $class_name);
            $class_name = $class_name . 'Client';
            if (!class_exists($class_name)) {
                return array('code' => '80005', 'message' => '类不存在');
            }
            $client = new $class_name($protocol);

            $transport->open(); //打开
            if (!method_exists($client, $method_name)) {
                return array('code' => '80005', 'message' => '方法不存在');
            }
            $rs = $client->$method_name(json_encode($param));
            $transport->close(); //关闭
        } catch (TException $tx) {
            return array('code' => '80005', 'message' => 'TException: ' . $tx->getMessage());
        }
        return $this->object_to_array(json_decode($rs));
    }

    /**
     * object转array
     */
    private function object_to_array($obj) {
        $arr = array();
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ((array) $_arr as $key => $val) {
            $val = (is_array($val)) || is_object($val) ? $this->object_to_array($val) : $val;
            $arr[$key] = $val;
        }

        return $arr;
    }

}
