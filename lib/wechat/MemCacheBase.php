<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jet
 * Date: 13-10-10
 * Time: 上午11:31
 * To change this template use File | Settings | File Templates.
 */


class MemCacheBase
{

    const DEFAULT_EXPIRE = 86400;

    /**
     * @var HaloPdo
     */
    public $db;

    /**
     * @var Memcache
     */
    static public $mc;
    static public $mcArray;//mc的数组
    static public $tableKeyMap;//存储table表名和key之间的关系

    public function __construct($mcName='wechat')
    {
        if(!isset(static::$tableKeyMap))
        {
            static::$tableKeyMap = array();

            static::$tableKeyMap['account_user'] = array(
                'uid'=>array(
                    CacheKey::INFO_ACCOUNT_INFO,
                ));

        }
        MemCacheBase::getMc($mcName);

    }

    /**
     * generate key
     * @param $ids
     * @param $tag
     * @return string
     */
    public  function makeKey($id, $tag)
    {
        $prefix = self::$mc->get('ns:'.$tag);
        if($prefix === false)
        {
            $prefix = sprintf('%s#%d', $tag, substr(strval(time()), -4));
            self::$mc->set('ns:'.$tag, $prefix, 0, 86400*3);
//            Logger::traceUser('ns:'.$tag, 'set-tag');
        }
        
        return  empty($id) ? $prefix : $prefix.'#'.$id;
    }
    public function invalidAllValuesByTag($tag)
    {
        $prefix = self::$mc->get('ns:'.$tag);
        if($prefix)
        {
//            Logger::traceUser($prefix, 'del-tag');
            $prefix = sprintf('%s#%d', $tag, substr(strval(time()), -4));
            self::$mc->set('ns:'.$tag, $prefix, 0, 86400*3);
        }
    }

    /**
     * init MC SEVER
     * @param string $name
     * @return Memcache
     * @throws Exception
     */
    public  static function getMc($name='wechat')
    {
        if (self::$mc == null){
            $config = WeChatEnv::getConfig();
            $mcConfig = $config->memcache;
            if (empty($mcConfig))
            {
                YafDebug::log('Failed to connect to memcache !');
            }
            $serverCount = intval($mcConfig->$name->count);
            try{

                $memcache = new Memcache();
                for($i = 1; $i<= $serverCount; $i++)
                {
                    $hostKey = 'host_'.$i;
                    $portKey = 'port_'.$i;
                    $memcache->addServer($mcConfig->$name->$hostKey, $mcConfig->$name->$portKey);
                }
                self::$mc = $memcache;

            } catch(Exception $e){

                YafDebug::log('Failed to connect to memcache !');
            }
        }

    }

    /**
     * @param $key
     * @param $data
     * @param int $expire
     * @return bool
     */
    public function set($key, &$data, $expire= MemCacheBase::DEFAULT_EXPIRE)
    {
        if(self::$mc == null || empty($key))
            return false;

//        Logger::traceMc($key, 'set');
        $ret = self::$mc->set($key, $data, 0, $expire);
        if($ret === false)
        {
            YafDebug::log(sprintf("Failed to set key:%s",$key));
            $msg = sprintf('Memcahce set failed for key:%s|size:%d', $key, strlen(serialize($data)));
//            Logger::traceRealtime('mc_set_failed', $msg);
        }
        return $ret;
    }

    /**
     * @param $key
     * @return array|bool|string
     */
    public function get($key)
    {
        if ( self::$mc == null || empty($key))
            return false;
        
        $ret = self::$mc->get($key);
//        Logger::traceMc($key, $ret===false ? 'miss' : 'hit');
////        TODO disable meme cache
//        return false;
        return $ret;
    }


    private function delete($key)
    {
//        Logger::DEBUG(sprintf('delete tag :%s',$key));
        if ( self::$mc == null ) return false;
        
//        Logger::traceMc($key, 'del-key');
        return self::$mc->delete($key);
    }

    public function setByIdAndTag($id, $tag, &$data, $expire= MemCacheBase::DEFAULT_EXPIRE)
    {
        if($data == '')
        {
//            Logger::DEBUG('row is empty');
        }
        return $this->set($this->makeKey($id, $tag), $data, $expire); 
    }

    public function getByIdAndTag($id, $tag)
    {
        return $this->get($this->makeKey($id, $tag));
    }

    public function multiGetByIdsAndTag($ids, $tag)
    {
        foreach($ids as $id)
        {
            $keys[] = $this->makeKey($id, $tag);
        }
        return $this->get($keys);
    }

    public function flush()
    {
        if ( self::$mc == null )
            return false;
        return self::$mc->flush();
    }
    public function deleteByIdAndTag($id, $tag)
    {
        if ( self::$mc == null || empty($tag)) return;
        
        $key = $this->makeKey($id, $tag);
        $this->delete($key);
    }
    function increment($tag, $id, $count)
    {
        $key = $this->makeKey($id, $tag);
        $ret = self::$mc->increment($key, $count);
    }
    
    /**
     * 过滤已存在memcache的内容
     * @param $uids
     * @param $tag
     * @param $result
     * @return array
     */
    public function getMissedId($uids,$tag,&$result)
    {
        $remainIds = array();
        foreach($uids as $uid)
        {
            $dataMc = $this->getByIdAndTag($tag,$uid);
            if($dataMc)
            {
                $result[]=$dataMc;
            }
            else
            {
                $remainIds[]=$uid;
            }
        }
        return $remainIds;
    }
    
    public function setArrayWithIdKey($key,$tag,$array)
    {
        foreach($array as $a)
        {
            $id = $a[$key];
            if(isset($id))
            {
                $this->setByIdAndTag($tag,$id,$a,self::DEFAULT_EXPIRE);
            }
        }
    }
}


