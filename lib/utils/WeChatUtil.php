<?php
/**
 *	微信公众平台PHP-SDK, 官方API部分
 *  @author  dodge <dodgepudding@gmail.com>
 *  @link https://github.com/dodgepudding/wechat-php-sdk
 *  @version 1.2
 *  usage:
 *   $options = array(
 *			'token'=>'tokenaccesskey', //填写你设定的key
 *			'appid'=>'wxdk1234567890', //填写高级调用功能的app id
 *			'appsecret'=>'xxxxxxxxxxxxxxxxxxx', //填写高级调用功能的密钥
 *		);
 *	 $weObj = new Wechat($options);
 *   $weObj->valid();
 *   $type = $weObj->getRev()->getRevType();
 *   switch($type) {
 *   		case Wechat::MSGTYPE_TEXT:
 *   			$weObj->text("hello, I'm wechat")->reply();
 *   			exit;
 *   			break;
 *   		case Wechat::MSGTYPE_EVENT:
 *   			....
 *   			break;
 *   		case Wechat::MSGTYPE_IMAGE:
 *   			...
 *   			break;
 *   		default:
 *   			$weObj->text("help info")->reply();
 *   }
 *   //获取菜单操作:
 *   $menu = $weObj->getMenu();
 *   //设置菜单
 *   $newmenu =  array(
 *   		"button"=>
 *   			array(
 *   				array('type'=>'click','name'=>'最新消息','key'=>'MENU_KEY_NEWS'),
 *   				array('type'=>'view','name'=>'我要搜索','url'=>'http://www.baidu.com'),
 *   				)
 *  		);
 *   $result = $weObj->createMenu($newmenu);
 */
define('WECHAT_AUTH_STATE',99);

class WeChatUtil
{
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_LOCATION = 'location';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_EVENT = 'event';//qy
    const MSGTYPE_MUSIC = 'music';
    const MSGTYPE_NEWS = 'news';
    const MSGTYPE_FILE = 'file';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_VIDEO = 'video';

/////////////////qy
    const MESSAGE_SEND = '/message/send?access_token=';
    const MEDIA_UPLOAD = '/media/upload?access_token=';

///////////////////
    const RED_PACK_URL_PREFIX = 'https://api.mch.weixin.qq.com';
    const RED_PACK_SEND = '/mmpaymkttransfers/sendredpack';
    const FAKEID_2_OPENID = '/user/convert_openid?access_token=';
///////////////////
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const AUTH_URL = '/token?grant_type=client_credential&';
    const MENU_CREATE_URL = '/menu/create?';
    const MENU_GET_URL = '/menu/get?';
    const MENU_DELETE_URL = '/menu/delete?';

    const MEDIA_GET_URL = '/media/get?';
    const QRCODE_CREATE_URL='/qrcode/create?';
    const QR_SCENE = 0;
    const QR_LIMIT_SCENE = 1;
    const QRCODE_IMG_URL='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';
    const USER_GET_URL='/user/get?';
    const USER_INFO_URL='/user/info?';
    const GROUP_GET_URL='/groups/get?';
    const GROUP_CREATE_URL='/groups/create?';
    const GROUP_UPDATE_URL='/groups/update?';
    const GROUP_MEMBER_UPDATE_URL='/groups/members/update?';
    const GROUP_GET_ID='/groups/getid?';
    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
    const OAUTH_AUTHORIZE_URL = '/authorize?';
    const OAUTH_TOKEN_PREFIX = 'https://api.weixin.qq.com/sns/oauth2';
    const OAUTH_TOKEN_SNS = 'https://api.weixin.qq.com/sns';

    const API_BASE_URL_PREFIX = 'https://api.weixin.qq.com'; //以下API接口URL需要使用此前缀
    const OAUTH_TOKEN_URL = '/sns/oauth2/access_token?';
    const OAUTH_REFRESH_URL = '/sns/oauth2/refresh_token?';
    const OAUTH_USERINFO_URL = '/sns/userinfo?';
    const OAUTH_AUTH_URL = '/sns/auth?';
    const GET_TICKET_URL = '/ticket/getticket?';

    private $token;
    private $appid;
    private $appsecret;
    private $encrypt_type;
    private $access_token;
    private $user_token;
    private $_msg;
    private $_newsMsg;
    private $_receive;
    private $_encryptMsg;
    private $postxml;
    public $debug = true;
    public $errCode = 40001;
    public $errMsg = "err msg default";
    private $_logcallback;
    public $agentId;
    public $aesKey;
    private $urlNonce;
    private $urlTimeStamp;
    private $jsapi_ticket;

    public function __construct()
    {
        $config = Yaf_Registry::get('config');
        $corpInfo = WeChatEnv::getCorpInfo();

        $options = array(
            'debug'=>$config['app']['debug'],
            'appid' => $corpInfo->appId,
            'appsec'=>$corpInfo->appSec,
            'aeskey'=>$corpInfo->appAes,
            'token'=>$corpInfo->token,
        );

        $this->appid = isset($options['appid'])?$options['appid']:'';
        $this->debug = isset($options['debug'])?$options['debug']:false;
        $this->appsecret = isset($options['appsec'])?$options['appsec']:'';
        $this->token = isset($options['token'])?$options['token']:'';
        $this->aesKey = isset($options['aeskey'])?$options['aeskey']:'';

    }

