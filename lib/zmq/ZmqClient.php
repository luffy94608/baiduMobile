<?php

class ZmqClient
{
    private $host;  
    private $errmsg;
    
    public function __construct($host='127.0.0.1:5555')  
    {
        $this->host=$host;
    }
     
    // false 
    public function send ( $request , $timeout=5000 , $retry = 2 )
    {
        if (!class_exists('ZMQContext'))
        {
            $this->errmsg = 'Not found ZMQContext';
            return false;
        }
    	$context = new ZMQContext();
    	$client = $this->getClientSocket($context);
    	
    	$retries_left=$retry;
    	$read = $write = array();

    	while ($retries_left)
    	{
    		$client->send($request);
    		$expect_reply = true;
    		while ($expect_reply)
    		{
    			$poll = new ZMQPoll();
    			$poll->add($client, ZMQ::POLL_IN);
    			$events = $poll->poll($read, $write, $timeout);
    			if ($events > 0)
    			{
    				$reply = $client->recv();
    				$retries_left = $retry;
    				$expect_reply = false;
    				return $reply;
    			}
    			elseif (--$retries_left == 0)
    			{
    				$this->errmsg = 'server seems to be offline';
    				break;
    			}
    			else
    			{
    				$this->errmsg = 'no response from server, retrying...';
    				$client = $this->getClientSocket($context);
    				$client->send($request);
    			}
    		}   		
    	}
        return false;
    }
    
    
    public function error ()
    {
        return  $this->errmsg;
    }
    
    private function getClientSocket(ZMQContext $context)   
    {
        $client = new ZMQSocket($context,ZMQ::SOCKET_REQ);    
        $client->connect($this->host);    
        $client->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);   
        return $client;
    }  
}
