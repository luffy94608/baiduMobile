<?php
/**
 * Created by PhpStorm.
 * User: luffy
 * Date: 15/7/13
 * Time: 11:29
 */

class ControllerAnalyze extends ControllerBase
{

    /**
     * @var UserModel()
     */
    public $model;

    public function init()
    {
        parent::init();
        $this->model=new UserModel();
    }

    /**
     * 获取来源统计列表
     */
    public function getSourceAnalyzeListAction()
    {
        $result=$this->model->getSourceTotalListData();
        $this->inputResult($result);
    }

    /**
     * 获取单个来源统计详情
     */
    public function getSourceAnalyzeDetailAction()
    {
        $params['id']=$this->getLegalParam('id','int',array(),0);
        $result=$this->model->getSourceDetailData($params['id']);

        $this->inputResult(array('total'=>$result));
    }

}

