<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 14/10/24
 * Time: 上午10:57
 */

class WechatSession
{
    private static  $corpName;
    public  static function getInstance()
    {
        static $instance;

        if(!isset($instance)){
            $c = __CLASS__;
            $instance = new $c;
            $t = WeChatEnv::getCorpAes();
            self::$corpName = isset($t) ? WeChatEnv::getCorpAes() : 'session';
        }

        return $instance;
    }

    public function offsetGet($name)
    {
        $session = Yaf_Session::getInstance();
        $corpSession = $session->offsetGet(self::$corpName);

//        YafDebug::log("\n WechatSession  current url is".'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//        YafDebug::log("\n WechatSession  corpSession name is".WeChatEnv::getCorpAes());
//        YafDebug::log("\n WechatSession offsetGet corpSession array is".json_encode($corpSession));

        if($corpSession && is_array($corpSession) && array_key_exists($name,$corpSession))
        {
            return $corpSession[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @param string $value
     * @return void
     */

    public function offsetSet($name, $value)
    {
        $session = Yaf_Session::getInstance();
        $corpSession = $session->offsetGet(self::$corpName);
        if(!isset($corpSession))
        {
            $corpSession = array();
        }
        $corpSession[$name]=$value;

//        YafDebug::log("\n WechatSession  corpSession name is".self::$corpName);
//        YafDebug::log("\n WechatSession offsetSet corpSession array is".json_encode($corpSession));

        $session->offsetSet(self::$corpName,$corpSession);
    }

    /**
     * @param string $name
     * @return void
     */
    public function offsetUnset($name)
    {
        $session = Yaf_Session::getInstance();
        $corpSession = $session->offsetGet(self::$corpName);
        if(is_array($corpSession) && array_key_exists($name,$corpSession))
        {
            unset($corpSession[$name]);
            $session->offsetSet(self::$corpName,$corpSession);
        }
    }

    public function del($name)
    {
        $session = Yaf_Session::getInstance();
        $corpSession = $session->offsetGet(self::$corpName);
        if(is_array($corpSession) && array_key_exists($name,$corpSession))
        {
            unset($corpSession[$name]);
            $session->offsetSet(self::$corpName,$corpSession);
        }
    }


} 