<?php
class MagicDB
{
	public static $database;

	public static function Query ($sql)
	{
		if(self::$database instanceof MagicDatabase){
			return self::$database->query($sql);
		}else{
			throw new MagicException("Call to MagicDB::Query() before the database connection was instantiated!");
		}
	}

	/**
	 * Escape a text string. Wraps MagicDB::escape_string()
	 * @param string $text
	 * @return string;
	 */
	static public function escape ($text)
	{
		return MagicDB::escape_string($text);
	}

	/**
	 * Escape a text string. Wraps MagicDB::escape_string()
	 * @param string $text
	 * @return string;
	 */
	static public function escape_text ($text)
	{
		return MagicDB::escape_string($text);
	}

	/**
	 * Escape a text string.
	 * @param string $text
	 * @return string;
	 */
	static public function escape_string ($text)
	{
        if(is_array($text)){
            return self::escape_array($text);
        }
		$text = addslashes($text);
		return $text;
	}

	/**
	 * Escape booleans. Pretty obvious how.
	 * Yes I'm aware they're not strictly bools... Bah.
	 * @param boolean $b
	 * @return boolean;
	 */
	static public function escape_bool ($b)
	{
		if ($b === 0 || $b === FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * This function escapes numbers by simply making sure its an integer first.
	 * @param number $n
	 * @return number
	 */
	static public function escape_number ($n)
	{
		if (is_numeric($n)) {
			return $n;
		} else {
			return false;
		}
	}

	/**
	 * This was to escape an array of strings. Long story.
	 * @param array $arr
	 * @return array
	 */
	static public function escape_array ($arr)
	{
		$newarr = array();
		foreach ($arr as $key => $element) {
			$newarr[$key] = MagicDB::escape($element);
		}
		return $newarr;
	}
}