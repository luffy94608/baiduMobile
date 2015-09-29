<?php

class ControllerBase extends WechatControllerBase
{
    /**
     * @var YafView
     */
    protected $view;

    public function init()
    {
        parent::init();
        $this->_view->setRequest($this->getRequest());
        $this->view = $this->_view;
//        $this->inputNotLoginResult();
    }
    public function checkLogin()
    {
        if(empty($this->uid))
        {
            $callback=$_POST['refer'];
            $loginUrl='/register?callback='.$callback;
            $this->inputResultJumpUrl($loginUrl);
        }
    }

    protected  function enAes($str)
    {
        if(!$str)
        {
            return $str;
        }
        $config = Yaf_Registry::get('config');
        $key =  $config->aes->key;
        $str = aesEncrypt($str, $key);

        $str=base64_encode(bin2hex($str));
        return $str;
    }

    protected function deAes($str)
    {
        if(!$str)
        {
            return $str;
        }
        $str=hex2bin(base64_decode($str));

        $config = Yaf_Registry::get('config');
        $key =  $config->aes->key;
        $str = aesDecrypt($str, $key);
        return trim($str);
    }

    public function jumpDirect($url='/')
    {
        YafDebug::log('jump ::'.$url);
        header('Location: '.$url);
        haloDie();
    }

    protected function checkReferer()
    {

        $refer = $_SERVER['HTTP_REFERER'];
        if(empty($refer))
            $this->inputRefererErrorResult();
        else
        {
            $config = HaloEnv::get('config');
            $host = $config['url']['host'];
            $legalHost = array($host,'local.hollo.baidu.com','baidu.hollo.cn','bd.hollo.cn',);
            if($config['app']['debug'])
            {
                $legalHost[] = '127.0.0.1';
            }
            $url = parse_url($refer);
            $result = false;
            foreach($legalHost as $v)
            {
                $pos = stripos($url['host'],$v);
                if($pos!==false)
                {
                    $result = true;
                    break;
                }
            }
            if($result===false)
                $this->inputRefererErrorResult();
            else
            {
                if($_REQUEST['trace_type']!='ajax')
                {
                    echo $_REQUEST['trace_type'];
                    $this->inputRefererErrorResult();
                }
            }
        }
    }

    /***
     * 获取城市列表
     * @return array|bool|string
     */
    public function getBusCityList()
    {
        $mc = DataCenter::getMc();
        $cityList = $mc->get(CacheKey::INFO_CITY_LIST);
        if(!$cityList)
        {
            $service = new HolloBusService();
            $res = $service->getBusCityList();
            if($res->code == 0)
            {
                $cityList = $res->data;
                if($cityList)
                {
                    $mc->set(CacheKey::INFO_CITY_LIST,$cityList,24*3600);
                }
            }
        }
        return $cityList;
    }

}