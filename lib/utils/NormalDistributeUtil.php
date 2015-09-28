<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/2/26
 * Time: 上午10:50
 */

define('pi',3.1415926);

define('sigma',1.0);



class NormalDistributeUtil
{
    //区间[min,max]上的均匀分布,min和max要求传入的参数类型一致
    private function randAverage($min,$max)
    {
        return $min+($max-$min)*rand()/(rand()+1.0);
    }

    //求均值为miu，方差为sigma的正太分布函数在x处的函数值
    private function normal($x,$miu,$sigma)
    {
        return 1.0/sqrt(2*pi)/$sigma*exp(-1*($x-$miu)*($x-$miu)/(2*$sigma*$sigma));
    }

    //按照矩形区域在函数值曲线上下位置分布情况得到正太函数x值
    private  function randn($miu,$sigma, $min ,$max)
    {
        do{
            $x=$this->randAverage($min,$max);
            $y=$this->normal($x,$miu,$sigma);
            $dScope=$this->randAverage(0.0,$this->normal($miu,$miu,$sigma));
        }while($dScope>$y);
        return $x;
    }

    static function createNum($min,$max,$avg)
    {
        $a = new NormalDistributeUtil();
        return $a->randn($avg,sigma,$min,$max);
    }

}