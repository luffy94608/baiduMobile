<?php

class ApiInvoker
{
   static public function synCall($module, $api, $params, $server='http://loc.yarserver.com')
   {
       $url = $server.'/'.$module;
       $client = new Yar_Client($url);
       return call_user_func(array($client, $api), $params);
   }

   static public function asynCall($module, $api, $params, $callback, $server='http://loc.yarserver.com')
   {
        return Yar_Concurrent_Client::call($server.'/'.$module, $api, array($params), $callback);
   }

   static public function pageletCall($module, $api, Pagelet $pagelet, $server='http://loc.yarserver.com')
   {
       $arguments = $pagelet->arguments;

       foreach ($arguments as $k=>$v)
       {
           if (is_object($v))
           {
               unset($arguments[$k]);
           }
       }

       if ($pagelet->asyn)
       {
           $callable = array($pagelet, 'on_result_arrive');
           return self::asynCall($module, $api, $arguments, $callable, $server);
       }

       return ApiInvoker::synCall($module, $api, $arguments, $server);
   }

   static public function errorCallback($retVal, $error, $callInfo)
   {
   }

   static public function loop()
   {
        Yar_Concurrent_Client::loop(null, array('ApiInvoker', 'errorCallback'));
   }
}