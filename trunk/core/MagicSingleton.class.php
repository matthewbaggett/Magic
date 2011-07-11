<?php 
	class MagicSingleton
	{

		static public function Factory ()
		{
			$class = get_called_class();
			$my_singleton = forward_static_call(array($class, 'get_singleton'));
			if ($my_singleton === NULL) {
				$my_singleton = new $class();
				$my_singleton->init();
			}
			$my_singleton->reset();
			return $my_singleton;
		}

		public function init ()
		{
		}

		public function reset ()
		{
		}
	}