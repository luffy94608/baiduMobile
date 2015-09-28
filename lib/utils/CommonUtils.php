<?php

class CommonUtils
{
    public static function arrayToMap ($data, $srcTag, $desTag)
    {
        $res = array();
        if (! $data || count($data) == 0)
            return $res;
        
        foreach ($data as $item) 
        {
            $res[$item[$srcTag]] = $item[$desTag];
        }
        return $res;
    }
    
    public static function arrayPushFront($array, $value, $key = 0)
    {
        $res = array($key => $value);
        foreach ($array as $key => $value) 
        {
            $res[$key] = $value;
        }
        return $res;
    }
    
    public static function saveData($array, $key, $default)
    {
        if(isset($array[$key]))
            return $array[$key];
        else 
            return $default;
    }
    public static  function cutText($str,$limit)
    {
    	$str = SecurityUtils::unescape($str);
    	$length = self::measureText($str);
    	if($length>$limit)
    	{
    		$suffix = '...';
    		$str = mb_strcut($str, 0, ceil($limit*3/2), 'utf-8');
    		while(true)
    		{
    			$length = self::measureText($str.$suffix);
    			if($length-3>$limit)
    			{
    				$charLength = mb_strlen($str,'utf-8');
    				$str = mb_substr($str,0,$charLength-1,'utf-8');
    			}
    			else
    				break;
    		}
    		return SecurityUtils::escape($str.$suffix);
    	}
    	else
	    	return SecurityUtils::escape($str);
    }
    public static  function measureText($str)
    {
    	$length = ceil((strlen($str) + mb_strlen($str,'UTF8')) / 2);
    	return $length;
    }
    
    public static function wrapPostDraftContent(&$posts)
    {
    	if(!empty($posts))
    	{
    		foreach ($posts as $k => &$v)
    		{
    			$content = $v['content'];
    			if ( !isEmptyString( $content ))
    			{
    				$content = SecurityUtils::extract($content);
    				$richText = self::getRichTextStrByStr($v['content']);
    				$v['draftContent'] = $richText.$content;
    			}
    		}
    	}
    }
    
    public static function getRichTextStrByStr($str)
    {
    	$img = strpos($str, '<img');
    	$iframe = strpos($str, '<iframe');
    	$embed = strpos($str, '<embed');
    	$application = strpos($str, 'application/x-shockwave-flash');
    		
    	$richText = '';
    	if(!($img === false))
    		$richText .= '(图片)';
    	if(!($iframe === false && ($embed === false || $application === false)))
    		$richText .= '(视频)';
    	
    	return $richText;
    }
    
    public static function userIndentity ( $user , $glue=', ' )
    {
        $identity = $user['identity'];
        if ( isEmptyString($identity) )
        {
            $org = $user['org'];
            if ( isEmptyString($org) )
            {
                $org = '';
            }
            $job = $user['job'];
            if ( isEmptyString($job)  )
            {
                $job = '';
            }
            $identity = $org.$glue.$job;
            if ( $identity == $glue)
            {
                $identity = '';
            }
            $user['identity'] = $identity;
        }
        else
        {
            if ( $glue != ', ')
                $identity = str_replace(', ', '<br/>', $identity );           
        }
        return $identity;
    }
    
    public static function concat ( $str1, $str2, $glue=', ' )
    {
        $str = '';
        if ( !isEmptyString($str1))
        {
            $str = $str1;
        }
        if ( !isEmptyString($str2) )
        {
            if ( $str != '' )
            {
                $str = $str.$glue.$str2;
            }
            else
            {
                $str = $str2;
            }
        }          
        return $str;
    }

    public static function callStack()
    {
        $e = new Exception;
        haloDump($e->getTraceAsString());
        haloDie();
    }

    public static function addParam($url, $key, $value)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if( $query )
            $url .= sprintf('&%s=%s', $key, $value);
        else
            $url .= sprintf('?%s=%s', $key, $value);
        return $url;
    }

    public static function addAutoJumpParam($url)
    {
        return self::addParam($url, 'auto_jump', '1');
    }
}
