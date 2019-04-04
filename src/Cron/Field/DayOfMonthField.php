<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 04.02.19
	 * Time: 21:08
	 */

	namespace MehrIt\LaraCron\Cron\Field;



	class DayOfMonthField extends AbstractField
	{

		/**
		 * Creates a new instance
		 * @param string $expression The field expression
		 * @param \DateTimeZone $timeZone The timezone to interpret the expression
		 */
		public function __construct(string $expression, \DateTimeZone $timeZone) {
			parent::__construct($expression, $timeZone, 'day', 1, 31);
		}


	}