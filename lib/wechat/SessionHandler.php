<?php

class WContact_Session_Handler
{
    const SESSION_TYPE_MOBILE = 1;
    const SESSION_TYPE_WEB = 0;

	protected $db;
    protected $type; // 1 mobile. 0 web
    
    
	public function __construct($type)
	{
		$this->db = DataCenter::getDb('web');
        $this->type=$type;
	}
// 	protected $_table = null;
// 	protected $_session_left_time = 0;
	public function read($name)
	{
        $condition = HaloPdo::condition('Fsid=?',$name);
        $sessionRow = $this->db->getRowByCondition('account_session', $condition, '*',  MemCacheBase::TABLE_ACCOUNT_SESSION, $name);
        $data = null;
        if($sessionRow)
            $data = $sessionRow['Fdata'];
        
		if($data)
        {
            if (strpos($data, 'Auth|') !== false)
            {
                $session = unserialize(substr($data, 5));

                $ret = '';
                foreach($session as $k=>$v)
                {
                    $ret .= sprintf('%s|%s', $k, serialize($v));
                }

                return $ret;
            }

            return $data;
        }
		else
			return '';
	}

	public function write($name, $data)
	{
		$session = $this->unserializesession($data);

        $uid = 0;
        if (isset($session['Auth']))
        {
            $uid = $session['Auth']['uid'];
        }
        else
        {
            $uid = isset($session['uid']) ? $session['uid'] : 0;
            $data = sprintf('Auth|%s', serialize($session));
        }

        $fields = array('Fsid'=>$name,'Fuid'=>$uid,'Fdata'=>$data,'Ftime'=>time(), 'Ftype'=>$this->type);
        $this->db->replaceTable('account_session', $fields);
        $this->db->setCacheData(MemCacheBase::TABLE_ACCOUNT_SESSION, $name, $fields);
	}
	public function destroy($name)
	{
        $this->db->invalidByTagAndId(MemCacheBase::TABLE_ACCOUNT_SESSION, $name);
		$condition = HaloPdo::condition('Fsid=?',$name);
		if($this->db->delRowByCondition2('account_session', $condition))
			return true;
		else
			return 0;
	}
	public function gc($maxlifetime)
	{
		$condition = HaloPdo::condition('Ftime<?',time() - $maxlifetime);
        $sids = $this->db->getColByCondition('account_session', $condition, 'Fsid');
        if(count($sids))
        {
            foreach($sids as $sid)
                $this->db->invalidByTagAndId(MemCacheBase::TABLE_ACCOUNT_SESSION, $sid);
        }
		$this->db->delRowByCondition2('account_session', $condition);
	}
	
	public function open($save_path, $name)
	{
		return true;
	}
	public function close()
	{
		return true;
	}
	public static function unserializesession($data)
	{
// echo 'unserializesession'.$name;
		if (strlen($data) == 0)
		{
			return array();
		}
		// match all the session keys and offsets
		preg_match_all('/(^|;|\})([a-zA-Z0-9_]+)\|/i', $data, $matchesarray, PREG_OFFSET_CAPTURE);
		$returnArray = array();
		
		$lastOffset = null;
		$currentKey = '';
		foreach($matchesarray[2] as $value)
		{
			$offset = $value[1];
			if (!is_null($lastOffset))
			{
				$valueText = substr($data, $lastOffset, $offset - $lastOffset);
				$returnArray[$currentKey] = unserialize($valueText);
			}
			$currentKey = $value[0];
			
			$lastOffset = $offset + strlen($currentKey) + 1;
		}
		$valueText = substr($data, $lastOffset);
		$returnArray[$currentKey] = unserialize($valueText);
		
		return $returnArray;
	}
}
