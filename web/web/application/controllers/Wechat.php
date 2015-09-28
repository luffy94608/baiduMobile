<?php
/**
 * Created by PhpStorm.
 * User: su
 * Date: 14-4-22
 * Time: 下午8:01
 */

class ControllerWechat extends ControllerBase
{

    const CLICK_KEY_SUBSCRIBE = 'scribe';
    const CLICK_KEY_INTRO     = 'intro';

    const MENUID_DEFAULT = 0;
    const MENUID_MY_SCHEDULE_BUS = 1;
    const MENUID_MY_FERRY_BUS = 2;
    const MENUID_MY_ROAD = 3;

    const MENUID_FREE_SCHEDULE_BUS = 4;
    const MENUID_FREE_FERRY_BUS = 5;

    const MENUID_HOLLO_WEB_SITE = 6;
    const MENUID_HOLLO_DOWNLOAD = 7;
    const MENUID_HOLLO_LOST = 8;
    const MENUID_HOLLO_QUSTION = 9;
    const MENUID_HOLLO_MY_ACCOUNT = 10;
    const MENUID_HOLLO_INTRO = 11;

//    v2 版本
    const MENUID_HOLLO_ACTIVITY=12;
    const MENUID_MY_SCHEDULE_BUS_V2=13;
    const MENUID_HOLLO_MY_ACCOUNT_V2=14;
    const MENUID_MY_FERRY_BUS_V2=15;
    const MENUID_SEARCH_BUS_PLACE_V2=16;
    const MENUID_MY_TICKET=17;
    const MENUID_MY_TRAVEL_LIST=18;
    const MENUID_MY_TRAVEL_TICKET=19;

    private  $projectId;

    /**
     * @var WeChatUtil
     */
    protected $wechat = null;

    public function init()
    {
        parent::init();
        $this->wechat = new WeChatUtil();
    }

    public function indexAction()
    {
        YafDebug::log('====wechat ===========');
        $this->wechat->valid();
        $type = $this->wechat->getRev()->getRevType();
        YafDebug::log('====messsge=====type:'.$type);
        switch($type)
        {
            case WeChatUtil::MSGTYPE_TEXT:
                $this->handleTextMsg($this->wechat);
                break;
            case WeChatUtil::MSGTYPE_EVENT:
                $this->msEvent($this->wechat);
                break;
            case WeChatUtil::MSGTYPE_IMAGE:
                break;
              case WeChatUtil::MSGTYPE_VOICE:
                break;
            default:
                break;
        }

        haloDie();
    }

    public function getAutoReplyInfoWithKey($key,$type=0)
    {
        $replyModel=new ReplyModel();
        $replyInfo=$replyModel->getAutoReplyInfoWithKey($key,$type);
        YafDebug::log('auto reply key: '.$key.' word is :'.$replyInfo);

        return $replyInfo;

    }

    /**
     * 处理用户发送的文字信息
     * @param WeChatUtil $wechatUtil
     */
    public function handleTextMsg(WeChatUtil $wechatUtil)
    {
        YafDebug::log('====messsge=====text:'.json_encode($this->wechat->getRevData()));
        $msg = $wechatUtil->getRevContent();
        if(stripos($msg,'jetluffy') === 0)
        {
            $btn = $this->getBtnCreateArray();
            $str = urldecode(utf8_json_encode($btn));
            $str2 =  str_replace('\/','/',$str);
            $wechatUtil->text($str2)->reply();
        }
        else
        {
            $res = $this->getAutoReplyInfoWithKey($msg);
        }
        if(!$res)
        {
            $res = '感谢您的留言，你可以关注“哈罗同行服务号”相关信息，或加入哈罗同行畅聊大巴 QQ1群 :421571161（满）   QQ2群： 292395214   会有意想不到的惊喜等待大家呦！！';
        }
        $wechatUtil->text($res)->reply();
    }

