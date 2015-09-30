<?php
/**
 * Created by PhpStorm.
 * User: luffy
 * Date: 15/7/22
 * Time: 12:26
 */

class ControllerHollo extends ControllerBase
{

    public function init()
    {
        parent::init();
    }

    public function translateAction()
    {
        $params['lng']=$this->getLegalParam('lng','str');
        $params['lat']=$this->getLegalParam('lat','str');
        if(in_array(false,$params))
        {
            $this->inputParamErrorResult();
        }
        $params['type']=$this->getLegalParam('type','str');

        $model=new HolloDataService();
        $result=$model->geoconvBaiduApi(floatval($params['lat']),floatval($params['lng']));

        if($params['type']=='jsonp')
        {
            $callback = $_GET['callback'];
            echo $callback.'('.json_encode($result).')';
            die();
        }
        else
        {
            $this->inputResult($result);
        }

    }

    /**
     * 班车线路
     */
    public function getTravelListAction()
    {
        $this->checkReferer();
        $params['timestamp']=$this->getLegalParam('timestamp','int',array(),0);//获取列表时间戳
        $params['cursor_id']=$this->getLegalParam('cursor_id','int',array(),0);//cursor_id
        $params['lng']=$this->getLegalParam('lng','str',array(),'');
        $params['lat']=$this->getLegalParam('lat','str',array(),'');
        $params['is_next']=$this->getLegalParam('is_next','enum',array(0,1),0);
        if(in_array(false,$params,true))
        {
            $this->inputParamErrorResult();
        }

        $model=new HolloDataService();
//        $result=$model->getTravelList($params['city_id'],$params['timestamp'], $params['is_next'], $params['cursor_id']);
        $result=$model->getRouteList($params['timestamp'], $params['is_next'], $params['cursor_id'], $params['lat'], $params['lng']);
        $data='';
        if($result->code==0)
        {
            $data=$result->data;
        }
        $this->inputResult($data);
    }


    /*
    * 根据线路id查询实时位置 v2
    */
    public function searchBusPlaceAction()
    {
        $params['id']=$this->getLegalParam('id','str');

        if(in_array(false,$params))
        {
            $this->inputParamErrorResult();
        }

        $model=new HolloDataService();
        $result=$model->searchBusLocation($params['id']);
        $data='';
        if($result->code==0)
        {
            $data=$result->data;
        }
        $data=array(
            'cur_loc'=>array(
                'lat'=>40.04158147225453,
                'lng'=>116.31060676942272
            ),
            "cur_pos"=> "测试地点",
            "dest_station_arrive_time"=> null,
            "dest_station_name"=> "天堂",
            "line_code"=> "Z015",
            "line_id"=> "5518d816471d06247467ff33",
            "line_schedule_id"=> "5574e9123deae2e73b602d83",
            "next_station_arrive_time"=> "08:40",
            "next_station_name"=> "云南"
        );
        $this->inputResult($data);
    }

} 