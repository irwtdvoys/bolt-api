<?php
	namespace Bolt\Api\Request;

	use Bolt\Validation\Constraint;
	use Bolt\Validation\Constraints\Required;
	use Bolt\Base;
	use Bolt\Arrays;

	class InputMask extends Base
	{
		public $name;
		public $type;
		/** @var InputMask[] */
		public $children = array();
		/** @var Constraint[] */
		public $constraints = array();

		public function __construct($data = null)
		{
			parent::__construct($data);
		}

		public function add($name, $type = null, $constraints = array())
		{
			$structure = array(
				"name" => $name,
				"type" => $type,
				"constraints" => $constraints
			);

			$class = ($type !== null) ? $type : InputMask::class;

			$this->children[] = new $class($structure);

			return $this;
		}

		public function inflate($fields)
		{
			foreach ($fields as $key => $value)
			{
				if (Arrays::type($fields) === "numeric" || is_integer($key))
				{
					$this->add($value, null, [new Required()]);
				}
				else
				{
					$mask = new InputMask();
					$mask->name($key);
					$mask->inflate($value);
					$mask->constraints([new Required()]);
					$this->children[] = $mask;
				}
			}

			return $this;
		}

		public function constraints($data = null)
		{
			if ($data === null)
			{
				return $this->constraints;
			}

			$this->constraints = array_merge($this->constraints, $data);

			return $this;
		}
	}
?>
