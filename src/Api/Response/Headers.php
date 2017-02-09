<?php
	namespace Bolt\Api\Response;

	use Bolt\Base;

	class Headers extends Base
	{
		private $headers = array();

		public function headers()
		{
			$headers = $this->headers;

			if ($headers === array())
			{
				$headers = array(" ");
			}

			return $headers;
		}

		public function add($header)
		{
			$this->headers[] = $header;
		}
	}
?>
