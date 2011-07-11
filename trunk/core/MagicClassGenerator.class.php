<?php 

	class MagicClassGenerator
	{
		public function __construct ($name, $object_map, $definition)
		{
			$this->name = $name;
            $this->objectmap = $object_map;
			$this->definition = $definition;
		}

		public function go ($file = "object.default")
		{
//			error_reporting(1);
			include(ROOT . "/templates/system/{$file}");
			error_reporting(ERROR_REPORTING_LEVEL);
		}
	}
