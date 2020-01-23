<?php
	namespace Bolt\Api;

	use Bolt\Http;
	use Bolt\Http\Codes as HttpCodes;

	class Response extends Http
	{
		public $headers;
		public $view;
		public $data;
		public $code;

		public function __construct()
		{
			$this->code = HttpCodes::OK;
			$this->data = false;
			$this->headers = new Response\Headers();
			$this->setView("json");
		}

		public function output()
		{
			$headers = $this->headers->headers();

			foreach ($headers as $header)
			{
				header($header, true, $this->code);
			}

			$group = $this->groupLookup($this->code);

			if ($this->code == HttpCodes::NO_CONTENT || $this->code == HttpCodes::NOT_MODIFIED)
			{
				$result = null;
			}
			elseif ($this->code >= 400 || $this->data === false)
			{
				$result = array(
					"code" => $this->code,
					"category" => $group,
					"message" => $this->codeLookup($this->code)
				);

				if ($this->data !== false)
				{
					$result['data'] = $this->data;
				}
			}
			else
			{
				$result = $this->data;
			}

			$this->view->render($result);

			die();
		}

		public function status($code, $data = false, $headers = false)
		{
			$this->code = $code;

			if ($code == HttpCodes::AUTHENTICATION_TIMEOUT)
			{
				$this->headers->add("HTTP/1.1 419 Authentication Timeout");
			}

			if ($data !== false)
			{
				$this->data = $data;
			}

			if ($headers !== false)
			{
				$this->setHeaders($headers);
			}

			$this->output();
		}

		public function setHeaders($headers)
		{
			$this->headers = new Response\Headers();

			$headers = !is_array($headers) ? array($headers) : $headers;

			foreach ($headers as $header)
			{
				$this->headers->add($header);
			}
		}

		public function setView($format)
		{
			$viewName = "\\Bolt\\Views\\" . ucfirst($format);

			if (class_exists($viewName))
			{
				$this->view = new $viewName();
			}
			else
			{
				$this->status(HttpCodes::INTERNAL_SERVER_ERROR, "Unable to display requested view format");
			}
		}
	}
?>
