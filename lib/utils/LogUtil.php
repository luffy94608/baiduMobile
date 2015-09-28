<?php
/**
 * WContactLog
 * @author frank.xu
 * @date Dec 20, 2012 6:19:00 PM
 * @copyright  Copyright (c) 2012 Youlu
 * @version    1.0.0
 */

class  LogUtil
{
    private static $daemon = false;
    private static $debug = false;

    public static function setDaemon($daemon)
    {
        self::$daemon = $daemon;
    }

    public static function setDebug($debug)
    {
        self::$debug = $debug;
    }

    protected static function canLog()
    {
        if (self::$daemon)
            return true;

        return self::$debug;
    }

    public static function logHTMLHeader()
    {
        if (!LogUtil::canLog())
            return;
        echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';

    }

    public static function logHTMLFooter()
    {
        if (!LogUtil::canLog())
            return;
        echo '</body></html>';
    }

    public static function logObject($obj)
    {
        if (self::$daemon) {
            echo var_dump($obj);
            echo "\n";
        } else if (LogUtil::canLog()) {
            echo '<pre>';
            var_dump($obj);
            echo '</pre>';
        }
    }

    public static function logMsg($format, $args = null, $_ = null)
    {
        $varArray = func_get_args();

        if (self::$daemon) {
            $format = "[MSG]" . $format . "\n";
        } else if (LogUtil::canLog()) {
            $format = "<pre>" . $format . "</pre>";
        }
        $varArray[0] = $format;
        if(count($varArray) > 1)
            call_user_func_array('printf', $varArray);
        else
            echo $format;
    }



    public static function logQuery($db, $msg = '')
    {
        if (self::$daemon) {
            echo sprintf("</pre>%s [SQL] %s</pre>\n", $msg, $db->last_query);
        } else if (LogUtil::canLog()) {
            echo '<pre>' . $msg . '[SQL]' . $db->last_query . '</pre>';
        }
    }

    public static function logJSON($obj)
    {
        if (LogUtil::canLog()) {
            echo json_encode($obj);
        }
    }

    public static function logArrayAsTable($datas, $opptions = array('titles' => '', 'showNmber' => 0, 'width' => 800))
    {
        $keys = array_keys($opptions);
        $titles = in_array('titles', $keys) ? $opptions['titles'] : '';
        $showRowNumber = in_array('showNmber', $keys) ? $opptions['showNmber'] : 0;
        $width = in_array('width', $keys) ? $opptions['width'] : 800;

        if (!LogUtil::canLog())
            return;

        echo sprintf('<table border="1" cellpadding="0" cellspacing="0" width="%d" >', $width);

        if ($titles) {
            if ($showRowNumber) {
                $head = sprintf('<tr><th>%s</th><th>%s</th></tr>', 'Row', implode('</th><th>', $titles));
            } else {
                $head = sprintf('<tr><th>%s</th></tr>', implode('</th><th>', $titles));
            }
            echo $head;
        }

        $cnt = 1;
        foreach ($datas as $row) {
            if ($showRowNumber) {
                $row = sprintf('<tr align="center"><td>%s</td><td>%s</td></tr>', $cnt, implode('</td><td>', $row));
            } else {
                $row = sprintf('<tr align="center"><td>%s</td></tr>', implode('</td><td>', $row));
            }
            echo $row;
            $cnt++;
        }
        echo '</table>';
    }
}