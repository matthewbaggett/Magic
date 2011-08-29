<?php
class MagicAutoloader
{
	const OBJECT_MAP_FILE = "/config/objects.map.yml";

	public $class_map;

	public function __construct ()
	{
		$this->class_map = Spyc::YAMLLoad(ROOT . self::OBJECT_MAP_FILE);
		spl_autoload_register(array($this, 'loader'));
	}

	private function loader ($class_name){
		if (defined("APPNAME")) {
			$application_path = ROOT . "/application/" . APPNAME . "/objects/{$class_name}.class.php";
		} else {
			$application_path = '';
		}
		
		//Merge in the plugin maps
		foreach((array) MagicApplication::$config->raw['Plugins'] as $plugin_name => $plugin_parameters){
			$plugin_paths[] = ROOT . "/plugins/{$plugin_name}/objects/{$class_name}.class.php";
		}
		$shared_objects_path = ROOT . "/objects/{$class_name}.class.php";
		$generated_path = ROOT . sprintf("/application/%s/gen/php/", APPNAME) . "{$class_name}.class.php";
		$core_path = ROOT . "/core/{$class_name}.class.php";

		$path_to_require = NULL;
		//Load it from the application
		if (!empty($application_path) && file_exists($application_path)) {
			$path_to_require = $application_path;
			MagicLogger::log("loader: Application");
		}
		//Load from plugins
		if(!$path_to_require){
			if(count($plugin_paths) > 0){
				foreach($plugin_paths as $plugin_path){
					if(!$path_to_require && file_exists($plugin_path)){
						$path_to_require = $plugin_path;
						MagicLogger::log("loader: Plugin");
					}
				}
			}
		}
		//Load from shared objects
		if (!$path_to_require && file_exists($shared_objects_path)) {
			$path_to_require = $shared_objects_path;
			MagicLogger::log("loader: Shared objects");
		}
		//Load it from the map
		if (!$path_to_require && isset($this->class_map[$class_name])) {
			$path_to_require = ROOT . $this->class_map[$class_name];
			MagicLogger::log("loader: Map");
		}
		//Could it be generated?
		if (!$path_to_require && file_exists($generated_path)) {
			$path_to_require = $generated_path;
			MagicLogger::log("loader: Generated");
		}
		//Last ditch attempt to find the file...
		if (!$path_to_require && file_exists($core_path)) {
			$path_to_require = $core_path;
			MagicLogger::log("loader: Core");
		}
		if(!$path_to_require){
			//print_r($this->class_map);
			//die("arrrgh");
			//return false;
			throw new MagicException(
					"Class '{$class_name}' cannot be found in this application.\n" .
					"I've looked in the following locations: \n" .
					"Application: {$application_path}\n" .
					"Plugin: ".implode(", ",$plugin_paths)."\n".
					"Generated: {$generated_path}\n" .
					"Core: {$core_path}\n\n"
			);
		}
		require_once($path_to_require);
	}
}

$autoloader = new MagicAutoloader();
