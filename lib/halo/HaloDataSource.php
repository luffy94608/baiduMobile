<?php

class HaloDataSource extends HaloPdo
{
    /**
     * @var MemCacheBase
     */
    public $mc;

    public function __construct($config, $mcName = 'web')
    {
        parent::__construct($config);
        $this->mc = new MemCacheBase($mcName);
    }

    public function getCacheData($tag, $id = null)
    {
        if($id===null)
            return $this->mc->get($tag);
        
        return $this->mc->getByIdAndTag($id, $tag);
    }

    public function setCacheData($tag, $id = '', $data, $expire = MemCacheBase::DEFAULT_EXPIRE)
    {
        if (!empty($tag))
            return $this->mc->setByIdAndTag($id, $tag, $data, $expire);
        return false;
    }

    /**
     * 使tag相关的缓存失效
     * @param $tag
     */
    public function invalidAllValuesByTag($tag)
    {
        $this->mc->invalidAllValuesByTag($tag);
    }

    /**
     * 使所有tag相关的缓存失效
     * @param $tags
     */
    public function invalidAllValuesByTags($tags)
    {
        if (empty($tags))
            return;

        foreach ($tags as $tag)
            $this->mc->invalidAllValuesByTag($tag);
    }

    public function invalidByTagAndId($tag, $id)
    {
        $this->mc->deleteByIdAndTag($id, $tag);
    }

    /**
     * 使所有表相关的缓存失效 array('uid'=>'','id'=>'')
     * param $table
     */
    public function invalidTableCache($table, $idMaps)
    {
        if (!isset(MemCacheBase::$tableKeyMap[$table]))
            return;

        foreach (MemCacheBase::$tableKeyMap[$table] as $idType => $tags)
        {
            if (isset($idMaps[$idType]))
            {
                foreach ($tags as $tag)
                {
                    foreach ($idMaps[$idType] as $id)
                    {
                        $this->mc->deleteByIdAndTag($id, $tag);
                    }
                }
            }
            else
            {
                foreach ($tags as $tag)
                {
                    $this->mc->invalidAllValuesByTag($tag);
                }
            }
        }
    }

    /**
     * 使table相关的id缓存失效
     * param $table
     * param $ids 单个id或id数组
     * param string $type
     */
    public function invalidTableCacheByIds($table, $ids, $type = 'uid', $force = false)
    {
        if (empty($ids))
            return;

        if (isset(MemCacheBase::$tableKeyMap[$table][$type]))
        {
            if (!is_array($ids))
                $ids = array($ids);

            $allTags = MemCacheBase::$tableKeyMap[$table][$type];
            foreach ($allTags as $tag)
            {
                if (count($ids))
                {
                    foreach ($ids as $id)
                    {
                        $this->mc->deleteByIdAndTag($id, $tag);
                    }
                }
                else
                {
                    $this->mc->invalidAllValuesByTag($tag);
                    YafDebug::log(sprintf('Invalid all tag values for table:%s tag:%s', $table, $tag));
                }
            }
        }
    }

    /**
     * @param $tagMap array('tag_name'=>array(id,id))
     */
    public function invalidCacheByTagMap($tagMap)
    {
        foreach ($tagMap as $tag => $ids)
        {
            foreach ($ids as $id)
                $this->mc->deleteByIdAndTag($id, $tag);
        }
    }

    public function getVarByCondition($table, $condition, $varName, $cacheTag = '', $cacheId = '', $expire = MemCacheBase::DEFAULT_EXPIRE)
    {
        $var = false;

        if (!empty($cacheTag))
        {
            $var = $this->mc->getByIdAndTag($cacheId, $cacheTag);
        }

        if ($var === false)
        {
            $var = parent::getVarByCondition($table, $condition, $varName);
            $this->setCacheData($cacheTag, $cacheId, $var, $expire);
        }

        return empty($var) ? null : $var;
    }

    public function getDistinctCountByCondition($table, $condition = '', $countPara = '', $cacheTag = '', $cacheId = '', $expire = MemCacheBase::DEFAULT_EXPIRE)
    {
        $count = false;
        if (!empty($cacheTag))
            $count = $this->mc->getByIdAndTag($cacheId, $cacheTag);

        if ($count === false)
        {
            $count = parent::getDistinctCountByCondition($table, $condition, $countPara);
            if (!empty($cacheTag))
                $this->mc->setByIdAndTag($cacheId, $cacheTag, $count, $expire);
        }

        return $count;
    }

