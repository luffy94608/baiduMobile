<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/8
 * Time: 下午3:35
 */

class UserModel extends HolloBaseModel
{
    public function getUser($openId)
    {
        $row = $this->wechatDb_slave->getRowByCondition('user_info',HaloPdo::condition('Fopen_id = ?',$openId),'*',CacheKey::TABLE_USER_INFO,$openId);
        if($row)
        {
            $res = $this->ridFieldPrefix($row);
            return $res;
        }
        return false;
    }

    /**
     * 获取地理位置
     * @param $openId
     * @return bool|string
     */
    public function getUserLocation($openId)
    {
        $row = $this->wechatDb_slave->getRowByCondition('location',HaloPdo::condition('Fopen_id = ? ORDER BY Ftime DESC',$openId));
        if($row)
        {
            $res = $this->ridFieldPrefix($row);
            return $res;
        }
        return false;
    }


    public function registerUser($openId)
    {
        if(!empty($openId))
        {
            return $this->wechatDb->replaceTable('user_info',array('Fopen_id'=>$openId,'Fstatus'=>1,'Ftime'=>time()));
        }
        return false;
    }

    public function subscribeUser($openId)
    {
        $user = $this->getUser($openId);
        if($user)
        {
            if($user['status'] == 0)
            {
                return $this->updateUser($openId,array('Fstatus'=>1));
            }
            return true;
        }
        else
        {
            return $this->registerUser($openId);
        }
    }

    public function unSubscribeUser($openId)
    {
        if(!empty($openId))
        {
            return $this->updateUser($openId,array('Fstatus'=>0));
        }
        return true;
    }

    public function updateUser($openId,$data)
    {
        if(!empty($data))
        {
            $data['Ftime'] = time();
            $this->wechatDb->updateTable('user_info',$data,HaloPdo::condition('Fopen_id = ?',$openId));
            $this->mc->deleteByIdAndTag($openId,CacheKey::TABLE_USER_INFO);
        }
        return true;
    }

    /**
     * 添加统计信息
     * @param $openId
     * @param $uid
     * @return bool
     */
    public function updateUserFirstLogin($openId,$uid)
    {
        if(!empty($openId) && !empty($uid))
        {
            $info =  $this->wechatDb->getRowByCondition('user_login_first',HaloPdo::condition('Fuid = ?',$uid),'*',CacheKey::TABLE_USER_FIRST_LOGIN,$uid);
            if($info == false)
            {
                $this->wechatDb->insertTable('user_login_first',array('Fopen_id'=>$openId,'Fuid'=>$uid));
            }
        }
        return true;
    }

    /***
     * 获取公司信息
     * @return array|bool|string
     */
    public function getCorpInfo()
    {
        $row = $this->wechatDb_slave->getRowByCondition('corp_info','','*',CacheKey::TABLE_CORP_INFO);
        if($row)
        {
            $info = $this->ridFieldPrefix($row);
        }
        else
        {
            $info = false;
        }
        YafDebug::log('get corp info is '.json_encode($info));
        return $info;
    }


    public function getRefreshToken($uid)
    {
        $row = $this->wechatDb_slave->getRowByCondition('refresh_token',HaloPdo::condition('Fuid = ?',$uid));
        if($row && $row['Fexpire_time'] > time())
        {
            $time = $row['Fexpire_time'];
            $expire = time() - $time - 60*60;
            $this->mc->setByIdAndTag($uid,CacheKey::INFO_USER_REFRESH_TOKEN,$row['Frefresh_token'],$expire);
            return $row['Frefresh_token'];
        }
        return false;
    }

    public function insertRefreshToken($uid,$token,$expireTime)
    {
        $data = array(
            'Fuid'=>$uid,
            'Frefresh_token'=>$token,
            'Fexpire_time'=>time()+$expireTime - 60*60,
        );
        return $this->wechatDb->replaceTable('refresh_token',$data);
    }

    /***
     * 更新地理位置
     * @param $openId
     * @param $uid
     * @param $time
     * @param $lat
     * @param $lng
     * @param $pre
     * @return bool|int
     */
    public function updateLocation($openId,$uid,$time,$lat,$lng,$pre)
    {
        $data = array(
            'Fopen_id'=>$openId,
            'Fuid'=>$uid,
            'Ftime'=>$time,
            'Flat'=>$lat,
            'Flng'=>$lng,
            'Fpre'=>$pre
        );
        return $this->wechatDb->replaceTable('location',$data);
    }


    /**
     * 统计用户来源信息
     */
    public function getSourceTotalListData()
    {
        $result=$this->wechatDb_slave->getResultsByCondition('qrcode_source s left join user_login_first u ON  s.`Fopen_id`=u.`Fopen_id`',HaloPdo::condition('u.`Fuid`>0 group by s.`Fsource_qr_id` having count(s.`Fsource_qr_id`)>0'),'s.Fsource_qr_id id,count(s.Fsource_qr_id) total');
        $rMap=array();
        if($result)
        {
            foreach($result as $v)
            {
                $rMap[$v['id']]=$v;
            }
        }
        $res=$this->wechatDb_slave->getResultsByCondition('qrcode_limit',HaloPdo::condition('Fid'),'Fid');
        $resArr=array();
        if($res)
        {
            foreach($res as $k=>&$v2)
            {
                if(array_key_exists($v2['Fid'],$rMap))
                {
                    $v2['Ftotal']=$rMap[$v2['Fid']]['total'];
                }
                else
                {
                    unset($res[$k]);
                }
            }
            $resArr=$this->ridResultSetPrefix($res);
        }
        return $resArr;
    }

    /**
     * 获取单个来源的列表
     */
    public function getSourceDetailData($id)
    {
        $total=0;
        if(empty($id))
        {
            return $total;
        }
        $result=$this->wechatDb_slave->getRowByCondition('qrcode_source s left join user_login_first u ON  s.`Fopen_id`=u.`Fopen_id`',HaloPdo::condition('u.`Fuid`>0 AND s.`Fsource_qr_id`=? group by s.`Fsource_qr_id` having count(s.`Fsource_qr_id`)>0',$id),'s.Fsource_qr_id id,count(s.Fsource_qr_id) total');
        if($result)
        {
            $total=$result['total'];
        }
        return $total;
    }


    public function getAddressFromBaiduApi($lat='',$lng='')
    {
        $url='http://api.map.baidu.com/geocoder/v2/';
        $params=array(
            'ak'=>'A1piiq9w924IhDjMhHdnRBuW',
            'location'=>$lat.','.$lng,
            'output'=>'json',
        );
        $url.="?".http_build_query($params);

        $result= $this->http_get($url);
        $location=array();
        if($result)
        {
            $result=json_decode($result,true);
            if($result['status']===0)
            {
                $data=$result['result'];
                $location=$data['addressComponent'];
            }

        }
        return $location;
    }
    public function geoconvBaiduApi($lat='',$lng='')
    {
        $url='http://api.map.baidu.com/geoconv/v1/';
        $params=array(
            'ak'=>'A1piiq9w924IhDjMhHdnRBuW',
            'coords'=>$lng.','.$lat,
            'output'=>'json',
        );
        $url.="?".http_build_query($params);

        $result= $this->http_get($url);
        $location=array();
        if($result)
        {
            $result=json_decode($result,true);
            if($result['status']===0)
            {
                $data=$result['result'];
                $location=$data;
            }

        }
        return $location;
    }

    /**
     * GET 请求
     */
    private function http_get($url)
    {
        YafDebug::log('get url is '.$url);
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

}