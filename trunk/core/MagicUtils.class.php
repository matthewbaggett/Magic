<?php
class MagicUtils
{
	static $canonicalised_url = null;
	static public function canonical(){
		if(self::$canonicalised_url === null){
			// Work out which gets are being passed as non-url-parameters:
			$non_parameter_gets = $_SERVER['REQUEST_URI'];
			$non_parameter_gets = explode("?",$non_parameter_gets,2);
			$gets = array();
			if(isset($non_parameter_gets[1])){
				$non_parameter_gets = $non_parameter_gets[1];
				$non_parameter_gets = explode("&",$non_parameter_gets);
				foreach($non_parameter_gets as $non_parameter_get){
					$bits = explode("=",$non_parameter_get,2);
					$gets[$bits[0]] = $bits[1];
				}
			}
			
			// Sort the gets			
			ksort($gets);
			
			// Build the new string
			$gets_string = '';
			foreach($gets as $key => $value){
				$gets_string.= "&$key=$value";
			}
			$gets_string = trim($gets_string,'&');
			
			// Build the canonicalised URL
			if(isset($_SERVER['REDIRECT_URL'])){
				$redir = $_SERVER['REDIRECT_URL'];
			}else{
				$redir = $_SERVER['REQUEST_URI'];
			}
			
			$url = self::thisdomain() . $redir . '?' . $gets_string;
			$url = rtrim($url,'?');
			
			self::$canonicalised_url = $url;
		}
		//die("Canonicalised url: " . self::$canonicalised_url);
		return self::$canonicalised_url;	
	}
	
