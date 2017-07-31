<?php
	namespace Bolt\Api\Request;

	use Bolt\Arrays;
	use Bolt\Base;

	class Parameters extends Base
	{
		private $parameters;

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
			return isset($this->parameters[$name]) ? true : false;
		}

		public function __call($name, $arguments)
		{
			if ($name == "parameters")
			{
				return $this->parameters;
			}

			if ($arguments == array())
			{
				return $this->parameters[$name];
			}

			$this->parameters[$name] = $arguments[0];
			return true;
		}

		public function parse()
		{
			$parameters = array();

			// Get
			if (isset($_SERVER['QUERY_STRING']))
			{
				parse_str($_SERVER['QUERY_STRING'], $parameters);
			}

			// Post
			$body = file_get_contents("php://input");
			$contentType = false;

			if (isset($_SERVER['CONTENT_TYPE']))
			{
				list($contentType) = explode(";", $_SERVER['CONTENT_TYPE']);
			}

			switch ($contentType)
			{
				case "application/json":
					$body_params = json_decode($body);

					$error = json_last_error();

					if ($error !== JSON_ERROR_NONE)
					{
						throw new \Exception("Error decoding JSON", $error);
					}

					if ($body_params)
					{
						foreach($body_params as $param_name => $param_value)
						{
							$parameters[$param_name] = $param_value;
						}
					}
					break;
				case "text/xml":
					// NYI
					break;
				case "application/x-www-form-urlencoded":
					parse_str($body, $postvars);

					foreach ($postvars as $field => $value)
					{
						$parameters[$field] = $value;
					}
					break;
				case "multipart/form-data":
					if (count($_POST) > 0)
					{
						foreach ($_POST as $key => $value)
						{
							$parameters[$key] = $value;
						}
					}
					break;
				// Todo: parse other formats here
			}

			if ($parameters != array())
			{
				$this->parameters = $parameters;
			}
		}

		public function check($fields)
		{
			if (!is_array($fields))
			{
				$fields = explode(",", $fields);
			}

			foreach ($fields as $field)
			{
				$namespace = explode(".", $field);
				$node = (object)$this->parameters;
				$current = array();

				foreach ($namespace as $next)
				{
					$current[] = $next;

					if ($node === null || !array_key_exists($next, $node))
					{
						return implode(".", $current);
					}

					$node = $node->$next;
				}
			}

			return true;
		}

		public function filter($fields, $parameters = null)
		{
			if (!is_array($fields))
			{
				$fields = explode(",", $fields);
			}

			if ($parameters === null)
			{
				$parameters = (object)$this->parameters();
			}

			$result = array();

			foreach ($fields as $key => $value)
			{
				if (Arrays::type($fields) === "numeric" || is_integer($key))
				{
					$field = $value;
					$data = null;
				}
				else
				{
					$field = $key;
					$data = $value;
				}

				if (isset($parameters->{$field}))
				{
					$result[$field] = ($data === null) ? $parameters->{$field} : $this->filter($fields[$field], $parameters->{$field});
				}
			}

			return $result;
		}
	}
?>
