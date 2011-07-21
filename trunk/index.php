<?php
if(file_exists("update.lock")){
	die("Hang on a tick! Site down for maintainance (an update is being deployed!)");
}
require_once("core/MagicCore.php");
$config = MagicApplicationConfiguration::LoadByHost();
$app = Application::Factory($config);
$app->route();
