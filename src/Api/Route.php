<?php
	namespace Bolt\Api;

	use Bolt\Base;
	use Bolt\Files;

	class Route extends Base
	{
		private $rules;

		public $info;

		public $controller;
		public $method;

		public function __construct($auto = false)
		{
			$this->info = new Route\Info($auto);

			if ($auto === true)
			{
				$this->load();
			}
		}

		public function load()
		{
			$this->loadRules();

			if (count($this->rules) > 0)
			{
				foreach ($this->rules as $next)
				{
					if ($next->check($this->info) === true)
					{
						$data = $next->route($this->info);
						break;
					}
				}
			}

			$this->controller = isset($data['controller']) ? $data['controller'] : null;
			$this->method = isset($data['method']) ? $data['method'] : null;
		}

		private function loadRules()
		{
			$fileHandler = new Files();

			$config = json_decode($fileHandler->load(ROOT_SERVER . "/library/routes.json"));
			$error = json_last_error();

			if ($error !== JSON_ERROR_NONE)
			{
				throw new \Exception("Unable to load routes, invalid JSON", $error);
			}

			if (count($config) > 0)
			{
				foreach ($config as $rule)
				{
					$this->rules[] = new Route\Rule($rule);
				}
			}
		}
	}
?>
