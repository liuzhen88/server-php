<?php
/**
 * 队列
 
 */
//if (empty($_SERVER['argv'][1])) exit('Access Invalid!');

define('APP_ID','crontab');
define('BASE_PATH',str_replace('\\','/',dirname(__FILE__)));
define('TRANS_MASTER',true);
if (!@include(dirname(dirname(__FILE__)).'/global.php')) exit('global.php isn\'t exists!');
if (!@include(BASE_CORE_PATH.'/emall.php')) exit('emall.php isn\'t exists!');

if (PHP_SAPI == 'cli') {
    $_GET['act'] = $_SERVER['argv'][1];
    $_GET['op'] = empty($_SERVER['argv'][2]) ? 'index' : $_SERVER['argv'][2];
}
if (!@include(BASE_PATH.'/control/control.php')) exit('control.php isn\'t exists!');

Base::run();
?>