<?php
   define("FORCE_REGEN", TRUE);

require_once(dirname(__FILE__) . "/../core/MagicCore.php");
$flags = MagicUtils::get_cli_flags($argv);

if (MagicUtils::has_cli_flag('project')) {
    define("APPNAME", MagicUtils::get_cli_flag('project'));
    MagicLogger::log("Regen of " . APPNAME);

    $config = MagicApplicationConfiguration::LoadByName(APPNAME);
    $app = new Application($config);
    if (!MagicUtils::has_cli_flag('no-tests')) {
        $app->testAction();
    }

} elseif(MagicUtils::has_cli_flag('all')) {
    $php = MagicUtils::get_php_binary();
    $applications = MagicUtils::get_applications();
    foreach ($applications as $application) {
        $exec = str_replace("\n", "", "$php " . __FILE__ . " --project={$application} " . (MagicUtils::has_cli_flag('no-sql')
                ? '--no-sql' : '') . " " . (MagicUtils::has_cli_flag('no-tests') ? '--no-tests' : ''));
        echo "Running $exec\n";
        passthru($exec);
    }
}else{
    echo "You must specify the flag --project=YOURPROJECTNAME or --all\n";
}

