<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 06.02.19
	 * Time: 07:49
	 */

	namespace MehrIt\LaraCron\Cron\Field\Expression;


	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;

	/**
	 * Expression matching a value within a given range
	 * @package MehrIt\LaraDynamicSchedules\Cron\Field\Expression
	 */
	class RangeExpression implements FieldExpression
	{
		protected $min;
		protected $max;

		/**
		 * Creates a new instance
		 * @param int $min The minimum value
		 * @param int $max The maximum value
		 */
		public function __construct(int $min, int $max) {
			$this->min = $min;
			$this->max = $max;
		}

		/**
		 * @inheritdoc
		 */
		public function isMatching(int $value): bool {
			return ($value >= $this->min && $value <= $this->max);
		}

		/**
		 * @inheritdoc
		 */
		public function nextAfter(int $value): int {

			// if below min, min is the next value
			if ($value < $this->min)
				return $this->min;

			if ($value >= $this->max)
				throw new OutOfRangeException();

			return $value + 1;
		}

		/**
		 * @return int
		 */
		public function getMin(): int {
			return $this->min;
		}

		/**
		 * @return int
		 */
		public function getMax(): int {
			return $this->max;
		}


	}