    private function getAccessToken()
    {
        if(!empty($this->access_token))
        {
            return $this->access_token;
        }

        $accessToken = $this->getTokenFromRedis(HaloRedis::WECHAT_TOKEN_TAG.$this->appid.$this->appsecret);
        if(empty($accessToken))
        {
            $accessToken = $this->checkAuth();
        }
        $this->access_token = $accessToken;
        return $accessToken;
    }

    /**
     * For weixin server validation
     */
    private function checkSignature()
    {
        $signature = isset($_GET["signature"])?$_GET["signature"]:'';
        $signature = isset($_GET["msg_signature"])?$_GET["msg_signature"]:$signature; //如果存在加密验证则用加密验证段
        $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce,$str);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * For weixin server validation
     * @param bool $return 是否返回
     */
    public function valid($return=false)
    {
        $encryptStr="";
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $postStr = file_get_contents("php://input");
            $array = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->encrypt_type = isset($_GET["encrypt_type"]) ? $_GET["encrypt_type"]: '';
            if ($this->encrypt_type == 'aes')
            { //aes加密
                $this->log($postStr);
                $encryptStr = $array['Encrypt'];
                $pc = new Prpcrypt($this->aesKey);
                $array = $pc->decrypt($encryptStr,$this->appid);
                if (!isset($array[0]) || ($array[0] != 0))
                {
                    if (!$return) {
                        die('decrypt error!');
                    } else {
                        return false;
                    }
                }
                $this->postxml = $array[1];
                if (!$this->appid)
                    $this->appid = $array[2];//为了没有appid的订阅号。
            }
            else
            {
                $this->postxml = $postStr;
            }
        }
        else if (isset($_GET["echostr"]))
        {
            $echoStr = $_GET["echostr"];
            if ($return)
            {
                if ($this->checkSignature())
                    return $echoStr;
                else
                    return false;
            } else
            {
                if ($this->checkSignature())
                    die($echoStr);
                else
                    die('no access');
            }
        }

