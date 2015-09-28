<?php

class ControllerError extends ControllerBase
{
    public function errorAction($exception)
    {
//        $this->view->disableLayout();
        $session = Yaf_Session::getInstance();
//        $this->fillViewWithCommonData($session->offsetGet('uid'));
        $this->logger->ERROR(sprintf("uid:%s uri:%s code:%s msg:%s stack:\r\n%s", $this->uid, $this->getRequest()->getRequestUri(), $exception->getCode(), $exception->getMessage(), $exception->getTraceAsString()),
            __FILE__, __LINE__, '404');

//        YafDebug::dump($exception);
//        return false;
    }
    public function maintainAction()
    {
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) 
        {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
}

