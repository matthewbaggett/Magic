<?php 
	class MagicCache
	{
		static private $tc = null;

		const FILE_STORAGE_LOCATION = "cache/";
		static private $use_apc = false;

		static public function init ()
		{
			if (USE_GEN_TO_APC && function_exists("apc_fetch")) {
				MagicCache::$use_apc = true;
			} else {
				MagicCache::$use_apc = false;
				if (!is_dir(DIR_TEMP . MagicCache::FILE_STORAGE_LOCATION)) {
                    //echo "Dir: " . DIR_TEMP . MagicCache::FILE_STORAGE_LOCATION;
					if(!mkdir(DIR_TEMP . MagicCache::FILE_STORAGE_LOCATION)){
                        throw new MagicException("Cannot make cache directory in ".DIR_TEMP . MagicCache::FILE_STORAGE_LOCATION);
                    }
				}
			}
			MagicCache::$tc = new MagicCache();
		}

		static public function Factory ()
		{
			if (MagicCache::$tc === null) {
				MagicCache::init();
			}
			return MagicCache::$tc;
		}

		static public function clear ()
		{
			MagicLogger::log("Forced regeneration");
			if (MagicCache::$use_apc) {
				apc_clear_cache("user");
				apc_clear_cache("opcode");
			} else {
				MagicCache::delete_directory(MagicCache::FILE_STORAGE_LOCATION . APPNAME . "/");
			}
		}

		/**
		 * Convienence function so we can do MagicCache::get($foobar) rather than MagicCache->foobar;
		 * @param $key Key of item to be returned
		 * @returns Cached object
		 */
		public function get ($key)
		{
			return $this->$key;
		}

		/**
		 * Convienence function so we can do MagicCache::set($foobar,$value) rather than MagicCache->foobar = $value;
		 * @param $key Key of item to be cached
		 * @param $value Value of object to be stored
		 * @returns nothing
		 */
		public function set ($key, $value)
		{
			$this->$key = $value;
		}

		public function __get ($key)
		{
			if (MagicCache::$use_apc) {
				return unserialize(apc_fetch($key));
			} else {
				return unserialize($this->file_get($key));
			}
		}

		public function __set ($key, $value)
		{
			$value = serialize($value);
			if (MagicCache::$use_apc) {
				return apc_store($key, $value);
			} else {
				return $this->file_set($key, $value);
			}
		}

		public function file_get ($key)
		{
			$file_path = MagicCache::FILE_STORAGE_LOCATION . APPNAME . "/" . $key;
			if (file_exists($file_path)) {
				return file_get_contents($file_path);
			} else {
				return FALSE;
			}
		}

		public function file_set ($key, $value)
		{
			$directory = MagicCache::FILE_STORAGE_LOCATION . APPNAME . "/";
			if (!file_exists($directory)) {
				if (!@mkdir($directory, 0777, true)) {
					throw new MagicException("Cannot make directory: {$directory}");
				} else {
					chmod($directory, 0777);
				}
			} else {
				if (!is_writable($directory)) {
					throw new MagicException("Cannot write to {$directory}");
				}
			}
			return file_put_contents($directory . $key, $value);
		}

		static private function delete_directory ($dirname)
		{
			if (is_dir($dirname)) {
				$dir_handle = opendir($dirname);
			}
			if (isset($dir_handle)) {
				if (!$dir_handle) {
					return false;
				}
			} else {
				return false;
			}
			while ($file = readdir($dir_handle)) {
				if ($file != "." && $file != "..") {
					if (!is_dir($dirname . "/" . $file))
						unlink($dirname . "/" . $file);
					else
						MagicCache::delete_directory($dirname . '/' . $file);
				}
			}
			closedir($dir_handle);
			rmdir($dirname);
			return true;
		}
	}

	function magic_cache_get ($key)
	{
		return MagicCache::Factory()->get($key);
	}

	function magic_cache_set ($key, $value)
	{
		return MagicCache::Factory()->set($key, $value);
	}