    public function msEvent(WeChatUtil $wechatUtil)
    {
        $event = $wechatUtil->getRevEvent();
        YafDebug::log('====event=====event:'.$event['event']);
        if($event['event'] == 'LOCATION')
        {
            $location = $this->wechat->getRevLocation();
            $fakeId = $this->wechat->getRevFrom();
            $model = new UserModel();
            $user = $model->getUser($fakeId);
            $model->updateLocation($fakeId,$user['uid'],$location['time'],$location['lat'],$location['lng'],$location['pre']);
        }
        else if ($event['event'] == 'subscribe')
        {
            YafDebug::log('subscribe');
            $fakeId = $this->wechat->getRevFrom();

            $model = new UserModel();
            $model->subscribeUser($fakeId);

            $eventKey = $event['key'];
            if($eventKey)
            {
                YafDebug::log('event key: '.$eventKey);
                $qrEventKey = 'qrscene_';
                $pos = stripos($eventKey,$qrEventKey);
                //qrcode
                if($pos !== false)
                {
                    $qrParam = substr($eventKey,strlen($qrEventKey),strlen($eventKey) - strlen($qrEventKey));
                    YafDebug::log('qr param :'.$qrParam);
                    if(!empty($qrParam))
                    {
                        $qrModel = new QrcodeModel();
                        $qrModel->addScanQrCodeUser($fakeId,$qrParam);
                    }
                }
            }
//                $wechatUtil->text('欢迎关注哈罗同行。哈叔携一众小哈、哈妹欢迎你!
//同行让你上下班更~~~舒坦，免费摆渡车、定制班车随你坐，当然，要下载哈罗同行APP才可以哦!
//具体事宜，您可以点开下拉菜单，如遇其他问题，您可加入哈罗同行畅聊大巴1群 QQ：421571161 （满）   2群QQ：292395214，哈妹们会给您进行一 一解答。给您带来的不便敬请谅解。')->reply();

            $news = array(
//                array(
//                    'Title'=>'微信注册即送现金大礼包',
//                    'Description'=>'在这里您可以查询到哈罗同行班车时间及线路',
//                    'PicUrl'=>'http://wx.hollo.cn/images/news-banner.png',
//                    'Url'=>'http://wx.hollo.cn/activity'),
//                array(
//                    'Title'=>'哈罗同行班车线路查询',
//                    'Description'=>'在这里您可以查询到哈罗同行班车时间及线路',
//                    'PicUrl'=>'http://static.hollo.cn/images/wechat/wechat_cover_zao.png',
//                    'Url'=>'http://mp.weixin.qq.com/s?__biz=MzA3MTgwODk2Nw==&mid=206556119&idx=1&sn=47fc78b21ba52cb0ea8c95f31c0d5ba0#rd'),
//                array(
//                    'Title'=>'哈罗同行班车线路查询',
//                    'Description'=>'在这里您可以查询到哈罗同行班车时间及线路',
//                    'PicUrl'=>'http://static.hollo.cn/images/wechat/wechat_cover_wan.png',
//                    'Url'=>'http://mp.weixin.qq.com/s?__biz=MzA3MTgwODk2Nw==&mid=206450309&idx=1&sn=c999c869869deae4cfa39dd15624d43e#rd'),
                array(
                    'Title'=>'哈罗同行注册有礼',
                    'Description'=>'成功注册成为哈罗用户，即可获得哈罗为您准备的50元现金大礼包。赶快行动起来吧。',
                    'PicUrl'=>WeChatEnv::getHostUrl().'/images/news-banner.png',
                    'Url'=>WeChatEnv::getHostUrl().'/activity')
            );
            $wechatUtil->news($news)->reply();
        }
        else if ($event['event'] == 'unsubscribe')
        {

            $fakeId = $this->wechat->getRevFrom();
            YafDebug::log("fakeID =======".$fakeId);
            $model = new UserModel();
            $model->unSubscribeUser($fakeId);
        }
        elseif($event['event'] == 'view')
        {
            $fakeId = $this->wechat->getRevFrom();
            if($fakeId)
            {
                WeChatEnv::setOpenId($fakeId);
            }
        }
        elseif(strtolower($event['event']) == 'click')
        {
            $key=$event['key'];
            YafDebug::log('get event key is::'.$key);
            $replyContent=$this->getAutoReplyInfoWithKey($key,1);
            if(!empty($replyContent))
            {
                $wechatUtil->text($replyContent)->reply();
            }
        }
    }

    public function getBtnCreateArray()
    {
        $url = WeChatEnv::getHostUrl().'/api/wechat/wechat-menu?menuid=';

        $btn =  array();
        $btn1 = array(
            'name'=>'我要坐车',
            'sub_button'=>array(
                array(
                    'type'=>'view',
                    'name'=>'我的班车',
                    'url'=>$url.self::MENUID_MY_SCHEDULE_BUS
                ),
                array(
                    'type'=>'view',
                    'name'=>'我的车票',
                    'url'=>$url.self::MENUID_MY_TICKET
                ),
                array(
                    'type'=>'view',
                    'name'=>'我的摆渡车',
                    'url'=>$url.self::MENUID_MY_FERRY_BUS
                ),
                array(
                    'type'=>'view',
                    'name'=>'我的定制线路',
                    'url'=>$url.self::MENUID_MY_ROAD
                ),
                array(
                    'type'=>'view',
                    'name'=>'查询实时位置',
                    'url'=>$url.self::MENUID_SEARCH_BUS_PLACE_V2
                ),
            )
        );
        $btn2 = array(
            'name'=>'周边游',
            'sub_button'=>array(
                array(
                    'type'=>'view',
                    'name'=>'旅游线路',
                    'url'=>$url.self::MENUID_MY_TRAVEL_LIST
                ),
                array(
                    'type'=>'view',
                    'name'=>'行程凭证',
                    'url'=>$url.self::MENUID_MY_TRAVEL_TICKET
                ),
            )
        );
        $btn3 = array(
            'name'=>'我的哈罗',
            'sub_button'=>array(
                array(
                    'type'=>'view',
                    'name'=>'哈罗官网',
                    'url'=>'http://www.hollo.cn/index.html'
                ),
                array(
                    'type'=>'view',
                    'name'=>'下载哈罗',
                    'url'=>$url.self::MENUID_HOLLO_INTRO
                ),
//                array(
//                    'type'=>'click',
//                    'name'=>'遗失招领',
//                    'key'=>self::MENUID_HOLLO_LOST
//                ),
//                array(
//                    'type'=>'click',
//                    'name'=>'问题解答',
//                    'key'=>self::MENUID_HOLLO_QUSTION
//                ),
                array(
                    'type'=>'view',
                    'name'=>'我的帐号',
                    'url'=>$url.self::MENUID_HOLLO_MY_ACCOUNT
                ),

            )
        );
        $btn['button'] =  array($btn1,$btn2,$btn3);
        return $btn;

    }

