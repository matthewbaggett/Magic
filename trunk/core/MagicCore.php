<?php
define("ROOT", realpath(dirname(__FILE__) . "/../") . "/");
if (file_exists("PHP/Beautifier.php")) {
   require_once('PHP/Beautifier.php');
}
require_once(ROOT . "lib/swift/swift_required.php");
require_once(ROOT . "lib/firephp/fb.php");
require_once(ROOT . "lib/krumo/class.krumo.php");
require_once(ROOT . "lib/spyc/spyc.php");
require_once(ROOT . "lib/smarty/Smarty.class.php");
require_once(ROOT . "lib/lesscssphp/lessc.inc.php");

require_once(ROOT . "core/MagicDefines.php");
require_once(ROOT . "core/MagicApplication.class.php");
require_once(ROOT . "core/MagicAutoloader.php");
require_once(ROOT . "core/MagicException.class.php");
require_once(ROOT . "core/PHP5.2_compat.class.php");
require_once(ROOT . "core/MagicCache.class.php");
require_once(ROOT . "core/MagicUtils.class.php");
require_once(ROOT . "core/MagicTranslate.class.php");

$core_files = MagicUtils::get_directory_list(ROOT . "/core");
foreach ($core_files as $core_file) {
   $class = basename($core_file);
   $class = str_replace(".class.php", "", $class);
   $class = str_replace(".php", "", $class);
   $core_map[$class] = "/core/" . basename($core_file);
}
//print_r($core_map);
$core_map_data = Spyc::YAMLDump($core_map);
//echo $core_map_data;exit;
file_put_contents(ROOT . "/config/objects.map.yml", $core_map_data);





