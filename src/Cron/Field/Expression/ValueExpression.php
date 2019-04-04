<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 06.02.19
	 * Time: 07:52
	 */

	namespace MehrIt\LaraCron\Cron\Field\Expression;


	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;

	/**
	 * Expression matching a predefined value
	 * @package MehrIt\LaraDynamicSchedules\Cron\Field\Expression
	 */
	class ValueExpression implements FieldExpression
	{
		protected $value;

		/**
		 * Creates a new instance
		 * @param int $value The value to match
		 */
		public function __construct(int $value) {
			$this->value = $value;
		}


		/**
		 * @inheritdoc
		 */
		public function isMatching(int $value): bool {
			return $this->value === $value;
		}

		/**
		 * @inheritdoc
		 */
		public function nextAfter(int $value): int {

			// smaller values
			if ($value < $this->value)
				return $this->value;

			throw new OutOfRangeException();
		}
	}