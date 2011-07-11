<?php 
class MagicApplicationConfiguration extends MagicSingleton
{
    protected static $singleton = null;
    const APPLICATION_DEFINITION_FILE = "/config/applications.yml";

    public $app_name;
    public $app_root;
    public $canonical_domain;
    public $aliases;

    public function __construct()
    {
        $this->aliases = array();
    }

    public function get_singleton()
    {
        return MagicApplicationConfiguration::$singleton;
    }

    /**
     * Load the configuration by scanning for a matching host.
     */
    static public function LoadByHost()
    {
        //Host of the machine we're on
        $host = $_SERVER['HTTP_HOST'];

        //If the turbocrms-site header is sent, use that instead of HTTP_HOST.
        if (isset($_SERVER['HTTP_TURBOCRMS_SITE'])) {
            $host = $_SERVER['HTTP_TURBOCRMS_SITE'];
        }

        //Start to load the config
        $applications = spyc::YAMLLoad(ROOT . MagicApplicationConfiguration::APPLICATION_DEFINITION_FILE);
        foreach ($applications['Applications'] as $application_name) {
            $config = self::get_config($application_name);
            //Check that the application configuration file does exist
            $all_domains = array_merge((array)$config['Aliases'], (array)$config['Domain']);
            if (in_array($host, $all_domains)) {
                MagicApplicationConfiguration::$singleton = MagicApplicationConfiguration::ManufactureConfig($config);
                return MagicApplicationConfiguration::$singleton;
            }

        }
        MagicApplication::exception_handler_web(new MagicException("Cannot find a server at $host"), true);
    }

    static public function LoadByName($application_name)
    {
        $config = self::get_config($application_name);
        MagicApplicationConfiguration::$singleton = MagicApplicationConfiguration::ManufactureConfig($config);
        return MagicApplicationConfiguration::$singleton;
    }

    static public function get_config($application_name){
        //Work out where our configs should be
        $hostname = gethostname();
        $application_config_file_specific = ROOT . "application/{$application_name}/config.{$hostname}.yml";
        $application_config_file_generic = ROOT . "application/{$application_name}/config.yml";

        //Load the base config
        $config = spyc::YAMLLoad($application_config_file_generic);
        //If there is an available set of local config for this machine, overload the base config with it
        if (file_exists($application_config_file_specific)) {
            MagicLogger::log("Using specific config $application_config_file_specific rather than the default");
            $config = array_merge((array)$config, (array)spyc::YAMLLoad($application_config_file_specific));
        } else {
            MagicLogger::log("Cannot find specific config {$application_config_file_specific}. Using generic configuration file {$application_config_file_generic}");
        }
        return $config;
    }

    static public function ManufactureConfig($config)
    {
        $oConfig = new MagicApplicationConfiguration();
        $oConfig->app_name = $config['AppName'];
        $oConfig->domains = array_merge($config['Aliases'], (array)$config['Domain']);
        $oConfig->web_root = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        $oConfig->app_root = "{$oConfig->web_root}/application/{$oConfig->app_name}/resources/";
        $oConfig->database = new MagicDatabaseConfig();
        $oConfig->database->host = $config['Database']['Host'];
        $oConfig->database->port = $config['Database']['Port'];
        $oConfig->database->username = $config['Database']['Username'];
        $oConfig->database->password = $config['Database']['Password'];
        $oConfig->database->database = $config['Database']['Database'];
        $oConfig->raw = $config;
        define("APPNAME", $oConfig->app_name);
        define("DIR_APP", ROOT . "application/" . APPNAME);
        define("ROOT_APP", DIR_APP);
        define("DIR_TEMP", DIR_APP . "/temp");
        define("DIR_GEN", DIR_APP . "/gen");

        if(!file_exists(DIR_TEMP)){
            if(!mkdir(DIR_TEMP, 0777, true)){
                throw new exception("Wuhoh, there is no /temp directory at " . DIR_TEMP);
            }
        }

        if(!file_exists(DIR_GEN)){
            if(!mkdir(DIR_GEN, 0777, true)){
                throw new exception("Wuhoh, there is no /gen directory at " . DIR_TEMP);
            }
        }

        return $oConfig;
    }
}
