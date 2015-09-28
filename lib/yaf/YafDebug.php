<?php

define('YAF_LOG_LEVEL_DEBUG', 1);
define('YAF_LOG_LEVEL_ERROR', 5);

class YafDebug
{
    public static $LOG_LEVEL;
    public static $LOG_BASE_DIR;

    public static function error($v)
    {
        if(YAF_LOG_LEVEL_ERROR >= self::$LOG_LEVEL)
        {
            $traces = debug_backtrace(0, 1);
            $invoker = $traces[0];
            self::write($v, $invoker['file'], $invoker['line']);
        }
    }

    public static function log($v)
    {
        if(YAF_LOG_LEVEL_DEBUG >= self::$LOG_LEVEL)
        {
            $traces = debug_backtrace(0);
            $invoker = $traces[0];
            self::write($v, YAF_LOG_LEVEL_DEBUG, $invoker['file'], $invoker['line']);
        }
    }
    public static function dump($var, $label=null, $echo=true)
    {
        if (YAF_LOG_LEVEL_DEBUG < self::$LOG_LEVEL) return;
        
        // format the label
        $label = ($label===null) ? '' : rtrim($label) . ' ';

        // var_dump the variable into a buffer and keep the output
        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        // neaten the newlines and indents
        $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
//        if (self::getSapi() == 'cli') {
//            $output = PHP_EOL . $label
//                . PHP_EOL . $output
//                . PHP_EOL;
//        } else {
        if(!extension_loaded('xdebug')) {
            $output = htmlspecialchars($output, ENT_QUOTES);
        }

        $output = '<pre>'
            . $label
            . $output
            . '</pre>';
//        }

        if ($echo) {
            echo($output);
        }
        return $output;
    }

    private static function write(&$info , $level, $file = '', $line='')
    {
        if (strlen ($file) > 0)
            $file = substr($file, strlen( $_SERVER['DOCUMENT_ROOT']));

        $levelMsg = 'DEBUG';

        if($level == YAF_LOG_LEVEL_ERROR)
            $levelMsg = 'ERROR';

        $time = date('H:i:s');
        $info = print_r($info, true);
        $message = sprintf("%s - [%s] - %s:%s%s\r\n",$time, $levelMsg, $file, $line, $info);

        $date = date('Y-m-d');
        $hour = date('H');
        $path = sprintf('%s/%s', self::$LOG_BASE_DIR, $date);

        ensureFilePath($path,true);
        $path =  sprintf('%s/%d.log', $path,$hour);
        file_put_contents($path , $message, FILE_APPEND);
    }
}