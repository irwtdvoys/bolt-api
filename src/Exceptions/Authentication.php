<?php
	namespace Bolt\Exceptions;

	use \Exception;

	class Authentication extends Exception
	{
		public function __construct($code, Exception $previous = null)
		{
			parent::__construct("", $code, $previous);
		}
	}
?>
