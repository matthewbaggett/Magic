<?php 

class MagicBaseController
{
	protected $application;
	
	public function __construct(){
		$this->application = Application::GetInstance();
		if(method_exists($this, 'setup')){
			$this->setup();
		}
    }
	
    public function Factory(){
		throw new exception("Uhoh, there is no Factory() definition in ".get_called_class());
	}
	
	public function is_post(){
		if(count($_POST) > 0){
			return true;
		}else{
			return false;
		}
	}
}

