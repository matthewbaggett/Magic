<?php
class MagicPage
{
	public $site;
	public $user;
	public $head;

	public function __construct ()
	{
		//Populate site and head objects
		$this->site = new StdClass();

		//Populate user object
		$this->user = new User();
		if (method_exists($this->user, "generate_dummy")) {
			$this->user->generate_dummy();
			$this->user->save();
		}

		$this->site->name = "Default Magic Site Name";
		$this->site->title = "Magic Page Title";

        $this->settings = new StdClass();
        foreach(MagicApplication::$settings as $oSetting){
            $sys_name = strtolower($oSetting->get_system_name());
            $this->settings->$sys_name = $oSetting->get_value();
        }
	}
}
