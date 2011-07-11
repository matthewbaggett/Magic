<?php

    if (isset($argv[1])) {
        require_once(dirname(__FILE__) . "/../core/MagicCore.php");
        $application_name = $argv[1];
        MagicLogger::log("Running tests for {$application_name}");
        $config = MagicApplicationConfiguration::LoadByName($application_name);
        try {
            $app = new Application($config);
            $app->testAction();
        } catch (Exception $e) {
            Application::exception_handler_cli($e);
        }
        unset($app);
        MagicLogger::log("Tests complete");
    } else {
        require_once(dirname(__FILE__) . "/../core/MagicCore.php");

       /*
        * Run all the core tests
        */
        $tests = MagicUtils::get_directory_list(ROOT . "/tests");
        foreach($tests as $test){
           echo "Test case file: $test\n";
           require_once($test);
        }

       /*
        * Now fire off the application tests
        */
        $applications = spyc::YAMLLoad(ROOT . MagicApplicationConfiguration::APPLICATION_DEFINITION_FILE);
        foreach ($applications['Applications'] as $application_name) {
            $run = "{$_SERVER['_']} {$_SERVER['PHP_SELF']} {$application_name}";
            echo "Running: $run\n\n";
            passthru($run);
            echo "\n\n\n";
        }
    }