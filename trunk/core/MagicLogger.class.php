<?php 
class MagicLogger
{
    public static $cli_offset;

    public static $log;

    static public function init()
    {
        if (PHP_SAPI != 'cli') {
            fb::setEnabled(true);
        } else {
            fb::setEnabled(false);
        }
        MagicLogger::log("MagicLogger initialised");
    }

    static public function log($Object, $Label = null)
    {
        if ($Label !== null) {
            $Label = "{$Label}: ";
        }
        $spaces = str_repeat(" ", (2 * MagicLogger::$cli_offset));
        $log_item = array('time' => time(), 'message' => $spaces . $Label . $Object);
        self::$log[] = $log_item;
        error_log(MagicLogger::generate_log_line($log_item));
        if (PHP_SAPI == 'cli') {
            //echo MagicLogger::generate_log_line($log_item) . "\n";
            return false;
        } else {
//            echo MagicLogger::generate_log_line($log_item) . "<br />\n";
            return fb::log($Object, $Label);
        }

    }

    static public function log_array(Array $array)
    {
        foreach ($array as $log_line) {
            self::log($log_line);
        }
    }

    static public function get_log()
    {
        foreach ((array)self::$log as $log_item) {
            $rows[] = MagicLogger::generate_log_line($log_item);
        }
        return implode("\n", (array)$rows);
    }

    static public function generate_log_line($log_item)
    {
        return "[" . date("H:i:s", $log_item['time']) . "] " . $log_item['message'];
    }

    static public function group($Name, $show_open = false)
    {
        if (PHP_SAPI == "cli") {
            MagicLogger::log($Name);
            MagicLogger::$cli_offset++;
        } else {
            $Options = array();
            if ($show_open === false) {
                $Options['Collapsed'] = true;
            }
            fb::group($Name, $Options);
        }
    }

    static public function groupEnd()
    {
        if (PHP_SAPI == "cli") {
            MagicLogger::$cli_offset--;
        } else {
            fb::groupEnd();
        }
    }
}
