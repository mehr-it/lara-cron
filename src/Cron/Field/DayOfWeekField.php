<?php

	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 04.02.19
	 * Time: 21:28
	 */

	namespace MehrIt\LaraCron\Cron\Field;


	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;

	class DayOfWeekField extends AbstractField
	{
		const NAME_MAPPINGS = [
			'SUN' => 0,
			'MON' => 1,
			'TUE' => 2,
			'WED' => 3,
			'THU' => 4,
			'FRI' => 5,
			'SAT' => 6,
		];

		/**
		 * Creates a new instance
		 * @param string $expression The field expression
		 * @param \DateTimeZone $timeZone The timezone to interpret the expression
		 */
		public function __construct(string $expression, \DateTimeZone $timeZone) {
			parent::__construct($expression, $timeZone, 'dayOfWeek', 0, 6);
		}

		/**
		 * @inheritDoc
		 */
		protected function parseToken(string $expression) {

			if ($expression == '7') {
				// translate 7 to 0 (sunday)
				$expression = '0';
			}
			else {
				// translate names
				$expression = self::NAME_MAPPINGS[$expression] ?? $expression;
			}

			return parent::parseToken($expression);
		}

		/**
		 * @inheritDoc
		 */
		protected function nextMatchingDate(int $targetDate): \DateTime {

			$date = $this->dateForTimestamp($targetDate, $this->timezone);
			$targetMonth = (int)$date->format('m');

			$expressions = $this->getFieldExpressions();

			// we simply loop through all weekdays starting with current
			// week day. If the first weekday matches the field expression
			// we stop and increment the date's day by the number of loop
			// iterations (which represents the number of days until next
			// weekday matches)
			$dow = (int)$date->format('w');
			for ($i = 1; $i <= 7; ++$i) {

				// increment weekday cyclic (next after 6=SAT is 0=SUN)
				if (++$dow === 7)
					$dow = 0;

				// is matching?
				foreach($expressions as $currExp) {

					if ($currExp->isMatching($dow)) {

						$ret = $date->setTime(0, 0, 0)->add(new \DateInterval("P{$i}D"));

						// If date month has changed, we have no match
						// here. First, the month must be incremented,
						// but that's subject to the month field, not
						// subject of the day-of-week field
						if ((int)$ret->format('m') !== $targetMonth)
							throw new OutOfRangeException();

						return $ret;
					}

				}
			}

			throw new OutOfRangeException();
		}



		//		/**
//		 * @inheritDoc
//		 */
//		public function nextAfter(Carbon $date): Carbon {
//			$targetMonth = $date->month;
//
//			// create a copy of the original date which can be modified and returned
//			$ret = $date->copy();
//
//
//			// The algorithm is very simple. Iterate all days in current month
//			// until a date matches the field expression. The first matching day
//			// is returned. If no day in current month matches, we throw an overflow
//			// exception
//			while (true) {
//				$ret->addDay();
//
//				// we modify the day component, so we do not overflow to the next month
//				if ($ret->month != $targetMonth)
//					throw new OutOfRangeException();
//
//				// if date matches, we return the start of the day
//				if ($this->matches($ret))
//					return $ret->startOfDay();
//			}
//		}


	}