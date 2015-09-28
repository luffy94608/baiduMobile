<?php
class Halo_FormLoaction extends Zend_View_Helper_Abstract
{
    public function formLoaction ($options, $style = null, $parent='province', $child='city', $country = 'country')
    {
        $parentList = array( 0 => '请选择' );
        $childList = array( 0 => '请选择' );
        if (!$options) {
            $options = array();
        }
        
        $parentCode = isset($options[$parent]) ? $options[$parent] : 0;
        $childCode = isset($options[$child]) ? $options[$child] : 0;
        
        if(isset($options[$country]) && $options[$country] > 1) {
            $parentCode = 400;
            $childCode = $options[$country];
        }

        $lbsModel = new LbsModel();
        $parentLoc = $lbsModel->getParentLocation();

        foreach ($parentLoc as $v) {
            $parentList[$v['id']] = $v['name'];
        }
        if ($parentCode) {
            $childLoc = $lbsModel->getChildLocation($parentCode);
            foreach ($childLoc as $v) {
                $childList[$v['id']] = $v['name'];
            }
        }
        
        $select = new Zend_View_Helper_FormSelect();
        $select->setView($this->view);
        
        $html = array();
        $html[] = $select->formSelect($parent, $parentCode, $style, $parentList);
        $html[] = $select->formSelect($child, $childCode, $style, $childList);
        return implode("\n", $html);
    }
}
?>