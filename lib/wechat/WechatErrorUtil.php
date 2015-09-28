<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 14-7-14
 * Time: 下午1:41
 */

class WechatErrorUtil
{
    const ERROR_NT_NO_MONEY = 100;          //没钱了
    const ERROR_NT_AREADY_RECOMMEND = 101;  //已经被推荐

    const ERROR_INVALID_NAME_PSW = 102;          //用户名或密码错误


    public function getErrorDesc($errorId)
    {
        switch($errorId)
        {
            case ERROR_NT_NO_MONEY:
                $desc = '公司欠费';
                break;
            case ERROR_NT_AREADY_RECOMMEND:
                $desc = '已被推荐过';
                break;
        }

        return $desc;
    }


} 