<?php
	namespace Bolt\Api\Request;

	use Bolt\Base;

	class Headers extends Base
	{
		private $headers;

		public function __construct($auto = false)
		{
			if ($auto === true)
			{
				$this->parse();
			}
		}

		public function __get($name)
		{
			return $this->$name;
		}

		public function __isset($name)
		{
			return isset($this->headers[$name]) ? true : false;
		}

		public function __call($name, $arguments)
		{
			if ($name == "headers")
			{
				return $this->headers;
			}

			if ($arguments == array())
			{
				return $this->headers[$name];
			}

			$this->headers[$name] = $arguments[0];
			return true;
		}

		public function parse()
		{
            $this->headers = array_change_key_case(getallheaders(), CASE_LOWER);
		}
	}
?>
