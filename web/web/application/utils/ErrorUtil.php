<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/14
 * Time: 上午10:54
 */

class ErrorUtil
{
    const ErrCodeNeedLogin = -1000;
    const ErrUnkownErr = -1001;
    const ErrPayServerError = -1002;
    const ErrActionFailed = -1;

    const ErrActionPayFailed = -2000;

    public static $config = array(
        self::ErrActionFailed=>'操作失败',
        self::ErrActionPayFailed=>'支付未成功',
    );

    public static function getErrorText($code,$lang='zh')
    {
        $desc='未知错误';
        $config=self::$config;

        if(isset($config[$code]))
            $desc = $config[$code];

        return $desc;
    }
} 