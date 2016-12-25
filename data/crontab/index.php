<?php
/**
 * 任务计划执行入口
 */

if (empty($_SERVER['argv'][1]) || empty($_SERVER['argv'][2])) exit('parameter error');
if (!@include(dirname(dirname(__FILE__)).'/../global.php')) exit('global.php isn\'t exists!');
if (!@include(dirname(dirname(__FILE__)).'/../core/emall.php')) exit('emall.php isn\'t exists!');

$file_name = strtolower($_SERVER['argv'][1]);

$method = $_SERVER['argv'][2].'Op';

if (!@include(dirname(__FILE__).'/include/'.$file_name.'.php')) exit($file_name.'.php isn\'t exists!');

$class_name = $file_name.'Control';
$cron = new $class_name();

if (method_exists($cron,$method)){
    $cron->$method();
}else{
    exit('method '.$method.' isn\'t exists');
}