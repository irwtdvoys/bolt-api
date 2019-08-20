<?php
	namespace Bolt\Api\Request;

	use Bolt\Base;
	use Bolt\Arrays;

	class InputMask extends Base
	{
		public $name;
		public $type;
		public $children = array();
		public $options = array();

		public function __construct($data = null)
		{
			parent::__construct($data);
		}

		public function add($name, $type = null, $options = array())
		{
			$structure = array(
				"name" => $name,
				"type" => $type,
				"options" => $options
			);
			$this->children[] = new InputMask($structure);



			return $this;
		}

		public function inflate($fields)
		{
			foreach ($fields as $key => $value)
			{
				if (Arrays::type($fields) === "numeric" || is_integer($key))
				{
					$this->add($value, null, ["required" => true]);
				}
				else
				{
					$mask = new InputMask();
					$mask->name($key);
					$mask->inflate($value);
					$mask->options(["required" => true]);
					$this->children[] = $mask;
				}
			}

			return $this;
		}
	}
?>
