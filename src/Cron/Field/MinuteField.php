<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 04.02.19
	 * Time: 20:55
	 */

	namespace MehrIt\LaraCron\Cron\Field;


	class MinuteField extends AbstractField
	{

		/**
		 * Creates a new instance
		 * @param string $expression The field expression
		 * @param \DateTimeZone $timeZone The timezone to interpret the expression
		 */
		public function __construct(string $expression, \DateTimeZone $timeZone) {
			parent::__construct($expression, $timeZone, 'minute', 0, 59);
		}

	}