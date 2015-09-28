<?php
/**
 * Created by PhpStorm.
 * User: jet
 * Date: 15/5/9
 * Time: 下午9:37
 */

class ReplyModel extends HolloBaseModel
{
    /**
     * 获取自动回复信息
     * $type 0=查询文本回复，1=查询按钮事件回复
     */
    public function getAutoReplyInfoWithKey($key_word,$type=0)
    {
        if(empty($key_word))
        {
            return false;
        }
        $key_word=trim($key_word);
        $result=$this->wechatDb_slave->getRowByCondition('wechat_auto_reply',HaloPdo::condition('Fkey = ? AND Ftype=? AND Fenable=1',$key_word,$type),'*',CacheKey::TABLE_AUTO_REPLY_INFO,$type.'_'.$key_word);
        if(!empty($result))
        {
            return $result['Fword'];
        }
        return false;
    }
}