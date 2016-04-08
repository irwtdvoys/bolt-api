<?php
	namespace Bolt\Api\Authentication;

	use Bolt\Base;
	use Bolt\Interfaces\Authentication;
	use Bolt\Interfaces\Connection;

	class Basic extends Base implements Authentication
	{
		public function __construct(Connection $connection = null)
		{
		}

		public function authenticate($raw)
		{
			$data = base64_decode(str_replace("Basic ", "", $raw));

			list($username, $password) = explode(":", $data, 2);

			if ($username !== AUTH_USERNAME || $password !== AUTH_PASSWORD)
			{
				throw new \Exception("", 401);
			}

			return true;
		}
	}
?>
