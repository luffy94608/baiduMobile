<?php

class Halo_UrlRewrite
{
    protected static $inited = false;
    protected static $websit = null;
    protected static $url = null;
    
    public static function startWith($str, $sub)
    {
        return (substr($str, 0, strlen($sub)) == $sub);
    }
    
    public static function doRewrite ($matches)
    {
    	$config = HaloEnv::get('config');
        $url = trim($matches[2]);
    	
        $id = $url ? $url[0] : "";
        if ($id == '/') 
        { 
            $url= self::$websit . $url;
        }
        else if($id == '#')
        {
            $url= self::$websit . self::$url . '#';
        }
        // debug
		else if($id && $config['debug'] == 1 &&
		        !self::startWith($url, 'http://') &&  
		        !self::startWith($url, 'javascript:') &&
		        !self::startWith($url, 'mailto:')) 
		{
// 		    Zend_Debug::dump($url, "Bad Href!");
		}
		
        return 'href="' . $url . '"';
    }

    public static function doCheckAnchor ($matches)
    {
        $content = $matches[1];
        
        if (! preg_match('/\btarget\s*=/i', $content))
        {
            $content .= ' target="_top"';
        }
        
        $content = preg_replace_callback('/\bhref\s*=\s*(\'|")([^\'"]+)(\'|")/i', 
                array( 'Halo_UrlRewrite', 'doRewrite' ), $content);
        
        return '<a ' . $content . ' >';
    }
    
    public function __construct()
    {
        if(!self::$inited)
        {
            self::$websit =  $_ENV['host'];
            self::$url = rtrim($_SERVER['REQUEST_URI'], '/') ;
            
            self::$inited = true;
        }
    }

    public function filter ($html)
    {
        if(!self::$websit)
        {
            return $html;
        }
        return preg_replace_callback('|\<a\s([^>]+)>|', array('Halo_UrlRewrite', 'doCheckAnchor'), $html);
    }
}

?>