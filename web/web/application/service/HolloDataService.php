<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/19
 * Time: 下午1:58
 */

class HolloDataService extends HolloBaseService
{
    const POST_GET_TRAVEL_LIST = '/travel/travel_lines_page';
    const POST_GET_LIST = '/buses/near_by';
    const POST_GET_TRAVEL_DETAIL = '/travel/travel_line_detail';

    const GET_SEARCH_BUS_DETAIL_V2 = '/bus_path/search';
    const GET_SEARCH_BUS_LOCATION_V2 = '/buses/real_time_position';

    /**
     * 班车列表
     * @param int $time
     * @param int $next
     * @param int $cursor
     * @param string $lat
     * @param string $lng
     * @return array|HolloHttpResult|mixed
     */
    public function getRouteList($time = 0,$next = 0,$cursor = 0,$lat='',$lng='')
    {
        $param=array(
        );
//        $param = $this->getListQueryParams($time,$next,$cursor);
        if(!empty($lat) && !empty($lng))
        {
            $param['location'] =array(
                'lng'=>$lng,
                'lat'=>$lat,
            );
        }
        return $this->http_post(self::API_URL_PREFIX.self::POST_GET_LIST,$param);
    }

    /**
     * 旅游班车列表
     * @param string $cityId
     * @param int $time
     * @param int $next
     * @param int $cursor
     * @return array|HolloHttpResult|mixed
     */
    public function getTravelList($cityId = '',$time = 0,$next = 0,$cursor = 0)
    {
        $param = $this->getListQueryParams($time,$next,$cursor);
        $param['city_id'] =$cityId;
        return $this->http_post(self::API_URL_PREFIX.self::POST_GET_TRAVEL_LIST,$param);
    }
    /**
     * 旅游线路详情
     * @param $line_id
     * @return array|HolloHttpResult|mixed
     */
    public function getTravelDetail($line_id)
    {
        $param=array(
            'line_id'=>$line_id
        );
        return $this->http_post(self::API_URL_PREFIX.self::POST_GET_TRAVEL_DETAIL,$param);
    }

    /**
     * 获取班车实时位置 v2
     */
    public function searchBusLocation($busPathScheduleId)
    {
        return $this->http_post(self::API_V2_URL_PREFIX.self::GET_SEARCH_BUS_LOCATION_V2,array('bus_path_schedule_id'=>$busPathScheduleId));
    }

    /**
     * 获取车票id 获取实时位置 v2
     */
    public function busPosition($contract_id)
    {
        return $this->http_post(self::API_V2_URL_PREFIX.self::GET_SEARCH_BUS_LOCATION_V2,array('contract_id'=>$contract_id));
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

        $result= $this->http_get_data($url);
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
    private function http_get_data($url)
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