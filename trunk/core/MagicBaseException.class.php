<?php 
	abstract class MagicBaseException extends Exception implements MagicExceptionInterface
	{
		protected $message = 'Unknown exception'; // Exception message
		private $string; // Unknown
		protected $code = 0; // User-defined exception code
		protected $file; // Source filename of exception
		protected $line; // Source line of exception
		private $trace; // Unknown
		private $prettyprint_object;
		private $detail_message;

		public function __construct ($message = null, $detail = null, $object = null, $code = 0)
		{
			$this->prettyprint_object = $object;
			$this->detail_message = $detail;
			if (!$message) {
				throw new $this('Unknown ' . get_class($this));
			}
			parent::__construct($message, $code);
			$this->send_email();
		}

		private function send_email ()
		{
			//TODO: implement sending email on exception
		}

		public function __toString ()
		{
			if (PHP_SAPI == 'cli') {
				return parent::__toString()."\n\n".var_export($this->prettyprint_object);
			} else {
				$code = get_class($this) . " '" . nl2br($this->message) . "' in {$this->file}({$this->line})\n"
						. "{$this->getTraceAsString()}";
				$code .= krumo::backtrace();
				// print all the included(or required) files
				$code .= krumo::includes();
				// print all the included functions
				$code .= krumo::functions();
				// print all the declared classes
				$code .= krumo::classes();
				// print all the defined constants
				$code .= krumo::defines();
			}
			return $code;
		}

		public function getObject ()
		{
			return $this->prettyprint_object;
		}

		public function getDetail ()
		{
			return $this->detail_message;
		}
	}
