<?php
/**
 * Created by PhpStorm.
 * User: su
 * Date: 14-5-12
 * Time: 上午11:15
 */
require_once dirname(__FILE__) . '/../configs/SystemConfig.php';

$redis = WeChatEnv::getRedis();
$redis->delete('*');