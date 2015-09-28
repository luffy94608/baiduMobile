<?php
class BeanstalkdUtil 
{
	
	const WCONTACT_TUBE = 'WContactTask';
	
	/**
	 *   beanstalkd  task type
	 */
	const  SEND_WEIBO = 1;
	const  SEND_EMAIL = 2;
	
	/**
	 * weibo task subtype
	 */
	const  SHARE_OPPORTUNITY_BY_WEIBO=1;
	const  SHARE_POST_BY_WEIBO=2;
	const  SHARE_POST_COMMENT_BY_WEIBO=3;
	const  SHARE_PROJECT_BY_WEIBO=4;
	const  SHARE_REVIEW_BY_WEIBO=5;
	const  SEND_WEIBO_NEW_NOTIFICATION=6;
	const  SHARE_SKILL_BY_WEIBO=7;
	const  SHARE_GUIDE_TO_WEIBO=8;
	
	
	/**
	 * email task  subtype
	 **/
	const  SEND_VERIFY_EMAIL =1;
	const  SEND_INVITE_EMAIL =2;
	const  SEND_RESUME_EMAIL =3;
	const  SHARE_OPPORTUNITY_BY_EMAIL =4;
	const  SHARE_POST_BY_EMAIL =5;
	const  SEND_GENERAL_NOTIFICATION =6;
	const  SEND_NEW_NOTIFICATION=7;
	const  SEND_SYSTEM_NOTIFICATION =8;
	const  SHARE_POST_COMMENT_BY_EMAIL =9;
	
	
	private $BeanstalkdHost;
	private $db;
	public function __construct()
	{
		$config = HaloEnv::get('config');
		$this->BeanstalkdHost = $config->beanstalkd->host.':'.$config->beanstalkd->port;
		$this->db = DataCenter::getDb('web');
		try {
		    $this->beanstalkd = new  Pheanstalk_Pheanstalk($this->BeanstalkdHost);
		}
		catch ( Exception $e ) {
		    Logger::ERROR('BeanstalkdUtil error ['.$e->getMessage().']',__FILE__,__LINE__,ERROR_LOG_FILE);
		}
	}
	
	public function  putJobToServer($data,$tube='WContactTask')
	{
	    try {		
    		$taskData=json_encode($data);

            $task_id = $this->db->insertTable('data_tasks', array('Ftype'=>$data['task_type'], 'Fsub_type'=>$data['sub_type'], 'Fdata'=>$taskData, 'Ftime'=>time()));
    		$data['task_id'] = $task_id;
    		$this->beanstalkd->useTube($tube);	
		    $this->beanstalkd->put(json_encode($data), 1024, 0, 120);	
		}
		catch ( Exception $e ) {
		    Logger::ERROR('BeanstalkdUtil error ['.$e->getMessage().']',__FILE__,__LINE__,ERROR_LOG_FILE);
		}	
	}
}