<?php
	namespace Bolt\Api;

	class Connections extends \Bolt\Base
	{
		public $sources;

		public function __construct($connections = null)
		{
			if ($connections !== null)
			{
				if (is_array($connections))
				{
					foreach ($connections as $connection)
					{
						$this->add($connection);
					}
				}
				else
				{
					$this->add($connections);
				}
			}
		}

		public function add($connection, $identifier = null)
		{
			$this->sources[] = $connection;

			if ($identifier !== null)
			{
				$this->assign($identifier, count($this->sources) - 1);
			}
		}

		public function assign($name, $index)
		{
			$this->$name = &$this->sources[$index];
		}

		private function filter($className)
		{
			$results = array();

			foreach ($this->sources as $source)
			{
				if ($source->className() == $className)
				{
					$results[] = $source;
				}
			}

			return $results;
		}

		public function __call($name, $args)
		{
			if ($args == array())
			{
				return $this->$name;
			}

			$this->$name = $args[0];

			return true;
		}
	}
?>
