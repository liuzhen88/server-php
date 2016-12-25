<?php
/**
 * 记录日志 
 * @package    library

 * @since      File available since JD Release v3.0
 */
defined('emall') or exit('Access Invalid!');
class Log{

    const SQL       = 'SQL';
    const ERR       = 'ERR';
    private static $log =   array();

    public static function record($message,$level=self::ERR) {
        $now = @date('Y-m-d H:i:s',time());
        switch ($level) {
            case self::SQL:
               self::$log[] = "[{$now}] {$level}: {$message}\r\n";
               break;
            case self::ERR:
                $error_log_path = C('error_log_path');
                if (!empty($error_log_path))
                {
                    $log_file = $error_log_path.'/'.date('Ymd',TIMESTAMP).'.log';
                    $url = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
                    $url .= " ( act={$_GET['act']}&op={$_GET['op']} ) ";
                    $content = "[{$now}] {$url}\r\n{$level}: {$message}\r\n";
                    @chmod($log_file, 0777);
                    @file_put_contents($log_file,$content, FILE_APPEND);
                }
                
                break;
        }
    }

    public static function read(){
    	return self::$log;
    }
}