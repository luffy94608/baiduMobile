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

        $model=new UserModel();
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
     * 获取旅游线路
     */
    public function getTravelListAction()
    {
        $this->checkReferer();
        $params['timestamp']=$this->getLegalParam('timestamp','int',array(),0);//获取列表时间戳
        $params['cursor_id']=$this->getLegalParam('cursor_id','int',array(),0);//cursor_id
        $params['city_id']=$this->getLegalParam('city_id','str',array(),'');//city_id
        $params['is_next']=$this->getLegalParam('is_next','enum',array(0,1),0);
        if(in_array(false,$params,true))
        {
            $this->inputParamErrorResult();
        }

        $model=new HolloDataService();
        $result=$model->getTravelList($params['city_id'],$params['timestamp'], $params['is_next'], $params['cursor_id']);
        $data='';
        if($result->code==0)
        {
            $data=$result->data;
        }
        $this->inputResult($data);
    }


    /**
     * 获取旅游线路详情
     */
    public function getTravelDetailAction()
    {
        $this->checkReferer();
        $params['id']=$this->getLegalParam('id','str');
        if(in_array(false,$params,true))
        {
            $this->inputParamErrorResult();
        }
        $model=new HolloDataService();
        $result=$model->getTravelDetail($params['id']);
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
        $result=$model->busPosition($params['id']);
        $data='';
        if($result->code==0)
        {
            $data=$result->data;
        }

        $this->inputResult($data);
    }

} 