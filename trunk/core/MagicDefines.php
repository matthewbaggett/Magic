<?php
   $core_config = Spyc::YAMLLoad(ROOT . "/config/config.yml");
	define("USE_GEN_TO_APC", ($core_config['Core']['GenerateToAPC'] == "true") ? TRUE : FALSE);
	if(!defined("FORCE_REGEN")){
		define("FORCE_REGEN", ($core_config['Core']['ForceRegen'] == "true") ? TRUE : FALSE);
	}
	define("ERROR_DISPLAY", ($core_config['Core']['ErrorDisplay'] == "true") ? TRUE : FALSE);
	define("ERROR_REPORTING_LEVEL", E_ALL & ~(E_NOTICE | E_DEPRECATED));
	define("DEFAULT_TIMEZONE", $core_config['Core']['DefaultTimezone']);
	define("USE_BEAUTIFIER", ($core_config['Core']['UseBeautifier'] == "true") ? TRUE : FALSE);

    //Not settable at runtime
    /*
     * Should the concrete classes have get_x set_x in them that pass to parent::set_x?
     */
    define("USE_CONCRETE_GETSET_WRAPPERS", FALSE);

	//Do things
	error_reporting(ERROR_REPORTING_LEVEL);
	ini_set("display_errors", ERROR_DISPLAY);
	date_default_timezone_set(DEFAULT_TIMEZONE);
	if (USE_GEN_TO_APC && function_exists("apc_fetch")) {
		define("GEN_TO_APC", TRUE);
	} else {
		define("GEN_TO_APC", FALSE);
	}
	unset($core_config);
