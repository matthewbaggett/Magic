<?php

    if (isset($argv[1])) {
        require_once(dirname(__FILE__) . "/../core/MagicCore.php");
        $application_name = $argv[1];
        MagicLogger::log("Running cron for {$application_name} cron @ " . date("F j, Y, g:i a e") . " (" . time() . ")");
        $config = MagicApplicationConfiguration::LoadByName($application_name);
        try {
            $app = new Application($config);
            $app->cronAction();
        } catch (Exception $e) {
            Application::exception_handler_cli($e);
        }
        unset($app);
        MagicLogger::log("Cron complete");
    } else {
        require_once(dirname(__FILE__) . "/../core/MagicCore.php");
        //$applications = spyc::YAMLLoad(ROOT . MagicApplicationConfiguration::APPLICATION_DEFINITION_FILE);
        $applications = MagicUtils::get_applications();
        print_r($applications);
        foreach ($applications as $application_name) {
            $run = "{$_SERVER['_']} {$_SERVER['PHP_SELF']} {$application_name}";
            echo "Running: $run\n\n";
            passthru($run);
            echo "\n\n\n";
        }
    }