<?php
	namespace Bolt;

	use Bolt\Api\Response;
	use Bolt\Exceptions\Output;
	use Bolt\Http\Codes as HttpCodes;

	class Handler
	{
		public static function error($level, $message, $file, $line, $context)
		{
			throw new Exceptions\Error($message, 0, $level, $file, $line);
		}

		public static function exception($exception)
		{
			$className = get_class($exception);

			$type = $className;

			if ($exception instanceof Exception)
			{
				$type .= "::" . $exception->getCodeKey();
			}

			$code = HttpCodes::INTERNAL_SERVER_ERROR;

			if ($exception instanceof Output)
			{
				$data = $exception->getMessage();
				$code = $exception->getCode();
			}
			elseif (DEPLOYMENT === Deployment::PRODUCTION)
			{
				$data = $type;
			}
			else
			{
				$data = array(
					"type" => $type,
					"message" => $exception->getMessage(),
					"code" => $exception->getCode(),
					"line" => $exception->getLine(),
					"file" => $exception->getFile(),
					"trace" => $exception->getTrace()
				);
			}

			if (php_sapi_name() == "cli")
			{
				if (!is_string($data))
				{
					$data = Json::encode($data, JSON_PRETTY_PRINT);
				}

				echo($data . "\n");
			}
			else
			{
				$response = new Response();
				$response->status($code, $data);
			}

			return true;
		}
	}
?>
