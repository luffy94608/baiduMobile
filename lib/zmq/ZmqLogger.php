<?php
$config = Yaf_Registry::get('config');
define('ZMQ_HOST',$config->zmq->logger->host);
define('ZMQ_PORT',$config->zmq->logger->port);

function getZmqLogger($platform = ZMQ_PLAT_FORM)
{
    global $logger;
    if(empty($logger))
    {
        $logger = new ZmqLogger($platform);
    }
    return $logger;
}

class ZmqLogger
{
    private $uid=-1;
    private $protocol=-1;
    private $type=-1;
    private $serialNum=-1;
    private $msg;
    private $platform;

    public function __construct($platform)
    {
        $this->serialNum = rand(100000,999999);
        $this->msg = array();
        $this->platform = $platform;
    }

    public function sendLog($uid,$protocol,$type,$msg = array())
    {
        if(count($msg)==0) return;

        $msg['platform'] = $this->platform;
        $msg['server'] = $_SERVER['SERVER_ADDR'];
        $msg['uri'] = $_SERVER['REQUEST_URI'];
        $msg['timestamp'] = time();
        $msg['refer']  = $_SERVER['HTTP_REFERER'];
    
        if($this->uid==-1 && $this->protocol==-1 && $this->type==-1)
        {
            $this->uid = $uid;
            $this->protocol = $protocol;
            $this->type = $type;
        }
        $msg['ip'] = get_cfg_var('local_addr');
        $this->msg[] =  $msg;
        $this->doSend();

    }

    public function doSend()
    {
        $config = Yaf_Registry::get('config');
        $enable = $config->zmq->logger->enable;
        if(!$enable)
            return;

        if(count($this->msg)==0) return;
        try
        {
            $context = new ZMQContext();
            $requester = new ZMQSocket($context,ZMQ::SOCKET_REQ);
            $conection = $requester->connect(sprintf('tcp://%s:%s',ZMQ_HOST,ZMQ_PORT));
            $requester->setSockOpt (ZMQ::SOCKOPT_LINGER, 0);
            $requester->send(json_encode(array('uid'=>$this->uid,'msg_body'=>json_encode($this->msg),'protocol'=>$this->protocol,'type'=>$this->type,'serial'=>$this->serialNum)),ZMQ::MODE_DONTWAIT);
            $read = $write = array();
            $poll = new ZMQPoll();
            $poll->add($requester, ZMQ::POLL_IN);
            $events = $poll->poll($read, $write, 1000);
            if ($events > 0)
            {
                $reply = $requester->recv();

                $this->reset();
                return $reply;
            }
            else
            {
                $this->reset();
                //do nothing
            }
        }
        catch (ZMQException $e)
        {
            $this->reset();
            //do nothing
        }
    }

    private function reset()
    {
        $this->msg = array();
        $this->uid = -1;
        $this->protocol = -1;
        $this->type = -1;
    }
}