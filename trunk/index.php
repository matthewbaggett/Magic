<?php

require_once("core/MagicCore.php");
$config = MagicApplicationConfiguration::LoadByHost();
$app = Application::Factory($config);
$app->route();
