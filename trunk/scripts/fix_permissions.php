<?php

	require_once(dirname(__FILE__) . "/../core/MagicCore.php");

	function recursiveChmod ($path, $filePerm = 0644, $dirPerm = 0755)
	{
		echo "Entering {$path}\n";
		// Check if the path exists
		if (!file_exists($path)) {
			return (FALSE);
		}
		// See whether this is a file
		if (is_file($path)) {
			// Chmod the file with our given filepermissions
			echo "chmod(\"{$path}\", \"{$filePerm}\");s\n";
			chmod($path, $filePerm);
			// If this is a directory...
		} elseif (is_dir($path)) {
			// Then get an array of the contents
			$foldersAndFiles = scandir($path);
			// Remove "." and ".." from the list
			$entries = array_slice($foldersAndFiles, 2);
			// Parse every result...
			foreach ($entries as $entry) {
				// And call this function again recursively, with the same permissions
				recursiveChmod($path . "/" . $entry, $filePerm, $dirPerm);
			}
			// When we are done with the contents of the directory, we chmod the directory itself
			chmod($path, $dirPerm);
			echo "chmod(\"{$path}\", \"{$dirPerm}\");\n";
		}
		// Everything seemed to work out well, return TRUE
		return (TRUE);
	}

	$applications = spyc::YAMLLoad(ROOT . MagicApplicationConfiguration::APPLICATION_DEFINITION_FILE);

	recursiveChmod(MagicCache::FILE_STORAGE_LOCATION, 0666, 0777);

	foreach ($applications['Applications'] as $application_name) {
      recursiveChmod(ROOT . "/config", 0666, 0777);
		recursiveChmod(sprintf(rtrim(ROOT,"/") . MagicObjectFactory::OBJECT_GENERATION_OUTPUT_DIR, $application_name), 0666, 0777);
		recursiveChmod(sprintf(rtrim(ROOT,"/") . "/application/%s/temp/", $application_name), 0666, 0777);
      recursiveChmod(sprintf(rtrim(ROOT,"/") . "/application/%s/tests/", $application_name), 0666, 0777);
	}