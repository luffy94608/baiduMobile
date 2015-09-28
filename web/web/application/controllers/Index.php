<?php

class ControllerIndex extends ControllerBase
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        YafDebug::dump('Hello');
        return false;
    }


    public function toLoginAction()
    {
        $config = HaloEnv::get('config');
        $url = $config['url']['mobile']."?t=".time();
        $loginUrl = $this->getLoginUrl($url);
        $this->jumpDirect($loginUrl);
    }



}

