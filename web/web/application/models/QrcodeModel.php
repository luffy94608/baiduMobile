<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/20
 * Time: 下午6:02
 */

class QrcodeModel extends HolloBaseModel
{
    public function getQrcodeInfo($id,$type = 1)
    {
        $row = $this->wechatDb_slave->getRowByCondition('qrcode_limit',HaloPdo::condition('Fid = ?',$id),'*',CacheKey::TABLE_QRCODE_INFO,$id);
        if($row)
        {
            return $this->ridFieldPrefix($row);
        }
        return false;
    }

    public function createQrcode($id,$ticket,$url,$name = '',$type = 1)
    {
        $data = array(
            'Fid'=>$id,
            'Fticket'=>$ticket,
            'Furl'=>$url,
            'Fname'=>$name,
            'Ftime'=>time(),
        );
        return $this->wechatDb->insertTable('qrcode_limit',$data);
    }

    public function addScanQrCodeUser($openId,$sourceId,$sourceFakeId='')
    {
        $data = array(
            'Fsource_qr_id'=>$sourceId,
            'Fopen_id'=>$openId,
            'Fsource_open_id'=>$sourceFakeId,
            'Ftime'=>time()
        );
        return $this->wechatDb->insertTable('qrcode_source',$data);
    }

    public function getQrcodeRecommendResult($ids,$detail = 0,$type = 1)
    {
        $con = sprintf('Fsource_qr_id IN (%s)',implode(',',$ids));
        $ret = $this->getQrcodeResutlWithConition($con,$detail);
        return $ret;
    }

    public function getAllQrcodeRecommendResult($detail = 0, $type = 1)
    {
        $ret = $this->getQrcodeResutlWithConition('',$detail);
        return $ret;
    }

    private function getQrcodeResutlWithConition($con,$detail)
    {
        $res = $this->wechatDb_slave->getResultsByCondition('qrcode_source',$con);
        $ret = array();
        if($res)
        {
            foreach($res as $row)
            {
                if(!isset($ret[$row['Fsource_qr_id']]['open_id']))
                {
                    $ret[$row['Fsource_qr_id']]['qrcode_id'] = $row['Fsource_qr_id'];
                    $ret[$row['Fsource_qr_id']]['open_id'] = $row['Fopen_id'];
                }
                if(!isset($ret[$row['Fsource_qr_id']]['count']))
                {
                    $ret[$row['Fsource_qr_id']]['count'] = 1;
                }
                else
                {
                    $ret[$row['Fsource_qr_id']]['count'] += 1;
                }
                if($detail == 1)
                {
                    $ret[$row['Fsource_qr_id']]['members'] = array($row['Fopen_id']);
                }
            }
        }
        return $ret;
    }
} 