<?php 

    class MagicObjectFactory {
        const OBJECT_DEFINITION_FILE = "/config/objects.definition.yml";
        const OBJECT_DEFINITION_APPLICATION_FILE = "/application/%s/objects.definition.yml";
        const OBJECT_GENERATION_OUTPUT_DIR = "/application/%s/gen/";
        const OBJECT_GENERATION_POST_RUN_SCRIPTS = "application/%s/sql/";

        private $object_builders;

        private $object_map;

        public function get_list_of_objects () {
            return array_keys($this->object_map);
        }

        public function __construct () {
            $this->object_map = Spyc::YAMLLoad(ROOT . self::OBJECT_DEFINITION_FILE);
            $this->object_application_map = Spyc::YAMLLoad(ROOT . sprintf(self::OBJECT_DEFINITION_APPLICATION_FILE, APPNAME));

            //Merge the application maps
            $this->object_map = array_merge_recursive($this->object_map, $this->object_application_map);

            //Merge in the plugin maps
            if(count(MagicApplication::$config->raw['Plugins']) > 0){
	            foreach(MagicApplication::$config->raw['Plugins'] as $plugin_name => $plugin_parameters){
	               $plugin_object_map_file = ROOT . "/plugins/{$plugin_name}/objects.definition.yml";
	               MagicLogger::log($plugin_object_map_file,"Plugin {$plugin_name} map file");
	               if(file_exists($plugin_object_map_file)){
	                  $plugin_object_map = (array) Spyc::YAMLLoad($plugin_object_map_file);
	                  $this->object_map = array_merge_recursive($this->object_map,$plugin_object_map);
	               }else{
	                  MagicLogger::log("Couldn't load object.definition.yml for {$plugin_name}");
	               }
	            }
            }

            //Sort the map by key
            ksort($this->object_map);

            //List all the builder files
            $this->object_builders["controller.app.gen.php"]         = "php/%sAppController.class.php";
            $this->object_builders["controller.core.gen.php"]        = "php/%sCoreController.class.php";
            $this->object_builders["controller.base.gen.php"]        = "php/%sBaseController.class.php";
            $this->object_builders["controller.concrete.gen.php"]    = "php/%sController.class.php";
            $this->object_builders["searcher.gen.php"]               = "php/%sSearcher.class.php";
            $this->object_builders["object.actionlog.gen.php"]       = "php/%sActionLogger.class.php";
            $this->object_builders["object.base.gen.php"]            = "php/%sBaseObject.class.php";
            $this->object_builders["object.core.gen.php"]            = "php/%sCoreObject.class.php";
            $this->object_builders["object.app.gen.php"]             = "php/%sAppObject.class.php";
            $this->object_builders["object.object.gen.php"]          = "php/%sObject.class.php";
            $this->object_builders["object.concrete.gen.php"]        = "php/%s.class.php";
            $this->object_builders["object.interface.gen.php"]       = "php/%sInterface.class.php";
            $this->object_builders["object.tests.gen.php"]           = "../temp/tests/%sTest.test.php";
            $this->object_builders["sql.construct.php"]              = "sql/construct/%s.construct.sql";
            $this->object_builders["sql.alter.php"]                  = "sql/alter/%s.alter.sql";
            $this->object_builders["sql.actionlog.php"]              = "sql/log/%s.actionlog.sql";
        }

        /**
         * Sanity check the application. Check all the objects match the schema
         */
        public function check () {
            $do_regen = false;
            if ($this->has_md5s_changed()) {
                $do_regen = true;
            }
            if (FORCE_REGEN) {
                $do_regen = true;
                MagicLogger::Log("Forced to regenerate.");
            }
            //exit;
            if ($do_regen) {
                $this->destroy_current_object_files();
                $this->regenerate();
				$use_sql = MagicUtils::get_cli_flag("no-sql") !== NULL?false:true;
				if($use_sql){
					$this->runSQL(ROOT . sprintf(MagicObjectFactory::OBJECT_GENERATION_OUTPUT_DIR, APPNAME) . "sql/construct/");
					$this->runSQL(ROOT . sprintf(MagicObjectFactory::OBJECT_GENERATION_OUTPUT_DIR, APPNAME) . "sql/alter/");
					$this->runSQL(ROOT . sprintf(MagicObjectFactory::OBJECT_GENERATION_OUTPUT_DIR, APPNAME) . "sql/log/");
					if (is_dir(ROOT . sprintf(MagicObjectFactory::OBJECT_GENERATION_POST_RUN_SCRIPTS, APPNAME))) {
						$this->runSQL(ROOT . sprintf(MagicObjectFactory::OBJECT_GENERATION_POST_RUN_SCRIPTS, APPNAME));
					}
				}
                $use_data = MagicUtils::get_cli_flag("no-data") !== NULL?false:true;
                if($use_data){
                    $listing = MagicUtils::get_directory_list(ROOT . "/data");
                    $app_listing = MagicUtils::get_directory_list(ROOT ."/application/".APPNAME."/data");
                    sort($listing); sort($app_listing);
                    $listing = array_merge((array) $listing, (array) $app_listing);
                    foreach($listing as $file){
                        MagicLogger::log("Running datafile: $file");
                        include_once($file);
                    }
                }

                $regen_count_setting = SettingSearcher::Factory()->search_by_system_name("REGEN_COUNT")->execute_one();
                $regen_count_setting->set_value((int) $regen_count_setting->get_value() + 1)->save()->reload();
                
                return FALSE;
            } else {
                //MagicLogger::log("No need to regenerate");
                return TRUE;
            }
        }

        private function has_md5s_changed () {
            $file_object_map = ROOT . self::OBJECT_DEFINITION_FILE;
            $file_application_map = ROOT . sprintf(self::OBJECT_DEFINITION_APPLICATION_FILE, APPNAME);
            if (magic_cache_get(__CLASS__ . "_OBJECT_MAP_MD5") != md5_file($file_object_map)) {
                MagicLogger::log("Object map changed");
                return true;
            }
            if (magic_cache_get(__CLASS__ . "_OBJECT_APPLICATION_MAP_MD5") != md5_file($file_application_map)) {
                MagicLogger::log("Application map {$file_application_map} changed ");
                return true;
            }
            foreach ($this->object_builders as $build_file => $output) {
                if (magic_cache_get("BUILDER_" . strtoupper($build_file)) != md5_file(ROOT . "/templates/system/" . $build_file)) {
                    MagicLogger::log("Builder {$build_file} has changed.");
                    return true;
                }
            }
            return false;
        }

        /**
         * Regenerate the application
         */
        private function regenerate () {
            MagicLogger::group("Regenerating Objects");
            //print_r($this->object_map);
            foreach ($this->object_map as $object_name => $object_definition) {
                MagicLogger::group("Regenerating $object_name", false);
                //MagicLogger::log("Definition for {$object_name}");
                $this->regenerate_class($object_name, $object_definition);
                MagicLogger::groupEnd();
            }
            //Lastly, update the MD5.
            magic_cache_set(__CLASS__ . "_OBJECT_MAP_MD5", $md5_schema = md5_file(ROOT . self::OBJECT_DEFINITION_FILE));
            magic_cache_set(__CLASS__ . "_OBJECT_APPLICATION_MAP_MD5", md5_file(ROOT . sprintf(self::OBJECT_DEFINITION_APPLICATION_FILE, APPNAME)));
            foreach ($this->object_builders as $build_file => $output) {
                magic_cache_set("BUILDER_" . strtoupper($build_file), md5_file(ROOT . "/templates/system/" . $build_file));
            }
            MagicLogger::groupEnd();
        }

        private function regenerate_class ($name, $definition) {
            foreach ($this->object_builders as $generator_file => $output_file) {
                $output_file = sprintf($output_file, $name);
                $this->regenerate_class_file($name, $definition, $generator_file, $output_file);
            }
        }

        private function regenerate_class_file ($name, $definition, $file, $output) {
            //MagicLogger::log("{$name}/{$file} - Begining to generate {$output}");
            ob_start();
            //Generate here.
            $cg = new MagicClassGenerator($name, $this->object_map, $definition);
            $cg->go($file);
            //OK STOP. HAMMERTIME^H^H^H^H^H^H^
            //Time to grab the buffer and poke it into a file~
            $class_contents = ob_get_clean();
            //MagicLogger::log("{$name}/{$file} - Generated {$output}");
            /*
                  * If PHP Beautifier is available, and we're configured to use it...
                  */
            if (class_exists("PHP_Beautifier", false) && USE_BEAUTIFIER) {
                MagicLogger::log("{$name}/{$file} - Passing through PHP_Beautifier");
                $oBeautifier = new PHP_Beautifier();
                error_reporting(ERROR_REPORTING_LEVEL);
                // Add another filter, with one parameter
                //$oBeautifier->addFilter('Pear',array('add_header'=>ROOT . "/templates/system/licence.txt"));
                // Set the indent char, number of chars to indent and newline char
                $oBeautifier->setIndentChar("\t");
                $oBeautifier->setIndentNumber(1);
                $oBeautifier->setNewLine("\n");
                // Define the input file
                $oBeautifier->setInputString($class_contents);
                // Process the file. DON'T FORGET TO USE IT
                $oBeautifier->process();
                $class_contents = $oBeautifier->get();
            }
            /*
                  * Decide what to do with the generated output
                  */
            if (GEN_TO_APC) {
                $output = str_replace(".class.php", "", $output);
                magic_cache_set("APC_CLASS_{$output}", $class_contents);
                MagicLogger::log("$name/$file - Generated " . strlen($class_contents) . " bytes of code AND DID EVIL WITH IT.");
            } else {
                
                $class_file = ROOT . sprintf(MagicObjectFactory::OBJECT_GENERATION_OUTPUT_DIR, APPNAME) . $output;
                if (!file_exists(dirname($class_file))) {
                    MagicLogger::log("Making file {$class_file}");
                    mkdir(dirname($class_file), 0777, true);
                    chmod(dirname($class_file), 0777);
                }
                @chmod(ROOT . sprintf(MagicObjectFactory::OBJECT_GENERATION_OUTPUT_DIR, APPNAME), 0777);
                if (is_writable(dirname($class_file))) {
                    file_put_contents($class_file, $class_contents);
                    @chmod($class_file, 0666);
                } else {
                    throw new MagicException("Cannot write into {$class_file}");
                }
                MagicLogger::log("$name/$file - Generated " . strlen($class_contents) . " bytes of code.");
            }
        }

        private function destroy_current_object_files () {
            return $this->delete_directory(ROOT . "/gen");
        }

        private function delete_directory ($dirname) {
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
                    if (!is_dir($dirname . "/" . $file)) unlink($dirname . "/" . $file); else
                        $this->delete_directory($dirname . '/' . $file);
                }
            }
            closedir($dir_handle);
            rmdir($dirname);
            return true;
        }

        public function runSQL ($sql_dir) {
            if (is_dir($sql_dir)) {
                $dir_handle = opendir($sql_dir);
            } else {
                throw new MagicException("Cannot find directory {$sql_dir}");
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
                    if (!is_dir($sql_dir . "/" . $file)) {
                        $files[] = $sql_dir . "/" . $file;
                    }
                }
            }
            sort($files);
            MagicLogger::group("SQL Executions");
            foreach ($files as $file) {
                $sql = file_get_contents($file);
                $result = MagicDB::Query($sql);
                MagicLogger::log($result, "Executed {$file}");
            }
            MagicLogger::groupEnd();
        }
    }
