<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 04.02.19
	 * Time: 21:09
	 */

	namespace MehrIt\LaraCron\Cron\Field;


	class MonthField extends AbstractField
	{
		const NAME_MAPPINGS = [
			'JAN' => 1,
			'FEB' => 2,
			'MAR' => 3,
			'APR' => 4,
			'MAY' => 5,
			'JUN' => 6,
			'JUL' => 7,
			'AUG' => 8,
			'SEP' => 9,
			'OCT' => 10,
			'NOV' => 11,
			'DEC' => 12,
		];

		/**
		 * Creates a new instance
		 * @param string $expression The field expression
		 * @param \DateTimeZone $timeZone The timezone to interpret the expression
		 */
		public function __construct(string $expression, \DateTimeZone $timeZone) {
			parent::__construct($expression, $timeZone, 'month', 1, 12);
		}

		/**
		 * @inheritDoc
		 */
		protected function parseToken(string $expression) {

			// translate names
			$expression = self::NAME_MAPPINGS[$expression] ?? $expression;

			return parent::parseToken($expression);
		}


	}