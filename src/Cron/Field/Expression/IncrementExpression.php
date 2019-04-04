<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 06.02.19
	 * Time: 07:24
	 */

	namespace MehrIt\LaraCron\Cron\Field\Expression;


	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;


	/**
	 * Expression matching an incremented value, optionally in a given range
	 * @package MehrIt\LaraDynamicSchedules\Cron\Field\Expression
	 */
	class IncrementExpression implements FieldExpression
	{

		protected $min;
		protected $max;
		protected $increment;

		/**
		 * Creates a new instance
		 * @param int $increment The increment. Must be a positive number
		 * @param int|null $min The minimum value if existing
		 * @param int|null $max The maximum value if existing
		 */
		public function __construct(int $increment, ?int $min, ?int $max) {
			if ($increment < 1)
				throw new \InvalidArgumentException("Increment must be greater 0, got $increment");

			$this->increment = $increment;
			$this->min       = $min;
			$this->max       = $max;
		}


		/**
		 * @inheritdoc
		 */
		public function isMatching(int $value): bool {
			return
				($this->min === null || $value >= $this->min) &&
				($this->max === null || $value <= $this->max) &&
				(($value - $this->min) % $this->increment) === 0;
		}

		/**
		 * @inheritdoc
		 */
		public function nextAfter(int $value): int {

			// if below min, the next value is the minimum
			if ($this->min !== null && $value < $this->min)
				return $this->min;

			// calculate next value
			$nextValue = $value - (($value - $this->min) % $this->increment) + $this->increment;

			// check that the next value is in bounds
			if ($this->max !== null && $nextValue > $this->max)
				throw new OutOfRangeException();

			return $nextValue;
		}
	}