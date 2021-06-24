<?php
	namespace Bolt\Api\Request;

	use Bolt\Base;
	use Bolt\Exceptions\Framework as FrameworkException;
	use Bolt\Exceptions\Output as OutputException;
	use Bolt\Exceptions\Validation;
	use Bolt\Http\Codes as HttpCodes;
	use Bolt\Json;

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
					// Catch empty string body from GET requests being processed if the content-type header is still set, json_decode will raise an error code if asked to decode an empty string
					if ($body === "" && $_SERVER['REQUEST_METHOD'] == "GET")
					{
						break;
					}

					$body_params = Json::decode($body);

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

		public function check(InputMask $mask, $parameters = null)
		{
			if ($parameters === null)
			{
				$parameters = (object)$this->parameters();
			}

			$fields = $mask->children();

			foreach ($fields as $next)
			{
				$field = $next->name();

				if ($next->options['required'] === true && $parameters->{$field} === null)
				{
					return $field;
				}

				if (!empty($next->children))
				{
					$check = $this->check($next, $parameters->{$field});

					if ($check !== true)
					{
						return $field . "." . $check;
					}
				}
			}

			return true;
		}

		public function filter(InputMask $mask, $parameters = null)
		{
			if ($parameters === null)
			{
				$parameters = (object)$this->parameters();
			}

			$result = array();
			$fields = $mask->children();

			foreach ($fields as $next)
			{
				$field = $next->name();
				$data = $next->children();

				if (isset($parameters->{$field}))
				{
					$result[$field] = empty($data) ? $parameters->{$field} : $this->filter($next, $parameters->{$field});
				}
			}

			return $result;
		}

		public function checkConstraints(InputMask $mask, $parameters)
		{
			$constraints = $mask->constraints();
			$results = array();

			if (count($constraints) > 0)
			{
				foreach ($constraints as $constraint)
				{
					if ($constraint->isValid($parameters) === false)
					{
						$results[] = $constraint->message();
					}
				}
			}

			$children = $mask->children();

			if (!empty($children))
			{
				foreach ($children as $child)
				{
					$name = $child->name();
					$result = $this->checkConstraints($child, $parameters->{$name});

					if ($result !== array())
					{
						$results[$name] = $result;
					}
				}
			}

			return $results;
		}

		public function validate($mask, $filter = false)
		{
			if (is_string($mask))
			{
				$mask = new $mask();
			}

			if (!$mask instanceof InputMask)
			{
				throw new FrameworkException("Expected InputMask, got `" . gettype($mask) . "`", HttpCodes::INTERNAL_SERVER_ERROR);
			}

			$result = $this->checkConstraints($mask, (object)$this->parameters());

			if ($result !== array())
			{
				throw new Validation(Json::encode($result), HttpCodes::BAD_REQUEST);
			}

			return ($filter === true) ? $this->filter($mask) : null;
		}
	}
?>
