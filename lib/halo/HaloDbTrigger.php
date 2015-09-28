<?php
/**
 * Created by JetBrains PhpStorm.
 * User: chenjian
 * Date: 13-11-12
 * Time: 下午2:53
 * To change this template use File | Settings | File Templates.
 */

class HaloDbTrigger
{
    const TABLE_EVENT_NONE = 0;
    const TABLE_EVENT_UPDATE = 1;
    const TABLE_EVENT_DELETE = 2;
    const TABLE_EVENT_REPLACE = 3;

    const DATA_TRIGGER = 1;
    const DATA_RELATION_TRIGGER = 2;

    
    /**
     * @var HaloPdo
     */
    private $_task_db;
    private $actionEnum = array('i', 'u', 'd');

    /**
     * @var HaloPdo
     */
    private $monitorDb = null;

    private $eventTable = null;
    private $eventType = self::TABLE_EVENT_UPDATE;

    private $eventTablePrimaryKey = null;
    private $delRelatedField = null;
    private $eventTableCacheFields = null;

    private $needProcess = false;
    private $needTrigger = false;
    private $isEventTableCachable = false;

    private $triggerId = null;
    private $affectedRows = null; 
    
    public  function __construct(HaloDataSource $monitorDb)
    {
        $name = 'task';
        $config = Yaf_Registry::get('config');
        $dbConfig = $config->db->{$name};
        if (empty($dbConfig))
        {
            throw new Exception(sprintf('config of db %s is not found', $name), -9999);
        }
        $this->triggerEnable = $config->trigger->enable == "1" ? true : false;

        $this->monitorDb = $monitorDb;
        
        $this->_task_db = new HaloPdo(array('host'=>$dbConfig->host, 'port'=>$dbConfig->port, 'user'=>$dbConfig->user, 'pass'=>$dbConfig->pass, 'dbname'=>$dbConfig->name));
    }

    public function beforeUpdateTable($table, $condition,  $eventType, $needTrigger=true)
    {
        $triggerId = $needTrigger ? $this->getTriggerId($this->monitorDb->dbname, $table) : null;
        $this->needTrigger = $this->triggerEnable && !empty($triggerId);

        $cacheFields = MemCacheBase::getCacheIdFields($this->monitorDb, $table);
        $this->isEventTableCachable = count($cacheFields) > 0;
        
        $this->needProcess  = ($this->isEventTableCachable ||  $this->needTrigger);
        
        if(!$this->needProcess)
            return ;
        
        $primaryKey = $this->monitorDb->getPrimaryKeyName($table);
        
        $this->eventTable = $table;
        $this->triggerId = $triggerId;
        $this->eventType = $eventType;
        $this->eventTableCacheFields = $cacheFields;
        $this->eventTablePrimaryKey  = $primaryKey;
        
        $queryFields = $cacheFields;
        if(!empty($primaryKey) && !in_array($primaryKey, $cacheFields))
            $queryFields[] = $primaryKey;

        $delRelatedField = null;
        if($eventType == self::TABLE_EVENT_DELETE || $this->eventType == self::TABLE_EVENT_REPLACE)
        {
            $delRelatedField = $this->_task_db->getVarByCondition('data_db_map', sprintf('Fdb_name=\'%s\' AND Ftable_name=\'%s\'', $this->monitorDb->dbname, $table), 'Frelated_field');
        }
        $this->delRelatedField = $delRelatedField;
        
        if(!empty($delRelatedField) && !in_array($primaryKey, $cacheFields))
            $queryFields[] = $delRelatedField;
                
        if(empty($queryFields))
            return ;

        $this->affectedRows = $this->monitorDb->getResultsByCondition($table, $condition, implode(',', $queryFields));
    }
    public function afterUpdateTable($table, $condition)
    {
        if(!$this->needProcess || $this->eventTable != $table)
        {
            $this->eventTable = null;
            $this->affectedRows = null;
            $this->needProcess = false;
            return ;
        }
        
        if(count($this->affectedRows) == 0)
            return ;
        
        $cacheIdMap = array();
        foreach($this->affectedRows as $row)
        {
            foreach($this->eventTableCacheFields as $cacheFeild)
            {
                $cacheIdMap[$cacheFeild][] = $row[$cacheFeild];
            }
            $this->monitorDb->invalidCacheRowsByIdMap($table, $cacheIdMap);
        }
        
        if($this->needTrigger)
            $this->processTrigger($this->eventTable);
        
        $this->relatedRows = null;
        $this->eventTable = null;
        $this->needProcess = false;
    }

