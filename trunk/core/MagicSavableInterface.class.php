<?php 

interface MagicSavableInterface
{
	public function save ($force_save = false);
	public function reload();
	public function load ($id = null);
}