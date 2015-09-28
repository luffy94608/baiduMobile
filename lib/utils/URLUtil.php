<?php
/**
 * Created by PhpStorm.
 * User: su
 * Date: 14-5-17
 * Time: 下午6:11
 */
class URLUtil
{
    public static function setParam($url,$param,$filter = null)
    {
        $urlArray = parse_url($url);
        $queryArray = array();
        $queryStr = $urlArray['query'];
        if($queryStr)
        {
            parse_str($queryStr,$queryArray);
        }
        array_merge($queryArray,$param);
        if($filter)
        {
            foreach($filter as $key=>$value)
            {
                unset($queryArray[$value]);
            }
        }
        return $urlArray['scheme']."://". $urlArray['host'].'?'.http_build_query($queryArray);
    }
}