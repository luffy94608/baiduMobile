<?php

date_default_timezone_set('Asia/Shanghai');
error_reporting(E_ALL & ~E_NOTICE);

defined('ERROR_LOG_FILE') || define('ERROR_LOG_FILE', 'error');

defined('IMAGE_ROOT') || define('IMAGE_ROOT', 'http://weirenmai002.com/images');

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__DIR__).'/../'));
defined('LIB_PATH') || define('LIB_PATH', realpath(APPLICATION_PATH . '/../../lib'));
defined('API_PATH') || define('API_PATH', realpath(APPLICATION_PATH . '/api'));
defined('CUSTOM_LIB_PATH') || define('CUSTOM_LIB_PATH', realpath(APPLICATION_PATH . '/library'));
defined('COMMON_CONFIG') || define('COMMON_CONFIG', realpath(APPLICATION_PATH . '/../config'));

class SystemConfig
{
    public static function  init()
    {
        $_ENV['APP_NAME']=(pathinfo(realpath(APPLICATION_PATH),  PATHINFO_BASENAME ));

        //load config
//        $configurePath = sprintf('%s/../config/config.ini', APPLICATION_PATH);
//        $config = new Yaf_Config_Ini($configurePath, 'production');
//        Yaf_Registry::set('config', $config);

        SystemConfig::loadEssentials();



    }

    public static function get($key)
    {
        if(empty($key))
            return null;

        if(HaloEnv::isRegistered($key))
            return HaloEnv::get($key);

        $obj = null;
        $methodName = self::getMethodName($key);
        if(method_exists('SystemConfig', $methodName))
        {
            $config = HaloEnv::get('config');
            $obj = call_user_func_array(array('SystemConfig', $methodName), array($key, $config));
            HaloEnv::set($key, $obj);
        }

        return $obj;
    }

    public static function loadEssentials()
    {
        Yaf_Loader::import(sprintf('%s/wechat/WechatInclude.php', LIB_PATH));
        Yaf_Loader::import(sprintf('%s/yaf/LocalAutoLoader.php', CUSTOM_LIB_PATH));

        LocalAutoLoader::register();

        $config = WeChatEnv::getConfig();

        YafDebug::$LOG_LEVEL = $config->log->level;
        YafDebug::$LOG_BASE_DIR = self::getLogDir();
        HaloEnv::instance($config);
    }

    public static function getLogDir()
    {
        $config = Yaf_Registry::get('config');
        $basedir = '../logs';
        if($config->log->basedir)
        {
            $basedir = $config->log->basedir;
        }
        return sprintf('%s/%s/%s', APPLICATION_PATH, $basedir, $_ENV['APP_NAME']);
    }
    //----------------------------------------------------------------------------
    //For test
    public static function printDebug($msg, $debug=false)
    {
        if($debug)
            echo "<div>$msg<div/>\n";
    }

    public static function checkConnection($debug=false)
    {
        $time = microtime();

        $dbs = array('web','task','company');
        foreach($dbs as $name)
        {
            $db = DataCenter::getDb($name);
            SystemConfig::printDebug('正在连接数据库：'.$name, true);
            $db->query("SET NAMES 'utf8'");
        }
        
        $startTime = microtime();

        SystemConfig::printDebug('正在连接mongo....', $debug);
        $kv = DataCenter::getMongo();
        if(!$kv->isConnected())
            SystemConfig::printDebug('mongodb连接失败!', $debug);
        SystemConfig::printDebug(sprintf('连接耗时：%.3fms', microtime()-$startTime), $debug);
        
        $startTime = microtime();
        SystemConfig::printDebug('正在连接memchace....', $debug);
        DataCenter::getMc();
        SystemConfig::printDebug(sprintf('连接耗时：%.3fms', microtime()-$startTime), $debug);

        $startTime = microtime();
//        SystemConfig::printDebug('正在连接Redis....', $debug);
//        DataCenter::getRedis();
        SystemConfig::printDebug(sprintf('连接耗时：%.3fms', microtime()-$startTime), $debug);
//
    }
}

SystemConfig::init();

if(isset($argv[1]))
{
    if($argv[1] == 'test-mongo')
    {
        $kv = SystemConfig::get('kv');
        $kv->set('frank','{}{}{}{}');
        $ret = $kv->get('frank');
        LogUtil::logObject($ret);
    }
}