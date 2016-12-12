<?php
	namespace Bolt;

	class Handler
	{
		public static function error($level, $message, $file, $line, $context)
		{
			throw new Exceptions\Error($message, 0, $level, $file, $line);
		}

		public static function exception($exception)
		{
			$className = get_class($exception);

			if (DEPLOYMENT == "production")
			{
				$data = $className;
			}
			else
			{
				$data = array(
					"type" => $className,
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
					$data = json_encode($data, JSON_PRETTY_PRINT);
				}

				echo($data . "\n");
			}
			else
			{
				$response = new Api\Response();
				$response->status(500, $data);
			}

			die();
		}
	}
?>
