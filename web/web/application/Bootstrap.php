<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    protected function _initRoute(Yaf_Dispatcher $dispatcher)
    {
        $router = $dispatcher->getRouter();
//        $router->addRoute('api', new Yaf_Route_Regex('#^/(\w+)$#', array('controller' => 'index', 'action' => 1),array()));
    }

    protected function _initPlugin(Yaf_Dispatcher $dispatcher)
    {

        $dispatcher->registerPlugin(new PluginAuth());
        //导入公共的调用配置文件插件
        $dispatcher->registerPlugin(new WeChatCommonPlugin());

    }

    protected function _initView(Yaf_Dispatcher $dispatcher)
    {
        $view = new YafView(APPLICATION_VIEW_SCRIPTS_PATH);
//        $view->enableLayout('layout/normal.phtml');
        $dispatcher->setView($view);

    }
}

