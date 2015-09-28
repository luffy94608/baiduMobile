<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/2/3
 * Time: 下午3:36
 */

class WechatRedPacketInfo
{
    public $mch_id;
    public $mch_billno;
    public $wxappid;
    public $nick_name;
    public $send_name;
    public $total_amount;
    public $min_value;
    public $max_value;
    public $total_num;
    public $wishing;
    public $client_ip;
    public $act_name;
    public $remark;
    public $re_openid;

    public $logo_imgurl;
    public $share_content;
    public $share_url;
    public $share_imgurl;
}

class WechatRedPacket
{
//    static private $paySecret = 'c82c38d76e8ad0a6170c79efa1787ceb';

    /***
     * @var WechatRedPacketInfo
     */
    public $redPacketInfo;
    public $paySec;
    public $sslCerPath;
    public $sslKeyPath;
    public $caPath;

    public $errcode;
    public $errmsg;

    public function __construct($paySec,$sslCerPath,$sslKeyPath,$caPath,WechatRedPacketInfo $redPacketInfo)
    {
        $this->paySec = $paySec;
        $this->sslCerPath = $sslCerPath;
        $this->sslKeyPath = $sslKeyPath;
        $this->caPath = $caPath;
        $this->redPacketInfo = $redPacketInfo;
    }

    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    private  function getRandomStr($len = 16)
    {
        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < $len; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

    static function getMchBillNumber($mchId,$tailId)
    {
        $timeStr = date('Ymd',time());
        $idStr = strval($tailId);
        $len = strlen($idStr);
        if($len > 10)
        {
            $tailStr = substr($idStr,$len - 11,$len);
        }
        else
        {
            $needLen = 10 - $len;
            $fmt = sprintf('%%0%dd',$needLen);
            $tailStr = sprintf($fmt,$idStr);
        }
        return $mchId.$timeStr.$tailStr;
    }

    private function getSignStr($array)
    {
        ksort($array,SORT_STRING);
        $strArray = array();
        foreach($array as $k=>$v)
        {
            $strArray[] = $k.'='.$v;
        }
        $strArray[] = 'key='.$this->paySec;

        $sign = strtoupper(md5(implode('&',$strArray)));
        return $sign;
    }

    public function createSendArray()
    {
        if(empty($this->redPacketInfo) || empty($this->paySec))
        {
            return false;//参数不全
        }
        $r = new ReflectionClass($this->redPacketInfo);//反射
        $propertyList = $r->getProperties();
        foreach ($propertyList as $property)
        {
            if($property->getValue($this->redPacketInfo) != null)
            {
                $sendArray[$property->getName()] = $property->getValue($this->redPacketInfo);
            }
        }
        $sendArray['nonce_str'] = $this->getRandomStr(rand(16,32));

        $signStr = $this->getSignStr($sendArray);
        $sendArray['sign'] = $signStr;

        return $sendArray;
    }

    public function sendRedPacket()
    {
        $sendArray = $this->createSendArray();
        $res = $this->redPacketSend($sendArray);
        return $res;
    }

//network
    public function redPacketSend($msg)
    {
        $xmldata=  $this->xml_encode($msg);
        YafDebug::log('send packet result is ::'.json_encode($msg));
        $result = $this->http_post('https://api.mch.weixin.qq.com'.'/mmpaymkttransfers/sendredpack',$xmldata);
        if($result)
        {
            $array = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
            YafDebug::log('send packet result is ::'.json_encode($array));

            if($array['return_code'] == 'SUCCESS' && $array['result_code'] == 'SUCCESS')
            {
                return $array;
            }
            $this->errcode = $array[''];
            return false;
        }
        return false;
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8')
    {
        if(is_array($attr))
        {
            $_attr = array();
            foreach ($attr as $key => $value)
            {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml   = "<{$root}{$attr}>";
        $xml   .= self::data_to_xml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    public static function data_to_xml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }
    public static function xmlSafeStr($str)
    {
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }

    private function http_post($url,$param)
    {
        $header[] = "Content-type: text/xml";        //定义content-type为xml,注意是数组
        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,$this->sslCerPath);
        curl_setopt($ch,CURLOPT_SSLKEY,$this->sslKeyPath);
        curl_setopt($ch,CURLOPT_CAINFO,$this->caPath);

        $response = curl_exec($ch);
        if(curl_errno($ch)){
            print curl_error($ch);
        }
        curl_close($ch);
        return $response;

//        $oCurl = curl_init();
//        YafDebug::log('post url is '.$url);
//        YafDebug::log('post param is '.$param);
//        if(stripos($url,"https://")!==FALSE){
//            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
//            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
//        }
//        if (is_string($param))
//        {
//            $strPOST = $param;
//        }
//        else
//        {
//            $strPOST =  $this->xml_encode($param);
//        }
//        curl_setopt($oCurl, CURLOPT_URL, $url);
//        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
//        curl_setopt($oCurl, CURLOPT_POST,true);
//        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
//
//        $sContent = curl_exec($oCurl);
//        $aStatus = curl_getinfo($oCurl);
//        curl_close($oCurl);
//        YafDebug::log('post res is '.$sContent);
//        if(intval($aStatus["http_code"])==200){
//            return $sContent;
//        }else{
//            return false;
//        }
    }
}
