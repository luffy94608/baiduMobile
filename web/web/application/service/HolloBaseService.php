<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/13
 * Time: 上午10:55
 */

class HolloHttpResult
{
    /***
     * 0-no error; 400xx...-error num
     * @var int
     */
    const ErrCodeNeedLogin = -1000;

    public $code;

    public $data;
    public $errMsg;

    public function __construct($data,$code = 0,$errMsg ='')
    {
        $this->data = $data;
        $this->code = $code;
        $this->errMsg = $errMsg;
    }
}

define('DaySecs',60*60*24);

class HolloBaseService
{
    const API_URL_PREFIX = 'http://211.151.0.150:80';
    const API_V2_URL_PREFIX = 'http://211.151.0.150:80';
//    const API_URL_PREFIX = 'http://api.hollo.cn';
//    const API_V2_URL_PREFIX = 'http://api.hollo.cn';

    public $token;

    public function __construct($uid = '')
    {

    }

    protected  function http_get($url,$param='',$restParam='',$needAuth=true)
    {
        return $this->http_request('GET',$url,$param,$restParam,$isXml = false,$needAuth);
    }

    /***
     * @param $url
     * @param string $param
     * @param string $restParam
     * @return array|HolloHttpResult|mixed
     */
    protected function http_post($url,$param = '',$restParam='')
    {
        return $this->http_request('POST',$url,$param,$restParam);
    }

    private function http_request($method,$url,$param = '',$restParam='',$isXml = false,$needAuth=true)
    {

        $oCurl = curl_init();

        $header = array(
            'User-Agent: PinChe/1.0.0(wechat)',
            'HOLLO-Version:400',
            'HOLLO-Platform:wechat',
            'HOLLO-OS:wechat',
        );

        if($isXml)
        {
            $header[] = 'Content-Type: text/xml';
        }
        else
        {
            $header[] = 'Content-Type: application/json; charset=utf-8';
        }

        if(stripos($url,"https://")!==FALSE)
        {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        switch($method)
        {
            case 'GET':
            {
                if($method == 'DELETE')
                {
                    curl_setopt ( $oCurl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                }

                if(is_array($param))
                {
                    $parmArray = array();
                    foreach($param as $k=>$v)
                    {
                        $temp = $k.'='.urlencode($v);
                        $parmArray[] = $temp;
                    }
                    if(!empty($parmArray))
                    {
                        $parmStr = implode('&',$parmArray);
                        $url .= '?'.$parmStr;
                    }
                }
                elseif(is_string($param) && !empty($param))
                {
                    $url .= '?'.$param;
                }

                YafDebug::log('get/delete url with param is '.$url);
                break;
            }
            case 'POST':
            {
                if(!$isXml)
                {
                    if (is_string($param))
                    {
                        $strPOST = $param;
                    }
                    else
                    {
                        $strPOST =  utf8_json_encode($param);
                    }
                }
                else
                {
                    $strPOST = $param;
                }
                $header[] = 'Content-Length: ' . strlen($strPOST);

                curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);

                if($method == 'POST')
                {
                    YafDebug::log('post param is '.json_encode($param));
                    curl_setopt($oCurl, CURLOPT_POST,true);
                }
                else
                {
                    YafDebug::log('put param is '.json_encode($param));
                    curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, "PUT");

                }
                break;
            }
        }

        curl_setopt($oCurl, CURLOPT_HTTPHEADER,$header);

//handle url
        if(!empty($restParam))
        {
            $url = $this->handleUrlParams($url,$restParam);
        }

        YafDebug::log('http header is'.json_encode($header));
        YafDebug::log('http url is '.$url);

        curl_setopt($oCurl, CURLOPT_URL, $url);

        $sContent = curl_exec($oCurl);
        YafDebug::log('http res is '.$sContent);

        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);

        if($isXml)
        {
            $aContent = (array)simplexml_load_string($sContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            if($aContent['return_code'] == 'SUCCESS')
            {
                $res = new HolloHttpResult($aContent);
            }
            else
            {
                $res = new HolloHttpResult($aContent,ErrorUtil::ErrPayServerError,$aContent['return_msg']);
            }
            return $aContent;
        }

        $aContent = json_decode($sContent,true);
        YafDebug::log('http status code  res is'.json_encode($aStatus));
        if(intval($aStatus["http_code"])==200)
        {

            if(isset($aContent['code']))
            {
                $res = new HolloHttpResult($aContent['data'],$aContent['code'],$aContent['msg']);
            }
            else
            {
                $res = new HolloHttpResult($aContent);
            }

            return $res;
        }
        else if(intval($aStatus["http_code"])>=400 && intval($aStatus["http_code"])<500)
        {
            if($aStatus["http_code"] == 401)
            {
                $code =  ErrorUtil::ErrCodeNeedLogin;
            }
            else
            {
                $code = $aContent['code'];
            }
            $res = new HolloHttpResult($aContent,$code,$aContent['message']);
            return $res;
        }
        else
        {
            YafDebug::log('err not found');
            return new HolloHttpResult($aContent,ErrorUtil::ErrUnkownErr);
        }
    }

    private function handleUrlParams($url,&$params)
    {
        $res = $url;
        foreach($params as $k=>$v)
        {
            $key = sprintf('<:%s>',$k);
            $res = str_replace($key,$v,$res);
        }
        return $res;
    }


    public function getListQueryParams($time,$next,$cursor)
    {
        return array(
            'cursor_id'=>intval($cursor),
            'is_next'=>intval($next),
            'timestamp'=>intval($time)
        );
    }

}