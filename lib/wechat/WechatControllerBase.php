<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 14/12/10
 * Time: 下午4:21
 */

class WechatControllerBase  extends YafController
{
    /***
     * @var CorpInfo
     */
    protected  $corpInfo;

    public function init()
    {
        parent::init();
        $session = Yaf_Session::getInstance();
        YafDebug::log("WechatControllerBase uid is".$this->uid." session open is".WeChatEnv::getOpenId());
//        $this->corpInfo = WeChatEnv::getCorpInfo();
    }
} 