        if (!$this->checkSignature($encryptStr))
        {
            if ($return)
                return false;
            else
                die('no access');
        }
        return true;

    }

    /**
     * 设置发送消息
     * @param array $msg 消息数组
     * @param bool $append 是否在原消息数组追加
     */
    public function Message($msg = '',$append = false){
        if (is_null($msg)) {
            $this->_msg =array();
        }elseif (is_array($msg)) {
            if ($append)
                $this->_msg = array_merge($this->_msg,$msg);
            else
                $this->_msg = $msg;
            return $this->_msg;
        } else {
            return $this->_msg;
        }
    }

    private function log($log){
        if ($this->debug && $this->_logcallback) {
            if (is_array($log)) $log = print_r($log,true);
            return call_user_func($this->_logcallback,$log);
        }
    }

    /**
     * 获取微信服务器发来的信息
     */
    public function getRev()
    {
        if ($this->_receive) return $this;
        $postStr = !empty($this->postxml)?$this->postxml:file_get_contents("php://input");
        //兼顾使用明文又不想调用valid()方法的情况
        $this->log($postStr);
        if (!empty($postStr))
        {
            $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $this;
    }

    public function getRevDecrypt()
    {
        return $this->_encryptMsg;
    }

    /**
     * 获取微信服务器发来的信息
     */
    public function getRevData()
    {
        return $this->_receive;
    }

    /**
     * 获取消息发送者
     */
    public function getRevFrom() {
        if (isset($this->_receive['FromUserName']))
            return $this->_receive['FromUserName'];
        else
            return false;
    }

    /**
     * 获取消息接受者
     */
    public function getRevTo() {
        if (isset($this->_receive['ToUserName']))
            return $this->_receive['ToUserName'];
        else
            return false;
    }

    /**
     * 获取接收消息的类型
     */
    public function getRevType() {
        if (isset($this->_receive['MsgType']))
            return $this->_receive['MsgType'];
        else
            return false;
    }

    /**
     * 获取消息ID
     */
    public function getRevID() {
        if (isset($this->_receive['MsgId']))
            return $this->_receive['MsgId'];
        else
            return false;
    }

    /**
     * 获取消息发送时间
     */
    public function getRevCtime() {
        if (isset($this->_receive['CreateTime']))
            return $this->_receive['CreateTime'];
        else
            return false;
    }

    /**
     * 获取接收消息内容正文
     */
    public function getRevContent(){
        if (isset($this->_receive['Content']))
            return $this->_receive['Content'];
        else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容，需申请开通
        return $this->_receive['Recognition'];
        else
            return false;
    }

    public function getRevMediaId() {
        if (isset($this->_receive['MediaId']))
            return $this->_receive['MediaId'];
        else
            return false;
    }

    /**
     * 获取接收消息图片
     */
    public function getRevPic(){
        if (isset($this->_receive['PicUrl']))
            return $this->_receive['PicUrl'];
        else
            return false;
    }

    /**
     * 获取接收消息链接
     */
    public function getRevLink(){
        if (isset($this->_receive['Url'])){
            return array(
                'url'=>$this->_receive['Url'],
                'title'=>$this->_receive['Title'],
                'description'=>$this->_receive['Description']
            );
        } else
            return false;
    }

    /**
     * 获取接收地理位置
     */
    public function getRevGeo(){
        if (isset($this->_receive['Location_X'])){
            return array(
                'x'=>$this->_receive['Location_X'],
                'y'=>$this->_receive['Location_Y'],
                'scale'=>$this->_receive['Scale'],
                'label'=>$this->_receive['Label']
            );
        } else
            return false;
    }

    /**
     * 获取上报地理位置事件
     */
    public function getRevEventGeo(){
        if (isset($this->_receive['Latitude'])){
            return array(
                'x'=>$this->_receive['Latitude'],
                'y'=>$this->_receive['Longitude'],
                'precision'=>$this->_receive['Precision'],
            );
        } else
            return false;
    }

    /**
     * 获取接收地理位置
     */
    public function getRevLocation(){
        if ($this->_receive['Event'] == 'LOCATION')
        {
            return array(
                'time'=>$this->_receive['CreateTime'],
                'lat'=>$this->_receive['Latitude'],
                'lng'=>$this->_receive['Longitude'],
                'pre'=>$this->_receive['Precision']
            );
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取接收事件推送
     */
    public function getRevEvent(){
        if (isset($this->_receive['Event'])){
            return array(
                'event'=>$this->_receive['Event'],
                'key'=>$this->_receive['EventKey'],
            );
        } else
            return false;
    }

    public function getSuitePush()
    {
        return $this->_receive;
    }


    /**
     * 获取接收语言推送
     */
    public function getRevVoice(){
        if (isset($this->_receive['MediaId'])){
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'format'=>$this->_receive['Format'],
            );
        } else
            return false;
    }

    /**
     * 获取接收视频推送
     */

    public function getRevVideo(){
        if (isset($this->_receive['MediaId'])){
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'thumbmediaid'=>$this->_receive['ThumbMediaId']
            );
        } else
            return false;
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
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
    public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
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

    /**
     * 设置回复音乐
     * @param string $title
     * @param string $desc
     * @param string $musicurl
     * @param string $hgmusicurl
     */
    public function music($title,$desc,$musicurl,$hgmusicurl='') {
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'CreateTime'=>time(),
            'MsgType'=>self::MSGTYPE_MUSIC,
            'Music'=>array(
                'Title'=>$title,
                'Description'=>$desc,
                'MusicUrl'=>$musicurl,
                'HQMusicUrl'=>$hgmusicurl
            ),
//            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复图文
     * @param array $newsData
     * 数组结构:
     *  array(
     *  	[0]=>array(
     *  		'Title'=>'msg title',
     *  		'Description'=>'summary text',
     *  		'PicUrl'=>'http://www.domain.com/1.jpg',
     *  		'Url'=>'http://www.domain.com/1.html'
     *  	),
     *  	[1]=>....
     *  )
     */
    public function news($newsData=array())
    {
        $count = count($newsData);

        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_NEWS,
            'CreateTime'=>time(),
            'ArticleCount'=>$count,
            'Articles'=>$newsData,
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复消息
     * Examle: $obj->text('hello')->reply();
     * @param string $text
     */

    public function text($text='')
    {
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_TEXT,
            'Content'=>$text,
            'CreateTime'=>time(),
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复消息
     * Example: $obj->image('media_id')->reply();
     * @param string $mediaid
     */
    public function image($mediaid='')
    {
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_IMAGE,
            'Image'=>array('MediaId'=>$mediaid),
            'CreateTime'=>time(),
        );
        $this->Message($msg);
        return $this;
    }

    /**
     *
     * 回复微信服务器, 此函数支持链式操作
     * @example $this->text('msg tips')->reply();
     * @param string $msg 要发送的信息, 默认取$this->_msg
     * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
     */
    public function reply($msg=array(),$return = false)
    {
        if (empty($msg))
            $msg = $this->_msg;
        YafDebug::log('reply array msg: '.json_encode($msg));
        $xmldata=  $this->xml_encode($msg);
        YafDebug::log('reply msg: '.$xmldata);

        if ($this->encrypt_type == 'aes')
        { //如果来源消息为加密方式
            $pc = new Prpcrypt($this->encodingAesKey);
            $array = $pc->encrypt($xmldata, $this->appid);
            $ret = $array[0];
            if ($ret != 0) {
                $this->log('encrypt err!');
                return false;
            }
            $timestamp = time();
            $nonce = rand(77,999)*rand(605,888)*rand(11,99);
            $encrypt = $array[1];
            $tmpArr = array($this->token, $timestamp, $nonce,$encrypt);//比普通公众平台多了一个加密的密文
            sort($tmpArr, SORT_STRING);
            $signature = implode($tmpArr);
            $signature = sha1($signature);
            $xmldata = $this->generate($encrypt, $signature, $timestamp, $nonce);
            YafDebug::log('encrypt reply msg: '.$xmldata);
        }
        if ($return)
            return $xmldata;
        else
            echo $xmldata;


    }

    /**
     * xml格式加密，仅请求为加密方式时再用
     */
    private function generate($encrypt, $signature, $timestamp, $nonce)
    {
        //格式化加密信息
        $format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }

    /**
     * GET 请求
     * @param string $url
     */
    private function http_get($url)
    {
        YafDebug::log('get url is '.$url);
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    private function http_post($url,$param){
        $oCurl = curl_init();
        YafDebug::log('post url is '.$url);
        YafDebug::log('post param is '.json_encode($param));
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param))
        {
            $strPOST = $param;
        }
        else
        {
            $strPOST =  utf8_json_encode($param);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($strPOST))
        );

        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        YafDebug::log('post res is '.$sContent);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @return string content
     */
    private function file_post($url,$param){
        $oCurl = curl_init();
        YafDebug::log('url is '.$url);
        YafDebug::log('param is '.json_encode($param));
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$param);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * 通用auth验证方法，暂时仅用于菜单更新操作
     * @param string $appid
     * @param string $appsecret
     */
    public function checkAuth($appid='',$appsecret=''){
        if (!$appid || !$appsecret)
        {
            $appid = $this->appid;
            $appsecret = $this->appsecret;
        }
        $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                YafDebug::log('error::::::::');
                YafDebug::log($this->errCode);
                YafDebug::log($this->errMsg);
                return false;
            }
            YafDebug::log('check auth::::');
            YafDebug::log($json);
            $token = $json['access_token'];
            $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
            $this->access_token = $token;
            $this->setTokenToRedis(HaloRedis::WECHAT_TOKEN_TAG.$this->appid.$this->appsecret,$token,$expire);
            return $token;
        }
        return false;
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    static function json_encode($arr) {
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }


    /**
     * 根据媒体文件ID获取媒体文件
     * @param string $media_id 媒体文件id
     * @return raw data
     */
    public function getMedia($media_id){

        if (!$this->getAccessToken())
        {
            return false;
        }

        $result = $this->http_get(self::API_URL_PREFIX.self::MEDIA_GET_URL.'access_token='.$this->access_token.'&media_id='.$media_id);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                YafDebug::log('get media error : '. json_encode($json));
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $result;
        }
        return false;
    }

    /**
     * 获取关注者详细信息
     * @param string $openid
     * @return array {subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getUserInfo($openid){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::USER_INFO_URL.'access_token='.$this->access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }


    /**
     * oauth 授权跳转接口
     * @param string $callback 回调URI
     * @return string
     */
    public function getOauthRedirect($callback, $state='',$scope='snsapi_base')
    {
        YafDebug::log('!!!!!!!!!!!getOauthRedirect ::'.self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect');
        return self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
    }

    public function getUserAuthCode($callback, $state='',$scope='snsapi_base')
    {
        YafDebug::log('!!!!!!!!!!!getUserAuthCode ::callBack = '.$callback.'state ='.$state);
        $url = self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
        YafDebug::log('getUserAuthCode :: url : '.$url);

        $result = $this->http_get($url);
//        YafDebug::log('getUserAuthCode :: res: '.$result);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || $json['errcode']>0) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return true;
    }

    public function getUserIdWithCode($code='')
    {
        $code = isset($_GET['code'])?$_GET['code']:'';
        if (!$code) return false;
        $result = $this->http_get(self::OAUTH_TOKEN_PREFIX.self::OAUTH_TOKEN_URL.'appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || $json['errcode']>0) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }

    /**
     * 通过code获取Access Token
     * @return array {access_token,expires_in,refresh_token,openid,scope}
     */
    public function getOauthAccessToken()
    {
        $code = isset($_GET['code'])?$_GET['code']:'';
        if (!$code) return false;
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_TOKEN_URL.'appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode']))
            {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }

    /**
     * 刷新access token并续期
     * @param string $refresh_token
     * @return boolean|mixed
     */
    public function getOauthRefreshToken($refresh_token){
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_REFRESH_URL.'appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }

    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return array {openid,nickname,sex,province,city,country,headimgurl,privilege}
     */
    public function getOauthUserinfo($access_token,$openid){
        $result = $this->http_get(self::OAUTH_TOKEN_SNS.self::OAUTH_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || $json['errcode']>0) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 检验授权凭证是否有效
     * @param string $access_token
     * @param string $openid
     * @return boolean 是否有效
     */
    public function getOauthAuth($access_token,$openid)
    {
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_AUTH_URL.'access_token='.$access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            } else
                if ($json['errcode']==0) return true;
        }
        return false;
    }


   public static  function isMobileMicroMessenger()
   {
       $result = array();
       $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
       if(strstr($user_agent, 'AppleWebKit') && strstr($user_agent, 'Mobile'))
       {
           if(strpos($user_agent, 'iPhone') !== FALSE || (strstr($user_agent,'iPod')!== FALSE) || (strstr($user_agent,'iPad')!== FALSE))
           {
               $result['platform'] = 'iOS';
//               if(!strstr($user_agent, 'Safari'))
//               {
//                   $result['isMMM'] = true;
//               }
           }

           if(strstr($user_agent, 'MicroMessenger/'))
           {
               $result['isMMM'] = true;
           }
           else
           {
               $result['isMMM'] = false;
           }
       }
       else
       {
           $result['isMMM'] = false;
       }
       return $result;
   }

    public function getTokenFromRedis($key)
    {
//        YafDebug::log('==========get redis=========== begin');
        $redis = WeChatEnv::getRedis();
//        YafDebug::log('==========get redis=========== end');
        if($key==null)
        {
            $key = HaloRedis::WECHAT_TOKEN_TAG;
        }
        return $redis->get($key);
//        return false;

    }

    public function setTokenToRedis($key,$token,$expire)
    {
        $redis = WeChatEnv::getRedis();
        if($redis)
        {
            if($key==null)
            {
                $key = HaloRedis::WECHAT_TOKEN_TAG;
            }
            $redis->set($key,$token,0,0,$expire);
            YafDebug::log('$redis set done');
        }
        else
        {
            YafDebug::log('$redis is null');
        }

    }
    //设置jsticket
    public function getJsTicketFromRedis($key)
    {
//        YafDebug::log('==========get redis=========== begin');
        $redis = WeChatEnv::getRedis();
//        YafDebug::log('==========get redis=========== end');
        if($key==null)
        {
            $key = HaloRedis::WECHAT_JS_API_TICKET;
        }
        return $redis->get($key);
    }

    public function setJsTicketToRedis($key,$ticket,$expire)
    {
        $redis = WeChatEnv::getRedis();
        if($redis)
        {
            if($key==null)
            {
                $key = HaloRedis::WECHAT_JS_API_TICKET;
            }
            $redis->set($key,$ticket,0,0,$expire);
//            YafDebug::log('$redis set done');
        }
        else
        {
//            YafDebug::log('$redis is null');
        }
    }

//企业微信

    private function sendPost($url,$data)
    {
        $result = $this->http_post($url, $data);
        if ($result)
        {
            $json = json_decode($result,true);
            YafDebug::log('post recv is '.$result);
            if (!$json || $json['errcode']>0) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $json;
            }
            return $json;
        }
        return false;
    }

    private function sendGet($url)
    {
        $result = $this->http_get($url);
        if ($result)
        {
            $json = json_decode($result,true);
            YafDebug::log('post recv is '.$result);
            if (!$json || $json['errcode']>0) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $json;
            }
            return $json;
        }
        return false;
    }


    public function sendCunstomNewsReply($content,$userIds='@all',$partyIds=null)
    {
//        MESSAGE_SEND
        if (!$this->getAccessToken())
        {
            return false;
        }

        $msg = array();
        $hasToAllStatus=false;//touser 是否为@all 是忽略toparty

        if($userIds != null)
        {
            if(is_array($userIds))
            {
                $msg['touser'] = implode('|',$userIds);
            }
            else if ($userIds === '@all')
            {
                $msg['touser'] = '@all';
                $hasToAllStatus=true;
            }
            else
            {
                $msg['touser'] = $userIds;
            }
        }

        if($partyIds != null)
        {
            if(!$hasToAllStatus)
            {
                $msg['toparty'] = implode('|',$partyIds);
                $hasDes = true;
            }
        }
        $msg['msgtype'] = 'news';
        $msg['agentid'] = $this->agentId;
        $msg['safe'] = 0;


        $cardContent = array();
        $cardContent['articles'] = array();

        if(isset($content['title']))
        {
            $content['title'] = urldecode(urlencode($content['title']));
            $content['description'] = urldecode(urlencode($content['description']));
            if(isset($content['content']))
            {
                $content['content'] = urldecode(urlencode($content['content']));
            }

            $cardContent['articles'][] = $content;
        }
        else
        {
            $cardContent['articles'] = $content;
        }
        $msg['news'] = $cardContent;

        return $this->sendCustomReply($msg);
    }


    public function sendCunstomMPNewsReply($content,$userIds='@all',$partyIds=null,$safe = 0)
    {
//        MESSAGE_SEND
        if (!$this->getAccessToken())
        {
            return false;
        }

        $msg = array();
        $hasToAllStatus=false;//touser 是否为@all 是忽略toparty

        if($userIds != null)
        {
            if(is_array($userIds))
            {
                $msg['touser'] = implode('|',$userIds);
            }
            else if ($userIds === '@all')
            {
                $msg['touser'] = '@all';
                $hasToAllStatus=true;
            }
            else
            {
                $msg['touser'] = $userIds;
            }
        }

        if($partyIds != null)
        {
            if(!$hasToAllStatus)
            {
                $msg['toparty'] = implode('|',$partyIds);
                $hasDes = true;
            }
        }
        $msg['msgtype'] = 'mpnews';
        $msg['agentid'] = $this->agentId;
        $msg['safe'] = $safe;


        $cardContent = array();
        $cardContent['articles'] = array();

        if(isset($content['title']))
        {

            $cardContent['articles'][] = $content;
        }
        else
        {
            $cardContent['articles'] = $content;
        }
        $msg['mpnews'] = $cardContent;

        return $this->sendCustomReply($msg);
    }


    public static function createMPNewsItem($title,$mediaId,$content,$author='',$desc='',$url='',$showCover=0)
    {
        return array(
           "title"=>urldecode(urlencode($title)),
           "thumb_media_id"=>$mediaId,
           "author"=>urldecode(urlencode($author)),
           "content_source_url"=>$url,
           "content"=>urldecode(urlencode($content)),
           "digest"=>urldecode(urlencode($desc)),
           "show_cover_pic"=>$showCover,
        );
    }

    /**
     * @param $MEDIA_ID
     * @param string $userIds
     * @param null $partyIds
     * @param int $safe
     * @return bool|mixed
     */
    public function sendCunstomFileReply($MEDIA_ID,$userIds='@all',$partyIds=null,$safe=0)
    {
//        MESSAGE_SEND
        if (!$this->getAccessToken())
        {
            return false;
        }
        $msg = array();
        $hasToAllStatus=false;//touser 是否为@all 是忽略toparty

        if(isset($userIds))
        {
            if(is_array($userIds))
            {
                $msg['touser'] = implode('|',$userIds);
            }
            else if ($userIds === '@all')
            {
                $msg['touser'] = '@all';
                $hasToAllStatus=true;
            }
            else
            {
                $msg['touser'] = $userIds;
            }
        }

        if(isset($partIds))
        {
            if(!$hasToAllStatus)
            {
                $msg['toparty'] = implode('|',$partIds);
                $hasDes = true;
            }
        }

        if(!isset($msg['touser']) && !isset($msg['toparty']))
        {
            YafDebug::log('error:touser and toparty are null;/n');
            return false;
//            $msg['touser'] = $this->getRevFrom();
        }

        $msg['msgtype']='file';
        $msg['agentid']=$this->agentId;
        $msg['file']['media_id']=$MEDIA_ID;
        $msg['safe']=$safe;  //News 消息指定 safe 为 1 时会报错，错误码为 2003001(安全消息 无法转发)
        return $this->sendCustomReply($msg);
    }


    /***
     * 回复应用消息text
     * @param $content
     * @param $userIds
     * @param $partIds
     * @param $safe
     * @return bool|mixed
     */
    public function sendCunstomTextReply($content,$userIds=null,$partIds=null,$safe=0)
    {
//        YafDebug::log('sendCunstomTextReply :: uid is'.$userIds.'content :'.$content);
        if (!$this->getAccessToken())
        {
            YafDebug::log('error');
            return false;
        }
        $msg = array();
        $hasDes = false;

        if(isset($userIds) && !empty($userIds))
        {
            if(is_array($userIds))
            {
                $msg['touser'] = implode('|',$userIds);
                $hasDes = true;
            }
            else if ($userIds === '@all')
            {
                $msg['touser'] = '@all';
                $hasDes = true;
            }
            else
            {
                $msg['touser'] = $userIds;
                $hasDes = true;
            }
        }

        if(isset($partIds) && !empty($partIds))
        {
            if(is_array($partIds))
            {
                $msg['toparty'] = implode('|',$partIds);
            }
            else
            {
                $msg['toparty'] = $partIds;
            }
            $hasDes = true;
        }

        if(!$hasDes)
        {
            $msg['touser'] = $this->getRevFrom();
        }
        $msg['msgtype'] = self::MSGTYPE_TEXT;
        $msg['agentid'] = $this->agentId;
        $msg['text'] = array('content'=>$content);
        $msg['safe'] = $safe;
        YafDebug::log('sendCunstomTextReply is==========');
        YafDebug::log($msg);
        return $this->sendCustomReply($msg);
    }

    /***
     * 发送图片信息
     * @param $content
     * @param null $userIds
     * @param null $partIds
     * @param int $safe
     * @return bool|mixed
     */
    public function sendCunstomImgReply($mediaId,$userIds=null,$partIds=null,$safe=0)
    {
//        YafDebug::log('sendCunstomImgReply :: uid is'.$userIds.'meidaId :'.$mediaId);
        if (!$this->getAccessToken())
        {
            YafDebug::log('error');
            return false;
        }
        $msg = array();
        $hasDes = false;

        if(isset($userIds) && !empty($userIds))
        {
            if(is_array($userIds))
            {
                $msg['touser'] = implode('|',$userIds);
                $hasDes = true;
            }
            else if ($userIds === '@all')
            {
                $msg['touser'] = '@all';
                $hasDes = true;
            }
            else
            {
                $msg['touser'] = $userIds;
                $hasDes = true;
            }
        }

        if(isset($partIds) && !empty($partIds))
        {
            if(is_array($partIds))
            {
                $msg['toparty'] = implode('|',$partIds);
            }
            else
            {
                $msg['toparty'] = $partIds;
            }
            $hasDes = true;
        }

        if(!$hasDes)
        {
            $msg['touser'] = $this->getRevFrom();
        }

        $msg['msgtype'] = self::MSGTYPE_IMAGE;
        $msg['agentid'] = $this->agentId;
        $msg['image'] = array('media_id'=>$mediaId);
        $msg['safe'] = $safe;
        YafDebug::log('sendCunstomTextReply is==========');
        YafDebug::log($msg);
        return $this->sendCustomReply($msg);
    }

    private function sendCustomReply($data)
    {
        $url = self::API_URL_PREFIX.self::MESSAGE_SEND. $this->access_token;
        if(is_array($data))
        {
            $data = self::json_encode($data);
        }
        return $this->sendPost($url,$data);
    }

    /**
     *
     * 回复微信服务器, 此函数支持链式操作
     * @example $this->text('msg tips')->reply();
     * @param string $msg 要发送的信息, 默认取$this->_msg
     * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
     */
    public function replyWithJson($msg=array(),$return = true)
    {
        if (empty($msg))
            $msg = $this->_msg;

        $json=  $this->json_encode($msg);
        YafDebug::log('reply msg: '.$json);
        if ($return)
            return $json;
        else
            echo $json;
    }


    public function upLoadMeida($path,$type='image')
    {
        if (!$this->getAccessToken())
        {
            YafDebug::log('error');
            return false;
        }
        $str = pathinfo($path,PATHINFO_BASENAME);
        $media =  new CURLFile($path);
//        $fileData = array('media'=>"@".$path.';filename='.$str);
        $fileData = array('media'=>$media,'filename='.$str);
        $url = $this::API_URL_PREFIX.$this::MEDIA_UPLOAD.$this->access_token.'&type='.$type;
        $result = $this->file_post($url,$fileData);
        if ($result)
        {
            $json = json_decode($result,true);
            YafDebug::log($result);
            if (!$json || $json['errcode']>0) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

/*user***************************************************************************************************/


/*menu***************************************************************************************************/
    /**
     * 创建菜单
     * @param array $data 菜单数组数据
     */
    public function createMenu($data)
    {
        if (!$this->getAccessToken())
        {
            YafDebug::log('error');
            return false;
        }
        $url = self::API_URL_PREFIX.self::MENU_CREATE_URL.'access_token='.$this->access_token;
        return $this->sendPost($url,$data);
    }

    /**
     * 获取菜单
     * @return array('menu'=>array(....s))
     */
    public function getMenu()
    {
        if (!$this->getAccessToken())
        {
            return false;
        }
        $url = self::API_URL_PREFIX.self::MENU_GET_URL.'access_token='.$this->access_token;
        $result = $this->http_get($url);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 删除菜单
     * @return boolean
     */
    public function deleteMenu()
    {
        if (!$this->getAccessToken())
        {
            return false;
        }
        $url = self::API_URL_PREFIX.self::MENU_DELETE_URL.'access_token='.$this->access_token;
        $result = $this->http_get($url);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || $json['errcode']>0)
            {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }

            return true;
        }
        return false;
    }

/*menu***************************************************************************************************/

    /***
     * 检查fake id
     * @param array $exception
     */
    public static  function checkWechatAuth($exception = array('/wechat','/api/wechat','{{','api/data/flush','api/qrcode/get'))
    {
        $session = Yaf_Session::getInstance();
        $fakeId = WeChatEnv::getOpenId();
        $token_time =  $session->offsetGet('token_time') ? $session->offsetGet('token_time'):0;
        if($token_time < time() - 3600)
        {
            $fakeId = false;
        }

        $need = true;
        foreach($exception as $r)
        {
            if(stripos($_SERVER['REQUEST_URI'],$r) !== false)
            {
                $need = false;
                break;
            }
        }

        YafDebug::log('Auth::::::::open id is '.$fakeId.' request is '.$_SERVER['REQUEST_URI']);
//        YafDebug::log('Auth:::::::: check res is '.$need);
        if(!$fakeId && $need)
        {
            $wechat = new WeChatUtil();
            //授权回来获取用户信息
            if(isset($_GET['state']) && $_GET['state'] == WECHAT_AUTH_STATE)
            {
                //获取用户授权信息
                $jsonInfo= $wechat->getOauthAccessToken();
                if($jsonInfo)
                {
                    $fakeId = $jsonInfo['openid'];
                    YafDebug::log('Auth:::get open id :'.$fakeId);
                    WeChatEnv::setOpenId($fakeId);
                    $session->offsetSet('user_token',$jsonInfo['user_token']);
                    $session->offsetSet('token_time',time());

//                    $model = new UserAdminModel(WeChatEnv::getCorpDbId());
//                    $uid = $model->getUidByFakeId($fakeId);
//                    if($uid == null)
//                    {
//                        $uid = $model->createUser($fakeId);
//                    }
//                    YafDebug::log('Auth:::get uid is :'.$uid);
//                    $user = $wechat->getUserInfo($fakeId);
                }
                else
                {
                    YafDebug::log('Auth:::get open id error:');
                    if(stripos($_SERVER['REQUEST_URI'],'authorize')===false)
                    {
//                        die();
                    }
                }
            }
            else//准备去授权
            {
                YafDebug::log('Auth::::::::prepare');
                $reUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                YafDebug::log('Auth:::get open id url :'.$reUrl);
                $url =  $wechat->getOauthRedirect($reUrl, WECHAT_AUTH_STATE);
                header('Location:'.$url);
                haloDie();
            }
        }
    }

    //微信jsAPI


    /**
     * 获取JsApi使用签名
     * @param string $url 网页的URL，自动处理#及其后面部分
     * @param string $timestamp 当前时间戳 (为空则自动生成)
     * @param string $noncestr 随机串 (为空则自动生成)
     * @param string $appid 用于多个appid时使用,可空
     * @return array|bool 返回签名字串
     */
    public function getJsSign($url, $timestamp=0, $noncestr='', $appid=''){
        if (!$this->jsapi_ticket && !$this->getJsApiTicket($appid) || !$url) return false;
        if (!$timestamp)
            $timestamp = time();
        if (!$noncestr)
            $noncestr = $this->generateNonceStr();
        $ret = strpos($url,'#');
        if ($ret)
            $url = substr($url,0,$ret);
        $url = trim($url);
        if (empty($url))
            return false;
        $arrdata = array("timestamp" => $timestamp, "noncestr" => $noncestr, "url" => $url, "jsapi_ticket" => $this->jsapi_ticket);
        $sign = $this->getSignature($arrdata);
        if (!$sign)
            return false;
        $signPackage = array(
            "appid"     => $this->appid,
            "noncestr"  => $noncestr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $sign
        );
        return $signPackage;
    }

    /**
     * 生成随机字串
     * @param number $length 长度，默认为16，最长为32字节
     * @return string
     */
    public function generateNonceStr($length=16){
        // 密码字符集，可任意添加你需要的字符
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for($i = 0; $i < $length; $i++)
        {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    /**
     * 获取签名
     * @param array $arrdata 签名数组
     * @param string $method 签名方法
     * @return boolean|string 签名值
     */
    public function getSignature($arrdata,$method="sha1") {
        if (!function_exists($method)) return false;
        ksort($arrdata);
        $paramstring = "";
        foreach($arrdata as $key => $value)
        {
            if(strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        YafDebug::log('getSignature : '.$paramstring);
        $Sign = $method($paramstring);
        return $Sign;
    }

    /**
     * 获取jsapi_ticket
     */
    private function getJsApiTicket($appid='')
    {
        if (!$this->access_token && !$this->checkAuth()) return false;
        if (!$appid) $appid = $this->appid;

        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $ticket = $this->getJsTicketFromRedis(HaloRedis::WECHAT_JS_API_TICKET.$appid);
        YafDebug::log('getJsTicketFromRedisis'.$ticket);
        if (empty($ticket))
        {
            $result = $this->http_get(self::API_URL_PREFIX.self::GET_TICKET_URL.'access_token='.$this->access_token.'&type=jsapi');
            if ($result)
            {
                $json = json_decode($result,true);
                if (!$json || !empty($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                $ticket = $json['ticket'];
                $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
                $this->setJsTicketToRedis(HaloRedis::WECHAT_JS_API_TICKET.$appid,$ticket,$expire);
            }

        }
        $this->jsapi_ticket = $ticket;
        return $ticket;
    }

    /**
     * 创建二维码ticket
     * @param int|string $scene_id 自定义追踪id,临时二维码只能用数值型
     * @param int $type 0:临时二维码；1:永久二维码(此时expire参数无效)；2:永久二维码(此时expire参数无效)
     * @param int $expire 临时二维码有效期，最大为1800秒
     * @return array('ticket'=>'qrcode字串','expire_seconds'=>1800,'url'=>'二维码图片解析后的地址')
     */
    public function getQRCode($scene_id,$type=0,$expire=1800){
        if (!$this->getAccessToken()) return false;
        $type = ($type && is_string($scene_id))?2:$type;
        $data = array(
            'action_name'=>$type?($type == 2?"QR_LIMIT_STR_SCENE":"QR_LIMIT_SCENE"):"QR_SCENE",
            'expire_seconds'=>$expire,
            'action_info'=>array('scene'=>($type == 2?array('scene_str'=>$scene_id):array('scene_id'=>$scene_id)))
        );
        if ($type == 1) {
            unset($data['expire_seconds']);
        }
        $result = $this->http_post(self::API_URL_PREFIX.self::QRCODE_CREATE_URL.'access_token='.$this->access_token,json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取二维码图片
     * @param string $ticket 传入由getQRCode方法生成的ticket参数
     * @return string url 返回http地址
     */
    public function getQRUrl($ticket)
    {
        return self::QRCODE_IMG_URL.urlencode($ticket);
    }

}