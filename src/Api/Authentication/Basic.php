<?php
	namespace Bolt\Api\Authentication;

	use Bolt\Api\Connections;
	use Bolt\Base;
	use Bolt\Interfaces\Authentication;

	class Basic extends Base implements Authentication
	{
		public function __construct(Connections $connection = null)
		{
		}

		public function authenticate($parameters)
		{
			$data = base64_decode($parameters->token);

			list($username, $password) = explode(":", $data, 2);

			if ($username !== AUTH_USERNAME || $password !== AUTH_PASSWORD)
			{
				throw new \Bolt\Exceptions\Authentication(401);
			}

			return true;
		}
	}
?>