    public function getCountByCondition($table, $condition = '', $filedName = '*', $cacheTag = '', $cacheId = '', $expire = MemCacheBase::DEFAULT_EXPIRE)
    {

        $count = false;
        if (!empty($cacheTag))
            $count = $this->mc->getByIdAndTag($cacheId, $cacheTag);

        if ($count === false)
        {

            $count = parent::getCountByCondition($table, $condition, $filedName);
            if (!empty($cacheTag))
                $this->mc->setByIdAndTag($cacheId, $cacheTag, $count, $expire);
        }

        return $count;
    }

    public function getDistinctByCondition($table, $condition, $distinct, $cacheTag = '', $cacheId = '', $expire = MemCacheBase::DEFAULT_EXPIRE)
    {
        $results = false;
        if (!empty($cacheTag))
        {
            $results = $this->mc->getByIdAndTag($cacheId, $cacheTag);
        }

        if ($results === false)
        {
            $results = parent::getDistinctByCondition($table, $condition, $distinct);
            if (!empty($cacheTag))
                $this->mc->setByIdAndTag($cacheId, $cacheTag, $results, $expire);
        }

        return $results;
    }

    public function getRowByCondition($table, $condition, $fields = '', $cacheTag = '', $cacheId = '', $expire = MemCacheBase::DEFAULT_EXPIRE)
    {
        $row = false;
        if (!empty($cacheTag))
            $row = $this->mc->getByIdAndTag($cacheId, $cacheTag);

        if ($row === false)
        {
            $row = parent::getRowByCondition($table, $condition, $fields);
            if (!empty($cacheTag) && $row)
            {
                $this->mc->setByIdAndTag($cacheId, $cacheTag, $row, $expire);
            }
        }

        return empty($row) ? null : $row;
    }
    public function getColByCondition($table, $condition, $colName, $cacheTag = '', $cacheId = '', $expire = MemCacheBase::DEFAULT_EXPIRE)
    {
        $result = false;
        if (!empty($cacheTag))
            $result = $this->mc->getByIdAndTag($cacheId, $cacheTag);

        if ($result === false)
        {
            $result = parent::getColByCondition($table, $condition, $colName);
            if (!empty($cacheTag))
                $this->mc->setByIdAndTag($cacheId, $cacheTag, $result, $expire);
        }

        return $result;
    }

    public function getResultsByCondition($table, $condition = '', $fields = '', $cacheTag = '', $cacheId = '', $expire = MemCacheBase::DEFAULT_EXPIRE)
    {
        $result = false;
        if (!empty($cacheTag))
            $result = $this->mc->getByIdAndTag($cacheId, $cacheTag);

        if ($result === false)
        {
            $result = parent::getResultsByCondition($table, $condition, $fields);
            if (!empty($cacheTag))
                $this->mc->setByIdAndTag($cacheId, $cacheTag, $result, $expire);
        }

        return $result;
    }
    
    /**
     * 过滤已存在memcache的内容
     * param $uids
     * param $tag
     * param $result
     * return array
     */
    public function getMissedId($tag, $uids, &$result)
    {
        $remainIds = array();
        foreach ($uids as $uid)
        {
            $dataMc = $this->getCacheData($tag, $uid);
            if ($dataMc)
            {
                $result[] = $dataMc;
            }
            else
            {
                $remainIds[] = $uid;
            }
        }

        return $remainIds;
    }

    public function getResultsByIds($table, $ids, $cacheTag = '', $idName = 'Fid', $extraCon ='',$expire = MemCacheBase::DEFAULT_EXPIRE)
    {
        if (!is_array($ids))
        {
            $ids = explode(',', $ids);
        }
        if (count($ids) == 0)
            return array();

        $idDataMap = array();
        $missIds = array();
        foreach ($ids as $id)
        {
            $data = $this->getCacheData($cacheTag, $id);
            if ($data)
                $idDataMap[$id] = $data;
            else
                $missIds[] = $id;
        }

        if (count($missIds))
        {
            if(!empty($extraCon))
            {
                $con = sprintf('%s AND %s IN (%s)',$extraCon,$idName, implode(',', $missIds));
            }
            else
            {
                $con = sprintf('%s IN (%s)',$idName,implode(',', $missIds));
            }
            $results = $this->getResultsByCondition($table, $con);

            foreach ($results as $row)
            {
                $id = $row[$idName];
                $idDataMap[$id] = $row;
                $this->setCacheData($cacheTag, $id, $row, $expire);
            }
        }

        $results = array();
        foreach ($ids as $id)
        {
            if (isset($idDataMap[$id]))
                $results[] = $idDataMap[$id];
        }

        return $results;
    }

