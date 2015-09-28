<?php

class PluginAuth extends Yaf_Plugin_Abstract
{
    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        if(WeChatUtil::isMobileMicroMessenger()['isMMM'])
        {
//            WeChatUtil::checkWechatAuth();
        }
    }
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        $aliasName = $request->getActionName();
        $name = str_replace('-','',$aliasName);
        $request->setActionName($name);

        $aliasController = $request->getControllerName();
        $list = explode('-', $aliasController);
        if ($list && count($list))
        {
            foreach ($list as &$item)
            {
                $item = ucfirst($item);
            }
            $request->setControllerName(implode('', $list));
        }

        $request->setParam('action_alias_name', $aliasName);
    }

    public function headerLocation($url)
    {
        header('Location: '.$url);
        haloDie();
    }
}
