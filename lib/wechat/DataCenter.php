<?php

class DataCenter
{
    private static $connections = array('db'=>array(),'redis'=>array(),'mongo'=>array(), 'mc'=>array());

    /**
     * @param $name
     * @return HaloDataSource
     * @throws Exception
     */
    public static function getDb($name)
    {
        if (isset(static::$connections['db'][$name]))
        {
            $db = static::$connections['db'][$name];
            if($db->mysqlPing())
            {
                return static::$connections['db'][$name];
            }
        }
        $config = Yaf_Registry::get('config');

        $dbConfig = $config->db->{$name};
        if (empty($dbConfig))
        {
            $config = WeChatEnv::getConfig();
            $dbConfig = $config->db->{$name};
            if(empty($dbConfig))
            {
                throw new Exception(sprintf('config of db %s is not found', $name), -9999);
            }
        }
        $db = new HaloDataSource(array('host'=>$dbConfig->host, 'port'=>$dbConfig->port, 'user'=>$dbConfig->user, 'pass'=>$dbConfig->passwd, 'dbname'=>$dbConfig->name),$name);
        return static::$connections['db'][$name] = $db;
    }




    /**
     * @param string $name
     * @return Redis
     * @throws Exception
     */
    public static function getRedis($name='wechat')
    {
        if (isset(static::$connections['redis'][$name]))
        {
            return static::$connections['redis'][$name];
        }

        $config = WeChatEnv::getConfig();
        $redis = new Redis();
        $redisConfig = $config->redis;

        if (empty($redisConfig))
        {
            throw new Exception(sprintf('config of redis %s is not found', $name), -9998);
        }

        $redis->pconnect($redisConfig->host,$redisConfig->port);
        return static::$connections['redis'][$name] = $redis;
    }


    /**
     * @param string $name
     * @return MemCacheBase
     * @throws Exception
     */
    public static function getMc($name='wechat')
    {
        if (isset(static::$connections['mc'][$name]))
        {
            return static::$connections['mc'][$name];
        }

        $config = WeChatEnv::getConfig();
        $mcConfig = $config->memcache;
        if (empty($mcConfig))
        {
            throw new Exception(sprintf('config of memcache %s is not found', $name), -9999);
        }
        $serverCount = intval($mcConfig->$name->count);
        $mc = new MemCacheBase($name);
//        for($i = 1; $i<= $serverCount; $i++)
//        {
//            $hostKey = 'host_'.$i;
//            $portKey = 'port_'.$i;
//            $mc->addServer($mcConfig->$name->$hostKey, $mcConfig->$name->$portKey);
//        }

        return static::$connections['mc'][$name] = $mc;
    }

    public static function getWeiboClient()
    {
        if (isset(static::$connections['weibo']))
        {
            return static::$connections['weibo'];
        }

        $config = HaloEnv::get('config');
        $akey = $config['weibo']['akey'];
        $skey = $config['weibo']['skey'];

        if($_COOKIE['token'])
        {
            return static::$connections['weibo'] = new SaeTClientV2($akey, $skey, null, $_COOKIE['token'], '' );
        }
        else
        {
            if(empty($_COOKIE['SUE']) && empty($_COOKIE['SUW']))
            {
                $session = static::getDb('web')->getRowByCondition('account_session','Fuid>0 AND Ftype=0 ORDER BY Ftime DESC');
                $token = WContact_Session_Handler::unserializesession($session['Fdata']);
                return static::$connections['weibo'] = new SaeTClientV2($akey, null, $token['Auth'], '' );
            }

            return static::$connections['weibo'] = new SaeTClientV2($akey, null, $_COOKIE, null, '' );
        }
    }

    public static  function getMongo()
    {
        return false;
    }
}


