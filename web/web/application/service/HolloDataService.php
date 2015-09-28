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
    const POST_GET_TRAVEL_DETAIL = '/travel/travel_line_detail';

    const GET_SEARCH_BUS_DETAIL_V2 = '/bus_path/search';
    const GET_SEARCH_BUS_LOCATION_V2 = '/buses/real_time_position';
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
}