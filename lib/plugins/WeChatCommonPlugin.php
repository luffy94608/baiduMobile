<?php

class WeChatCommonPlugin extends Yaf_Plugin_Abstract
{
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        YafDebug::log('routerStartup :: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        YafDebug::log('post param :'.json_encode($_POST));
    }

    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {

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



}
