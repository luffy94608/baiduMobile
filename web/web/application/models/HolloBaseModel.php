<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/14
 * Time: 下午1:47
 */

class HolloBaseModel extends WechatModelBase
{
    /**
     * @var HaloDataSource
     */
    public $wechatDb;

    /**
     * @var HaloDataSource
     */
    public $wechatDb_slave;

    public function __construct($dbName = 'wechat')
    {
        parent::__construct($dbName);
        $this->wechatDb = DataCenter::getDb('wechat');
        $this->wechatDb_slave = DataCenter::getDb('wechat_slave');
    }
} 