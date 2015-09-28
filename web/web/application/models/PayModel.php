<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/14
 * Time: 下午1:42
 */

class PayModel extends HolloBaseModel
{

    public function updatePayInfo($holloPayId,$info)
    {
        $corpInfo = WeChatEnv::$corpInfo;
        if($corpInfo->appId == $info['appid'])
        {
            $map = array(
                'openid'=>'Fopen_id',
                'transaction_id'=>'Ftransaction_id',
                'total_fee'=>'Ffee',
                'err_code'=>'Ferr_code',
                'time_end'=>'Fend_time',
                'prepay_id'=>'Fpre_oder_id',
                //查询订单
                'trade_state'=>'Ftrade_state',
                'hollo_code'=>'Fhollo_code',
                'hollo_msg'=>'Fhollo_msg',
            );
            $data = array();
            $dbInfo = $this->getPayInfo($holloPayId);
            $needUpdate = false;
            foreach($info as $k=>$v)
            {
                if(isset($map[$k]))
                {
                    $data[$map[$k]] = $v;
                    if(!$needUpdate && $dbInfo[$map[$k]] !== $v)
                    {
                        $needUpdate = true;
                    }
                }
            }
            if($needUpdate)
            {
                YafDebug::log('update pay info :'.json_encode($data));
                $this->mc->deleteByIdAndTag($holloPayId,CacheKey::TABLE_PAY_INFO);
                return $this->wechatDb->updateTable('pay_info',$data,HaloPdo::condition('Fout_trade_no=?',$holloPayId));
            }
            else
            {
                return true;
            }
        }
        return false;
    }

    public function createPayInfo($openId,$info)
    {
        $map = array(
            'out_trade_no'=>'Fout_trade_no',
            'total_fee'=>'Ffee',
            'spbill_create_ip'=>'Fip',
        );
        //trade_type

        $data = array();
        foreach($info as $k=>$v)
        {
            if(isset($map[$k]))
            {
                $data[$map[$k]]=$v;
            }
        }
        $data['Fcreate_time']=date('YmdHis');
        $data['Ftype'] = $info['trade_type']=='JSAPI'? 0 : 1;
        $data['Fopen_id'] = $openId;
        YafDebug::log('create pay info :'.json_encode($data));
        $this->wechatDb->insertTable('pay_info',$data);
    }

    public function getPayInfo($holloPayId)
    {
        $row = $this->wechatDb_slave->getRowByCondition('pay_info',HaloPdo::condition('Fout_trade_no = ?',$holloPayId),'*',CacheKey::TABLE_PAY_INFO,$holloPayId);
        if($row)
        {
            return $row;
        }
        return false;
    }
} 