	static public function thisurl(){
		return self::thisdomain() . $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Return this domain.
	 */
	static public function thisdomain(){
		$url = "http://" . $_SERVER['HTTP_HOST'];
		return $url;
	}
	
	/**
	 * Redirect to canonical version
	 */
	static public function canonicalise(){
		$canonical_url = self::canonical();
		//echo "Redirect to $canonical_url";
		header("Location: $canonical_url",TRUE,301);
		exit;
	}
	
	/**
	 * Should we canonicalise this URL?
	 */
	static public function canonicalisationAppropriate(){
		if(count($_POST) > 0){
			return false;
		}
		return true;
	}

	/**
	 * Display fuzzy time. IE: "One week ago"
	 */
	static public function fuzzyTime($time)
    {
        //echo $time." is: ";
        
        define('NOW', time());
        define('ONE_MINUTE', 60);
        define('ONE_HOUR', 3600);
        define('ONE_DAY', 86400);
        define('ONE_WEEK', ONE_DAY * 7);
        define('ONE_MONTH', ONE_WEEK * 4);
        define('ONE_YEAR', ONE_MONTH * 12);

        // sod = start of day :)
        $sod = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $sod_now = mktime(0, 0, 0, date('m', NOW), date('d', NOW), date('Y', NOW));

        // used to convert numbers to strings
        $convert = array(1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven');

        // today
        if ($sod_now == $sod) {
            if ($time > NOW - (ONE_MINUTE * 3)) {
                return 'just a moment ago';
            } else if ($time > NOW - (ONE_MINUTE * 7)) {
                return 'a few minutes ago';
            } else if ($time > NOW - (ONE_HOUR)) {
                return 'less than an hour ago';
            }
            return 'today at ' . date('g:ia', $time);
        }

        // yesterday
        if (($sod_now - $sod) <= ONE_DAY) {
            if (date('i', $time) > (ONE_MINUTE + 30)) {
                $time += ONE_HOUR / 2;
            }
            return 'yesterday around ' . date('ga', $time);
        }

        // within the last 5 days
        if (($sod_now - $sod) <= (ONE_DAY * 5)) {
            $str = date('l', $time);
            $hour = date('G', $time);
            if ($hour < 12) {
                $str .= ' morning';
            } else if ($hour < 17) {
                $str .= ' afternoon';
            } else if ($hour < 20) {
                $str .= ' evening';
            } else {
                $str .= ' night';
            }
            return $str;
        }

        // number of weeks (between 1 and 3)...
        if (($sod_now - $sod) < (ONE_WEEK * 3.5)) {
            if (($sod_now - $sod) < (ONE_WEEK * 1.5)) {
                return 'about a week ago';
            } else if (($sod_now - $sod) < (ONE_DAY * 2.5)) {
                return 'about two weeks ago';
            } else {
                return 'about three weeks ago';
            }
        }

        // number of months (between 1 and 11)...
        if (($sod_now - $sod) < (ONE_MONTH * 11.5)) {
            for ($i = (ONE_WEEK * 3.5), $m = 0; $i < ONE_YEAR; $i += ONE_MONTH, $m++) {
                if (($sod_now - $sod) <= $i) {
                    return 'about ' . $convert[$m] . ' month' . (($m > 1) ? 's' : '') . ' ago';
                }
            }
        }

        // number of years...
        for ($i = (ONE_MONTH * 11.5), $y = 0; $i < (ONE_YEAR * 10); $i += ONE_YEAR, $y++) {
            if (($sod_now - $sod) <= $i) {
                return 'about ' . $convert[$y] . ' year' . (($y > 1) ? 's' : '') . ' ago';
            }
        }

        // more than ten years...
        return 'more than ten years ago';
    }


    static public function generate_password($length = 9, $strength = 0)
    {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';
        if ($strength & 1) {
            $consonants .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($strength & 2) {
            $vowels .= "AEUY";
        }
        if ($strength & 4) {
            $consonants .= '23456789';
        }
        if ($strength & 8) {
            $consonants .= '@#$%';
        }

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password;
    }

    static public function redirect($controller, $action = '')
    {
        header("Location: " . rtrim(MagicApplication::GetInstance()->page->site->sys_root,"/") . "/{$controller}/{$action}");
        exit;
    }

    static public function get_php_binary()
    {
        $potential_php_bins = array('php-5.3', 'php');
        $php_to_run = '';
        foreach ($potential_php_bins as $potential_php_bin) {
            if (!strlen(trim($php_to_run)) > 0) {
                $php_to_run = trim(`/usr/bin/which $potential_php_bin`);
            }
        }
        if (!strlen(trim($php_to_run)) > 0) {
            $php_to_run = $_ENV['_'];
        }
        if (!strlen(trim($php_to_run)) > 0) {
            throw new MagicException("Cannot find the PHP binary");
        }
        return $php_to_run;
    }

    static public function sort_objects(Array &$objects, $prop = "id", $direction = "DESC")
    {
        {
            return usort($objects, function($a, $b) use ($prop, $direction)
                {
                    $call = "get_{$prop}";
                    if ($direction == "DESC") {
                        return $a->$call() < $b->$call() ? 1 : -1;
                    } else {
                        return $a->$call() > $b->$call() ? 1 : -1;
                    }
                });
        }
    }

    /**
     * List a directory
     * @param  string $directory the directory to list
     * @return array
     */
    static public function get_directory_list($directory)
    {
        if (is_dir($directory)) {
            $directory = rtrim($directory, '/') . "/";
            $results = array();
            $handler = opendir($directory);
            while ($file = readdir($handler)) {
                if ($file != "." && $file != ".." && $file != '.svn') {
                    $results[] = $directory . $file;
                }
            }
            closedir($handler);
            return (array)$results;
        } else {
            return array();
        }
    }

    /**
     * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
     * @param    string   $str    String in camel case format
     * @return    string            $str Translated into underscore format
     */
    static public function from_camel_case($str)
    {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     * @param    string   $str                     String in underscore format
     * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
     * @return   string                              $str translated into camel caps
     */
    static public function to_camel_case($str, $capitalise_first_char = false)
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }

    static public function serialize($o)
    {
        return serialize($o);
    }

    static public function unserialize($o)
    {
        return unserialize($o);
    }


    static public function faerie($message, $action = "magic", $mood = "friendly")
    {
        $action = explode(" ", $action);
        array_walk($action, function(&$item, $key)
            {
                $item = ucfirst($item);
            });
        $action = implode(" ", $action);

        $message = explode("\n", $message);
        $message = array_filter($message);
        $max_length = 0;
        foreach ($message as $message_line) {
            $trimmed_message_line = trim($message_line);
            if (strlen($trimmed_message_line) > $max_length) {
                $max_length = strlen($trimmed_message_line);
            }
            $trimmed_message_lines[] = $trimmed_message_line;
        }
        $response_lines[] = "/*";
        foreach ($trimmed_message_lines as $message_line) {
            $response_lines[] = " * {$message_line}";
        }
        $response_lines[] = " * ";
        $response_lines[] = " * " . str_pad("The {$mood} {$action} Faerie", $max_length, " ", STR_PAD_LEFT);
        $response_lines[] = " */";

        return implode("\n", $response_lines) . "\n";
    }

    static public function get_cli_flags($argv = null)
    {
        if ($argv == null) {
            global $argv;
        }
        unset($argv[0]);
        if (count($argv) > 0) {
            foreach ($argv as $argument) {
                if (substr($argument, 0, 2) == "--") {
                    $argument = substr($argument, 2);
                    if (strpos($argument, "=") !== FALSE) {
                        $flag_bits = explode("=", $argument, 2);
                        $flag = $flag_bits[0];
                        $value = $flag_bits[1];
                        $flags[$flag] = $value;
                    } else {
                        $flags[$argument] = TRUE;
                    }
                }
            }
        }
        return $flags;
    }

    static public function get_cli_flag($flag)
    {
        $flags = self::get_cli_flags();
        if (isset($flags[$flag])) {
            return $flags[$flag];
        } else {
            return NULL;
        }
    }

    static public function has_cli_flag($flag)
    {
        if (self::get_cli_flag($flag) !== NULL) {
            return TRUE;
        }
        return FALSE;
    }

    static public function is_unique($table, $column, $value)
    {
        $frequency = MagicQuery::Factory($table, "SELECT")
                ->addColumn("COUNT (id) as freq")
                ->addWhere($column, "=", $value)
                ->execute_single_value();
        if ($frequency == 0) {
            return true;
        } else {
            return false;
        }
    }

    static public function get_applications(){
        $applications = MagicUtils::get_directory_list(ROOT."/application");
        foreach($applications as &$application){
            $application_name = end(explode("/",$application));
            switch($application_name){
                case 'Exception':
                case 'gen':
                case 'temp':
                    $application = '';
                    break;
                default:
                    $application = $application_name;
            }
        }
        return array_filter($applications);
    }
    static public function timeDifference($first,$last){
    	$time_difference = abs($last - $first); 
    	return self::rel_time($time_difference);
    }
    static private function rel_time($diff)
	 {
	
	  $units = array
	  (
	   "year"   => 29030400, // seconds in a year   (12 months)
	   "month"  => 2419200,  // seconds in a month  (4 weeks)
	   "week"   => 604800,   // seconds in a week   (7 days)
	   "day"    => 86400,    // seconds in a day    (24 hours)
	   "hour"   => 3600,     // seconds in an hour  (60 minutes)
	   "minute" => 60,       // seconds in a minute (60 seconds)
	   #"second" => 1         // 1 second
	  );
	
	  
	  
	
	  foreach($units as $unit => $mult)
	   if($diff >= $mult)
	   {
	    $and = (($mult != 1) ? ("") : ("and "));
	    $output .= ", ".$and.intval($diff / $mult)." ".$unit.((intval($diff / $mult) == 1) ? ("") : ("s"));
	    $diff -= intval($diff / $mult) * $mult;
	   }
	  
	  $output = substr($output, strlen(", "));
	
	  return $output;
	 }
    
}