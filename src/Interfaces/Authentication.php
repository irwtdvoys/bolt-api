<?php
	namespace Bolt\Interfaces;

	interface Authentication
	{
		public function __construct(Connection $connection);
		public function authenticate($header);
	}
?>