    /*
     * @param $table
     * @param $data
     * @param $invalidMaps = array('id'=>array, 'uid'=>array())
     * 新生成一条数据，影响到关联到的表的cache
     * 什么都不传入
     * id|uid 只有一个
     *
     * @return bool|int
     */
    public function insertTable($table, $data,  $tagName='', $idName='Fuid', $invalidUids=null, $invalidIds=null)
    {
        $id = parent::insertTable($table, $data);
        if ($id === false)
            return false;
        
        if (!empty($tagName))
        {
            if (empty($idName))
                $cacheId = $id;
            else
            {
                if (isset($data[$idName]))
                {
                    $cacheId = $data[$idName];
                }
                else
                {
                    $cacheId = $id;
                }
            }

            $data['Fid'] = $cacheId;
            $this->setCacheData($tagName, $cacheId, $data);
        }

        $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
        $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);

        return $id;
    }

    public function updateFieldByIncrease($table,$field,$condition, $diff=1,   $invalidUids=null, $invalidIds=null)
    {
        $ret = parent::updateFieldByIncrease($table, $field, $condition, $diff);
            $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
            $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);
        return $ret;
    }

    public function batchInsertData( $table, $fields, $valueData ,  $invalidUids=null, $invalidIds=null)
    {
        $ret = parent::batchInsertData($table, $fields, $valueData);
        $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
        $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);
        return $ret;
    }

    public function updateTable($table, $data, $condition, $invalidUids=null, $invalidIds=null)
    {
        $ret = parent::updateTable($table, $data, $condition);
            $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
            $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);
        return $ret;
    }

    public function updateFieldsByIncrease($table, $data, $condition, $invalidUids = null, $invalidIds = null)
    {
        $ret = parent::updateFieldByIncrease($table, $data, $condition);
            $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
            $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);
        return $ret;
    }

    public function insertOrUpdateTable($table, $data, $condition, $idField = 'Fid', $invalidUids = null, $invalidIds = null)
    {
        $ret = parent::insertOrUpdateTable($table, $data, $condition, $idField);
            $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
            $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);
        return $ret;
    }