    protected function processTrigger($table)
    {
        $triggerId = $this->triggerId;
        $delRelatedIds = array();
        if ($this->eventType == self::TABLE_EVENT_DELETE || $this->eventType == self::TABLE_EVENT_REPLACE)
        {
            if (!empty($this->delRelatedField))
            {
                foreach ($this->affectedRows as $row)
                {
                    $delRelatedIds[] = $row[$this->delRelatedField];
                }
                $this->insertRecords($this->monitorDb->dbname, $table, $delRelatedIds, 'd', $triggerId);
            }
        }
        if ($this->eventType == self::TABLE_EVENT_UPDATE || $this->eventType == self::TABLE_EVENT_REPLACE)
        {
            $primaryKey = $this->eventTablePrimaryKey;
            if (!empty($primaryKey))
            {
                $triggerIds = array();
                foreach ($this->affectedRows as $row)
                {
                    $triggerIds[] = $row[$primaryKey];
                }
                $this->insertRecords($this->eventTablePrimaryKey, $table, $triggerIds, 'u', $triggerId);
            }
        }
    }
    
    public function updateTableTrigger($table, $condition, $needTrigger=true)
    {
        $this->beforeUpdateTable($table, $condition, self::TABLE_EVENT_UPDATE, $needTrigger);
        $this->afterUpdateTable($table, $condition);
        return ;
    }
    
    public function insertTableTrigger($table, $ids, $triggerId=null, $needTrigger=true)
    {
        if(!$this->triggerEnable)
            return ;
        
        empty($triggerId) && $triggerId = $this->getTriggerId($this->monitorDb->dbname, $table);
    
        if (!empty($triggerId) && $needTrigger)
             $this->insertRecords($this->monitorDb->dbname, $table, $ids, 'i', $triggerId);
    }

    private function insertRecords($dbname, $table, $ids, $action='u', $triggerId=null)
    {
        if (empty($ids))
            return null;

        if (!is_array($ids))
        {
            $ids = array($ids);
        }
        
        is_null($triggerId) && $triggerId = $this->_task_db->getVarByCondition('data_db_map', sprintf('Fdb_name=\'%s\' AND Ftable_name=\'%s\'', $dbname, $table), 'Fid');
        if (!empty($triggerId))
        {
            $recordField = ($action != 'd') ? 'Frecord_id' : 'Frelated_id';
            $time = $this->_task_db->get_var("SELECT UNIX_TIMESTAMP(NOW())");
            $batchData = array();
            $data = array('Fmap_id' => $triggerId, 'Faction' => trim($action), $recordField => null, 'Ftime' => $time);
            foreach ($ids as $id)
            {
                $data[$recordField] = $id;
                $batchData[] = $data;
                $data[$recordField] = null;
            }

            return $this->_task_db->batchInsertData('data_trigger', array('Fmap_id', 'Faction', $recordField, 'Ftime'), $batchData);
        }
    }

    public function getTriggerId($dbname, $table, $triggerType=self::DATA_TRIGGER)
    {
        if ($this->triggerEnable)
        {
            return $this->_task_db->getVarByCondition('data_db_map', sprintf('Fdb_name=\'%s\' AND Ftable_name=\'%s\' AND Ftrigger_type=%s',$dbname, $table, $triggerType), 'Fid');
        }
        return null;
    }

    public function addOrGetTriggerId($dbname, $table)
    {
        $triggerId = $this->getTriggerId($dbname, $table);
        if (empty($triggerId))
        {
            return $this->_task_db->insertTable('data_db_map', array('Fdb_name'=>$dbname, 'Ftable_name'=>$table));
        }
        return $triggerId;
    }

    public function deleteTriggerId($dbname, $table)
    {
        return $this->_task_db->delRowByCondition2('data_db_map', sprintf('Fdb_name=\'%s\' AND Ftable_name=\'%s\'',$dbname, $table));
    }
    public function insertRelationRecord($dbname, $table, $uid, $objUid, $action)
    {
        if (in_array($action, $this->actionEnum))
        {
            $mapId = $this->getTriggerId($dbname, $table, self::DATA_RELATION_TRIGGER);
            if (!empty($mapId))
            {
                return $this->_task_db->insertTable('data_relation_trigger', array('Fmap_id'=>$mapId, 'Fuid'=>$uid, 'Fobj_uid'=>$objUid, 'Faction'=>trim($action), 'Ftime'=>time()));
            }
        }
        return null;
    }
}