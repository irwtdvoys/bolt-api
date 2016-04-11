<?php
	namespace Bolt\Interfaces;

	use Bolt\Api\Connections;

	interface Authentication
	{
		public function __construct(Connections $connections);
		public function authenticate($header);
	}
?>