//    public function insertIfNotExist($table, $data, $condition, $keyField='Fid')
//    {
//    }
    public function replaceTable($table, $data, $invalidUids=null, $invalidIds=null)
    {
        $ret = parent::replaceTable($table, $data);
            $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
            $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);
        return $ret;
    }
    public function delRowByCondition($table, $map, $invalidUids=null, $invalidIds=null)
    {
        $ret = parent::delRowByCondition($table, $map);
        $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
        $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);
        return $ret;
    }
    //Modify by Jet 传ids，可以清memcache
    public function delRowByCondition2($table,$condition, $invalidUids=null, $invalidIds=null)
    {
        $ret = parent::delRowByCondition2($table, $condition);
            $this->invalidTableCacheByIds($table, $invalidUids, 'uid', true);
            $this->invalidTableCacheByIds($table, $invalidIds, 'id', true);
        return $ret;
    }
    public static function getResultWithField($rows, $fieldNames, $ridPrefix = true)
    {
        if (!is_array($fieldNames))
        {
            $fieldNames = explode(',', $fieldNames);
            array_walk($fieldNames, function (&$val)
            {
                $val = trim($val);
            });
        }

        $results = array();
        foreach ($rows as $row)
        {
            $item = array();
            foreach ($fieldNames as $fieldName)
            {
                if (isset($row[$fieldName]))
                    $item[$fieldName] = $row[$fieldName];
                else
                    $item[$fieldName] = '';
            }
            $results[] = $item;
        }

        if ($ridPrefix)
            $results = self::ridResultSetPrefix($results);

        return $results;
    }

    public static function getRowWithField($row, $fieldNames, $ridPrefix = false)
    {
        if (empty($row))
            return null;

        if (!is_array($fieldNames))
        {
            $fieldNames = explode(',', $fieldNames);
            array_walk($fieldNames, function (&$val)
            {
                $val = trim($val);
            });
        }
        $result = array();
        foreach ($fieldNames as $item)
        {
            $k = $ridPrefix ? substr($item, 1) : $item;
            $result[$k] = $row[$item];
        }

        return $result;
    }

    public static function ridFieldPrefix($data)
    {
        $result = array();
        if ($data)
        {
            foreach ($data as $k => $v)
            {
                $result[substr($k, 1)] = $v;
            }
        }

        return $result;
    }

    public static function ridResultSetPrefix(&$data)
    {
        $result = array();
        if (!empty($data))
        {
            foreach ($data as &$row)
            {
                $result[] = self::ridFieldPrefix($row);
            }
        }

        return $result;
    }

    public function getRowInResultByConditionMap(&$result, $conditionMap = array())
    {
        if (empty($result))
        {
            return null;
        }

        if (is_array($conditionMap))
        {
            foreach ($result as $row)
            {
                $match = true;
                foreach ($conditionMap as $k => $v)
                {
                    if ($row[$k] != $v)
                    {
                        $match = false;
                        break;
                    }
                }
                if ($match)
                {
                    return $row;
                }
            }

        }

        return false;
    }

    public function getColInResultByConditionMap(&$result, $col, $conditionMap = array())
    {
        $ret = array();
        if (is_array($conditionMap) && !empty($col))
        {
            foreach ($result as $row)
            {
                $match = true;
                foreach ($conditionMap as $k => $v)
                {
                    if ($row[$k] != $v)
                    {
                        $match = false;
                        break;
                    }
                }
                if ($match)
                {
                    $ret[] = $row[$col];
                }
            }
        }

        return $ret;
    }

    public function getSubResultByConditionMap(&$result, $conditionMap = array())
    {
        $ret = array();
        if (is_array($conditionMap))
        {
            foreach ($result as $row)
            {
                $match = true;
                foreach ($conditionMap as $k => $v)
                {
                    if ($row[$k] != $v)
                    {
                        $match = false;
                        break;
                    }
                }
                if ($match)
                {
                    $ret[] = $row;
                }
            }
        }

        return $ret;
    }
    
    //===========================================================================
    //Row cache related
    public function getCacheRowById($table, $id, $idField='Fid', $excludeDel = true)
    {
        $row  = false;

        $isCacheTable =  MemCacheBase::isCacheEnabled($this, $table, $idField);
        $isCacheTable = true;
        $tag = null;
        if ($isCacheTable)
        {
            $tag = $this->getTableCacheTag($table, $idField);
            $row = $this->mc->getByIdAndTag($id, $tag);
        }
        if ($row === false)
        {
            $row = parent::getRowByCondition($table, HaloPdo::condition(sprintf('%s=?', $idField), $id));
            if ($row && $isCacheTable)
                $this->mc->setByIdAndTag($row[$idField], $tag, $row);
        }

        if($excludeDel && isset($row['Fdel']) && ($row['Fdel']>0))
            return null;
        
        return empty($row) ? null : $row;
    }
    public function getCacheResultsByIds($table, $ids, $idField='Fid',  $excludeDel=true)
    {
        $results = array();
        foreach($ids as $id)
        {
            $row = $this->getCacheRowById($table, $id, $idField, $excludeDel);
            if($row)
                $results[] = $row;
        }
        return $results;
    }
    public function invalidCacheRowByIds($table, $ids, $idField='Fid')
    {
        if(empty($ids))
            return ;
        
        foreach($ids as $id)
        {
            $this->invalidCacheRowById($table, $id, $idField);
        }
    }
    public function invalidCacheRowById($table, $id, $idField)
    {
        $tag = $this->getTableCacheTag($table, $idField);
        $this->mc->deleteByIdAndTag($id, $tag);
    }
    public function invalidCacheRowsByIdMap($table, $idMap)
    {
        foreach($idMap as $idField=>$ids)
            $this->invalidCacheRowByIds($table, $ids, $idField);
    }
    protected function getTableCacheTag($table, $idField)
    {
        return $this->dbname . ':' . strtolower($table).':'.$idField;
    }
}