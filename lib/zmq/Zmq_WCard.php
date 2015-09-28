<?php
/**
 * Created by JetBrains PhpStorm.
 * User: frank.xu
 * Date: 7/30/13
 * Time: 5:07 PM
 * To change this template use File | Settings | File Templates.
 */

class ZmqWCard
{
    
    public  $ocr_zmq_host = null;
    public $ocr_zmq_port = null;
    public $response = null;
    public $errmsg = '';
    public $test = false;
    
    public function __construct()
    {
        $config = Yaf_Registry::get('config');

        $this->ocr_zmq_host = $config->ocr->zmq->host;
        $this->ocr_zmq_port = $config->ocr->zmq->port;
    }
    public function addCardTask($cardId, $path, $lang)
    {
        $config = Yaf_Registry::get('config');
        $priority = $config->ocr->zmq->priority;

        // 第三个参数(名片语言) 增加海外服务器信息, 占用十位, 语言占用各位, 中英日对应123 , 如 日本服务器发的中文名片, 此字段传31
        $third = 11;
        $cmd = sprintf("%d\n%s\n%d\n%d", $cardId, $path, $third, $priority);
        return $this->send($cmd);
    }
    public function send ( $request , $timeout=1000 , $retry = 1 )
    {
        if ( !class_exists('ZMQContext'))
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
            $b = $client->send($request);
            if (!$b)
            {
            }

            $expect_reply = true;

            while ($expect_reply)
            {
                $retryTime = ($retry - $retries_left + 1);

                $poll = new ZMQPoll();
                $poll->add($client, ZMQ::POLL_IN);
                $events = $poll->poll($read, $write, $timeout);
                if ($events > 0)
                {
                    $reply = $client->recv();
                    $this->response = $reply;
                    return true;

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
        
        if(!empty( $this->errmsg))
        {
        }
        
        return false;
    }

    private function getClientSocket(ZMQContext $context)
    {
        $url = sprintf('tcp://%s:%d', $this->ocr_zmq_host, $this->ocr_zmq_port);
        $client = new ZMQSocket($context, ZMQ::SOCKET_REQ);
        $client->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);
        $client->connect($url);
        return $client;
    }
}