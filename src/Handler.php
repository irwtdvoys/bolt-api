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
			$response = new Api\Response();
			$className = get_class($exception);

			error_log($exception->getMessage());

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

			$response->status(500, $data);
		}
	}
?>
