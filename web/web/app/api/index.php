<?php
error_reporting(E_ALL & ~E_NOTICE);
define('APPLICATION_PATH', realpath(dirname(__FILE__).'/../../'));

Yaf_Loader::import(APPLICATION_PATH.'/application/configs/SystemConfig.php');


$config = Yaf_Registry::get('config');

$_ENV['url']='http://'.$config['url']['host'];
$_ENV['host']=$config['url']['host'];
$_ENV['debug']=$config['app']['debug'];

define('ZMQ_PLAT_FORM', 0); //for zmq log web

Logger::$PLATFORM = Logger::PLATFORM_WEB;
Logger::timeDebug('start');

//WeChatEnv::setCommonCookieDomainName('wrm.youyun.com');
session_start();
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
if($_REQUEST['trace_type'] == 'ajax')
    header('Content-type:application/json;charset=utf-8');
else
    header('Content-type:text/html;charset=utf-8');

define('APPLICATION_VIEW_SCRIPTS_PATH', sprintf('%s/application/views/scripts', APPLICATION_PATH));
$application = new Yaf_Application( APPLICATION_PATH . '/application/configs/application.ini');
$application->bootstrap()->run();


//HaloXhprof::disable($_SERVER['REQUEST_METHOD'] == 'GET');
Logger::timeDebug('end');