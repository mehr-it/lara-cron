<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 06.02.19
	 * Time: 17:25
	 */

	namespace MehrIt\LaraCron\Cron\Field\Expression;


	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;

	/**
	 * Expression matching any value, optionally in a given range
	 * @package MehrIt\LaraDynamicSchedules\Cron\Field\Expression
	 */
	class WildcardExpression implements FieldExpression
	{
		protected $min;
		protected $max;

		/**
		 * Creates a new instance
		 * @param int|null $min The minimum value if any exists
		 * @param int|null $max The maximum value if any exists
		 */
		public function __construct(?int $min, ?int $max) {
			$this->min = $min;
			$this->max = $max;
		}

		/**
		 * @inheritdoc
		 */
		public function isMatching(int $value): bool {
			return ($this->min === null || $value >= $this->min) &&
			       ($this->max === null || $value <= $this->max);
		}

		/**
		 * @inheritdoc
		 */
		public function nextAfter(int $value): int {

			// if below min, min is the next value
			if ($this->min !== null && $value < $this->min)
				return $this->min;

			if ($this->max !== null && $value >= $this->max)
				throw new OutOfRangeException();

			return $value + 1;
		}
	}