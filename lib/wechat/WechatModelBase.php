<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 14-6-24
 * Time: 上午11:40
 */
define("EXTRA_INFO_TYPE_INPUT",0);
define("EXTRA_INFO_TYPE_DATE",1);
define("EXTRA_INFO_TYPE_SELECT",2);
define("EXTRA_INFO_TYPE_CLASS",3);

class WechatModelBase extends Halo_Model
{

    public function __construct($dbName = 'wechat')
    {
        parent::__construct($dbName);
    }

    public static  function getSubTable($prefix,$uid = 0)
    {
        $r = $uid%10;
        return $prefix.'_'.$r;
    }


}
