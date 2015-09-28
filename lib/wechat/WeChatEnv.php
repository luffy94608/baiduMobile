<?php

const SYS_MODEL_POST = 0;
const SYS_MODEL_OPPORTUNITY = 0;

const LOGIN_STATUS_NONE = 0;
const LOGIN_STATUS_WEIBO = 1;
const LOGIN_STATUS_WCONTACT = 2;

define('Wechat_Debug',0);

class CorpInfo
{
    public $appId;
    public $appSec;
    public $appAes;
    public $token;
    public $paySec;
    public $mchId;

    public function __construct($appId,$appSec,$appAes,$token,$paySec,$mchId)
    {
        $this->appId = $appId;
        $this->appSec = $appSec;
        $this->appAes = $appAes;
        $this->token = $token;
        $this->paySec = $paySec;
        $this->mchId = $mchId;
    }
}

class WeChatEnv
{
    /***
     * @var MemCacheBase;
     */
    static $memCache = null;
    static $redis = null;
    /***
     * @var CorpInfo
     */
    static $corpInfo = null;
    static $token = null;
    static $uid = null;

    public static function getConfig()
    {
        $config = Yaf_Registry::get('config');
        if($config == null)
        {
            $typePath = sprintf('%s/../config/%s', APPLICATION_PATH,'server_type.ini');
            $configName = 'config.ini';
            if(file_exists($typePath))
            {
                $typeConfig = new Yaf_Config_Ini($typePath, 'production');
                if($typeConfig->type == 1)
                {
                    $configName = 'config_dev.ini';
                }
                elseif($typeConfig->type == 2)
                {
                    $configName = 'config_loc.ini';
            }
            }

            $configurePath = sprintf('%s/../config/%s', APPLICATION_PATH,$configName);
            $config = new Yaf_Config_Ini($configurePath, 'production');
            Yaf_Registry::set('config', $config);
        }
        return $config;
    }

    public static function getCorpInfo()
    {
        if(self::$corpInfo != null)
        {
            return self::$corpInfo;
        }

        $model = new UserModel();
        $info = $model->getCorpInfo();
        $corpInfo = new CorpInfo($info['app_id'],$info['app_sec'],$info['aes_key'],$info['token'],$info['pay_sec'],$info['mch_id']);
        self::$corpInfo = $corpInfo;
        return $corpInfo;
    }

    public static function setCommonCookieDomainName($host='')
    {
        YafDebug::log('app session host :'.$host);
        ini_set("session.cookie_domain",$host);
    }

    public static function getHostUrl()
    {
        $config = self::getConfig();
        $urlPrefix = "http://".$config->url->host;
        return $urlPrefix;
    }

    public static function getUserToken($uid)
    {
        $redis = self::getRedis();
        $token = $redis->get(CacheKey::INFO_USER_TOKEN.'_'.$uid);
        return $token;
    }

    public static function setUserToken($uid,$token,$expire)
    {
        $redis = self::getRedis();
        $redis->set(CacheKey::INFO_USER_TOKEN.'_'.$uid,$token,0,0,$expire);
    }

    public static function getUserId()
    {
        //TODO delete
//        return '79b26166061011e5b00c5254002cbe0c';

        if(self::$uid)
        {
            return self::$uid;
        }
        $fakeId = WeChatEnv::getOpenId();
        if($fakeId)
        {
            $model =  new UserModel();
            $user = $model->getUser($fakeId);
            if($user && isset($user['uid']))
            {
                self::$uid = $user['uid'];
            }
        }

        return self::$uid;
    }

    public static function setUserId($uid)
    {
        self::$uid = $uid;
    }

    public static function getOpenId()
    {
//        return "111111";
        $session = Yaf_Session::getInstance();
        return $fakeId = $session->offsetGet('open_id');
    }

    public static function setOpenId($openId)
    {
        $session = Yaf_Session::getInstance();
        $session->offsetSet('open_id',$openId);
        setcookie('open_id', $openId, time()+3156000,'/'); // 储存open_id到Cookie中,设置很长时间
        $copenId=$_COOKIE['open_id'];
        YafDebug::log('set cookie open_id is :::::::'.$copenId);
    }


    public static function getEncryptStr($str)
    {
        $config = HaloEnv::get('config');
        $key = $config->aes->key;
        return bin2hex(aesEncrypt(base64_encode($str),$key));
    }

    public static function getDecryptStr($str)
    {
        $config = HaloEnv::get('config');
        $key = $config->aes->key;
        return  base64_decode(aesDecrypt(hex2bin($str),$key));
    }

    public static function getMemCache()
    {
        if (self::$memCache == null)
        {
            self::$memCache = new MemCacheBase();
        }
        return self::$memCache;
    }

    public static function getRedis()
    {
        if (self::$redis == null)
        {
            $config = WeChatEnv::getConfig();
            Logger::DEBUG('Redis :'.$config['redis']['host'].$config['redis']['port'].$config['redis']['password']);
            self::$redis = new HaloRedis($config['redis']['host'],$config['redis']['port'],$config['redis']['password']);
        }
        return self::$redis;
    }

    public static function checkCookies()
    {
        $config = HaloEnv::get('config');
        $isWechat = $config['app']['wehcat'];
        if($isWechat)
        {

        }
        else
        {
            self::isWeiboLogined();
        }
    }

    public static function encodeParams($str,$key,$type=false)
    {
        YafDebug::log("@@@@@@@@@@@encode params::: aeskey is {$key},data is ".json_encode($str));
        if(!$type)
        {
            return $str;
        }
        $encodeStr = base64_encode(bin2hex(aesEncrypt($str, $key)));
        return  $encodeStr;
    }


    public static function decodeParams($str,$key,$type=false)
    {
        if(!$type)
        {
            return $str;
        }
        $str =  aesDecrypt(hex2bin(base64_decode($str)),$key);
        if(strlen($str) !== 0)
        {
            return $str;
        }
        else
        {
            return false;
        }
    }

    /**
     * 套件应用中生成分享url (suite 方式)
     */

    public static  function getUrlWithCorpId($url)
    {
        return $url;
    }



    /**
     * 过滤字符串中的表情
     */
    public static function filterEmoji($text)
    {
        $clean_text = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);

        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);

        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);

        return $clean_text;
    }

    /**
     * 不需要登录的路由map
     */
    public static function getFilterRouteMap()
    {
        $map=array(
            '/my-ferry-bus',
            '/my-bus/',
            '/my-ferry-ticket',
        );
        return $map;
    }

    public static function checkLogin($status=false)
    {
        $callback=$_POST['refer'];
        $map=WeChatEnv::getFilterRouteMap();
        $needLogin=true;
        if(count($map)){
            foreach($map as $v)
            {
                if(stripos($callback,$v)!==false)
                {
                    $needLogin=false;
                    break;
                }
            }
        }

        if($needLogin || $status)
        {
            $copenId=$_COOKIE['open_id'];
            $sopenId=WeChatEnv::getOpenId();
            YafDebug::log('get cookie open_id is :::::::'.$copenId);
            if(!$sopenId && $copenId)
            {
                echo json_encode(array('code'=>401,'url'=>$callback));
                die();
            }
            $loginUrl='/register?callback='.$callback;
            echo json_encode(array('redirect_url'=>$loginUrl,'code'=>301));
            die();
        }


    }
}
