<?php
class Halo_FormDateSelect extends Zend_View_Helper_Abstract
{
    public function formDateSelect($beginYear, $date, $style = null, $delimiter=" ", $day='day', $month = 'month', $year='year')
    {
        if($date) 
        {
            $date = getdate($date);
        } 
        else 
        {
            $date = array('year' => 0, 'mon' => 0, 'mday' => 0);
        }
        
        $years = array_reverse(range($beginYear, intval(date('Y'))));
        $yearList = array_combine($years, $years);
        $monthList = array_combine(range(1, 12), range(1, 12));
        $dayRang = array_combine(range(1, 31), range(1, 31));
        
        $yearList = CommonUtils::arrayPushFront($yearList, '年');
        $monthList = CommonUtils::arrayPushFront($monthList, '月');
        $dayRang = CommonUtils::arrayPushFront($dayRang, '日');
        
        $form = new Zend_View_Helper_FormSelect();
        $form->setView($this->view);
        
        ////Halo_Model::logs($date);
        
        echo $form->formSelect($year, $date['year'], $style, $yearList);
        echo $delimiter;
        echo $form->formSelect($month, $date['mon'], $style, $monthList);
        if($day != "") 
        {
            echo $delimiter;
            echo $form->formSelect($day, $date['mday'], $style, $dayRang);
        }
    }
}

?>