    public function createMenuAction()
    {
        $btn = $this->getBtnCreateArray();
        $menuJs = utf8_json_encode($btn);

        YafDebug::log($menuJs);
        $this->wechat = new WeChatUtil();
        $response = $this->wechat->createMenu($menuJs);

        YafDebug::log('response : '.json_encode($response));
        echo "<pre>";
        var_dump($menuJs);
        var_dump($response);
        echo "</pre>";

        if(!$response)
        {
            YafDebug::log($this->wechat->errCode);
            YafDebug::log($this->wechat->errMsg);
        }
        haloDie();
    }

    public function getJsSignAction()
    {
        $url = $this->getLegalParam('url');
//        YafDebug::log('getJsSignAction get url is ::'.$url);
        $wechatUtil=new WeChatUtil();
//        $url=urlencode($url);
        $signPackage=$wechatUtil->getJsSign($url);
//        YafDebug::log('getJsSign is ::'.json_encode($signPackage));
        $this->inputResult($signPackage);
    }

    public function getJsSignJsonpAction()
    {
        $url = $this->getLegalParam('url');
//        YafDebug::log('getJsSignAction get url is ::'.$url);
        $wechatUtil=new WeChatUtil();
//        $url=urlencode($url);
        $signPackage=$wechatUtil->getJsSign($url);
//        YafDebug::log('getJsSign is ::'.json_encode($signPackage));
        $callback = $_GET['callback'];
        echo $callback.'('.json_encode($signPackage).')';
        die();
    }

    public function wechatMenuAction()
    {
        if(WeChatUtil::isMobileMicroMessenger()['isMMM'])
        {
            WeChatUtil::checkWechatAuth(array());
        }

        $session = Yaf_Session::getInstance();
        $fakeId =  WeChatEnv::getOpenId();
//        $uid = $session->offsetGet('uid');
//        $this->uid=$uid;

        YafDebug::log('open id is '.$fakeId);
//        YafDebug::log('uid is '.$this->uid);

//        if(!empty($fakeId) && $uid == null)
//        {
//            $model = new UserAdminModel(WeChatEnv::getCorpDbId());
//            $uid = $model->getUidByFakeId($fakeId);
//            if($uid == null)
//            {
//                $uid = $model->createUser($fakeId);
//            }
//            $this->uid = $uid;
//            $session->offsetSet('uid',$uid);
//        }

        $state = $this->getLegalParam('menuid','int');
        $host=WeChatEnv::getHostUrl();
        switch($state)
        {
            case self::MENUID_MY_SCHEDULE_BUS:
                $url = $host."/my-bus/0";
                break;
            case self::MENUID_MY_TICKET:
                $url = $host."/my-order/2";//未使用
                break;
            case self::MENUID_MY_FERRY_BUS:
                if($this->uid){
                    $url = $host."/my-ferry-ticket";
                }else{
                    $url = $host."/my-ferry-bus";
                }
                break;
            case self::MENUID_MY_ROAD:
                $url = $host."/my-custom-route/0/0";
                break;
            case self::MENUID_HOLLO_ACTIVITY:
                $url = $host."/activity";
                break;
            case self::MENUID_SEARCH_BUS_PLACE_V2:
                $url = $host."/search-bus-place";
                break;
            case self::MENUID_FREE_FERRY_BUS:
                $url = $host."/free-ferry-bus";
                break;
            case self::MENUID_HOLLO_MY_ACCOUNT:
                $url = $host."/account";
                break;
            case self::MENUID_HOLLO_INTRO:
                $url = $host."/intro";
                break;
            case self::MENUID_MY_TRAVEL_LIST:
                $url = $host."/travel-list";
                break;
            case self::MENUID_MY_TRAVEL_TICKET:
                $url = $host."/travel-ticket";
                break;
            default:
                $url = $session->offsetGet('origin_uri');
                $session->offsetUnset('origin_uri');
                break;
        }
        $this->jumpDirect($url);
        return false;
    }

}
