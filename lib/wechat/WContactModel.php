<?php

class WContactModel extends Halo_Model
{
    public function isFriends($uid,$objUid)
    {
        $row = $this->db->getRowByCondition('relation_friend', HaloPdo::condition('Fuid=? AND Fobj_uid=?',$uid,$objUid), '*', MemCacheBase::TABLE_RELATION_FRIEND,$uid.'_'.$objUid);
        if ($row)
            return true;
        else
            return false;
    }
    public function isBlockUser($uid, $objUid)
    {
        if($objUid)
        {
            $dbWeb = $this->dbWeb();
            $row = $dbWeb->getRowByCondition('relation_block', HaloPdo::condition('Fuid=? AND Fobj_uid=?',$uid, $objUid), '*', MemCacheBase::TABLE_RELATION_BLOCK,$uid.'_'.$objUid);
            if ($row)
                return true;
            else
                return false;
        }
        else
            return false;
    }

    /**
     * @return HaloDataSource
     */
    public function dbCompany()
    {
        return DataCenter::getDb('company');
    }

    /**
     * @return HaloDataSource
     */
    public function dbWeb()
    {
        return DataCenter::getDb('web');
    }

    /**
     * @return HaloDataSource
     */
    public function dbFeed()
    {
        return DataCenter::getDb('feed');
    }

    /**
     * @return HaloKVClient
     */
    public function dbMongo()
    {
        return DataCenter::getMongo();
    }
}
