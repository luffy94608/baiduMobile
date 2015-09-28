<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/2/6
 * Time: 下午5:00
 */

define('MaxInviteCash',30);
define('PaySec','xJNT4VBzhyWiyqX4gFonEhdZuoRwNxas');

class WrmModel extends WechatModelBase
{
    /***
     * @var HaloDataSource
     */
    private $wechat;

    /***
     * @var HaloDataSource
     */
    private $wechat_slave;

    const RedpacketSubscribe = 0;
    const RedpacketDownload = 1;
    const RedpacketForward = 2;



    public function __construct()
    {
        parent::__construct();
        $this->wechat = DataCenter::getDb('wechat');
        $this->wechat_slave = DataCenter::getDb('wechat_slave');
    }

    public function getVerifyInfo($fakeId)
    {
        $row = $this->wechat_slave->getRowByCondition('user_verify',HaloPdo::condition('Ffake_id = ?',$fakeId));
        return $row;
    }

    /**
     * 生成一个被邀请的人
     * @param $phone
     * @param $from
     * @return bool|string
     */
    public function createUnregisterUser($phone,$from)
    {
        return $this->wechat->replaceTable('user_from',HaloPdo::condition('Fphone = ? AND Ffrom = ? AND Fcreate_time = ?',$phone,$from,time()));
    }

    /**
     * 添加一个未认证用户
     * @param $phone
     * @param $name
     * @param $org
     * @param $job
     * @return bool|int
     */
    public function registerUser($phone,$name,$org,$job)
    {
        return $this->wechat->insertTable('user_verify',array('Ffake_id'=>$phone,'Fname'=>$name,'Forg'=>$org,'Fjob'=>$job,'Freg_time'=>time()));
    }

    /**
     * 验证用户，之后发送红包
     * @param $fakeIds
     * @return bool|int
     */
    public function verifyUser($fakeIds)
    {
        if(is_array($fakeIds))
        {
            $ids = implode(',',$fakeIds);
        }
        else
        {
            $ids = $fakeIds;
        }

        return $this->wechat->updateTable('user_verify',array('Fverify'=>1,'Fverify_time'=>time()),sprintf('Ffake_id IN (%s)',$ids));
    }

}