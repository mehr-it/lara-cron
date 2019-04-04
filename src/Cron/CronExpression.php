<?php
	/**
	 * Created by PhpStorm.
	 * User: chris
	 * Date: 29.03.19
	 * Time: 10:31
	 */

	namespace MehrIt\LaraCron\Cron;


	use DateTimeZone;
	use MehrIt\LaraCron\Contracts\CronField;
	use MehrIt\LaraCron\Cron\Exception\InvalidCronExpressionException;
	use MehrIt\LaraCron\Cron\Exception\OutOfRangeException;
	use MehrIt\LaraCron\Cron\Field\DayOfMonthField;
	use MehrIt\LaraCron\Cron\Field\DayOfWeekField;
	use MehrIt\LaraCron\Cron\Field\HourField;
	use MehrIt\LaraCron\Cron\Field\MinuteField;
	use MehrIt\LaraCron\Cron\Field\MonthField;

	class CronExpression implements \MehrIt\LaraCron\Contracts\CronExpression
	{
		use ConvertsTimestamps;
		use DateStartOf;

		const MODE_DAY_OF_MONTH = 1;
		const MODE_DAY_OF_WEEK = 2;
		const MODE_BOTH = 3;

		protected $expression;
		protected $timezone;

		protected $parsed = false;

		protected $dayMode;


		/**
		 * @var CronField
		 */
		protected $month;
		/**
		 * @var CronField
		 */
		protected $dayOfWeek;
		/**
		 * @var CronField
		 */
		protected $dayOfMonth;
		/**
		 * @var CronField
		 */
		protected $hour;
		/**
		 * @var CronField
		 */
		protected $minute;

		/**
		 * @inheritDoc
		 */
		public function __construct(string $expression, $timezone) {

			if (!($timezone instanceof DateTimeZone))
				$timezone = new DateTimeZone($timezone);

			$this->expression = $expression;
			$this->timezone   = $timezone;
		}

		/**
		 * @inheritDoc
		 */
		public function getExpression(): string {
			return $this->expression;
		}

		/**
		 * @inheritDoc
		 */
		public function getTimezone(): DateTimeZone {
			return $this->timezone;
		}


		protected function parse(string $expression, DateTimeZone $timezone) {

			// split into field expressions
			$fieldExpressions = explode(' ', preg_replace('/[\s]+/', ' ', trim($expression)));

			// verify the the correct number of fields
			if (count($fieldExpressions) !== 5)
				throw new InvalidCronExpressionException($expression);

			// init fields
			$this->minute     = new MinuteField($fieldExpressions[0], $timezone);
			$this->hour       = new HourField($fieldExpressions[1], $timezone);
			$this->dayOfMonth = new DayOfMonthField($fieldExpressions[2], $timezone);
			$this->month      = new MonthField($fieldExpressions[3], $timezone);
			$this->dayOfWeek  = new DayOfWeekField($fieldExpressions[4], $timezone);

			// determine day mode
			if (!$this->dayOfMonth->isWildcard() && !$this->dayOfWeek->isWildcard())
				$this->dayMode = self::MODE_BOTH;
			else if (!$this->dayOfWeek->isWildcard())
				$this->dayMode = self::MODE_DAY_OF_WEEK;
			else
				$this->dayMode = self::MODE_DAY_OF_MONTH;
		}

		protected function prepare() {
			if (!$this->parsed) {
				$this->parse($this->expression, $this->timezone);

				$this->parsed = true;
			}
		}

		/**
		 * Returns if the given fields match the given timestamp
		 * @param int $ts The timestamp
		 * @param array $fields The fields
		 * @return bool True if matching. Else false.
		 */
		protected function matchesFields(int $ts, array $fields): bool {
			foreach ($fields as $index => $currField) {
				if (!$currField->matches($ts))
					return false;
			}

			return true;
		}/** @noinspection PhpDocMissingThrowsInspection */

		/**
		 * Returns the next matching timestamp after the given date for the given cron fields
		 * @param int $ts The timestamp
		 * @param int $maxTs The maximum timestamp to return
		 * @param CronField[] $fields The cron fields to match (most significant first)
		 * @return int|null The timestamp or null if none found before maxTs
		 */
		protected function nextAfterFields(int $ts, int $maxTs, array $fields) : ?int {

			$startField = 0;

			// evaluate if all fields are matching
			$allFieldsMatching = true;
			foreach ($fields as $index => $currField) {
				if (!$currField->matches($ts)) {
					$allFieldsMatching = false;

					// We start with the most significant field, which is not matching. This field must
					// be advanced.
					$startField = $index;
					break;
				}
			}

			// If ALL fields are matching, we begin with the least significant field which is not a
			// wildcard and force it to advance the timestamp to next matching value. Otherwise the
			// following algorithm would not advance the timestamp at all
			if ($allFieldsMatching) {

				// in case all fields are wildcards, the start field is not changed hereafter, so we
				// default it to least significant field
				$startField = 3;

				for ($j = 3; $j >= 0; --$j) {
					if (!$fields[$j]->isWildcard()) {
						$startField = $j;
						break;
					}
				}
			}

			// Loop through all fields (from most to least significant field) until all
			// fields are matching the timestamp or the timestamp becomes greater than
			// the given maximum.
			//
			// For each field:
			//
			// If current field matches and is not forced to advance, continue with next
			// field.
			//
			// Else, try to advance the timestamp by setting the current field's component
			// to next value matching the field. If not possible jump back to the previous
			// (more significant) field and force it to advance.
			//
			//
			$forceNext = true;
			for ($i = $startField; $i < 4; ++$i) {
				$currField = $fields[$i];

				// If the current field does not match the timestamp or if it is forced to
				// advance, we let it advance the timestamp to the next matching value.
				if ($forceNext || !$currField->matches($ts)) {
					$forceNext = false;

					try {
						// advance to next matching value by incrementing field's component of
						// the timestamp
						$ts = $currField->nextAfter($ts);
					}
					catch (OutOfRangeException $ex) {
						// If this exception is thrown, no later matching timestamp could be
						// generated by changing the field's component. So we have to increment
						// a more significant component

						if ($i === 0) {
							// advance to next year
							$tsDate = $this->dateForTimestamp($ts, $this->timezone);
							/** @noinspection PhpUnhandledExceptionInspection */
							$ts = $this->startOf($tsDate, 'year', (int)$tsDate->format('Y') + 1, $this->timezone)->getTimestamp();
							--$i;
						}
						else {
							// continue with next higher component and force to increment
							$i -= 2;
							$forceNext = true;
						}
					}

					// if exceeds passed maximum, we stop searching
					if ($ts > $maxTs)
						return null;
				}

			}

			return $ts;
		}



		/**
		 * @inheritDoc
		 */
		public function matches(int $ts): bool {
			$this->prepare();

			switch($this->dayMode) {

				case self::MODE_DAY_OF_MONTH:
					// only day-of-month is set => it must match
					return $this->matchesFields($ts, [$this->minute, $this->hour, $this->dayOfMonth, $this->month]);

				case self::MODE_DAY_OF_WEEK:
					// only day-of-week is set => it must match
					return $this->matchesFields($ts, [$this->minute, $this->hour, $this->dayOfWeek, $this->month]);

				default:
					// day-of-month and day-of-week are set => one of them must match
					return
						$this->matchesFields($ts, [$this->minute, $this->hour, $this->dayOfMonth, $this->month]) ||
						$this->matchesFields($ts, [$this->minute, $this->hour, $this->dayOfWeek, $this->month]);

			}
		}

		/**
		 * @inheritDoc
		 */
		public function nextAfter(int $ts, int $maxTs): ?int {
			$this->prepare();

			switch ($this->dayMode) {

				case self::MODE_DAY_OF_MONTH:
					// only day-of-month is set
					return $this->nextAfterFields($ts, $maxTs, [$this->month, $this->dayOfMonth, $this->hour, $this->minute]);

				case self::MODE_DAY_OF_WEEK:
					// only day-of-week is set
					return $this->nextAfterFields($ts, $maxTs, [$this->month, $this->dayOfWeek, $this->hour, $this->minute]);

				default:
					// day-of-month and day-of-week are set => return least
					$ret = [
						$this->nextAfterFields($ts, $maxTs, [$this->month, $this->dayOfMonth, $this->hour, $this->minute]),
						$this->nextAfterFields($ts, $maxTs, [$this->month, $this->dayOfWeek, $this->hour, $this->minute]),
					];

					// return lowest
					if ($ret[0] === null)
						return $ret[1];
					elseif ($ret[1] === null)
						return $ret[0];
					else
						return min($ret[0], $ret[1]);
			}

		}


	}