<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/11
 * Time: 下午2:12
 */
class WechatPayInfo
{
    public $out_trade_no;//hollo id
    public $goods_tag;//商品标记，代金券或立减优惠功能的参数
    public $total_fee;
    public $attach;//额外信息
    public $body;//商品或支付单简要描述
    public $detail;
    public $spbill_create_ip;

    public $trade_type; //0-jsapi; 1-native
    public $product_id;//trade_type=NATIVE，此参数必传
    public $openid;//trade_type=JSAPI，此参数必传
}

class WechatPayErrInfo
{
    /***
     * 1-return error 传输错误
     * 2-result error 业务错误
     */
    public $type;
    public $code;
    public $msg;
}



class WechatPay
{

    const API_URL_PREFIX = 'https://api.mch.weixin.qq.com';
    //pay
    const PAY_UNIFIEDORDER = '/pay/unifiedorder';
    const PAY_RESULT_QUERY = '/pay/orderquery';

    const CALL_BACK_URL = 'http://wx.hollo.cn/api/pay/notify';

    const PAY_QRCODE_PREFIX ='weixin://wxpay/bizpayurl?';

    public  $appId;
    public  $paySec;
    public  $mchId;
    public  $errcode;
    public  $errmsg;

    public function __construct($appId,$paySec,$mchId)
    {
        $this->paySec = $paySec;
        $this->appId = $appId;
        $this->mchId = $mchId;
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

//        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
//        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
//        curl_setopt($ch,CURLOPT_SSLCERT,$this->sslCerPath);
//        curl_setopt($ch,CURLOPT_SSLKEY,$this->sslKeyPath);
//        curl_setopt($ch,CURLOPT_CAINFO,$this->caPath);

        $response = curl_exec($ch);
        if(curl_errno($ch)){
            print curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }


    private function createOrderArray(WechatPayInfo $payInfo)
    {
        $r = new ReflectionClass($payInfo);//反射
        $propertyList = $r->getProperties();
        foreach ($propertyList as $property)
        {
            if($property->getValue($payInfo) != null)
            {
                if($property->getName() == 'product_id' || $property->getName() == 'openid')
                {
                    continue;
                }
                $sendArray[$property->getName()] = $property->getValue($payInfo);
            }
        }

        if($payInfo->trade_type == 'JSAPI')
        {
            $sendArray['openid'] = $payInfo->openid;
        }
        else if($payInfo->trade_type == 'NATIVE')
        {
            $sendArray['product_id'] = $payInfo->product_id;
        }

        $sendArray['appid'] = $this->appId;
        $sendArray['mch_id'] = $this->mchId;

        $sendArray['notify_url'] = self::CALL_BACK_URL;
        $sendArray['nonce_str'] = $this->getRandomStr(rand(16,32));
        $signStr = $this->getSignStr($sendArray);
        $sendArray['sign'] = $signStr;

        return $sendArray;
    }

    public function createJSPayPacket($prePayId)
    {
        $sendArray['appId'] = $this->appId;
        $sendArray['nonceStr'] = $this->getRandomStr(rand(16,32));
        $sendArray['timeStamp'] = time();
        $sendArray['package'] = 'prepay_id='.$prePayId;
        $sendArray['signType'] = 'MD5';
        $signStr = $this->getSignStr($sendArray);
        $sendArray['paySign'] = $signStr;
        $sendArray['timestamp'] = $sendArray['timeStamp'];
        unset($sendArray['appId']);
        return $sendArray;
    }


//network
    private  function preOrderSend($msg)
    {
        $xmldata=  $this->xml_encode($msg);
        YafDebug::log('send pay request is ::'.json_encode($msg));
        $result = $this->http_post(self::API_URL_PREFIX.self::PAY_UNIFIEDORDER,$xmldata);
        if($result)
        {
            $array = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
            YafDebug::log('send pay result is ::'.json_encode($array));

            if($array['return_code'] == 'SUCCESS' && $array['result_code'] == 'SUCCESS')
            {
                return $array;
            }
            $this->errcode = $array[''];
            return false;
        }
        return false;
    }


    /***
     * 统一下单api
     * @param WechatPayInfo $payInfo
     * @return array|bool
     */
    public function createPreOrder($openid,WechatPayInfo $payInfo)
    {
        $sendArray = $this->createOrderArray($payInfo);
        $model = new PayModel();
        $model->createPayInfo($openid,$sendArray);

        $res = $this->preOrderSend($sendArray);

        $model->updatePayInfo($payInfo->out_trade_no,$res);
        return $res;
    }

    /***
     * 生成QRCode Url
     * @param $productId
     * @return string
     */
    public function createPayQRcodeUrl($productId)
    {
        $params = array(
            'appid'=>$this->appId,
            'mch_id'=>$this->mchId,
            'nonce_str'=>$this->getRandomStr(),
            'product_id'=>$productId,
            'time_stamp'=>time()
        );
        $sign = $this->getSignStr($params);
        $params['sign'] = $sign;

        foreach($params as $k=>$v)
        {
            $paramArray[] = $k.'='.$v;
        }
        $parmsStr = implode('&',$paramArray);
        $url = self::PAY_QRCODE_PREFIX.$parmsStr;
        return $url;
    }

    /***
     * 为扫码支付回调生成 结果
     * @param $info
     * @return string
     */
    public function createNativeRetrunXml($info)
    {
        $xmlArray = array(
            'return_code' => $info['return_code'],
            'result_code' => $info['result_code'],
            'prepay_id' => $info['prepay_id'],
            'appid' => $info['appid'],
            'mch_id' => $info['mch_id'],
            'nonce_str'=>$info['nonce_str'],
        );
        $sign = $this->getSignStr($xmlArray);
        $xmlArray['sign'] = $sign;
        $xml = $this->xml_encode($xmlArray);;
        YafDebug::log('Native return xml is '.$xml);
        return $xml;
    }

    public function getPayResult($holloPayId,$transactionId='')
    {
        $params = array(
            'appid' =>$this->appId,
            'mch_id' =>$this->mchId,
            'nonce_str'=>$this->getRandomStr(),
        );

        if(!empty($transactionId))
        {
            $params['transaction_id'] = $transactionId;
        }
        else
        {
            $params['out_trade_no'] = $holloPayId;
        }
        $params['sign'] = $this->getSignStr($params);

        $xmldata=  $this->xml_encode($params);
        $result = $this->http_post(self::API_URL_PREFIX.self::PAY_RESULT_QUERY,$xmldata);
        if($result)
        {
            $array = (array)simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
            YafDebug::log('send pay result is ::'.json_encode($array));

            if($array['return_code'] == 'SUCCESS' && $array['result_code'] == 'SUCCESS')
            {
                return $array;
            }
            elseif($array[''])
            {

            }
            $this->errcode = $array[''];
            return false;
        }
        return false;
    }

    public function checkPackge($packge)
    {
        $info = $packge;
        $revSign = $info['sign'];
        unset($info['sign']);
        $oriSign = $this->getSignStr($info);
        YafDebug::log('rev sing :'.$revSign.' ,oriSign is :'.$oriSign);
        return $revSign === $oriSign;
    }
}