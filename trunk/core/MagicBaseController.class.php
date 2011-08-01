<?php 

class MagicBaseController
{
	public function Factory(){
		throw new exception("Uhoh, there is no Factory() definition in ".get_called_class());
	}
}

