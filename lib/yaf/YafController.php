<?php

const SHARE_URL_AES_KEY='Msu8sgR3zKJicm';

class YafController extends Yaf_Controller_Abstract{
    protected $uid;
    protected $openId;
    /**
     * @var Logger
     */
    protected $logger = null;


    public  function init()
    {
        $session = Yaf_Session::getInstance();
        $this->uid = WeChatEnv::getUserId();
        $this->openId =  WeChatEnv::getOpenId();
        $clazz = get_class($this);
        $this->logger = Logger::LOG($clazz);

        $this->_view->setRequest($this->getRequest());

    }

    protected function getLegalParam($tag,$legalType='str',$legalList=array(),$default=null)
    {
        $param = $this->getRequest()->get($tag,$default);
        if($param!==null)
        {
            switch($legalType)
            {
                case 'eid': //encrypted id
                {
                    if($param)
                        return aesDecrypt(hex2bin($param), WAYGER_AES_KEY);
                    else
                        return null;
                    break;
                }
                case 'id':
                {
                    if (preg_match ('/^\d{1,20}$/', strval($param) ))
                    {
                        return strval($param);
                    }
                    break;
                }
                case 'time':
                {
                    return intval($param);
                    break;
                }
                case 'int':
                {
                    $val = intval($param);

                    if(count($legalList)==2)
                    {
                        if($val>=$legalList[0] && $val<=$legalList[1])
                            return $val;
                    }
                    else
                        return $val;
                    break;
                }
                case 'str':
                {
                    $val = strval($param);
                    if(count($legalList)==2)
                    {
                        if(strlen($val)>=$legalList[0] && strlen($val)<=$legalList[1])
                            return $val;
                    }
                    else
                        return $val;
                    break;
                }
                case 'trim_spec_str':
                {
                    $val = trim(strval($param));
                    if(!preg_match("/['.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$val))
                    {
                        if(count($legalList)==2)
                        {
                            if(strlen($val)>=$legalList[0] && strlen($val)<=$legalList[1])
                                return $val;
                        }
                        else
                            return $val;
                    }
                    break;
                }
                case 'enum':
                {
                    if(in_array($param,$legalList))
                    {
                        return $param;
                    }
                    break;
                }
                case 'array':
                {
                    if(count($legalList)>0)
                        return explode($legalList[0],strval($param));
                    else
                    {
                        if (empty($param))
                            return array();
                        return explode(',',strval($param));
                    }

                    break;
                }
                case 'json':
                {
                    return json_decode(strval($param),true);
                    break;
                }
                case 'raw':
                {
                    return $param;
                    break;
                }
                default:
                    break;
            }
        }
        return false;
    }
    protected function getPageParams()
    {
        $param['offset'] = $this->getLegalParam('offset', 'int', array(), 0);
        $param['length'] = $this->getLegalParam('length', 'int', array(), 20);

        return $param;
    }
    protected function getSharpParam()
    {
        $url = $_SERVER['REQUEST_URI'];
        $idx = stripos($url, "#");
        if($idx === false)
            return array();
        $param = array();
        $paramstr = substr($url, $idx);
        return $paramstr;

    }
    protected function checkReferer()
    {
        $refer = $_SERVER['HTTP_REFERER'];
        if(empty($refer))
            $this->inputRefererErrorResult();
        else
        {
            $legalHost = array('weibo.com', 'weibo.cn');
            $url = parse_url($refer);
            $result = false;
            foreach($legalHost as $v)
            {
                $pos = stripos($url['host'],$v);
                if($pos!==false)
                {
                    $result = true;
                    break;
                }
            }
            if($result===false)
                $this->inputRefererErrorResult();
            else
            {
                if($_REQUEST['trace_type']!='ajax')
                    $this->inputRefererErrorResult();
            }
        }
    }

    protected function getLegalParamArray($fields)
    {
        $params = array();
        foreach($fields as $f => $type)
        {
            $params[$f] = $this->getLegalParam($f, $type);
        }
        return $params;
    }

    protected function getRequestDate($year='year', $month='month', $day='day')
    {
        $y = $this->getLegalParam($year, 'int');
        $m = $this->getLegalParam($month, 'int');
        $d = $this->getLegalParam($day, 'int');
        return mktime(0, 0, 0, $m, $d, $y);
    }

    protected function inputUpgradeResult($result, $model)
    {
        $desc = $model->getErrorText($result['code']);
        echo json_encode(array('data'=>$result,'code'=>$result['code'], 'desc'=>$desc));
        die();
    }

    protected function inputResultRedirectUrl($url)
    {
        echo json_encode(array('redirect_url'=>$url,'code'=>301));
        die();
    }

    protected function inputResultRefreshCurrentUrl($url)
    {
        echo json_encode(array('redirect_url'=>$url,'code'=>3011));
        die();
    }

    protected function inputResult($data=null)
    {
        if ($data !== null)
            echo json_encode(array('data'=>$data,'code'=>0));
        else
        {
            echo json_encode(array('code'=>0,'status'=>array('login'=>'')));
        }
// 		}
        haloDie();
    }

    public function inputBackResult()
    {
        echo json_encode(array('code'=>302,));
        haloDie();
    }

    protected function inputBase64Result($data=null)
    {
        $data['base64'] = true;
        if (isset($data['html']))
        {
            $data['html'] = base64_encode($data['html']);
        }

        echo json_encode(array('data'=>$data,'code'=>0));
        haloDie();
    }

    protected function inputErrorResult($code,$model)
    {
        echo json_encode(array('code'=>$code,'desc'=>$desc));
        haloDie();
    }

    protected function inputErrorWithModelResult($code)
    {
//        $desc = ErrorCode::errorMsgByCode($code);
        $desc = ErrorUtil::getErrorText($code);
        echo json_encode(array('code'=>-400,'desc'=>$desc));
        haloDie();
    }

    protected function inputErrorCodeDesc($code,$desc)
    {
//        $desc = ErrorCode::errorMsgByCode($code);
//        $desc = $model->getErrorText($code);
        echo json_encode(array('code'=>$code,'desc'=>$desc));
        haloDie();
    }

    protected function inputHolloError(HolloHttpResult $res)
    {
        echo json_encode(array('code'=>$res->code,'desc'=>$res->errMsg));
        haloDie();
    }


    protected function inputParamErrorResult()
    {
        echo json_encode(array('code'=>-100,'desc'=>'param error'));
        haloDie();
    }

    protected function inputRefererErrorResult()
    {
        echo json_encode(array('code'=>-101,'desc'=>'referer error'));
        haloDie();
    }



    protected function inputResultJumpUrl($url = '')
    {
        echo json_encode(array('code'=>401,'url'=>$url));
        haloDie();
    }


    protected function inputErrorWithDesc($desc)
    {
        echo json_encode(array('code'=>410,'desc'=>$desc));
        haloDie();
    }

    protected function _forward($action,$controller='',$parameters=array())
    {
        $this->forward('Index', $controller, $action, $parameters);
    }

    protected function render($tpl, array $parameters = null)
    {
        $this->display($tpl, $parameters);
    }


    /**
     * 套件应用中生成分享url (suite 方式)
     */

    public function getUrlWithCorpId($url)
    {
//        if(stripos($url,'?')===false)
//        {
//            $enStr = '?corp_id='.WeChatEnv::encodeParams(WeChatEnv::getCorpAes(),WeChatEnv::$corpAesKey);
//        }
//        else
//        {
//            $enStr = '&corp_id='.WeChatEnv::encodeParams(WeChatEnv::getCorpAes(),WeChatEnv::$corpAesKey);
//        }
//        $shareUrl=$url.$enStr;
        return $url;
    }


}

