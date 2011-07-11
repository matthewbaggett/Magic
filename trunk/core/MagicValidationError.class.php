<?php
class MagicValidationError
{
	public $error;

	public function __construct ($error)
	{
		$this->error = $error;
	}
}
