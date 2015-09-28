<?php

/**
 * 需要导入css和javascrpt参见map.phtml的head部分.
 */
class Halo_POIMap extends Zend_View_Helper_Abstract
{
    public function poiMap ($options)
    {
        ////Halo_Model::logs($options);
        if(!$options)
            $options = array();
        
        if (isset($options['country']) && $options['country'] != 1) {
            $parentCode = 400;
            $childCode = $options['country'];
        } else {
            $parentCode = isset($options['province']) ? $options['province'] : 0;
            $childCode = isset($options['city']) ? $options['city'] : 0;
        }
        
        $lbsModel = new LbsModel();
        $parentLoc = $lbsModel->getParentLocation();
        foreach ($parentLoc as $v) {
            $parentList[$v['id']] = $v['name'];
        }
        if (! $parentCode) {
            $parentCode = $parentLoc[0]['id'];
        }
        
        $childLoc = $lbsModel->getChildLocation($parentCode);
        foreach ($childLoc as $v) {
            $childList[$v['id']] = $v['name'];
        }
        
        $partial = new Zend_View_Helper_Partial();
        $partial->setView($this->view);
        
        return $partial->partial('view/map.phtml', 
                array(
                        'script' => 0,
                        'user' => array(
                                'province' => $parentCode,
                                'city' => $childCode
                        ),
                        'data' => array(
                                'province' => &$parentList,
                                'city' => &$childList
                        )
                ));
    }
}

?>