<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/9
 * Time: 下午10:27
 */

class ControllerData extends ControllerBase
{
    public function flushMcAction()
    {
        $token = $this->getLegalParam('token');
        if($token != 'hollo')
        {
            haloDie();
        }

        $mc = DataCenter::getMc();
        $this->inputResult($mc->flush());

        $redis = DataCenter::getRedis();
        $this->inputResult($redis->flushDB());
    }

    public function flushRedisAction()
    {
        $token = $this->getLegalParam('token');
        if($token != 'hollo')
        {
            haloDie();
        }
        $redis = DataCenter::getRedis();
        $this->inputResult($redis->flushDB());
    }
} 