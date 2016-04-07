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
			if ($arguments == array())
			{
				return $this->headers[$name];
			}

			$this->headers[$name] = $arguments[0];
			return true;
		}

		private function fetchHeaders()
		{
			$headers = array();

			foreach ($_SERVER as $key => $value)
			{
				if (strpos($key, "HTTP_") === 0)
				{
					$bits = explode("_", $key);
					array_shift($bits);

					foreach ($bits as &$bit)
					{
						$bit = ucwords(strtolower($bit));
					}

					$headers[implode("-", $bits)] = $value;
				}
			}

			return $headers;
		}

		public function parse()
		{
            $this->headers = array_change_key_case($this->fetchHeaders(), CASE_LOWER);
		}
	}
?>
