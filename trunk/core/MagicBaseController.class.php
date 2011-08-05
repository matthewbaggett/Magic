<?php 

class MagicBaseController
{
	protected $application;
	
	public function __construct(){
		$this->application = Application::GetInstance();
    }
	
    public function Factory(){
		throw new exception("Uhoh, there is no Factory() definition in ".get_called_class());
	}
}

